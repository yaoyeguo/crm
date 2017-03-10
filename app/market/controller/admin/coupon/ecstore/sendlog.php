<?php
class market_ctl_admin_coupon_ecstore_sendlog extends desktop_controller{
    var $workground = 'market.sales';

    var $pagelimit = 10;

    var $is_debug = false;

    public function __construct($app){
        parent::__construct($app);
        $this->interfacePacketName = 'ShopMemberAnalysis';
        $this->interfaceMethodName = 'SearchMemberAnalysisList';
        $this->interfaceTableName = 'taocrm_mdl_middleware_member_analysis';
    }

   

    public function index(){
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name',array('node_type'=>'ecos.b2c'));
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if ($_GET['view']!=0){
            $view=$view-1;
            $shop_id =$shops[$view];
        }

        $actions = array(
    
                );
                $baseFilter = array();
                $baseFilter =   array('shop_id|in' => $shops);
                //        if ($view == 0) {
                //            $baseFilter =   array('shop_id|in' => $shops);
                //        }
                //        else {
                //            $baseFilter =   array('shop_id|in' => $shops);
                //        }
                $this->finder('market_mdl_coupon_ecstore_sendlog',array(
            'title'=>'Ecstore优惠券发送记录',
            'actions'=>$actions,
            'use_buildin_recycle'=>false,
            'base_filter' => $baseFilter,
                ));
    }


  
    public function _views(){
        $oRecord = $this->app->model('coupon_ecstore_sendlog');

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name',array('node_type'=>'ecos.b2c'));
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }

        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> array('shop_id|in' => $shops),
            'optional'=>false,	
        );

        foreach((array)$shopList as $v){
            $sub_menu[] = array(
            'label'=>$v['name'],
            'filter'=> array('shop_id' => $v['shop_id']),
            'optional'=>false,	
            );
        }

        $i=0;
        foreach($sub_menu as $k=>$v){
            $count =$oRecord->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=market&ctl=admin_coupon_ecstore_sendlog&act=index&view='.$i++;
        }
        return $sub_menu;
    }


}