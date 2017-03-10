<?php

class taocrm_ctl_admin_member_caselog extends desktop_controller{

    //var $workground = 'taocrm.member';
    
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
    
    public function index()
    {
        $title = '客户服务';
        $actions = array(

        );
        $baseFilter = array();
        
        //搜索参数
        if(isset($_POST['s'])){
            $s = $_POST['s'];
            foreach($s as $k=>$v){
                if(!$v) continue;
                if($k=='customer' or $k=='category' or $k=='media' or $k=='shop_id'){
                    $baseFilter[$k] = trim($v);
                }elseif($k=='time_from'){
                    $baseFilter["modified_time|bthan"] = strtotime($v);
                }elseif($k=='time_to'){
                    $baseFilter["modified_time|lthan"] = strtotime($v.' 23:59:59');
                }
            }
            $this->pagedata['s'] = $_POST['s'];
        }
        
        $rs_shop = $this->get_shops();
        $this->pagedata['rs_shop'] = $rs_shop;
        
        $category = $this->get_category();
        $this->pagedata['category'] = $category;
        
        $extra_view = array('taocrm'=>'admin/caselog/log_header.html');

        $this->finder('taocrm_mdl_member_caselog',array(
            'title'=> '客户服务记录',
            'base_filter'=>$baseFilter,
            'actions'=>$actions,
            'top_extra_view' => $extra_view,
        	'orderBy' => 'modified_time DESC',
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'use_buildin_setcol'=>false,//列配置
            'use_buildin_refresh'=>false,//刷新
            'finder_cols' => 'column_edit,source,column_uname,customer,column_mobile,category,media,agent,status,shop_id,modified_time',
        ));
    }
    
    public function caselog_edit()
    {
        if($_POST){
            $this->save_caselog();
        }
        
        $from = trim($_GET['from']); 
        $select_index = trim($_GET['select_index']); 
        $mobile = trim($_GET['mobile']); 
        $member_id = intval($_GET['member_id']);
        $id = intval($_GET['id']);
        
        //页面参数
        $category = $this->get_category();
        $this->pagedata['category'] = $category;
        
        $rs_shop = $this->get_shops();
        $this->pagedata['rs_shop'] = $rs_shop;

        if($id > 0){
            $rs = $this->app->model('member_caselog')->dump($id);
            if($rs['content']){
                $rs['content'] = preg_replace('/【(.+?)】/','【<font color="#5779BD">$1</font>】',$rs['content']);
            }
        }elseif($member_id > 0){
            $rs = $this->app->model('members')->dump($member_id);
            $rs['customer'] = $rs['contact']['name'];
            $rs['mobile'] = $rs['contact']['phone']['mobile'];
        }
        
        $this->pagedata['select_index'] = $select_index;
        $this->pagedata['mobile'] = $mobile;
        $this->pagedata['from'] = $from;
        $this->pagedata['rs'] = $rs;
        $this->display('admin/caselog/edit.html');
    }
    
    public function save_caselog()
    {
            $data = $_POST;
            
            if($data['from']=='callin'){
                $this->begin('index.php?app=market&ctl=admin_callcenter_callin&act=index&mobile='.$data['mobile'].'&select_index='.$data['select_index']);
            }elseif($data['from']=='callplan'){
                $this->begin('');
            }else{
            $this->begin('');
            }
            
            $data['modified_time'] = time();
            if($data['alarm_time']){
                $data['alarm_time'] = strtotime($data['alarm_time'].' '.$data['_DTIME_']['H']['alarm_time'].':'.$data['_DTIME_']['M']['alarm_time']);
            }
            $data['agent'] = kernel::single('desktop_user')->get_name();
            $id = intval($data['id']);
            $member_id = intval($data['member_id']);
        
        $data['content'] = '【'.$data['agent'].' '.date('y-m-d H:i').'】'.$data['content'];
        
            if($id==0){
                $data['create_time'] = time();
                $this->app->model('member_caselog')->insert($data);
            }else{
            if($data['old_content']){
                $data['content'] .= '<br/>'.$data['old_content'];
            }
                $this->app->model('member_caselog')->update($data,array('id'=>$id));
            }
            
            $last_caselog = json_encode(array(
                'customer'=>$data['customer'],
                'source'=>$data['source'],
                'category'=>$data['category'],
                'media'=>$data['media'],
                'status'=>$data['status'],
            ));
            $last_contact_time = time();
            $sql = "update sdb_taocrm_members set last_caselog='%s',last_contact_time='%s' ";
            if($id==0){
                $sql .= ',contact_times=contact_times+1 ';
            }
            $sql .= " where member_id={$member_id} ";
            $sql = sprintf($sql, $last_caselog, $last_contact_time);
            $this->app->model('member_caselog')->db->exec($sql);
            
            $this->end(true, '保存成功');
        }
        
    public function add()
    {    
        //finder初始化参数
        $title = '客户接待';
        $baseFilter = array();
        $extra_view = array('taocrm'=>'admin/caselog/header.html');
        $actions = array();
        $member_add_url = 'index.php?app=taocrm&ctl=admin_member&act=add_member&from=caselog';
        
        //全网客户添加
        if(kernel::single('taocrm_system')->get_version_code()=='Pro_Ver'){
            $member_add_url = 'index.php?app=taocrm&ctl=admin_all_member&act=add_member';
        }
        
        //搜索参数
        $q_params = array();
        if(isset($_GET['s'])) $q_params = $_GET['s'];
        if(isset($_POST['s'])) $q_params = $_POST['s'];
        if($q_params){
            $member_ids = array();
            $mobile = trim($q_params['mobile']);
            $order_bn = trim($q_params['order_bn']);
            $truename = trim($q_params['truename']);
        
            $wherestr = '';
            if($mobile) {
                $sql = "select member_id from sdb_taocrm_members where mobile='{$mobile}' ";
                $rs = kernel::database()->select($sql);
                if($rs){
                    foreach($rs as $v){
                        $member_ids[$v['member_id']] = $v['member_id'];
                    }
                }
                
                $sql = "select member_id from sdb_ecorder_orders where ship_mobile like '{$mobile}%' ";
                $rs = kernel::database()->select($sql);
                if($rs){
                    foreach($rs as $v){
                        $member_ids[$v['member_id']] = $v['member_id'];
                    }
                }
            }
            if($order_bn) {
                $sql = "select member_id from sdb_ecorder_orders where order_bn like '{$order_bn}%' "; 
                $rs = kernel::database()->select($sql);
                if($rs){
                    foreach($rs as $v){
                        $member_ids[$v['member_id']] = $v['member_id'];
                    }
                }
            }
            if($truename) {
                $sql = "select member_id from sdb_taocrm_members where uname like '{$truename}%' ";
                $rs = kernel::database()->select($sql);
                if($rs){
                    foreach($rs as $v){
                        $member_ids[$v['member_id']] = $v['member_id'];
                    }
                }
                
                $sql = "select member_id from sdb_taocrm_members where name like '{$truename}%' ";
                $rs = kernel::database()->select($sql);
                if($rs){
                    foreach($rs as $v){
                        $member_ids[$v['member_id']] = $v['member_id'];
                    }
                }
                
                //ship_name无索引，暂时不参与搜索
                $sql = "select member_id from sdb_ecorder_orders where ship_name='{$truename}' ";
                //$rs = kernel::database()->select($sql);
                if($rs){
                    foreach($rs as $v){
                        $member_ids[$v['member_id']] = $v['member_id'];
                    }
                }
            }        
            
            if(!$member_ids){
                $baseFilter['member_id'] = -1;
            }else{
                foreach($member_ids as $v){
                    $member_id = $v;
                    $member_ids[$v] = $v;
                }
                $baseFilter['member_id'] = $member_ids;
                $this->pagedata['member_id'] = $member_id;
            }
            $this->pagedata['has_member'] = count($member_ids);
            $this->pagedata['s'] = $q_params;
        }else{
            $baseFilter['member_id'] = -1;
            $this->pagedata['has_member'] = -1;
        }

        $this->pagedata['member_add_url'] = $member_add_url;
        $this->finder('taocrm_mdl_members',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
            'top_extra_view' => $extra_view, 
            'actions'=>$actions,
        	'orderBy' => '',
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
            'finder_cols' => 'column_edit,uname,name,mobile,addr,area,order_succ_num,order_succ_amount,create_time,last_contact_time,contact_times',
            'use_buildin_setcol'=>true,//列配置
            'use_buildin_refresh'=>false,//刷新
        ));
    }

    public function _views()
    {
        $member_caselog = $this->app->model('member_caselog');
        $category = $this->get_category();
        $sub_menu[] = array(
            'label' => '全部',
            'optional' => false,
        );
    
        foreach($category[4] as $k=>$v){
        $sub_menu[] = array(
                'label' => $v,
                'filter' => array('status' => $k),
            'optional' => false,
        );
        }

        $i = 0;
        foreach($sub_menu as $k => $v){
            $sub_menu[$k]['addon'] = $member_caselog->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&type='.$v['filter']['type'].'&view='.$i++;
        }
        return $sub_menu;
    }
    
    public function alarm()
    {
        $title = '事件提醒';
        $baseFilter = array(
            'alarm_time|than' => 0,
        );
        
        if(!isset($_POST['s'])){
            $_POST['s']['time_from'] = date("Y-m-d");
            $_POST['s']['time_to'] = date("Y-m-d");
        }
        
        //搜索参数
        if(isset($_POST['s'])){
            $s = $_POST['s'];
            foreach($s as $k=>$v){
                if(!$v) continue;
                if($k=='customer' or $k=='category' or $k=='media' or $k=='shop_id'){
                    $baseFilter[$k] = trim($v);
                }elseif($k=='time_from'){
                    $baseFilter["alarm_time|bthan"] = strtotime($v);
                }elseif($k=='time_to'){
                    $baseFilter["alarm_time|lthan"] = strtotime($v.' 23:59:59');
                }
            }
            $this->pagedata['s'] = $_POST['s'];
        }
        
        $actions = array(
        );
        
        $rs_shop = $this->get_shops();
        $this->pagedata['rs_shop'] = $rs_shop;
        
        $extra_view = array('taocrm'=>'admin/caselog/alarm_header.html');

        $this->finder('taocrm_mdl_member_caselog',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
            'actions'=>$actions,
            'top_extra_view' => $extra_view, 
        	'orderBy' => '',
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
            'use_view_tab'=>false,
            'use_buildin_setcol'=>false,//列配置
            'use_buildin_refresh'=>false,//刷新
            'use_buildin_new_dialog' => false,
        ));
    }
    
    public function caselog_list()
    {
        $title = '客户服务';
        $actions = '';
        $baseFilter = array();

        $view = (isset($_GET['view'])) ? max(0, intval($_GET['view'])) : 0;
        $baseFilter['shop_id'] = $shops[$view];
        if ($baseFilter['shop_id'] == '') {
            if  (isset($_GET['shop_id'])) {
                $baseFilter['shop_id'] = $_GET['shop_id'];
                unset($_GET['shop_id']);
            }
            else {
                $baseFilter['shop_id'] = $shops[0];
            }
        }
        
        $actions = array(
            array(
                'label'=>'添加',
                'href'=>'index.php?app=taocrm&ctl=admin_member_caselog&act=add',
                'target'=>'dialog::{width:500,height:350,title:\'添加客户服务\'}'
            ),
        );

        $this->finder('taocrm_mdl_member_caselog',array(
            'title'=> '客户服务记录',
            'base_filter'=>$baseFilter,
            'actions'=>$actions,
        	'orderBy' => '',
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
        ));
    }
    
    public function config()
    {
        $this->redirect('index.php?app=taocrm&ctl=admin_member_caselog_config&act=config');
    }
    
    public function init_category()
    {
        if($_POST['task']=='init'){
            $url = 'index.php?app=taocrm&ctl=admin_member_caselog&act=config';
            $this->begin($url);
            
            kernel::database()->exec('begin');
            
            $sql = "TRUNCATE TABLE `sdb_taocrm_member_caselog_category`";
            kernel::database()->exec($sql);
            
            $sql = "INSERT INTO `sdb_taocrm_member_caselog_category` (`category_id`,`category_name`,`desc`,`type`,`create_time`,`status`) VALUES 
            (1, '电话', '', '1', UNIX_TIMESTAMP(), 1),
            (2, '旺旺', '', '1', UNIX_TIMESTAMP(), 1),
            (3, 'QQ', '', '1', UNIX_TIMESTAMP(), 1),
            (4, '微信', '', '1', UNIX_TIMESTAMP(), 1),
            (5, 'SMS', '', '1', UNIX_TIMESTAMP(), 1),
            (6, '退换货', '', '2', UNIX_TIMESTAMP(), 1),
            (7, '售前咨询', '', '2', UNIX_TIMESTAMP(), 1),
            (8, '回访', '', '2', UNIX_TIMESTAMP(), 1),
            (9, '新品推荐', '', '2', UNIX_TIMESTAMP(), 1),
            (10, '客户咨询', '', '3', UNIX_TIMESTAMP(), 1),
            (11, '主动服务', '', '3', UNIX_TIMESTAMP(), 1),
            (12, '其他', '', '3', UNIX_TIMESTAMP(), 1),
            (13, '完成', '', '4', UNIX_TIMESTAMP(), 1),
            (14, '需要跟进', '', '4', UNIX_TIMESTAMP(), 1),
            (15, '升级', '', '4', UNIX_TIMESTAMP(), 1),
            (16, '其他', '', '4', UNIX_TIMESTAMP(), 1)";
            kernel::database()->exec($sql);
            
            kernel::database()->commit();
            
            $this->end(true,'操作成功');
        }
        $this->display('admin/caselog/category_init.html');
    }
    
    public function category_edit()
    {
        if($_POST){
            $this->begin('index.php?app=taocrm&ctl=admin_member_caselog&act=config');
            $save_data = array(
                'category_name'=>trim($_POST['category_name']),
                'desc'=>trim($_POST['desc']),
                'type'=>trim($_POST['type']),
                'create_time'=>time(),
            );
            $category_id = intval($_POST['category_id']);
            if($category_id==0){
                $this->app->model('member_caselog_category')->insert($save_data);
            }else{
                $this->app->model('member_caselog_category')->update($save_data, array('category_id'=>$category_id));
            }
            $this->end('true', '保存成功');
        }
        
        $rs = array(
            'type'=>intval($_GET['type_id'])
        );
        
        $category_id = intval($_GET['category_id']);
        if($category_id > 0){
            $rs = $this->app->model('member_caselog_category')->dump($category_id);
        }
        
        switch($rs['type']){
            case 1:$example='例如：电话、旺旺，QQ、微信';break;
            case 2:$example='例如：退换货、售前咨询，回访、新品推荐';break;
            case 3:$example='例如：客户咨询,主动服务,其他';break;
            case 4:$example='例如：完成,需要跟进,升级,其他';break;
        }

        $this->pagedata['rs'] = $rs;
        $this->pagedata['example'] = $example;
        $this->display('admin/caselog/category_edit.html');
    }
    
    public function get_category()
    {
        $rs = $this->app->model('member_caselog_category')->getlist('*');
        foreach((array)$rs as $v){
            $res[$v['type']][$v['category_id']] = $v['category_name'];
        }
        
        return $res;
    }
    
    public function get_shops()
    {
        $rs = app::get('ecorder')->model('shop')->get_shops('all');
        foreach((array)$rs as $v){
            if($v['name'] == '') continue;
            $res[$v['shop_id']] = $v['name'];
        }
        
        return $res;
    }
}
