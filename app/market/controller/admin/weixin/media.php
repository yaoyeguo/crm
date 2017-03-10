<?php

class market_ctl_admin_weixin_media extends desktop_controller {
    
    public function up_send_all_msg($id = null)
    {
        if(!$_POST){
            $this->pagedata['id'] = $id;
            $this->pagedata['msg'] = '是否确定上传素材？';
            $this->pagedata['app'] = $_GET['app'];
            $this->pagedata['ctl'] = $_GET['ctl'];
            $this->pagedata['act'] = $_GET['act'];
            $this->pagedata['is_ajax'] = true;
            $this->pagedata['ajax_msg'] = '后台正在上传素材，请稍后刷新列表查看状态';
            $this->display('admin/weixin/check_form.html');
        }else{
            //$this->begin('index.php?app=market&ctl=admin_weixin&act=msg_send_all');
            $msg_mod = app::get('market')->model('wx_msg_send_all');
            $arr = $_POST;

            $arr['upload_time'] = time();
            $arr['upload_man'] = kernel::single('desktop_user')->get_name();

            $msg_mod = app::get('market')->model('wx_msg_send_all');
            $msg_info = $msg_mod->dump($arr['id']);
            if($msg_info['send_type'] == 'msg'){
                echo('文字素材类型不需要上传');
                exit;
            }
            
            $media_rs = $this->upload_wx_data($msg_info);
            if($media_rs['st'] == false && isset($media_rs['st'])){
                echo('上传失败,请检查接口或稍后再试;err:'.$media_rs['msg']);
                exit;
            }
            
            $arr['media_id'] = $media_rs['media_id'];
            $arr['created_at_wx'] = $media_rs['created_at'];

            $arr['past_time_at_wx'] = $arr['created_at_wx']+(60*60*24*3);
            $arr['update_time'] = time();

            if($msg_mod->save($arr)){
                echo('保存成功');
            }else{
                echo('保存失败');
            }
        }
    }

    //上传素材
    public function upload_wx_data($info = null)
    {
        if(!$info){
            exit('msg info empty');
        }
        $info_news = json_decode($info['msg_content'],true);
        $i = 0;
        foreach($info_news as $nk => $news){
            //微信首图360x200，次图200x200
            if($i == 0){
                $w = 360; $h = 200;
            }else{
                $w = 200; $h = 200;
            }
            $i++;
            
            $pic = explode('?',$news['picurl']);
            $file_rs = market_func::getImage($pic[0]);
            $pic_name = $file_rs['filedir'].$file_rs['filename'];
            $pic_s_name = $file_rs['filedir'].'_s_'.$file_rs['filename'];
            market_func::img2thumb($pic_name, $pic_s_name, $w, $h, 1);
            $thumb_data = array(
                'media' => $pic_s_name,
            );
            $api_get_param = array('type'=>'thumb');
            $thumb_media_rs = $this->push_msg_to_wx('media_upload',$thumb_data,$api_get_param);
            //@unlink($pic_name);
            //@unlink($pic_s_name);
            if($thumb_media_rs['errcode'] > 0){
                return array('st' => false,'msg'=>json_encode($thumb_media_rs));
            }
            
            //是否在内容里显示封面
            if($news['show_cover_pic']){
                $news['show_cover_pic'] = intval($news['show_cover_pic']);
            }else{
                $news['show_cover_pic'] = 0;
            }
            
            $api_data_articles[] = array(
                'thumb_media_id' => $thumb_media_rs['thumb_media_id'],
                'title' => $news['title'],
                'digest' => $news['digest'],
                'content' => $news['link_type_article'],
                'show_cover_pic' => $news['show_cover_pic'],
                //'author' => '',
                //'content_source_url' => '',
                //'digest' => '',
            );
        }
        $api_data = array(
            'articles' => $api_data_articles,
        );
        $media_rs = $this->push_msg_to_wx('media_uploadnews', $api_data);
        if($media_rs['errcode'] > 0){
            return array('st' => false,'msg'=>json_encode($media_rs));
        }
        return $media_rs;
    }

    //发送数据调用微信接口函数
    public function push_msg_to_wx($fun_name=null, $data=null, $api_get_param=null, $test_api=false)
    {
        //1.矩阵的微信接口
        $wechat_shops = app::get('ecorder')->model('shop')->get_shops('wechat');
        if($wechat_shops){
            $wechat_shops = array_values($wechat_shops);
            $data['node_id'] = $wechat_shops[0]['node_id'];
            if($api_get_param) $data = array_merge($data, $api_get_param);
            $resp = $this->$fun_name($data);
            return $resp;
        }
            
        //2.直连微信接口
        $wx_obj = kernel::single('market_service_weixin_api');
        $test_api && $wx_obj->set_test();
        return $wx_obj->push_api($fun_name,$data,$api_get_param);
    }
    
    //上传图片
    public function media_upload($params)
    {
        $res = kernel::single('ecorder_rpc_request_weixin_media')->upload($params);
        $result = json_decode($res,true);
        //elog($res);elog($result);
        return $result;
    }
    
    //上传图文素材
    public function media_uploadnews($params)
    {
        $params['articles'] = base64_encode(json_encode($params['articles']));
        $res = kernel::single('ecorder_rpc_request_weixin_media')->uploadnews($params);
        $result = json_decode($res,true);
        //elog($res);elog($result);
        return $result;
    }

}
