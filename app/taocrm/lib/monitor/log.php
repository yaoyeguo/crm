<?php

class taocrm_monitor_log{

   
    /**
     * 
     * 日期 最大执行时间 最小执行时间 队列数 僵尸队列数 monitor_queue_log
     * 
     * 日期 队列名称  最大执行时间 最小执行时间 队列数 僵尸队列数 monitor_queue_log 
     * 
     * 日期 域名  最大执行时间 最小执行时间 队列数 僵尸队列数 
     * 
     * 队列明细：日期 队列名称 执行时间  域名 状态
     */
    public function monitorQueue(){
        $log_file = ROOT_DIR .'/script/queue/logs/queue/'.strtotime('-1 day').'.log';
        if(is_file($log_file)){
            $str = file_get_contents($log_file);
            $logs = explode($str, "\n");
            $data = array();
            foreach($logs as $log){
                $str = explode($log, "|");
                if(!isset($data[$str[0]])){
                    $data[$str[0]] = array();
                }
                $data[$str[0]][] = $str;
            }
        }
         
    }


}