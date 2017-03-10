<?php
class market_finder_active_plan {

    public $addon_cols = "auto_run_hour,auto_run_min,plan_send_time";
    
    var $column_edit = '操作';
    var $column_edit_order = COLUMN_IN_HEAD;
    var $column_edit_width = 80;
    function column_edit($row) {
        $act = '<a href="index.php?app=market&ctl=admin_active_plan&act=edit&active_id='.$row['active_id'].'">修改</a>';
        return $act;
    }
    
    var $column_run_time = '计划发送时间';
    var $column_run_time_order = 30;
    var $column_run_time_width = 120;
    function column_run_time($row) {
        $plan_send_time = $row[$this->col_prefix.'plan_send_time'];
        $auto_run_hour = $row[$this->col_prefix.'auto_run_hour'];
        $auto_run_min = $row[$this->col_prefix.'auto_run_min'];
        
        $plan_send_time = date('Y-m-d', $plan_send_time);
        $act = "$plan_send_time $auto_run_hour:$auto_run_min";
        return $act;
    }

}
