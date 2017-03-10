<?php
class market_backstage_fxsms{


    function fetch($data){
        if(empty($data['activity_id']) || empty($data['sms_id'])){
            return array('status'=>'fail','errmsg'=>'param is error');
        }

        $db = kernel::database();
        $activity = $db->selectrow('select valid_num,templete,unsubscribe,filter_mem from sdb_market_fx_activity  where activity_id='.$data['activity_id']);
        if(!$activity){
            return array('status'=>'fail','errmsg'=>'activity is no exist');
        }

        if($activity['valid_num'] <= 0){
            return array('status'=>'fail','errmsg'=>'activity hos no valid nums');
        }

        $activity['filter_mem'] = unserialize($activity['filter_mem']);
        //积分兑换完整地址
        $url = kernel::base_url(1);
        $url = $url . '/index.php/taocrm/default/index/app/site';
        //将积分兑换地址变为短地址
        $SinaObj = kernel::single('market_shorturl');
        $shorturl = $SinaObj->shortenSinaUrl($url);


        $activity_id = $data['activity_id'];
        $sms_id = $data['sms_id'];
        $sms_content = $activity['templete'];
        if($activity['unsubscribe'] == 1){
            //            $sms_templates['content'] = $unsubscribe['templete'].' 退订回N';
            $sms_content .= ' 退订回N';
        }

        if(strstr($sms_content, '<{积分兑换}>')){
            $sms_content = str_replace('<{积分兑换}>', $shorturl,$sms_content);
        }

        if(strstr($sms_content, '<{店铺}>')){
            $shop = $db->selectrow('select name from sdb_ecorder_shop where shop_id="'.$activity['shop_id'].'"');
            $sms_content = str_replace('<{店铺}>', $shop['name'],$sms_content);
        }

        $page_size = 200;
        //$this->setActivityMQueueTemplete($active_id);

        $smsObj = app::get('market')->model('fx_sms');
        $sms = $smsObj->getSms($sms_id);
        if(empty($sms)){
            return array('status'=>'fail','errmsg'=>'sms is no exist');
        }

        if( ($sms['succ_num'] + $sms['fail_num']) >= $sms['total_num']){
            return array('status'=>'succ');
        }

        $pages = ceil($activity['valid_num'] / $page_size);


        $db->exec('delete from sdb_market_fx_sms_log where sms_id='.$sms_id);
        $db->exec('START TRANSACTION;');
        for($page = 0;$page < $pages;$page++){
            $log = array('sms_id'=>$data['sms_id'],'page'=>$page);
            $db->insert("sdb_market_fx_sms_log",$log);
        }
        $db->exec('COMMIT; ');

        for($page = 0;$page < $pages;$page++){
            $rows =  kernel::single('market_mdl_fx_activity')->getSmsTaskList($activity['filter_mem']);
            if(empty($rows))continue;

            $sms_list = array();
            $mids = array();
            foreach($rows as $row){
                if(strstr($sms_content, '<{用户名}>')){
                    $sms_content = str_replace('<{用户名}>', $row['ship_name'],$sms_content);
                }

                $sms_list[] = array('phones'=>$row['mobile'],'content'=>$sms_content);
                $mids[] = $row['member_id'];
            }
            $result = $this->sendSms($sms_list);
             
            $this->updateSms($sms_id,count($sms_list),$result);
            $this->updateSmsLog($sms_id,$page,$result);
            $this->updateMembers($mids);
        }

        $db->exec('update sdb_market_fx_sms set send_status="succ" where sms_id='.$sms_id);

        return array('status'=>'succ');

    }

    /*function afreshSendImport($batch_id,$sms_id){
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
     $result = $this->sendSms($sms_list);

     $this->updateSms($sms_id,count($sms_list),$result);
     $this->updateSmsLog($sms_id,$page,$result);
     $this->updateMembers($mids);
     }
     $db->exec('update sdb_taocrm_member_import_batch set last_send_status="succ" where batch_id='.$batch_id);
     $db->exec('update sdb_taocrm_member_import_sms set send_status="succ" where sms_id='.$sms_id);
     }*/

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

        $db->exec('update sdb_market_fx_sms set succ_num=succ_num+'.$succ_nums.',fail_num=fail_num+'.$fail_nums.',last_send_time='.time().' where sms_id='.$sms_id);
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
        $db->exec('update sdb_market_fx_sms_log set is_send='.$is_send.',reason="'.$reason.'",send_time='.time().' where sms_id='.$sms_id .' and page='.$page);
    }

    function updateMembers($mids){
        $db = kernel::database();
        $db->exec('update sdb_taocrm_fx_members set last_send_time='.time().' where member_id in('.implode(',', $mids).')');
    }

    function sendSms($sms_list){
        $smssendobj= kernel::single('market_service_smsinterface');
        $content=json_encode($sms_list);
        $succ_count = count($sms_list);

        $type='fan-out';
        $result=$smssendobj->send($content,$type);
        if($result){
            return $result;
        }else{
            return array();
        }
    }
     

}

