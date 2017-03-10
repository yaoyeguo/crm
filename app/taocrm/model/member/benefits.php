<?php
class taocrm_mdl_member_benefits extends dbeav_model {

    function getNumsByCode($member_id,$code){
        $row = $this->db->selectRow('select sum(nums) as total_nums from sdb_taocrm_member_benefits where member_id='.$member_id .' and (effectie_time <='.time().' and failure_time = 0 ) or ( effectie_time <='.time().' and failure_time >= '.time().' )');

        return intval($row['total_nums']);
    }


}