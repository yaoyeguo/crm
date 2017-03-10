<?php
class market_mdl_wx_join_log extends dbeav_model {

    function getTodayInfo(){
        $stime = strtotime(date('Y-m-d 00:00:00'));
        $etime = strtotime('+1 day',$stime);
        $row = $this->db->selectrow('select count(*) as total from sdb_market_wx_join_log where created >='.$stime .' and created<='.$etime);
        $todayJoinPeople = intval($row['total']);

        $row = $this->db->selectrow('select count(*) as total from sdb_market_wx_join_log where created >='.$stime .' and created<='.$etime.' and is_survey=1 and is_vote=1 and is_due=1');
        $todayMarketPeople = intval($row['total']);

        $row = $this->db->selectrow('select count(*) as total from sdb_market_wx_survey where start_date <='.time() .' and end_date>='.time());
        $todayValidNums = intval($row['total']);
        
        return array('join_people'=>$todayJoinPeople,'market_people'=>$todayMarketPeople,'valid_nums'=>$todayValidNums);
    }

}