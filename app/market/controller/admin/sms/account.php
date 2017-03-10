<?php
class market_ctl_admin_sms_account extends desktop_controller {
    var $workground = 'rolescfg';
    public function __construct($app) {
    	if (!defined('APP_TOKEN') || !defined('APP_SOURCE')) {
    		echo "请在Config文件中定义常量APP_TOKEN和APP_SOURCE";
    		exit;
    	}
    	parent::__construct($app);
    }

    public function index() {
    	base_kvstore::instance('market')->fetch('account', $account);
    	if (unserialize($account)) {
//    		免登			
			$param = unserialize($account);
			$url = market_sms_utils::get_book_url($param);
			$info = market_sms_utils::get_user_info($param);
			$infoedm = $this->getEmailAccount();
			$param['email'] = $infoedm['edm_email'];
			$this->pagedata['frameurl'] = $url;
			$this->pagedata['account'] = $param;
			$this->pagedata['info'] = (array)$info;
			$this->page('admin/sms/info.html');
    	}
    	elseif (market_sms_utils::hasRegister()) {
//    		已注册过一次，执行绑定
			$this->page('admin/sms/bind.html');
    	}
    	else {
//    		注册
			$this->page('admin/sms/register.html');
    	}
    }
    
    protected function getEmailAccount()
    {
        $send = kernel::single('market_service_edminterface');
        $account = $send->useredm_info();
        return $account['info']['account_info'];
    }
    
    public function register() {
    	$region = $_POST['region'];
    	$region = explode(':', $_POST['region']);
    	$regionArr = explode('/', $region[1]);
		
    	$this->begin('index.php?app=market&ctl=admin_sms_account');
    	if (!trim(trim($_POST['email']))) {
    		$this->end(false, 'email不能为空！');
    	}
    	
    	if (!trim($_POST['password'])) {
    		$this->end(false, '密码不能为空！');
    	}
    	
        if (trim($_POST['confirm']) != trim($_POST['password'])) {
    		$this->end(false, '两次输入密码不一致！');
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
    	$param['email'] = trim($_POST['email']);
    	$param['entPwd'] = trim($_POST['password']);
    	$param['source'] = APP_SOURCE;
//    	$param['mobile'] = trim($_POST['mobile']);
    	$param['owner'] = trim($_POST['owner']);
    	$param['biz_user'] = trim($_POST['wangwang']);
    	$param['biz_paipai'] = trim($_POST['paipai']);
    	$param['tel'] = trim($_POST['tel']);
    	$param['province'] = market_sms_utils::province_mapping($regionArr[0]);
    	$param['city'] = $regionArr[1];
    	$param['address'] = trim($_POST['address']);
    	$param['postalcode'] = trim($_POST['postcode']);
    	$result = market_sms_utils::register($param);
    	if ('succ' == $result->res) {
    		$info = $result->info;
    		$data = array(
    			'entid' => $info->entid,
    			'password' => $param['entPwd'],
    			'email' => $info->email,
    			'state' => 1
    		);
			base_kvstore::instance('market')->store('account', serialize($data));
			base_kvstore::instance('sms')->store('hasRegister', 1);
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
    		$this->end(true, '注册成功！');
    	}else {   		
    		$msg = market_sms_utils::registerErrorMsg($result->msg);
    		$this->end(false, $msg ? $msg : '注册失败！');
    	}
    }
	
	public function bind() {
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
    	$this->end(false, '帐号或密码错误！'.var_export($result));
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
    
    //查询短信和edm的账户余额
    public function get_amount()
    {
        $amount['sms_amount'] = $amount['edm_amount'] = '未绑定';

        base_kvstore::instance('market')->fetch('account', $kv_account);
        $kv_account = unserialize($kv_account);
        if($kv_account){
            $res = kernel::single('market_service_smsinterface')->get_usersms_info();
            if($res['res']!='fail'){
                if(isset($res['info']['month_residual']))
                    $amount['sms_amount'] = $res['info']['all_residual'] - $res['info']['block_num'];
            }else{
                $amount['sms_amount'] = $res['msg'];
            }
                
            $res = kernel::single('market_service_edminterface')->useredm_info();
            if($res['res']!='fail'){
                if(isset($res['info']['month_residual']))
                    $amount['edm_amount'] = $res['info']['all_residual'] - $res['info']['block_num'];
            }else{
                $amount['edm_amount'] = $res['msg'];
            }
        }
        
        echo(json_encode($amount));
    }
}
