<?php

class market_ctl_admin_callcenter_calllog extends desktop_controller{

    var $pagelimit = 10;
    var $is_debug = false;

    public function index()
    {
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach ((array)$shopList as $v){
            $shops[]=$v['shop_id'];
        }
        if ($_GET['view']){
            $view=($_GET['view']-1);
            $shop_id=$shops[$view];
        }
        
        $actions = array(
            array(
                'label'=>'清除分配',
            ),
            array(
                'label'=>'标识完成',
            ),
        );
        
        $param=array(
            'title'=>'呼叫历史',
            //'actions'=>$actions,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => true,
            //'orderBy' => "field( is_active, 'sel_member', 'sel_template', 'wait_exec', 'finish', 'dead' )",
            'orderBy' => "update_time DESC",
            'base_filter'=>array(),
        );
        $this->finder('market_mdl_callplan_members',$param);
    }

    function _views()
    {
        
    }

}