<?php
class market_finder_wx_news {
     
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $wx_news_id = $row[$this->col_prefix.'wx_news_id'];

        $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=addNews&p[0]='.$wx_news_id.'&finder_id='.$finder_id.'" target="dialog::{width:800,height:400,title:\'编辑\'}">编辑</a>';

        return $button1;
    }
    
   
}