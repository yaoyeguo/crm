<?php 

class market_finder_sms_templates {
    
    var $addon_cols = 'title,cloud_id';
    
	var $column_edit = '操作';
	var $column_edit_order = 2;
	function column_edit($row) {
		$href = '<a href="index.php?app=market&ctl=admin_sms_templates&act=edit_theme&p[0]='.$row['template_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑模板分类').'\', width:700, height:500}">编辑</a>'.$row[$this->col_prefix.'is_fixed'];
		if($row['status']){
			$status = '禁用';
		}else{
			$status = '启用';
		}
		$href .= ' | <a href="index.php?app=market&ctl=admin_sms_templates&act=edit_status&p[0]='.$row['template_id'].'&p[1]='.$row['status'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '">'.$status.'</a>'.$row[$this->col_prefix.'is_fixed'];
		return $href;
	}	
	
    var $column_title = '模板名称';
    var $column_title_order = 10;
    var $column_title_width = 180;
    function column_title($row) {
        $cloud_id = $row[$this->col_prefix.'cloud_id'];
        $href = '';
        if($cloud_id > 0){
            $href = '<img title="云模板" src="'.app::get('market')->res_url.'/cloud.gif" /> ';
        }
        $href .= $row[$this->col_prefix.'title'];
        return $href;
    }

}
