<?php
class market_backstage_activity{


    var $activity;
    var $activeMemberNums = 0;
    var $assessMemberNums = 0;
    //var $actualMemberNums = 0;
    var $smsConfig;
    var $assessTime = 1296000;
    /**
     *
     * 根据搜索条件生成队列表,总数不超过活动总人数，冻结短信和短信帐户检测在前台做掉
     *
     * 	 $jobarray = array(
     'active_id'=>$active_id,
     'msgid'=>$msgid,
     );
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
            $this->processSmsTemplateGroup($data['active_id']);
            $this->processAsses($data['active_id']);

            if (in_array('coupon', $this->activity["type"])){//发送优惠券
                $this->sendCoupon($data['active_id'],$this->activity['shop_id'],$this->activity['coupon_id']);
            }

            if (in_array('sms', $this->activity["type"])){
                $this->sendSms($data['active_id'],$data['msgid']);
            }
        }

        kernel::database()->dbclose();

        return array('status'=>'succ');

    }

    protected function processSmsTemplateGroup($active_id)
    {
        $db = kernel::database();
        if ($this->activity['template_id_b']) {
            //未开启活动对照组
            if ($this->activity['control_group']=='no'){
                $this->ProcessSmsTemplateGroupSql($active_id, 3, $this->activity['template_id_b']);
                $this->ProcessSmsTemplateGroupSql($active_id, 1, $this->activity['template_id']);
            }
            elseif ($this->activity['control_group']=='yes') { //开启活动对照组
                $this->ProcessSmsTemplateGroupSql($active_id, 3, $this->activity['template_id_b']);
            }
        }
    }
    protected function ProcessSmsTemplateGroupSql($active_id, $status, $template_id)
    {
        $db = kernel::database();
        $updateSql = "UPDATE
                          sdb_market_active_member A, sdb_market_activity_m_queue B
                      SET
                        A.status = {$status}
                      WHERE
                        A.active_id = B.active_id
                      AND
                        A.member_id = B.member_id
                      AND 
                        B.is_send = 1
                      AND
                        B.template_id = {$template_id}
                      AND
                        A.active_id = {$active_id}";
        $db->exec($updateSql);
    }

    function processControlGroup($active_id,$msgid){
        $db = kernel::database();
        //清楚重复数据
        $sql = 'delete from sdb_market_active_member where active_id='.$active_id;
        $db->exec($sql);

        if ($this->activity['control_group']=='no'){
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,0 from sdb_market_activity_m_queue where active_id='.$active_id.' and is_send=1';
            $db->exec($sql);
            $is_control = 0;
        }elseif($this->activity['control_group']=='yes') {//开启对照组
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,1 from sdb_market_activity_m_queue where active_id='.$active_id.' and is_send=1';
            $db->exec($sql);
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,2 from sdb_market_activity_m_queue where active_id='.$active_id.' and is_send=2';
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

        //$this->processAsses($active_id);
    }

    function sendSms($active_id,$msgid){
        $db = kernel::database();
        $this->db = $db;

        //积分兑换完整地址
        $url = kernel::base_url(1);
        $url = $url . '/index.php/taocrm/default/index/app/site';
        //将积分兑换地址变为短地址
        $SinaObj = kernel::single('market_shorturl');
        $shorturl = $SinaObj->shortenSinaUrl($url);

        $sms_array=array();
        $sms_array['template_id']=$this->activity['template_id'];
        $sms_array['template_id_b']=$this->activity['template_id_b'];
        $sms_array['active_name']=$this->activity['active_name'];
        $sms_array['active_id']=$active_id;
        $sms_array['sms_type']='active';
        $sms_array['create_time']=time();
        $sms_array['plan_send_time']=$this->activity['sent_time'];
        $sms_array['total_num']=$this->activeMemberNums;
        $sms_array['shop_id']=$this->activity['shop_id'];
        $sms_array['success_num']=0;//shop_id
        $sms_array['is_send']='unsend';
        $db->insert('sdb_market_sms', $sms_array);
        $sms_id = $db->lastinsertid();

        //        $sms_templates = $db->selectrow('select content from sdb_market_sms_templates where template_id='.$this->activity['template_id']);

        //        $unsubscribe = $db->selectrow('select unsubscribe,templete from sdb_market_active where active_id='.$active_id.' ');


        $templateArray = array(
        $this->activity['template_id'] => array('templete_title' => $this->activity['templete_title'], 'templete' => $this->activity['templete']),
        $this->activity['template_id_b'] => array('templete_title' => $this->activity['templete_title_b'], 'templete' => $this->activity['templete_b'])
        );

        if($this->activity['unsubscribe'] == 1){
            //            $sms_templates['content'] = $unsubscribe['templete'].' 退订回N';
            $templateArray[$this->activity['template_id']]['templete'] .= ' 退订回N';
            $templateArray[$this->activity['template_id_b']]['templete'] .= ' 退订回N';
        }

        $page = 0;
        $page_size = 200;
        $n = 1;
        //$this->setActivityMQueueTemplete($active_id);
        while(true){
            $data = array();
            $rows = $db->select('select member_id,uname,mobile,template_id from sdb_market_activity_m_queue where is_send=1 and  active_id='.$active_id .' order by member_id limit '.($page*$page_size).','.$page_size);

            if(empty($rows))break;
             
            $sms_list = array();
            foreach($rows as $row){
                $templete = $templateArray[$row['template_id']]['templete'];
                //模板无法短信内容，只发送短信A模板内容
                if ($templete == '') { 
                    $templete = $this->activity['templete'];
                }
                $msgContent = str_replace(array('<{用户名}>','<{店铺}>','<{积分兑换}>'), array($row['uname'],$this->activity['shop_name'],$shorturl),$templete);
                $sms_list[] = array('member_id'=>$row['member_id'],'phones'=>$row['mobile'],'content'=>$msgContent);
            }
            $data = array(
            //'sms_config'=>$this->smsConfig,
             'sms_batch_no'=> $sms_id."_".$n,
             'plan_send_time'=>$this->activity['sent_time'],
             'shop_id'=>$this->activity['shop_id'],
             'active_id'=>$active_id,
             'sms_id'=>$sms_id,
             'batch_no'=>$msgid,
             'sms_list'=>$sms_list,
            );
            //var_export($data);exit;
            //ilog(var_export($data,true));
            kernel::single('taocrm_service_queue')->addJob('market_backstage_sms@send',$data);
            $page++;
            $n++;
        }
    }

    function getActivity($active_id){
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_active where active_id='.$active_id);
        if(!$activity)return false;
        $shop = $db->selectrow('select name from sdb_ecorder_shop where shop_id="'.$activity['shop_id'].'"');
        $activity['shop_name'] = $shop['name'];
        $activity['type']      = unserialize($activity['type']);

        return $activity;
    }

    function processMember($active_id){
        $db = kernel::database();
        $activity = $this->getActivity($active_id);
        if(!$activity)return false;
        $this->activity = $activity;

        $row = $db->selectrow('select count(*) as member_nums from sdb_market_activity_m_queue where is_send=1 and active_id='.$active_id);
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
                $sql = 'update sdb_market_activity_m_queue set is_send=2 where active_id='.$active_id.' and is_send = 1 limit ' . $this->assessMemberNums;
                $db->exec($sql);
            }
        }else{
            $this->activeMemberNums = $member_nums;
            $this->assessMemberNums = 0;
        }

        //如果开启短信对照
        if ($activity['template_id_b']) {
            //B短信起始位置
            $offset_b = ceil($this->activeMemberNums / 2) + $this->assessMemberNums;
            $lastSql = 'select * from sdb_market_activity_m_queue where  active_id = ' .$active_id . ' AND is_send = 1 limit ' . ($offset_b - 1) . ' , 1';
            $lastRecord = $db->select($lastSql);
            //获得当前记录的队列ID号
            $lastQueueId = $lastRecord[0]['queue_id'];
            $updateSql = 'update sdb_market_activity_m_queue set template_id = ' . intval($activity['template_id_b'])
            . ' WHERE active_id = ' .$active_id . ' AND is_send = 1 AND  queue_id > ' . $lastQueueId;
            $db->exec($updateSql);
            //            $tmpSql = 'select template_id, count(template_id) as _count from sdb_market_activity_m_queue where is_send = 1 and active_id='.$active_id . ' GROUP BY template_id';
            //            $row = $db->select($tmpSql);
            //            //是否已经更新短信对照，防止代码重新执行。
            //            if (1 >= count($row)) {
            //                $row = $db->selectrow('select count(*) as member_nums from sdb_market_activity_m_queue where active_id='.$active_id ." AND is_send = 1");
            //                $activeNum = intval($row['member_nums']);
            //                $contrastNum = ceil($activeNum / 2);
            //                $updateSql = 'update sdb_market_activity_m_queue set template_id = ' . intval($activity['template_id_b'])
            //                              . ' WHERE active_id = ' .$active_id . ' AND is_send = 1 limit ' . $contrastNum;
            //                $db->exec($updateSql);
            //            }
        }

        //更新活动人数
        $row = $db->selectrow('select count(*) as member_nums from sdb_market_activity_m_queue where active_id='.$active_id);
        $total_nums = intval($row['member_nums']);
        $db->exec('update sdb_market_active  set total_num='.$total_nums.',valid_num='.$this->activeMemberNums.' where active_id='.$active_id);
         
    }


    function assess($id=0){
        $db = kernel::database();
        $finishTime = time() - $this->assessTime;
        $id = floatval($id);
        if($id == 0){
            $sql = 'select active_id from sdb_market_active_assess where state="unfinish" order by id';
        }else{
            $sql = "select active_id from sdb_market_active_assess where id={$id}";
        }
        $assessList = $db->select($sql);
        if($assessList){
            foreach($assessList as $assess){
                $this->processAsses($assess['active_id']);
            }
        }
        kernel::database()->dbclose();

        return array('status'=>'succ');
    }

    function processAsses($active_id){
        if (!$this->activity){
            $activity = $this->getActivity($active_id);
            $this->activity = $activity;
        }

        if(!$this->activity)return false;
         

        $db = kernel::database();
        $assess = $db->selectrow('select * from sdb_market_active_assess where active_id='.$active_id);
        if($assess['state'] == 'unfinish'){
            $active_members_res_b = 0;
            if($assess['is_control'] == 0){
                //$active_members_res = $this->getAssess($assess['active_id'],0,$assess['exec_time']);
                //根据店铺来过滤营销活动评估(新)
                if ($this->activity['template_id_b']) {
                    $active_members_res = $this->getAssess($assess['active_id'],1,$assess['exec_time'],$assess['shop_id']);
                    $active_members_res = serialize($active_members_res);

                    $active_members_res_b = $this->getAssess($assess['active_id'],3,$assess['exec_time'],$assess['shop_id']);
                    $active_members_res_b = serialize($active_members_res_b);

                    $values = array('active_members_res'=>$active_members_res, 'active_members_res_b' => $active_members_res_b);
                }
                else {
                    $active_members_res = $this->getAssess($assess['active_id'],0,$assess['exec_time'],$assess['shop_id']);
                    $active_members_res = serialize($active_members_res);
                    $values = array('active_members_res'=>$active_members_res);
                }
            }else if($assess['is_control'] == 1){
                //$active_members_res = $this->getAssess($assess['active_id'],1,$assess['exec_time']);
                //根据店铺来过滤营销活动评估(新)
                if ($this->activity['template_id_b']) {
                    $active_members_res = $this->getAssess($assess['active_id'],1,$assess['exec_time'],$assess['shop_id']);
                    $active_members_res = serialize($active_members_res);

                    $active_members_res_b = $this->getAssess($assess['active_id'],3,$assess['exec_time'],$assess['shop_id']);
                    $active_members_res_b = serialize($active_members_res_b);
                }
                else {
                    $active_members_res = $this->getAssess($assess['active_id'],1, $assess['exec_time'],$assess['shop_id']);
                    $active_members_res = serialize($active_members_res);
                }
                //$control_members_res = $this->getAssess($assess['active_id'],2,$assess['exec_time']);
                //根据店铺来过滤营销活动评估(新)
                $control_members_res = $this->getAssess($assess['active_id'],2,$assess['exec_time'],$assess['shop_id']);
                $control_members_res = serialize($control_members_res);
                $values = array('active_members_res'=>$active_members_res,'control_members_res'=>$control_members_res, 'active_members_res_b' => $active_members_res_b);
            }
            $db->update('sdb_market_active_assess', $values, 'active_id='.$assess['active_id']);
        }
    }


    function getAssess($active_id,$status=0,$exec_time,$shop_id){
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



        $sql = 'select count( DISTINCT a.member_id ) AS total,sum(b.total_amount) as sale_amount from sdb_market_active_member as a
            inner join sdb_ecorder_orders as b
            on a.member_id=b.member_id 
            where b.shop_id = "'.$shop_id.'" and a.status='.$status.' and a.active_id='.$active_id .' and b.createtime >='.$date_from.' and b.createtime <='.$date_to;
        $row = $db->selectrow($sql);
        $ordernums = $row['total'] ? $row['total'] : 0;
        $asale_money = $row['sale_amount'] ? $row['sale_amount'] : 0;

        $sql = 'select count( DISTINCT a.member_id ) AS total from sdb_market_active_member as a
            inner join sdb_ecorder_orders as b
            on a.member_id=b.member_id 
            where b.shop_id = "'.$shop_id.'" and a.status='.$status.' and a.active_id='.$active_id .' and b.createtime >='.$date_from.' and b.createtime <='.$date_to .' and b.pay_status="1"';
        $row = $db->selectrow($sql);
        $apaynums = $row['total'] ? $row['total'] : 0;

        $sql = 'select count( DISTINCT a.member_id ) AS total from sdb_market_active_member as a
            inner join sdb_ecorder_orders as b
            on a.member_id=b.member_id 
            where b.shop_id = "'.$shop_id.'" and a.status='.$status.' and a.active_id='.$active_id .' and b.createtime >='.$date_from.' and b.createtime <='.$date_to .' and b.status="finish"';
        $row = $db->selectrow($sql);
        $afinish_members = $row['total'] ? $row['total'] : 0;

        /*
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

         //$sql = 'select member_id,pay_status,status,total_amount from sdb_ecorder_orders where member_id in('.implode(',', $mids).') and createtime >='.$date_from.' and createtime <='.$date_to.' ';
         //根据店铺来获取数据
         $sql = 'select member_id,pay_status,status,total_amount from sdb_ecorder_orders where member_id in('.implode(',', $mids).') and createtime >='.$date_from.' and createtime <='.$date_to.' and shop_id="'.$shop_id.'" ';

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
         */

         
         
         

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

        $shopInfo = $db->selectrow('select node_type,addon from sdb_ecorder_shop where shop_id="'.$shop_id.'"');
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
            $rows = $db->select('select uname from sdb_market_activity_m_queue where  is_send=1  and active_id='.$active_id .' order by member_id limit '.($page*$page_size).','.$page_size);

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
        //$db->dbclose();

        return true;
    }

    public function countCommission($data){
        $db = kernel::database();
        $smssendobj= kernel::single('market_service_smsinterface');
        $finishTime = time() - $this->assessTime;
        //$finishTime = 1353081600- $this->assessTime;
        $assessList = $db->select('select id,active_id,msgid from sdb_market_active_assess where state="unfinish" and exec_time <= '.$finishTime.' order by id');
        if($assessList){
            foreach($assessList as $assess){
                $activity = $db->selectrow('select active_id from sdb_market_active where pay_type="market" and active_id='.$assess['active_id']);
                if(!$activity)continue;

                $msgid = $assess['msgid'];
                $this->processAsses($assess['active_id']);

                $finnalAssess = $db->selectrow('select active_members_res from sdb_market_active_assess where id='.$assess['id']);
                $active_members_res = unserialize($finnalAssess['active_members_res']);
                $countMoney = $active_members_res['ordernums'] * $data['market_pay_rule'];
                if ($countMoney>0){
                    $cur_time = time();
                    $para=array(
                            'active_id'=>$assess['active_id'],
						 	'actual_pay'=>0,
						 	'plan_pay'=>$countMoney,
						 	'create_time'=>$cur_time,
						 	'update_time'=> $cur_time,
						 	'status'=>'unpay',
                    );
                    if($db->insert('sdb_market_sms_deduction_record', $para)){
                        $db->update('sdb_market_active_assess',array('state'=>'finish','end_time'=>time()),'id='.$assess['id']);
                    }

                     
                }else{
                    $db->update('sdb_market_active_assess',array('state'=>'finish','end_time'=>time()),'id='.$assess['id']);
                }

            }
        }
        $db->dbclose();

        return array('status'=>'succ');
    }

    //扣除短信佣金
    public function deductionRecord()
    {
        return false;
        
        $smssendobj=kernel::single('market_service_smsinterface');
        $send_info=$smssendobj->get_usersms_info();//get_usersms_info
        if ($send_info['res']=='succ'){
            $month_residual=$send_info['info']['month_residual']; //短信总条数 all_residual
            $blocknums=intval($send_info['info']['block_num']);//冻结短信条数
        }else {
            return array('status'=>'fail','errmsg'=>'获取短信详细信息接口失败');
        }
        $overcount = $month_residual- $blocknums;
        //$overcount = 201;
        if($overcount <= 0 ){
            return array('status'=>'succ');
        }

        $db = kernel::database();
        $rows = $db->select('select * from sdb_market_sms_deduction_record where status in("unpay","paypart")');
        if($rows){
            $cur_time = time();
            foreach($rows as $row){
                $pay_money = $row['plan_pay'] - $row['actual_pay'];
                if($overcount >=$pay_money){
                    $status = 'paysucc';
                }else{
                    $status = 'paypart';
                    $pay_money = $overcount;
                }

                if($pay_money > 0){
                    $msgid = date('ymdHis').rand(111,999);//对账用唯一识别码
                    $result = $smssendobj->payment($msgid,$pay_money,0, 4, '营销超市');
                    kernel::ilog('payment:');
                    kernel::ilog(var_export($result,true));
                     
                    //$result = $smssendobj->payment($msgid,$pay_money);
                    //$res['res']  = 'succ';
                    if($result['res'] == 'succ'){
                        $db->exec('update sdb_market_sms_deduction_record set status="'.$status.'" , update_time='.$cur_time.',actual_pay=actual_pay+'.$pay_money .' where id='.$row['id']);
                        $para=array(
						 	'record_id'=>$row['id'],
						 	'pay_nums'=>$pay_money,
						 	'create_time'=>$cur_time,
                        );
                        $db->insert('sdb_market_sms_deduction_record_item', $para);
                    }
                }else{
                    $db->update('sdb_market_sms_deduction_record',array('status'=>'paysucc','update_time'=>$cur_time),'id='.$row['id']);
                }
            }
        }
        $db->dbclose();

        return array('status'=>'succ');
    }

    function afreshSendSms($active_id=0,$msgid='',$sms_id=0,$n=2)
    {
        if($active_id == 0) return false;
    
        $db = kernel::database();
        $this->db = $db;

        //积分兑换完整地址
        $url = kernel::base_url(1);
        $url = $url . '/index.php/taocrm/default/index/app/site';
        //将积分兑换地址变为短地址
        $SinaObj = kernel::single('market_shorturl');
        $shorturl = $SinaObj->shortenSinaUrl($url);

        $activity = $this->getActivity($active_id);
        if(!$activity)return false;
        $this->activity = $activity;


        //        $sms_templates = $db->selectrow('select content from sdb_market_sms_templates where template_id='.$this->activity['template_id']);

        //        $unsubscribe = $db->selectrow('select unsubscribe,templete from sdb_market_active where active_id='.$active_id.' ');


        $templateArray = array(
        $this->activity['template_id'] => array('templete_title' => $this->activity['templete_title'], 'templete' => $this->activity['templete']),
        $this->activity['template_id_b'] => array('templete_title' => $this->activity['templete_title_b'], 'templete' => $this->activity['templete_b'])
        );

        if($this->activity['unsubscribe'] == 1){
            //            $sms_templates['content'] = $unsubscribe['templete'].' 退订回N';
            $templateArray[$this->activity['template_id']]['templete'] .= ' 退订回N';
            $templateArray[$this->activity['template_id_b']]['templete'] .= ' 退订回N';
        }

        $page = 0;
        $page_size = 200;
      
        //$this->setActivityMQueueTemplete($active_id);
        while(true){
            $data = array();
            $rows = $db->select('select member_id,uname,mobile,template_id from sdb_market_activity_m_queue where is_send=1 and is_send_finish = 0 and  active_id='.$active_id .' order by member_id limit '.($page*$page_size).','.$page_size);

            if(empty($rows))break;
             
            $sms_list = array();
            foreach($rows as $row){
                $templete = $templateArray[$row['template_id']]['templete'];
                //模板无法短信内容，只发送短信A模板内容
                if ($templete == '') {
                    $templete = $this->activity['templete'];
                }
                $msgContent = str_replace(array('<{用户名}>','<{店铺}>','<{积分兑换}>'), array($row['uname'],$this->activity['shop_name'],$shorturl),$templete);
                $sms_list[] = array('member_id'=>$row['member_id'],'phones'=>$row['mobile'],'content'=>$msgContent);
            }
            $data = array(
            //'sms_config'=>$this->smsConfig,
             'sms_batch_no'=> $sms_id."_".$n,
             'plan_send_time'=>$this->activity['sent_time'],
             'shop_id'=>$this->activity['shop_id'],
             'active_id'=>$active_id,
             'sms_id'=>$sms_id,
             'batch_no'=>$msgid,
             'sms_list'=>$sms_list,
            );
            echo $page."\n";
            //var_export($data);exit;
            //ilog(var_export($data,true));
            kernel::single('taocrm_service_queue')->addJob('market_backstage_sms@send',$data);
            $page++;
            $n++;
        }
    }

}

