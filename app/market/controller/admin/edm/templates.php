<?php 
class market_ctl_admin_edm_templates extends desktop_controller {
    //管理模板
    public function themes(){
        $this->finder('market_mdl_edm_templates',array(
            'title'=>'邮件模板',
            'actions'=>array(
                array('label'=>'添加邮件模板','href'=>'index.php?app=market&ctl=admin_edm_templates&act=add_theme',
                             'target'=>'dialog::{width:700,height:500,title:\'添加邮件模板\'}'),
            ),
            'use_buildin_filter' => false,
            'use_buildin_recycle' => false,
            'orderBy' => 'status desc',
        ));
    }

    //添加模板
    function add_theme(){
    	 $groupList = $this->app->model('edm_tclass')->getList('*');
         $this->pagedata['groupList'] = $groupList;
    	 $this->display('admin/edm/edit_template.html');
    }
 //编辑模板
    public function edit_theme($theme_id){
        $theme_id = isset ($theme_id) && intval($theme_id) > 0 ? intval($theme_id) : 0;

        $themeObj = &$this->app->model('edm_templates');
        $data = $themeObj->dump($theme_id);
        $this->pagedata['data'] = $data;
        $groupList = $this->app->model('edm_tclass')->getList('*');
        $this->pagedata['groupList'] = $groupList;
        
        $this->display('admin/edm/edit_template.html');
    }

//保存模板
    public function save_theme(){
        $oThemes= &$this->app->model('edm_templates');
        $data = $_POST;

       $template_id = isset ($data['theme_id']) && intval($data['theme_id']) > 0 ? intval($data['theme_id']) : 0;
        $this->begin();
        if($template_id){
            $filter = array(
                'theme_id' => $template_id,
            );
            $res = $oThemes->dump(array('theme_title'=>$data['set']['market.message.sampletitle']),'theme_id');
            if($res && $res['theme_id'] != $template_id){
            	$this->end(false,app::get('b2c')->_('模板标题重复'));
            }
            $ret = $oThemes->update(array(
                'theme_title' => $data['set']['market.message.sampletitle'],
                'theme_content' => $data['set']['market.message.samplecontent'],
            	'type_id' =>  $data['group'],
            ),$filter);
        } else {
        	$res = $oThemes->dump(array('theme_title'=>$data['set']['market.message.sampletitle']));
        	if($res){
        		$this->end(false,app::get('b2c')->_('模板标题已存在'));
        	}
            $arr_data = array(
                'theme_title' => $data['set']['market.message.sampletitle'],
                'theme_content'=> $data['set']['market.message.samplecontent'],
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
	
	public function edit_status(){
		$this->begin('index.php?app=market&ctl=admin_edm_templates&act=themes');
		$template_id = $_GET['p'][0];
		if($_GET['p'][1]){
            $tempObj =  $this->app->model('edm_templates');
            $activeObj = $this->app->model('active');
            $filter = array(
            	'template_id' => $template_id,
            	'is_active'=>array('wait_exec','sel_template'),
            	'type'=>array(serialize(array('edm')),serialize(array('coupon','edm'))));
            $active = $activeObj->getList('active_id',$filter);
            if(!count($active)){
            	$rec=$tempObj->update(array('status'=>0),array('theme_id' => $template_id));
            	$this->end();
            }
            $this->end(false,app::get('market')->_('该模板下的营销活动存在未发送！'));
		}else{
            $tempObj =  $this->app->model('edm_templates');
            $rec=$tempObj->update(array('status'=>1),array('theme_id' => $template_id));
            $this->end();
		}
	}
	
	
}





?>