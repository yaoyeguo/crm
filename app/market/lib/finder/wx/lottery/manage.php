<?php
class market_finder_wx_lottery_manage
{

    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $id = $row['lottery_id'];
        $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=lottery_manage_edit&item_id='.$id.'&type=edit" target="dialog::{width:822,height:450,title:\'编辑\'}">编辑</a>';
        $button2  = '<a href="index.php?app=market&ctl=admin_weixin&act=lottery_manage_close_select&item_id='.$id.'" target="dialog::{width:400,height:200,title:\'关闭\'}">关闭</a>';
        $button3  = '<a href="index.php?app=market&ctl=admin_weixin&act=lottery_manage_edit&item_id='.$id.'&type=view" target="dialog::{width:822,height:450,title:\'查看\'}">查看</a>';

        if($row['lottery_status'] == 'create')
            return $button1.'&nbsp;|&nbsp;'.$button2;
        elseif($row['lottery_status'] == 'close')
            return $button3;
        else
            return $button3.'&nbsp;|&nbsp;'.$button2;
    }

    var $column_pnum = "查看";
    var $column_pnum_width = 80;
    var $column_pnum_order = COLUMN_IN_TAIL;
    function column_pnum($row)
    {
        $button  = '<a href="index.php?app=market&ctl=admin_weixin&act=lottery_manage_viewp&item_id='.$row['lottery_id'].'" target="dialog::{width:822,height:450,title:\'查看\'}">查看</a>';

        return $row['participants'].'&nbsp;|&nbsp;'.$button;
    }

    var $column_href = "活动链接和二维码";
    var $column_href_width = 110;
    var $column_href_order = 55;
    function column_href($row)
    {
        $button  = '<a href="index.php?app=market&ctl=admin_weixin&act=lottery_manage_href&item_id='.$row['lottery_id'].'" target="dialog::{width:822,height:500,title:\'链接和二维码\'}">链接和二维码</a>';

        return $button;
    }

    public function row_style($row){
        if($row['lottery_status']=='close'){
            return 'list-close';
        }else{
            return '';
        }
    }
}