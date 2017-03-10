<?php

require_once(dirname(__FILE__) . '/../config/config.php');

$redis = new Redis();
$redis->connect(S_REDIS_HOST, S_REDIS_PORT);
$param = $redis->LPOP(S_ORDER_QUEUE);
if (empty($param)) {

    exit;
}

$param = unserialize($param);
$_SERVER['SERVER_NAME'] = $param['host'];
$argv[1] = $param['host'];

require_once(S_LIB_DIR.'/init.php');

$response = kernel::single('base_rpc_service');
base_rpc_service::$node_id = $param['nodeId'];

$orderObj = new ecorder_rpc_response_order_add();
$result =  $orderObj->add($param['order'], $response);

//var_dump($result);
/*
 * @desc reput in queue if overtime
 */
if(isset($result['overtime']) && $result['overtime']){
    $redis->RPUSH(S_ORDER_QUEUE,array('order' => $param['order'], 'nodeId' => $param['nodeId'], 'host'=>$param['host']));

    $logArray = array();
    $logArray['nodeId'] = $param['nodeId'];
    $logArray['host'] = $param['host'];
    $logArray['overtime'] = $result['overtime'];
    $logArray['order']['order_bn'] = $param['order']['order_bn'];
    $logArray['order']['pay_status'] = $param['order']['pay_status'];
    $logArray['order']['status'] = $param['order']['status'];
    $logArray['order']['ship_status'] = $param['order']['ship_status'];

    error_log(var_export($logArray,true)."\n", 3, dirname(__FILE__)."/".date("Y-m-d")."_reputqueue.log");
}

