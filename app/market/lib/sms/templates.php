<?php 
class market_finder_sms_templates {
	var  $column_edit = '操作';
	var $column_edit_order = 2;
	 function column_edit($row) {
		return '<a href="index.php?app=market&ctl=admin_sms_templates&act=edit_theme&p[0]='.$row['template_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑模板分类').'\', width:700, height:500}">编辑</a>';
	}	
}




?>


