<?php 
class market_ctl_admin_sms_templates extends desktop_controller {
    //管理模板
    public function themes()
    {
        $actions = array(
            array(
                'label'=>'添加短信模板',
                'href'=>'index.php?app=market&ctl=admin_sms_templates&act=add_theme',
                'target'=>'dialog::{width:700,height:500,title:\'添加短信模板\'}'
            ),
            array(
                'label' => '添加模板分类',
                'href' => 'index.php?app=market&ctl=admin_sms_template_type&act=themeAdd',
                'target' => 'dialog::{width:680,height:250,title:\'添加模板分类\'}',
            ),
            array(
                'label' => '云模板',
                'href' => 'index.php?app=market&ctl=admin_sms_templates&act=cloud',
                'target' => 'dialog::{width:700,height:365,title:\'云模板\'}',
            ),
        );
    
        if( ! isset($_GET['view'])){
            $base_filter = array('status'=>1);
        }
    
        $this->finder('market_mdl_sms_templates',array(
            'title'=>'短信模板',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'use_buildin_recycle'=>false,
            'use_buildin_recycle' => false,
            'orderBy' => 'template_id desc,status desc',
        ));
    }
    
    public function _views()
    {
        $mdl_sms_templates = app::get('market')->model('sms_templates');

        $sub_menu[] = array(
            'label'=> '可用',
            'filter'=> array('status'=>1),
            'optional'=>false,
        );
        
        $sub_menu[] = array(
            'label'=>'已禁用',
            'filter'=>array('status'=>0),
            'optional'=>false,
        );

        $i=0;
        foreach($sub_menu as $k=>$v){
            $count =$mdl_sms_templates->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }
    
    function cloud()
    {
        if($_POST){
            $this->cloud_sync();
            exit;
        }
        
        $page = intval($_GET['page2']);
        if($page==0) $page ++;
        $page_size = 10;
        $params = array(
            'offset' => ($page-1) * $page_size,
            'limit' => $page_size,
        );
        
        if( ! defined('MONITOR_URL')){
            define('MONITOR_URL','http://monitor.crmm.taoex.com/');
        }
        
        $http = new base_httpclient;
        $url = MONITOR_URL.'index.php/openapi/taocrm.sms/get_templates/';
        $res = $http->post($url, $params);
        $res = json_decode($res, true);
        //var_dump($res['total']);
        
        //将接口数据缓存到kv
        base_kvstore::instance('market')->store('cloud_template', json_encode($res['data']));
        
        //已经存在的云模板id
        $cloud_ids = $this->get_cloud_ids();
        
        $data = $res['data'];
        foreach($data as $k=>$v){
            $data[$k]['sub_content'] = mb_substr($v['content'], 0, 15, 'utf-8');
            
            if(isset($cloud_ids[$v['id']])){
                $data[$k]['has_sync'] = $cloud_ids[$v['id']];
            }else{
                $data[$k]['has_sync'] = 0;
    }
        }
        
        $pager = $this->ui()->pager ( array ('current' => $page, 'total' => ceil($res['total']/$page_size), 'link' =>'index.php?app=market&ctl=admin_sms_templates&act=cloud&page2=%d'));
        
        $this->pagedata['data'] = $data;
        $this->pagedata['pager'] = $pager;
        $this->display('admin/sms/cloud_templates.html');
    }
    
    //同步云模板到本地
    function cloud_sync()
    {
        $url = 'index.php?app=market&ctl=admin_sms_templates&act=themes';
        $this->begin($url);
        
        //从kv读取缓存的接口数据
        base_kvstore::instance('market')->fetch('cloud_template', $data);
        $data = json_decode($data, true);
        foreach($data as $v){
            $cloud_templates[$v['id']] = $v; 
        }
        
        //已经存在的云模板id
        $cloud_ids = $this->get_cloud_ids();
        
        $cloud_id = $_POST['cloud_id'];
        foreach($cloud_id as $v){
            $arr = array(
                'title' => $cloud_templates[$v]['title'],
                'content' => $cloud_templates[$v]['content'],
                'cloud_id' => $v,
                'create_time' => time(),
                'is_fixed' => 0,
                'status' => 1,
            );
            
            if(isset($cloud_ids[$v])){
                $arr = array(
                    'template_id' => $cloud_ids[$v],
                    'title' => $cloud_templates[$v]['title'],
                    'content' => $cloud_templates[$v]['content'],
                    'cloud_id' => $v,
                    'create_time' => time(),
                );
                $this->app->model('sms_templates')->save($arr);
            }else{
                $this->app->model('sms_templates')->insert($arr);
            }
        }
        
        $this->end(true,'保存成功');
    }
    
    function get_cloud_ids()
    {
        $cloud_ids = array();
        $rs = $this->app->model('sms_templates')->getList('cloud_id,template_id', array('cloud_id|than'=>0));
        if($rs){
            foreach($rs as $v){
                $cloud_ids[$v['cloud_id']] = $v['template_id'];
            }
        }
        return $cloud_ids;
    }
    
    //添加模板
    function add_theme()
    {
    	 $groupList = $this->app->model('sms_template_type')->getList('*');
         $this->pagedata['groupList'] = $groupList;
    	 $this->display('admin/sms/edit_template.html');
    }
    
 //编辑模板
    public function edit_theme($theme_id)
    {
        $theme_id = isset ($theme_id) && intval($theme_id) > 0 ? intval($theme_id) : 0;
        
        $themeObj = &$this->app->model('sms_templates');
        $data = $themeObj->dump($theme_id);
        $this->pagedata['data'] = $data;
        $groupList = $this->app->model('sms_template_type')->getList('*');
        $this->pagedata['groupList'] = $groupList;
        
        $this->display('admin/sms/edit_template.html');
    }

//保存模板
    public function save_theme()
    {
        $oThemes= &$this->app->model('sms_templates');
        $data = $_POST;
        $template_id = isset ($data['theme_id']) && intval($data['theme_id']) > 0 ? intval($data['theme_id']) : 0;
        $this->begin();
        if($template_id){
            $filter = array(
                'template_id' => $template_id,
            );
            $res = $oThemes->dump(array('title'=>$data['set']['market.message.sampletitle']),'template_id');
            if($res && $res['template_id'] != $template_id){
            	$this->end(false,app::get('b2c')->_('模板标题重复'));
            }
            $ret = $oThemes->update(array(
                'title' => $data['set']['market.message.sampletitle'],
                'content' => $data['set']['market.message.samplecontent'],
            	'type_id' =>  $data['group'],
            ),$filter);
        } else {
        	$res = $oThemes->dump(array('title'=>$data['set']['market.message.sampletitle']));
        	if($res){
        		$this->end(false,app::get('b2c')->_('模板标题已存在'));
        	}
            $arr_data = array(
                'title' => $data['set']['market.message.sampletitle'],
                'content' => $data['set']['market.message.samplecontent'],
            	'type_id' => $data['group'],
                'create_time' => time(),
            );
            $ret = $oThemes->insert($arr_data);
        }

        if($ret){
            $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
             $this->end(false,app::get('b2c')->_('操作失败'));
        }
    }
	
    public function edit_status()
    {
		$this->begin('index.php?app=market&ctl=admin_sms_templates&act=themes');
		$template_id = $_GET['p'][0];
		if($_GET['p'][1]){
            $tempObj =  $this->app->model('sms_templates');
            $activeObj = $this->app->model('active');
            $filter = array(
            	'template_id' => $template_id,
            	'is_active'=>array('wait_exec','sel_template'),
            	'type'=>array(serialize(array('sms')),serialize(array('coupon','sms'))));
            $active = $activeObj->getList('active_id',$filter);
            if(!count($active)){
            	$rec=$tempObj->update(array('status'=>0),array('template_id' => $template_id));
            	$this->end();
            }
            $this->end(false,app::get('market')->_('该模板下的营销活动存在未发送！'));
		}else{
            $tempObj =  $this->app->model('sms_templates');
            $rec=$tempObj->update(array('status'=>1),array('template_id' => $template_id));
            $this->end();
		}
	}
}
