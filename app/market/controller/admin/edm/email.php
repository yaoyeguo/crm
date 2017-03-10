<?php 
class market_ctl_admin_edm_email extends desktop_controller {
    //var $workground = 'sys.config';
    function index(){
        $title='邮件模板 ';
        $params=array(
            'title' => $title,
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,//设置标签操作
            'use_buildin_recycle'=>true,//删除操作 
            'use_buildin_export'=>false,//导出
            'use_buildin_import'=>false,//导入
            'use_buildin_filter'=>false,//高级筛选
            'use_buildin_setcol'=>true,//列配置
            'use_buildin_refresh'=>false,//刷新
            'actions' => array(
                array(
                    'label' => '添加模板',
                    'href' => 'index.php?app=market&ctl=admin_edm_email&act=addtempate',
                    'target' => "dialog::{width:700,height:400,title:'添加模板'}",
                ),
        ),
            
        );
       $this->finder('market_mdl_edm_templates',$params);
    }
	
    //添加模板的函数
    function addtempate() {
        $templateObj = $this->app->model('edm_template_type');
        $templateData = $this->app->model('edm_template');
        $class_data=$templateObj->getList();
        $this->pagedata['sel_data']=$class_data;
        $this->display("admin/edm/apptemplate.html");
    }



    function edit_tempate() {
        $this->begin('index.php?app=market&ctl=admin_edm_email&act=index');
        $templateObj = $this->app->model('edm_template_type');
        $templateData = $this->app->model('edm_template');
        $data['theme_id']=$_POST['theme_id'];
        $data['theme_title']=$_POST['theme_title'];
        $data['theme_content']=$_POST['theme_content'];
        $data['type_id']=$_POST['group'];
        $data['create_time']=time();
        if(empty($data['theme_id'])) {
                $templateData->save($data);
                $this->end(true, '保存完成！');
        }else {
            $templateData->update_data($data);
            $this->end(true, '保存完成！');
        }
        $this->display("admin/edm/apptemplate.html");
    }

    function edittempate() {
        $templateObj = $this->app->model('edm_template_type');
        $templateData = $this->app->model('edm_template');
        $class_data=$templateObj->getList();
        $this->pagedata['sel_data']=$class_data;
        $data_array=$templateData->dump(array('theme_id'=>$_GET[p][0]));
        $this->pagedata['data']=$data_array;
        $this->display("admin/edm/apptemplate.html");
    }
	    
	    /*
	     * 添加模板分类
	     */
    function template_type() {
	
        $title='模板分类 ';
        $params=array(
            'title' => $title,
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,//设置标签操作
            'use_buildin_recycle'=>true,//删除操作 
            'use_buildin_export'=>false,//导出
            'use_buildin_import'=>false,//导入
            'use_buildin_filter'=>false,//高级筛选
            'use_buildin_setcol'=>true,//列配置
            'use_buildin_refresh'=>false,//刷新
            'actions' => array(
                array(
                    'label' => '添加模板分类',
                    'href' => 'index.php?app=market&ctl=admin_edm_email&act=add_template',
                    'target' => "dialog::{width:700,height:400,title:'添加模板分类'}",
                ),
        ),
        );
        $this->finder('market_mdl_edm_template_type',$params);
    }

    function add_template() {
        $this->display("admin/edm/apptempate_type.html");
    }
    
    function addtempate_type() {
        $templateObj = $this->app->model('edm_template_type');
        if (!empty($_GET[p][0])) {
            $one_data = $templateObj->dump(array('type_id'=>$_GET[p][0]));
            $this->pagedata["data"]=$one_data;
        }
        $this->display("admin/edm/apptempate_type.html");
    }


    function save() {
        $this->begin('index.php?app=market&ctl=admin_edm_email&act=template_type');
        $templateObj = $this->app->model('edm_template_type');
        $data['create_time']=time();
        $data['title']=$_POST['title'];
        $data['remark']=$_POST['description'];
        $data['type_id']=$_POST['type_id'];
        if (empty($data['type_id'])){
            $templateObj->save($data);
            $this->end(true, '保存完成！');
        }else {
            $templateObj->update_data($data);
            $this->end(true, '更新完成！');
        }
    }
}
