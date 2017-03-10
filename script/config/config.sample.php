<?php
/**
 * 定义一些常量
 */

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set("Asia/Shanghai");

//定义一些基准目录的信息
define('S_BASE_DIR', dirname(__FILE__) . '/../');
define('S_LIB_DIR', S_BASE_DIR . 'lib/');

define('S_REDIS_HOST', 'REDIS_HOST_VAL');
define('S_REDIS_PORT', 'REDIS_PORT_VAL');


define('S_OP_HOST','tgcrm:OP_HOST');
define('S_OP_HOST_SESSION','tgcrm:OP_HOST_SESSION');
define('S_NORMAL_QUEUE', 'tgcrm:SYS_NORMAL_QUEUE');
define('S_REALTIME_QUEUE', 'tgcrm:SYS_REALTIME_QUEUE');
define('S_IS_STOP_QUEUE', 'tgcrm:SYS_IS_STOP_QUEUE');
define('S_HOST_QUEUE', 'tgcrm:SYS_HOST_QUEUE');
define('S_ORDER_QUEUE', 'tgcrm:SYS_ORDER_QUEUE');
define('S_ORDER_QUEUE_EXEC_SCRIPT', 'CRM_DIR_VAL/script/thread/threadOrder.php');

define('S_CSTOOLS_ORDER_QUEUE_EXEC_SCRIPT', 'CRM_DIR_VAL/script/thread/cstoolsThreadOrder.php');
define('S_CSTOOLS_ORDER_QUEUE', 'tgcrm:SYS_CSTOOLS_ORDER_QUEUE');

define('S_PHP_EXEC', 'PHP_BIN_VAL');
define('S_QUEUE_EXEC_SCRIPT', 'CRM_DIR_VAL/script/queue/threadQueue.php');
define('S_WAITING_QUEUE_EXEC_SCRIPT', 'CRM_DIR_VAL/script/queuewaiting/threadQueue.php');

