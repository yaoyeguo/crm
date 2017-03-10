<?php
class ecorder_ctl_admin_goods extends desktop_controller{

    var $workground = 'goods_manager';
    public $use_buildin_import = true;
    
    function index($supplier_id=null){
        
        $cat_id = intval($_GET['cat_id']);
        if ($cat_id){//按分类显示商品
            $base_filter = array('cat_id'=>$cat_id);
        }
        
        $this->finder('ome_mdl_goods',array(
            'title'=>'商品',
            'base_filter' => $base_filter,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => true,
            'use_buildin_filter'  => true,
            'use_buildin_export'  => true,
            'use_buildin_import'  => true,
            'orderBy' => 'goods_id DESC'
        ));
    }
    
    function view_gimage($image_id){
        $oImage = $this->app->model('image');
        $this->pagedata['image_id'] = $image_id;
        $this->display('goods/detail/img/view_gimages.html');        
    }

    function import(){      
		$oIo = kernel::servicelist('omecsv_io');
		if(!empty($oIo)){
			$this->pagedata['thisUrl'] = 'index.php?app=omecsv&ctl=admin_to_import&act=treat';
			$import = new omecsv_ctl_admin_import($this);
			$_GET['ctler']='ome_mdl_goods';
			$_GET['add']='ome';
		}else{
			$this->pagedata['thisUrl'] = 'index.php?app=ome&ctl=admin_goods&act=index';
			$import = new desktop_finder_builder_import($this);
		}
        
        $oGtype = $this->app->model('goods_type');
        $this->pagedata['gtype'] = $oGtype->getList('type_id,name');
        echo $this->page('admin/goods/download.html');
        echo "<div class=\"tableform\">";
        $import->main();
        echo "</div></div></div>";
    }
    
    //ajax查询商品信息
    public function ajaxGet()
    {
        $page = intval($_POST['page']);
        $page_size = 10;
        $where = '';
        $sort_type = trim($_POST['sort_type']);
        $name = trim($_POST['name']);
        $name2 = trim($_POST['name2']);
        $sign = trim($_POST['sign']);
        $bn = trim($_POST['bn']);
        $shop_id = trim($_POST['shop_id']);
        $filter_goods_id = trim($_POST['filter_goods_id']);
        $sel_goods = trim($_POST['sel_goods']);
        
        ($sign == 'or') ? $sign='or' : $sign='and';
        
        //商品排序：默认按name asc，可选bn
        ($sort_type=='name') ? $sort_type='name' : $sort_type='bn';
        
        //$where = " WHERE shop_id = '$shop_id'";
        $where = " WHERE 1=1 ";
        if($name or $bn) {
            if($name){
                if($name2){
                    $where .= " AND (name like '%$name%' $sign name like '%$name2%')";
                }else{
                    $where .= " AND (name like '%$name%')";
                }
            }
            if($bn) $where .= " AND (bn like '%$bn%')";
        }elseif($filter_goods_id){
            $page_size = 200;
            $filter_goods_id = '0'.$filter_goods_id.'0';
            $where .= " AND goods_id IN ($filter_goods_id)";
        }
        //$where .= " AND goods_id NOT IN ($sel_goods)";
        /**
         * 添加根据店铺过滤商品
         */
        if ($shop_id) {
            $where .= " AND shop_id = '{$shop_id}'";
        }
        
        $sql = "select count(*) as total from sdb_ecgoods_shop_goods $where ";
        $rs = kernel::database()->selectRow($sql);
        $total = $rs['total'];
        
        $sql = "select goods_id,IF(bn is not null,bn,'') as bn,name,price from sdb_ecgoods_shop_goods $where ORDER BY $sort_type ASC limit ".$page_size*$page.",$page_size";
        $rs = kernel::database()->select($sql);
        //var_dump($filter);
        if($rs){
            $rs[] = $total;
            echo(json_encode($rs));
        }else{
            echo('null');
        }
    }
    
    //ajax查询商品订单信息
    public function ajaxGetOrder()
    {
        $page = intval($_POST['page']);
        $page_size = 7;
        $where = '';
        $name = trim($_POST['name']);
        $shop_id = trim($_POST['shop_id']);
        $filter_goods_id = trim($_POST['filter_goods_id']);
        $sel_goods = trim($_POST['sel_goods']);
        
        $where = " WHERE `sdb_ecorder_order_items`.shop_id = '$shop_id'";
        if($name) {
            $where .= " AND name like '%$name%'";
        }elseif($filter_goods_id){
            $page_size = 100;
            $filter_goods_id = '0'.$filter_goods_id.'0';
            $where .= " AND goods_id IN ($filter_goods_id)";
        }
        $where .= " AND goods_id NOT IN ($sel_goods)";
        
        //$sql = "select goods_id,IF(bn is not null,bn,'') as bn,name,price from `sdb_ecorder_order_items` LEFT JOIN `sdb_ecorder_orders` ON `sdb_ecorder_order_items`.order_id = `sdb_ecorder_orders`.order_id $where limit ".$page_size*$page.",$page_size";
//        $sql = "SELECT
//                  goods_id, IF(bn IS NOT NULL, bn, '') AS bn, name, price
//                FROM
//                  `sdb_ecorder_order_items`
//                LEFT JOIN `sdb_ecorder_orders` ON `sdb_ecorder_order_items`.order_id = `sdb_ecorder_orders`.order_id
//                $where limit " . $page_size*$page.",$page_size";
//        echo $sql;
        $sql = "SELECT
                  goods_id,
                  MAX(IF(bn IS NOT NULL, bn, ''))AS bn,
                  MAX(name) as name,
                  MAX(price) as price
                FROM
                  `sdb_ecorder_order_items`
                ".$where."
                GROUP BY `goods_id`
                LIMIT " . $page_size*$page.",$page_size";
        $rs = kernel::database()->select($sql);
        if($rs){
            echo(json_encode($rs));
        }else{
            echo('null');
        }
    }
    
}

