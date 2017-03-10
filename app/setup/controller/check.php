<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class setup_ctl_check extends setup_controller{

	public function __construct($app){

		parent::__construct($app);
		define(LOG_TYPE, 3);
	}


	public function index(){
		$this->pagedata['base_url'] = kernel::base_url();
		$this->display('check_install.html');
	}

	function check_install_crm(){
		$result = array('rsp'=>'succ');

		if(!kernel::single('base_setup_lock')->lockfile_exists()){
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	/*function check_install_java(){
		$result = array('rsp'=>'succ');

		if(!$this->check_url($_POST['java_url'])){
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}*/


	function check_install_redis(){
		$result = array('rsp'=>'succ');

		try {
			if(!class_exists('Redis')){
				$result = array('rsp'=>'fail');
			}

			$url = 'http://' . $_POST['redis_ip'] . ':' . $_POST['redis_port'];
			if(!$this->check_url($url)){
				$result = array('rsp'=>'fail');
			}

		}catch (Exception $e){
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function check_install_redisphp(){
		$result = array('rsp'=>'succ');

		try {
			if(!class_exists('Redis')){
				$result = array('rsp'=>'fail');
			}

		}catch (Exception $e){
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function check_install_crmdir(){
		$result = array('rsp'=>'succ');
		if(!is_dir($_POST['crm_dir'])){
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function check_install_dir_iswrite(){
		$result = array('rsp'=>'succ');
		$data_dir = ROOT_DIR .'/data';
		$script_dir = ROOT_DIR .'/script';
		$config_dir = ROOT_DIR .'/config';
		if(!is_writable($data_dir) || !is_writable($script_dir) || !is_writable($config_dir)){
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function check_install_curl(){
		$result = array('rsp'=>'succ');
		if(!function_exists('curl_init')){
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function check_install_php_bin(){
		$result = array('rsp'=>'succ');
		$php_bin = $_POST['php_bin'];
		$cmd = sprintf('%s %s/check_php.php',$php_bin,ROOT_DIR);
		$a = exec($cmd,$b,$c);
		if($a != 'ok'){
			$result = array('rsp'=>'fail');
		}
		
		echo json_encode($result);
		exit;
	}
	
	/**
	 * 检测java中间件状态
	 */
    function check_activecode()
	{
		set_time_limit(60*2);
		$step = 8;
        
		$url = $_POST['java_install_url'];//java中间件地址
		$java_url = $_POST['java_url'];//java中间件地址
        $member_center_url = $_POST['member_center_url'];//java中间件地址
        $crm_url = explode(':',$java_url);
        $member_center_url = explode(':',$member_center_url);
		if(!$this->check_url($url)){
            $url_e = explode(':',$url);
            if(empty($url_e[2])){
                $result = array(
                    'rsp'=>'fail',
                    'errmsg'=>"Java 安装服务 Url 须按着示例填写！",
                );
                echo json_encode($result);
                exit;
            }
			
			if(!$this->check_url($java_url) || !$this->check_url($member_center_url)){


                if(empty($crm_url[2]) || empty($member_center_url[2])){
                    $errmsg = "Java 中间件 Url 或者 会员中心 Url 须按着示例填写！";
                }else{
                    $errmsg = "{$step}. $url 无法连接";
                }

				$result = array(
					'rsp'=>'fail',
					'errmsg'=>$errmsg,
				);
				echo json_encode($result);
				exit;
		     }else{
				$result = array(
					'rsp'=>'succ',
					'errmsg'=>"{$step}.ShopEx CRM Java 中间件安装程序已经启动成功",
				);
				echo json_encode($result);
				exit;
			}
		}
		
		$core_http = kernel::single('base_httpclient');
		
		//向java端发送第一次请求  传递参数
		$param1 = array(
			'targets'           => 'crm.monitor.init',
			'authorizationCode' => $_POST['java_activecode'],
            'netAddr'          => $_POST['netAddr'],
			'quartzIP'          => DB_HOST,//$_POST['db_host'],
			'quartzDBName'      => DB_NAME,//$_POST['db_name'],
			'quartzUserName'    => DB_USER,//$_POST['db_user'],
			'quartzPassWd'      => DB_PASSWORD,//$_POST['db_password'],
			'redisIp'           =>$_POST['redis_ip'],
			'redisPort'         =>$_POST['redis_port'],
            'crmPort'           =>$crm_url[2],
            'memberPort'           =>$member_center_url[2],
		);
		$response = $core_http->post($url,$param1);
		$result = json_decode($response, true);
		if($result['status'] != 'succ'){
			echo $response;
			exit;
		}
		
		//向java端发送第二次请求  轮询等待结果
		$param2 = array(
			'targets'=>'crm.monitor.polling',
		);
		while(true){
			$response = $core_http->post($url,$param2);
			$result = json_decode($response, true);
			//如果结果为真跳出循环
			if($result['status'] == 'succ'){
				$result = array(
					'rsp'=>'succ',
					'errmsg'=>"{$step}.ShopEx CRM Java 中间件安装程序启动成功",
				);
				echo json_encode($result);
				exit;
			}elseif($result['status'] == 'fail'){
				$result = array(
					'rsp'=>'fail',
					'errmsg'=>$step.'.'.$result['errmsg'],
				);
				echo json_encode($result);
				exit;
			}
			sleep(3);
		}
    }

	function auto_deploy_crontab_sh(){
		$config = $_POST;
		$result = array('rsp'=>'succ');

		$config_file = ROOT_DIR .'/script/crontab.sample.sh';
		$to_config_file = ROOT_DIR .'/script/crontab.sh';
		if(file_exists($config_file) && is_writable($config_file)){

			$str = file_get_contents($config_file);
			$str = str_replace('PHP_BIN_VAL', $config['php_bin'], $str);
			$str = str_replace('CRM_DIR_VAL', $config['crm_dir'], $str);
			file_put_contents($to_config_file, $str);
		}else{
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function auto_deploy_crontab_hour_sh(){
		$config = $_POST;
		$result = array('rsp'=>'succ');

		$config_file = ROOT_DIR .'/script/crontab_hour.sample.sh';
		$to_config_file = ROOT_DIR .'/script/crontab_hour.sh';
		if(file_exists($config_file) && is_writable($config_file)){

			$str = file_get_contents($config_file);
			$str = str_replace('PHP_BIN_VAL', $config['php_bin'], $str);
			$str = str_replace('CRM_DIR_VAL', $config['crm_dir'], $str);
			file_put_contents($to_config_file, $str);
		}else{
			$result = array('rsp'=>'fail');
		}


		echo json_encode($result);
		exit;
	}

	function auto_deploy_crontab_plugin_sh(){
		$config = $_POST;
		$result = array('rsp'=>'succ');

		$config_file = ROOT_DIR .'/script/crontab_plugin.sample.sh';
		$to_config_file = ROOT_DIR .'/script/crontab_plugin.sh';
		if(file_exists($config_file) && is_writable($config_file)){

			$str = file_get_contents($config_file);
			$str = str_replace('PHP_BIN_VAL', $config['php_bin'], $str);
			$str = str_replace('CRM_DIR_VAL', $config['crm_dir'], $str);
			file_put_contents($to_config_file, $str);
		}else{
			$result = array('rsp'=>'fail');
		}


		echo json_encode($result);
		exit;
	}

	function auto_deploy_crontab_day_sh(){
		$config = $_POST;
		$result = array('rsp'=>'succ');

		$config_file = ROOT_DIR .'/script/crontab_day.sample.sh';
		$to_config_file = ROOT_DIR .'/script/crontab_day.sh';
		if(file_exists($config_file) && is_writable($config_file)){

			$str = file_get_contents($config_file);
			$str = str_replace('PHP_BIN_VAL', $config['php_bin'], $str);
			$str = str_replace('CRM_DIR_VAL', $config['crm_dir'], $str);
			file_put_contents($to_config_file, $str);
		}else{
			$result = array('rsp'=>'fail');
		}


		echo json_encode($result);
		exit;
	}
	
	function auto_deploy_config(){
		$config = $_POST;
		$result = array('rsp'=>'succ');

		$config_file = ROOT_DIR .'/config/config.php';
		if(file_exists($config_file) && is_writable($config_file)){

			$str = file_get_contents($config_file);
			$str = str_replace('REDIS_HOST_VAL', $config['redis_ip'], $str);
			$str = str_replace('REDIS_PORT_VAL', $config['redis_port'], $str);
			$str = str_replace('MEMO_SERVICE_URL_VAL', $config['java_url'], $str);
            $str = str_replace('JAVA_NEW_URL_VAL', $config['member_center_url'], $str);
			file_put_contents($config_file, $str);
		}else{
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function auto_deploy_script_config(){
		$config = $_POST;
		$result = array('rsp'=>'succ');

		$config_file = ROOT_DIR .'/script/config/config.sample.php';
		$to_config_file = ROOT_DIR .'/script/config/config.php';
		if(file_exists($config_file) && is_writable($config_file)){

			$str = file_get_contents($config_file);
			$str = str_replace('REDIS_HOST_VAL', $config['redis_ip'], $str);
			$str = str_replace('REDIS_PORT_VAL', $config['redis_port'], $str);
			$str = str_replace('CRM_DIR_VAL', $config['crm_dir'], $str);
			$str = str_replace('PHP_BIN_VAL', $config['php_bin'], $str);
			file_put_contents($to_config_file, $str);
		}else{
			$result = array('rsp'=>'fail');
		}

		echo json_encode($result);
		exit;
	}

	function check_url($url){
		$url_info=parse_url($url);
		$port = isset($url_info['port']) ? $url_info['port'] : 80;
		$fp=fsockopen($url_info['host'], $port, $errno, $errstr, 5);
		if($fp){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 文件或目录权限检查函数
	 *
	 * @access          public
	 * @param           string  $file_path   文件路径
	 * @param           bool    $rename_prv  是否在检查修改权限时检查执行rename()函数的权限
	 *
	 * @return          int     返回值的取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
	 *                          返回值在二进制计数法中，四位由高到低分别代表
	 *                          可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
	 */
	function file_mode_info($file_path){
		/* 如果不存在，则不可读、不可写、不可改 */
		if (!file_exists($file_path))
		{
			return false;
		}
		$mark = 0;
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
		{
			/* 测试文件 */
			$test_file = $file_path . '/cf_test.txt';
			/* 如果是目录 */
			if (is_dir($file_path))
			{
				/* 检查目录是否可读 */
				$dir = @opendir($file_path);
				if ($dir === false)
				{
					return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
				}
				if (@readdir($dir) !== false)
				{
					$mark ^= 1; //目录可读 001，目录不可读 000
				}
				@closedir($dir);
				/* 检查目录是否可写 */
				$fp = @fopen($test_file, 'wb');
				if ($fp === false)
				{
					return $mark; //如果目录中的文件创建失败，返回不可写。
				}
				if (@fwrite($fp, 'directory access testing.') !== false)
				{
					$mark ^= 2; //目录可写可读011，目录可写不可读 010
				}
				@fclose($fp);
				@unlink($test_file);
				/* 检查目录是否可修改 */
				$fp = @fopen($test_file, 'ab+');
				if ($fp === false)
				{
					return $mark;
				}
				if (@fwrite($fp, "modify test.\r\n") !== false)
				{
					$mark ^= 4;
				}
				@fclose($fp);
				/* 检查目录下是否有执行rename()函数的权限 */
				if (@rename($test_file, $test_file) !== false)
				{
					$mark ^= 8;
				}
				@unlink($test_file);
			}
			/* 如果是文件 */
			elseif (is_file($file_path))
			{
				/* 以读方式打开 */
				$fp = @fopen($file_path, 'rb');
				if ($fp)
				{
					$mark ^= 1; //可读 001
				}
				@fclose($fp);
				/* 试着修改文件 */
				$fp = @fopen($file_path, 'ab+');
				if ($fp && @fwrite($fp, '') !== false)
				{
					$mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
				}
				@fclose($fp);
				/* 检查目录下是否有执行rename()函数的权限 */
				if (@rename($test_file, $test_file) !== false)
				{
					$mark ^= 8;
				}
			}
		}
		else
		{
			if (@is_readable($file_path))
			{
				$mark ^= 1;
			}
			if (@is_writable($file_path))
			{
				$mark ^= 14;
			}
		}
		return $mark;
	}
}
