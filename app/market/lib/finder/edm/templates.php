<?php 

class market_finder_edm_templates {
    
	var $column_edit = '操作';
	var $column_edit_order = 2;
	function column_edit($row) {
		$href = '<a href="index.php?app=market&ctl=admin_edm_templates&act=edit_theme&p[0]='.$row['theme_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑模板分类').'\', width:700, height:500}">编辑</a>'.$row[$this->col_prefix.'is_fixed'];
		if($row['status']){
			$status = '禁用';
		}else{
			$status = '启用';
		}
		$href .= ' | <a href="index.php?app=market&ctl=admin_edm_templates&act=edit_status&p[0]='.$row['theme_id'].'&p[1]='.$row['status'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '">'.$status.'</a>'.$row[$this->col_prefix.'is_fixed'];
		return $href;
	}	
}
