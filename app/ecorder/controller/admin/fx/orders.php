<?php
class ecorder_ctl_admin_fx_orders extends desktop_controller{
    var $workground = 'taocrm.fxmember';

    var $is_debug = false;


    public function index()
    {
        $title = '订单列表';
        $baseFilter = array();
        $this->finder('ecorder_mdl_fx_orders',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        	'actions'=>array(),
        	//'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
            'orderBy' => 'createtime DESC',
        ));
    }

    function _views()
    {
        $memberObj = app::get('ecorder')->model('fx_orders');
        $base_filter=array();
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> $base_filter,
            'optional'=>false,	
        );
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('fenxiao');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,	    
            );
        }
        
        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!empty($v['filter'])){
                $v['filter'] = array_merge($v['filter'],$base_filter);
            }
            $count =$memberObj->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=ecorder&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }

}

