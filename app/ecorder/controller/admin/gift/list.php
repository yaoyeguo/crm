<?php
class ecorder_ctl_admin_gift_list extends desktop_controller{

	 public function index()
     {
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $base_filter = array();
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        $base_filter = array_merge($base_filter, array('shop_id|in' => $shops));
        $this->finder('ecorder_mdl_shop_gift',array(
            'title'=>'ERP赠品列表',
        	'actions'=>array(
        		array(
                	'label'=>'同步ShopEx ERP赠品信息',
                    'href'=>'index.php?app=ecorder&ctl=admin_gift_list&act=refresh_gifts&shop_id='.$_GET['shop_id'],
                    'target'=>'dialog::{width:500,height:150,title:\'同步赠品\'}'
             	),
             ),
            //'base_filter'=>$base_filter,
            'orderBy' => 'id DESC',
            'use_buildin_recycle' => true,
        ));
	 }
	 
	function _views(){
        $shopGiftObj = $this->app->model('shop_gift');
        $base_filter = array('status'=>1);
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false
        );
        
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name', array('node_type'=>'ecos.ome'));
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false
            );
        }
        
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $base_filter = array_merge($base_filter, array('shop_id|in' => $shops));
        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $shopGiftObj->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=ecorder&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
        return $sub_menu;
    }
    
    
	public function refresh_gifts()
    {
        $sql = "select shop_id,name from sdb_ecorder_shop where node_id!='' and node_type='ecos.ome' ";
        $shop_data=kernel::database()->select($sql);
        foreach((array)$shop_data as $v){
            $shoplist[$v['shop_id']] = $v['name'];
        }
        
        $this->pagedata['shop_id'] = $_GET['shop_id'];
        $this->pagedata['shoplist'] = $shoplist;
        $this->pagedata['finder_id'] = $_GET['finder_id'];
		$this->display("admin/shop/refresh_gifts.html");
	}
	
	public function get_gifts()
    {
		$shop_id = $_POST['shop_id'];
		$gift = kernel::single('ecorder_rpc_request_gift');
		$flag = true;
		$res = $gift->get_gift($shop_id,$flag);
	}
    
    public function ajax_get_gifts()
    {
        $filter = array('gift_num|bthan'=>1);
        $s_gift_bn = trim($_POST['s_gift_bn']);
        $s_gift_name = trim($_POST['s_gift_name']);
        $sel_goods = explode(',', $_POST['sel_goods']);
        if($s_gift_bn) $filter['gift_bn|has'] = $s_gift_bn;
        if($s_gift_name) $filter['gift_name|has'] = $s_gift_name;
        $rs = $this->app->model('shop_gift')->getList('id,gift_bn,gift_name', $filter);
        foreach($rs as $k=>$v){
            if(in_array($v['id'], $sel_goods)){
                unset($rs[$k]);
            }
        }
        echo(json_encode(array_values($rs)));
    }
    
    public function edit($gift_id=0)
    {
        if($_POST){
            $this->begin('index.php?app=ecorder&ctl=admin_gift_list&act=index');
            $data = $_POST;
            $data['id'] = intval($data['id']);
            $data['update_time'] = time();
            $this->app->model('shop_gift')->save($data);
            $this->end(true, '保存成功');
        }
    
        ///var_dump($_GET['_finder']);
        if($gift_id>0){
            $rs = $this->app->model('shop_gift')->dump($gift_id);
            //var_dump($rs);
            $this->pagedata['rs'] = $rs;
            $this->display('admin/gift/edit.html');
        }else{
            echo('gift_id error.');
        }
    }
}