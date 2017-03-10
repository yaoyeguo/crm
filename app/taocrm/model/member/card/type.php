<?php
class taocrm_mdl_member_card_type extends dbeav_model{


	function saveType($data,& $msg){

		$row = $this->dump(array('type_code'=>$data['type_code']));
		if($row){
			$msg = '类型编码已经存在';
			return false;
		}
		$typeData = array('type_name'=>$data['type_name'],'type_code'=>$data['type_code'],'update_time'=>time());

		if(!empty($data['id'])){
			$typeData['id'] = $data['id'];
		}else{

			$typeData['create_time'] = time();
		}

		parent::save($typeData);
			
		if($typeData['id']){

			return $typeData['id'];
		}else{

			$msg = '保存失败';
			return false;
		}
	}

	function delType($id,& $msg){

		$memberCardTemplateObj = &$this->app->model('member_card_template');
		//$row = $this->db->selectRow('select id from sdb_taocrm_member_card_template where member_card_type_id='.$id);
		$row = $memberCardTemplateObj->dump(array('member_card_type_id'=>$id),'id');
		if($row){
		  $msg = '会员卡类型已经绑定,请先解除绑定,再来删除!';
		  return false;
		}

		$this->db->exec('delete from sdb_taocrm_member_card_type where id='.$id);

		return true;
	}

}
