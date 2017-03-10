<?php
class taocrm_finder_member_export{
    var $detail_basic = "详细";

    function detail_basic($id){
        $obj = &app::get('taocrm')->model('member_export_log');
        $logs = $obj->getList('*',array('export_id'=>$id),0,-1,'create_time desc');

        $render = app::get('taocrm')->render();
        $render->pagedata['logs'] = $logs;
        return $render->fetch('admin/member/export/log.html');
    }

    var $addon_cols = "export_id,export_status";

    var $column_edit = "操作";
    var $column_edit_width = 150;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $export_id = $row[$this->col_prefix.'export_id'];
        $export_status = $row[$this->col_prefix.'export_status'];
        // var_dump($export_status);exit;

        $button1 = '';

        if($export_status == 'succ'){
            $button1 .= '<a href="index.php?app=taocrm&ctl=admin_member&act=download&p[0]='.$export_id.'&finder_id='.$finder_id.'" target="dialog::{width:500,height:200,title:\'下载客户导出CSV\'}">下载</a>';
        }

        return $button1;
    }

     
}
?>