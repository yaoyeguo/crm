<?php
class market_backstage_import{

    function fetch($data){
        if(empty($data['batch_id']) || empty($data['sms_id']) || empty($data['sms_content'])){
            return array('status'=>'fail','errmsg'=>'param is error');
        }

        $batch_id = $data['batch_id'];
        $sms_id = $data['sms_id'];
        $sms_content = $data['sms_content'];
        $page_size = 200;
        //$this->setActivityMQueueTemplete($active_id);
        $batchObj = app::get('taocrm')->model('member_import_batch');
        $batch = $batchObj->getBatch($batch_id);

        $smsObj = app::get('taocrm')->model('member_import_sms');
        $sms = $smsObj->getSms($sms_id);
        if( ($sms['succ_num'] + $sms['fail_num']) > $sms['total_num']){
            return array('status'=>'succ');
        }

        $pages = ceil($batch['mobile_valid_nums'] / $page_size);

        $db = kernel::database();
        $db->exec('START TRANSACTION;');
        for($page = 0;$page < $pages;$page++){
            $log = array('sms_id'=>$data['sms_id'],'page'=>$page);
            $db->insert("sdb_taocrm_member_import_sms_log",$log);
        }
        $db->exec('COMMIT; ');

        for($page = 0;$page < $pages;$page++){
            $rows = $db->select('select member_id,mobile from sdb_taocrm_member_import where batch_id='. $batch_id .' and is_mobile_valid=1 order by member_id limit '.($page*$page_size).','.$page_size);
            if(empty($rows))continue;

            $sms_list = array();
            $mids = array();
            foreach($rows as $row){
                $sms_list[] = array('phones'=>$row['mobile'],'content'=>$sms_content);
                $mids[] = $row['member_id'];
            }
            $result = $this->sendSms($sms_list, $data);
             
            $this->updateSms($sms_id,count($sms_list),$result);
            $this->updateSmsLog($sms_id,$page,$result);
            $this->updateMembers($mids);
        }

        $db->exec('update sdb_taocrm_member_import_batch set last_send_status="succ" where batch_id='.$batch_id);
        $db->exec('update sdb_taocrm_member_import_sms set send_status="succ" where sms_id='.$sms_id);

        return array('status'=>'succ');

    }

    function afreshSendImport($batch_id,$sms_id)
    {
        $data['batch_id'] = $batch_id;
        $smsObj = app::get('taocrm')->model('member_import_sms');
        $sms = $smsObj->getSms($sms_id);
        if( ($sms['succ_num'] + $sms['fail_num']) > $sms['total_num']){
            return false;
        }

        $page_size = 200;
        $db = kernel::database();
        $pageList = $db->select('select page from sdb_taocrm_member_import_sms_log  where sms_id='.$sms_id.' and is_send=0');
        foreach($pageList as $pageRow){
            $page = $pageRow['page'];
            $rows = $db->select('select member_id,mobile from sdb_taocrm_member_import where batch_id='. $batch_id .' and is_mobile_valid=1 order by member_id limit '.($page*$page_size).','.$page_size);
            if(empty($rows))continue;

            $sms_list = array();
            $mids = array();
            foreach($rows as $row){
                $sms_list[] = array('phones'=>$row['mobile'],'content'=>$sms['template']);
                $mids[] = $row['member_id'];
            }
            $result = $this->sendSms($sms_list, $data);
             
            $this->updateSms($sms_id,count($sms_list),$result);
            $this->updateSmsLog($sms_id,$page,$result);
            $this->updateMembers($mids);
        }
        $db->exec('update sdb_taocrm_member_import_batch set last_send_status="succ" where batch_id='.$batch_id);
        $db->exec('update sdb_taocrm_member_import_sms set send_status="succ" where sms_id='.$sms_id);
    }

    function updateSms($sms_id,$sms_len,$result){
        $db = kernel::database();
        $succ_nums = 0;
        $fail_nums = 0;
        if(!empty($result)){
            if($result['res']=='succ'){
                $succ_nums = $sms_len;
            }else{
                $fail_nums = $sms_len;
            }
        }else{
            $fail_nums = $sms_len;
        }

        $db->exec('update sdb_taocrm_member_import_sms set succ_num=succ_num+'.$succ_nums.',fail_num=fail_num+'.$fail_nums.',last_send_time='.time().' where sms_id='.$sms_id);
    }


    function updateSmsLog($sms_id,$page,$result){
        $db = kernel::database();
        $is_send = 0;
        $reason = '';
        if(!empty($result)){
            if($result['res']=='succ'){
                $is_send = 1;
            }else{
                $is_send = 2;
                $reason = json_encode($result);
            }
        }else{
            $is_send = 2;
            $reason = 'timeout';
        }
        $db->exec('update sdb_taocrm_member_import_sms_log set is_send='.$is_send.',reason=\''.$reason.'\',send_time='.time().' where sms_id='.$sms_id .' and page='.$page);
    }

    function updateMembers($mids){
        $db = kernel::database();
        $db->exec('update sdb_taocrm_member_import set send_count=send_count+1,last_send_time='.time().' where member_id in('.implode(',', $mids).')');
    }
     
    function sendSms($sms_list, $data=array())
    {
        $smssendobj= kernel::single('market_service_smsinterface');
        $content=json_encode($sms_list);
        $succ_count = count($sms_list);

        $type='fan-out';
        $result=$smssendobj->send($content,$type);
        if(!$result){
            $result = array();
        }
        
        //保存全局短信日志
        $this->oLog = app::get('taocrm')->model('sms_log');
        $this->source = 'taocrm_member_import_batch';
        $this->source_id = $data['batch_id'];
        $this->op_user = $data['op_user'];
        $this->ip = $data['ip'];
        $this->status = ($result['res']=='succ' ? 'succ' : 'fail');
        $this->remark = ($this->status=='succ' ? '' : json_encode($result));
        $this->save_sms_log($sms_list);
        
        return $result;
    }
    
    //保存全局短信日志
    function save_sms_log($sms_list)
    { 
        foreach($sms_list as $v){
            $log = array(
                'source'=>$this->source,
                'source_id'=>$this->source_id,
                'batch_no'=>0,
                'mobile'=>$v['phones'],
                'content'=>$v['content'],
                'status'=>$this->status,
                'send_time'=>time(),
                'create_time'=>time(),
                'sms_size'=>ceil(mb_strlen($v['content'],'utf-8')/67),
                'cyear'=>date('Y'),
                'cmonth'=>date('m'),
                'cday'=>date('d'),
                'op_user'=>$this->op_user,
                'ip'=>$this->ip,
                'remark'=>$this->remark,
            );
            $this->oLog->insert($log);
        }
    }
}
