<?php
class taocrm_service_redis {


    public  $redis;

    public function __construct(){
        $this->app = app::get('taocrm');
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        
        $this->redis = $redis;
    }


}
