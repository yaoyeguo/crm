<?php 

class ecorder_ctl_admin_refunds extends desktop_controller{

    var $workground = 'taocrm.sales';
    
    public function __construct($app){
        parent::__construct($app);
    }

    //测试代码
    function _test()
    {
        //echo('<pre>');
        $data = array ();
        $responseObj = $this;
        $ecorder_rpc_response_refund = kernel::single('ecorder_rpc_response_refund');
        $ecorder_rpc_response_refund->add($data, $responseObj);
    }

    function index()
    {
        //$this->_test();
        $actions = array(
            array(
                'label'=>'退款单更新',
                'href'=>'index.php?app=ecorder&ctl=admin_refunds&act=refresh',
                'target'=>'dialog::{width:500,height:200,title:\'退款单更新\'}'
            ),
        );
        
        $this->finder('ecorder_mdl_tb_refunds',array(
            'title'=>'退款单',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'orderBy' => 'id DESC',
        ));
    }
     
    function _views(){
        $oGoods = $this->app->model('tb_refunds');
        $base_filter = array();
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false
        );

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false
            );
            $shop_id_arr[] = $shop['shop_id'];
        }
        
        $sub_menu[0]['filter'] = array('shop_id'=>$shop_id_arr);

        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }else{
                $v['filter'] = array('shop_id'=>$shop_id_arr); 
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $oGoods->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
        return $sub_menu;
    }
	
    function refresh()
    {
        if($_POST['act'] == 'exec'){
            $url = 'index.php?app=ecorder&ctl=admin_refunds&act=index';
            $this->begin($url);
            
            $mdl_tb_refunds = $this->app->model('tb_refunds');
            $sql = "select id,tid,title,oid,mobile from sdb_ecorder_tb_refunds where member_id=0 ";
            $rs = $mdl_tb_refunds->db->select($sql);
            foreach($rs as $v){
                if($v['tid']){
                    $sql = "select a.member_id,a.ship_name,a.ship_mobile,b.uname 
                        from sdb_ecorder_orders as a 
                        left join sdb_taocrm_members as b on a.member_id=b.member_id 
                        where a.order_bn='".$v['tid']."' ";
                    $rs_order = $mdl_tb_refunds->db->selectrow($sql);
                    if($rs_order){
                        $data = array();
                        $data['down_time'] = time();
                        $data['id'] = $v['id'];
                        $data['member_id'] = $rs_order['member_id'];
                        $data['mobile'] = $rs_order['ship_mobile'];
                        $data['buyer_nick'] = $rs_order['uname'] ? $rs_order['uname'] : $rs_order['ship_name'];
                        
                        //从子订单查询商品明细
                        if(!$v['title'] && $v['oid']){
                            $sql = "select name,nums from sdb_ecorder_order_items where oid='".$v['oid']."' ";
                            $rs_order_items = $mdl_tb_refunds->db->selectrow($sql);
                            if($rs_order_items){
                                $data['num'] = $rs_order_items['nums'];
                                $data['title'] = $rs_order_items['name'];
                            }
                        }
                        $mdl_tb_refunds->save($data);
                    }
                }
            }
            
            $this->end(true, '执行成功');
            exit;
        }
        $this->pagedata['data'] = $data;
        $this->display('admin/refunds/refresh.html');
    }
}

