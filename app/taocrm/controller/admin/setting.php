<?php
class taocrm_ctl_admin_setting extends desktop_controller{
    var $workground = 'rolescfg';

    public function index(){
        $set['method'] = $this->app->getConf('taocrm.level_point.method');
        $set['config'] = $this->app->getConf('taocrm.level_point.config');
        if(!$set['config']['normal']){
            $set['config']['normal'] = array(
                'amount' => '1',
            );
        }
        if(!$set['config']['advanced']){
            $set['config']['advanced'] = array (
                'num' => '1',
                'amount' => '1',
                'F' => array ('1','2','3','4'),
                'M' => array ('1','2','3','4'),
            );
        }
        $this->pagedata['set'] = $set;
        $this->page('admin/system/setting.html');
    }

    public function toLvSetting(){
        $this->begin('index.php?app=taocrm&ctl=admin_setting&act=index');
        $data = $_POST;
        if($data['point_type'] && $data['point_type'] == 1){
            $method = 1;
            $config['normal'] = $data['normal'];
        }else{
            $method = 0;
            $config['advanced'] = $data['advanced'];
            $config['advanced']['F'] = $data['F'];
            $config['advanced']['M'] = $data['M'];
        }
        $setting = array(
            'taocrm.level_point.method' => $method,
            'taocrm.level_point.config' => $config,
        );
        foreach($setting as $key=>$val){
            $this->app->setConf($key,$val);
        }
        $this->end(true,'操作成功');
    }
}