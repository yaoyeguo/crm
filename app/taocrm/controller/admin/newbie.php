<?php
class taocrm_ctl_admin_newbie extends desktop_controller{

	public function index()
    {
        //测试代码
        base_kvstore::instance('newbie')->store('bind_shop','');
    
        $init_step = 1;
        $sms_bind = 0;
        
        $db = kernel::database();
        $sql = 'select * from sdb_ecorder_shop';
        $rs = $db->selectrow($sql);
        if($rs) $init_step = 2;
        
        if($init_step == 1) {
            $shop_id = md5(date('YmdHis').$_SERVER['HTTP_HOST']);//自动创建shop_id
            $callback_url = (kernel::openapi_url('openapi.ome.shop','shop_callback',array('shop_id'=>$shop_id)));
            $api_url = ("http://".$_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api");
            
            //解决ie兼容性问题
            if(stristr($_SERVER['HTTP_USER_AGENT'],'MSIE')){
                $callback_url = urlencode($callback_url);
                $api_url = urlencode($api_url);
            }
            
            $license_iframe = $this->apply_bindrelation('taocrm', $callback_url, $api_url);
        }
        
        base_kvstore::instance('market')->fetch('account', $account);
        $account = unserialize($account);
        if($account['entid']) $sms_bind = 1;
        
        $this->pagedata['sms_bind'] = $sms_bind;
        $this->pagedata['init_step'] = $init_step;
        $this->pagedata['license_iframe'] = $license_iframe;
        $this->display('admin/newbie/step1.html');
	}
    
    private function apply_bindrelation($app_id='ome', $callback='', $api_url='') 
    {
        $this->Certi = base_certificate::get('certificate_id');
        $this->Token = base_certificate::get('token');
        $this->Node_id = base_shopnode::node_id($app_id);
        $token = $this->Token;
        $sess_id = kernel::single('base_session')->sess_id();
        $apply['certi_id'] = $this->Certi;
        if ($this->Node_id)
        $apply['node_id'] = $this->Node_id;
        $apply['sess_id'] = $sess_id;
        $str = '';
        ksort($apply);
        foreach ($apply as $key => $value) {
            $str.=$value;
        }
        $apply['certi_ac'] = md5($str . $token);

        $bind_type = '';
        if(strstr($_SERVER['SERVER_NAME'], '.mcrm.taoex.com')){
            $bind_type = 'taobao';
        }

        $license_iframe = '<iframe width="100%" frameborder="0" height="290" id="iframe"  src="' . MATRIX_RELATION_URL . '?source=apply&certi_id=' . $apply['certi_id'] . '&node_id=' . $apply['node_id'] . '&sess_id=' . $apply['sess_id'] . '&certi_ac=' . $apply['certi_ac'] . '&callback=' . $callback . '&api_url=' . $api_url . '&bind_type='.$bind_type.'" ></iframe>';
        return $license_iframe;
    }
    
    public function ajax_chk_bind_shop()
    {
        base_kvstore::instance('newbie')->fetch('bind_shop',$bind_shop);
        echo($bind_shop);
    }
    
    public function ajax_get_shop()
    {
        $db = kernel::database();
        $sql = 'select * from sdb_ecorder_shop ';
        $rs = $db->selectrow($sql);
        $rs['config'] = unserialize($rs['config']);
        base_kvstore::instance('market')->fetch('account', $account);
        $account = unserialize($account);
        if( ! $rs['mobile']) $rs['mobile'] = $account['mobile'];
        echo(json_encode($rs));
    }
    
    public function ajax_save_shop()
    {
        $shop = array(
            'addr' => trim($_POST['addr']),
            'zip' => trim($_POST['zip']),
            'default_sender' => trim($_POST['default_sender']),
            'mobile' => trim($_POST['mobile']),
            'tel' => trim($_POST['tel']),
        );
        
        $shop_id = trim($_POST['shop_id']);
    
        $oShop = &app::get('ecorder')->model('shop');
        $oShop->update($shop,array('shop_id'=>$shop_id));
        echo('succ');
    }
    
 	//域名7天内到期提醒
    public function expired_notice(){
    	$this->pagedata['day'] = $_GET['day'];
    	$this->display('admin/newbie/expired_notice.html');
    }
}

