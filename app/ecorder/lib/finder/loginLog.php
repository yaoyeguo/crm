<?php 

class ecorder_finder_loginLog {
	
	function detail_basic($id){
    	$render = app::get('ecorder')->render();
    	$loginObj = app::get('ecorder')->model('login_log');
    	$data = $loginObj->dump(array('id'=>$id),'addon');
    	$data = json_decode($data['addon'],true);
    	$render->pagedata['data'] = $data;
        return $render->fetch('admin/system/loginLog.html');
    }
}
