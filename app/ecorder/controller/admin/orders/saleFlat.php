<?php 

class ecorder_ctl_admin_orders_saleFlat extends desktop_controller{

    var $workground = 'taocrm.sales';
    
    public function __construct($app)
    {
        parent::__construct($app);
    }

    function index()
    {
        //$base_filter = array('op_name'=>'matrix');
        if(isset($_POST['member_id']) && $_POST['member_id']){
            $filter_uname = trim($_POST['member_id']);
            $sql = "select member_id from sdb_taocrm_members where uname like '{$filter_uname}%' ";
            $rs = $this->app->model('orders')->db->select($sql);
            if($rs){
                foreach($rs as $v){
                    $base_filter['member_id'][] = $v['member_id'];
                }
            }else{
                $base_filter['order_id'] = -1;
            }
            unset($_POST['member_id']);
        }
        
        $this->finder('ecorder_mdl_orders',array(
            'title'=>'全部订单列表',
            //'actions'=>$actions,
            'base_filter'=>$base_filter,
            'orderBy' => 'createtime DESC',
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        ));
    }
     
    function _views(){
        $oGoods = $this->app->model('orders');
        $base_filter = array();
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false,
            'display'=>true,
        );

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,
                'display'=>true,
            );
            $shop_id_arr[] = $shop['shop_id'];
        }
        
        $sub_menu[0]['filter'] = array('shop_id'=>$shop_id_arr);

        $i=0;
       // print_r($sub_menu);
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }else{
                $v['filter'] = array('shop_id'=>$shop_id_arr); 
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = 0;//$oGoods->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
        return $sub_menu;
    }
}

