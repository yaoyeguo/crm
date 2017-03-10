<?php 
class market_ctl_admin_sms_send extends desktop_controller {

    function index(){
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $base_filter = array();
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if ($view == 0) {
            $base_filter = array('shop_id|in' => $shops);
        }
        $this->finder(
            'market_mdl_sms',
            array(
                'title'=>'营销短信日志',
                'use_buildin_recycle'=>false,
                'use_buildin_recycle' => false,
                'orderBy' => 'sms_id desc',
                'base_filter' => $base_filter,
            )
        );
    }

    function _views(){
        $memberObj = &app::get('market')->model('sms');
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        
        $base_filter=array();
          $sub_menu[] = array(
            'label'=> '全部',
//            'filter'=> '',
            'filter'=> array('shop_id|in' => $shops),
            'optional'=>false,	
        );
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,	
            );
        }
        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
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



