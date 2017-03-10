<?php
class taocrm_finder_shop_lv{    
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=taocrm&ctl=admin_shop_lv&act=addnew&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['shop_lv_id'].'" target="dialog::{title:\''.app::get('taocrm')->_('编辑客户等级').'\', width:680, height:250}">'.app::get('taocrm')->_('编辑').'</a>';
        
    }  
}