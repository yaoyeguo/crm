<?php

class desktop_ctl_surveillance extends desktop_controller{
	var $workground = 'sys.config';


	public function __construct($app){
		parent::__construct($app);
	}

	public function show(){
        $sur_arr = array();
        //数据库
        $rs = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
        if(!$rs){
            $sur_arr['mysql']['code'] = 'fail';
            $sur_arr['mysql']['msg'] = '数据库连接失败！';
        }else{
            $sur_arr['mysql']['code'] = 'success';
            $sur_arr['mysql']['msg'] = '数据库连接成功！';
        }
        //redis
        try{
            $redis = new Redis();
            $redis->connect(REDIS_HOST, REDIS_PORT);
            $sur_arr['redis']['code'] = 'success';
            $sur_arr['redis']['msg'] = 'redis连接成功！';
        }catch (Exception $e){
            $sur_arr['redis']['code'] = 'fail';
            $sur_arr['redis']['msg'] = 'redis连接失败！';
        }
        //java
        $java_url = MEMO_SERVICE_URL;
        if(!$this->check_url($java_url)){
            $sur_arr['java']['code'] = 'fail';
            $sur_arr['java']['msg'] = 'java中间件启动失败！';
        }else{
            $sur_arr['java']['code'] = 'success';
            $sur_arr['java']['msg'] = 'java中间件启动成功！';
        }
        $this->pagedata['sur_arr'] = $sur_arr;
        $surobj = $this->app->model('surveillance');
        $surdata = $surobj->getList('*',array(),0,-1,'begin_time DESC');
        foreach($surdata as $k => $v){
            if($v['cycle'] == '每分钟'){
                $surdata[$k]['cycle_int'] = 60;
            }elseif($v['cycle'] == '每小时'){
                $surdata[$k]['cycle_int'] = 3600;
            }elseif($v['cycle'] == '每天'){
                $surdata[$k]['cycle_int'] = 86400;
            }
            $surdata[$k]['begin_time'] = date('Y-m-d H:i:s', $v['begin_time']);
            $surdata[$k]['end_time'] = date('Y-m-d H:i:s', $v['end_time']);
        }
       // var_dump($surdata);
        $this->pagedata['surdata'] = $surdata;
		$this->page('surveillance.html');
	}
    function check_url($url){
        $url_info=parse_url($url);
        $port = isset($url_info['port']) ? $url_info['port'] : 80;
        $fp=fsockopen($url_info['host'], $port, $errno, $errstr, 5);
        if($fp){
            return true;
        }else{
            return false;
        }
    }
}


