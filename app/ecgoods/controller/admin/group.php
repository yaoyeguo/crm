<?php 

class ecgoods_ctl_admin_group extends desktop_controller{

    var $workground = 'ecgoods.group';
    
    public function __construct($app)
    {
        parent::__construct($app);        
        $timeBtn = array(
            'today' => date("Y-m-d"),
            'yesterday' => date("Y-m-d", time()-86400),
            
            'this_month_from' => date("Y-m-" . 01),
            'this_month_to' => date("Y-m-d"),
            
            'this_7days_from' => date("Y-m-d"),
            'this_7days_to' => date("Y-m-d"),
            
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
        $extra_view = array('ecgoods'=>'finder/group.html');
        $actions[] = array(
            'label'=>'增加分组',
            'href'=>'index.php?app=ecgoods&ctl=admin_group&act=edit',
            'target'=>'dialog::{width:400,height:200,title:\'增加分组\'}'
        );
        
        $actions[] = array(
            'label'=>'删除',
            'submit'=>'index.php?app=ecgoods&ctl=admin_group&act=delete',
            'target'=>'dialog::{width:400,height:200,title:\'删除分组\'}'
        );
        
        //搜索参数
        if(!isset($_POST['s'])){
            $_POST['s']['time_from'] = $this->pagedata['timeBtn']['this_7days_from'];
            $_POST['s']['time_to'] = $this->pagedata['timeBtn']['this_7days_to'];
        }
        $this->pagedata['s'] = $_POST['s'];
    
        $this->finder('ecgoods_mdl_group',array(
            'title'=>'商品分组管理',
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
            'orderBy'=>'cat_path',
            'use_buildin_setcol'=>false,//列配置
            'use_buildin_refresh'=>false,//刷新
        ));
    }
    
    public function edit()
    {
        $group_id = intval($_GET['group_id']);
        $parent_id = intval($_GET['parent_id']);
        
        $oBrand = $this->app->model('group');
        $shop_type = array();
        
        if($group_id>0){
            $rs = $oBrand->dump($group_id);
        }elseif($parent_id>0){
            $rs = $oBrand->dump($parent_id);
        }
        
        $this->pagedata['parent_id'] = $parent_id;
        $this->pagedata['group'] = $rs;
        $this->display("group/edit.html");
    }
    
    public function chk_group()
    {
        $goods_id = $_POST['goods_id'];
        $sql = "select count(*) as total from sdb_ecgoods_shop_goods where goods_id in ($goods_id) and group_id>0 ";
        $rs = kernel::database()->select($sql);
        echo($rs[0]['total']);
    }
    
    public function sel_group()
    {
        if(isset($_POST['group_id'])){
            $this->begin('index.php?app=ecgoods&ctl=admin_shop_goods');
            $goods_id = $_POST['goods_id'];
            $group_id = intval($_POST['group_id']);
            if($group_id==0) $group_id = 'null';
            
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
    
    public function delete(){
        
        $this->begin('index.php?app=ecgoods&ctl=admin_group');
        $oGroup = $this->app->model('group');
        $group_id = intval($_GET['group_id']);
        
        /*
        $sql = "update sdb_ecgoods_shop_goods set brand_id=null where brand_id in ($brand_id) ";
        kernel::database()->exec($sql);
        */
        
        $oGroup->delete(array('group_id'=>$group_id));
        $oGroup->delete(array('parent_id'=>$group_id));
        $this->end(true,'删除成功');
    }
	
    public function save_group()
    {
        $this->begin('index.php?app=ecgoods&ctl=admin_group');
        $group_name = trim($_POST['group_name']);
        $group_id = intval($_POST['group_id']);
        $parent_id = intval($_POST['parent_id']);
        
        $oGroup = $this->app->model('group');
        if($group_id==0 && $oGroup->dump(array('group_name'=>$group_name))){
            $this->end(false,'分组名称不能重复');
        }
        
        //新增分组
        if($parent_id>0 or $group_id==0){
            $goods_count = 0;
            $arr = array(
                'group_name'=>$group_name,
                'goods_count'=>$goods_count,
                'cat_path'=>$cat_path,
                'parent_id'=>$parent_id,
                'create_time'=>date('Y-m-d H:i:s'),
            );
            $oGroup->insert($arr);
            
            if($parent_id == 0){
                $cat_path = ','.$arr['group_id'];
            }else{
                $rs = $oGroup->dump($parent_id);
                $cat_path = $rs['cat_path'].','.$arr['group_id'];
            }
            $arr['cat_path'] = $cat_path;
            $oGroup->save($arr);
            
            if($parent_id>0){
                $sql = "update sdb_ecgoods_group set child_count=child_count+1  where group_id=$parent_id ";
                $oGroup->db->exec($sql);
            }
            
        //修改分组
        }else{
        
            $sql = "select count(*) as total from sdb_ecgoods_group where parent_id=$group_id ";
            $rs = $oGroup->db->select($sql);
        
            $arr = array(
                'group_name'=>$group_name,
                'child_count'=>$rs[0]['total'],
                'create_time'=>date('Y-m-d H:i:s'),
            );
            $oGroup->update($arr, array('group_id'=>$group_id));
        }
        $this->end(true,'保存成功');
    }
    
    public function getChildGroup(){
        $parent_id = intval($_POST['parent_id']);
        $oGroup = $this->app->model('group');
        $filter = array('parent_id'=>$parent_id);
        $rs = $oGroup->getList('*', $filter, 0, -1, 'group_id ASC');
        echo(json_encode($rs));
    }
    
    public function set_goods()
    {
        $group_id = intval($_GET['group_id']);
        $oGroup = $this->app->model('group');
        $shop_type = array();
        
        if($group_id>0){
            $group_arr = $oGroup->dump($group_id);
            $this->pagedata['group'] = $group_arr;
            
            //获取已经选择的商品列表
            if($group_arr['goods_id']){
                $shop_goods = $this->app->model('shop_goods');
                $rs_goods = $shop_goods->getList('goods_id,name,bn',array('goods_id'=>explode(',',$group_arr['goods_id']))); 
                foreach($rs_goods as &$v){
                    $v['name'] = mb_substr($v['name'],0,18,'utf-8');
                }
                $this->pagedata['rs_goods'] = $rs_goods;
            }
        }
        
        $this->display("group/set_goods.html");
    }
    
    public function save_group_goods()
    {
        $this->begin('index.php?app=ecgoods&ctl=admin_group&act=index');
        
        $oGroup = $this->app->model('group');
        $goods_id = @implode(',',$_POST['goods_id']);
        $group_id = intval($_POST['group_id']);
        $arr = array(
            'goods_id'=>$goods_id,
            'goods_count'=>count($_POST['goods_id']),
        );
        $oGroup->update(
            $arr, 
            array('group_id'=>$group_id)
        );
        
        $this->end(true,'保存成功');
    }
}

