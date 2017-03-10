<?php
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
$root_dir = realpath(dirname(__FILE__) . '/../../../');
require_once( $root_dir . '/config/config.php');
require ($root_dir . '/app/base/kernel.php');
define('APP_DIR', $root_dir . '/app/');
include_once (APP_DIR . '/base/defined.php');
include_once (APP_DIR . '/base/lib/http.php');
if (!kernel::register_autoload()){
    require (APP_DIR . '/base/autoload.php');
}
function getList($file,$spilt=','){
    $fileContent = file_get_contents($file);
    $row = explode("\r", $fileContent);
    foreach ($row as $cols) {
        $res[] = explode(';', $cols);
    }
    return $res;
}
$path = 'region.csv';
$list = getList($path,';');
$rObj = app::get('member')->model('region');
foreach ($list as $row) {
   $rObj->add($row[0],$row[2],'',$row[1]); 
}



