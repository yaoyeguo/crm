<?php
class market_finder_wx_chat {

    var $column_edit = "";
    var $column_edit_width = 5;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $row['chat_id'];

       // $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=response_chat&id='.$finder_id.'" target="dialog::{width:500,height:300,title:\'回复\'}">回复</a>';
        $button1  = '';

        return $button1;
    }


}