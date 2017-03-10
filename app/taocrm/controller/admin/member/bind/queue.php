<?php
class taocrm_ctl_admin_member_bind_queue extends desktop_controller{
    var $workground = 'taocrm.all_member';

    public function index()
    {
        $title = '合并客户队列列表';
        $actions = array();
        $baseFilter = array();

        //$actions = $this->_action();

        $this->finder(
            'taocrm_mdl_member_bind_queue',
            array(
                'title'=> $title,
                'actions' => $actions,
                'base_filter'=>$baseFilter,
                'orderBy' => '',//去掉默认排序
                //'use_buildin_set_tag'=>true,
                'use_buildin_import'=>false,
                'use_buildin_export'=>false,
                'use_buildin_recycle'=>false,
                //'use_buildin_filter'=>true,//暂时去掉高级筛选功能
                'use_buildin_tagedit'=>true,
                'use_view_tab'=>true,
            )
        );
    }

}
