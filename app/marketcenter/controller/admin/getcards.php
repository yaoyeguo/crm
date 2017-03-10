<?php
class marketcenter_ctl_admin_getcards extends desktop_controller {
    var $workground = 'marketcenter.workground.setting';
    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    function index() {
        $params = array (
            'title' => app::get('desktop')->_('卡劵领取列表'),
            'use_buildin_recycle' => false,
            'use_buildin_refresh' => true,
            'actions' => array(),
        );
        $this->finder('marketcenter_mdl_get_cards', $params);
        $this->page('getcards/index.html');
    }
}