<?php

/**
 * 前端店铺支付业务处理接口
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_response_payment extends ecorder_rpc_response {

    /**
     * 添加支付单
     * @access public
     * @param array $payment_sdf 付款单标准结构数据
     * @param object $responseObj 框架API接口实例化对象
     * @return array('payment_id'=>'付款单主键ID')
     */
    public function add($payment_sdf, &$responseObj) {
        $shop_id = $this->get_shop_id($responseObj);
        $status = $payment_sdf['status'];
        $payment_money = $payment_sdf['money'];
        $payment_bn = $payment_sdf['payment_bn'];
        $order_bn = $payment_sdf['order_bn'];
        $source_shop = $payment_sdf['source_shop'];

        //返回值
        $return_value = array('tid' => $order_bn, 'payment_id' => $payment_bn);

        $paymentObj = &app::get('ecorder')->model('payments');
        $orderObj = &app::get('ecorder')->model('orders');
        $shopObj = &app::get('ecorder')->model('shop');
        $oApi_log = &app::get('ecorder')->model('api_log');
        $order_detail = $orderObj->dump(array('shop_id' => $shop_id, 'order_bn' => $order_bn), 'pay_status,status,process_status,order_id,payed,cost_payment,total_amount');
        $shop_detail = $shopObj->dump($shop_id, 'name');
        $shop_name = $shop_detail['name'];

        //前端店铺发起新建支付单
        if (!$source_shop) {
            //判断支付单号是否为空
            if (empty($payment_bn)) {
                $msg = 'payment_bn not allow empty ';
                $responseObj->send_user_error(app::get('base')->_($msg), $return_value);
            }
            //判断订单是否存在
            if (empty($order_detail['order_id'])) {
                $msg = 'order_bn incorrect ';
                $responseObj->send_user_error(app::get('base')->_($msg), $return_value);
            }
            //状态值判断
            if (empty($status)) {
                $responseObj->send_user_error(app::get('base')->_('Status field value ' . $status . ' is not correct'), '');
            }
            //判断支付单是否已经存在
            if ($paymentObj->dump(array('payment_bn' => $payment_sdf['payment_bn'], 'shop_id' => $shop_id))) {
                return $return_value;
            }
            //支付金额判断
            if ($payment_money <= 0) {
                $responseObj->send_user_error(app::get('base')->_('Money field value is not correct'), $return_value);
            }
            //当前支付金额+已支付金额  > 订单总金额
            if ($payment_money + $order_detail['payed'] + $order_detail['cost_payment'] > $order_detail['total_amount']) {
                //日志记录
                $api_filter = array('marking_value' => $payment_sdf['payment_bn'], 'marking_type' => 'payment_money');
                $api_detail = $oApi_log->dump($api_filter, 'log_id');
                if (empty($api_detail['log_id'])) {
                    $msg = '支付金额+已支付金额  > 订单总金额';
                    $log_title = '店铺(' . $shop_name . ')添加支付单:' . $payment_sdf['payment_bn'] . ',' . $msg;
                    $addon = $api_filter;
                    $log_id = $oApi_log->gen_id();
                    $oApi_log->write_log($log_id, $log_title, __CLASS__, __FUNCTION__, '', '', 'response', 'fail', $msg, $addon);
                }
                $responseObj->send_user_error(app::get('base')->_('payment money abnormal'), $return_value);
            }
        } else {
            //自身发起新建支付单
            $payment_bn = $paymentObj->gen_id();
        }
        if ($payment_bn != '' and $order_bn != '') {

            $shop_id = $this->get_shop_id($responseObj);

            //判断订单是否:部分退款\全部退款\全部支付
            if (in_array($order_detail['pay_status'], array('1', '4', '5'))) {
                //日志记录
                $api_filter = array('marking_value' => $payment_bn . $order_detail['pay_status'], 'marking_type' => 'payment_pay_status');
                $api_detail = $oApi_log->dump($api_filter, 'log_id');
				
                if (empty($api_detail['log_id'])) {
                    $msg = $order_detail['order_bn'] . '订单已部分退款/全部退款/全部支付,无法支付';
                    $log_title = '店铺(' . $shop_name . ')' . $msg;
                    $addon = $api_filter;
                    $log_id = $oApi_log->gen_id();
                    $oApi_log->write_log($log_id, $log_title, __CLASS__, __FUNCTION__, '', '', 'response', 'fail', $msg, $addon);
                }
                $responseObj->send_user_error(app::get('base')->_('Order status: ' . $order_detail['pay_status'] . ',can not pay'), $return_value);
            }
			
            //判断订单状态是否为活动订单
            if ($order_detail['status'] != 'active') {
                $responseObj->send_user_error(app::get('base')->_('Order status is not active,can not pay'), $return_value);
            }
            //判断订单确认状态
           if ($order_detail['process_status'] == 'cancel') {
                //日志记录
                $api_filter = array('marking_value' => $payment_bn . $order_detail['status'], 'marking_type' => 'payment_status');
                $api_detail = $oApi_log->dump($api_filter, 'log_id');
                if (empty($api_detail['log_id'])) {
                    $msg = $order_detail['order_bn'] . '订单已取消';
                    $log_title = '店铺(' . $shop_name . ')添加支付单' . $msg;
                    $addon = $api_filter;
                    $log_id = $oApi_log->gen_id();
                    $oApi_log->write_log($log_id, $log_title, __CLASS__, __FUNCTION__, '', '', 'response', 'fail', $msg, $addon);
                }
                $responseObj->send_user_error(app::get('base')->_('Order is cancel，can not pay'), $return_value);
            }

            $order_id = $order_detail['order_id'];
            $payment_sdf['t_begin'] = kernel::single('ecorder_func')->date2time($payment_sdf['t_begin']);
            $payment_sdf['t_end'] = kernel::single('ecorder_func')->date2time($payment_sdf['t_end']);

            $sdf = array(
                'payment_bn' => $payment_bn,
                'shop_id' => $shop_id,
                'order_id' => $order_id,
                'account' => $payment_sdf['account'],
                'bank' => $payment_sdf['bank'],
                'pay_account' => $payment_sdf['pay_account'],
                'currency' => $payment_sdf['currency'] ? $payment_sdf['currency'] : 'CNY',
                'money' => $payment_money ? $payment_money : '0',
                'paycost' => $payment_sdf['paycost'],
                'cur_money' => $payment_sdf['cur_money'] ? $payment_sdf['cur_money'] : '0',
                'pay_type' => $payment_sdf['pay_type'] ? $payment_sdf['pay_type'] : 'online',
                'paymethod' => $payment_sdf['paymethod'],
                't_begin' => $payment_sdf['t_begin'] ? $payment_sdf['t_begin'] : time(),
                't_end' => $payment_sdf['t_end'] ? $payment_sdf['t_end'] : time(),
                'status' => $status,
                'memo' => $payment_sdf['memo'],
                'trade_no' => $payment_sdf['trade_no']
            );
            $paymentObj->create_payments($sdf);

            $result = array('tid' => $order_bn, 'payment_id' => $payment_bn);
            return $result;
        } else {
            $responseObj->send_user_error(app::get('base')->_('payment_bn and Order_bn can not be empty'), $return_value);
        }
    }

    /**
     * 更新付款单状态
     * @access public
     * @param array $status_sdf 付款单状态标准结构数据
     * @param object $responseObj 框架API接口实例化对象
     */
    public function status_update($status_sdf, &$responseObj) {

        $status = $status_sdf['status'];
        $payment_bn = $status_sdf['payment_bn'];
        $order_bn = $status_sdf['order_bn'];

        //返回值
        $return_value = array('tid' => $order_bn, 'payment_id' => $payment_bn);
        //状态值判断
        if ($status == '') {
            $responseObj->send_user_error(app::get('base')->_('Status field value is not correct'), $return_value);
        }
        if ($payment_bn != '' and $order_bn != '') {

            $shop_id = $this->get_shop_id($responseObj);
            $orderObj = &app::get('ecorder')->model('orders');
            $paymentObj = &app::get('ecorder')->model('payments');
            $order_detail = $orderObj->dump(array('shop_id' => $shop_id, 'order_bn' => $order_bn), 'order_id');
            $payment_detail = $paymentObj->dump(array('payment_bn' => $payment_bn, 'shop_id' => $shop_id));

            $order_id = $order_detail['order_id'];
            if ($status == "succ") {//已支付
                //更新支付单状态
                $filter = array('payment_bn' => $payment_bn, 'shop_id' => $shop_id);
                $data = array('status' => $status);
                $paymentObj->update($data, $filter);
                //更新订单状态
                $this->_updateOrder($order_id, $shop_id, $payment_detail['money']);
            }

            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('payment_bn and Order_bn can not be empty'), $return_value);
        }
    }

}