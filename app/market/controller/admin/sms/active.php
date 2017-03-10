<?php
class market_ctl_admin_sms_active extends desktop_controller {

    var $workground = 'rolescfg';

    public function __construct($app) {
    	if (!defined('APP_TOKEN') || !defined('APP_SOURCE')) {
    		echo "请在Config文件中定义常量APP_TOKEN和APP_SOURCE";
    		exit;
    	}
    	parent::__construct($app);
    }

    public function index()
    {
    	base_kvstore::instance('market')->fetch('account', $account);
		$change = intval($_GET['change']);
    	if (unserialize($account) && $change==0) {
			//免登
			$param = unserialize($account);
			$url = market_sms_utils::get_book_url($param);
			$info = market_sms_utils::GetEnterpriseByMobile($param);
			if ($info->info->active != 1) {
                $param['password'] = $info->info->password;
                $result = market_sms_utils::activeShopexid($param);
			    if ($result->res == 'succ') {
			        $param['password'] = $info->info->password;
			        $param['state'] = 1;
			        base_kvstore::instance('market')->store('account', serialize($param));
			    }
			}
			//var_dump($info);
			$this->pagedata['frameurl'] = $url;
			$this->pagedata['account'] = $param;
			$this->pagedata['info'] = (array)$info;
			$this->page('admin/sms/info_active.html');
    	}
    	/*elseif (market_sms_utils::hasRegister()) {
			//已注册过一次，执行绑定
			$this->page('admin/sms/bind.html');
    	}*/
    	else {
			//注册
			if($_GET['init'] == '1'){
				$this->singlepage('admin/sms/active.html');
			}else{
				$this->page('admin/sms/active.html');
			}
    	}
    }

	//创建并发送验证码
	public function send_passcode(){

		$mobile = $_POST['mobile'];
		if( !$mobile ) {
			$result = '请输入手机号码';
		}elseif( !preg_match("/^(1)\d{10}$/",$mobile) ) {
			$result = '手机号码格式错误，请重新输入。';
		}else{
			$kvstore = base_kvstore::instance('market');
			$kvstore->fetch('passcode', $kv_passcode);
			if($kv_passcode) {
				$kv_passcode = json_decode($kv_passcode, 1);
			}

			if(isset($kv_passcode['create_time']) && $mobile==$kv_passcode['mobile'] && (time() - $kv_passcode['create_time'] < 600)){
				die('10分钟内只能申请一次验证码，请稍后再试。');
			}

			$passcode = rand(1000,9999).date('s');
			$create_time = time();
			$kv_passcode = array(
				'mobile' => $mobile,
				'passcode' => $passcode,
				'create_time' => $create_time,
			);
			$kvstore->store('passcode', json_encode($kv_passcode));

			$sms_content = "尊敬的用户，以下是您的ShopEx CRM激活码（6位数字）:{$passcode}。请在一小时内用此激活码进行验证。【商派云CRM】";
			$sms[] = array(
				'phones' => $mobile,
				'content'=>$sms_content
			);
			$data = kernel::single('market_service_smsinterface')->send_passcode(json_encode($sms));
			if($data['res'] == 'succ'){
				$result = '验证码已经发送到:'.$mobile;
			}else{
				$result = var_export($data,1);
			}
			//$result = var_export($data,1);
		}
		echo ($result);
	}

	//核对验证码
	public function check_passcode()
    {
        $check = 1;

		$mobile = $_POST['mobile'];
		$passcode = $_POST['passcode'];

		$kvstore = base_kvstore::instance('market');
		$kvstore->fetch('passcode', $kv_passcode);
		if($kv_passcode) {
			$kv_passcode = json_decode($kv_passcode, 1);
			if($kv_passcode['mobile'] != $mobile){
				if($check==1) die('手机号码不匹配:'.$kv_passcode['mobile']);
			}
			if($kv_passcode['passcode'] != $passcode){
				if($check==1) die('验证码错误，请重新输入。');
			}
			if(isset($kv_passcode['create_time']) && (time() - $kv_passcode['create_time'] > 3600)){
				if($check==1) die('验证码过期，请重新申请。');
			}
		}else{
			if($check==1) die('请先点击获取验证码。');
		}

		$arr = array(
			'mobile'=>$mobile,
			'passcode'=>$passcode
		);

		//检测手机号码是否已经存在
		$result = $this->chk_mobile($arr);
		if($result != 'succ'){
			//注册短信平台帐号
			$result = $this->register($arr);
			if($result == 'succ'){
				$this->chk_mobile($arr);
			}
		}

		//重新定向到短信平台
		echo($result);
	}

	public function chk_mobile(&$arr) {
    	$param = array();
		$param['mobile'] = $arr['mobile'];
    	$result = market_sms_utils::GetEnterpriseByMobile($param);
		//return var_export($result,1);
    	if ('succ' == $result->res) {
    		$info = $result->info;
    		$data = array(
    			'entid' => $info->entid,
    			'mobile' => $arr['mobile'],
    			'password' => $info->password,
    			'state' => 1
    		);
			base_kvstore::instance('market')->store('account', serialize($data));
			base_kvstore::instance('market')->store('hasRegister', 1);
	    	base_kvstore::instance('market')->fetch('present', $present);
			if ('yes' !== $present && defined('SMS_PRESENT_ID')) {
			    base_kvstore::instance('sms')->fetch('account', $account);
		    	if (!unserialize($account)) {
		    		return false;
		    	}
				$param = unserialize($account);
				$result = market_sms_utils::buy($param, SMS_PRESENT_ID, time());

				if ('succ' == $result->res) {
					base_kvstore::instance('sms')->store('present', 'yes');
				}
			}

            market_edm_utils::update_edm_kv();//存储edm平台的帐号和密码

    		$res = 'succ';
    	}else {
    		$msg = market_sms_utils::registerErrorMsg($result->info);
    		$res = 'fail:'.$msg;
    	}
		return $res;
    }

    public function register(&$arr) {
    	$param = array();
    	$param['email'] = '';
    	$param['entPwd'] = $arr['mobile'];
    	$param['source'] = APP_SOURCE;
		$param['mobile'] = $arr['mobile'];
    	$param['owner'] = '';
    	$param['active_code'] = substr($arr['mobile'],-4);
    	$param['biz_user'] = '';
    	$param['biz_paipai'] = '';
    	$param['tel'] = $arr['mobile'];
    	$param['province'] = market_sms_utils::province_mapping('上海');
    	$param['city'] = '上海市';
    	$param['address'] = '23';
    	$param['postalcode'] = '200000';
    	$result = market_sms_utils::register($param);
    	if ('succ' == $result->res) {
    		$info = $result->info;
    		$data = array(
    			'entid' => $info->entid,
    			'password' => $param['entPwd'],
    			'email' => $info->email,
				'mobile' => $arr['mobile'],
    			'state' => 1
    		);
			base_kvstore::instance('market')->store('account', serialize($data));
			base_kvstore::instance('market')->store('hasRegister', 1);
	    	base_kvstore::instance('market')->fetch('present', $present);
			if ('yes' !== $present && defined('SMS_PRESENT_ID')) {
			    base_kvstore::instance('sms')->fetch('account', $account);
		    	if (!unserialize($account)) {
		    		return false;
		    	}
				$param = unserialize($account);
				$result = market_sms_utils::buy($param, SMS_PRESENT_ID, time());

				if ('succ' == $result->res) {
					base_kvstore::instance('sms')->store('present', 'yes');
				}
			}
    		$res = 'succ';
    	}else {
    		$msg = market_sms_utils::registerErrorMsg($result->info);
    		$res = '激活失败：'.var_export($result);
    	}
		return $res;
    }

	public function bind()
    {
	    $this->begin('index.php?app=market&ctl=admin_sms_account');
    	if (!trim(trim($_POST['bindemail']))) {
    		$this->end(false, 'email不能为空！');
    	}
    	if (!trim($_POST['bindpassword'])) {
    		$this->end(false, '密码不能为空！');
    	}

    	$param = array();
    	$param['identifier'] = trim($_POST['bindemail']);
    	$param['password'] = trim($_POST['bindpassword']);
    	$result = market_sms_utils::login($param);
    	if ('succ' == $result->res) {
    		$data = array(
    			'entid' => $result->info->entid,
    			'password' => trim($_POST['bindpassword']),
    			'email' => $result->info->email,
    			'status' => 1
    		);
    		base_kvstore::instance('market')->store('account', serialize($data));
    		$this->end(true, $result->res);
    	}
    	$this->end(false, '帐号或密码错误！');
	}

	public function unbind() {
		$this->begin('index.php?app=market&ctl=admin_sms_account');
		base_kvstore::instance('market')->store('account', serialize(array()));
		$this->end(true, '操作成功！');
	}

	public function buy() {
		base_kvstore::instance('sms')->fetch('present', $present);
		if ('yes' !== $present) {
		    base_kvstore::instance('sms')->fetch('account', $account);
	    	if (!unserialize($account)) {
	    		return false;
	    	}

			$param = unserialize($account);
			$result = market_sms_utils::buy($param, SMS_PRESENT_ID, time());
			if ('succ' == $result->res) {
				base_kvstore::instance('sms')->store('present', 'yes');
			}
		}
		else {
			echo "已赠送!";
		}
	}

	public function blacklist() {
		$result = market_sms_utils::update_blacklist();
	}

    public function checkBannedKeywords() {
        if (isset($_POST['smscontent'])) {
            $rs = market_sms_utils::is_banned_keywords(trim($_POST['smscontent']));
            $this->pagedata['smscontent'] = trim($_POST['smscontent']);
            $this->pagedata['bannedwords'] = (false == $rs) ? '' : $rs;
        }
        $this->page('admin/checkBannedKeywords.html');
    }
}
