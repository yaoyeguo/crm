<?php
class market_backstage_edmactivity{


    var $activity;
    var $activeMemberNums = 0;
    var $assessMemberNums = 0;
    //var $actualMemberNums = 0;
    var $smsConfig;
    var $assessTime = 1296000;
    /**
     *
     * 根据搜索条件生成队列表,总数不超过活动总人数，冻结短信和短信帐户检测在前台做掉
     * 	$data = array(
     'day'=>'2012-06-28',
     'session'=>'610090621766fa16cbdb26c202e34b68aa9f6d06baf4dcc374544688',
     'node_id'=>'',
     'token'=>'',
     );
     */
    function fetch($data){
        if(empty($data['active_id'])){
            return array('status'=>'fail','errmsg'=>'param is error');
        }

        /*if(isset($data['sms_config'])){
            $this->smsConfig = $data['sms_config'];
        }*/

        //根据过滤条件生成活动客户队列表
        $this->processMember($data['active_id']);

        if($this->activity && $this->activeMemberNums > 0 && $this->activity["type"]){
            //营销活动评估以及对照组处理
            $this->processControlGroup($data['active_id'],$data['msgid']);

            if (in_array('edm', $this->activity["type"])){
                $this->sendEdm($data['active_id'],$data['msgid']);
            }
        }

        kernel::database()->dbclose();

        return array('status'=>'succ');

    }

    function processControlGroup($active_id,$msgid){
        $db = kernel::database();
        //清楚重复数据
        $sql = 'delete from sdb_market_active_member where active_id='.$active_id;
        $db->exec($sql);

        if ($this->activity['control_group']=='no'){
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,0 from sdb_market_activity_edm_queue where active_id='.$active_id;
            $db->exec($sql);
            $is_control = 0;
        }elseif($this->activity['control_group']=='yes') {//开启对照组
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,1 from sdb_market_activity_edm_queue where active_id='.$active_id.' and is_send=1';
            $db->exec($sql);
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,2 from sdb_market_activity_edm_queue where active_id='.$active_id.' and is_send=2';
            $db->exec($sql);
            $is_control = 1;
        }

        //清楚重复数据
        $sql = 'delete from sdb_market_active_assess where active_id='.$active_id;
        $db->exec($sql);

        $exec_time = $this->activity['sent_time']?$this->activity['sent_time']:time();
        $assess_array=array(
			'active_id'=>$this->activity['active_id'],
        //'active_members'=>'',
			'shop_id'=>$this->activity['shop_id'],
        //'con_members'=>$noactiveids,
			'create_time'=>$this->activity['create_time'],
			'end_time'=>$exec_time + $this->assessTime,
			'exec_time'=>$exec_time,
            'msgid'=>$msgid,
            'is_control'=>$is_control,
			'state'=>'unfinish',
        );

        $db->insert('sdb_market_active_assess', $assess_array);
        
        $this->processAsses($active_id);
    }

    function sendEdm($active_id,$msgid){
        $db = kernel::database();
        //print_r($this->activity);
        
        //积分兑换完整地址
		$url = kernel::base_url(1);
		$url = $url . '/index.php/taocrm/default/index/app/site';
		//将积分兑换地址变为短地址
        $SinaObj = kernel::single('market_shorturl');
       	$shorturl = $SinaObj->shortenSinaUrl($url);

        $edm_array=array();
        $edm_array['type']       ='batch';
        $edm_array['theme_id']   =$this->activity['template_id'];
        $edm_array['active_name']=$this->activity['active_name'];
        $edm_array['active_id']  =$active_id;
        $edm_array['type']       ='1';
        $edm_array['create_time']=time();
        $edm_array['plan_send_time']=$this->activity['sent_time'];
        $edm_array['total_num']  =$this->activeMemberNums;
        $edm_array['shop_id']    =$this->activity['shop_id'];
        $edm_array['success_num']=0;//shop_id
        $edm_array['is_send']    ='unsend';

        $db->insert('sdb_market_edm', $edm_array);
        $edm_id = $db->lastinsertid();

        $edm_templates = $db->selectrow('select theme_title,theme_content from sdb_market_edm_templates where theme_id='.$this->activity['template_id'],$shorturl);
        
        $edm_templates['theme_content'] = $this->activity['templete'];
        $edm_templates['theme_title'] = $this->activity['templete_title'];

        $page = 0;
        $page_size = 600;
        $n = 1;
        while(true){
            $data = array();
            $rows = $db->select('select member_id,uname,email from sdb_market_activity_edm_queue where is_send=1 and  active_id='.$active_id .' order by member_id limit '.($page*$page_size).','.$page_size);

            if(empty($rows))break;
             
            $edm_list = array();
            foreach($rows as $row){
                //print_r($row);
                $msgContent = str_replace(array('<{用户名}>','<{店铺}>','<{积分兑换}>'), array($row['uname'],$this->activity['shop_name'],$shorturl), Stripslashes($edm_templates['theme_content']) );
                //print_r($msgContent);
                //echo("sssssssss");
                $msgTitle  = $edm_templates['theme_title'];
                $edm_list[] = array('member_id'=>$row['member_id'],'uname'=>$row['uname'],'email'=>$row['email'],'title'=>$msgTitle,'content'=>$msgContent);
            }
            $data = array(
             //'sms_config'=>$this->smsConfig,
             'edm_batch_no'  => $edm_id."_".$n,
             'plan_send_time'=> $this->activity['sent_time'],
             'shop_id'       => $this->activity['shop_id'],
             'active_id'     => $active_id,
             'edm_id'        => $edm_id,
             'batch_no'      => $msgid,
             'edm_list'      => $edm_list,
            );
            //var_export($data);exit;
            //ilog(var_export($data,true));
            kernel::single('taocrm_service_queue')->addJob('market_backstage_edm@send',$data);
            $page++;
            $n++;
        }
    }

    function processMember($active_id){
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_active where active_id='.$active_id);
        if(!$activity)return false;
        $shop = $db->selectrow('select name from sdb_ecorder_shop where shop_id="'.$activity['shop_id'].'"');
        $activity['shop_name'] = $shop['name'];
        $activity['type'] = unserialize($activity['type']);
        //print_r($activity);
        $this->activity = $activity;

        $row = $db->selectrow('select count(*) as member_nums from sdb_market_activity_edm_queue where is_send=1 and active_id='.$active_id);
        $member_nums = intval($row['member_nums']);
        //$this->actualMemberNums = $member_nums;
        //计算活动人数,对照人数,实际发送人数
        if($activity['control_group']=='yes'){
            if($member_nums != 0 ){
                $lengh=$member_nums/2;
                if ( ceil($lengh) != $lengh) {
                    $lengh=ceil($lengh);
                }
                $this->activeMemberNums = $lengh;
                $this->assessMemberNums = $member_nums-$lengh;
                $sql = 'update sdb_market_activity_edm_queue set is_send=2 where active_id='.$active_id.' and is_send = 1 limit ' . $this->assessMemberNums;
                $db->exec($sql);
            }
        }else{
            $this->activeMemberNums = $member_nums;
            $this->assessMemberNums = 0;
        }

        //更新活动人数
        $row = $db->selectrow('select count(*) as member_nums from sdb_market_activity_edm_queue where active_id='.$active_id);
        $total_nums = intval($row['member_nums']);
        $db->exec('update sdb_market_active  set total_num='.$total_nums.',valid_num='.$this->activeMemberNums.' where active_id='.$active_id);
         
    }


    function assess(){
        $db = kernel::database();
        $finishTime = time() - $this->assessTime;
        $assessList = $db->select('select active_id from sdb_market_active_assess where state="unfinish" order by id');
        if($assessList){
            foreach($assessList as $assess){
                $this->processAsses($assess['active_id']);
            }
        }
        kernel::database()->dbclose();

        return array('status'=>'succ');
    }

    function processAsses($active_id){
        $db = kernel::database();
        $assess = $db->selectrow('select * from sdb_market_active_assess where active_id='.$active_id);
        if($assess['state'] == 'unfinish'){
            if($assess['is_control'] == 0){
                $active_members_res = $this->getAssess($assess['active_id'],0,$assess['exec_time']);
                $active_members_res = serialize($active_members_res);
                $values = array('active_members_res'=>$active_members_res);
            }else if($assess['is_control'] == 1){
                $active_members_res = $this->getAssess($assess['active_id'],1,$assess['exec_time']);
                $active_members_res = serialize($active_members_res);
                $control_members_res = $this->getAssess($assess['active_id'],2,$assess['exec_time']);
                $control_members_res = serialize($control_members_res);
                $values = array('active_members_res'=>$active_members_res,'control_members_res'=>$control_members_res);
            }

            $db->update('sdb_market_active_assess', $values, 'active_id='.$assess['active_id']);
        }
    }


    function getAssess($active_id,$status=0,$exec_time){
        $db = kernel::database();
        $page = 0;
        $page_size = 1000;
        $date_from=$exec_time;
        $date_to=$exec_time+$this->assessTime;
        $acmembers = 0;
        $ordernums = 0;//下单数
        $apaynums = 0;//支付单数
        $afinish_members = 0;//订单完成数
        $aratio = 0;//回头数
        $asale_money = 0;//总金额

        $acmembers = $db->selectrow('select count(*) as total from sdb_market_active_member where status='.$status.' and active_id='.$active_id);
        $acmembers = $acmembers['total'];

        $all_member = array();
        $paid_member = array();
        $finish_member = array();

        while(true){
            $rows = $db->select('select member_id from sdb_market_active_member where status='.$status.' and active_id='.$active_id .' order by member_id limit '.($page*$page_size).','.$page_size);
            if(!$rows)break;
            $mids = array();
            foreach($rows as $row){
                $mids[] = $row['member_id'];
            }

            $sql = 'select member_id,pay_status,status,total_amount from sdb_ecorder_orders where member_id in('.implode(',', $mids).') and createtime >='.$date_from.' and createtime <='.$date_to.' ';
            $orders = $db->select($sql);
            if($orders){

                //$ordernums += count($orders);
                foreach($orders as $order){

                    $all_member[$order['member_id']] = 1;

                    if($order['pay_status'] == '1'){
                        $paid_member[$order['member_id']] = 1;
                    }
                    if($order['status'] == 'finish'){
                        $finish_member[$order['member_id']] = 1;
                    }
                    $asale_money += $order['total_amount'];
                }

            }

            $page++;
        }

        $ordernums = sizeof($all_member);
        $apaynums = sizeof($paid_member);
        $afinish_members = sizeof($finish_member);

        if($acmembers > 0){
            $aratio = (($ordernums/$acmembers))?(round(($ordernums/$acmembers),4))*100:0;
        }else{
            $aratio = 0;
        }

        return array('acmembers'=>$acmembers,'ordernums'=>$ordernums,'apaynums'=>$apaynums,'afinish_members'=>$afinish_members,'aratio'=>$aratio,'asale_money'=>$asale_money);
    }

    //优惠券发送任务
    function sendCoupon($active_id,$shop_id,$coupon_id){
        $db = kernel::database();

        $shopInfo = $db->selectrow('select addon from sdb_ecorder_shop where shop_id="'.$shop_id.'"');
        if(empty($shopInfo['addon']) || $shopInfo['node_type'] != 'taobao'){
            return false;
        }
        $shopInfo['addon'] = unserialize($shopInfo['addon']);
        if(empty($shopInfo['addon']['session'])){
            return false;
        }

        $page = 0;
        $page_size = 100;
        while(true){
            $data = array();
            $rows = $db->select('select uname from sdb_market_activity_edm_queue where active_id='.$active_id .' order by member_id limit '.($page*$page_size).','.$page_size);

            if(empty($rows))break;

            $nick_list = array();
            foreach($rows as $row){
                $nick_list[] = $row['uname'];
            }
             
            $jobarray = array(
            //'order_id'=>$data['order_id'],
            'shop_id'=>$shop_id,
            'coupon_id'=>$coupon_id,
            'buyer_nick'=>$nick_list,
            //'session'=>$shopInfo['addon']['session']
            );
            kernel::single('taocrm_service_queue')->addJob('market_backstage_coupon@send',$jobarray);
            $page++;
        }
        $db->close();

        return true;
    }

    public function countCommission($data){
        $db = kernel::database();
        $smssendobj= kernel::single('market_service_smsinterface');
        $finishTime = time() - $this->assessTime;
        $assessList = $db->select('select id,active_id,msgid from sdb_market_active_assess where state="unfinish" and exec_time <= '.$finishTime.' order by id');
        if($assessList){
            foreach($assessList as $assess){
                $activity = $db->selectrow('select pay_type from sdb_market_active where active_id='.$assess['active_id']);
                if($activity != 'market')continue;

                $msgid = $assess['msgid'];
                $this->processAsses($assess['active_id']);

                $finnalAssess = $db->selectrow('select active_members_res from sdb_market_active_assess where id='.$assess['id']);
                $active_members_res = unserialize($finnalAssess['active_members_res']);
                $countMoney = $active_members_res['ordernums'] * $data['pay_rule'];
                if ($countMoney>0){
                    $result=$smssendobj->ununfreeze($data['sms_config'],$msgid,$countMoney,"commission");
                    if ($result['res']=='succ'){
                        $para=array(
						 	'action'=>'deduct',
						 	'msgid'=>$msgid,
						 	'nums'=>$countMoney,
						 	'remark'=>'扣除佣金',
						 	'status'=>'deductsucc',
						 	'create_time'=>time(),
                        );
                    }else {
                        $para=array(
						 	'action'=>'deduct',
						 	'msgid'=>$msgid,
						 	'nums'=>$countMoney,
						 	'remark'=>'扣除佣金',
						 	'status'=>'deductfail',
						 	'create_time'=>time(),
                        );
                    }
                    $db->insert('sdb_market_sms_op_record', $para);
                }


                //解冻
                $row = $db->selectrow('select nums from sdb_market_sms_op_record where msgid="'.$msgid.'"');
                $unblockMoney=(intval($row['nums'])-$countMoney);
                //$unblockMoney = 10;
                if ($unblockMoney>0){
                    $res=$smssendobj->ununfreeze($data['sms_config'],$msgid,$unblockMoney,"unlock");
                    if ($res['res']=="succ"){
                        $para=array(
							 'action'=>'unfreeze',
							 'msgid'=>$msgid,
							 'nums'=>$unblockMoney,
							 'remark'=>'短信解冻',
							 'status'=>'freezesucc',
							 'create_time'=>time(),
                        );
                    }else {
                        $para=array(
							 'action'=>'unfreeze',
							 'msgid'=>$msgid,
							 'nums'=>$unblockMoney,
							 'remark'=>'短信解冻',
							 'status'=>'freezefail',
							 'create_time'=>time(),
                        );
                    }
                    $db->insert('sdb_market_sms_op_record', $para);
                }
                $db->update('sdb_market_active_assess',array('state'=>'finish','end_time'=>time()),'id='.$assess['id']);
            }
        }
        $db->dbclose();

        return array('status'=>'succ');
    }

}

