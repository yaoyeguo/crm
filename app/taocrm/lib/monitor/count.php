<?php
//crm 统计监控
class taocrm_monitor_count{

    function setOrderTime($order_lastmodify){
        $order_lastmodify = date('Ymd',$order_lastmodify);
        if(!kernel::single('taocrm_service_redis')->redis->get($_SERVER['SERVER_NAME'].':order_count:'.$order_lastmodify)){
            if(!kernel::single('taocrm_service_redis')->redis->SISMEMBER($_SERVER['SERVER_NAME'].':order_count:day',$order_lastmodify)){
                kernel::single('taocrm_service_redis')->redis->SADD($_SERVER['SERVER_NAME'].':order_count:day',$order_lastmodify);
            }
        }
        kernel::single('taocrm_service_redis')->redis->set($_SERVER['SERVER_NAME'].':order_count:'.$order_lastmodify,time());
    }
     
    function checkCount(){
        $tmpDays = array();

        while(true){
            $day = kernel::single('taocrm_service_redis')->redis->SPOP($_SERVER['SERVER_NAME'].':order_count:day');
            if(!$day)break;

            $updated = kernel::single('taocrm_service_redis')->redis->get($_SERVER['SERVER_NAME'].':order_count:'.$day);
            $updated_day_time = strtotime(date('Y-m-d 23:59:59',$updated));

            /**
             * 更新频率已经超出当前时间一个小时，被认为暂时不会有更新，所以进行统计。
             * 如果更新时间处于两天间隔时，防止更新时间频繁更新，导致漏统计,所以更新时间超过自身当天时间就更新。
             */
            if( ($updated + 3600) < time()  || ($updated_day_time <= time()) ){
                $data = array(
					'day'=> $day,
                );
                kernel::single('taocrm_service_queue')->addJob('market_backstage_analysis@count',$data);
                kernel::single('taocrm_service_redis')->redis->DEL($_SERVER['SERVER_NAME'].':order_count:'.$day);
            }else{
                $tmpDays[] = $day;
            }

            // var_dump($tmpDays);exit;
        }

        if($tmpDays){
            foreach($tmpDays as $day){
                kernel::single('taocrm_service_redis')->redis->SADD($_SERVER['SERVER_NAME'].':order_count:day',$day);
            }
        }

    }

}