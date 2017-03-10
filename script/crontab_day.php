<?php
$begin_time = time();
set_time_limit(0);

define('LIB_DIR_A', dirname(__FILE__) . '/lib/');
require_once(LIB_DIR_A . '/init.php');


$data = array(
        'day'=>date('Y-m-d',strtotime('-1 day')),
        'type'=>'modified',
);

//删除api日志
kernel::single('ecorder_service_shop')->del_api_logs(7);

kernel::single('market_backstage_crontab')->day($data);

//子旺旺会员
kernel::single('taocrm_wangwangjingling_shop')->run();

//旺旺咨询会员
kernel::single('taocrm_wangwangjingling_chat_log')->run();


//统计商品销售数据
kernel::single("ecgoods_service_goods")->run_analysis();

//统计店铺销售数据
kernel::single("ecorder_service_shop")->run_analysis();


//写入数据库
$task_name = 'php_'.basename(__FILE__,".php");
$time_consuming = time() - $begin_time;
require_once(dirname(__FILE__) . '/lib/init.php');
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
                NULL , '".$task_name."','每天', '".$begin_time."', '".time()."','".$time_consuming."','定时每天任务'
                );";
}

$db->exec($sql);