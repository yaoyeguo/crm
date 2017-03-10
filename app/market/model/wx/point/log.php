<?php
class market_mdl_wx_point_log extends dbeav_model {

    var $defaultOrder = array('create_time','DESC');

    function modifier_FromUserName($row)
    {
        if ($row){
            $db = kernel::database();
            $member = $db->selectRow('select wx_nick from sdb_market_wx_member where FromUserName="'.$row.'"');

            return $member['wx_nick'] ? urldecode($member['wx_nick']) : '-';
        }else{
            return '-';
        }
    }

}