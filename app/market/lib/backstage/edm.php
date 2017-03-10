<?php
class market_backstage_edm{

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
        $edmsendobj= kernel::single('market_service_edminterface');
        
        $shop_name = '';
        if($data['shop_id'] != ''){
            $oShop = app::get('ecorder')->model('shop');
            $shop_arr = $oShop->dump($data['shop_id']);
            if($shop_arr) {
                $shop_name = $shop_arr['name'];
            }
        }
        
        $edm_list = array();
        $mids = array();
        $title= "";
        $email= "";
        $content="";
        foreach($data['edm_list'] as $edm){
            //$edm_list[] = array('title'=>$edm['title'],'email'=>$edm['email'],'content'=>$edm['content']);
            $title        = $edm['title'];
            $content      = $edm['content'];
            $edm_list[]   = $edm['email'].':'.$edm['uname'];
            $mids[]       = $edm['member_id'];

            //print_r($edm);
            //print_r('fffffffffffffffffffffffffffffffffff');
        }
        $succ_count = count($edm_list);
        $email      = implode(',',$edm_list);

        $db = kernel::database();
        $db->exec('START TRANSACTION;');
        if(isset($data['plan_send_time']) && !empty($data['plan_send_time'])){
            $type='fan-out';
            $result=$edmsendobj->send($title,$email,$content,$type,$shop_name);

            if ($result['res']=='succ'){
                $this->edm_log_save($data,$result);
                $sql="update sdb_market_edm set `success_num`=`success_num` +".$succ_count.", is_send='sending' where id=".$data['edm_id'];
                $db->exec($sql);
            }else {
                $this->edm_log_save($data,$result);
                $sql="update sdb_market_edm set is_send='fail' where id=".$data['edm_id'];
                $db->exec($sql);
            }
        }else {
            $type='fan-out';
            $type='notice';
            $result=$edmsendobj->send($title,$email,$content,$type,$shop_name);
            //print_r($result);
            if ($result['res']=='succ'){
                $this->edm_log_save($data,$result);
                $sql="update sdb_market_edm set `success_num`=`success_num` +".$succ_count.", is_send='sending' where id=".$data['edm_id'];
                $rs=$db->exec($sql);
            }else {
                $this->edm_log_save($data,$result);
                $sql="update sdb_market_edm set is_send='fail' where id=".$data['edm_id'];
                $db->exec($sql);
            }
        }

        $sql='update sdb_market_edm set is_send="succ",end_time='.time().' where id='.$data['edm_id'].' and `total_num`=`success_num`';
        $rs=$db->exec($sql);

        //更新队列完成状态和完成时间
        $sql='update sdb_market_activity_edm_queue set is_send_finish="1",sent_time='.time().' where active_id='.$data['active_id'] .' and member_id in('.implode(',', $mids).')';
        $rs=$db->exec($sql);

        $db->exec('COMMIT; ');
        $db->dbclose();

        return array('status'=>'succ');
    }



    public function edm_log_save($data,$state){

        $memberlist = array();
        $email = array();
        foreach ($data['edm_list'] as $k=>$v){
            if (is_numeric($k)){
                $memberlist[]=$v['member_id'];
                $email[]=$v['email'];
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
    		'shop_id'   => $data['shop_id'],
    		'email'     => json_encode($email),
    		'active_id' => $data['active_id'],
   			'edm_id'    => $data['edm_id'],
   			'batch_no'  =>$data['batch_no'],
    		'plan_send_time' => isset($data['plan_send_time']) ? $data['plan_send_time'] : 0,
   			'edm_batch_no'=>isset($data['edm_batch_no']) ? $data['edm_batch_no'] : 0,
			'reason'=>json_encode($state),
			'status'=>$status,
			'create_time'=>time(),
        );
        $db = kernel::database();
        $re=$db->insert("sdb_market_edm_log",$log);

        if(!$re){
            ilog(sprintf("%s:error edm_log_save \n param:%s",$_SERVER['SERVER_NAME'],var_export($log,true)));
        }

        return true;

        /*
         * $real_sql
         */

    }








}

