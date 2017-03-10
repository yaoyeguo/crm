<?php
class openapi_ctl_admin_setting extends desktop_controller{
    var $name = "基础设置";
    var $workground = "setting_tools";

    public function index(){

        $this->finder('openapi_mdl_setting',array(
            'title'=>'基础配置',
            'actions' => array(
                 array('label'=>'添加','href'=>'index.php?app=openapi&ctl=admin_setting&act=addNew','target'=>'_blank'),
            ),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>true,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>true,
            'orderBy' =>'s_id DESC'
	    ));
    }

    public function addNew(){
        $this->pagedata['configLists'] = openapi_conf::getMethods();
        $this->singlepage('admin/setting/detail.html');
    }

    public function edit($id){
        $settingObj = &$this->app->model('setting');
        $settingInfo = $settingObj->dump($id);
        $this->pagedata['settingInfo'] = $settingInfo;
        $this->pagedata['configLists'] = openapi_conf::getMethods();
        $this->singlepage('admin/setting/detail.html');
    }

    public function save(){
        $this->begin('index.php?app=openapi&ctl=admin_setting&act=index');
        $settingObj = &$this->app->model('setting');

        $data = array(
            'name' => trim($_POST['name']),
            'config' => $_POST['config'],
            'status' => $_POST['status'] == 1 ? 1 : 0,
            'interfacekey' => trim($_POST['interfacekey']),
        );

        if(empty($_POST['s_id'])){
            $settingInfo = $settingObj->dump(array('code'=>$_POST['code']),'s_id');
            if($settingInfo){
                $this->end(false,'标识已存在');
            }
            $data['code'] = trim($_POST['code']);
        }else{
            $settingInfo = $settingObj->dump(array('s_id'=>$_POST['s_id']),'s_id,code');
            if(!$settingInfo){
                $this->end(false,'配置信息不存在');
            }
    		$data['s_id'] = $_POST['s_id'];
        }

        if($settingObj->save($data)){
            $this->end(true,'保存成功');
        }else{
            $this->end(false,'保存失败');
        }

    }

    public function setStatus($sid,$status){
        $settingObj = &$this->app->model('setting');
        $data = array(
            's_id' => $sid,
            'status' => $status,
        );
        $settingObj->save($data);
        echo "<script>parent.MessageBox.success('更新成功!');parent.finderGroup['{$_GET[finder_id]}'].refresh();</script>";
        exit;
    }

    public function api_log(){
        $this->finder('openapi_mdl_api_log',array(
            'title'=>'API调用日志',
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>true,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>true,
            'orderBy' =>'log_id DESC'
        ));
    }
}
?>
