<?php
class ecorder_ctl_admin_gift_rule extends desktop_controller{
	
    public function index($act)
    {
        if($act == 'add'){
            $this->add();
            exit;
        }
        
        $this->finder('ecorder_mdl_gift_rule',array(
            'title'=>'促销规则列表',
            'actions'=>array(
            	array(
                	'label'=>'添加促销规则',
                    'href'=>'index.php?app=ecorder&ctl=admin_gift_rule&act=index&p[0]=add&shop_id='.$_GET['shop_id'].'&view='.$view,
                    //'target'=>'dialog::{width:700,height:380,title:\'添加促销规则\'}'
             	),
             ),
            //'base_filter'=>$base_filter,
            'orderBy' => 'status DESC,priority DESC,id DESC',
            'use_buildin_recycle' => false,
        ));
    }

    function _views()
    {
        if($_GET['act']=='logs'){
            $shopGiftObj = $this->app->model('gift_logs');
        }else{
            $shopGiftObj = $this->app->model('gift_rule');
            //$base_filter = array('status'=>1);
        }
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false
        );

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false
            );
        }
        
        foreach($sub_menu as $k=>$v){
            if ($base_filter){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }
            //var_dump($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $shopGiftObj->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
        return $sub_menu;
    }
    
    function add()
    {
    	$this->edit();
    }
    
    function edit()
    {
    	$shopObj =  app::get(ORDER_APP)->model('shop');
    	$shop_id = $_GET['shop_id'];
        $id = intval($_GET['id']);

        $shops_name = $shopObj->getList('shop_id,name',array('node_id|noequal'=>''));
        foreach($shops_name as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $rule = array(
            'start_time' => date('Y-m-d'),
            'status' => 1,
            'shop_id' => $shop_id,
            'time_type' => 'pay_time',
            'lv_id' => 0,
            'filter_arr' => array(
                'order_amount' => array(
                    'type'=>0
                ),
                'buy_goods' => array(
                    'type'=>0
                ),
            ),
        );
        
        //修改规则信息
        if($id>0){
            $rule = $this->app->model('gift_rule')->dump($id);
            $rule['filter_arr'] = json_decode($rule['filter_arr'], true);
            
            $goods_name = '';
            $goods_bn = $rule['filter_arr']['buy_goods']['goods_bn'];
            if( ! is_array($goods_bn)){
                $goods_bn = array($goods_bn,'','','','','','','','','');
            }
            $rule['filter_arr']['buy_goods']['goods_bn'] = $goods_bn;
            
            //店铺等级规则限定
            if($rule['shop_id']){
                $shopLvObj = app::get(ORDER_APP)->model('shop_lv');
                $shop_lv = $shopLvObj->getList('lv_id,name',array('shop_id'=>$rule['shop_id']));
            }
        }else{
            $rule['filter_arr']['buy_goods']['goods_bn'] = array('','','','','','','','','','');
        }
        
        //已经设定的赠品组合
        $gifts = array();
        if($rule['gift_ids']){
            $gift_ids = explode(',', $rule['gift_ids']);
            $gift_num = explode(',', $rule['gift_num']);
            
            foreach($gift_ids as $k=>$v){
                $gift_id_num[$v] = $gift_num[$k];
            }
            
            $gifts = $this->app->model('shop_gift')->getList('*,"checked" as checked',array('id'=>$gift_ids,'gift_num|bthan'=>1));
            foreach($gifts as $k=>$v){
                $gifts[$k]['gift_name'] = mb_substr($v['gift_name'],0,22,'utf-8');
                $gifts[$k]['num'] = $gift_id_num[$v['id']];
            }
        }else{
            //$gifts = $this->app->model('shop_gift')->getList('*',array(),0,5);
        }
        
        /*
        $rs = app::get('ectools')->model('regions')->getList('local_name',array('region_grade'=>1,'region_id|sthan'=>3320));
        foreach($rs as $v){
            $provinces[$v['local_name']] = $v['local_name'];
        }
        */
        
        $this->pagedata['shop_lv'] = $shop_lv;
        $this->pagedata['provinces'] = $provinces;
        $this->pagedata['goods_name'] = $goods_name;
        $this->pagedata['shops'] = $shops;
    	$this->pagedata['gifts'] = $gifts;
    	$this->pagedata['rule'] = $rule;
    	$this->pagedata['view'] = $_GET['view'];
    	$this->pagedata['beigin_time'] = date("Y-m-d",time());
    	$this->pagedata['end_time'] = date('Y-m-d',strtotime('+15 days'));
        $this->page('admin/gift/rule_edit.html');
    }
    
    function view_rule($id=0)
    {
        $shopObj =  app::get(ORDER_APP)->model('shop');
        $shop_id = $_GET['shop_id'];

        $shops_name = $shopObj->getList('shop_id,name',array('node_id|noequal'=>''));
        foreach($shops_name as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $rule = array(
            'start_time' => date('Y-m-d'),
            'status' => 1,
            'shop_id' => $shop_id,
            'time_type' => 'pay_time',
            'lv_id' => 0,
            'filter_arr' => array(
                'order_amount' => array(
                    'type'=>0
                ),
                'buy_goods' => array(
                    'type'=>0
                ),
            ),
        );
        
        if($id>0){
            $rule = $this->app->model('gift_rule')->dump($id);
            $rule['filter_arr'] = json_decode($rule['filter_arr'], true);
            
            $goods_name = '';
            $goods_bn = &$rule['filter_arr']['buy_goods']['goods_bn'];
            if(! is_array($goods_bn)){
                $goods_bn = array($goods_bn);
            }
            
            $goods_bn = kernel::single('ecorder_func')->clear_value($goods_bn);
            
            //店铺等级规则限定
            if($rule['shop_id']){
                $shopLvObj = app::get(ORDER_APP)->model('shop_lv');
                $shop_lv = $shopLvObj->getList('lv_id,name',array('shop_id'=>$rule['shop_id']));
            }
        }
        
        //已经设定的赠品组合
        $gifts = array();
        if($rule['gift_ids']){
            $gift_ids = explode(',', $rule['gift_ids']);
            $gift_num = explode(',', $rule['gift_num']);
            
            foreach($gift_ids as $k=>$v){
                $gift_id_num[$v] = $gift_num[$k];
            }
            
            $gifts = $this->app->model('shop_gift')->getList('*,"checked" as checked',array('id'=>$gift_ids,'gift_num|bthan'=>1));
            foreach($gifts as $k=>$v){
                $gifts[$k]['gift_name'] = mb_substr($v['gift_name'],0,22,'utf-8');
                $gifts[$k]['num'] = $gift_id_num[$v['id']];
            }
        }
        
        $this->pagedata['shop_lv'] = $shop_lv;
        $this->pagedata['provinces'] = $provinces;
        $this->pagedata['goods_name'] = $goods_name;
        $this->pagedata['shops'] = $shops;
        $this->pagedata['gifts'] = $gifts;
        $this->pagedata['rule'] = $rule;
        $this->pagedata['view'] = $_GET['view'];
        $this->pagedata['beigin_time'] = date("Y-m-d",time());
        $this->pagedata['end_time'] = date('Y-m-d',strtotime('+15 days'));
        $this->display('admin/gift/rule_view.html');
    }
    
    function priority($id=0)
    {      
        if($_POST){
            $this->begin("index.php?app=ecorder&ctl=admin_gift_rule&act=index");
            $shopGiftObj = app::get('ecorder')->model('gift_rule');
            $data = $_POST;
            $data['priority'] = intval($_POST['priority']);
            $data['modified_time'] = time();
            if($shopGiftObj->save($data)){
                $this->end(true,'添加成功');
            }else{
                $this->end(false,'添加失败');
            }
        }
    
        //修改规则信息
        if($id>0){
            $rule = $this->app->model('gift_rule')->dump($id);
            
            $rule['start_time'] = date("Y-m-d", $rule['start_time']);
            $rule['end_time'] = date("Y-m-d", $rule['end_time']);
        }
        
        $this->pagedata['rule'] = $rule;
        $this->pagedata['view'] = $_GET['view'];
        $this->display('admin/gift/priority.html');
    }
    
    function recycle_rule($id)
    {
    	$this->pagedata['id']=$id;
    	$this->pagedata['view'] = $_GET['p'][1];
        $this->page('admin/shop/invalid.html');
    }
    
 	function invalid()
    {
       $this->begin("index.php?app=ecorder&ctl=admin_gift_rule&act=index&view=".$_POST['view']);
        if($_POST['invalid_name']=='on'){
            $gift_obj = &app::get('ecorder')->model('gift_rule');
            $rec=$gift_obj->update(array('status'=>0),array('id'=>$_POST['id']));
            $this->end(true,'修改成功');
        }else {
            $this->end(true,'修改成功');
        }
    }
    
    function edit_rule($id)
    {
        $this->edit($id);
    }
    
    function check_gift_rule()
    {
    	$flag = 0;
    	$gift_rule = app::get(ORDER_APP)->model('gift_rule');
    	$start_time = strtotime($_POST['start_time']);
    	$end_time = strtotime($_POST['end_time']);
    	$result = $gift_rule -> dump(array('lv_id'=>$_POST['lv_id'],'shop_id'=>$_POST['shop_id'],'gift_bn'=>$_POST['gift_bn'],'id|noequal'=>$_POST['id'],'status'=>1),'id');
    	if($result){
    		$flag = 1;
    	}else if($start_time >= $end_time){
    		$flag = 2;
    	}
    	echo $flag;
    }
    
    function save_rule()
    {
    	$this->begin("index.php?app=ecorder&ctl=admin_gift_rule&act=index");
        $shopGiftObj = app::get('ecorder')->model('gift_rule');
        $data = $_POST;
        $data['filter_arr'] = $_POST['filter_arr'];
        $data['gift_ids'] = $_POST['gift_id'];
        $data['gift_num'] = $_POST['gift_num'];
        $data['start_time'] = strtotime($_POST['start_time']);
        $data['end_time'] = strtotime($_POST['end_time']);
        $data['modified_time'] = time();
        
        if($data['filter_arr']['buy_goods']['goods_bn']){
            foreach($data['filter_arr']['buy_goods']['goods_bn'] as &$v){
                $v = strtoupper($v);
            }
        }
        $data['filter_arr'] = json_encode($data['filter_arr']);
        
        if(!$data['id']) $data['create_time'] = time();
        
        //清理gift_num
        foreach($data['gift_num'] as $k=>$v){
            if(!in_array($k, $data['gift_ids'])){
                unset($data['gift_num'][$k]);
            }
        }
        
        $data['gift_ids'] = @implode(',', $data['gift_ids']);
        $data['gift_num'] = @implode(',', $data['gift_num']);
        
        if($shopGiftObj->save($data)){
        	$this->end(true,'添加成功');
        }else{
            $this->end(false,'添加失败');
        }
    }
    
	function update_rule()
    {
    	$this->begin("index.php?app=ecorder&ctl=admin_gift_rule&act=index&view=".$_POST['view']);
        $shopGiftObj = $this->app->model('gift_rule');
        $data['gift_bn'] = $_POST['gift_bn'];
        $data['start_time'] = strtotime($_POST['start_time']);
        $data['end_time'] = strtotime($_POST['end_time']);
        $data['create_time'] = time();
        if($shopGiftObj->update($data,array('id'=>$_POST['id']))){
        	$this->end(true,'修改成功');
        }else{
            $this->end(false,'修改失败');
        }	
    }
    
    function get_rules()
    {
    	$shopLvObj = app::get(ORDER_APP)->model('shop_lv');
    	$shop_gift = app::get(ORDER_APP)->model('shop_gift');
    	$aLv = $shopLvObj->getList('lv_id,name',array('shop_id'=>$_POST['shop_id']));
    	$aGift = $shop_gift->getList('gift_bn,gift_name',array('shop_id'=>$_POST['shop_id']));
    	echo json_encode(array('aLv'=>$aLv,'aGift'=>$aGift));
    }
    
    function check_gift_lv()
    {
    	$flag = 0;
    	$start_time = strtotime($_POST['start_time']);
    	$end_time = strtotime($_POST['end_time']);
    	$shop_gift = app::get(ORDER_APP)->model('gift_rule');
    	$res = $shop_gift -> dump(array('gift_bn'=>$_POST['gift_bn'],'shop_id'=>$_POST['shop_id'],'lv_id'=>$_POST['lv_id'],'status'=>1),'*');
    	if($res){
    		$flag = 1;
    	}else if($end_time <= $start_time){
    		$flag = 2;
    	}
    	echo $flag;
    }
    
    public function logs()
    {
        $actions = array();
        $base_filter = array();
        $this->finder('ecorder_mdl_gift_logs',array(
            'title'=>'赠品发送记录',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'orderBy' => 'id DESC',
            'use_buildin_recycle' => false,
            //'use_buildin_filter' => true,
            'use_view_tab' => true,
        ));
    }
    
    public function set_logs()
    {
        if($_POST){
            $url = 'index.php?app=ecorder&ctl=admin_gift_rule&act=set_logs';
            $this->begin($url);
            
            $set_type = $_POST['set_type'];
            $arr = array(
                'set_type' => $set_type=='include' ? 'include' : 'exclude',
                'op_user' => kernel::single('desktop_user')->get_name(),
                'create_time' => time(),
            );
            $this->app->model('gift_set_logs')->insert($arr);
            
            $this->end(true,'保存成功');
        }
        
        //以最后一次设定的模式为准
        //默认为叠加 include
        $set_type = 'include';
        $rs = $this->app->model('gift_set_logs')->getList('set_type', '', 0, 1, 'id DESC');
        if($rs){
            $set_type = $rs[0]['set_type'];
        }
        $this->pagedata['set_type'] = $set_type;
    
        $extra_view = array('ecorder'=>'admin/gift/set.html');
    
        $actions = array();
        $base_filter = array();
        $this->finder('ecorder_mdl_gift_set_logs',array(
            'title'=>'赠品设置',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'orderBy' => 'id DESC',
            'use_buildin_recycle' => false,
            'use_buildin_filter' => false,
            'use_view_tab' => false,
            'top_extra_view' => $extra_view,
        ));
    }
    
    public function ajax_get_goods()
    {
        $goods_bn = trim($_POST['goods_bn']);
        $sql = "select name from sdb_ecgoods_shop_goods where bn like '{$goods_bn}%' ";
        $rs = kernel::database()->selectRow($sql);
        if($rs){
            echo($rs['name']);
        }else{
            echo("商家编码 {$goods_bn} 可能不存在");
        }
    }
}