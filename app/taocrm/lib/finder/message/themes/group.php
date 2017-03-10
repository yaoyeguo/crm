<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */

class taocrm_finder_message_themes_group {
	public $column_edit = '操作';
	public $column_edit_order = 2;
	public function column_edit($row) {
		return '<a href="index.php?app=taocrm&ctl=admin_sms_themes_group&act=themeEdit&p[0]='.$row['group_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑模板分类').'\', width:680, height:250}">编辑</a>';
	}	
}