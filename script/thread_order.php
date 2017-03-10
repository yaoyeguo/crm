<?php
require_once(dirname(__FILE__) . '/config/config.php');
require_once(S_LIB_DIR . 'orderManager.php');

$begin_time = time();

$manager = new orderManager();
$manager->exec();

//写入数据库
$task_name = 'php_'.basename(__FILE__,".php");
$time_consuming = time() - $begin_time;
require_once(S_LIB_DIR.'/init.php');
$db = kernel::database();
$cnt = $db->selectrow('select count(*) as cnt from sdb_desktop_surveillance where task_name = "'.$task_name.'"');
//print_r($cnt);
if($cnt['cnt']){
    $sql = "UPDATE `sdb_desktop_surveillance` set `begin_time`='".$begin_time."' ,`end_time`='".time()."',`time_consuming`=".$time_consuming." WHERE `task_name`='".$task_name."';";
}else{
    $sql = "INSERT INTO `sdb_desktop_surveillance` (
                `id` ,
                `task_name` ,
                `cycle` ,
                `begin_time` ,
                `end_time` ,
                `time_consuming` ,
                `task_desc`
                )
                VALUES (
                NULL , '".$task_name."','每分钟', '".$begin_time."', '".time()."','".$time_consuming."','订单任务'
                );";
}

$db->exec($sql);