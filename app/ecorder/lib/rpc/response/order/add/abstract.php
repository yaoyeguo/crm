<?php

/**
 * 订单接收模块抽像类
 *
 * @author hzjsq@msn.com
 * @version 0.1b
 */
abstract class ecorder_rpc_response_order_add_abstract {

    /**
     * 订单数据模块所在APP名
     * @var boolean
     */
    static $__ORDER_APP = 'ecorder';
    /**
     * 商品模式
     */
    protected static $ecgoodsObj = null;
    /**
     * 订单结构
     * @var Array
     */
    protected $_orderSdf = array();
    /**
     * 优惠结构
     * @var Array
     */
    protected $_orderPmt = array();
    /**
     * 支付结构
     * @var Array
     */
    protected $_orderPayment = array();
    /**
     * RPC对像
     * @var object
     */
    protected $_response = null;
    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();
    /**
     * 返回值
     * @var array
     */
    protected $_result = array();
    /**
     * 订单内容
     * @var array
     */
    protected $_oldOrderInfo = array();

    /**
     * 支持的店铺类型
     * @var array
     */
    protected $_supportShopTypes = array();

    /**
     * 接收并更新订单
     *
     * @param mixed $sdf 标准订单结构
     * @param object $response RPC对像
     * @return mixed
     */
    /**
     * 硬件接口实例化
     */
    protected static $hardWareConnect = null;

    public function getConnect()
    {
        if (self::$hardWareConnect == null) {
            self::$hardWareConnect = new taocrm_middleware_connect;
        }
        return self::$hardWareConnect;
    }

    /**
     * 检查数据库所有是否加载
     */
    public function checkDbIndexState()
    {
        $connect = $this->getConnect();
        $result = $connect->DbIndexState();
        if ($result == 'READY') {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 更新订单数据
     */
    public function hardWareUpdateOrder($data)
    {
        //去掉向内存请求修改订单信息
        return ;
        $hardStatus = $this->checkDbIndexState();
        if ($hardStatus) {
            $orderParams = array();
            $orderParams['orderId'] = $data['order_id'];
            $orderParams['shopId'] = $data['shop_id'];
            $orderParams['memberId'] = $data['member_id'];
            $orderParams['status'] = $data['status'];
            $orderParams['payStatus'] = $data['pay_status'];
            $orderParams['createTime'] = $data['createtime'];
            $orderParams['totalAmount'] = $data['total_amount'];
            $orderParams['itemNum'] = $data['item_num'];
            $orderParams['stateId'] = $data['state_id'];
            $orderParams['shipStatus'] = $data['ship_status'];

            $connect = $this->getConnect();
            //更新订单到内存
            $result = $connect->updateOrder($orderParams);
            if ($result == 'true') {
                $orderItmeParams = array();
                $orderItemData =  $data['order_objects'][0]['order_items'];
                $this->hardWareCreateOrderItem($orderItemData, 'updateOrderItem');
            }
        }
        else {
            kernel::ilog("rollBack:\n".var_export($this->_orderSdf,true),'hardware');
        }
    }

    /**
     * 创建更新订单明细到内存
     */
    public function hardWareCreateOrderItem($data, $funcName = 'addOrderItem')
    {
        $connect = $this->getConnect();
        $orderItmeParams = array();
        foreach ($data as $k => $v) {
            $orderItmeParams['shopId'] = $v['shop_id'];
            $orderItmeParams['itemId'] = $v['item_id'];
            $orderItmeParams['amount'] = $v['amount'];
            $orderItmeParams['createTime'] = $v['create_time'];
            $orderItmeParams['orderId'] = $v['order_id'];
            $orderItmeParams['goodsId'] = $v['goods_id'];
            $orderItmeParams['nums'] = $v['quantity'];
            $connect->$funcName($orderItmeParams);
        }
        return true;
    }

    /**
     * 创建内存订单
     */
    public function hardWareCreateOrder($data)
    {
        //去掉向内存请求添加订单信息
        return;
        $hardStatus = $this->checkDbIndexState();
        if ($hardStatus) {
            $orderParams = array();
            $orderParams['orderId'] = $data['order_id'];
            $orderParams['shopId'] = $data['shop_id'];
            $orderParams['memberId'] = $data['member_id'];
            $orderParams['status'] = $data['status'];
            $orderParams['payStatus'] = $data['pay_status'];
            $orderParams['createTime'] = $data['createtime'];
            $orderParams['totalAmount'] = $data['total_amount'];
            $orderParams['itemNum'] = $data['item_num'];
            $orderParams['stateId'] = $data['state_id'];
            $orderParams['shipStatus'] = $data['ship_status'];

            $connect = $this->getConnect();
            //添加订单到内存
            $result = $connect->addOrder($orderParams);
            if ($result == 'true') {
                $orderItmeParams = array();
                $orderItemData =  $data['order_objects'][0]['order_items'];
                $this->hardWareCreateOrderItem($orderItemData);
                //                foreach ($orderItemData as $k => $v) {
                //                    $orderItmeParams['shopId'] = $v['shop_id'];
                //                    $orderItmeParams['itemId'] = $v['item_id'];
                //                    $orderItmeParams['amount'] = $v['amount'];
                //                    $orderItmeParams['createTime'] = $v['create_time'];
                //                    $orderItmeParams['orderId'] = $v['order_id'];
                //                    $orderItmeParams['goodsId'] = $v['goods_id'];
                //                    $orderItmeParams['nums'] = $v['quantity'];
                //
                //                }
            }
            //添加订单明细到内存
        }
        else {
            kernel::ilog("rollBack:\n".var_export($this->_orderSdf,true),'hardware');
        }
    }

    public function add($sdf, &$response)
    {
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '订单接口['. $sdf['order_bn'] .']';
        $logInfo = '订单接口：<BR>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<BR>';
        
        //已发货订单默认为已付款
        if($sdf['ship_status']==1) $sdf['pay_status']=1;

        //获取店铺信息并做检查
        $this->_shopInfo = $this->fetchShopInfo();
        if (empty($this->_shopInfo)) {
            $err_msg = "Can't recognize the shopInfo";
            $logInfo .= '返回值为：' . $err_msg . '<BR>';
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
        
            $this->throwError($err_msg);
            return false;
        }

        //设置RPC对像
        $this->_response = $response;

        //无订单编号
        if (empty($sdf['order_bn'])) {
            $err_msg = "Order number can not be empty";
            $logInfo .= '返回值为：' . $err_msg . '<BR>';
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
            
            $this->throwError($err_msg);
            return false;
        }
        
        //获取老订单内容
        $this->fetchOldOrderInfo($sdf['order_bn'], $this->_shopInfo['shop_id']);
        
        //设置并检查标准订单数据的完整性
        $this->parseSdf($sdf);
         
        //订单主体结构参数转换
        $this->convertSdfParams();

        $this->convrtSdfObjectsParams();        

        //插件用订单数据
        kernel::single('plugins_service_api')->save_trades($this->_orderSdf);

        //检查该订单是否存在
        if(empty($this->_oldOrderInfo['order_bn'])){
            //检查该订单是否需操作该订单
            if($this->acceptCreateOrder()){
                $return_value = $this->createProcess();
            
                $logTitle = '订单创建接口['. $sdf['order_bn'] .']';
                $logInfo .= '返回值为：' . var_export($this->_result, true) . '<BR>';
                if(!$return_value){
                    $res = 'fail';
                }else{
                    $res = 'success';
                }
                $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', $res, $logInfo, array('task_id'=>$sdf['order_bn']));
            }
        }else{
            $this->updateProcess();
            
            if( ! $this->_orderSdf['order_id']){
                $this->_orderSdf['order_id'] = $this->_oldOrderInfo['order_id'];
            }
            
            $logTitle = '订单更新接口['. $sdf['order_bn'] .']';
            $logInfo .= '返回值为：' . var_export($this->_result, true) . '<BR>';
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo, array('task_id'=>$sdf['order_bn']));
        }
        
        if( ! $this->_orderSdf['member_id'] && $this->_oldOrderInfo['member_id']){
            $this->_orderSdf['member_id'] = $this->_oldOrderInfo['member_id'];
        }
        
        //订单的附加处理
        $objOrderProcess = kernel::servicelist("order.process");
        foreach($objOrderProcess as $obj){
            if(method_exists($obj, 'process')){
                $obj->process($this->_orderSdf);
            }
        }

        return $this->_result;
    }

    public function initSupportShopType($supportShopTypes)
    {
        $this->_supportShopTypes = $supportShopTypes;
    }

    /**
     * 获得商品ID号
     * @param array $goodsInfo 商品数据
     */
    protected function geGoodsId($goodsInfo)
    {
        $ecgoodsObj = $this->getShopGoosModel();
        $shopGoodsId = $goodsInfo['shop_goods_id'];
        $shopType = $this->_orderSdf['shop_type'];
        $shopId = $this->_orderSdf['shop_id'];
        $ecgoodsInfo = 0;
        $goodsId = null;

        //判断店铺类型是否是淘宝
        /*
         if ($shopType == 'taobao' && $shopId && $shopGoodsId && $shopGoodsId!='0') {
         $filter = array();
         $filter['shop_id'] = $shopId;
         $filter['outer_id'] = $shopGoodsId;
         $ecgoodsInfo = $ecgoodsObj->dump($filter);
         //根据shop_goods_id是否查询到商品
         }

         if(isset($ecgoodsInfo['goods_id']) && $ecgoodsInfo['goods_id']){
         $goodsId = $ecgoodsInfo['goods_id'];
         }elseif($goodsInfo['name']!=''){
         //检查根据商品名称是否在店铺
         $filter = array();
         $filter['name'] = $goodsInfo['name'];
         $ecgoodsInfo = $ecgoodsObj->dump($filter);
         if ($ecgoodsInfo)
         $goodsId = $ecgoodsInfo['goods_id'];
         unset($filter);
         }
         */

        //仅根据商品名称查找商品ID
        if($goodsInfo['name']!=''){
            $filter = array();
            $filter['name'] = $goodsInfo['name'];
            $ecgoodsInfo = $ecgoodsObj->dump($filter);
            if ($ecgoodsInfo)
            $goodsId = $ecgoodsInfo['goods_id'];
            unset($filter);
        }

        //商品不存在，插入商品
        if ($goodsId == null && $goodsInfo['name']!='') {
            $time = time();
            $insertData = array(
                'shop_id' => $this->_orderSdf['shop_id'], 
                'outer_id' => $shopGoodsId,
                'bn' => $goodsInfo['order_items'][0]['bn'],
                'name' => $goodsInfo['name'],
                'goods_type' => $goodsInfo['item_status'],
                'cost' => $goodsInfo['order_items'][0]['cost'],
                'price' => $goodsInfo['order_items'][0]['price'],
                'weight' => $goodsInfo['weight'],
                'last_modify' => $time,
                'create_time' => $time,
                'total_num' => 0,
                'sale_money' => 0,
            );
            $goodsId = $ecgoodsObj->insert($insertData);
        }
        if( ! $goodsId){
            $goodsId = 0;
        }

        //统计商品销量
        if($goodsId>0){
            $total_num = $goodsInfo['quantity'];
            $sale_money = $goodsInfo['amount'];
            if(!$total_num) $total_num=0;
            if(!$sale_money) $sale_money=0;
            if($total_num>0){
                $sql = "update sdb_ecgoods_shop_goods set
                total_num=total_num+$total_num, sale_money=sale_money+$sale_money
                where goods_id=$goodsId ";
                kernel::database()->exec($sql);
            }
        }

        return $goodsId;
    }

    //实例化店铺商品模式
    protected function getShopGoosModel()
    {
        if (self::$ecgoodsObj == null) {
            self::$ecgoodsObj = app::get('ecgoods')->model('shop_goods');
        }
        return self::$ecgoodsObj;
    }

    /**
     * 更新订单备注
     *
     * @param void
     * @return void
     */
    protected function updateOrderMemo()
    {
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

        if(!empty($updateBody)){
            app::get(self::$__ORDER_APP)->model('orders')->update($updateBody, array('order_id' => $this->_oldOrderInfo['order_id']));
        }
    }

    /**
     * 获取要更新的备注类内容
     *
     * @param String $nContent 新内容
     * @param String $oContent 原有内容
     * @return String
     */
    protected function createOrderMemo($nContent, $oContent) {

        $nContent = trim($nContent);
        $opName = trim($this->_shopInfo['shop_type']);
        $contents = array();
        if ($oContent) {
            $contents = unserialize($oContent);

            //生成索引
            $opIdx = array();
            foreach ((array) $contents as $idx => $cnt) {
                $opIdx[$cnt['op_name']] = $idx;
            }

            //检查有无同类来源的更新
            if (isset($opIdx[$opName])) {
                //有并内容不一致则更新内容
                if (trim($nContent) <> trim($contents[$opIdx[$opName]]['op_content']))
                $contents[$opIdx[$opName]] = array('op_name' => $opName, 'op_time' => time(), 'op_content' => $nContent);
            } else {
                //无则新增内容
                $contents[] = array('op_name' => $opName, 'op_time' => time(), 'op_content' => htmlspecialchars($nContent));
            }
        } else {
            $contents[] = array('op_name' => $opName, 'op_time' => time(), 'op_content' => htmlspecialchars($nContent));
        }

        return serialize($contents);
    }

    /**
     * 删除一个订单
     *
     * @param Integer $orderId
     * @return void
     */
    protected function deleteOrder($orderId)
    {
        app::get(self::$__ORDER_APP)->model('orders')->delete(array('order_id' => $orderId));
        app::get(self::$__ORDER_APP)->model('order_objects')->delete(array('order_id' => $orderId));
        app::get(self::$__ORDER_APP)->model('order_items')->delete(array('order_id' => $orderId));
        app::get(self::$__ORDER_APP)->model('order_pmt')->delete(array('order_id' => $orderId));
        
        //$this->delPointsLog($orderId);//删除积分
    }

    /**
     * 删除积分日志
     */
    protected function delPointsLog($orderId)
    {
        $db = kernel::database();
        $sql = "SELECT `member_id`, `shop_id`, sum(`points`) as sum_points FROM `sdb_taocrm_all_points_log` WHERE `order_id` = {$orderId}";
        $poinitsLogInfo = $db->select($sql);
        /**
         * 删除积分日志
         */
        if ($poinitsLogInfo) {
            $memberId = $poinitsLogInfo[0]['member_id'];
            $shopId = $poinitsLogInfo[0]['shop_id'];
            //获得客户对应的分析数据
            $sql = "SELECT `points` FROM `sdb_taocrm_member_analysis` WHERE `member_id` = {$memberId} AND `shop_id` = '{$shopId}'";
            $memberAnalysisInfo = $db->select($sql);
            if ($memberAnalysisInfo) {
                $oldPoints = $points = $memberAnalysisInfo[0]['points'];
                //                $tmpPoints = 0;
                //                foreach ($poinitsLogInfo as $v) {
                //                    $tmpPoints += $v['points'];
                //                }
                $points = max(0, ($points - $poinitsLogInfo[0]['sum_points']));
                $poinitsLogInfo[0]['sum_points'] *= -1;
                //更新客户表信息
                $sql = 'update sdb_taocrm_member_analysis set points=' . $points . ' WHERE member_id=' . $memberId . ' AND shop_id="' . $shopId . '" ';
                $db->exec($sql);
                //增加删除积分日志
                $time = time();
                $sql = "INSERT INTO `sdb_taocrm_all_points_log` (`member_id`,`points`,`order_id`,`shop_id`,`op_time`,`is_active`,`op_user`,`point_desc`)
                        VALUES ({$memberId}, {$poinitsLogInfo[0]['sum_points']},{$orderId},'{$shopId}',{$time}, '1', 'system','delete order points')";
                $db->exec($sql);
                //删除积分日志
                //                $sql = "DELETE FROM `sdb_taocrm_points_log` WHERE `member_id` = {$memberId} AND `shop_id` = '{$shopId}'";
                //                $db->exec($sql);
            }
        }
    }

    /**
     * 创建一个新的订单
     *
     * @param void
     * @return Array();
     */
    protected function createProcess()
    {
        //处理商品信息
        $this->checkProductSku();
         
        //处理订单商品信息转换
        //gyct:移到父方法
        //$this->convrtSdfObjectsParams();

        //处理客户信息
        if(!$this->processMemberInfo()){
            $this->throwError('客户创建失败','客户创建失败');
            return false;
        }

        $this->_result['member_id'] = $this->_orderSdf['member_id'];

        //设置订单失败时间
        //$this->setOrderLimitTime();
        //将订单的已支付金额与支付状态初始化，即支付金额为0，支付状态为0（未支付）
        //TODO：此步操作是为了先将所有的订单为未支付，然后会在生成支付单的时候根据支付单数据改变订单的已支付金额与支付状态
        $this->restPayInfo();
        
        //处理备注及留言
        $this->convertSdfMemo();

        //条件满足，可生成订单
        kernel::database()->exec('begin');
        $orderId = $this->createOrder();

        if($orderId){
        
            $this->_result['order_id'] = $orderId;
        
            kernel::database()->commit();

            //处理优惠信息
            $this->createPmt($orderId);

            //处理支付信息
            $this->createPayment($orderId);

            //统计客户 店铺 渠道 购买数据
            $this->countBuys();
             
            //更新客户积分
            $this->updateMemberPoints($orderId,'orders',$this->_orderSdf);

            //更新到内存
            //$this->hardWareCreateOrder($this->_orderSdf);
        }else{
            kernel::database()->rollBack();
            kernel::ilog("rollBack:\n".var_export($this->_orderSdf,true),'error');
            return false;
        }

        //更新店铺下载订单时间
        $uAttr = array('shop_id' => $this->_shopInfo['shop_id'], 'last_download_time' => time());
        app::get(self::$__ORDER_APP)->model('shop')->save($uAttr);

        return $orderId;
    }

    protected function checkProductSku() {
        foreach ($this->_orderSdf['order_objects'] as $gKey => $object) {
            $this->_orderSdf['order_objects'][$gKey]['goods_id'] = $this->geGoodsId($object);

            foreach ($object['order_items'] as $iKey => $item) {
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['goods_id'] = $this->_orderSdf['order_objects'][$gKey]['goods_id'];
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['product_id'] = 0;
                if($item['shop_product_id']){
                    $row = kernel::database()->selectrow('select product_id from sdb_ecgoods_shop_products where outer_sku_id="'.$item['shop_product_id'].'"');
                    if($row){
                        $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['product_id'] = $row['product_id'];
                    }
                }
            }
        }
    }

    /**
     * 创建订单
     *
     * @param void
     * @return integer
     */
    protected function createOrder()
    {
        app::get(self::$__ORDER_APP)->model('orders')->create_order($this->_orderSdf);
        return $this->_orderSdf['order_id'];
    }

    /**
     * 转换备注及留言信息
     *
     * @param void
     * @return void
     */
    protected function convertSdfMemo() {

        //买家留言
        $custom_memo = $this->_orderSdf['custom_mark'];

        if (strtolower(trim($this->_orderSdf['shipping']['shipping_name'])) == 'ems') {
            $custom_memo = empty($custom_memo) ? "系统：用户选择了 EMS 的配送方式" : "{$custom_memo}\n系统：用户选择了 EMS 的配送方式";
        }

        if ($custom_memo) {
            $custommemo[] = array('op_name' => $this->_shopInfo['shop_type'], 'op_time' => date("Y-m-d H:i:s", time()), 'op_content' => htmlspecialchars($custom_memo));
            $this->_orderSdf['custom_mark'] = serialize($custommemo);
        }
        //订单备注
        $mark_memo = $this->_orderSdf['mark_text'];
        if ($mark_memo) {
            $markmemo[] = array('op_name' => $this->_shopInfo['shop_type'], 'op_time' => date("Y-m-d H:i:s", time()), 'op_content' => htmlspecialchars($mark_memo));
            $this->_orderSdf['mark_text'] = serialize($markmemo);
        }
    }

    /*
     * 创建支付信息
     * @param string order_id
     *
     */
    private function createPayment($orderId)
    {
        if ($this->_tmpPay['status'] == 1 && in_array($this->_shopInfo['shop_type'], $this->_supportShopTypes)) {
            $shop_id = $this->_orderSdf['shop_id'];
            $payment_money = $this->_tmpPay['payed'] + 0;

            $orderObj = app::get('ecorder')->model('orders');
            $order_detail = $orderObj->dump(array('order_id'=>$orderId), 'payed,cost_payment,total_amount');

            //支付状态已支付,订单总金额为不为0，已支付金额为0得情况下跳出，不处理
            if(!$order_detail || ($order_detail['total_amount'] != 0 && $payment_money == 0) ){
                return;
            }

            $filter = array('order_id'=>$orderId);
            $orderdata['payed'] = $order_detail['payed'] + $payment_money;
            if ($orderdata['payed'] < $order_detail['total_amount'])
            {
                //如果已经付款金额小于总金额，则为部分付款
                $orderdata['pay_status'] = 1;
            }else{
                //如果已经付款金额等于总金额，则为全部付款
                $orderdata['pay_status'] = 1;
            }

            //支付时间
            $paymentDetail = json_decode($this->_orderPayment, true);
            $orderdata['pay_time'] = kernel::single('ecorder_func')->date2time($paymentDetail['pay_time']);

            //$orderdata['pay_time'] = strtotime($paymentDetail['pay_time']);
            
            //解析ecstore的付款单
            if($this->_orderSdf['payment_lists']){
                $payment_lists = json_decode($this->_orderSdf['payment_lists'], true);
                if($payment_lists['payment_list'][0] && $payment_lists['payment_list'][0]['status']=='SUCC'){
                    //以初次付款时间为准
                    $orderdata['pay_time'] = max(strtotime($payment_lists['payment_list'][0]['t_begin']), strtotime($payment_lists['payment_list'][0]['t_end']));
                }
            }
            
            //老订单数据的付款时间
            if(!$orderdata['pay_time'] && $this->_oldOrderInfo['pay_time']){
                $orderdata['pay_time'] = $this->_oldOrderInfo['pay_time'];
            }
            
            //自动填充付款时间
            if(!$orderdata['pay_time'] && $orderdata['pay_status']==1){
                $orderdata['pay_time'] = time();
            }  

            $orderObj->update($orderdata, $filter);
            $this->_orderSdf['pay_time'] = $orderdata['pay_time'];
            $this->_orderSdf['payed'] = $orderdata['payed'];
            $this->_orderSdf['pay_status'] = $orderdata['pay_status'];

            //大屏幕数据
            //kernel::single('market_backstage_publish')->push_order($this->_orderSdf);
        }
    }    

    /**
     * 将订单的已支付金额与支付状态初始化，即支付金额为0，支付状态为0（未支付）
     *
     * @param void
     * @return void
     */
    protected function restPayInfo() {

        $this->_tmpPay = array('payed' => $this->_orderSdf['payed'], 'status' => $this->_orderSdf['pay_status']);
        $this->_orderSdf['payed'] = 0;
        $this->_orderSdf['pay_status'] = '0';
    }

    /**
     * 创建订单优惠信息
     *
     * @param integer $orderId 订单ID
     * @return void
     */
    protected function createPmt($orderId)
    {
        //兼容ecstore订单格式
        if( ! is_array($this->_orderPmt)){
            $pmtDetail = json_decode($this->_orderPmt, true);
        }else{
            $pmtDetail = $this->_orderPmt;
        }
            
        if ($pmtDetail) {
            foreach ($pmtDetail as $k => $v) {
                //如优惠金额为空，则跳过
                if (!$v['pmt_amount'])
                continue;
                $pmt_sdf = array(
                    'order_id' => $orderId,
                    'pmt_amount' => $v['pmt_amount'],
                    'pmt_describe' => $v['pmt_describe'],
                    'pmt_desc' => $v['pmt_desc'],
                    'promotion_id' => $v['promotion_id'],
                    'oid' => $v['pmt_id'],
                );

                //营销工具id-优惠活动id_优惠详情id，如mjs-123024_211143）
                //AAA1328703854-33462933_146490834
                //shopbonus-14288856_14288856-5521602042
                if($v['promotion_id'] != '' && strstr($v['promotion_id'],'_')){
                    $promotion_id_arr = explode('_',$v['promotion_id']);
                    if(strstr($promotion_id_arr[1],'-')){
                        $promotion_id_arr = explode('-',$promotion_id_arr[1]);
                        $pmt_sdf['coupon_id'] = $promotion_id_arr[0];
                    }else{
                        $pmt_sdf['coupon_id'] = $promotion_id_arr[1];
                    }
                }

                app::get(self::$__ORDER_APP)->model('order_pmt')->save($pmt_sdf);
            }
        }
    }

    /**
     * 统计客户 店铺 渠道等购买数据
     *
     * @return void
     */
    protected function countBuys()
    {
        if( ! $this->_orderSdf['member_id']) return false;
        
        kernel::single('taocrm_service_member')->countMemberBuys( $this->_orderSdf['member_id'],$this->_shopInfo['shop_id']);

        //todo:每天按最后购买时间来更新客户统计信息
        return true;//gyct:屏蔽实时统计客户信息 20141111

        kernel::single('taocrm_service_shop')->countShopBuys($this->_shopInfo['shop_id']);
        kernel::single('taocrm_service_channel')->countChannelBuys($this->_shopInfo['channel_id']);
    }

    /**
     * 统计客户积分
     *
     * @return void
     */
    protected function updateMemberPoints($orderId,$type,$_orderSdf)
    {
        //已支付给积分
        if($this->_orderSdf['pay_status'] == 1){
            kernel::single('taocrm_service_member')->updateMemberPoints( $orderId,$type,$_orderSdf);
        }
    }


    /**
     * 设置订单的失败时间
     *
     * @param void
     * @return void
     */
    protected function setOrderLimitTime()
    {
        $this->_orderSdf['order_limit_time'] = time() + 60 * (app::get('ecorder')->getConf('ecorder.order.failtime'));
    }

    /**
     * 处理并保存客户信息
     *
     * @param void
     * @return void
     */
    protected function processMemberInfo()
    {
        $this->_orderSdf['member_id'] = null;
        $shop_id = $this->_shopInfo['shop_id'];
        if ($this->_orderSdf['member_info']) {
            $oServiceMember = kernel::single('taocrm_service_member');
            $member_id = $oServiceMember->saveMember($shop_id, $this->_orderSdf['member_info'], $this->_orderSdf['consignee'], $this->_shopInfo['node_type'], $this->_orderSdf['createtime']);

            if ($member_id) {
                $this->_orderSdf['member_id'] = $member_id;

                //初始化客户分析表数据
                $oServiceMember->createMemberAnalysis($member_id,$shop_id,$this->_orderSdf);
            }else{
                $log = app::get('ecorder')->model('api_log');
                $logTitle = '会员接口['. $this->_orderSdf['order_bn'] .']';
                $logInfo = '会员接口：<BR>';
                $logInfo .= '接收参数 $sdf 信息：' . var_export($this->_orderSdf, true) . '<BR>';
                $logInfo .= '会员创建失败！<BR>';
                $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$this->_orderSdf['order_bn']));
            }
        }

        return $this->_orderSdf['member_id'];
    }


    /**
     * 处理订单商品信息转换
     *
     * @param void
     * @return void
     */
    protected function convrtSdfObjectsParams()
    {
        //不处理无订单明细的数据
        if(!$this->_orderSdf['order_objects']){
            return true;
        }
    
        $logistics_company = '';
        $logistics_code = '';
        $item_num = 0;

        foreach ($this->_orderSdf['order_objects'] as $gKey => $object) {
            //商品参数转换
            if($object['logistics_company']){
                $logistics_company = $object['logistics_company'];
            }
            if($object['logistics_code']){
                $logistics_code = $object['logistics_code'];
            }

            $this->_orderSdf['order_objects'][$gKey]['obj_type'] = $object['obj_type'] ? $object['obj_type'] : 'goods';
            $this->_orderSdf['order_objects'][$gKey]['shop_goods_id'] = $object['shop_goods_id'] ? $object['shop_goods_id'] : 0;
            $this->_orderSdf['order_objects'][$gKey]['goods_id'] = $object['goods_id'];
            $this->_orderSdf['order_objects'][$gKey]['oid'] = $object['oid'] ? $object['oid'] : 0;
            $this->_orderSdf['order_objects'][$gKey]['price'] = $object['price'] ? $this->get_money($object['price']) : 0.00;
            $this->_orderSdf['order_objects'][$gKey]['weight'] = $object['weight'] ? $object['weight'] : 0.00;
            $this->_orderSdf['order_objects'][$gKey]['amount'] = $object['amount'] ? $this->get_money($object['amount']) : 0.00;
            $this->_orderSdf['order_objects'][$gKey]['quantity'] = $object['quantity'] ? $object['quantity'] : 0;
            $item_num += $this->_orderSdf['order_objects'][$gKey]['quantity'];
            //转换产品参数
            foreach ($object['order_items'] as $iKey => $item) {
                //货号规格属性
                $item['shop_goods_id'] = $item['shop_goods_id'] ? $item['shop_goods_id'] : 0;
                $item['shop_product_id'] = $item['shop_product_id'] ? $item['shop_product_id'] : 0;
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['addon'] = $item['product_attr'] ? serialize(array('product_attr' => $item['product_attr'])) : '';
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['delete'] = ($item['status'] == 'close') ? true : false;
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['shop_goods_id'] = $item['shop_goods_id'];
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['shop_product_id'] = $item['shop_product_id'];
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['goods_id'] = $item['goods_id'];
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['product_id'] = $item['product_id'];
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['oid'] = $object['oid'] ? $object['oid'] : 0;
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['price'] = $item['price'] ? $this->get_money($item['price']) : 0.00;
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['amount'] = $item['amount'] ? $this->get_money($item['amount']) : 0.00;
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['quantity'] = $item['quantity'] ? $item['quantity'] : 1;
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['sendnum'] = $item['sendnum'] ? floatval($item['sendnum']) : 0;
                $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['item_type'] = trim($item['item_type']) ? $item['item_type'] : 'product';
                
                //兼容淘宝订单的sale_amount 2015-04-29 By YW
                if($item['sale_amount']) $item['sale_price'] = $this->get_money($item['sale_amount']);
                
                //兼容ecstore的sale_price 2015-04-29 By YW
                if($item['sale_price']){
                    $item['sale_price'] = $this->get_money($item['sale_price']);
                    $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['amount'] = $item['sale_price'];
                    $this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['price'] = round($this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['amount']/$this->_orderSdf['order_objects'][$gKey]['order_items'][$iKey]['quantity'] ,2);
                }
            }
        }

        //商品数据冗余数据
        $this->_orderSdf['logistics_code'] = $logistics_code;
        $this->_orderSdf['logistics_company'] = $logistics_company;
        $this->_orderSdf['item_num'] = $item_num;
        $this->_orderSdf['skus'] = count($this->_orderSdf['order_objects']);

        $this->_orderSdf['is_refund'] = $this->_orderSdf['is_refund'];
        $this->_orderSdf['refund_order_bn'] = $this->_orderSdf['refund_order_bn'];
        $this->_orderSdf['consumer_terminal'] = $this->_orderSdf['consumer_terminal'];
        $this->_orderSdf['op_name'] = $this->_orderSdf['op_name'];
    }

    /**
     * 处理商品信息
     *
     * @param void
     * @return boolean
     */
    /*protected function processProductInfo() {
     $error_msg = '';
     $orderSkus = $this->getAllSkus();

     if (empty($orderSkus)) {
     $error_msg = '订单明细商品出错';
     return $error_msg;
     }

     //检查 商品 sku
     $goodsIds = $this->getGoodsIdBySku($orderSkus['goods']);
     //商品ID只做更新，不做强制检查，可没有对应的SKU
     foreach ($orderSkus['goods'] as $key => $sku) {
     if (isset($goodsIds[$sku])) {
     $this->_orderSdf['order_objects'][$key]['goods_id'] = $goodsIds[$sku];
     } else {
     $this->_orderSdf['order_objects'][$key]['goods_id'] = 0;
     }
     }
     //检查产品SKU
     $productIds = $this->getProdutIdBySku($orderSkus['items']);

     //产品需检查SKU,并做强制检查
     $result = array();
     foreach ($orderSkus['items'] as $key => $sku) {
     $idx = split('_', $key);
     if (isset($productIds[$sku])) {//在系统中找到商品
     $this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]['product_id'] = $productIds[$sku];
     } else {
     $productInfo = array();
     $a = kernel::single('ecorder_service_product')->autoCreateProduct($this->_shopInfo, $this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]);

     if (empty($productInfo)) {
     $this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]['product_id'] = 0;
     //$this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]['bn'] = '';
     $result[] = array('sId' => $this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]['shop_product_id'],
     'sku' => $this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]['bn']);
     } else {
     $this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]['product_id'] = $productInfo['product_id'];
     $this->_orderSdf['order_objects'][$idx[0]]['order_items'][$idx[1]]['bn'] = $productInfo['bn'];
     }
     }
     }

     return $error_msg;
     }*/

    /**
     * 获取产品指定sku的product_id
     *
     * @param mixed $skus SKU
     * @return Array
     */
    /*protected function getProdutIdBySku($skus) {

    if (empty($skus)) {
    return array();
    }

    if (!is_array($skus)) {
    $skus = array($skus);
    }

    $result = array();
    $goods = app::get(self::$__ORDER_APP)->model('products')->dump(array('outer_sku_id' => $skus), 'product_id,bn');
    if ($goods) {
    foreach ($goods as $row) {
    $result[$row['bn']] = $row['product_id'];
    }
    }

    return $result;
    }*/

    /**
     * 获取商品指定sku的goods_id
     *
     * @param mixed $skus SKU
     * @return Array
     */
    /*protected function getGoodsIdBySku($skus) {

    if (empty($skus)) {
    return array();
    }

    if (!is_array($skus)) {
    $skus = array($skus);
    }

    $result = array();
    $goods = app::get(self::$__ORDER_APP)->model('goods')->dump(array('outer_id' => $skus), 'goods_id,bn');
    if ($goods) {
    foreach ($goods as $row) {
    $result[$row['bn']] = $row['goods_id'];
    }
    }

    return $result;
    }*/

    /**
     * 获取订单中的所有 SKU
     *
     * @param void
     * @return Array
     */
    /*protected function getAllSkus() {

    if (is_array($this->_orderSdf['order_objects'])) {
    $skus = array('goods' => array(), 'items' => array());
    foreach ($this->_orderSdf['order_objects'] as $gKey => $object) {
    $skus['goods'][$gKey] = trim($object['num_iid']);
    foreach ($object['order_items'] as $iKey => $item) {
    $key = sprintf("%s_%s", $gKey, $iKey);
    $skus['items'][$key] = trim($item['sku_id']);
    }
    }
    return $skus;
    } else {
    return array();
    }
    }*/

    /**
     * 转换订单数据
     *
     * @param void
     * @return void
     */
    protected function convertSdfParams()
    {
        $ecorder_func = kernel::single('ecorder_func');
        if(!$this->_orderSdf['op_name']){
            $this->_orderSdf['op_name'] = 'matrix';
        }
        if( ! $this->_orderSdf['pmt_order']) $this->_orderSdf['pmt_order'] = 0;
        if( ! $this->_orderSdf['pmt_goods']) $this->_orderSdf['pmt_goods'] = 0;
        if( ! $this->_orderSdf['score_u']) $this->_orderSdf['score_u'] = 0;
        if( ! $this->_orderSdf['score_g']) $this->_orderSdf['score_g'] = 0;
        if( ! $this->_orderSdf['discount']) $this->_orderSdf['discount'] = 0;
        if( ! $this->_orderSdf['payed']) $this->_orderSdf['payed'] = 0;

        //erp导出的订单号包含等号等特殊字符 by yw
        $this->_orderSdf['order_bn'] = str_replace(array('=',"'",'"','“','”'), '', $this->_orderSdf['order_bn']);
        $this->_orderSdf['score_u'] = $this->_orderSdf['score_u'] ? $this->_orderSdf['score_u'] : 0;
        $this->_orderSdf['score_g'] = $this->_orderSdf['score_g'] ? $this->_orderSdf['score_g'] : 0;
        $this->_orderSdf['payed'] = $this->_orderSdf['payed'] ? $this->get_money($this->_orderSdf['payed']) : 0.00;
        $this->_orderSdf['shipping'] = json_decode($this->_orderSdf['shipping'], true);
        $this->_orderSdf['shipping']['cost_shipping'] = $this->_orderSdf['shipping']['cost_shipping'] ? floatval($this->_orderSdf['shipping']['cost_shipping']) : 0.00;
        $this->_orderSdf['shipping']['is_protect'] = $this->_orderSdf['shipping']['is_protect'] ? $this->_orderSdf['shipping']['is_protect'] : 'false';
        $this->_orderSdf['shipping']['cost_protect'] = $this->_orderSdf['shipping']['cost_protect'] ? $this->_orderSdf['shipping']['cost_protect'] : 0.00;
        $this->_orderSdf['shipping']['is_cod'] = $this->_orderSdf['shipping']['is_cod'] ? $this->_orderSdf['shipping']['is_cod'] : 'false';
        $this->_orderSdf['shop_id'] = $this->_shopInfo['shop_id'];
        $this->_orderSdf['shop_type'] = $this->_shopInfo['shop_type'];
        $this->_orderSdf['is_delivery'] = $this->_orderSdf['is_delivery'] ? $this->_orderSdf['is_delivery'] : 'N';
        $this->_orderSdf['cost_item'] = $this->_orderSdf['cost_item'] ? $this->get_money($this->_orderSdf['cost_item']) : 0.00;
        $this->_orderSdf['is_tax'] = $this->_orderSdf['is_tax'] ? $this->_orderSdf['is_tax'] : 'false';
        $this->_orderSdf['cost_tax'] = $this->_orderSdf['cost_tax'] ? $this->_orderSdf['cost_tax'] : 0.00;
        $this->_orderSdf['discount'] = $this->_orderSdf['discount'] ? $this->_orderSdf['discount'] : 0.00;
        $this->_orderSdf['total_amount'] = $this->_orderSdf['total_amount'] ? $this->get_money($this->_orderSdf['total_amount']) : 0.00;
        $this->_orderSdf['cur_amount'] = $this->_orderSdf['cur_amount'] ? $this->get_money($this->_orderSdf['cur_amount']) : $this->_orderSdf['total_amount'];
        $this->_orderSdf['source'] = $this->_orderSdf['source'] ? $this->_orderSdf['source'] : 'matrix';
        $this->_orderSdf['createtime'] = $ecorder_func->date2time($this->_orderSdf['createtime']);
        $this->_orderSdf['download_time'] = time();
        $this->_orderSdf['finish_time'] = $ecorder_func->date2time($this->_orderSdf['finish_time']);
        $this->_orderSdf['order_limit_time'] = $ecorder_func->date2time($this->_orderSdf['order_limit_time']);
        $this->_orderSdf['consignee'] = json_decode($this->_orderSdf['consignee'], true);
        $this->_orderSdf['consignee']['area_state'] = trim($this->_orderSdf['consignee']['area_state']);
        $this->_orderSdf['consignee']['area_city'] = trim($this->_orderSdf['consignee']['area_city']);
        $this->_orderSdf['consignee']['area_district'] = trim($this->_orderSdf['consignee']['area_district']);
        $area = $this->_orderSdf['consignee']['area_state'] . '/' . $this->_orderSdf['consignee']['area_city'] . '/' . $this->_orderSdf['consignee']['area_district'];
        $ecorder_func->region_validate($area);
        $area = str_replace('::', '', $area);
        $this->_orderSdf['consignee']['area'] = $area;

        $arr_area = explode(':', $area);
        $arr_area = explode('/', $arr_area[1]);

        $this->_orderSdf['consignee']['area_state'] = 0;
        $this->_orderSdf['state_id'] = 0;
        if(!empty($arr_area[0])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$arr_area[0].'"');
            $this->_orderSdf['state_id'] = $row['region_id'];
            $this->_orderSdf['consignee']['area_state'] = $row['region_id'];
            $this->_orderSdf['consignee']['state'] = $row['region_id'];;
        }

        $this->_orderSdf['consignee']['area_city'] = 0;
        if(!empty($arr_area[1])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$arr_area[1].'"');
            $this->_orderSdf['city_id'] = $row['region_id'];
            $this->_orderSdf['consignee']['area_city'] = $row['region_id'];
            $this->_orderSdf['consignee']['city'] = $row['region_id'];
        }

        $this->_orderSdf['consignee']['area_district'] = 0;
        if(!empty($arr_area[2])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$arr_area[2].'"');
            $this->_orderSdf['district_id'] = $row['region_id'];
            $this->_orderSdf['consignee']['area_district'] = $row['region_id'];
            $this->_orderSdf['consignee']['district'] = $row['region_id'];
        }

        //如果手机号为空，使用电话号码
        if(!$this->_orderSdf['consignee']['mobile'] 
            && $this->_orderSdf['consignee']['telephone']
            && strlen($this->_orderSdf['consignee']['telephone'])==11
        ){
            $this->_orderSdf['consignee']['mobile'] = $this->_orderSdf['consignee']['telephone'];
        }

        //订单没有客户信息，使用收货人信息
        $this->_orderSdf['member_info'] = json_decode($this->_orderSdf['member_info'],true);
        $member_info = $this->_orderSdf['member_info'];
        $member_info = kernel::single('ecorder_func')->clear_value($member_info);
        $this->_orderSdf['member_info'] = array_merge($this->_orderSdf['consignee'], $member_info);
        
        //特别处理姓名字段
        if($this->_orderSdf['consignee']['name']){
            $this->_orderSdf['member_info']['name'] = $this->_orderSdf['consignee']['name'];
        }
        
        if( ! $this->_orderSdf['member_info']['tel']){
        $this->_orderSdf['member_info']['tel'] = $this->_orderSdf['telephone'];
        }

        if(!$this->_orderSdf['member_info']['email'] && strstr($member_info['alipay_no'],'@')){
            $this->_orderSdf['member_info']['email'] = $member_info['alipay_no'];
        }

        //特别处理ecstore订单的会员ID
        if(!$this->_orderSdf['member_info']['member_id'] && $this->_orderSdf['buyer_id']){
            $this->_orderSdf['member_info']['member_id'] = $this->_orderSdf['buyer_id'];
        }

        $this->_orderSdf['member_info']['source_terminal'] =  $this->_orderSdf['consumer_terminal'];
        $this->_orderSdf['member_info']['zipcode'] =  $this->_orderSdf['consignee']['zip'];
         
        $this->_orderSdf['payinfo'] = json_decode($this->_orderSdf['payinfo'], true);
        $this->_orderSdf['product_hash'] = $this->getProductHash();
        $this->_orderSdf['order_objects'] = json_decode($this->_orderSdf['order_objects'], true);

        //自动填充修改时间,20150115-矩阵的bug处理后可删除
        if(!$this->_orderSdf['modified']) $this->_orderSdf['modified'] = time();

        //增加前端更新时间
        $this->_orderSdf['f_modified'] = $ecorder_func->date2time($this->_orderSdf['modified']);
        
        //自动填充发货时间
        if(!$this->_orderSdf['delivery_time'] && $this->_oldOrderInfo['delivery_time']){
            $this->_orderSdf['delivery_time'] = $this->_oldOrderInfo['delivery_time'];
        }
        
        if($this->_orderSdf['ship_status'] == 1 && !$this->_orderSdf['delivery_time']){
            $this->_orderSdf['delivery_time'] = time();
        }
        
        //自动填充完成时间 finish_time
        if($this->_orderSdf['status'] == 'finish' && !$this->_orderSdf['finish_time']){
            $this->_orderSdf['finish_time'] = time();
        }

        //处理京东订单的状态,20150115-矩阵的bug处理后可删除
        if($this->_orderSdf['order_source']=='360buy' && $this->_orderSdf['finish_time']>978278400 && $this->_orderSdf['pay_status']==1){
            $this->_orderSdf['status'] = 'finish';
        }

        //处理货到付款订单
        if($this->_orderSdf['shipping']['is_cod'] != 'false' && $this->_orderSdf['status'] == 'finish' 
            && $this->_orderSdf['ship_status'] == 1){
            //已发货已完成的COD订单，自动填充付款状态，时间，金额
            $this->_orderSdf['pay_status'] = 1;
            $this->_orderSdf['pay_time'] = time();
            if($this->_orderSdf['payed'] == 0){
                $this->_orderSdf['payed'] = $this->_orderSdf['final_amount'] ? $this->get_money(++$this->_orderSdf['final_amount']) : $this->_orderSdf['total_amount'];
            }
        }

        $this->_orderSdf['delivery_time'] = $ecorder_func->date2time($this->_orderSdf['delivery_time']);
        $this->_orderSdf['f_ship_time'] = $this->_orderSdf['delivery_time'];
    }

    /**
     * 获取用于检验的产品信息
     *
     * @param void
     * @return String
     */
    protected function getProductHash()
    {
        return md5($this->_orderSdf['order_objects']);
    }

    /**
     * 通过订单编号和SHOPID获取订单内容
     *
     * @param string $orderBN 订单编号
     * @param string $shopId 店铺ID
     * @return mixed
     */
    protected function fetchOldOrderInfo($orderBN, $shopId)
    {
        //设置默认操作正式订单环境
        $this->_isLogOrder = false;

        //检查正式订单表
        $this->_oldOrderInfo = app::get(self::$__ORDER_APP)->model('orders')->dump(array('order_bn' => $orderBN, 'shop_id' => $shopId), 'order_id,order_bn,mark_text,custom_mark,f_modified,pay_status,product_hash,member_id,pay_time,delivery_time');
        if(empty($this->_oldOrderInfo)){
            $this->_isLogOrder = true;
        }
    }

    /**
     * 设置接收数据
     *
     * @param mixed $sdf 标准订单数据结构
     * @return void
     */
    protected function parseSdf($sdf)
    {
        //分别提出支付、优惠等信息
        $this->_orderPmt = $sdf['pmt_detail'];
        //$this->_orderPayment = $sdf['payment_detail'];

        //处理易开店的支付信息
        if(isset($sdf['payment_detail'])){
            $this->_orderPayment = $sdf['payment_detail'];
        }elseif(isset($sdf['payments'])){
            $this->_orderPayment = json_decode($sdf['payments'], true);
            $this->_orderPayment = json_encode($this->_orderPayment[0]);
        }
        
        //处理ecstore的发货信息
        if(isset($sdf['shipping'])){
            $shipping = json_decode($sdf['shipping'], true);
            if($shipping){
                $company_name = explode(',', $shipping['company_name']);
                $logistics_no = explode(',', $shipping['logistics_no']);
                $consign_time = explode(',', $shipping['consign_time']);
                
                //以最后一个发货信息为准
                if($consign_time[0]){
                    $sdf['delivery_time'] = end($consign_time);
                    $sdf['logistics_code'] = end($logistics_no);
                    $sdf['logistics_company'] = end($company_name);
                }
            }
        }

        unset($sdf['payment_detail']);
        //unset($sdf['order_source']);
        unset($sdf['pmt_detail']);
        $this->_orderSdf = $sdf;
        $this->_result = array('tid' => $sdf['order_bn']);
    }

    //格式化金额参数
    private function get_money($str)
    {
        return floatval(str_replace('￥','',$str));
    }

    /**
     * 获取店铺信息
     *
     * @param void
     * @return array
     */
    protected function fetchShopInfo() {

        $nodeId = base_rpc_service::$node_id;
        return app::get(self::$__ORDER_APP)->model('shop')->dump(array('node_id' => $nodeId), '*');
    }

    /**
     * 记录API错误日志并返回错误信息
     *
     * @param string $msg 错误信息
     * @param string $addon 附加信息
     * @return void
     */
    protected function throwError($msg, $title='', $addon='') {
        if (title == '') {
            $title = sprintf("接收店铺(%s)的订单: %s", $this->_shopInfo['name'], $this->_orderSdf['order_bn']);
        }
        $logId = app::get(self::$__ORDER_APP)->model('api_log')->gen_id();
        app::get(self::$__ORDER_APP)->model('api_log')->write_log($logId, $title, __CLASS__, 'add', '', '', 'response', 'fail', $msg, $addon);
        if (!defined('COMMAND_MODE')) {
            $this->_response->send_user_error(app::get(self::$__ORDER_APP)->_($msg), $this->_result);
        } else {
            return $msg;
        }
    }

}
