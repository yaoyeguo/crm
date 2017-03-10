<?php 
class market_finder_edm_tclass {
    
    var $addon_cols = 'is_fixed';
    
	var  $column_edit = '操作';
	var $column_edit_order = 2;
    function column_edit($row) {
        if($row[$this->col_prefix.'is_fixed'] == 1){
            return false;
        }
		return '<a href="index.php?app=market&ctl=admin_edm_tclass&act=themeEdit&p[0]='.$row['type_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑模板分类').'\', width:680, height:250}">编辑</a>';
	}	
}


