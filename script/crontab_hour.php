<?php
$begin_time = time();
set_time_limit(0);

define('LIB_DIR_A', dirname(__FILE__) . '/lib/');
require_once(LIB_DIR_A . '/init.php');

kernel::single('plugins_service_api')->run_hour();

//写入数据库
$task_name = 'php_'.basename(__FILE__,".php");
$time_consuming = time() - $begin_time;
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
                NULL , '".$task_name."','每小时', '".$begin_time."', '".time()."','".$time_consuming."','定时每小时任务'
                );";
}

$db->exec($sql);