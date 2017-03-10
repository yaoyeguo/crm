<?php

class market_finder_callcenter_callplan_members {

    var $addon_cols = "member_id";

    var $column_edit = '操作';
    var $column_edit_width = 60;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $result = '';
        $callplan_id = $row['callplan_id'];
        $status = $row[$this->col_prefix.'status'];
        $member_id = $row[$this->col_prefix.'member_id'];
        
        $result .= '<a href="index.php?app=market&ctl=admin_callcenter_callin&act=callplan&callplan_id='.$callplan_id.'&finder_id='.$finder_id.'&member_id='.$member_id.'" target="dialog::{width:1000,height:450,title:\'跟进\'}">跟进</a>';
        return $result;
    }
    
}
