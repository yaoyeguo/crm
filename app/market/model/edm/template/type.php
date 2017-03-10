<?php 
class market_mdl_edm_template_type extends dbeav_model {
   function update_data($data) {
    $sql="update sdb_market_edm_template_type set title='$data[title]',remark='$data[remark]',create_time='$data[create_time]' where type_id='$data[type_id]'";
    $this->db->exec($sql);
   	
   	
   }
	
}



?>