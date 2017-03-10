<?php
class ecorder_finder_shop_channel{
    var $detail_basic = "渠道统计数据";

    function detail_basic($channel_id){
    
        //重新校对渠道统计数据
        kernel::single('taocrm_service_channel')->countChannelBuys($channel_id);
    
        $analysis = app::get('ecorder')->model('shop_channel')->dump($channel_id);
    
        $render = app::get('ecorder')->render();
        $render->pagedata['analysis'] = $analysis;
        return $render->fetch("admin/channel/analysis.html");

    }

    var $addon_cols = "channel_id,is_fixed";
    
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $channel_id = $row[$this->col_prefix.'channel_id'];
        $is_fixed = $row[$this->col_prefix.'is_fixed'];
        if($is_fixed == '1') return false;//固定渠道不允许修改

        $button1 = '<a href="index.php?app=ecorder&ctl=admin_shop_channel&act=addnew&p[0]='.$channel_id.'&finder_id='.$finder_id.'" target="dialog::{width:680,height:250,title:\'编辑渠道\'}">编辑</a>';

        return $button1;
    }
}
?>