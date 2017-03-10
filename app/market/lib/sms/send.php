<?php
class market_sms_send {

    //发送短信
    function sms_send(&$cursor_id,$params){
        $smssendobj=kernel::single("market_service_smsinterface");
        $smsobj = &app::get('market')->model('sms');
        $core_http = kernel::single('base_httpclient');
        base_kvstore::instance('market_sms')->fetch($params['file_name'],$contents);
        $sms = unserialize( $contents );//发送短信的内容
        $sms_batch_no=$v['sms_batch_no'];//短信发送的批次号

        $msgContents = array();
        foreach ($sms['sms_list'] as $k=>$v){
            $msgContents[] = array(
				'phones'=>$v['phones'],
				'content'=>$v['content'],//$msgContent,
            );
        }
        $content=json_encode($msgContents);
        if(!empty($sms['plan_send_time'])){
            base_kvstore::instance('market')->fetch('account', $account);
            $arr=unserialize($account);
            $plan_sent_time=$sms['plan_send_time'];
            $type='fan-out';
            $result=$smssendobj->time_send($content,$type,$sms_batch_no,$plan_sent_time);
            if ($result['res']=='succ'){
                $count=count($sms['sms_list']);
                $re =$this->sms_log_save($sms,$result);
                $sql="update sdb_market_sms set `success_num`=`success_num` +".$count.", is_send='1' where sms_id=".$sms['sms_id'];
                $smsobj->db->exec($sql);
            }else {
                $re =$this->sms_log_save($sms,$result);
            }
        }else {
            $type='fan-out';
            $result=$smssendobj->send($content,$type);
            if ($result['res']=='succ'){
                $re =$this->sms_log_save($sms,$result);
                $count=count($sms['sms_list']);
                $sql="update sdb_market_sms set `success_num`=`success_num` +".$count.", is_send='1' where sms_id=".$sms['sms_id'];
                $smsobj->db->exec($sql);
            }else {
                $re =$this->sms_log_save($sms,$result);
            }
        }
    }


    public function sms_log_save($data,$state){
        $members_log = &app::get('market')->model('sms_log');
        foreach ($data['sms_list'] as $k=>$v){
            $memberlist[]=$v['member_id'];
            $mobile[]=$v['mobile'];
        }
        if ($state['res']=='succ'){
            $status="success";
        }else {
            $status="failed";
        }
        $log=array(
			'member_id' =>json_encode($memberlist),
	    	'shop_id' =>$data['shop_id'],
	    	'mobile' => json_encode($mobile),
	    	'active_id' =>$data['active_id'],
	   		'sms_id' => $data['sms_id'],
	   		'batch_no' =>$data['batch_no'],
	    	'plan_send_time' =>$data['plan_send_time'],
	   		'sms_batch_no'=>$data['sms_batch_no'],
			'reason'=>json_encode($state),
			'status'=>$status,
			'create_time'=>$data['create_time'],
        );
        $re=$members_log->insert($log);
    }

    //定时运行   解冻 扣除佣金的接口
    /*public function op_fu(){
     $systemType = kernel::single('taocrm_system')->getSystemType();
     $smssendobj=kernel::single("market_service_smsinterface");
     $smsreobj = &app::get('market')->model('sms_op_record');
     $orderobj = &app::get('ecorder')->model('orders');
     $smsobj = &app::get('market')->model('active_assess');
     $filter=array('state'=>'unfinish');
     $data_list=$smsobj->getList("*",$filter);
     foreach ($data_list as $k=>$v){
     $msgid=$v['msgid'];
     $dead_time = $v['exec_time'] + (15*86400);
     if ((time()) > $dead_time){
     $activemems=unserialize($v['active_members']);
     $date_from=$v['exec_time'];
     $date_to=$dead_time;
     $filter=array('createtime|between'=>array($date_from,$date_to));
     $renums=$orderobj->getList('member_id',$filter);
     foreach ($renums as $k=>$v){
     $rememuns[]=$v[member_id];
     }
     $s=array_intersect($rememuns,$activemems);
     $count=count($s);//二次购买的人数
     $op_obj = &app::get('market')->model('sms_op_record');
     $smsfreezenums=$smsreobj->dump(array('msgid'=>$msgid,'action'=>'freeze'),"nums");//冻结的短信条数

     //扣除佣金
     $commission_nmu=($count*$systemType['pay_rule']);
     if ($commission_nmu>0){
     $result=$smssendobj->ununfreeze($msgid,$commission_nmu,"commission");
     if ($result['res']=='succ'){
     $para=array(
     'action'=>'deduct',
     'msgid'=>$msgid,
     'nums'=>$commission_nmu,
     'remark'=>'扣除佣金',
     'status'=>'deductsucc',
     'create_time'=>time(),
     );
     }else {
     $para=array(
     'action'=>'deduct',
     'msgid'=>$msgid,
     'nums'=>$commission_nmu,
     'remark'=>'扣除佣金',
     'status'=>'deductfail',
     'create_time'=>time(),
     );
     }
     $op_obj->save($para);
     }

     //解冻
     $unblock_nmu=($smsfreezenums['nums']-$commission_nmu);
     if ($unblock_nmu>0){
     $res=$smssendobj->ununfreeze($msgid,$unblock_nmu,"unlock");
     if ($res['res']=="succ"){
     $para=array(
     'action'=>'unfreeze',
     'msgid'=>$msgid,
     'nums'=>$unblock_nmu,
     'remark'=>'短信解冻',
     'status'=>'freezesucc',
     'create_time'=>time(),
     );
     }else {
     $para=array(
     'action'=>'unfreeze',
     'msgid'=>$msgid,
     'nums'=>$unblock_nmu,
     'remark'=>'短信解冻',
     'status'=>'freezefail',
     'create_time'=>time(),
     );
     }
     $op_obj->save($para);
     }
     $smsobj->update(array('state'=>'finish'),array('msgid'=>$msgid));
     }
     }
     }*/

    public function op_fu(){
        //kernel::ilog('crontab op_fu start......');
        base_kvstore::instance('market')->fetch('account', $arr);
        $arr= unserialize($arr);
        $sms_config = array(
                 'entid'=>$arr['entid'],
                 'password'=>$arr['password'],
                 'license'=>base_certificate::get('certificate_id') ? base_certificate::get('certificate_id') : 1,
                 'source'=>APP_SOURCE,
                 'app_token'=>APP_TOKEN,
        );
        $systemType = kernel::single('taocrm_system')->getSystemType();
        $jobarray = array (
            'sms_config' => $sms_config,
 			'market_pay_rule' => $systemType['market_pay_rule'],
        );

        kernel::single('market_backstage_activity')->countCommission($jobarray);
        //kernel::single('taocrm_service_queue')->addJob('market_backstage_activity@countCommission',$jobarray);
        //kernel::ilog('crontab op_fu end......');

        $this->monitorCountCommission();
    }

    public function monitorCountCommission(){
        $data = array('payed_nums'=>0,
            'payed_sms_nums'=>0,
            'unpayed_nums'=>0,
            'unpayed_sms_nums'=>0,
            'partpayed_nums'=>0,
            'partpayed_sms_nums'=>0,
        	'uncount_assess_nums'=>0,
        	'count_assess_nums'=>0,
            'uncount_assess_list'=>array()
        );

        kernel::single('taocrm_service_redis')->redis->del($_SERVER['SERVER_NAME'].':count_commission');
        $db = kernel::database();
        $row = $db->selectrow('select count(*) as total from sdb_market_active where pay_type="market" and is_active="finish"');
        if($row['total'] > 0){

            //还没结算的营销统计
            $row = $db->selectrow('select count(*) as total from sdb_market_active_assess as a left join sdb_market_active as b on a.active_id = b.active_id where a.state="finish" and b.pay_type="market"');
            $data['count_assess_nums'] = $row['total'];

            $row = $db->selectrow('select count(*) as total from sdb_market_active_assess as a left join sdb_market_active as b on a.active_id = b.active_id where a.state="unfinish" and b.pay_type="market"');
            $data['uncount_assess_nums'] = $row['total'];
            if($row['total'] > 0){
                $rows = $db->select('select a.end_time from sdb_market_active_assess as a left join sdb_market_active as b on a.active_id = b.active_id where a.state="unfinish" and b.pay_type="market" order by a.end_time');
                foreach($rows as $row){
                    $data['uncount_assess_list'][] = $row['end_time'];
                }
            }

            //支付
            $row = $db->selectrow('select count(*) as total,sum(plan_pay) as sms_nums from sdb_market_sms_deduction_record where status="paysucc"');
            $data['payed_nums'] = $row['total'];
            $data['payed_sms_nums'] = $row['sms_nums'] ? $row['sms_nums'] : 0;

            //未支付
            $row = $db->selectrow('select count(*) as total,sum(plan_pay) as sms_nums from sdb_market_sms_deduction_record where status="unpay"');
            $data['unpayed_nums'] = $row['total'];
            $data['unpayed_sms_nums'] = $row['sms_nums'] ? $row['sms_nums'] : 0;

            //部分支付
            $row = $db->selectrow('select count(*) as total,sum(plan_pay) as plan_sms_nums,sum(actual_pay) as actual_sms_nums from sdb_market_sms_deduction_record where status="paypart"');
            $data['partpayed_nums'] = $row['total'];
            $row['plan_sms_nums'] = $row['plan_sms_nums'] ? $row['plan_sms_nums'] : 0;
            $row['actual_sms_nums'] = $row['actual_sms_nums'] ? $row['actual_sms_nums'] : 0;
            //还需支付短信数
            $data['partpayed_sms_nums'] = $row['plan_sms_nums'] - $row['actual_sms_nums'];

            kernel::single('taocrm_service_redis')->redis->set($_SERVER['SERVER_NAME'].':count_commission',json_encode($data));
        }


    }
}
