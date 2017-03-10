<?php
/**
 * 订单退款同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_refund extends ome_rpc_request {
    
    //退款状态
    var $status = array(
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
     * 添加交易退款单
     * @access public
     * @param int $refund_id 退款单主键ID
     * @return boolean
     */
    public function add($refund_id){

        if(!empty($refund_id)){
            $refundObj = &app::get('ome')->model('refunds');
            $orderObj = &app::get('ome')->model('orders');
            $memberObj = &app::get('ome')->model('members');
            $refund_detail = $refundObj->dump(array('refund_id'=>$refund_id), '*');
            $order = $orderObj->dump($refund_detail['order_id'], 'order_bn,member_id');
            //买家客户信息 
            $member_info = $memberObj->dump($order['member_id'], 'uname,name');
            $params['tid'] = $order['order_bn'];
            $params['refund_id'] = $refund_detail['refund_bn'];
            $params['buyer_account'] = $refund_detail['account']?$refund_detail['account']:'';
            $params['buyer_bank'] = $refund_detail['bank']?$refund_detail['bank']:'';
            $params['seller_account'] = $refund_detail['pay_account']?$refund_detail['pay_account']:'';
            $params['buyer_name'] = $member_info['contact']['name'];#买家姓名
            $params['buyer_id'] = $member_info['account']['uname'];#买家客户帐号
            $params['currency'] = $refund_detail['currency']?$refund_detail['currency']:'CNY';
            $params['refund_fee'] = $refund_detail['money'];
            $params['paycost'] = $refund_detail['paycost']?$refund_detail['paycost']:'';
            $params['currency_fee'] = $refund_detail['cur_money'];
            $params['pay_type'] = $refund_detail['pay_type'];
            $params['payment_type'] = $refund_detail['paymethod']?$refund_detail['paymethod']:'';
            $params['t_begin'] = date("Y-m-d H:i:s",$refund_detail['t_ready']);
            $params['t_sent'] = date("Y-m-d H:i:s",$refund_detail['t_sent']);
            $params['t_received'] = $refund_detail['t_received'] ? date("Y-m-d H:i:s",$refund_detail['t_received']) : date("Y-m-d H:i:s",time());
            $params['status'] = $this->status[$refund_detail['status']];
            $params['memo'] = $refund_detail['memo']?$refund_detail['memo']:'';
            $params['outer_no'] = $refund_detail['trade_no']?$refund_detail['trade_no']:'';
            
            $callback = array(
                'class' => 'ome_rpc_request_refund',
                'method' => 'refund_add_callback',
            );
            
            $shop_id = $refund_detail['shop_id'];
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')添加[交易退款单](订单号:'.$order['order_bn'].'退款单号:'.$refund_detail['refund_bn'].')';
            }else{
                $title = '添加交易退款单';
            }

            $this->request('store.trade.refund.add',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }

    }
    
    function refund_add_callback($result){
        return $this->callback($result);
    }
    
    /**
     * 更新交易退款状态
     * @access public
     * @param int $refund_id 退款单主键ID
     * @return boolean
     */
    public function status_update($refund_id){
        
        if(!empty($refund_id)){
            $refundObj = &app::get('ome')->model('refunds');
            $orderObj = &app::get('ome')->model('orders');
            $refund_detail = $refundObj->dump(array('refund_id'=>$refund_id), 'order_id,shop_id,refund_bn,status');
            $order = $orderObj->dump($refund_detail['order_id'], 'order_bn');
            $params['tid'] = $order['order_bn'];
            $params['refund_id '] = $refund_detail['refund_bn'];
            $params['oid '] = '';#子订单id
            $params['status'] = $this->status[$refund_detail['status']];
            
            $callback = array(
                'class' => 'ome_rpc_request_refund',
                'method' => 'refund_status_update_callback',
            );
            
            $shop_id = $refund_detail['shop_id'];
            if($shop_id){
                $shop_info = &app::get('ome')->model('shop')->dump($shop_id,'name');
                $title = '店铺('.$shop_info['name'].')更新[交易退款状态]:'.$params['status'].'(订单号:'.$order['order_bn'].'退款单号:'.$refund_detail['refund_bn'].')';
            }else{
                $title = '更新交易退款状态';
            }
            
            $this->request('store.trade.refund.status.update',$params,$callback,$title,$shop_id);
        }else{
            return false;
        }
    }
    
    function refund_status_update_callback($result){
        return $this->callback($result);
    } 
    

}