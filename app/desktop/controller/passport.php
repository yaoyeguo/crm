<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_ctl_passport extends desktop_controller{

	var $login_times_error=3;


	public function __construct($app)
	{
		parent::__construct($app);
		header("cache-control: no-store, no-cache, must-revalidate");
	}

	function index(){
		 
		$objSystem = kernel::single('taocrm_system');
		$objSystem->installInit();

		//更新用户登录状态
		if(strstr($_SERVER['SERVER_NAME'],'.crm.taoex.com')){
			$product = 'crm';
		}else{
			$product = 'mcrm';
		}
		base_kvstore::instance('market')->fetch('account', $account);
		$sms_account = unserialize($account);
		$ww = $shop_name = array();
		$rs = app::get('ecorder')->model('shop')->getList();
		foreach($rs as $v){
			$config = unserialize($v['config']);
			$shop_name[] = $v['name'];
			$ww[] = $config['account'];
		}
		$arr = array(
            'domain' => $_SERVER['SERVER_NAME'],
            'product' => $product,
            'version' => '2013',
            'qq' => '',
            'ww' => implode(';', $ww),
            'mobile' => $sms_account['mobile'],
            'shop_name' => implode(';', $shop_name),
            'remark' => '',
            'ip' => $_SERVER['REMOTE_ADDR'],
		);
		//$http = new base_httpclient;
		//$http->post('http://monitor.crmm.taoex.com/index.php/openapi/taocrm.customer/add/',$arr);

		//免登
		$params = json_decode(urldecode($_GET['params']), true);
		if ($params['saas_params'] && $params['saas_appkey'] && $params['saas_ts'] && $params['saas_sign']) {
			$params['type'] = pam_account::get_account_type($this->app->app_id);
			foreach (kernel::servicelist('login_trust') as $service) {
				if ($service->login($params)) {
					$this->redirect('index.php');
					exit();
				}
			}
		}

		/** 登录之前的预先验证 **/
		$obj_services = kernel::servicelist('app_pre_auth_use');
		foreach ($obj_services as $obj){
			if (method_exists($obj, 'pre_auth_uses') && method_exists($obj, 'login_verify')){
				$this->pagedata['desktop_login_verify'] = $obj->login_verify();
			}
		}
		/** end **/

		$auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
		$auth->set_appid($this->app->app_id);
		$auth->set_redirect_url($_GET['url']);
		$this->pagedata['desktop_url'] = kernel::router()->app->base_url(1);
		$this->pagedata['cross_call_url'] =base64_encode( kernel::router()->app->base_url(1).
		'index.php?ctl=passport&act=cross_call'
		);

        $pagedata['mobile'] = kernel::single('market_cti')->get_mobile($params);
		$conf = base_setup_config::deploy_info();
		foreach(kernel::servicelist('passport') as $k=>$passport){
			if($auth->is_module_valid($k,$this->app->app_id)){
				$this->pagedata['passports'][] = array(
                        'name'=>$auth->get_name($k)?$auth->get_name($k):$passport->get_name(),
                        'html'=>$passport->get_login_form($auth,'desktop','basic-login.html',$pagedata),     
				);
			}
		}
		/*
		 $server_name = $_SERVER['SERVER_NAME'];
		 $tt_obj = memcache_connect(SERVER_TT_HOST, SERVER_TT_PORT);
		 $preFix = md5(md5(sprintf('%s_%s', $server_name, SERVICE_IDENT)));
		 $data = unserialize(memcache_get($tt_obj, $preFix));
		 $this->pagedata['username'] = $data['USERNAME'];
		 $this->pagedata['time'] = time();
		 */
		$this->pagedata['product_key'] = $conf['product_key'];
		$this->display('login.html');
	}

	function gen_vcode(){
		$vcode = kernel::single('base_vcode');
		$vcode->length(4);
		$vcode->verify_key($this->app->app_id);
		$vcode->display();
	}

	function cross_call(){
		header('Content-Type: text/html;charset=utf-8');
		echo '<script>'.base64_decode($_REQUEST['script']).'</script>';
	}

	function logout($backurl='index.php'){
		$this->begin('javascript:Cookie.dispose("basicloginform_password");Cookie.dispose("basicloginform_autologin");
					   location="'.kernel::router()->app->base_url(1).'"');
		$this->user->login();
		$this->user->logout();
		$auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
		foreach(kernel::servicelist('passport') as $k=>$passport){
			if($auth->is_module_valid($k,$this->app->app_id))
			$passport->loginout($auth,$backurl);
		}
		kernel::single('base_session')->destory();
		$this->end('true',app::get('desktop')->_('已成功退出系统,正在转向...'));
		/* $this->redirect('');*/

	}
	 

}
