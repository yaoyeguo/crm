<?php
class market_ctl_admin_fx_sms extends desktop_controller{
    //var $workground = 'taocrm.fxmember';

    var $is_debug = false;
    var $workground = 'taocrm.fxmember';


    public function index()
    {
        $title = '发送记录列表';
        $baseFilter = array();
        $this->finder('market_mdl_fx_sms',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        	'actions'=>array(

        ),
        //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
        ));
    }

    function _views(){
        $memberObj = &app::get('market')->model('fx_sms');
        $base_filter=array();
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> $base_filter,
            'optional'=>false,	
        );
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name',array('shop_type'=>'taobao','subbiztype'=>'fx'));

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
            $sub_menu[$k]['href'] = 'index.php?app=market&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }



}