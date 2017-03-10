<?php
class market_finder_wx_store_subbranch {

    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $id = $row['store_id'];
        $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=store_manage_edit&item_id='.$id.'" target="dialog::{width:822,height:500,title:\'编辑\'}">编辑</a>';

        return $button1;
    }

    function detail_view($id){
        $objPointLog = &app::get('market')->model('wx_store_subbranch');
        $render = app::get('market')->render();
        $render->pagedata['info'] = $objPointLog->dump($id);
        return $render->fetch('admin/weixin/store/view.html');
    }
}