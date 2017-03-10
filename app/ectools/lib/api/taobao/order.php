<?php

/*
 * 订单API调用
 *
 * @author hzjsq
 * @version 0.1
 */

class ectools_api_taobao_order extends ectools_api_taobao_request implements ectools_api_interface {
    /**
     * 通过API接口要获取的订单内容
     *
     * @var String
     */
    const FIELDS = 'seller_nick, buyer_nick, title, type, created, sid, tid, seller_rate,buyer_flag, buyer_rate, status, payment, adjust_fee, post_fee, total_fee, pay_time, end_time, modified, consign_time, buyer_obtain_point_fee, point_fee, real_point_fee, received_payment, commission_fee, buyer_memo, seller_memo, alipay_no, buyer_message, pic_path, num_iid, num, price, buyer_alipay_no, receiver_name, receiver_state, receiver_city, receiver_district, receiver_address, receiver_zip, receiver_mobile, receiver_phone, buyer_email,seller_flag, seller_alipay_no, seller_mobile, seller_phone, seller_name, seller_email, available_confirm_fee, has_post_fee, timeout_action_time, snapshot_url, cod_fee, cod_status, shipping_type, trade_memo, orders, promotion_details, invoice_name, step_trade_status, step_paid_fee';

    /**
     * 定义API每页获取的订单数
     *
     * @var Integer
     */
    const PAGESIZE = '1';

    /**
     * 开始日期
     *
     * @var String
     */
    private $aliStartDay = '';
    /**
     * 结束日期
     *
     * @var String
     */
    private $aliEndDay = '';

    /**
     * 获取指定时间内所有旺旺的记录
     *
     * @param Integer $startTime 开始时间
     * @param Integer $endTime 结束时间
     * @return Array
     */
    public function & fetch($startTime, $endTime,$flag = false,$shop_id='') {

        if ($startTime + 86400*7 < $endTime) {

            $startTime = $endTime - 86400*7;
        }

        $orders = array();
        while( $startTime < $endTime ) {

            $eTime = $startTime + 86400;
            $temp = $this->fetchOrders($startTime, $eTime,$shop_id);
            if($flag){
                echo date('Y-m-d H:i:s', $startTime) . ' <-> ' . date('Y-m-d H:i:s', $eTime) . ' = '  . count($temp) . "\n";
            }
            foreach ($temp as $tid) {

                if (!in_array($tid, $orders)) {

                    $orders[] = $tid;
                }
            }
            $startTime = $startTime + 86400;
        }

        return $orders;
    }

    /**
     * 获取指定时间内所有旺旺的记录
     *
     * @param Integer $startTime 开始时间
     * @param Integer $endTime 结束时间
     * @return Array
     */
    private function fetchOrders($startTime, $endTime,$shop_id ='') {

        $this->aliStartDay = date('Y-m-d H:i:s', $startTime);
        $this->aliEndDay = date('Y-m-d H:i:s', $endTime);

        //获取 sessionKey , 如取不到，则直接返加空数组
        $this->sessionKey = $this->getSessionKey();
        if (empty($this->sessionKey)) {

            return array();
        }

        //$orders = $this->getIncrementOrders();
		$orders = $this->getOrders($shop_id);
        return $orders;
    }

    public function getOrdersByPage($startTime,$endTime,$page=1,$page_size=1,$shop_id='') {
        $result = array();
        $this->aliStartDay = date('Y-m-d H:i:s', $startTime);
        $this->aliEndDay = date('Y-m-d H:i:s', $endTime);

        //获取 sessionKey , 如取不到，则直接返加空数组
        $this->sessionKey = $this->getSessionKey();
        if (empty($this->sessionKey)) {

            return 'seesion is null';
        }

        $method = 'taobao.trades.sold.get';
        $params = array(
            'fields' => 'tid', //self::FIELDS,
            'page_size' => $page_size,
            'start_created' => $this->aliStartDay,
            'end_created' => $this->aliEndDay
        );


        $curParams = $params;
        $curParams['page_no'] = $page;

        $apiResult = $this->apiRequest($method, $curParams,$shop_id);
        if (is_array($apiResult) && !isset($apiResult['error_response']) && !empty($apiResult['trades_sold_get_response']['trades']['trade'])) {
            $totalNum = $apiResult['trades_sold_get_response']['total_results'];
            $orders = array();
            if(isset($apiResult['trades_sold_get_response']['trades']['trade']['tid'])){
                $orders[] = $apiResult['trades_sold_get_response']['trades']['trade']['tid'];
            }else{
                foreach ($apiResult['trades_sold_get_response']['trades']['trade'] as $value) {
                    if (!in_array($value['tid'], $orders))
                    $orders[] = $value['tid'];
                }
            }

            $result = array('totalNum'=>$totalNum,'orders'=>$orders,'status'=>'succ');
        }elseif(isset($apiResult['error_response']) && $apiResult['error_response']['sub_code'] =='isp.remote-service-timeout'){
            $result = array('status'=>'timeout');
        }elseif(empty($apiResult)){
            $result = array('status'=>'timeout');
        }

        return $result;
    }

    /**
     * 获取批量指定订单编号的详细信息
     *
     * @param Array $orders 要处理的订单编号
     * @return Array
     */
    private function fetchFullInfo($orders,$shop_id='') {

        $result = array();
        if (is_array($orders) && !empty($orders)) {

            foreach ($orders as $key => $tid) {

                $fullInfo = $this->getFullTradeInfo($tid,$shop_id='');
                if (!empty($fullInfo)) {

                    if (isset($fullInfo['orders']['order']['adjust_fee'])) {

                        $fullInfo['orders']['order'] = array($fullInfo['orders']['order']);
                    }

                    $result[] = $this->transOrderDetail($fullInfo);
                }
            }
        }

        return $result;
    }

    /**
     * 获取指定订单的信息
     *
     * @param 订单ID $tid
     * @return Array
     */
    function getFullTrade($tid,$shop_id='') {

        $fullInfo = $this->getFullTradeInfo($tid,$shop_id);
        if (!empty($fullInfo)) {

            if (isset($fullInfo['orders']['order']['adjust_fee'])) {

                $fullInfo['orders']['order'] = array($fullInfo['orders']['order']);
                 
            }

            return  $this->transOrderDetail($fullInfo);
        } else {

            return array();
        }
    }

    /**
     * 获取一指定订单的详细信息
     *
     * @param String $tid 订单ID
     * @return Array
     */
    private function getFullTradeInfo($tid,$shop_id='') {

        $method = 'taobao.trade.fullinfo.get';
        $params = array(
            'fields' => self::FIELDS,
            'tid' => $tid,
        );

        $apiResult = $this->apiRequest($method, $params,$shop_id);

        if (is_array($apiResult) && !isset($apiResult['error_response']) && !empty($apiResult['trade_fullinfo_get_response']['trade'])) {

            return $apiResult['trade_fullinfo_get_response']['trade'];
        } else {

            return array();
        }
    }

    /**
     * 获取当前会话用户作为卖家已卖出的交易数据,非增量，用于获取指定时间段内创建的订单
     *
     * @param void
     * @return Array
     */
    private function getOrders($shop_id ='') {

        $method = 'taobao.trades.sold.get';
        $params = array(
            'fields' => 'tid', //self::FIELDS,
            'type' => 'fixed,auction,hotel_trade,auto_delivery,ec,cod,b2c_cod,step,eticket,tmall_i18n',
            'page_size' => self::PAGESIZE,
            'start_created' => $this->aliStartDay,
            'end_created' => $this->aliEndDay
        );

        //最少有一页
        $maxPageNum = 1;
        $orders = array();
        for ($page = 1; $page <= $maxPageNum; $page++) {

            $curParams = $params;
            $curParams['page_no'] = $page;

            $apiResult = $this->apiRequest($method, $curParams,$shop_id);var_dump($apiResult);exit;
            if (is_array($apiResult) && !isset($apiResult['error_response']) && !empty($apiResult['trades_sold_get_response']['trades']['trade'])) {

                if ($page == 1) {
                    //如果是第一页，重设$maxPageNum
                    $totalNum = $apiResult['trades_sold_get_response']['total_results'];
                    $maxPageNum = ceil($totalNum / self::PAGESIZE);
                }

                if(!isset($apiResult['trades_sold_get_response']['trades']['trade']['tid'])){
                    foreach ($apiResult['trades_sold_get_response']['trades']['trade'] as $value) {

                        $orders[] = $value['tid'];
                    }
                }else{
                    $orders[] = $apiResult['trades_sold_get_response']['trades']['trade']['tid'];
                }
            }
        }

        return $orders;
    }
    
    private function getLogisticsOrders($tid,$shop_id='')
    {
        $method = 'taobao.logistics.orders.get';
        $params = array(
            'tid' => $tid,
            'fields' => 'tid,seller_nick,buyer_nick,out_sid,status,company_name,sub_tids',
            'page_no' => 1,
            'page_size' => 1
        );

        $apiResult = $this->apiRequest($method, $params,$shop_id);
        if (is_array($apiResult) && !isset($apiResult['error_response']) && !empty($apiResult['logistics_orders_get_response']['shippings']['shipping'])) {

            if(isset($apiResult['logistics_orders_get_response']['shippings']['shipping']['tid'])){
                $logistics = $apiResult['logistics_orders_get_response']['shippings']['shipping'];
            }else{
                foreach ($apiResult['logistics_orders_get_response']['shippings']['shipping'] as $value){

                    $logistics = $value;
                }
            }
        }

        return $logistics;
    }

    /**
     * 获取当前会话用户作为卖家已卖出的交易数据,增量方式，用于获取指定时间段内发生状态变化的订单
     *
     * @param void
     * @return Array
     */
    private function getIncrementOrders($shop_id='') {

        $method = 'taobao.trades.sold.increment.get';
        $params = array(
            'fields' => 'tid', //self::FIELDS,
            'type' => 'fixed,auction,hotel_trade,auto_delivery,ec,cod,b2c_cod,step,eticket,tmall_i18n',
            'page_size' => self::PAGESIZE,
            'start_modified' => $this->aliStartDay,
            'end_modified' => $this->aliEndDay
        );

        //最少有一页
        $maxPageNum = 1;
        $orders = array();
        for ($page = 1; $page <= $maxPageNum; $page++) {

            $curParams = $params;
            $curParams['page_no'] = $page;

            $apiResult = $this->apiRequest($method, $curParams,$shop_id);

            if (is_array($apiResult) && !isset($apiResult['error_response']) && !empty($apiResult['trades_sold_increment_get_response']['trades']['trade'])) {

                if ($page == 1) {
                    //如果是第一页，重设$maxPageNum
                    $totalNum = $apiResult['trades_sold_increment_get_response']['total_results'];
                    $maxPageNum = ceil($totalNum / self::PAGESIZE);
                }

                if(!isset($apiResult['trades_sold_increment_get_response']['trades']['trade']['tid'])){
                    foreach ($apiResult['trades_sold_increment_get_response']['trades']['trade'] as $value) {

                        if (!in_array($value['tid'], $orders))
                        $orders[] = $value['tid'];
                    }
                }else{
                    if (!in_array($apiResult['trades_sold_increment_get_response']['trades']['trade']['tid'], $orders))
                    $orders[] = $apiResult['trades_sold_increment_get_response']['trades']['trade']['tid'];
                }
            }
        }

        return $orders;
    }

    /**
     * 对taobao的订单数据进行转换
     *
     * @param Array $order 订单信息
     * @return Array
     */
    public function transOrderDetail($order) {

        //获取已支付金额
        if (in_array($order['status'], array('WAIT_SELLER_SEND_GOODS', 'WAIT_BUYER_CONFIRM_GOODS', 'TRADE_BUYER_SIGNED', 'TRADE_FINISHED'))) {
            $payed = $order['payment'];
        } else {
            $payed = '0.0';
        }

        //处理优惠信息
        $pmt_detail = array();
        if (isset($order['promotion_details'])) {
            if (isset($order['promotion_details']['promotion_detail']['promotion_name'])) {
                $order['promotion_details']['promotion_detail'] = array($order['promotion_details']['promotion_detail']);
            }
            foreach ($order['promotion_details']['promotion_detail'] as $pmt) {
                $pmt_detail[] = array(
                    'pmt_desc' => $pmt['promotion_desc'],
                    'promotion_id' => $pmt['promotion_id'],
                    'pmt_id' => $pmt['id'],                    
                    'pmt_amount' => $pmt['discount_fee'], 
                    'pmt_describe' => $pmt['promotion_name']
                );
            }
        } else {
            $pmt_detail[] = array('pmt_amount' => '', 'pmt_describe' => '');
        }

        //最后修改时间
        $modified = isset($order['modified']) ? $order['modified'] : $order['created'];
        $adjust_fee = 0.0;
        $pmt_order = 0.0;
        $num_iids = array();
        foreach ($order['orders']['order'] as $item) {
            $adjust_fee = $adjust_fee + floatval($item['adjust_fee']);
            $pmt_order = $pmt_order + floatval($item['discount_fee']);
            $num_iids[] = $item['num_iid'];
        }
        
        if($order['status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
            //$logistics = $this->getLogisticsOrders($order['tid']);
        }

        $omeOrder = array(
            'source' => 'manual',
            'order_source' => 'taobao',
            'order_bn' => strval($order['tid']),
            'memeber_id' => $order['seller_nick'] ? $order['seller_nick'] : '',
            'status' => $this->_status($order['status']),
            'pay_status' => $this->_payStatus($order['status']),
            'ship_status' => $this->_shipStatus($order['status']),
            'is_delivery' => '',
            'shipping' => json_encode(array(
                'shipping_name' => $this->_shipName($order['shipping_type']),
                'cost_shipping' => $order['post_fee'] ? $order['post_fee'] : '' ,
                'is_protect' => '',
                'cost_protect' => 0.0,
                'is_cod' => ($order['type']=='cod') ? 'true' : 'false',
                )),
            'member_info' => json_encode(array(
                'uname' => $order['buyer_nick'] ? $order['buyer_nick'] : '',
                'name' => '',
                'area_state' => '', 
                'area_city' => '',
                'area_district' => '',
                'alipay_no' => $order['buyer_alipay_no'] ? $order['buyer_alipay_no'] : '',
                'addr' => '',
                'mobile' => '',
                'tel' => '',
                'email' => $order['buyer_email'] ? $order['buyer_email'] : '',
                'zip' => ''
                )),
            'payinfo' => json_encode(array(
                'pay_name' => '支付宝',
                'cost_payment' => 0.0
                )),
            'weight' => '',
            'title' => $order['title'] ? $order['title'] : '',
            'itemnum' => $order['num'] ? $order['num'] : '',
            'modified' => strtotime($modified),
            'createtime' => strtotime($order['created']),
            'ip' => '',
            'consignee' => json_encode(array(
                'name' => $order['receiver_name'] ? $order['receiver_name'] : '',
                'area_state' => $order['receiver_state'] ? $order['receiver_state'] : '',
                'area_city' => $order['receiver_city'] ? $order['receiver_city'] :'',
                'area_district' => $order['receiver_district'] ? $order['receiver_district'] : '',
                'addr' => $order['receiver_address'] ? $order['receiver_address'] : '',
                'zip' => $order['receiver_zip'] ? $order['receiver_zip'] : '',
                'telephone' => $order['receiver_phone'] ? $order['receiver_phone'] : '',
                'email' => '',
                'r_time' => '',
                'mobile' => $order['receiver_mobile'] ? $order['receiver_mobile'] : ''
                )),
            'payment_detail' => json_encode(array(
                'pay_account' => $order['buyer_alipay_no'] ?  $order['buyer_alipay_no'] : '',
                'currency' => 'CNY',
                'paymethod' => '支付宝',
                'pay_time' => strtotime($order['pay_time'] ? $order['pay_time'] : ''),
                'trade_no' => $order['alipay_no'] ? $order['alipay_no'] : ''
                )),
            'pmt_detail' => json_encode($pmt_detail),
            'cost_item' => $order['total_fee'] ? $order['total_fee'] : '',
            'is_tax' => ($order['invoice_name'] == '') ? 'false' : 'true',
            'cost_tax' => '0.00',
            'tax_title' => $order['invoice_name'] ? $order['invoice_name'] : '',
            'currency' => 'CNY',
            'cur_rate' => 1.0,
            'score_u' => $order['point_fee'] ? $order['point_fee'] : '0',
            'scort_g' => $order['buyer_obtain_point_fee'] ? $order['buyer_obtain_point_fee'] : '0',
            'discount' => abs(floatval($adjust_fee)),
            'pmt_goods' => '0',
            'pmt_order' => abs(floatval($pmt_order)),
            'total_amount' => $order['payment'],
            'cut_amount' => $order['payment'] ? $order['payment'] : '',
            'payed' => $payed,
            'custom_mark' => $order['buyer_message'] ? $order['buyer_message'] : '',
            'mark_text' => $order['seller_memo'] ? $order['seller_memo'] : '',
            'mark_type' => $order['seller_flag'] ? $order['seller_flag'] : '',
            'tax_no' => '',
            'order_limit_time' => strtotime($order['timeout_action_time']),
            'coupons_name' => '',
            'order_objects' => '',
            'trade_type' => $order['type'],
            'step_trade_status' => $order['step_trade_status'],
            'step_paid_fee' => $order['step_paid_fee'],
            'delivery_time' => $order['consign_time'] ? strtotime($order['consign_time']):'',
            'ship_time' => $order['consign_time'] ? strtotime($order['consign_time']):'',
            'finish_time' => $order['end_time'] ? strtotime($order['end_time']):'',
            //'logistics_company' => ($logistics['company_name']),
            //'logistics_code' => ($logistics['out_sid']),
        );

        //增加具体商品
        foreach ($order['orders']['order'] as $item) {
        
            //物流信息
            if($item['logistics_company'] or $item['invoice_no']){
                $omeOrder['logistics_company'] = $item['logistics_company'];
                $omeOrder['logistics_code'] = $item['invoice_no'];
            }
        
            $order_objects[] = array(
                'oid' => $item['oid'],
                'logistics_company' => $item['logistics_company'],
                'logistics_code' => $item['invoice_no'],
                'obj_type' => 'goods',
                'obj_alias' => '商品',
                'shop_goods_id' => $item['num_iid'],
                'bn' => ($item['outer_sku_id'] == '') ? $item['outer_iid'] : $item['outer_sku_id'],
                'name' => $item['title'],
                'price' => $item['price'],
                'quantity' => $item['num'],
                'amount' => $item['total_fee'],
                'weight' => '',
                'score' => '',
                'order_items' => array(array(
                    'shop_product_id' => $item['sku_id'],
                    'shop_goods_id' => $item['num_iid'],
                    'item_type' => 'product',
                    'bn' => ($item['outer_sku_id'] == '') ? $item['outer_iid'] : $item['outer_sku_id'],
                    'name' => $item['title'],
                    'product_attr' => $this->_getProductAttr($item['sku_properties_name']),
                    'cost' => $item['price'],
                    'quantity' => $item['num'],
                    'sendnum' => 0,
                    'amount' => $item['total_fee'],
                    'price' => $item['price'],
                    'weight' => '',
                    'status' => $this->_productStatus($item['status']),
                    'score' => 0,
                    'create_time' => strtotime($order['created']),
                ))
            );
        }
        //		if($$order_objects && !empty($order_objects))
        $omeOrder['order_objects']=json_encode($order_objects);
        return $omeOrder;
    }
    
    //淘宝接口 taobao.logistics.trace.search 物流流转信息查询 
    public function getLogisticsTrace($seller_nick, $tid,$shop_id = '')
    {
        if(!$tid) return false;
        if(!$seller_nick) return false;
        
        $method = 'taobao.logistics.trace.search';
        $params = array(
            'seller_nick' => $seller_nick,
            'tid' => $tid,
        );

        $apiResult = $this->apiRequest($method, $params,$shop_id);

        if (is_array($apiResult) && !isset($apiResult['error_response']) ) {
            return $apiResult['logistics_trace_search_response']['trace_list']['transit_step_info'];
        } else {
            return array();
        }
    }

    /**
     * 转换产品状态
     *
     * @param String $status taobao状态码
     * @return String
     */
    private function _productStatus($status) {

        if (in_array($status, array('TRADE_CLOSED_BY_TAOBAO')))
        return 'close';
        else
        return 'active';
    }

    /**
     * 转换taobao的产品属性格式为数组
     *
     * @param String $properties 产品属性
     * @return Array
     */
    private function _getProductAttr($properties) {

        if (empty($properties)) {

            return '';
        } else {
            $result = array();
            $prop = array();
            if (strpos($properties, ';') !== false) {
                $prop = explode(';', $properties);
            } else {
                $prop[] = $properties;
            }

            foreach ($prop as $val) {
                if (!empty($val)) {

                    $tmp = explode(':', $val);
                    $result[] = array('label' => $tmp[0], 'value' => $tmp[1]);
                }
            }

            return $result;
        }
    }

    /**
     * 获取发货方式
     *
     * @param String $type taobao 类型
     * @return String
     */
    private function _shipName($type) {

        if ($type == 'free')
        return '卖家包邮';
        if ($type == 'post')
        return '平邮';
        if ($type == 'express')
        return '快递';
        if ($type == 'ems')
        return 'EMS';
    }

    /**
     * 获取发货状态
     *
     * @param String $status taobao订单状态
     * @return String
     */
    private function _shipStatus($status) {

        if (in_array($status, array('WAIT_BUYER_CONFIRM_GOODS', 'TRADE_BUYER_SIGNED', 'TRADE_FINISHED')))
        return 1;
        else
        return 0;
    }

    /**
     * 获取支付状态
     *
     * @param String $status taobao订单状态
     * @return String
     */
    private function _payStatus($status) {

        if (in_array($status, array('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'TRADE_CLOSED_BY_TAOBAO', 'TRADE_CLOSED', 'ALL_CLOSED')))
        return 0;
        else
        return 1;
    }

    /**
     * 转换taobao订单状态为OME订单状态
     *
     * @param String $status taobao订单状态
     * @return String
     */
    private function _status($status) {

        if (in_array($status, array('WAIT_SELLER_SEND_GOODS', 'TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_BUYER_CONFIRM_GOODS', 'TRADE_BUYER_SIGNED'))) {
            $data = 'active';
        } else if (in_array($status, array('TRADE_CLOSED_BY_TAOBAO', 'TRADE_CLOSED', 'ALL_CLOSED'))) {
            $data = 'dead';
        } else if (in_array($status, array('TRADE_FINISHED'))) {
            $data = 'finish';
        }
        return $data;
    }
}
