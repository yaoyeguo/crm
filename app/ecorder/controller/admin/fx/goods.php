<?php
class ecorder_ctl_admin_fx_goods extends desktop_controller{

    var $workground = 'goods_manager';
    public $use_buildin_import = true;
    
  
    //ajax查询商品信息
    public function ajaxGet()
    {
        $page = intval($_POST['page']);
        $page_size = 10;
        $where = '';
        $sort_type = trim($_POST['sort_type']);
        $name = trim($_POST['name']);
        $bn = trim($_POST['bn']);
        $brand_id = $_POST['brand_id'];
        $shop_id = trim($_POST['shop_id']);
        $filter_goods_id = trim($_POST['filter_goods_id']);
        $sel_goods = trim($_POST['sel_goods']);
        
        //商品排序：默认按name asc，可选bn
        ($sort_type=='name') ? $sort_type='name' : $sort_type='bn';
        
        //$where = " WHERE shop_id = '$shop_id'";
        $where = " WHERE 1=1 ";
        if($name or $bn) {
            if($name) $where .= " AND (name like '%$name%')";
            if($bn) $where .= " AND (bn like '%$bn%')";
        }elseif($filter_goods_id){
            $page_size = 100;
            $filter_goods_id = '0'.$filter_goods_id.'0';
            $where .= " AND goods_id IN ($filter_goods_id)";
        }
        
        if($brand_id){
        	$where .= " AND brand_id={$brand_id}"; 
        }

        /**
         * 添加根据店铺过滤商品
         */
        if ($shop_id) {
            $where .= " AND shop_id = '{$shop_id}'";
        }
        
        $sql = "select goods_id,IF(bn is not null,bn,'') as bn,name,price from sdb_ecorder_fx_order_items $where GROUP BY goods_id ORDER BY $sort_type ASC limit ".$page_size*$page.",$page_size";
        $rs = kernel::database()->select($sql);
        //var_dump($filter);
        if($rs){
            echo(json_encode($rs));
        }else{
            echo('null');
        }
    }
    
    
}

