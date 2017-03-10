<?php
class market_ctl_admin_edm_active extends desktop_controller {

    var $workground = 'rolescfg';

    public function __construct($app) {
    	if (!defined('APP_TOKEN') || !defined('APP_SOURCE')) {
    		echo "请在Config文件中定义常量APP_TOKEN和APP_SOURCE";
    		exit;
    	}
    	parent::__construct($app);
    }

	public function index() {
		//首先判断是否绑定过邮箱
		base_kvstore::instance('market')->fetch('account',$edms);
		if(unserialize($edms)){  //如果已经绑定过邮件
		   	//免登陆
			$edms = unserialize($edms);
			//对密码进行解密
			$market_edm_des = kernel::single('market_edm_des');
			if(strlen($edms['password']) > 64){
				$edms['password'] = $market_edm_des->decrypt($edms['password']);
			}else{//兼容旧的原始密码
				$edms['password'] = md5($edms['password'].'ShopEXUser');
			}
			$info = market_sms_utils::GetEnterpriseByMobile($edms);	  
			$info = json_decode($info, true);
			$edm = kernel::single('market_service_tempinterface');
			$data = $edm->check($edms);
			if($data['res'] == 'succ'){
				$this->pagedata['frameurl'] = $data['info']['mbt_url'];
				$this->pagedata['account'] = $edms;
				$this->pagedata['info'] = (array)$info;
				$this->page('admin/edm/template_info.html');
		   	}else{
		   		
			}
		}else if(market_edm_utils::hasRegister()){   //已注册过一次，执行绑定
			$sms_conf_url = 'index.php?app=market&ctl=admin_sms_active&act=index';
			die('<script>window.location.href="'.$sms_conf_url.'";</script>');
			$this->page('admin/edm/bind.html');
		}else{  
			$sms_conf_url = 'index.php?app=market&ctl=admin_sms_active&act=index';
			die('<script>window.location.href="'.$sms_conf_url.'";</script>');
			$this->page('admin/edm/register.html');
		}
	}
}
