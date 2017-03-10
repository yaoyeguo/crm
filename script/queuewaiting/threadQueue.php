<?php
require_once(dirname(__FILE__) . '/../config/config.php');

$host = $argv[1];

$redis = new Redis();
$redis->connect(S_REDIS_HOST, S_REDIS_PORT);

$queue = $redis->LPOP('tgcrm:'.$host.':queue');//实时队列

if (empty($queue)) {

	exit;
}

$queue = json_decode($queue,true);

/*$queue = array (
 'host' => 'localhost',
 'worker' => 'market_backstage_activity@fetch',
 'params' =>
 array (
 'active_id' => '74',
 'sms_config' =>
 array (
 'entid' => NULL,
 'password' => NULL,
 'license' => '1203739835',
 'source' => '9143',
 'app_token' => '2eed42036c01414c9d3e86694c3040f6',
 ),
 'msgid' => '1345530783_629',
 ),
 );*/
//var_export($queue);



$_SERVER['SERVER_NAME'] = $queue['host'];
$domain = $queue['host'];
$argv[1]  = $domain;

require_once(S_LIB_DIR.'/init.php');

$arr = explode('@', $queue['worker']);
$class = $arr[0];
$method = $arr[1];
$obj = kernel::single($class);

//进入补单流程
kernel::single('taocrm_service_queue')->setType('waiting');

if(method_exists($obj, $method)){
	$result = $obj->$method($queue['params']);
	//var_dump($result);
	if(empty($result)){
		ilog('no result('.$queue['worker'].')');
	}else{
		if($result['status'] == 'succ'){

		}else if($result['status'] == 'timeout'){
			kernel::single('taocrm_service_queue')->addJobHost($queue['worker'],$queue['params']);

		}else{
			ilog('fail:'.$result['errmsg'].'('.$queue['worker'].')');
		}
	}
}else{
	ilog('method no exists('.$queue['worker'].')');
}


function ilog($str) {

	global $domain;
	$filename = dirname(__FILE__) . '/logs/' . date('Y-m-d') . '.log';
	$fp = fopen($filename, 'a');
	fwrite($fp, date("m-d H:i") . "\t" . $domain . "\t" . $str . "\n");
	fclose($fp);
}