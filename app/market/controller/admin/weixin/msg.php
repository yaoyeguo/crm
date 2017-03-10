<?php

class market_ctl_admin_weixin_msg extends desktop_controller {

    public function __construct($app)
    {
        parent::__construct($app);
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        $this->wxuser = $wxuser;
    }

    public function send_msg($id = null)
    {
        if(!$_POST){
            $this->pagedata['id'] = $id;
            $this->pagedata['msg'] = '是否确定发送？';
            $this->pagedata['app'] = $_GET['app'];
            $this->pagedata['ctl'] = $_GET['ctl'];
            $this->pagedata['act'] = $_GET['act'];
            $this->display('admin/weixin/group/send_msg.html');
        }else{
            $this->begin('index.php?app=market&ctl=admin_weixin&act=msg_send_all');
            $id = $_POST['id'];
            $send_type = ($_POST['send_type']=='all') ? 'all' : 'openid';
            $openids = $_POST['openid'];
            $openids = kernel::single('ecorder_func')->clear_value($openids);

            $msg_info = $this->get_send_info($id);
            if($send_type=='all'){
                $user_list_arr = $this->get_user_list();
            }else{
                if(!$openids or count($openids)<2) {
                   $this->end(false, '请输入至少两个微信OpenID'); 
                }
                foreach($openids as $v){
                    $user_list_arr[0][] = array(
                        'FromUserName' => $v,
                    );
                }
            }

            $msg_log_mod = app::get('market')->model('wx_msg_send_all_log');
            foreach($user_list_arr as $user_list){
                foreach($user_list as $user){
                    !empty($user['FromUserName']) && $user_arr[] = $user['FromUserName'];
                }
                $msg_info['touser'] = $user_arr;
                $rs = $this->push_msg_to_wx('message_mass_send',$msg_info);
                //$rs = $this->push_msg_to_wx('message_mass_send',$msg_info,null,1);

                $log['send_openid'] = $this->wxuser['appid'];
                $log['error_code'] = $rs['errcode'];
                $log['error_msg'] = $rs['errmsg'];
                $log['msg_id'] = $rs['msg_id'] ? $rs['msg_id'] : 0;
                $log['create_time'] = time();
                $log['update_time'] = time();
                $log['send_msg_id'] = $id;
                $log['send_list'] = implode(',',$user_arr);
                $log['send_time'] = time();
                $log['send_man'] = kernel::single('desktop_user')->get_name();

                $msg_log_mod->save($log);
            }
            $this->end(true,'发送完成');
        }
    }
    
    public function get_user_list()
    {
        $msg_mod = app::get('market')->model('wx_member');
        $last_count = $user_count = $msg_mod->count();
              
        do{
            $limit = min($last_count,10000);
            $offset = 0; 
            $user_list[] = $msg_mod->getList('FromUserName',array(),$offset,$limit);
            $last_count -= 10000;
            $offset += 10000;
        }
        while($last_count > 10000);
        return $user_list;
    }
    
    //获取发送内容
    public function get_send_info($id = null)
    {
        $msg_mod = app::get('market')->model('wx_msg_send_all');
        $info = $msg_mod->dump($id);
        if($info['send_type'] == 'msg'){
            $msg_info = array(
                'msgtype' => 'text',
                'text' => array('content' => json_decode($info['msg_content'],true)),
            );
        }else{
            $msg_info = array(
                'msgtype' => 'mpnews',
                'mpnews' => array('media_id' => $info['media_id']),
            );
        }
        return $msg_info;
    }

    //发送数据调用微信接口函数
    public function push_msg_to_wx($fun_name = null,$data=null,$api_get_param = null,$test_api = false)
    {
        //1.矩阵的微信接口
        $wechat_shops = app::get('ecorder')->model('shop')->get_shops('wechat');
        if($wechat_shops){
            $wechat_shops = array_values($wechat_shops);
            $data['node_id'] = $wechat_shops[0]['node_id'];
            if($api_get_param) $data = array_merge($data, $api_get_param);
            $resp = $this->$fun_name($data);
            $result = json_decode($resp, true);
            return $result;
        }
        
        $wx_obj = kernel::single('market_service_weixin_api');
        $test_api && $wx_obj->set_test();
        return $wx_obj->push_api($fun_name,$data,$api_get_param);
    }
    
    //消息群发
    public function message_mass_send($params)
    {
        $params['touser'] = json_encode($params['touser']);
        $params['content'] = ($params['msgtype']=='mpnews') ? $params['mpnews']['media_id'] : $params['text']['content'];
        kernel::single('ecorder_rpc_request_weixin_msg')->mass_send($params);
    }
    
}

