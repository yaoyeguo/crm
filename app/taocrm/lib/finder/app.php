<?php 

class taocrm_finder_app {
    
	var $column_edit = '操作';
	var $column_edit_order = COLUMN_IN_TAIL;
	function column_edit($row) {
		$finder_id = $_GET['_finder']['finder_id'];
		$href = '<a href="index.php?app=taocrm&ctl=admin_app&act=edit_app&p[0]='.$row['id'].'&finder_id=' . $finder_id . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑绑定应用').'\', width:600, height:250}">编辑</a>'.$row[$this->col_prefix.'is_fixed'];
		if($row['status']){
			$status = '解除绑定';
			$href .= ' | <a href="index.php?app=taocrm&ctl=admin_app&act=unbind&p[0]='.$row['id'].'&p[1]='.$row['status'].'&finder_id=' . $finder_id . '" target="dialog::{title:\''.app::get('taocrm')->_('解除绑定').'\', width:400, height:100}">'.$status.'</a>'.$row[$this->col_prefix.'is_fixed'];
		}else{
			$status = '申请绑定';
			$href .= ' | <a href="index.php?app=taocrm&ctl=admin_app&act=bind&p[0]='.$row['id'].'&finder_id=' . $finder_id . '" target="dialog::{title:\''.app::get('taocrm')->_('绑定旺旺精灵').'\', width:450, height:200}">'.$status.'</a>'.$row[$this->col_prefix.'is_fixed'];
		}
		
		return $href;
	}	
	
	
	function detail_basic($id){
    	$render = app::get('taocrm')->render();
    	$appObj = app::get('taocrm')->model('app');
    	$arr = $appObj->dump(array('id'=>$id),'*');
    	$shopObj = app::get('ecorder')->model('shop');
    	$shop = $shopObj -> dump(array('shop_id'=>$arr['shop_id']),'name,config');
    	$shop_site = unserialize($shop['config']);
    	$arr['shop_name'] = $shop['name'];
    	$arr['website'] = $shop_site['url'];
    	$render->pagedata['data'] = $arr;
        return $render->fetch('admin/app/edit.html');
    }
}
