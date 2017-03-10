<?php
class market_mdl_fx_activity extends dbeav_model {

    public function modifier_start_time($row){
        $date = date("Y-m-d",$row);
        return $date ;
    }

    public function modifier_end_time($row){
        $date = date("Y-m-d",$row);
        return $date ;
    }

    //转换活动类型字段
    function modifier_type($row)
    {
        $arr = array();
        $row = unserialize($row);
        if($row){
            if(in_array('sms', $row)) $arr[] = '短信';
            if(in_array('edm', $row)) $arr[] = '邮件';
            if(in_array('coupon', $row)) $arr[] = '优惠券';
        }
        return implode(',', $arr);
    }

    function getSmsTaskList($filter,$page=1,$page_size=200){
        $db = kernel::database();
        $sql = 'SELECT a.member_id,a.ship_mobile as mobile,ship_name FROM sdb_ecorder_fx_orders AS a';

        $arrWhere = $this->getBuildFilter($filter);

        $page -= 1;

        if(!empty($arrWhere)){
            $sql .= ' WHERE '.implode(' AND ', $arrWhere);
        }
        $sql .= ' GROUP BY a.member_id LIMIT '.($page*$page_size) .','.$page_size;

        //echo '<pre>';var_dump($sql);exit;
        $smsList = $db->select($sql);

        return $smsList;
    }

    function getBuildFilter($filter){
        if(empty($filter))return array();

        $sign = array('than' => '>','lthan' => '<','nequal' => '=','sthan' => '<=','bthan' => '>=');
        $filterArr = array('shop_id','fx_uname','regions_id','createtime');
        $arrWhere = array();
        $class = array();
        foreach ($filter as $key => $value) {
            if(in_array($key, $filterArr)){
                if ( ( is_array($value) && $value['min_val'] ) || ( is_string($value) && $value ) || (is_array($value) && $value[0] != '')) {
                    $class[$key] = $value;
                }
            }
        }

        foreach($class as $k=>$v) {
            switch($k){
                case 'shop_id':
                    $arrWhere[] = ' a.shop_id="'.$filter['shop_id'].'"';
                    break;
                case 'fx_uname':
                    $arrWhere[] = ' a.agent_uname="'.$filter['fx_uname'].'"';
                    break;
                case 'regions_id':
                    $arrWhere[] = " a.state_id in (".implode(',',$filter['regions_id']).") ";
                    break;
                default://购买行为
                    if($v['sign']=='between') {
                        if(!$v['min_val'] || !$v['max_val']) break;
                        if($k=='createtime'){
                            if(!is_numeric($v['min_val'])) $v['min_val'] = strtotime($v['min_val']);
                            if(!is_numeric($v['max_val'])) $v['max_val'] = strtotime($v['max_val']);
                        }
                        $arrWhere[] = '  (a.'.$k.' BETWEEN '.$v['min_val'].' AND '.$v['max_val'].' )';
                    }elseif($v['sign']){
                        if(!$v['min_val']) break;
                        if($k=='createtime'){
                            if(!is_numeric($v['min_val'])) $v['min_val'] = strtotime($v['min_val']);
                        }
                        $arrWhere[] = '  (a.'.$k.' '.$sign[$v['sign']].' '.$v['min_val'].' )';
                    }else{
                        break;
                    }
                    break;
                     
            }
        }
         
        return $arrWhere;
    }

    function getSmsTaskInfo($filter){
        $db = kernel::database();
        $sql = 'SELECT count(*) as total FROM (SELECT a.member_id FROM sdb_ecorder_fx_orders AS a';

        $arrWhere = $this->getBuildFilter($filter);
        if(!empty($arrWhere)){
            $totalSql = $sql.' WHERE '.implode(' AND ', $arrWhere) .' GROUP BY a.member_id) as b';
        }else{
            $totalSql = $sql.' GROUP BY a.member_id) as b';
        }
        //echo $totalSql;exit;
        $row = $db->selectRow($totalSql);
        $count = intval($row['total']);

        $arrWhere[] = ' a.member_id !=0';
        $validSql = $sql.' WHERE '.implode(' AND ', $arrWhere) .' GROUP BY a.member_id) as b';
        $row = $db->selectRow($validSql);
        $valiNum = intval($row['total']);

        $unSend = $count - $valiNum;
        $waitSendMember = $valiNum;

        $activityCount = array(
                    'totalMembers' => $count,
        //'sentMembers' => $reSend,
                    'unvalidMembers' => $unSend,
                    'validMembers' => $valiNum,
                    'WaitSendMember' => $waitSendMember,
        );
        //var_dump($activityCount);exit;
        return $activityCount;
    }


}