<?php
/**
 * 订单支付同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_payment extends ome_rpc_request {
    
    //支付状态
    var $pay_status = array(
          'succ'=>'SUCC',
          'failed'=>'FAILED',
          'cancel'=>'CANCEL',
          'error'=>'ERROR',
          'invalid'=>'INVALID',
          'progress'=>'PROGRESS',
          'timeout'=>'TIMEOUT',
          'ready'=>'READY',
    );
    
    /**
     * 添加交易收款单
     * @access public
     * @param int $payment_id 支付单主键ID
     * @return boolean
     */
    public function add($payment_id){
       
        if(!empty($payment_id)){
            $paymentObj = &app::get('ome')->model('payments');
            $orderObj = &app::get('ome')->model('orders');
            $memberObj = &app::get('ome')->model('members');
            $payment_detail = $paymentObj->dump(array('payment_id'=>$payment_id), '*');
            $order = $orderObj->dump($payment_detail['order_id'], 'order_bn,member_id');
            $memberinfo = $memberObj->dump($order['member_id'],'uname,name');
            
            if ($payment_detail['status']=='succ'){#只有支付成功才发起同步
            
                $params['tid'] = $order['order_bn'];
                $params['payment_id'] = $payment_detail['payment_bn'];
                $params['buyer_id'] = $memberinfo['account']['uname'];
                $params['seller_account'] = $payment_detail['account']?$payment_detail['account']:'';
                $params['seller_bank'] = $payment_detail['bank']?$payment_detail['bank']:'';
                $params['buyer_account'] = $payment_detail['pay_account']?$payment_detail['pay_account']:'';
                $params['currency'] = $payment_detail['currency']?$payment_detail['currency']:'CNY';
                $params['pay_fee'] = $payment_detail['money'];
                $params['paycost'] = $payment_detail['paycost']?$payment_detail['paycost']:'';
                $params['currency_fee'] = $payment_detail['cur_money']?$payment_detail['cur_money']:'';
                $params['pay_type'] = $payment_detail['pay_type'];
                $params['payment_type'] = $payment_detail['paymethod']?$payment_detail['paymethod']:'';
                $params['t_begin'] = date("Y-m-d H:i:s",$payment_detail['t_begin']);
                $params['t_end'] = date("Y-m-d H:i:s",$payment_detail['t_end']);
                $params['memo'] = $payment_detail['memo']?$payment_detail['memo']:'';
                $params['status'] = $this->pay_status[$payment_detail['status']];
                $params['payment_operator'] = kernel::single('desktop_user')->get_login_name();
                $params['outer_no'] = $payment_detail['trade_no']?$payment_detail['trade_no']:'';#支付网关的内部交易单号

                $callback = array(
                    'class' => 'ome_rpc_request_payment',
                    'method' => 'payment_add_callback',
                );
                
                $shop_id = $payment_detail['shop_id'];
                if($shop_id){
                    $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                    $title = '店铺('.$shop_info['name'].')添加[交易付款单](订单号:'.$order['order_bn'].'付款单号:'.$payment_detail['payment_bn'].')';
                }else{
                    $title = '添加交易收款单';
                }
    
                $this->request('store.trade.payment.add',$params,$callback,$title,$shop_id);
            
            }else return false;
            
        }else{
            return false;
        }
        
    }
    
    function payment_add_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更新支付单状态
     * @access public
     * @param int $payment_id 支付单主键ID
     * @return boolean
     */
    public function status_update($payment_id){
        
        if(!empty($payment_id)){
            $paymentObj = &app::get('ome')->model('payments');
            $orderObj = &app::get('ome')->model('orders');
            //支付单详情
            $payment_detail = $paymentObj->dump(array('payment_id'=>$payment_id), 'order_id,shop_id,payment_bn,status');
            $order = $orderObj->dump($payment_detail['order_id'], 'order_bn');
            $params['tid'] = $order['order_bn'];
            $params['payment_id '] = $payment_detail['payment_bn'];
            $params['oid '] = '';#子订单id
            $params['status'] = $this->pay_status($payment_detail['status']);
            
            $callback = array(
                'class' => 'ome_rpc_request_payment',
                'method' => 'payment_status_update_callback',
            );
            
            $shop_id = $payment_detail['shop_id'];
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')更新[交易支付单状态](订单号:'.$order['order_bn'].'付款单号:'.$payment_detail['payment_bn'].')';
            }else{
                $title = '更新交易支付单状态';
            }
            
            $this->request('store.trade.payment.status.update',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function payment_status_update_callback($result){
        return $this->callback($result);
    }
}