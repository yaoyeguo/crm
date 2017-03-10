<?php
class taocrm_finder_member_group{

    var $column_control = '操作';
    var $column_control_order = COLUMN_IN_HEAD;
    function column_control($row){
        if($row['status'] && $row['status']==1){
            $button = '';
        }else{
            $button = '
                <a style="display:none" target="_blank" href="index.php?app=desktop&act=alertpages&goto='.urlencode("index.php?app=taocrm&ctl=admin_member_group&act=view&p[0]={$row['group_id']}&_finder[finder_id]={$_GET['_finder']['finder_id']}").'">查看</a>
                <a href="index.php?app=taocrm&ctl=admin_member_group&act=edit_group&p[0]='.$row['group_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'" target="dialog::{title:\''.app::get('taocrm')->_('编辑客户分组').'\', width:700, height:355}">'.'编辑'.'</a>
            ';
        }
        return $button;
    }
    
    var $detail_basic = '子分组';
    function detail_basic($group_id){
        $render = app::get('taocrm')->render();
        //var_dump($group_id);
        $render->pagedata['analysis'] = $analysis;
        return $render->fetch("admin/member/group/childs.html");
    }

}
