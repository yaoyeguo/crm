<?php
class market_ctl_admin_edm_account extends desktop_controller {
    var $workground = 'rolescfg';
    public function __construct($app) {
    	if (!defined('APP_TOKEN') || !defined('APP_SOURCE')) {
    		echo "请在Config文件中定义常量APP_TOKEN和APP_SOURCE";
    		exit;
    	}
    	parent::__construct($app);
    }

    public function index() {
    
        market_edm_utils::update_edm_kv();
    
        //首先判断是否绑定过邮箱
        base_kvstore::instance('market')->fetch('account',$edms);
        if(unserialize($edms)){  //如果已经绑定过邮件
               //免登陆
               $edms = unserialize($edms);
               
               //$info = market_edm_utils::edm_user_info($edms);
			   //---------------new
			   $info = market_sms_utils::GetEnterpriseByMobile($edms);
			   
			   $info = json_encode($info);
			   $info = json_decode($info, 1);
			   
			   $url = market_edm_utils::get_book_url($info['info']);
			   
			   //var_dump($edms);
			   
			   //---------------end
			   /*
               if($info['res'] == 'succ'){
                   $edms['edm_email'] = $info['info']['account_info']['edm_email'];
                   $edms['contact'] = $info['info']['account_info']['contact'];
                   $edms['mobile'] = $info['info']['account_info']['mobile'];
                   base_kvstore::instance('market')->store('account', serialize($edms));
               }
			   */
			   
               $this->pagedata['frameurl'] = $url;
               $this->pagedata['account'] = $edms;
               $this->pagedata['info'] = (array)$info;
               $this->page('admin/edm/info.html');
        }else if(market_edm_utils::hasRegister()){   //已注册过一次，执行绑定
			$sms_conf_url = 'index.php?app=market&ctl=admin_sms_active&act=index';
			die('<script>window.location.href="'.$sms_conf_url.'";</script>');
			$this->page('admin/edm/bind.html');
               //$this->page('admin/edm/info.html');
        }else{  //未绑定过邮件
        		/*
        		base_kvstore::instance('market')->fetch('account', $account);  //检测是否绑定短信
                if (unserialize($account)) {  //已经绑定过短信
                    // 免登
                    $param = unserialize($account);
                    $accs = market_edm_utils::shopex_user_get($param);  //获取商家用户中心的账户信息
                    $this->pagedata['account'] = $accs;
                    $this->page('admin/edm/register.html');
                }
                else {
                    //注册
                    $this->page('admin/edm/register.html');
                }
                */
			$sms_conf_url = 'index.php?app=market&ctl=admin_sms_active&act=index';
			die('<script>window.location.href="'.$sms_conf_url.'";</script>');
        	$this->page('admin/edm/register.html');
        }
        
    }
    
    /*
    * 打接口注册
    * 商家用户中心接口
    */
    public function register() {
	    $this->begin('index.php?app=market&ctl=admin_edm_account');
    	$region = $_POST['region'];
    	$region = explode(':', $_POST['region']);
    	$regionArr = explode('/', $region[1]);
    	if (!trim(trim($_POST['email']))) {
    		$this->end(false, 'email不能为空！');
    	}
    	
    	if (!trim($_POST['password'])) {
    		$this->end(false, '密码不能为空！');
    	}
    	
        if (trim($_POST['confirm']) != trim($_POST['password'])) {
    		$this->end(false, '两次输入密码不一致！');
    	}
    	
    	if (!trim($_POST['company'])) {
   		    $this->end(false, '公司名称不能为空！');
    	}
    	
    	if (!trim($_POST['owner'])) {
    		$this->end(false, '联系人不能为空！');
    	}
    	
    	if (!trim($_POST['wangwang']) && !trim($_POST['paipai'])) {
    		$this->end(false, '旺旺号和拍拍号必须输入一个！');
    	}
    	
    	if (!$_POST['tel']) {
    		$this->end(false, '联系电话不能为空！');
    	}
    	
    	if (!$regionArr[0] || !$regionArr[1]) {
    		$this->end(false, '请选择所属地区！');
    	}
    	
    	if (!trim($_POST['address'])) {
    		$this->end(false, '详细地址不能为空！');
    	}
    	
    	if (!trim($_POST['postcode'])) {
   			$this->end(false, '邮编不能为空！'); 		
    	}
    	
    	$param = array();
    	$param['username'] = trim($_POST['email']);
    	$param['password'] = trim($_POST['password']);
    	//$param['source'] = 'shopex_tcrm';
        $param['source'] = 'shopex_sms_email';
        $param['usertype'] = '0';
        $param['enttype'] = '1';
    	$param['owner'] = trim($_POST['owner']);
        $param['trade'] = '42';
        $param['company'] = $_POST['company'];
        $param['email'] = trim($_POST['email']);
    	$param['biz_user'] = trim($_POST['wangwang']);
    	$param['biz_paipai'] = trim($_POST['paipai']);
    	$param['tel'] = trim($_POST['tel']);
    	$param['province'] = market_edm_utils::province_mapping($regionArr[0]);
    	$param['city'] = $regionArr[1];
    	$param['address'] = trim($_POST['address']);
    	$param['postalcode'] = trim($_POST['postcode']);

    	$result = market_edm_utils::register($param);
    	if ($result) {
    		$data = array(
    			'entid' => $result['entid'],
                'email' => $result['email'],
    			'password' => $param['password'],
                'company' => $param['company'],
                'owner' => $param['owner'],
                'postalcode' => $param['postalcode'],
    			'status' => 1
    		);
			base_kvstore::instance('market')->store('account', serialize($data));
			base_kvstore::instance('edm')->store('hasRegister', 1);
    		$this->end(true, '注册成功，请绑定！');
    	}else {
    		$msg = market_edm_utils::registerErrorMsg($result['msg']);
    		$this->end(false, $msg ? $msg : '注册失败！');
    	}
    }
	
    /*
    *绑定邮件
    */
	public function bind() {
	    $this->begin('index.php?app=market&ctl=admin_edm_account');
    	if (!trim(trim($_POST['bindemail']))) {
    		$this->end(false, 'email不能为空！');
    	}
    	if (!trim($_POST['bindpassword'])) {
    		$this->end(false, '密码不能为空！');
    	}
        /*if (!trim($_POST['edm_email'])) {
    		$this->end(false, '发件箱不能为空！');
    	}
        if (!trim($_POST['company'])) {
    		$this->end(false, '公司名称不能为空！');
    	}
        if (!trim($_POST['contact'])) {
    		$this->end(false, '联系人不能为空！');
    	}
        if (!trim($_POST['cell'])) {
    		$this->end(false, '邮编不能为空！');
    	}*/
    	$param=$_POST;
        //登陆商家用户接口验证是否正确
        $users = market_edm_utils::shopex_user_login($param);
        if($users){
            /*$param['entid'] = $users['shopexid'];
            $param['source'] = APP_SOURCE;
            $token = APP_TOKEN;
            $result = market_edm_utils::email_user_create($param,$token);
            if ('succ' == $result['res']) {
                $data = array(
                    'entid' => $result['info']['entid'],
                    'password' => $param['bindpassword'],
                    'email' => $param['bindemail'],
                    //'edm_email' => $result['info']['default_email'],
                    'company' => $param['company'],
                    'owner' => $param['contact'],
                    'postalcode' => $param['cell'],
                    'status' => 1
                );
                base_kvstore::instance('edm')->store('account', serialize($data));
                $this->end(true, $result['info']);
                */
             $data['entid'] = $users['shopexid'];
             $data['email'] = $param['bindemail'];
             $data['password'] = $param['bindpassword'];
             $data['status'] = 1;
             base_kvstore::instance('market')->store('account', serialize($data));
             $this->end(true, $data);
            
        }else{
            $this->end(false, '帐号或密码错误！');
        }
        
	}
	
    /*解绑邮件账号*/
	public function unbind() {
        $this->begin('index.php?app=market&ctl=admin_edm_account');
        base_kvstore::instance('market')->store('account', serialize(array()));
        base_kvstore::instance('edm')->store('account', serialize(array()));
		$this->end(true, '操作成功！');
	}

    /*
    *重新绑定邮箱账号
    */
    public function re_bind(){
        $this->begin('index.php?app=market&ctl=admin_edm_account');
        if (!trim(trim($_POST['bindemail']))) {
    		$this->end(false, 'email不能为空！');
    	}
    	if (!trim($_POST['bindpassword'])) {
    		$this->end(false, '密码不能为空！');
    	}
        $param = array();
        $param['email'] = $_POST['bindemail'];
        $param['password'] = $_POST['bindpassword'];
        $users = market_edm_utils::shopex_user_get($param);  //登陆商家用户中心成功
        $data =array();
        if($users){
            $data = array(
                    'entid' => $users['entid'],
                    'password' => $param['password'],
                    'email' => $param['email'],
                    'company' => $users['company'],
                    'owner' => $users['owner'],
                    'postalcode' => $users['postalcode'],
                    'status' => 1
                );
             $param['entid'] = $users['entid'];
             $edms = market_edm_utils::edm_user_info($param);
             base_kvstore::instance('market')->store('account', serialize($data));
             $url = market_edm_utils::get_book_url($data);
             $this->pagedata['frameurl'] = $url;
             $this->pagedata['account'] = $data;
             $this->pagedata['info'] = (array)$edms;
             $this->page('admin/edm/info.html');

             /*if($edms['res'] == 'succ'){
                base_kvstore::instance('edm')->store('account', serialize($data));
                $url = market_edm_utils::get_book_url($data);
                $this->pagedata['frameurl'] = $url;
                $this->pagedata['account'] = $data;
                $this->pagedata['info'] = (array)$edms;
                $this->page('admin/edm/info.html');
             }else{
                //base_kvstore::instance('edm')->store('account', serialize(array()));
                $this->end(false,'此企业账号未开通邮件服务！');
             }*/
        }else{
            $this->end(false,'帐号或密码错误！');
        }
    }
}