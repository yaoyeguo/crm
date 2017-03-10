<?php
class ecorder_finder_gift_rule{

    var $addon_cols = "start_time,end_time,status";
    
    var $column_edit = "操作";
    var $column_edit_width = 100;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $finder_id = $_GET['_finder']['finder_id'];
        $id = $row['id'];
     	$shop_id = $row['shop_id'];
     	$lv_id = $row['lv_id'];
     	$gift_bn = $row['gift_bn'];
        $button = '';
        //$button .= '<a href="index.php?app=ecorder&ctl=admin_gift_rule&act=edit_rule&p[0]='.$id.'&p[1]='.$_GET['view'].'&finder_id='.$finder_id . '" target="dialog::{title:\''.app::get('ecorder')->_('编辑促销规则').'\', width:700, height:380}">编辑</a>';
        $button .= '<a href="index.php?app=ecorder&ctl=admin_gift_rule&act=index&p[0]=add&id='.$id.'&finder_id='.$finder_id . '">编辑</a>';
        
        $button .= ' | <a href="index.php?app=ecorder&ctl=admin_gift_rule&act=priority&p[0]='.$id.'&p[1]='.$_GET['view'].'&finder_id='.$finder_id . '" target="dialog::{title:\''.app::get('ecorder')->_('设置优先级').'\', width:550, height:250}">优先级</a>';
        
        return $button;
    }
    
    var $column_validtime = "有效期";
    var $column_validtime_width = 180;
    var $column_validtime_order = 80;
    function column_validtime($row)
    {
        $start_time = $row[$this->col_prefix.'start_time'];
        $end_time = $row[$this->col_prefix.'end_time'];
        
        $button = date('Y-m-d', $start_time).' ~ '.date('Y-m-d', $end_time);        
        return $button;
    }
    
    var $column_status = "状态";
    var $column_status_width = 80;
    var $column_status_order = 90;
    function column_status($row)
    {
        $start_time = $row[$this->col_prefix.'start_time'];
        $end_time = $row[$this->col_prefix.'end_time'];
        $status = $row[$this->col_prefix.'status'];

        $button = '';
        
        if($status=='0') $button .= ' <font color="#999">已关闭</font>';        
        elseif($start_time > time()) $button .= ' <font color="#999">未开始</font>';        
        elseif($end_time < time()) $button .= ' <font color="#999">已过期</font>';
        else $button .= ' <font color=green>活动中</font>';
        
        return $button;
    }
    
    public function row_style($row)
    {
        $start_time = $row[$this->col_prefix.'start_time'];
        $end_time = $row[$this->col_prefix.'end_time'];
        $status = $row[$this->col_prefix.'status'];
        
        if($status=='0' or $start_time > time() or $end_time < time()){
            return 'list-close';
        }else{
            return '';
        }
    }

}
