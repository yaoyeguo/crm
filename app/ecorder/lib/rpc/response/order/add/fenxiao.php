<?php

/**
 * 淘宝订单更新
 *
 * @author shiyao744@sohu.com
 * @version 0.1b
 */
class ecorder_rpc_response_order_add_fenxiao extends ecorder_rpc_response_order_add_abstract implements ecorder_rpc_response_order_add_interface {

    /**
     * 析构
     */
    public function ecorder_rpc_response_order_add_fenxiao() {

        self::$__ORDER_APP = 'ecorder';
    }

    /**
     * 检查一个订单是否要被在本地创建
     *
     * @param void
     * @return Boolean
     */
    public function acceptCreateOrder() {

        return true;
    }

    protected function processMemberInfo() {
        $is_mobile_valid = 0;
        $mobile = $this->_orderSdf['consignee']['mobile'];
        if(empty($mobile)){
            $is_mobile_valid = 0;
        }else{
            if(strlen($mobile) > 10){
                $is_mobile_valid = 1;
            }else{
                $is_mobile_valid = 0;
            }
        }

        if($is_mobile_valid){
            $curtime = time();
            $db = kernel::database();
            $row = $db->selectRow('select member_id from sdb_taocrm_fx_members where mobile="'.$mobile.'"');
            $data = array('mobile' => $mobile, 'is_mobile_valid' => $is_mobile_valid,'update_time'=>$curtime);
            if(!$row){
                $data['create_time'] = $curtime;
            }else{
                $data['member_id'] = $row['member_id'];
            }
            app::get('taocrm')->model('fx_members')->save($data);
            $this->_orderSdf['member_id'] = $data['member_id'];
            return $data['member_id'];
        }else{
            return 0;
        }
    }

    protected function createProcess()
    {
        //处理商品信息
        $this->checkProductSku();
         
        //处理客户信息
        $this->processMemberInfo();

        //$this->restPayInfo();

        //条件满足，可生成订单
        kernel::database()->exec('begin');
        
        $orderId = $this->createOrder();

        if ($orderId) {
        
            $this->_result['order_id'] = $orderId;
        
            kernel::database()->commit();

            //处理优惠信息
            //$this->createPmt($orderId);

            //处理支付信息
            //$this->createPayment($orderId);

            //统计客户 店铺 渠道 购买数据
            //$this->countBuys();
             
            //更新客户积分
            //$this->updateMemberPoints($orderId,'orders',$this->_orderSdf['order_bn']);

            //更新到内存
            //$this->hardWareCreateFxOrder($this->_orderSdf);
        }else{
            kernel::database()->rollBack();

            kernel::ilog("rollBack:\n".var_export($this->_orderSdf,true),'error');
        }


        $this->countBuys();

        //更新店铺下载订单时间
        $uAttr = array('shop_id' => $this->_shopInfo['shop_id'], 'last_download_time' => time());
        app::get(self::$__ORDER_APP)->model('shop')->save($uAttr);

        return true;
    }

    /**
     * 统计 店铺 渠道等购买数据
     *
     * @return void
     */
    protected function countBuys()
    {
        kernel::single('taocrm_service_shop')->countShopBuys($this->_shopInfo['shop_id']);
        kernel::single('taocrm_service_channel')->countChannelBuys($this->_shopInfo['channel_id']);
    }

    /**
     * 创建订单
     *
     * @param void
     * @return integer
     */
    protected function createOrder() {

        app::get(self::$__ORDER_APP)->model('fx_orders')->create_order($this->_orderSdf);
        return $this->_orderSdf['order_id'];
    }

    /**
     * 更新一个订单
     *
     * @param void
     * @return array
     */
    public function updateProcess()
    {
        //检查修改时间
        if ($this->_oldOrderInfo['f_modified'] > $this->_orderSdf['f_modified']) {
            return false;
        }

        //理新备注
        $this->updateOrderMemo();

        //更新订单主体内容及状态、包括支付单据，不更新商品列表
        if ($this->needUpdateOrder()) {

            if ($this->_orderSdf['status'] ==  'dead') {
                //更新订单状态
                $this->updateOrderStatus();
                //统计 店铺 渠道 购买数据
                $this->countBuys();

            }else if ($this->_orderSdf['status'] ==  'active' || $this->_orderSdf['status'] == 'finish') {
                 
                if ($this->_orderSdf['pay_status'] <> '0' && $this->_oldOrderInfo['pay_status'] == '0') {

                    //如订单更新为已支付，删除原有内容，重新生成订单，做全部更新
                    $this->deleteOrder($this->_oldOrderInfo['order_id']);
                    //$this->_oldOrderInfo = array();
                    return $this->createProcess();
                }else {
                    if ($this->specChange()) {
                        //重建
                        $this->deleteOrder($this->_oldOrderInfo['order_id']);
                        //$this->_oldOrderInfo = array();
                        return $this->createProcess();
                    } else {
                        //更新主表内容,主要是发货地址
                        $this->updateOrderMaster();

                        if($this->_orderSdf['status'] == 'finish'){
                            //统计客户 店铺 渠道 购买数据
                            $this->countBuys();
                        }
                    }
                }
            }

            // $this->hardWareUpdateOrder($this->_orderSdf);
        }

        return false;
    }

    /**
     * 更新订单备注
     *
     * @param void
     * @return void
     */
    protected function updateOrderMemo() {

        $updateBody = array();

        if ($this->_orderSdf['custom_mark']) {
            $content = $this->createOrderMemo($this->_orderSdf['custom_mark'], $this->_oldOrderInfo['custom_mark']);
            if ($content)
            $updateBody['custom_mark'] = $content;
        }

        if ($this->_orderSdf['mark_text']) {
            $content = $this->createOrderMemo($this->_orderSdf['mark_text'], $this->_oldOrderInfo['mark_text']);
            if ($content)
            $updateBody['mark_text'] = $content;
        }

        if (!empty($updateBody)) {

            app::get(self::$__ORDER_APP)->model('fx_orders')->update($updateBody, array('order_id' => $this->_oldOrderInfo['order_id']));
        }
    }

    /**
     * 检查规则参数是否发生变化
     *
     * @param void
     * @return boolean
     */
    private function specChange() {
        return ($this->_orderSdf['product_hash'] == $this->_oldOrderInfo['product_hash'] ? false : true);
    }

    /**
     * 更新订单主表内容，主要是一些状态位
     *
     * @param void
     * @return void
     */
    protected function updateOrderMaster() {


        //创建 HASH,基本没有用，去除 ，换会使用SQL语句直接更新数据内容
        //$this->createCombineIndex();

        //强制更新发货地址等收货人信息
        $updateBody = array(
            'order_id' => $this->_oldOrderInfo['order_id'],
            'status' => $this->_orderSdf['status'],
            'pay_status' => $this->_orderSdf['pay_status'],
            'ship_status' => $this->_orderSdf['ship_status'],
            'mark_type' => $this->_orderSdf['mark_type'],
            'modified' => $this->_orderSdf['modified'],
            'f_modified' => $this->_orderSdf['modified'],
            'finish_time' => $this->_orderSdf['finish_time'],
            'delivery_time' => $this->_orderSdf['	'],

            'trade_type' => $this->_orderSdf['trade_type'],
            'step_trade_status' => $this->_orderSdf['step_trade_status'],
            'step_paid_fee' => $this->_orderSdf['step_paid_fee'],
            'errortrade_desc' => $this->_orderSdf['errortrade_desc'],
            'is_errortrade' => $this->_orderSdf['is_errortrade'],
        );

        if($this->_orderSdf['ship_status'] == '1') {
            $updateBody['is_delivery'] = 'Y';
        }

        if ($this->_orderSdf['consignee'] && $this->_orderSdf['consignee']['area']) {

            $updateBody['consignee'] = $this->_orderSdf['consignee'];
            //对地区做校验
            $area = $updateBody['consignee']['area'];
            kernel::single("ecorder_func")->region_validate($area);
            $updateBody['consignee']['area'] = $area;
        }

        app::get(self::$__ORDER_APP)->model('fx_orders')->save($updateBody);
    }

    /**
     * 更新订单状态
     *
     * @param void
     * @return void
     */
    protected function updateOrderStatus() {
        //获取 member_id
        //$this->processMemberInfo();

        $updateBody = array(
            'order_id' => $this->_oldOrderInfo['order_id'],
            'status' => $this->_orderSdf['status'],
            'f_modified' => $this->_orderSdf['modified'],
        	'modified' => $this->_orderSdf['modified'],
        );

        app::get(self::$__ORDER_APP)->model('fx_orders')->save($updateBody);
        //$this->hardWareUpdateOrder($updateBody);
    }




    /**
     * 检查一个订单是否要更新
     *
     * @param void
     * @return boolean
     */
    protected function needUpdateOrder() {

        return true;
    }

    /**
     * 删除一个订单
     *
     * @param Integer $orderId
     * @return void
     */
    protected function deleteOrder($orderId) {

        app::get(self::$__ORDER_APP)->model('fx_orders')->delete(array('order_id' => $orderId));
        app::get(self::$__ORDER_APP)->model('fx_order_objects')->delete(array('order_id' => $orderId));
        app::get(self::$__ORDER_APP)->model('fx_order_items')->delete(array('order_id' => $orderId));
    }

    protected function parseSdf($sdf)
    {
        //分别提出支付、优惠等信息
        $this->_orderPmt = $sdf['pmt_detail'];
        $this->_orderPayment = $sdf['payment_detail'];

        unset($sdf['payment_detail']);
        //unset($sdf['order_source']);
        unset($sdf['pmt_detail']);
        $this->_orderSdf = $sdf;
        $selling_agent = json_decode($sdf['selling_agent'],true);
        $this->_orderSdf['agent_uname'] = $selling_agent['uname'];
        $this->_orderSdf['source_order'] = json_encode($sdf);
        $this->_result = array('tid' => $sdf['order_bn']);
    }

    /**
     * 通过订单编号和SHOPID获取订单内容
     *
     * @param string $orderBN 订单编号
     * @param string $shopId 店铺ID
     * @return mixed
     */
    protected function fetchOldOrderInfo($orderBN, $shopId) {
        //设置默认操作正式订单环境
        $this->_isLogOrder = false;

        //检查正式订单表
        $this->_oldOrderInfo = app::get(self::$__ORDER_APP)->model('fx_orders')->dump(array('order_bn' => $orderBN, 'shop_id' => $shopId), '*');
        if (empty($this->_oldOrderInfo)) {
            $this->_isLogOrder = true;
        }
    }

}