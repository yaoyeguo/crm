<?php
class taocrm_ctl_admin_shop extends desktop_controller{
    var $workground = 'rolescfg';

    public function index(){
        $certi = base_certificate::get('certificate_id');
        $node_id = base_shopnode::node_id('taocrm');
        //$title = '前端店铺管理(证书：'.$certi.'&nbsp;&nbsp;节点：'.$node_id.')';
        $title = '前端店铺管理';

        $this->finder(ORDER_APP.'_mdl_shop',array(
            'title' => $title,
            'actions'=>array(
                array('label'=>'添加店铺','href'=>'index.php?app='.ORDER_APP.'&ctl=admin_shop&act=addterminal&finder_id='.$_GET['finder_id'],'target'=>'_blank'),
                array('label'=>'查看绑定关系','href'=>'index.php?app='.ORDER_APP.'&ctl=admin_shop&act=view_bindrelation','target'=>'_blank'),
            ),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => true,
            'use_buildin_export' => false,
            'use_buildin_import' => false,
        ));
    }
}