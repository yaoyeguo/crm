<?php
class market_service_edminterface{
	const TIME_SERVICE_URL='http://api.edm.shopex.cn';//定时接口外网发送地址
	const TIME_IN_URL='http://api.edm.ex-sandbox.com/';//定时接口的内网发送地址
	
	const VERSION = '1.0';
	const API_URL = 'http://api.edm.shopex.cn/';
    const API_IN_URL = 'http://api.edm.ex-sandbox.com/';
    //内网注册接口，获取服务器时间戳  http://newsms.ex-sandbox.com/sms_webapi/
	
    const OUT_RE_URL='http://api.edm.shopex.cn';//外网注册接口
	const SERVICE_URL = 'http://api.edm.ex-sandbox.com';
	
	const IN_URL ='hhttp://api.edm.ex-sandbox.com/';//邮件内网发送接口
	const OUT_URL='http://api.edm.shopex.cn';// 邮件外网发送接口
	
	const EDM_IN_URL='http://api.edm.ex-sandbox.com/';//邮件信息获取内网地址
	const EDM_OUT_URL='http://api.edm.shopex.cn';//邮件信息获取外网地址
    
	const PAYIN = "http://api.edm.ex-sandbox.com/";
	const PAYOUT = "http://webapi.edm.shopex.cn";
	const IS_DEBUG = 0; // 内网和外网调试开关
	
    var $edm_account;// 邮件帐号信息
	
	function __construct(){
        /*
        if(self::IS_DEBUG == 1) {
            $arr['entid']='311109000446';
            $arr['password'] = 'sms2011';
            base_kvstore::instance('edm')->store('account', serialize($arr));
        }*/
        base_kvstore::instance('market')->fetch('account', $arr);
        if( ! $arr){
            $this->edm_account = market_edm_utils::update_edm_kv();
        }else{
            $this->edm_account = unserialize($arr);
        }
         //对密码进行解密
        $market_edm_des = kernel::single('market_edm_des');
        if(strlen($this->edm_account['password']) > 64){
        	$this->edm_account['password'] = $market_edm_des->decrypt($this->edm_account['password']);
        }else{//兼容旧的原始密码
        	$this->edm_account['password'] = md5($this->edm_account['password'].'ShopEXUser');
        }
        
	}

	function assemble($params){
		if(!is_array($params))  return null;
		ksort($params,SORT_STRING);
		$sign = '';
		foreach($params AS $key=>$val){
			if($key!= 'certi_ac'){
				$sign .= (is_array($val) ? assemble($val) : $val);
			}
		}
		return $sign;
	}

	function get_sign($params,$token=APP_TOKEN){
		return strtolower(md5($this->assemble($params).strtolower(md5($token)) ));
	}
    
	/**
	 *AC算法
	 */
	function make_ac_str($temp_arr,$token){
		$str = '';
		ksort($temp_arr);
		foreach($temp_arr as $key=>$value){
			if($key!='certi_ac'){
				$str.=$value;
			}
		}
		return md5($str.$token);
	}

	public  function base_make_shopex_ac($arr, $token) {
		ksort($arr);
		$str = '';
		foreach ($arr as $key => $value) {
			if ($key != 'ac') {
				$str .= $value;
			}
		}
		return strtolower(md5($str . strtolower(md5($token))));
	}

	public function get_server_time() {
        $arr = $this->edm_account;
		$core_http = kernel::single('base_httpclient');
		$substr['certi_app'] = 'email.time';
        //$substr['entId']    =  '10000';
        //$substr['entPwd']   =  md5($arr['password'].'ShopEXUser');
        //$substr['entPwd']   =  $arr['password'];
        $substr['source']   =  APP_SOURCE;
		$substr['version']  = self::VERSION;
		$substr['format']   = 'json';
		$substr['certi_ac'] = $this->base_make_shopex_ac($substr,APP_TOKEN);
		$result  = $core_http->post(self::OUT_RE_URL, $substr);
		$res = json_decode( $result );
		$arr = array();
		foreach ( $res as $k => $v ) {
			$arr[$k] = $v;
		}
		return $arr['info'];
	}

	//邮件实时发送接口
    //$type : fan-out[群发]/ notice[通知]
	public function send($subject,$address,$contents, $type) {
		$arr = $this->edm_account;
        $edm_email = $this->edm_account['edm_email'];
        $contact   = $this->edm_account['contact'];
        if(empty($edm_email)){
            $edm_email = 'admin@shopex.cn';
        }
        if(empty($contact)){
            $contact = $edm_email;
        }
        $contact = $edm_email;
        $from = $edm_email .':' .$contact;
        $param = array(
            'certi_app' => 'email.send',
            'entId' => $arr['entid'],
            //'entPwd' =>md5($arr['password'].'ShopEXUser'),
            'entPwd' =>$arr['password'],
            'license' => base_certificate::get('certificate_id') ? base_certificate::get('certificate_id') : 1,
            'source' => APP_SOURCE,
            'from'   =>$from,
            'subject'=>$subject,
            'contents'=>$contents,
            'address'=>$address,
            'version' => self::VERSION,
            'format' => 'json',
            'sendType' => $type,
            'timestamp' =>$this->get_server_time(),
        );
        //print_r($param);
        //print_r('ssssssssseeeeeeee');
		$param['certi_ac'] = $this->base_make_shopex_ac($param, APP_TOKEN);
		
		//$content = json_decode($content,true);
        //ilog('send:'.count($content));return array('res'=>'succ');
        
		$http = new base_httpclient;
		if(self::IS_DEBUG == 1){
			$result  = $http->post(self::IN_URL,$param);
		}else{
			$result  = $http->post(self::OUT_URL,$param);
		}
        //print_r($result);
		return json_decode($result,TRUE);
	}
	

	//用户邮件信息获取接口
	public function useredm_info(){
		$arr = $this->edm_account;
		$param=array(
			'certi_app' => 'email.info',
			'entId' => $arr['entid'],
			//'entPwd' =>md5($arr['password'].'ShopEXUser'),
            'entPwd' =>$arr['password'],
			'source' => APP_SOURCE,
			'version' => self::VERSION,
			'format' => 'json',
			'timestamp' =>$this->get_server_time(),
		);
		$param['certi_ac'] = $this->base_make_shopex_ac($param, APP_TOKEN);
		$http = new base_httpclient;
        if(self::IS_DEBUG == 1){
			$result  = $http->post(self::API_IN_URL, $param);
		}else{
			$result  = $http->post(self::API_URL, $param);
		}
		return json_decode($result,TRUE);
	}

	//失败记录接口
	public function fail_record($msgid,$flag = false){
		$arr = $this->edm_account;
		$param=array(
			'certi_app' => 'tgcrm.send_fail_log',
			'entId' =>$arr['entid'],
	    	//'entPwd' =>md5($arr['password'].'ShopEXUser'),
            'entPwd' =>$arr['password'],
			'msgid' => $msgid,
			'page' =>1,
			'sendtime' =>'1339689600',
			'version'=>self::VERSION,
			'format'=>'json',
			'timestamp' =>$this->get_server_time(),
		);
		$param['certi_ac'] = $this->base_make_shopex_ac($param, SENDFAILLOG);
		$http = new base_httpclient;
		if(self::IS_DEBUG == 1){
			$result  = $http->post(self::TIME_IN_URL, $param);
		}else{
			$result  = $http->post(self::TIME_SERVICE_URL, $param);
		}
		return json_decode($result,TRUE);
	}
	

	public function isBind(){
	    if($this->edm_account){
	        $send_info = $this->get_usersms_info();
	        return true;
	    }else{
	        return false;
	    }
	}
}
