<?php
class market_edm_utils {
	const VERSION = '1.0';
	const API_URL = 'http://newsms.ex-sandbox.com/sms_webapi/index.php'; //注册的线上地址
	const API_URL_IN='http://newsms.ex-sandbox.com/sms_webapi/';//注册的内网地址
	const SERVICE_URL = 'http://api.sms.shopex.cn/';

    /**新加信息**/
    const ENTID_APP_KEY = '123453';
    const ENTID_APP_KEY_TOKEN = '7d446682b8d8a4aea98b18d8c467c9f2';
    //const ENTID_URL = 'http://passport.ex-sandbox.com/api.php/index.php';  //商家用户中心内网接口地址
    const ENTID_URL = 'http://my.shopex.cn/api.php/index.php';  //商家用户中心线上接口地址

    //const EDM_CREATE_URL = 'http://api.edm.ex-sandbox.com';                  //邮件平台内网注册邮件地址
    //const EDM_CREATE_URL = 'http://api.edm.shopex.cn';                //邮件平台线上注册地址
    const EDM_CREATE_URL = 'http://webapi.sms.shopex.cn';

    //const EDM_URL = "http://edm.ex-sandbox.com/index.php";                   //邮件平台内网地址
    //const EDM_URL = "http://edm.shopex.cn/index.php";                       //邮件平台线上地址
    const EDM_URL = 'http://resource.shopex.cn/index.php?c=edm&m=charge&source=';
    
	const SINGLE_SMS_LENGTH = 70;
	const tag="";
	public static $blackList = null;
	public static $sumProcess = 0;
	public static $maxProcess = 30;
	public static $serverTimestamp = null;
	public static $localTimestamp = null;
	public static $writeLog = false;
	

    //获得商家用户中心的用户信息
    public static function shopex_user_get($params){
        $param['method']     = 'shopex.user.get';
        $param['app_key']    = self::ENTID_APP_KEY;
        $param['timestamp']  = time();
        $param['v']          = '1.0';
        $param['user']   = $params['email'];
        $param['format']     = 'json';
        $param['fields'] = 'username,entid,email,company,owner,url,postalcode';
        $param['sign']       = self::create_shopex_ac($param,self::ENTID_APP_KEY_TOKEN);
        
        $http = new base_httpclient;
        $result = $http->post(self::ENTID_URL, $param);
        $rs = json_decode($result,true);
        return $rs['shopex.user.get']['user'];
    }

    //登陆商家用户中心接口
    public static function shopex_user_login($params){
        $param['method']     = 'shopex.user.login';
        $param['username']   = $params['bindemail'];
        $param['password']   = md5($params['bindpassword'].'ShopEXUser');
        $param['app_key']    = self::ENTID_APP_KEY;
        $param['timestamp']  = time();
        $param['v']          = '1.0';
        $param['format']     = 'json';
        $param['sign']       =self:: create_shopex_ac($param,self::ENTID_APP_KEY_TOKEN);
        $http = new base_httpclient;
        $result = $http->post(self::ENTID_URL, $param);
        $rs = json_decode($result,true);
        $user_center_login_flag = ($rs['shopex.user.login']) ? true : false;
        if($user_center_login_flag){
            return $rs['shopex.user.login']['user'];
        }else{ 
            return false;
        }
    }
	
	/*
    * 商家用户中心sign验证
    */
    public static function create_shopex_ac($post_params,$token) {
        ksort($post_params);
        $str = '';
        foreach( $post_params as $key => $value )
        {
            if( $key != 'sign' )
            {
                $str .= $value;
            }
        }
        return md5($str.$token);
    }

    /*
    * 创建邮箱账号
    */
    public static function email_user_create($params,$token){
        $token = $token;
        $arr['certi_app']='email.user_create';
        $arr['entId']=$params['entid'];
        $arr['entPwd']=md5($params['bindpassword'].'ShopEXUser');
        $arr['email']    = $params['edm_email'];
        $arr['source']=$params['source'];
        $arr['version']='1.0';
        $arr['format']='json';
        $arr['timestamp']=self::get_edm_timestamp($params['source'],$token);
        $arr['company']    = $params['company'];
        $arr['contact']    = $params['contact'];
        $arr['cell']      = $params['cell'];
        $arr['certi_ac']=self::create_shopex_edm_ac($arr,$token);
        $http = new base_httpclient;
        $result = $http->post(self::EDM_CREATE_URL, $arr);
        $rs = json_decode($result,true);
        if($rs['res']=='succ'){
            return $rs;
        }else{
            return FALSE;
        }
    }

    /*
    * 获取邮件时间戳
    */
    public static function get_edm_timestamp($source,$token){
        $arr['certi_app']='email.time';
        $arr['source']=$source;
        $arr['version']='1.0';
        $arr['format']='json';
        $arr['certi_ac']=self::create_shopex_edm_ac($arr,$token);
        $http = new base_httpclient;
        $result = $http->post(self::EDM_CREATE_URL, $arr);
        $rs = json_decode($result,true);
        if($rs['res']=='succ'){
            return $rs['info'];
        }else{
            return false;
        }
    }

    /*
    *创建邮件验证
    */
    public static function create_shopex_edm_ac($arr,$token){
        ksort($arr);
        $str = '';
        foreach($arr as $key=>$value){
            if($key!=' certi_ac') {
                $str.= $value;
            }
        }
        return strtolower(md5($str.strtolower(md5($token))));
    }

    /*
    *获取邮件平台地址
    */
	public static function get_book_url($arr) {
		//解密短信平台返回的密码
		$market_edm_des = kernel::single('market_edm_des');
		$arr['password'] = $market_edm_des->decrypt($arr['password']);
	
		//$url = self::EDM_URL.'?c=welcome&m=dark_login&origin=';//拼接地址
		$url = self::EDM_URL;
        //$ac = market_edm_des::encrypt(md5(($arr['password'].'ShopEXUser')));
		$ac = $market_edm_des->encrypt($arr['password']);
        //免登进入购买套餐页面
		$param = array(
			'biz_id' => self::encode(APP_SOURCE),
			'entid' => $arr['entid'],
            'ac' => $ac,
			//'ac' => md5(($arr['password'].'ShopEXUser')),
			//'t' => self::get_edm_timestamp(APP_SOURCE,APP_TOKEN),
			't' => market_sms_utils::get_server_time(),
			'version' => '1.0',
		);
		$origin = self::encode($param['biz_id'] . '|' . $param['entid'] . '|' . $param['ac'] . '|' . $param['t'].'|'.$param['version']);

		return $url . $origin;
	}

    /*
    *获取邮件账号信息
    */
    
	public static function edm_user_info($arr) {
    	$param = array(
    		'certi_app' => 'email.info',
    		'entId' => $arr['entid'],
    		'entPwd' => md5($arr['password'].'ShopEXUser'),
    		'source' => APP_SOURCE,
    		'version' => '1.0',
    		'format' => 'json',
    		'timestamp' => self::get_edm_timestamp(APP_SOURCE,APP_TOKEN),
    	);
    	$param['certi_ac'] = self::create_shopex_edm_ac($param, APP_TOKEN);
    	$http = new base_httpclient;
    	$result = $http->post(self::EDM_CREATE_URL, $param);
        $rs = json_decode($result,true);
    	return $rs;
	}
    
    public function update_edm_kv()
    {
        $arr = array();
        base_kvstore::instance('market')->fetch('account',$edms);
        if(unserialize($edms)){  //如果已经绑定过邮件
            $edms = unserialize($edms);
            $info = market_sms_utils::GetEnterpriseByMobile($edms);
            $info = json_encode($info);
            $info = json_decode($info, 1);
            
            //解密短信平台返回的密码
            $market_edm_des = kernel::single('market_edm_des');
            $password = $market_edm_des->decrypt($info['info']['password']);
            
            $param = array(
                'certi_app' => 'email.info',
                'entId' => $info['info']['entid'],
                'entPwd' => $password,
                'source' => APP_SOURCE,
                'version' => '1.0',
                'format' => 'json',
                'timestamp' => self::get_edm_timestamp(APP_SOURCE,APP_TOKEN),
            );
            $param['certi_ac'] = self::create_shopex_edm_ac($param, APP_TOKEN);
            $http = new base_httpclient;
            $result = $http->post(self::EDM_CREATE_URL, $param);
            $info = json_decode($result,true);
            
            if(isset($info['info']['account_info'])) $arr = $info['info']['account_info'];
            $arr['password'] = $password;
            base_kvstore::instance('edm')->store('account', serialize($arr));
            //echo('<pre>');var_dump($arr);
        }
        return $arr;
    }

    /*
    *获取邮件服务器的时间戳
    */
	public static function get_server_time() {
		if (null === self::$serverTimestamp || null === self::$localTimestamp) {
			$param = array(
				'certi_app' => 'sms.servertime',
				'version' => self::VERSION,
				'format' => 'json',
			);
			$param['certi_ac'] = self::make_shopex_ac($param, 'SMS_TIME');
	
	    	$http = new base_httpclient;
	    	$result = $http->post(self::API_URL, $param);
	    	$result = json_decode($result);
	    	self::$serverTimestamp = ('succ' == $result->res) ? $result->info : 0;
	    	self::$localTimestamp = time();
	    	return self::$serverTimestamp;
		}
		else {
			return self::$serverTimestamp + time() - self::$localTimestamp;
		}    				
	}

    public static function make_shopex_ac($arr, $token) {
		$temp_arr = $arr;
		ksort($temp_arr);
		$str = '';
		foreach ($temp_arr as $key => $value) {
			if ($key != 'certi_ac') {
				$str .= $value;
        	}
    	}
		return md5($str . md5($token));
	}
	
	public static function province_mapping($provinceName) {
		$mapping = array(
			'北京' => '110000',
			'天津' => '120000',
			'上海' => '310000',
			'重庆' => '500000',
			'香港' => '810000',
			'台湾' => '710000',
			'澳门' => '820000',
			'新疆' => '650000',
			'宁夏' => '640000',
			'内蒙古' => '150000',		
			'广西' => '450000',
			'西藏' => '540000',
			'河北' => '130000',
			'河南' => '410000',		
			'陕西' => '610000',
			'辽宁' => '210000',
			'吉林' => '220000',
			'黑龙江' => '230000',		
			'江苏' => '320000',
			'浙江' => '330000',
			'安徽' => '340000',
			'福建' => '350000',		
			'江西' => '360000',
			'山东' => '370000',
			'山西' => '140000',
			'湖北' => '420000',		
			'湖南' => '430000',
			'广东' => '440000',		
			'海南' => '460000',
			'四川' => '510000',
			'贵州' => '520000',
			'云南' => '530000',	
			'陕西' => '610000',
			'甘肃' => '620000',		
			'青海' => '630000',																			
		);
		return $mapping[trim($provinceName)];
	}

    /*
    * 注册商家用户中心账号
    */
	public static function register($arr) {
    	$param = $arr;
    	$param['method'] = 'shopex.user.add';
    	$param['v'] = '1.0';
        $param['app_key'] = self::ENTID_APP_KEY;
    	$param['format'] = 'json';
    	$param['timestamp'] = time();
    	$param['sign'] = self::create_shopex_ac($param,self::ENTID_APP_KEY_TOKEN);
    	$http = new base_httpclient;
    	$result = $http->post(self::ENTID_URL, $param);//线上地址
        $rs = json_decode($result,true);
        if($rs['shopex.user.add']){
            return $rs['shopex.user.add']['user'];
        }else{
            return false;
        }
    	
	}

	public static function registerErrorMsg($code) {
		$msg = array(
			'EmailExist' => 'Email已被使用,请更换后再注册',
		);
		return $msg[$code];
	}
	

    
    public static function encode($str) {
        $str = base64_encode($str);
        return strtr($str, self::pattern());
        
    }
  

    public static function decode($str)
    {
        $str = strtr($str, array_flip(self::pattern()));
        return base64_decode($str);
    }

    public static function pattern(){
        return array(
        '+'=>'_1_',
        '/'=>'_2_',
        '='=>'_3_',
        );
    }

    /**
     * 
     * 发送短信过程中使用多进程
     * 主进程作为调度器，子进程负责短信发送
     * 
     */
    public function send_fanout_multi_process($arr, $content) {
		$pid = pcntl_fork();
		if ($pid == -1) {
			die("could not fork\n");
		}
		elseif ($pid) {
			self::$sumProcess++;
			echo str_pad(">>> [Process/Sum ".self::$sumProcess."/".self::$maxProcess."]", 30);
			echo "Send Sms\n";
			if (self::$sumProcess >= self::$maxProcess) {
				pcntl_wait($status);
				self::$sumProcess--;
 			}
			return $pid;
		}
		else {
			self::send($arr, $content, 'fan-out');
			exit();
		}
    }	
    
    public static function hasRegister() {
    	base_kvstore::instance('edm')->fetch('hasRegister', $hasRegister);
    	return $hasRegister;
    }
    
    public static function utf8_strlen($string = null) {
		preg_match_all("/./us", $string, $match);
		return count($match[0]);
	}
	
	public static function get_account() {
	    base_kvstore::instance('sms')->fetch('account', $account);
    	if (!unserialize($account)) {
    		return false;
    	}
    	
		$param = unserialize($account);
		return $param;	
	}
	
	public static function setLog($writeLog = false) {
		self::$writeLog = $writeLog;
	}
	
	/**
	 * 
	 * 记录特殊的短信日志，包括短信包含禁词，手机号码不正确。
	 * @param int $errno, 值为-3(包含禁词)或-4(手机号码不正确)
	 * @param array $content, 一维数组
	 */
	public static function writeSpecialLog($errno, $content) {
		$batchno = $errno;	//-3(包含禁词)或-4(手机号码不正确)
		$status = 0;
		$msg = '';
		
		$logObj = app::get('sms')->model('log');
		foreach ($content['phones'] as $mobile) {
			$log = array(
				'batchno' => $batchno,
				'mobile' => $mobile,
				'content' => $content['content'],
				'status' => $status,
				'msg' => $msg,
				'sendtime' => time()
			);
			$logObj->insert($log);
		}		
	}
	
	/**
	 * 
	 * 记录短信发送日志
	 * @param json array $content
	 * @param mix type $result
	 * 如果请求发送短信api失败, $result为false
	 * 如果请求成功，则为api返回的json格式的结果
	 */
	public static function writeLog($content, $result) {
		$logObj = app::get('sms')->model('log');
		$content = json_decode($content);
		if (false === $result) {
//			特殊批次号，api请求不成功时为-1
			$batchno = -1;
			$status = 0;
			$msg = '请求api失败';
		}
		else {
			$result = json_decode($result);
			$batchno = ('succ' == $result->res) ? $result->info->msgid : -2;	//请求api成功但是发送失败的时候，批次号记为-2
			$status = ('succ' == $result->res) ? 1 : 0;
			$msg = ('succ' == $result->res) ? '' : (is_object($result->info) ? $result->info->msg : $result->info);
		}
		
		foreach ($content as $value) {
			$mobiles = explode(",", $value->phones);
			foreach ($mobiles as $mobile) {
				$log = array(
					'batchno' => $batchno,
					'mobile' => $mobile,
					'content' => $value->content,
					'status' => $status,
					'msg' => $msg,
					'sendtime' => time()
				);
				$logObj->insert($log);
			}
		}
	}
	public static function isMobilePhone($num) {
		$pattern = '/^1\d{10}$/';
		if (preg_match($pattern, $num)) {
			return true;
		}
		else {
			return false;
		}
	}

    /*
    * 查看是否购买短信接口
    */
    public function get_sms_buy_info($arr){
        $token              = 'SMS_BUYINFO';
        $substr['certi_app']   = 'sms.buy_info';
        $substr['entid']   = $arr['entid'];
        $substr['bizid']   = APP_SOURCE;
        $substr['version']  = '1.0';
        $substr['format']   = 'json';
        $substr['certi_ac'] = self::make_shopex_ac( $substr, $token );
        $http = new base_httpclient;
    	$result = $http->post("http://webapi.sms.shopex.cn", $substr);//线上地址
        //$result = $http->post("http://newsms.ex-sandbox.com/sms_webapi/", $substr);//内网地址
    	return json_decode($result,true);
    }
}