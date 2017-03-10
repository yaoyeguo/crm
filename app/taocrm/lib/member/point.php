<?php
class taocrm_member_point {

    public function run()
    {
        $orderObj = app::get('ecorder')->model('orders');
        $memberObj = app::get('taocrm')->model('members');
        $shopLvObj = app::get('taocrm')->model('shop_lv');

        $pointSet = array();
        $pointSet['method'] = &app::get('taocrm')->getConf('taocrm.level_point.method');
        $pointSet['config'] = &app::get('taocrm')->getConf('taocrm.level_point.config');

        if ($pointSet['method'] == "0") {
            $setNum = $pointSet['config']['advanced']['num'] ? $pointSet['config']['advanced']['num'] : 1;
            $setAmount = $pointSet['config']['advanced']['amount'] ? $pointSet['config']['advanced']['amount'] : 1;
        }
        else {
            $setAmount = $pointSet['config']['normal']['amount'] ? $pointSet['config']['normal']['amount'] : 1;
        }

        $results = $orderObj->getMembersPaidAmount();
        if ($results) {
            foreach ($results as $value) {
                $point = floor($value['paid_amount']);
                if ($pointSet['method'] == "0") {
                    if ($value['order_succ_num'] >= 4) {
                        $i = $value['order_succ_num'] - 1;
                        $F = $pointSet['config']['advanced']['F'][$i] ? $pointSet['config']['advanced']['F'][$i] : $value['order_succ_num'];
                    }
                    else {
                        $i = $value['order_succ_num'] - 1;
                        $F = $pointSet['config']['advanced']['F'][$i] ? $pointSet['config']['advanced']['F'][$i] : $value['order_succ_num'];
                    }
                    $experience = ($F * $setNum) + ($point * $setAmount);
                }
                else {
                    $experience = $point * $setAmount;
                }
                $level = $shopLvObj->getLvExperience($experience, $value['shop_id']);
                $data = array('point' => $point, 'experience' => $experience, 'shop_lv_id' => $level);
                $filter = array('member_id' => $value['member_id']);

                $memberObj->update($data, $filter);
            }
        }
        return true;
    }

    /**
     *
     * 积分更新
     *
     *
     * @param unknown_type $channel
     * @param unknown_type $uname
     * @param unknown_type $point
     * @param unknown_type $type
     * 1.判断会员是否存在；2.查询全局积分明细表；3.更新全局积分明细表；4.更新全局会员表；5.更新店铺会员表；6.全局积分日志中添加日志
     */
    function update($shop_id,$member_id,$type=-1,$point,$point_desc='',& $msg, $invalid_time,$points_type='other')
    {
        $taocrm_middleware_connect = new taocrm_middleware_connect;
        $re = $taocrm_middleware_connect->requestUpdatePoint($shop_id,$member_id,$type,$point,$point_desc,$msg, $invalid_time,$points_type);

        if($re['errcode'] == 0){
            return $re['taocrm.point.update']['member_id'];
        }else{
            return false;
        }
       /* if(empty($invalid_time)){
            $invalid_time = time() + (86400 * 365 * 100);
        }
        $point = intval($point);
        $db = kernel::database();

        $mData = $db->select('select points from sdb_taocrm_members where member_id='.$member_id);
        if(!$mData){
            $msg = '此客户不存在';
            return false;
        }
        if(empty($shop_id)){
            $sql = 'select points,id from sdb_taocrm_member_points where member_id='.$member_id;
            $sql .= ' and (invalid_time >= '.time().' or ISNULL(invalid_time))';
        }else{
            $sql = 'select points,id from sdb_taocrm_member_points where  shop_id="'.$shop_id.'" and member_id='.$member_id;
            $sql .= ' and (invalid_time >= '.time().' or ISNULL(invalid_time))';
        }
        $sql .= ' order by invalid_time';
        $points_data = $db->select($sql);
        if(!empty($points_data)){
            $sum_point = 0;
            foreach($points_data as $vdata){
                $sum_point += $vdata['points'];
            }
        }
        $member['points'] = $sum_point;
        if($type == 1){

            if($point < 0){
                $msg = '全量更新,积分必须大于等于0的整数';
                return false;
            }
            if($member['points'] > $point){
               // $point_mode = '-';
                $point_num = -1;
            }elseif($member['points'] < $point){
               // $point_mode = '+';
                $point_num = +1;
            }else{
                $msg = '全量更新,积分必须大于或小于客户积分';
                return false;
            }

            $db->exec('update sdb_taocrm_member_analysis set points='.$point .' where shop_id="'.$shop_id.'" and member_id='.$member_id);
            $log = array(
                'member_id'=>$member_id,
         'shop_id'=>$shop_id,
        // 'point_type'=>$point_type,
        // 'point_mode'=>$point_mode,
         'op_before_point'=>$member['points'],
         'op_after_point'=>$point,
         'points'=>$point * $point_num,
         'point_desc'=>$point_desc,
            );
            $this->addPointLog($log);
        }elseif($type == 2){
            if($point < 0 && $member['points'] < abs($point)){
                $msg = '增量更新,积分必须小于客户积分';
                return false;
            }

            if($point < 0){
                $point_mode = '-';
                $point_num = -1;

                $global_points = array(
                    'member_id' => $member_id,
                    'points' => $point,
                    'shop_id' => $shop_id,
                    'point_data' => $points_data,
                );
            }elseif($point > 0){
                $point_mode = '+';
                $point_num = +1;

                $global_points = array(
                    'member_id' => $member_id,
                    'points' => $point,
                    'shop_id' => $shop_id,
                    'invalid_time' => $invalid_time,
                    'points_type' => $points_type
                );

            }else{
                $msg = '增量更新,积分不能等于0';
                return false;
            }

            //全渠道积分
            $mdl_member_points = app::get('taocrm')->model('member_points');
            $mdl_member_points->save_points($global_points);

            $point = abs($point);
            $op_after_point = ($point_mode == '+') ? ($member['points'] + $point) : ($member['points'] - $point);

            //更新店铺会员表
            $db->exec('update sdb_taocrm_member_analysis set points='.$op_after_point .' where shop_id="'.$shop_id.'" and member_id='.$member_id);
            //若有负积分，直接清零
            $sql = 'UPDATE sdb_taocrm_member_analysis SET points=0 where points < 0 ';
            $db->exec($sql);

           //全局积分日志表添加日志
            $log = array(
                'member_id'=>$member_id,
         'shop_id'=>$shop_id,
        // 'point_type'=>$point_type,
        // 'point_mode'=>$point_mode,
         'op_before_point'=>$member['points'],
         'op_after_point'=>$op_after_point,
         'points'=>$point * $point_num,
         'point_desc'=>$point_desc,
            );
            $this->addPointLog($log);
        }else{
            $msg = '操作类型不在范围';
            return false;
        }

        return $member_id;*/
    }

    function addPointLog($log){
        $db = kernel::database();
        $log['op_time'] = time();
        $db->insert('sdb_taocrm_all_points_log',$log);
    }

    /*function addFreezePointLog(){
     $db = kernel::database();
     $data = array('member_id'=>'',
     'shop_id'=>'',
     'point_type'=>'',
     'point_mode'=>'',
     'source_point'=>'',
     'op_time'=>'',
     'op_before_point'=>'',
     'op_after_point'=>'',
     'point'=>'',
     'freeze_time'=>'',
     'unfreeze_time'=>'',
     'is_expired'=>'',
     'expired_time'=>'',
     'point_desc'=>'',
     );
     $db->insert('sdb_taocrm_member_point_log',$data);
     }*/

    function get($member_id,& $msg,$shop_id=null,$node_id,$invalid_time=null){
        $taocrm_middleware_connect = new taocrm_middleware_connect;
        $re = $taocrm_middleware_connect->requestGetPoint($member_id,$msg,$shop_id,$node_id, $invalid_time);
        if($re['errcode'] == 0){
            $list = $re['taocrm.point.get']['list'];
            $result = array();
            $result = $list;
            $result['total_point'] = $re['taocrm.point.get']['total_point'];
            return $result;
        }else{
            return false;
        }
        /*$db = kernel::database();
        $member = $db->selectRow('select member_id from sdb_taocrm_members where member_id='.$member_id);
        if(!$member){
            $msg = '此客户不存在';
            return false;
        }
        $sql = 'select shop_id,points from sdb_taocrm_member_points where member_id='.$member_id;
        if(!empty($invalid_time)){
            $sql .= ' and (invalid_time >= '.$invalid_time.' or ISNULL(invalid_time))';
        }
        if(!empty($shop_id)){
            $sql .= ' and shop_id = '.$shop_id;
        }
        $PointsList = $db->select($sql);
        $pointList_group = array();
        foreach($PointsList as $k => $v){
            $pointList_group[$v['shop_id']][] = $v['points'];
        }
        $memberPointList = array();
        $i = 0;
        $total_point = 0;
        foreach($pointList_group as $k1 => $v1){
            $memberPointList[$i]['shop_id'] = $k1;
            //获取node_id的值
            $shop_obj = app::get('ecorder')->model('shop');
            $shop_data = $shop_obj->dump(array('shop_id'=>$k1));
            $node_id = $shop_data['node_id'];
            $memberPointList[$i]['node_id'] = $node_id;

            $memberPointList[$i]['points'] = array_sum($v1);
            $total_point += array_sum($v1);
            $i++;
        }
        $memberPointList['total_point'] = $total_point;
        if($memberPointList){
            return $memberPointList;
        }else{
            $msg = '此客户没有积分记录';
            return false;
        }*/
    }

    function getPointLogList($shop_id,$member_id,$page_size,$page,& $msg,$p_type=null){
        $taocrm_middleware_connect = new taocrm_middleware_connect;
        $re = $taocrm_middleware_connect->requestGetPointLog($shop_id,$member_id,$page_size,$page,$msg,$p_type);
        if($re['errcode'] == 0){
            $list = $re['taocrm.pointlog.getlist']['list'];
            foreach($list as $km=>$vm){
                $list[$km]['op_time'] = date('Y-m-d h:i:s',$vm['op_time']);
            }
            $result = array();
            $result['logs'] = $list;
            $result['totalResult'] = $re['taocrm.pointlog.getlist']['total_result'];
            return $result;
        }else{
            return false;
        }
        /*$page_size = intval(abs($page_size));
        $page = intval(abs($page));
        $db = kernel::database();
        $member = $db->selectRow('select member_id from sdb_taocrm_members where member_id='.$member_id);
        if(!$member){
            $msg = '此客户不存在';
            return false;
        }

        $whereSql = array('member_id='.$member_id);
        if($shop_id){
            $whereSql[] = 'shop_id="'.$shop_id.'"';
        }
        if($p_type != 'all'){
            if($p_type == '+'){
                $whereSql[] = 'points > 0';
            }else{
                $whereSql[] = 'points < 0';
            }
        }
        $totalSql = 'select count(*) as total from sdb_taocrm_all_points_log where '.implode(' and ', $whereSql);
        $total = $db->selectRow($totalSql);
        $totalResult = intval($total['total']);
        
        $sql = 'select * from sdb_taocrm_all_points_log';
        $sql .= ' where '.implode(' and ', $whereSql) . ' order by id desc limit '.(($page-1)*$page_size) .','.$page_size;
        //echo $sql;exit;
        $memberPointLogList = $db->select($sql);
        if($memberPointLogList){
            foreach($memberPointLogList as $k=>$log){
                unset($log['id']);
                unset($log['member_id']);
                $log['op_time'] = date('Y-m-d H:i:s',$log['op_time']);
                $log['freeze_time'] = (!empty($log['freeze_time'])) ? date('Y-m-d H:i:s',$log['freeze_time']) : $log['freeze_time'];
                $log['unfreeze_time'] = (!empty($log['unfreeze_time'])) ? date('Y-m-d H:i:s',$log['unfreeze_time']) : $log['unfreeze_time'];
                $log['expired_time'] = (!empty($log['expired_time'])) ? date('Y-m-d H:i:s',$log['expired_time']) : $log['expired_time'];
                $memberPointLogList[$k]= $log;
            }
        }else{
            $memberPointLogList = array();
        }
        
        return array('logs'=>$memberPointLogList,'totalResult'=>$totalResult);*/
    }
    
    public function init_member_point($shop_id, $member_id)
    {
        $db = kernel::database();
        $has_special_rules = false;
        $all_amount = 0;
        //$points_type = 'trade';
        $oPointsLog = app::get('taocrm')->model('all_points_log');
        
        //检查是否有合并的会员数据
        $mdl_members = app::get('taocrm')->model('members');
        $member_ids[] = $member_id;
        $rs = $mdl_members->getList('member_id', array('parent_member_id'=>$member_id));
        if($rs){
            foreach($rs as $v){
                $member_ids[] = $v['member_id'];
            }
        }
        $member_ids = implode(',', $member_ids);
    
        //删除积分日志
        $sql = "delete from sdb_taocrm_all_points_log where member_id in ($member_ids) and shop_id='$shop_id' and (order_id>0 or refund_id>0) ";
        $db->exec($sql);
        
        //获取积分规则
        $rs_point_rules = app::get('ecorder')->model('shop_credit')->getList('*', array ('shop_id' => $shop_id ) );

        $sql = "select a.order_id,a.total_amount,a.payed,a.member_id,a.shop_id,a.order_bn,a.pay_time
            ,b.refund_fee,b.status as refund_status 
            from sdb_ecorder_orders as a left join sdb_ecorder_tb_refunds as b
            ON a.order_bn=b.tid and a.shop_id=b.shop_id
            where a.member_id in ($member_ids) and a.shop_id='$shop_id' 
            and a.pay_status='1' and a.status in ('active','finish') ";
        $rs = $db->select($sql);
        if($rs){
            //获取客户的生日信息
            $member = $db->selectRow('select b_year,b_month,b_day from sdb_taocrm_member_ext where member_id='.$member_id);
            
            //积分设置（叠加 还是 排他）
            $point_log = $db->select('select set_type from sdb_ecorder_point_set_logs order by create_time desc limit 1');
        }

        foreach($rs as $rs_amount){
            $id = $rs_amount['order_id'];
            $single_amount = $rs_amount['total_amount'];
            $single_payed = $rs_amount['payed'];
            
            if($rs_amount['refund_status']=='SUCCESS'){
                $single_amount -= floatval($rs_amount['refund_fee']);
                $single_payed -= floatval($rs_amount['refund_fee']);
            }
            
            //处理单个商品的积分规则
            $good_sql = "select a.nums,a.amount,b.point_rule,b.fixed_point_num from sdb_ecorder_order_items as a
            left join sdb_ecgoods_shop_goods as b on a.goods_id=b.goods_id where a.order_id={$id}";
            $good_point_rules = $db->select($good_sql);
            $good_points = 0;
            foreach($good_point_rules as $goods){
                //特价商品送固定积分
                if($goods['point_rule'] == '2'){
                    $good_points = intval($goods['nums'] * $goods['fixed_point_num']);
                }
                
                //特价商品不送积分
                if($goods['point_rule'] == '2' or $goods['point_rule'] == '3'){
                    $single_amount -= $goods['amount'];
                    $single_payed -= $goods['amount'];
                }
            }            
            $points_mark = 1;

            //写入积分日志
            $shopId = $rs_amount ['shop_id'];
            $memberId = $rs_amount ['member_id'];
            $arr_log = array();
            $arr_log ['order_id'] = $id;
           // $arr_log ['point_type'] = 'order';
            $arr_log ['order_bn'] = $rs_amount['order_bn'];
            $arr_log ['shop_id'] = $shopId;
            $arr_log ['member_id'] = $memberId;
            $arr_log ['op_time'] = time();
            $arr_log ['op_user'] = 'system';
            $arr_log ['point_desc'] = '初始化';
            
            //通用积分规则计算
            foreach($rs_point_rules as $v){
                if( ! $v['cost_amount']){//'每个积分消费'为零时此积分规则为特殊积分规则（因为通用规则这个值大于等于1）
                    $has_special_rules = true;
                    continue;
                }

                if($v['count_type'] == 'total_amount'){
                    $amount = $single_amount;
                }else{
                    $amount = $single_payed;
                }

                if ($v ['amount_symbol'] == 'between') {
                    if ($amount >= $v ['min_amount'] && $amount <= $v ['max_amount']) {
                        $tag = true;
                    } else {
                        $tag = false;
                    }
                } elseif ($v ['amount_symbol'] == 'unlimited') {
                    $tag = true;
                } else {
                    $tag = $this->compara_val ( $v ['amount_symbol'], $amount, $v ['min_amount'] );
                    $tag = $tag;
                }
                if ($tag == true) {
                    $points = $points_mark * intval ( $amount / $v ['cost_amount'] );
                    break;
                }
            }
            
            //若有特价商品送固定积分
            $points = intval($points) + $good_points;
            
            //特殊积分倍数
            if($has_special_rules == true){
                $points = $points * $this->get_points_ratio($rs_point_rules,$point_log,$member,$rs_amount);
            }
            
            $points = floatval($points);
            if($points){
                $arr_log['points'] = $points;
               // $arr_log['points_type'] = $points_type;
                $oPointsLog->insert($arr_log);
            }
        }
        
        //app::get('taocrm')->model('member_points')->review($member_id); 
        return true;
    }
    
    function compara_val($sign, $val, $vall)
    {
        switch ($sign) {
            case 'gthan' :
                return $val > $vall;
                break;
            case 'sthan' :
                return $val < $vall;
                break;
            case 'equal' :
                return $val == $vall;
                break;
            case 'gethan' :
                return $val >= $vall;
                break;
            case 'sethan' :
                return $val <= $vall;
                break;
        }
    }
    
    //计算积分倍数
    public function get_points_ratio($rs_point_rules,$point_log,$member, $order)
    {
        if( ! $order['pay_time']){
            return 1;
        }
        
        $times_arr = array(1);
        $current_time = date('Y-m-d', $order['pay_time']);
        foreach ($rs_point_rules as $v ) {
            if($v ['cost_amount']){//'每个积分消费'值大于等于1时，为通用积分规则
                continue;
            }
            $point_times=array(
                '1' => '1.5',
                '2' => '2',
                '3' => '3',
                '4' => '5'
            );
            if($v['special_point_rule'] == '1'){
                if(strtotime($current_time) >= strtotime($v ['time_from']) && strtotime($current_time) <= strtotime($v ['time_to'])){
                    $times_arr[] = $point_times[$v['activity_point_times']];
                }
            }
            if($v['special_point_rule'] == '2'){
                if($v['birthday_type'] == '1' && $member['b_month'] && $member['b_day']){//当天
                    $current_day = date('m-d',time());
                    $b_month = strlen($member['b_month']) == 2 ? $member['b_month'] : '0'.$member['b_month'];
                    $b_day = strlen($member['b_day']) == 2 ? $member['b_day'] : '0'.$member['b_day'];
                    $birthday_day = $b_month.'-'.$b_day;
                    if($current_day == $birthday_day){
                        $times_arr[] = $point_times[$v['birthday_point_times']];
                    }
                }elseif($v['birthday_type'] == '2' && $member['b_month']){//当月
                    $current_month = date('m',time());
                    $birthday_month = strlen($member['b_month']) == 2 ? $member['b_month'] : '0'.$member['b_month'];
                    if($current_month == $birthday_month){
                        $times_arr[] = $point_times[$v['birthday_point_times']];
                    }
                }
            }
        }
        
        $re_times = 1;
        if($point_log[0]['set_type'] == 'exclude'){
            $re_times = max($times_arr);
        }elseif($point_log[0]['set_type'] == 'include'){
            foreach($times_arr as $n=>$m){
                if($m){
                    $re_times = $re_times * $m;
                }
            }
        }else{
            $re_times = max($times_arr);
        }
        return $re_times;
    }

  /*手机解绑---积分回写
 * 把全局积分表中积分回写到微信会员表中
 * 1.把该member_id微信积分写入微信会员表并写入会员积分日志表；2.全局积分表删除所有该member_id微信积分并写入全局积分日志表；3.全局会员表积分同步；
 * $data 参数 会员数组
 */
    public function update_points($data,& $msg){
        $objMemberPoints = app::get('taocrm')->model('member_points');
        //查询该member_id下的所有微信积分
        $points_list = $objMemberPoints->getList('points,id',array('points_type'=>'wechat','member_id'=>$data['member_id']));
        $total_point_wx = 0;
        foreach($points_list as $key => $value){
            $total_point_wx += $value['points'];
        }
        //把该member_id微信积分写入微信会员表并写入会员积分日志表
        $objWxMember = app::get('market')->model('wx_member');
        $msg_re = '';
        $id = $objWxMember->updatePoint($data['wx_member_id'],2,$total_point_wx,'手机解绑微信积分返回',$msg_re);
        if($id){
            //微信会员表中member_id清空
            $objWxMember->update(array('status'=>'false'),array('FromUserName'=>$data['FromUserName']));

            if($total_point_wx > 0){
                $get_point = $this->get($data['member_id'],$msg,'',time());
                $total_point = $get_point['total_point'];
                //全局积分表删除所有该member_id微信积分
                foreach($points_list as $key => $value){
                    $objMemberPoints->db->exec('DELETE FROM `sdb_taocrm_member_points` WHERE `id` = '.$value['id'].' LIMIT 1;');
                }
                $after_point = $total_point - $total_point_wx;
                //写入全局积分日志表
                $log = array(
                    'member_id'=>$data['member_id'],
                    'op_before_point'=>$total_point,
                    'op_after_point'=>$after_point,
                    'points'=>$total_point_wx * -1,
                    'point_desc'=>'手机解绑，微信积分扣除',
                );
                $this->addPointLog($log);
                //同步全局会员表
                $objMembers = app::get('taocrm')->model('members');
                $objMembers->update(array('points'=>$after_point,'point_update_time'=>time()),array('member_id'=>$data['member_id']));
            }
            return true;
        }else{
            $msg = $msg_re;
            return false;
        }
    }

    //通过推荐码更新积分接口
    function update_by_parent_code($id,$code,$point,& $msg){
        $rec_mod = app::get('taocrm')->model('members_recommend');
        $referee_data = $rec_mod->dump(array('self_code'=>$code));
        $member_id = $referee_data['member_id'];

        //调用java的积分更新接口的返回值
        $re = $this->update('',$member_id,2,$point,'推荐获取积分',null,'active');

        if(!empty($re) && $re['errcode'] == 0){
            return $id;
        }else{
            $msg = $re['errmsg'];
            return false;
        }
    }
}