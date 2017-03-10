<?php
class taocrm_ctl_admin_queue extends desktop_controller{
    var $workground = 'taocrm.member';

    public function index(){
        $this->finder('taocrm_mdl_queue',array(
            'title'=>'队列',
            'finder_cols' => 'queue_title,status,worker,start_time,worker_active',
        )
        );
    }

}
