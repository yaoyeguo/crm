<?php
class market_mdl_wx_unbind_member_log extends dbeav_model {

    public function get_log_data($data){
        $sql = 'select * from sdb_market_wx_unbind_member_log where FromUserName ="'.$data['FromUserName'].'" and create_time >= "'.$data['cur_begin'].'" and create_time <= "'.$data['cur_end'].'"';
       //echo $sql;
        return $this->db->select($sql);
    }
}