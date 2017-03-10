<?php

class taocrm_ctl_admin_member_card_type extends desktop_controller{

	var $workground = 'taocrm.member';

	public function __construct($app)
	{
		parent::__construct($app);
			
	}

	public function show_type(){
		$memberCardTypeObj = &$this->app->model('member_card_type');
		$typeList = $memberCardTypeObj->getList('*',array(),0,-1,'update_time desc');
		$this->pagedata['jsonTypeList'] = json_encode($typeList);
		$this->display('admin/member/card/type.html');
	}

	public function save_type(){
		$result = array('rsp'=>'succ');
		$memberCardTypeObj = &$this->app->model('member_card_type');
		$msg = '';
		if( ($id = $memberCardTypeObj->saveType($_POST,$msg)) ){
			$result['id'] = $id;
		}else{
			$result['msg'] = $msg;
			$result['rsp'] = 'fail';
		}
		echo json_encode($result);
		exit;
	}

	public function del_type(){
		$result = array('rsp'=>'succ');

		$memberCardTypeObj = &$this->app->model('member_card_type');
		$msg = '';
		if($memberCardTypeObj->delType($_POST['type_id'],$msg)){

		}else{
			$result['msg'] = $msg;
			$result['rsp'] = 'fail';
		}

		echo json_encode($result);
		exit;
	}

	public function get_type_list(){
		$result = array('rsp'=>'succ');

		$memberCardTypeObj = &$this->app->model('member_card_type');
		$typeList = $memberCardTypeObj->getList('id,type_name,type_code',array(),0,-1,'update_time desc');
		$result['type_list'] = $typeList;
		echo json_encode($result);
		exit;
	}

}
