<?php 

class ecorder_ctl_admin_exchange_orders extends desktop_controller{

    var $workground = 'taocrm.sales';
    
    public function __construct($app){
        parent::__construct($app);
    }

    function index()
    {
        $base_filter = array();
        $this->finder('ecorder_mdl_exchange_orders',array(
            'title'=>'积分兑换订单列表',
            'base_filter'=>$base_filter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>true,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
            'orderBy' => 'create_time DESC',
        ));
    }
    
}

