<?php

/**
 * 淘宝订单更新
 *
 * @author shiyao744@sohu.com
 * @version 0.1b
 */
class ecgoods_rpc_response_order_add_taobao extends ecgoods_rpc_response_order_add_abstract implements ecgoods_rpc_response_order_add_interface {

    /**
     * 析构
     */
    public function ecgoods_rpc_response_order_add_taobao() {

        self::$__ORDER_APP = 'ecgoods';
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

    /**
     * 更新一个订单
     *
     * @param void
     * @return array
     */
    public function updateProcess() {

        //检查修改时间
        if ($this->_oldOrderInfo['f_modified']>= $this->_orderSdf['modified']) {

            return false;
        }

         
        //理新备注
        $this->updateOrderMemo();

        //更新订单主体内容及状态、包括支付单据，不更新商品列表
        if ($this->needUpdateOrder()) {

            if ($this->_orderSdf['status'] ==  'dead') {
                //更新订单状态
                $this->updateOrderStatus();
                //统计客户 店铺 渠道 购买数据
                $this->countBuys();

            }else if ($this->_orderSdf['status'] ==  'active' || $this->_orderSdf['status'] == 'finish') {
                 
                if ($this->_orderSdf['pay_status'] <> '0' && $this->_oldOrderInfo['pay_status'] == '0') {

                    //如订单更新为已支付，删除原有内容，重新生成订单，做全部更新
                    $this->deleteOrder($this->_oldOrderInfo['order_id']);
                    $this->_oldOrderInfo = array();
                    return $this->createProcess();
                }else {
                    if ($this->specChange()) {
                        //重建
                        $this->deleteOrder($this->_oldOrderInfo['order_id']);
                        $this->_oldOrderInfo = array();
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
        }

        return false;
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

        //获取 member_id
        $this->processMemberInfo();
        //创建 HASH,基本没有用，去除 ，换会使用SQL语句直接更新数据内容
        //$this->createCombineIndex();
        //更新发货地址等收货人信息
        $updateBody = array(
            'order_id' => $this->_oldOrderInfo['order_id'],
            'status' => $this->_orderSdf['status'],
            'ship_status' => $this->_orderSdf['ship_status'],
            'mark_type' => $this->_orderSdf['mark_type'],
            'f_modified' => $this->_orderSdf['modified'],
        );

        if ($this->_orderSdf['consignee'] && $this->_orderSdf['consignee']['area']) {

            $updateBody['consignee'] = $this->_orderSdf['consignee'];
            //对地区做校验
            $area = $updateBody['consignee']['area'];
            kernel::single("ecgoods_func")->region_validate($area);
            $updateBody['consignee']['area'] = $area;
        }
        app::get(self::$__ORDER_APP)->model('orders')->save($updateBody);
    }

    /**
     * 更新订单状态
     *
     * @param void
     * @return void
     */
    protected function updateOrderStatus() {
        //获取 member_id
        $this->processMemberInfo();
        
        $updateBody = array(
            'order_id' => $this->_oldOrderInfo['order_id'],
            'status' => $this->_orderSdf['status'],
            'f_modified' => $this->_orderSdf['modified'],
        );

        app::get(self::$__ORDER_APP)->model('orders')->save($updateBody);
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

}