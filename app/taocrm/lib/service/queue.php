<?php
class taocrm_service_queue {

    var $type = '';
    var $queue_flag = 'tgcrm';

    public function __construct(){
        if(strstr($_SERVER['SERVER_NAME'],'.crm.taoex.com')){
            $this->queue_flag = 'tgcrm';
        }else if(strstr($_SERVER['SERVER_NAME'],'.mcrm.taoex.com')){
            $this->queue_flag = 'tbtgcrm';
        }else{
            $this->queue_flag = 'tgcrm';
        }
    }

    //后台脚本使用，方便传值
    function setType($type){
        $this->type = $type;
    }

    public function addJob($worker,$data){
        if(empty($worker)){
            return false;
        }


        //发到gearman server
        $queue = array('host'=>$_SERVER['SERVER_NAME'],
            'worker'=>$worker,
            'params'=>$data,
			'type'=>$this->type,
        );

        if(empty($this->type)){
           kernel::single('taocrm_service_redis')->redis->RPUSH($this->queue_flag.':SYS_NORMAL_QUEUE',json_encode($queue));
        }else{
            if($this->type == 'realtime'){
                kernel::single('taocrm_service_redis')->redis->RPUSH($this->queue_flag.':SYS_REALTIME_QUEUE',json_encode($queue));
            }else{//waiting
                kernel::single('taocrm_service_redis')->redis->RPUSH($this->queue_flag.':'.$_SERVER['SERVER_NAME'].':queue',json_encode($queue));
                kernel::single('taocrm_service_redis')->redis->SADD($this->queue_flag.':SYS_HOST_QUEUE',$_SERVER['SERVER_NAME']);
            }
        }

        return true;
    }


    public function isAddJob(){

        $len_normal = kernel::single('taocrm_service_redis')->redis->LLEN('tgcrm:SYS_NORMAL_QUEUE');
        $len_realtime = kernel::single('taocrm_service_redis')->redis->LLEN('tgcrm:SYS_REALTIME_QUEUE');

        $nums = $len_normal + $len_realtime;
        $nums = intval($nums);
        //$len_queue = $redis->LLEN('taocrm.gm_queue');
        if($nums > 200000){
            return false;
        }else{
            return true;
        }
    }

    public function checkQueueExist(){
        if(kernel::single('taocrm_service_redis')->redis->SISMEMBER('tgcrm:SYS_HOST_QUEUE',$_SERVER['SERVER_NAME'])){
            return true;
        }else{
            return false;
        }
    }
}
