<?php
class market_finder_wx_keywords_autoreply {
     
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $id = $row[$this->col_prefix.'id'];

        $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=addAutoReply&p[0]='.$id.'&finder_id='.$finder_id.'" target="dialog::{onClose:function(){window.location.reload();},width:680,height:270,title:\'修改关键字回复\'}">编辑</a>';

        return $button1;
    }
}