<?php
class market_coupon_task{
    function updateSentStatus(){
        $couponSentObj = &app::get('market')->model('coupon_sent');
        $queueObj = &app::get('base')->model('queue');
        $sentList = $couponSentObj->getList('*',array('coupon_status'=>'1'));

        $count = 0;
        $limit = 50;
        $page = 0;
        $sentData = array();
        foreach($sentList as $sent){
            if($count < $limit){
                $count ++;
            }else{
                $count = 0;
                $page ++;
            }

            $sentData[$page][] = $sent;
        }

        foreach($sentData as $data){
            $queueData = array(
                'queue_title'=>'优惠券使用状态查询',
                'start_time'=>time(),
                'params'=>array(
                    'data'=>$data,
                    'app' => 'market',
                    'mdl' => 'coupon_sent'
                ),
                'worker'=>'market_coupon_task.run',
            );
            $queueObj->save($queueData);
        }
        return true;
    }

    function run(&$cursor_id,$params){
        $couponRpcObj = kernel::single('market_rpc_request_coupon');
        foreach($params['data'] as $data){
            $couponRpcObj->getDetail($data);
        }
        return false;
    }
}