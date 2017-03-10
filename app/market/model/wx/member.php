<?php
class market_mdl_wx_member extends dbeav_model {
    
    var $defaultOrder = array('create_time','DESC');

    function _filter($filter,$tableAlias=null,$baseWhere=null)
    {
        if(isset($filter['wx_nick'])){
            $filter['wx_nick'] = urlencode($filter['wx_nick']);
        }
        return parent::_filter($filter,$tableAlias,$baseWhere);
    }

    function getWxInfo()
    {
        $sTime = strtotime(date('Y-m-d 00:00:00'));
        $eTime = strtotime('+1 day '.date('Y-m-d 00:00:00'));
        $info = array();

        $row = $this->db->selectrow('select count(*) as total from sdb_market_wx_event where create_time>='.$sTime .' and create_time<='.$eTime .' and event_type="subscribe"');
        $info['today_subscribe'] = $row['total'];

        $row = $this->db->selectrow('select count(*) as total from sdb_market_wx_event where event_type="subscribe"');
        $info['total_subscribe'] = $row['total'];

        $row = $this->db->selectrow('select count(*) as total from sdb_market_wx_member where tb_nick!=""');
        $info['total_bind'] = $row['total'];

        return $info;

    }

    function updatePoint($wx_member_id,$type=-1,$point,$point_desc='',& $msg)
    {
        $point = intval($point);
        $db = kernel::database();
        $member = $db->selectRow('select * from sdb_market_wx_member where wx_member_id='.$wx_member_id);
        if(!$member){
            $msg = '此客户不存在';
            return false;
        }

        if($type == 1){
            if($point < 0){
                $msg = '全量更新,积分必须大于等于0的整数';
                return false;
            }

            if($member['points'] > $point){
                $point_mode = '-';
            }elseif($member['points'] < $point){
                $point_mode = '+';
            }else{
                $msg = '全量更新,积分必须大于或小于客户积分';
                return false;
            }

            $db->exec('update sdb_market_wx_member set points='.$point .' where wx_member_id='.$wx_member_id);
            $log = array(
                'wx_member_id'=>$wx_member_id,
            'point_mode'=>$point_mode,
            'op_before_point'=>$member['points'],
            'op_after_point'=>$point,
         	'points'=>$point,
            'ToUserName'=>$member['ToUserName'],
            'FromUserName'=>$member['FromUserName'],
            'create_time'=>time(),
                'point_desc'=>$point_desc,
                'op_user'=>kernel::single("desktop_user")->get_name(),
            );
            $this->addPointLog($log);
        }elseif($type == 2){
            if($point < 0 && $member['points'] < abs($point)){
                $msg = '增量更新,积分必须小于客户积分';
                return false;
            }
            if($point < 0){
                $point_mode = '-';
            }elseif($point > 0){
                $point_mode = '+';
            }else{
                $msg = '增量更新,积分不能等于0';
                return false;
            }

            $point = abs($point);
            $op_after_point = ($point_mode == '+') ? ($member['points'] + $point) : ($member['points'] - $point);
            $db->exec('update sdb_market_wx_member set points='.$op_after_point .' where wx_member_id='.$wx_member_id);
            $log = array(
                'wx_member_id'=>$wx_member_id,
             'point_mode'=>$point_mode,
             'op_before_point'=>$member['points'],
             'op_after_point'=>$op_after_point,
         	 'points'=>$point,
             'ToUserName'=>$member['ToUserName'],
             'FromUserName'=>$member['FromUserName'],
             'create_time'=>time(),
                'point_desc'=>$point_desc,
                'op_user'=>kernel::single("desktop_user")->get_name(),
            );
            $this->addPointLog($log);
        }else{
            $msg = '操作类型不在范围';
            return false;
        }

        return $wx_member_id;
    }

    function addPointLog($log)
    {
        $objWxPointLog = app::get('market')->model('wx_point_log');
        $objWxPointLog->save($log);
    }

    function checkRegistPoint($wx_member_id,$data)
    {
        $sTime = strtotime($data.' 00:00:00');
        $eTime = strtotime($data.' 23:59:59');
        $db = kernel::database();
        $row = $db->selectRow('select * from sdb_market_wx_point_log where wx_member_id = '.$wx_member_id.' and create_time >= '.$sTime .' and create_time<=' . $eTime);
        if(!$row){
            return true;
        }else{
            return false;
        }
    }

    function checkRegistPoint_new($FromUserName,$time_info,$member_id=0)
    {
        $sTime = strtotime($time_info.' 00:00:00');
        $eTime = strtotime($time_info.' 23:59:59');
        $db = kernel::database();
        if(empty($FromUserName)){
            $row = $db->selectRow('select * from sdb_market_wx_sign_in_log where member_id = '.$member_id.' and create_time >= '.$sTime .' and create_time<=' . $eTime);
        }else{
            $row = $db->selectRow('select * from sdb_market_wx_sign_in_log where FromUserName = "'.$FromUserName.'" and create_time >= '.$sTime .' and create_time<=' . $eTime);
        }
        if(!$row){
            return true;
        }else{
            return false;
        }
    }

    function toRegist($wx_member_id)
    {
        $db = kernel::database();
        if(!$this->checkRegistPoint($wx_member_id,date('Y-m-d',strtotime('-1 day')))){
            $db->exec('update sdb_market_wx_member set continue_regist_count = continue_regist_count+1 where wx_member_id = '.$wx_member_id);
        }else{
            $db->exec('update sdb_market_wx_member set continue_regist_count = 1 where wx_member_id = '.$wx_member_id);
        }
    }

    function get($wx_member_id)
    {
        $db = kernel::database();
        return $db->selectRow('select * from sdb_market_wx_member where wx_member_id = '.$wx_member_id);

    }

    function getWxUserCount()
    {
        $row = $this->db->selectRow('select count(*) as total from sdb_market_wx_member');

        return intval($row['total']);
    }

    function getNeedUpdateWxUserCount()
    {
        $row = $this->db->selectRow('select count(*) as total from sdb_market_wx_member where wx_nick ="" or wx_nick is null');

        return intval($row['total']);
    }

    function getNeedUpdateWxUserList($page,$nums)
    {
        $offset = ($page -1) * $nums;
        
        return $this->db->select('select * from sdb_market_wx_member where wx_nick ="" or wx_nick is null order by wx_member_id limit '.$offset.','.$nums);
    }
    
   function modifier_wx_nick($row)
   {
        if ($row){
            return urldecode($row);
        }else{
            return '-';
        }
    }

}