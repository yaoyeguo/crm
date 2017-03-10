<?php
class market_finder_wx_sendmsg_all {
     
    var $addon_cols = "id,del_flag,media_id";

    var $column_edit = "操作";
    var $column_edit_width = 130;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $id = $row[$this->col_prefix.'id'];
        $media_id = $row[$this->col_prefix.'media_id'];
        $url_prefix = 'index.php?app=market&ctl=admin_weixin';
        $button_edit  = '<a href="'.$url_prefix.'&act=add_send_all_msg&p[0]='.$id.'&finder_id='.$finder_id.'" target="dialog::{width:680,height:270,title:\'修改群发内容\'}">编辑</a>';
        $button_upload = '<a href="'.$url_prefix.'_media&act=up_send_all_msg&p[0]='.$id.'&finder_id='.$finder_id.'" target="dialog::{onClose:function(){finderGroup[\''.$finder_id.'\'].refresh();},width:500,height:200,title:\'上传素材\'}">上传素材</a>';
        $button_invalid = '<a href="'.$url_prefix.'&act=delete_send_msg&p[0]='.$id.'&finder_id='.$finder_id.'" target="dialog::{width:500,height:200,title:\'作废\'}">作废</a>';
        $button_send = '<a href="'.$url_prefix.'_msg&act=send_msg&p[0]='.$id.'&finder_id='.$finder_id.'" target="dialog::{width:600,height:250,title:\'群发信息\'}">群发信息</a>';

        if(empty($media_id)){//如果没有上传
            if($row['send_type'] == 'news')
                $button = $button_edit.' | '.$button_upload;    
            else
                $button = $button_edit.' | '.$button_send;
        }else{
            $button = $button_send;    
        }
        $del_flag = $row[$this->col_prefix.'del_flag'];
        if($del_flag == 1){
            return '已作废';
        }
        return $button.' | '.$button_invalid;
    }

    var $detail_log = '发送日志';
    function detail_goods($id)
    {
        $mdl_msg = app::get('market')->model('wx_msg_send_all');
        $rs_msg = $mdl_msg->dump($id, 'send_type,msg_content,media_id');
        $rs_msg['content'] = json_decode($rs_msg['msg_content'], true);
        $rs_msg['content'] = isset($rs_msg['content'][0]['title']) ? $rs_msg['content'][0]['title'] : mb_substr($rs_msg['content'],0,50,'utf-8');
    
        $msg_log_mod = app::get('market')->model('wx_msg_send_all_log');
        $log_list = $msg_log_mod->getList('*', array('send_msg_id' => $id), 0, 10, 'id DESC');
        $log_arr = array();
        foreach($log_list as $log){
            $log_members = explode(',',$log['send_list']);
            if(count($log_members)>3){
                $log_arr[] = array('member' => '<strong style="color:#090">群发给 '.count($log_members).' 人</strong>') + $log;
            }else{
            foreach($log_members as $member){
                $log_arr[] = array('member' => $member) + $log;
                }
            }
        }
        
        $app = app::get('market');
        $render = $app->render();
        $render->pagedata['log'] = $log_arr;
        $render->pagedata['rs_msg'] = $rs_msg;
        return $render->fetch('admin/weixin/send_msg_log.html');
    }
}
