<?php 
class taocrm_mdl_app extends dbeav_model {
	public function modifier_status($row){
        if($row){
        	return '已绑定';
        }else{
        	return '未绑定';
        }
    }
    /*
	public function modifier_app_type($row){
        if($row == 'wwgenius'){
        	return '旺旺精灵';
        }
    }*/
}