<?php 
class ecgoods_finder_brand{

    var $column_edit = "操作";
    var $column_edit_width = 100;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $return = '<a target="dialog::{width:400,height:170,title:\'编辑品牌\'}" href="index.php?app=ecgoods&ctl=admin_brand&act=edit_brand&brand_id='.$row['brand_id'].'">修改</a>';

        $return .= '　<a target="dialog::{width:780,height:410,title:\'设置商品\'}" href="index.php?app=ecgoods&ctl=admin_brand&act=set_goods&brand_id='.$row['brand_id'].'">设置商品</a>';

        return $return;
    }
    
}