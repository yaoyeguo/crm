<?php
class taocrm_ctl_admin_member_contact extends desktop_controller{

    public function index()
    {
        $title = '联系人列表';
        $this->finder('taocrm_mdl_member_receivers',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'orderBy' => 'receiver_id DESC',//去掉默认排序
            //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            //'use_buildin_filter'=>true,//暂时去掉高级筛选功能
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
        ));
    }
    
}

