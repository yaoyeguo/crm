<?php

class taocrm_ctl_admin_member_weixin extends desktop_controller{

    var $workground = 'taocrm.member';

    public function index()
    {
        $this->redirect('index.php?app=market&ctl=admin_weixin&act=openId');
    }

}

