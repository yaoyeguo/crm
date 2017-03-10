<?php 
class market_ctl_admin_sms_log extends desktop_controller {
function index(){
        $this->finder('market_mdl_sms_log',
            array(
            'title'=>'短信模板',
            'use_buildin_recycle'=>false,
            'use_buildin_recycle' => true,
            )
        );
}




}
