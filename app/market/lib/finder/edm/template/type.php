<?php 

class market_finder_edm_template_type {
	var  $column_edit = '操作';
	var $column_edit_order = 2;
	 function column_edit($row) {
		return '<a href="index.php?app=market&ctl=admin_edm_email&act=addtempate_type&p[0]='.$row['type_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑模板分类').'\', width:680, height:250}">编辑</a>';
	}	
}




?>


