<?php
class market_ctl_admin_coupon_sent extends desktop_controller{
    var $workground = 'taocrm.sales';

    public function index(){
        $this->finder('market_mdl_coupon_sent',array(
            'title'=>'优惠券发送列表',
            'use_buildin_recycle'=>false,
        ));
    }
}