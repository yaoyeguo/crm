<?php
class taocrm_task{
    function post_install($options){
        kernel::single('base_initial', 'taocrm')->init();

        $sql = " UPDATE `sdb_desktop_menus` SET `display`='false' WHERE `menu_type`='menu' and `app_id`='ectools' ";
        kernel::database()->exec($sql,true);

    	$configPath = ROOT_DIR . '/config/config.php';
    	$handle = fopen($configPath, 'a');
    	$str = "\ndefine('GOODS_APP', 'ecgoods');\n";
    	fwrite($handle, $str);
    	$str = "\ndefine('ORDER_APP', 'ecorder');\n";
    	fwrite($handle, $str);
    	$str = "\ndefine('REDIS_HOST', 'REDIS_HOST_VAL');\n";
    	fwrite($handle, $str);
    	$str = "\ndefine('REDIS_PORT', 'REDIS_PORT_VAL');\n";
    	fwrite($handle, $str);
    	$str = "\ndefine('MEMO_SERVICE_URL', 'MEMO_SERVICE_URL_VAL');\n";
    	fwrite($handle, $str);
        $str = "\ndefine('JAVA_NEW_URL', 'JAVA_NEW_URL_VAL');\n";
        fwrite($handle, $str);
    	//$str = "define('APP_SOURCE', '616525');\n";
    	//fwrite($handle, $str);
    	//$str = "define('APP_TOKEN', 'd00d04835238a7kk27a13833697f9b3a');\n";
    	//fwrite($handle, $str);
    	fclose($handle);
    }

    function post_update($params){
    }
}
