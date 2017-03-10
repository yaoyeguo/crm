<?php
class market_finder_active_review {

    public $addon_cols = "active_id";

    //重复营销
    var $column_detail = '任务详情';
    var $column_detail_order = COLUMN_IN_HEAD;
    var $column_detail_width = 100;
    function column_detail($row) {
        $active_id = intval($row[$this->col_prefix . 'active_id']);
        $active_type = $row['active_type'];
        switch($active_type){
            case 'active_ontime':
                $href = 'app=market&ctl=admin_active_sms&act=index';
                $type_name = '营销活动';
                break;
            case 'active_cycle': 
                $href = 'app=market&ctl=admin_active_cycle&act=index';
                $type_name = '周期营销';
                break;
            case 'plugins': 
                $href = 'app=plugins&ctl=admin_manage&act=index';
                $type_name = '营销插件';
                break;
            default: 
                $href = 'app=market&ctl=admin_active_sms&act=index';
                $type_name = '营销活动';
        }
        $str = '<a href="index.php?'.$href.'">跳转到'.$type_name.'</a>';
        return $str;
    }
    
}
