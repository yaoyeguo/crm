<?php 
class market_mdl_sms_log extends dbeav_model {
	function get_batch_no(){
		$svRow=$this->db->select('select batch_no from sdb_market_sms_log order by log_id desc LIMIT 1');
		return $svRow;
	}
	
	
	
}


