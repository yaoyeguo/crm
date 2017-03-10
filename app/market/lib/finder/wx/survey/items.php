<?php
class market_finder_wx_survey_items {
     
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $id = $row[$this->col_prefix.'item_id'];

        $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=survey_items_edit&item_id='.$id.'&finder_id='.$finder_id.'" target="dialog::{width:650,height:355,title:\'编辑问题\'}">编辑</a>';

        return $button1;
    }
}