<?php
class market_finder_wx_member {
    var $pagelimit = 20;
    var $column_editbutton = '操作';
    var $column_editbutton_order = COLUMN_IN_HEAD;
    public function column_editbutton($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $wx_member_id = $row[$this->col_prefix.'wx_member_id'];
        $str = '<a href="index.php?app=market&ctl=admin_weixin&act=opPoint&p[0]='.$wx_member_id.'&finder_id='.$finder_id.'" target="dialog::{width:500,height:200,title:\'积分操作\'}">积分操作</a>';

        return $str;
    }

    var $detail_points = '积分日志';
    function detail_points($id){

        $objPointLog = &app::get('market')->model('wx_point_log');
        $render = app::get('market')->render();
        $render->pagedata['logs'] = $objPointLog->getList('*',array('wx_member_id'=>$id));
        return $render->fetch('admin/weixin/point/log.html');
    }

    var $detail_chats = '聊天记录';
    function detail_chats($id){
        $db = kernel::database();
        $row = $db->selectrow('select FromUserName from sdb_market_wx_member where wx_member_id='.$id);
        $FromUserName = $row['FromUserName'];
        $objWxChat = &app::get('market')->model('wx_chat');
        $render = app::get('market')->render();

        //当前页
        $page = $_GET['page'];
        $page = $page ? $page : 1;
        $pageLimit = 20;
        $count = $objWxChat->count(array('FromUserName'=>$FromUserName));
        $render->pagedata['chats'] = $objWxChat->getList('*',array('FromUserName'=>$FromUserName), $pageLimit*($page-1),$pageLimit, 'created DESC');

        $token = md5("page{$page}");
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>ceil($count/$pageLimit),
                'link'=>'index.php?app=market&ctl=admin_weixin&act=openId&page='.$page.'&token='.$token,
                'token'=>$token
        );
        return $render->fetch('admin/weixin/chat.html');
    }
}