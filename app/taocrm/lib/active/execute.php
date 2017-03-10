<?php
class taocrm_active_execute{
    function run(&$cursor_id,$params){
        $activeObj = &app::get('taocrm')->model('active');
        $acMemObj = &app::get('taocrm')->model('active_member');
        $active = $activeObj->dump($params['active_id']);
//      if the queue is complete return 0,
//		when return 1, the queue will be executing next time.
        $status = 0;        
        if($active && $active['status']=='0'){
            $members = $acMemObj->getList('member_id',array('active_id'=>$active['active_id'],'status'=>'0'));
            $smsSentObj = &app::get('taocrm')->model('sms_sent');
            $memberObj = &app::get('taocrm')->model('members');
            
            if($members && count($members)>0) {
                $couponSentObj = &app::get('taocrm')->model('coupon_sent');
                $couponRpcObj = kernel::single('taocrm_rpc_request_coupon');                
                $smsRpcObj = kernel::single('taocrm_messenger_sms');

                $i = 0;
                foreach($members as $member){
                    $curMember = $memberObj->dump($member['member_id'],'uname,mobile');
                    if($active['coupon_id']){
                        $couponSend['coupon_id'] = $active['coupon_id'];
                        $couponSend['relate_id'] = $active['active_id'];
                        $couponSend['send_type'] = '0';
                        $couponSend['send_time'] = time();
                        $couponSend['member_id'] = $member['member_id'];
                        $couponSentObj->insert($couponSend);
                        $couponRpcObj->send($couponSend);
                        unset($couponSend);
                    }
                    
                    if($active['theme_id'] && $curMember['contact']['phone']['mobile']){
                    	$smsSend = array();
                        $smsSend['theme_id'] = $active['theme_id'];
                        $smsSend['relate_id'] = $active['active_id'];
                        $smsSend['sms_type'] = '0';
                        $smsSend['send_time'] = time();
                        $smsSend['member_id'] = $member['member_id'];
                        $smsSend['mobile'] = $curMember['contact']['phone']['mobile'];
                        $smsSend['send_status'] = '0';
                        $smsSentObj->insert($smsSend);
                        unset($smsSend, $curMember);
                    }

                    $acMemObj->update(array('status'=>'1'),array('member_id'=>$member['member_id'],'active_id'=>$active['active_id']));

                    if( ++$i == 50 ){
                        $status = 1;
                        break;
                    }
                }
            }
            
//          send message got from table sms_sent
			$queryConditions = array(
				'relate_id' => $active['active_id'],
				'sms_type' => '0',
				'send_status' => '0'
			);
			$smsSentList = $smsSentObj->getList('*', $queryConditions);
			
			if ($smsSentList) {
//				coupon信息
				$couponContent = '';
				if ($active['coupon_id']) {
					$couponConditions = array('coupon_id' => $active['coupon_id']);
					$couponRs = &app::get('taocrm')->model('coupons')->dump($couponConditions);
					$couponContent = $couponRs['coupon_name'];
				}
				
//				shop信息
				$shopConditions = array('shop_id' => $active['shop_id']);
				$shopRs = &app::get('ecorder')->model('shop')->dump($shopConditions);
				$shopName = $shopRs['name'];
				
//				短信模板信息
				$templateConditions = array('theme_id' => $active['theme_id']);
				$templateRs = &app::get('taocrm')->model('message_themes')->dump($templateConditions);
				$contentTemplate = $templateRs['theme_content'];
				$contentTemplate = str_replace(array('<{$coupon}>', '<{$shop}>'), array($couponContent, $shopName), $contentTemplate);
				
				$msgContents = array();
				foreach ($smsSentList as $value) {
					$memberConditions = array('member_id' => $value['member_id']);
					$member = $memberObj->dump($memberConditions);
					$msgContent = array(
						'phones' => array($value['mobile']),
						'content' => str_replace('<{$uname}>', $member['account']['uname'], $contentTemplate),
					);
					$updateValue = array('send_status' => 1, 'send_time' => time());
					$updateConditions = array('sent_id' => $value['sent_id']);
					$smsSentObj->update($updateValue, $updateConditions);
					$msgContents[] = $msgContent;
				}
				taocrm_sms::sendMany($msgContents);
			}
			
            if (!$status) {
            	$activeObj->update(array('status'=>'1'),array('active_id'=>$active['active_id']));	
            }
        }
        return $status;
    }
}