<?php
class market_coupon_send_rule{
    public function run(){
        $couponSentObj = &app::get('market')->model('coupon_sent');
        $couponRpcObj = kernel::single('market_rpc_request_coupon');
        $smsSentObj = &app::get('market')->model('sms_sent');
        $ruleObj = &app::get('market')->model('coupon_send_rules');
        $ruleMemberObj = &app::get('market')->model('coupon_rule_member');

        $rules = $ruleObj->getList('*',array('status'=>'1'),0,-1,'`condition` desc');
        foreach($rules as $rule){
            if($rule['shop_id'] && $rule['shop_id']!=''){
                $whereSql .= " and O.shop_id='".$rule['shop_id']."'";
            }

            if($rule['config']['member_lv_ids'] && $rule['config']['member_lv_ids'][0]!='all'){
                $whereSql .= " and shop_lv_id in ('".implode("','",$rule['config']['member_lv_ids'])."') ";
            }

            if($rule['condition'] && $rule['condition']>0){
                $havingSql = ' HAVING amount>'.$rule['condition'];
            }

            $startTime = strtotime(date("Y-m-d", time()-86400));

            $whereSql .= " and O.createtime>=".$startTime;
            $whereSql .= " and O.createtime<".($startTime+86400);

            $sql = 'SELECT O.member_id, sum(O.total_amount) as amount, M.mobile FROM sdb_ecorder_orders as O
                LEFT JOIN sdb_market_members as M ON O.member_id=M.member_id
                WHERE 1 '.$whereSql.' GROUP BY O.member_id '.$havingSql;

            $dataList = $ruleObj->db->select($sql);
            
            foreach($dataList as $data){
                if(!in_array($data['member_id'],$memFilter[$rule['shop_id']])){
                    if($rule['coupon_id']){
                        $couponSend['coupon_id'] = $rule['coupon_id'];
                        $couponSend['relate_id'] = $rule['rule_id'];
                        $couponSend['send_type'] = '1';
                        $couponSend['send_time'] = time();
                        $couponSend['member_id'] = $data['member_id'];
                        $couponSend['theme_id'] = $rule['theme_id'];
                        $couponSentObj->insert($couponSend);
                        $couponRpcObj->send($couponSend);
                        unset($couponSend);
                    }
                    if($rule['theme_id'] && $data['mobile']){
                        $smsSend['member_id'] = $data['member_id'];
                        $smsSend['mobile'] = $data['mobile'];
                        $smsSend['theme_id'] = $rule['theme_id'];
                        $smsSend['relate_id'] = $rule['rule_id'];
                        $smsSend['sms_type'] = '1';
                        $smsSend['send_time'] = time();
                        $smsSend['send_status'] = '0';
                        $smsSentObj->insert($smsSend);
                        unset($smsSend);
                    }
                    $ruleMember['rule_id'] = $rule['rule_id'];
                    $ruleMember['member_id'] = $data['member_id'];
                    $ruleMember['coupon_id'] = $rule['coupon_id'];
                    $ruleMember['theme_id'] = $rule['theme_id'];
                    $ruleMember['createtime'] = time();
                    $ruleMemberObj->insert($ruleMember);
                    unset($ruleMember);
					
                    $memFilter[$rule['shop_id']][] = $data['member_id'];
                }
            }
            $ruleObj->update(array('last_exec_time'=>time()),array('rule_id'=>$rule['rule_id']));
            unset($dataList);
        }
        unset($rules);
        return true;
    }
}