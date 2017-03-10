<?php
class market_finder_wx_points_buy
{
    var $addon_cols = "join_num";

    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $id = $row['buy_id'];
        $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=buy_manage_edit&item_id='.$id.'&type=edit" target="dialog::{width:822,height:450,title:\'编辑\'}">编辑</a>';
        $button2  = '<a href="index.php?app=market&ctl=admin_weixin&act=buy_manage_close_select&item_id='.$id.'" target="dialog::{width:400,height:200,title:\'关闭\'}">关闭</a>';
        $button3  = '<a href="index.php?app=market&ctl=admin_weixin&act=buy_manage_edit&item_id='.$id.'&type=view" target="dialog::{width:822,height:450,title:\'查看\'}">查看</a>';

        return $row['buy_status'] == 'close' ? $button3 : $button1.'&nbsp;|&nbsp;'.$button2;
    }
    
    var $column_num = "剩余数量";
    var $column_num_width = 70;
    var $column_num_order = 160;
    function column_num($row)
    {
        $total_num = $row['goods_all_stock'];
        $join_num = $row[$this->col_prefix.'join_num'] ? $row[$this->col_prefix.'join_num'] : 0;
        return $total_num - $join_num;
    }

    var $column_pnum = "查看";
    var $column_pnum_width = 80;
    var $column_pnum_order = 200;
    function column_pnum($row)
    {

        $join_num = $row[$this->col_prefix.'join_num'] ? $row[$this->col_prefix.'join_num'] : 0;
        $button  = '<a href="index.php?app=market&ctl=admin_weixin&act=buy_manage_viewp&item_id='.$row['buy_id'].'" target="dialog::{width:822,height:450,title:\'查看\'}">查看</a>';

        return $join_num.'&nbsp;|&nbsp;'.$button;
    }
    public function row_style($row){
        if($row['buy_status']=='close'){
            return 'list-close';
        }else{
            return '';
        }
    }

}