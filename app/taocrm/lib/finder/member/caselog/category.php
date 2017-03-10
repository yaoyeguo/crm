<?php
class taocrm_finder_member_caselog_category{   
 
    var $column_edit = '编辑';
    var $column_edit_order = 'COLUMN_IN_HEAD';
    function column_edit($row)
    {
        return '<a href="index.php?app=taocrm&ctl=admin_member_caselog&act=category_edit&category_id='.$row['category_id'].'" target="dialog::{title:\''.app::get('taocrm')->_('修改').'\', width:500, height:150}">'.app::get('taocrm')->_('修改').'</a>';
    }
    
}