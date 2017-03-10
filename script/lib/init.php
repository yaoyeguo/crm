<?php
/**
 * 执行EC_OS指定域名环境下的初始化代码
 * 
 * @author hzjsq@msn.com
 * @version 1.0
 */
//define('$root_dir',realpath(dirname(__FILE__).'/../../'));
$root_dir = realpath(dirname(__FILE__).'/../../');
define('APP_DIR',$root_dir."/app/");
//define('SAAS_MODE', true);


require_once($root_dir . "/config/config.php");

require_once(APP_DIR . "/base/defined.php");

require_once(APP_DIR . '/base/kernel.php');
if(!kernel::register_autoload()){
    require(APP_DIR.'/base/autoload.php');
}

$GLOBALS['shell'] = new base_shell_loader;