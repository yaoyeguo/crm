<?php

class market_ctl_admin_callcenter_cti extends desktop_controller{
    
    public function set()
    {
        $cti['bird'] = kernel::single('market_cti_bird')->get_info();
        $cti['ronghe'] = kernel::single('market_cti_ronghe')->get_info();
        
        base_kvstore::instance('market')->fetch('cti_type', $cti_type);
        
        $this->pagedata['base_url'] = kernel::base_url(true);
        $this->pagedata['cti_type'] = $cti_type;
        $this->pagedata['cti'] = $cti;
        $this->page('admin/callcenter/cti/set.html');
    }
    
    public function dialog()
    {
        if($_POST){
            $this->save_conf();
            exit;
        }
        
        base_kvstore::instance('market')->fetch('cti_conf:'.kernel::single('desktop_user')->get_id(), $cti_conf);
        if($cti_conf) $cti_conf = json_decode($cti_conf, true);
        
        $cti_type = $_GET['cti_type'];
        $this->pagedata['cti_conf'] = $cti_conf;
        $this->pagedata['cti_type'] = $cti_type;
        $this->display('admin/callcenter/cti/dialog/'.$cti_type.'.html');
    }
    
    public function save_conf()
    {
        $url = 'index.php?app=market&ctl=admin_callcenter_cti&act=set';
        $this->begin($url);
        
        $cti_conf = $_POST['cti_conf'];
        $cti_type = $_POST['cti_type'];
        base_kvstore::instance('market')->store('cti_type', $cti_type);
        base_kvstore::instance('market')->store('cti_conf:'.kernel::single('desktop_user')->get_id(), json_encode($cti_conf));
        
        $this->end(true,'保存成功');
    }
}