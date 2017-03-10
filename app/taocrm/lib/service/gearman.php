<?php
class taocrm_service_gearman {


    private  $gmClient;

    public function __construct(){
        $this->app = app::get('taocrm');
        $gmClient= new GearmanClient();
        $hosts = explode(',', GM_SERVER);
        foreach($hosts as $host){
            $arr_host = explode(':',$host);
            $gmClient->addServer($arr_host[0], $arr_host[1]);
        }

        $this->gmClient = $gmClient;
    }


    public function addJob($function_name,$data){
        if(empty($function_name)){
            return false;
        }
        
        $function_name = 'tgcrm_'.$function_name;

        //发到gearman server
        $gmData = array('domain'=>$_SERVER['SERVER_NAME'],
            'function_name'=>$function_name,
            'data'=>$data
        );

        kernel::single('taocrm_service_redis')->redis->INCR('tgcrm.gm.queue_nums');
        $gmId =  $this->gmClient->doBackground($function_name, json_encode($gmData));
        kernel::single('taocrm_service_redis')->redis->RPUSH('tgcrm.gm.queue_list',$gmId);

        return $gmId;
    }

    function isAddJob(){

        $nums = kernel::single('taocrm_service_redis')->redis->get('taocrm.gm.queue_nums');
        if(!$nums)$nums = 0;
        //$len_queue = $redis->LLEN('taocrm.gm_queue');
        if($nums > 200000){
            return false;
        }else{
            return true;
        }
    }

    function checkQueueExist($funs){
        if(!is_array($funs) || empty($funs)){
            return false;
        }

        $len = kernel::single('taocrm_service_redis')->redis->LLEN($_SERVER['SERVER_NAME'].'.taocrm.gm.queue_list');
        $len = $len ? $len : 0;

        if($len > 0){
            return true;
        }else{
            return false;
        }
    }
}
