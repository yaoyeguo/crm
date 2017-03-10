<?php

class taocrm_ctl_admin_member_caselog_config extends desktop_controller{

    //var $workground = 'taocrm.member';
    
    public function __construct($app)
    {
        parent::__construct($app);
    }
    
    public function config()
    {
        $title = '服务媒体和类型';
        $actions = array();
        $baseFilter = array();
        $type = intval($_GET['type']);
        
        if($type==1){
            $actions[] = array(
                'label'=>'添加媒体',
                'href'=>'index.php?app=taocrm&ctl=admin_member_caselog&act=category_edit&type_id=1',
                'target'=>'dialog::{width:500,height:150,title:\'添加媒体\'}'
            );
        }elseif($type==2){
            $actions[] = array(
                'label'=>'添加类型',
                'href'=>'index.php?app=taocrm&ctl=admin_member_caselog&act=category_edit&type_id=2',
                'target'=>'dialog::{width:500,height:150,title:\'添加类型\'}'
            );
        }elseif($type==3){
            $actions[] = array(
                'label'=>'添加来源',
                'href'=>'index.php?app=taocrm&ctl=admin_member_caselog&act=category_edit&type_id=3',
                'target'=>'dialog::{width:500,height:150,title:\'添加来源\'}'
            );
        }elseif($type==4){
            $actions[] = array(
                'label'=>'添加状态',
                'href'=>'index.php?app=taocrm&ctl=admin_member_caselog&act=category_edit&type_id=4',
                'target'=>'dialog::{width:500,height:150,title:\'添加状态\'}'
            );
        }else{
            $actions[] = array(
                'label'=>'初始化',
                'href'=>'index.php?app=taocrm&ctl=admin_member_caselog&act=init_category',
                'target'=>'dialog::{width:500,height:150,title:\'初始化\'}'
            );
        }

        $this->finder('taocrm_mdl_member_caselog_category',
            array(
                'title'=> $title,
                'base_filter'=>$baseFilter,
                'actions'=>$actions,
                'use_buildin_import'=>false,
                'use_buildin_export'=>false,
                'use_buildin_recycle'=>true,
                'use_buildin_filter'=>false,
                'use_buildin_tagedit'=>true,
                'use_view_tab'=>true,
                'orderBy'=>'type ASC'
            )
        );
    }

    public function _views()
    {
        $member_caselog_category = $this->app->model('member_caselog_category');
        $sub_menu[] = array(
            'label' => '全部',
            'optional' => false,
        );
    
        $sub_menu[] = array(
            'label' => '媒体',
            'filter' => array('type' => 1),
            'optional' => false,
        );
        
        $sub_menu[] = array(
            'label' => '类型',
            'filter' => array('type' => 2),
            'optional' => false,
        );
        
        $sub_menu[] = array(
            'label' => '来源',
            'filter' => array('type' => 3),
            'optional' => false,
        );
        
        $sub_menu[] = array(
            'label' => '状态',
            'filter' => array('type' => 4),
            'optional' => false,
        );

        $i = 0;
        foreach($sub_menu as $k => $v){
            $sub_menu[$k]['addon'] = $member_caselog_category->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&type='.$v['filter']['type'].'&view='.$i++;
        }
        return $sub_menu;
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
        $rs = app::get('ecorder')->model('shop')->getlist('*');
        foreach((array)$rs as $v){
            if($v['name'] == '') continue;
            $res[$v['shop_id']] = $v['name'];
        }
        
        return $res;
    }
}
