<?php
class taocrm_mdl_member_card_template extends dbeav_model{

    
	function save($data){
	
		$data['update_time'] = time();
		if(!empty($data['id'])){
		
		}else{
		
			$data['create_time'] = time();
		}
		
		return parent::save($data);
	}
	
}
