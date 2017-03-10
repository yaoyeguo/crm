<?php
  
date_default_timezone_set('Asia/Shanghai');


define('LIB_DIR',  dirname(__FILE__) . '/../');
require_once(LIB_DIR. '/lib/init.php');
require_once(LIB_DIR . '/update/upgrade/'.CRM_VERSION.'.php');
kernel::single('base_shell_webproxy')->exec_command("update --ignore-download");


$sj = new upgrade();

$sj->start();
base_kvstore::instance('desktop')->store('firstLogin','');//升级后清空kv值


