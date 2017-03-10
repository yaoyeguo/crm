<?php

class plugins_ctl_admin_manage extends desktop_controller{

    var $workground = 'plugins.manage';

    public function __construct($app){
        parent::__construct($app);
    }

    public function index()
    {
        //调试代码
        //kernel::single('plugins_service_api')->run_task();
        //kernel::single('plugins_service_api')->run_hour();

        if(!$_GET['view']) $_GET['view'] = 0;

        $this->finder('plugins_mdl_plugins',array(
            'title'=>'插件设置',
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        ));
    }

    public function _views()
    {
        $oPlugins = $this->app->model('plugins');

        $sub_menu[] = array(
            'label'=> '已启用',
            'filter'=> array('end_time|than'=>time()),
            'optional'=>false,
        );

        $sub_menu[] = array(
            'label'=> '已禁用',
            'filter'=> array('end_time|sthan'=>time()),
            'optional'=>false,
        );

        $i=0;
        foreach($sub_menu as $k=>$v){
            $count =$oPlugins->count($v['filter']);
            if($count==0){
                unset($sub_menu[$k]);
                continue;
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=plugins&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }

    public function set($plugin_id){

        if($_POST){
            $this->_save();
            return false;
        }

        $oPlugins = $this->app->model('plugins');
        $rs_plugin = $oPlugins->dump($plugin_id);
        $params = json_decode($rs_plugin['params'],1);

        $plugins = kernel::single($rs_plugin['worker'])->get_desc();
        $items = kernel::single($rs_plugin['worker'])->get_items();

        $plugins['tags'][] = '签名';

        //获取短信模板内容
        $oTemplates = app::get('market')->model('sms_templates');
        $params['send_content'] = intval($params['send_content']);
        if($params['send_content']>0){
            $rs = $oTemplates->dump($params['send_content']);
            if($rs)
                $params['send_content'] = $rs['content'];
        }

        if(!$params['send_content1'] && $items['send_content1'])
            $params['send_content1'] = $items['send_content1']['options'];
        if(!$params['send_content2'] && $items['send_content2'])
            $params['send_content2'] = $items['send_content2']['options'];
        if(!$params['send_content3'] && $items['send_content3'])
            $params['send_content3'] = $items['send_content3']['options'];

        $this->pagedata['params'] = $params;
        $this->pagedata['rs'] = $rs_plugin;
        $this->pagedata['items'] = $items;
        $this->pagedata['plugins'] = $plugins;
        if(!in_array($rs_plugin['worker'],array('plugins_service_check','plugins_service_genius'))){
                //检测是否需要设置签名
                $need_sign = 'none';
                $chk_sms_sign = app::get('ecorder')->model('shop')->chk_sms_sign();
                if($chk_sms_sign == false){
                    $need_sign = 'block';
                }
                $this->pagedata['need_sign'] = $need_sign;

	        if(isset($plugins['view']) && $plugins['view']!=''){
	            $this->display('admin/config/'.$plugins['view'].'.html');
	        }else{
	            $this->display('admin/config.html');
	        }
        }else{
        	$this->display('admin/check_config.html');
        }
    }

    //保存插件设置
    private function _save()
    {
        $this->begin('index.php?app=plugins&ctl=admin_manage&act=index');

        $oPlugins = $this->app->model('plugins');
        $oTemplates = app::get('market')->model('sms_templates');

        //如果有短信内容
        $sms_content = $_POST['params']['send_content'];
        if($sms_content){
            $sms_content = str_replace("\n",'',$sms_content);//短信内容过滤换行符
            $filter = array('title'=>$_POST['plugin_name'],'is_fixed'=>1);
            $temp_arr = $oTemplates->dump($filter,'template_id');
            if($temp_arr){
                $temp_arr['content'] = $sms_content;
                $oTemplates->update($temp_arr,array('template_id'=>$temp_arr['template_id']));
            }else{
                $temp_arr['content'] = $sms_content;
                $temp_arr['title'] = $_POST['plugin_name'];
                $temp_arr['type_id'] = 0;
                $temp_arr['create_time'] = time();
                $temp_arr['is_fixed'] = 1;
                $oTemplates->insert($temp_arr);
            }
            $_POST['params']['send_content'] = $temp_arr['template_id'];
        }

        $plugin_id = intval($_POST['plugin_id']);
        $params = json_encode($_POST['params']);
        $arr = array('plugin_id'=>$plugin_id,'params'=>$params);

        $oPlugins->save($arr);

        $this->end(true,'保存成功');
    }

    public function logi_info()
    {
        $tid = trim($_GET['tid']);
        $oTrade = app::get('plugins')->model('trades');
        $rs = $oTrade->getList('*', array('tid'=>$tid));
        $transit_step_info = (json_decode($rs[0]['transit_step_info'], true));
        echo($rs[0]['logi_company'].'<br/>');
        foreach($transit_step_info as $v){
            echo($v['status_time'].' '.$v['status_desc'].'<br/>');
        }
        //echo($tid);
    }

    public function sel_sms_template(){

        $oSMSTemplete = app::get('market')->model('sms_templates');
        $sms_templates = $oSMSTemplete->getList('title,content');
        $this->pagedata['sms_templates'] = $sms_templates;
        $this->pagedata['setid'] = !empty($_GET['setid']) ? trim($_GET['setid']) : 'sms_content';
        $this->display('admin/sms_template.html');
    }
}

