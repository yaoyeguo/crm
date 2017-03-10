<?php 
class market_mdl_edm_template extends dbeav_model {
	function update_data($data){
	    $sql="update sdb_market_edm_template set type_id='$data[type_id]',theme_title='$data[theme_title]',theme_content='$data[theme_content]' where theme_id='$data[theme_id]'";
		$this->db->exec($sql);
		
	}	
	
	
	
}











?>