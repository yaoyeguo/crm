<?php
class ecorder_finder_refunds{

    var $addon_cols = 'tid,member_id,shop_id';

    var $column_tid = "订单号";
    var $column_tid_width = 140;
    var $column_tid_order = 10;
    function column_tid($row)
    {
        $tid = $row[$this->col_prefix.'tid'];
        $link = '<a target="dialog::{width:700,height:355,title:\'订单明细\'}" href="index.php?app=taocrm&ctl=admin_member&act=getOrderInfo&order_bn='.$tid.'">'.$tid.'</a>';
        return $link;
    }
    
    var $column_level = "客户等级";
    var $column_level_width = 80;
    var $column_level_order = 32;
    function column_level($row)
    {
        $level_name = $row['level_name'] ? $row['level_name'] : '-';
        return $level_name;
    }
    
    var $column_tag = '标签';
    var $column_tag_width = 60;
    var $column_tag_order = 33;
    function column_tag($row)
    {
        $tagInfo = $row['tagInfo'];
        if($tagInfo){
            $tagInfo = '<img border=0 title="'.$tagInfo.'" align="absmiddle" src="'.app::get('taocrm')->res_url.'/teg_ico.png" >';
        }
        return $tagInfo;
    }

    var $detail_basic = "退款说明";
    function detail_basic($id)
    {
        $mdl_refunds = app::get('ecorder')->model("tb_refunds");
        $rs = $mdl_refunds->dump($id, 'reason,`desc`');
        echo(
            '<ul style="padding:10px 0 0 10px;">
                <li style="color:#AD5700">退款原因：'.$rs['reason'].'</li>
                <li style="color:#017C7C">退款说明：'.$rs['desc'].'</li>
            </ul>'
        );
    }

}
