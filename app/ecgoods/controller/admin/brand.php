<?php 

class ecgoods_ctl_admin_brand extends desktop_controller{

    var $workground = 'ecgoods.goods';
    
    public function __construct($app)
    {
        parent::__construct($app);        
        $timeBtn = array(
            'today' => date("Y-m-d"),
            'yesterday' => date("Y-m-d", time()-86400),
            
            'this_month_from' => date("Y-m-" . 01),
            'this_month_to' => date("Y-m-d"),
            
            'this_week_from' => date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400),
            'this_week_to' => date("Y-m-d"),
            
            'this_7days_from' => date("Y-m-d", time()-6*86400),
            'this_7days_to' => date("Y-m-d"),
            
            'next_3days_from' => date("Y-m-d", strtotime('+1 days')),
            'next_3days_to' => date("Y-m-d", strtotime('+3 days')),
            
            'next_7days_from' => date("Y-m-d", strtotime('+1 days')),
            'next_7days_to' => date("Y-m-d", strtotime('+7 days')),
        );
        $this->pagedata['timeBtn'] = $timeBtn;
    }

    function index()
    {
        $extra_view = array('ecgoods'=>'finder/brand.html');
        $actions[] = array(
            'label'=>'增加品牌',
            'href'=>'index.php?app=ecgoods&ctl=admin_brand&act=edit_brand',
            'target'=>'dialog::{width:400,height:200,title:\'增加品牌\'}'
        );
        
        $actions[] = array(
            'label'=>'删除',
            'submit'=>'index.php?app=ecgoods&ctl=admin_brand&act=del_brand',
            'target'=>'dialog::{width:400,height:200,title:\'删除品牌\'}'
        );
        
        //搜索参数
        if(!isset($_POST['s'])){
            $_POST['s']['time_from'] = $this->pagedata['timeBtn']['this_7days_from'];
            $_POST['s']['time_to'] = $this->pagedata['timeBtn']['this_7days_to'];
        }
        $this->pagedata['s'] = $_POST['s'];
    
        $this->finder('ecgoods_mdl_brand',array(
            'title'=>'商品品牌管理',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'top_extra_view' => $extra_view, 
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'use_buildin_setcol'=>false,//列配置
            'use_buildin_refresh'=>false,//刷新
        ));
    }
    
    public function edit_brand(){
        $brand_id = intval($_GET['brand_id']);
        $oBrand = $this->app->model('brand');
        $shop_type = array();
        
        if($brand_id>0){
            $brand_arr = $oBrand->dump($brand_id);
            $this->pagedata['brand'] = $brand_arr;
        }
        
        $this->display("brand_edit.html");
    }
    
    
    public function chk_brand(){
        $goods_id = $_POST['goods_id'];
        $sql = "select count(*) as total from sdb_ecgoods_shop_goods where goods_id in ($goods_id) and brand_id>0 ";
        $rs = kernel::database()->select($sql);
        echo($rs[0]['total']);
    }
    
    public function sel_brand(){
        if(isset($_POST['brand_id'])){
            $this->begin('index.php?app=ecgoods&ctl=admin_shop_goods');
            $goods_id = $_POST['goods_id'];
            $brand_id = intval($_POST['brand_id']);
            if($brand_id==0) $brand_id = 'null';
            
            $sql = "update sdb_ecgoods_shop_goods set brand_id=$brand_id where goods_id in ($goods_id)";
            kernel::database()->exec($sql);
            
            $sql = "update sdb_ecgoods_brand as a, 
            (select count(*) as total,brand_id from sdb_ecgoods_shop_goods where brand_id>0 group by brand_id) as b 
            set a.goods_count=b.total 
            where a.brand_id=b.brand_id ";
            kernel::database()->exec($sql);
            
            $this->end(true,'保存成功');
        }
    
        $goods_id = $_POST['goods_id'];
        //var_dump($_POST);
        
        $brands = array();
        $oBrand = $this->app->model('brand');
        $rs = $oBrand->getList('brand_id,brand_name');
        //var_dump($rs);
        foreach($rs as $v){
            $brands[$v['brand_id']] = $v['brand_name'];
        }
        
        $this->pagedata['brands'] = $brands;
        $this->pagedata['goods_id'] = implode(',', $goods_id);
        $this->display("brand_sel.html");
    }
    
    public function del_brand(){
        
        if($_POST['task']=='del'){
            $this->begin('index.php?app=ecgoods&ctl=admin_brand');
            $oBrand = $this->app->model('brand');
            $brand_id = $_POST['brand_id'];
            
            $sql = "update sdb_ecgoods_shop_goods set brand_id=null where brand_id in ($brand_id) ";
            kernel::database()->exec($sql);
            
            $oBrand->delete(array('brand_id'=>explode(',', $brand_id)));
            $this->end(true,'删除成功');
        }
        
        $brand_id = $_POST['brand_id'];
        $del_msg = '确定要删除这 '.sizeof($brand_id).' 个品牌吗？';
        $brand_id = implode(',', $brand_id);        
        
        $sql = "select count(distinct brand_id) as total from sdb_ecgoods_shop_goods where brand_id in ($brand_id) ";
        $rs = kernel::database()->select($sql);
        if($rs[0]['total']>0){
            $del_msg = '有'.$rs[0]['total'].'个品牌包含商品，删除后这些商品会丢失品牌信息，确定吗？';
        }
        
        $this->pagedata['brand_id'] = $brand_id;
        $this->pagedata['del_msg'] = $del_msg;
        $this->display("brand_del.html");
    }
	
    public function save_brand(){
        $this->begin('index.php?app=ecgoods&ctl=admin_brand');
        $brand_name = trim($_POST['brand_name']);
        $brand_id = intval($_POST['brand_id']);
        
        $oBrand = $this->app->model('brand');
        if($brand_id==0 && $oBrand->dump(array('brand_name'=>$brand_name))){
            $this->end(false,'品牌名称不能重复');
        }
        
        if($brand_id==0){
            $goods_count = 0;
            $arr = array(
                'brand_name'=>$brand_name,
                'goods_count'=>$goods_count
            );
            $oBrand->insert($arr);
        }else{
            $arr = array(
                'brand_name'=>$brand_name,
            );
            $oBrand->update($arr, array('brand_id'=>$brand_id));
        }
        $this->end(true,'保存成功');
    }
    
    public function save_brand_goods()
    {
        $this->begin('index.php?app=ecgoods&ctl=admin_brand&act=index');
        
        $oBrand = $this->app->model('brand');
        $goods_id = @implode(',',$_POST['goods_id']);
        $brand_id = intval($_POST['brand_id']);
        $arr = array(
            'goods_id'=>$goods_id,
            'goods_count'=>count($_POST['goods_id']),
        );
        $oBrand->update(
            $arr, 
            array('brand_id'=>$brand_id)
        );
        
        //更新商品对应的品牌ID
        if($arr['goods_count']>0){
            $oGoods = $this->app->model('shop_goods');
            $oGoods->update(
                array('brand_id'=>$brand_id), 
                array('goods_id'=>$_POST['goods_id'])
            );
        }
        
        $this->end(true,'保存成功');
    }
    
    public function set_goods()
    {
        $brand_id = intval($_GET['brand_id']);
        $oBrand = $this->app->model('brand');
        $shop_type = array();
        
        if($brand_id>0){
            $brand_arr = $oBrand->dump($brand_id);
            $this->pagedata['brand'] = $brand_arr;
            
            //获取已经选择的商品列表
            if($brand_arr['goods_id']){
                $shop_goods = $this->app->model('shop_goods');
                $rs_goods = $shop_goods->getList('goods_id,name,bn',array('goods_id'=>explode(',',$brand_arr['goods_id']))); 
                foreach($rs_goods as &$v){
                    $v['name'] = mb_substr($v['name'],0,18,'utf-8');
                }
                $this->pagedata['rs_goods'] = $rs_goods;
            }
        }
        
        $this->display("brand/set_goods.html");
    }
}

