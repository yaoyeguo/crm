<?php
class taocrm_ctl_admin_sms extends desktop_controller{
    var $workground = 'rolescfg';

    public function index_bak(){
        $this->page('admin/sms/basic.html');
    }

    public function index(){ //申请短信应用register
        $certi_id = base_certificate::get('certificate_id');
        $sess_id = '';
        $auth = mktime();
        $ac = md5($certi_id.'SHOPEX_SMS'.$auth);
        $url = 'http://sms-service.shopex.cn/txh_index.php?certificate_id='.$certi_id.'&sess_id='.$sess_id.'&auth='.$auth.'&ac='.$ac.'&source=ecos_taocrm';

        $this->pagedata['url'] = $url;
        $this->page('admin/sms/register.html');
    }

    public function gateway(){ //申请网关
        $url = 'http://idx.sms.shopex.cn/service.php';
        $token = '6c6b5fbfd484c831364a6303cc297dec10233e25a21b96b800cb21d0a3f7d6cb';
        //$token = base_certificate::get('token');
        $data['certi_id'] = 1;
        //$data['certi_id'] = base_certificate::get('certificate_id');
        $data['version'] = '1.0';
        $data['source'] = 'ecos_taocrm';
        $data['ac'] = md5($data['certi_id'].$token);

        $http = new base_httpclient;
        if($result=$http->post($url,$data)){
            $result = explode('|',$result);
            return $result[1];
        }
    }

    public function send(){ //发送信息
        $url = $this->gateway();
        $token = '6c6b5fbfd484c831364a6303cc297dec10233e25a21b96b800cb21d0a3f7d6cb';
        //$token = base_certificate::get('token');
        $data['certi_id'] = 1;
        //$data['certi_id'] = base_certificate::get('certificate_id');
        $data['version'] = '1.0';
        $data['ex_type'] = '1';
        $data['source'] = 'ecos_taocrm';
        $send_arr = array(
            0 => array(
                0 => '13671733580', //发送的手机号码
                1 => '你获得了TaoCRM发送的优惠券', //发送信息(不能为空且不能为非法字符)
                2 => 'Now' //发送的时间
            )
        );
        $data['content']  = json_encode($send_arr);
        $data['encoding'] = 'utf8';

        $data['ac'] = md5($data['certi_id'].$data['ex_type'].$data['content'].$data['encoding'].$token);

        $http = new base_httpclient;
        if($result=$http->post($url,$data)){
            $result = explode('|',$result);
            var_dump($result[0]);
        }
    }

    public function get_off(){ //获得短信条数
        $url = 'http://service.shopex.cn/';
        $data['certi_app'] = 'sms.get_off';
        $token = '6c6b5fbfd484c831364a6303cc297dec10233e25a21b96b800cb21d0a3f7d6cb';
        //$token = base_certificate::get('token');
        $data['certi_id'] = 1;
        //$data['certi_id'] = base_certificate::get('certificate_id');
        $data['format'] = 'json';
        $str = $data['certi_app'].$data['certi_id'].$data['format'];
        $data['certi_ac'] = md5($str.$token);

        $http = new base_httpclient;
        if($result=$http->post($url,$data)){
            $result = json_decode($result,true);
            var_dump($result['info']);
        }
    }

    //管理模板
    public function themes(){
        $this->finder('taocrm_mdl_message_themes',array(
            'title'=>'短信模板',
            'actions'=>array(
                array('label'=>'添加短信模板','href'=>'index.php?app=taocrm&ctl=admin_sms&act=edit_theme',
                             'target'=>'dialog::{width:680,height:260,title:\'添加短信模板\'}'),
            ),
            'use_buildin_recycle'=>false,
            'use_buildin_recycle' => true,
        ));
        
    }

    //编辑模板
    public function edit_theme($theme_id){
        $theme_id = isset ($theme_id) && intval($theme_id) > 0 ? intval($theme_id) : 0;
        
        $themeObj = &$this->app->model('message_themes');
        $data = $themeObj->dump($theme_id);
        $this->pagedata['data'] = $data;
        
        $groupList = $this->app->model('message_themes_group')->getList('*');
        $this->pagedata['groupList'] = $groupList;
        
        $this->display('admin/sms/edit_theme.html');
    }
    //保存模板
    public function save_theme($theme_id){
        $oThemes= &$this->app->model('message_themes');
        $data = $_POST;
       
        $theme_id = isset ($data['theme_id']) && intval($data['theme_id']) > 0 ? intval($data['theme_id']) : 0;
        $this->begin();
        if($theme_id){
            $filter = array(
                'theme_id' => $theme_id,
            );
            $ret = $oThemes->update(array(
                'theme_title' => $data['theme_title'],
                'theme_content' => $data['theme_content'],
            	'group_id' => $data['group'],
            ),$filter);
        } else {
            $arr_data = array(
                'theme_title' => $data['theme_title'],
                'theme_content' => $data['theme_content'],
            	'group_id' => $data['group'],
                'theme_posttime' => time(),
            );
            $ret = $oThemes->insert($arr_data);
        }

        if($ret){
            $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
             $this->end(false,app::get('b2c')->_('操作失败'));
        }
    }

    public function getThemes($shop_id){
        $themeObj = &$this->app->model('message_themes');
        $themes = $themeObj->getList();
        if($themes){
            $this->pagedata['themes'] = $themes;
            echo $this->fetch('admin/sms/theme_list.html');
        }else{
            echo '<span class="red">没有有效的短信模板</span>';
        }
    }
    
    public function getTheme($theme_id) {
    	$theme = $this->app->model('message_themes')->dump(array('theme_id' => $theme_id));
    	echo $theme['theme_content'];
    }
    
    public function sendCustomerSms() {
    	
    }
}