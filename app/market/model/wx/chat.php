<?php
class market_mdl_wx_chat extends dbeav_model {


    function modifier_FromUserName($row){
        if ($row){
            $db = kernel::database();
            $member = $db->selectRow('select wx_nick from sdb_market_wx_member where FromUserName="'.$row.'"');

            return $member['wx_nick'] ? $member['wx_nick'] : '-';
        }else{
            return '-';
        }
    }
}