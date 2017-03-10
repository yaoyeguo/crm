<?php
class market_backstage_sms{

    /*
     发送短信
     $data = array(
     'sms_config'=>array(
     'entid'=>'11',
     'password'=>'11',
     'license'=>'11',
     'source'=>'11',
     'app_token'=>'11'
     ),
     'shop_id'=>'11',
     'active_id'=>'11',
     'sms_id'=>'11',
     'batch_no'=>'11',
     'sms_list'=>array(
     'member_id'=>11,
     'phones'=>'11232323',
     'content'=>'xxx',
     ),
     );

     */
    function send($data){
        $smssendobj= kernel::single('market_service_smsinterface');
        $sms_list = array();
        $mids = array();
        foreach($data['sms_list'] as $sms){
            $sms_list[] = array('phones'=>$sms['phones'],'content'=>$sms['content']);
            $mids[] = $sms['member_id'];
        }
        $content=json_encode($sms_list);
        $succ_count = count($sms_list);

        $db = kernel::database();
        $db->exec('START TRANSACTION;');
        if(isset($data['plan_send_time']) && !empty($data['plan_send_time'])){
            $type='fan-out';
            $result=$smssendobj->time_send($content,$type,$data['sms_batch_no'],$data['plan_send_time']);
            if($result){
                if ($result['res']=='succ'){
                    $this->sms_log_save($data,$result);
                    $sql="update sdb_market_sms set `success_num`=`success_num` +".$succ_count.", is_send='sending',end_time=".time()." where sms_id=".$data['sms_id'];
                    $db->exec($sql);
                }else {
                    $this->sms_log_save($data,$result);
                    $sql="update sdb_market_sms set is_send='fail',end_time=".time()." where sms_id=".$data['sms_id'];
                    $db->exec($sql);
                }
            }else{
                return array('status'=>'timeout');
            }
        }else {
            $type='fan-out';
            $result=$smssendobj->send($content,$type);
            if($result){
                if ($result['res']=='succ'){
                    $this->sms_log_save($data,$result);
                    $sql="update sdb_market_sms set `success_num`=`success_num` +".$succ_count.", is_send='sending',end_time=".time()." where sms_id=".$data['sms_id'];
                    $rs=$db->exec($sql);
                }else {
                    $this->sms_log_save($data,$result);
                    $sql="update sdb_market_sms set is_send='fail',end_time=".time()." where sms_id=".$data['sms_id'];
                    $db->exec($sql);
                }
            }else{
                return array('status'=>'timeout');
            }
        }

        $sql='update sdb_market_sms set is_send="succ",end_time='.time().' where sms_id='.$data['sms_id'].' and `total_num`=`success_num`';
        $rs=$db->exec($sql);

        //更新队列完成状态和完成时间
        $sql='update sdb_market_activity_m_queue set is_send_finish="1",sent_time='.time().' where active_id='.$data['active_id'] .' and member_id in('.implode(',', $mids).')';
        $rs=$db->exec($sql);

        $db->exec('COMMIT; ');
        $db->dbclose();

        return array('status'=>'succ');
    }



    public function sms_log_save($data,$state){

        $memberlist = array();
        $mobile = array();
        foreach ($data['sms_list'] as $k=>$v){
            if (is_numeric($k)){
                $memberlist[]=$v['member_id'];
                $mobile[]=$v['phones'];
            }
        }
        if ($state['res']=='succ'){
            $status="success";
        }else {
            $status="failed";
        }
        $log=array(
			'type'=>1,
			'member_id' =>json_encode($memberlist),
    		'shop_id' => $data['shop_id'],
    		'mobile' => json_encode($mobile),
    		'active_id'=> $data['active_id'],
   			'sms_id' => $data['sms_id'],
   			'batch_no' =>$data['batch_no'],
    		'plan_send_time' => isset($data['plan_send_time']) ? $data['plan_send_time'] : 0,
   			'sms_batch_no'=>isset($data['sms_batch_no']) ? $data['sms_batch_no'] : 0,
			'reason'=>json_encode($state),
			'status'=>$status,
			'create_time'=>time(),
        );
        $db = kernel::database();
        $re=$db->insert("sdb_market_sms_log",$log);

        if(!$re){
            ilog(sprintf("%s:error sms_log_save \n param:%s",$_SERVER['SERVER_NAME'],var_export($log,true)));
        }

        return true;

        /*
         * $real_sql
         */

    }








}

