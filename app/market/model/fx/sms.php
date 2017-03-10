<?php
class market_mdl_fx_sms extends dbeav_model {

    function addSms($data){
         
        $this->db->insert("sdb_market_fx_sms",$data);

        return $this->db->lastinsertid();
    }

    function getSms($sms_id){
        $row = $this->db->selectRow('select * from sdb_market_fx_sms where sms_id='.$sms_id);

        return $row;
    }

    function getSmsLogList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $sql = 'select '.$cols.' from sdb_market_fx_sms_log where sms_id='.$filter['sms_id'];

        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);
        $rows = $this->db->selectLimit($sql,$limit,$offset);

        return $rows;
    }

    function sendRunning($sms_id){
        $this->db->exec('update sdb_market_fx_sms set send_status="sending" where sms_id='.$sms_id);
    }
}