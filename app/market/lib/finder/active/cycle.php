<?php
class market_finder_active_cycle {

    public $addon_cols = "auto_run_hour,auto_run_min,cycle_type,auto_cycle_type,auto_cycle_days,fixed_cycle_days";
    
    var $column_edit = '操作';
    var $column_edit_order = COLUMN_IN_HEAD;
    var $column_edit_width = 80;
    function column_edit($row) {
        $act = '<a target="dialog::{width:800,height:400,title:\'周期营销活动\'}" href="index.php?app=market&ctl=admin_active_cycle&act=edit&active_id='.$row['active_id'].'">修改</a>';
        
        $act .= '　<a target="dialog::{width:400,height:150,title:\'确认操作\'}" href="index.php?app=market&ctl=admin_active_cycle&act=edit_status&active_id='.$row['active_id'].'">'.($row['status']=='1' ? '关闭' : '<font color=red>开启</font>').'</a>';
        
        return $act;
    }
    
    var $column_run_time = '执行时间';
    var $column_run_time_order = 100;
    var $column_run_time_width = 100;
    function column_run_time($row) {
        $auto_run_hour = $row[$this->col_prefix.'auto_run_hour'];
        $auto_run_min = $row[$this->col_prefix.'auto_run_min'];
        $act = "$auto_run_hour:$auto_run_min";
        return $act;
    }
    
    var $column_cycle_type = '周期设置';
    var $column_cycle_type_order = 20;
    var $column_cycle_type_width = 200;
    function column_cycle_type($row) {
        $cycle_type = $row[$this->col_prefix.'cycle_type'];
        $auto_cycle_type = $row[$this->col_prefix.'auto_cycle_type'];
        $auto_cycle_days = $row[$this->col_prefix.'auto_cycle_days'];
        $fixed_cycle_days = $row[$this->col_prefix.'fixed_cycle_days'];
        $columns = app::get('market')->model('active_cycle')->_columns();
        //echo('<pre>');var_dump($columns);
        
        if($cycle_type=='auto'){
            $str = $columns['auto_cycle_type']['type'][$auto_cycle_type].'后 '.$auto_cycle_days.' 天';
        }else{
            $str = '已完成订单，每 '.$fixed_cycle_days.' 天一次';
        }
        return $str;
    }
}
