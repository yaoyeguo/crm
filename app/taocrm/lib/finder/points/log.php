<?php
class taocrm_finder_points_log {
    var $pagelimit = 20;
    var $addon_cols = 'is_active,log_id';

    var $column_editbutton = '操作';
    var $column_editbutton_width = 60;
    var $column_editbutton_order = COLUMN_IN_HEAD;
    public function column_editbutton($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $log_id = $row[$this->col_prefix.'log_id'];
        if($row[$this->col_prefix.'is_active'] == '0'):
            $str = '已撤销';
        else:
            $str = '<a href="index.php?app=taocrm&ctl=admin_points_log&act=edit&p[0]='.$log_id.'&finder_id='.$finder_id.'" target="dialog::{width:680,height:270,title:\'积分操作\'}">撤销</a>';
        endif;
        return $str;
    }
    
}