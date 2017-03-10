<?php 
class market_ctl_admin_edm_send extends desktop_controller {

    /**
    *邮件发送列表
    */
    public function index()
    {
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
        $this->finder('market_mdl_edm',array(
            'title'=>'邮件发送列表',
            'use_buildin_recycle' => false,
            'orderBy' => 'id desc',
            'use_buildin_set_tag'=>false,
            'use_view_tab'=>true,
            'base_filter' => $base_filter,
        ));
    }


    public function _views()
    {
        $memberObj = &app::get('market')->model('edm');
        $base_filter=array();
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> '',
            'optional'=>true,  
        );
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');

        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>true,  
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
        }#print_r($sub_menu);exit;
        //print_r($sub_menu);exit;
        return $sub_menu;
    }
}
