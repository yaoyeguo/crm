<?php 

class market_mdl_sms_templates extends dbeav_model {
	public function modifier_status($row){
        if($row){
        	return '已启用';
        }else{
        	return '已禁用';
        }
    }
}
 