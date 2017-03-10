<?php
class taocrm_messenger_task{
    public function run(){
        $smsSentObj = &app::get('taocrm')->model('sms_sent');
        $templateObj = &app::get('taocrm')->model('message_themes');
        $couponsObj = &app::get('taocrm')->model('coupons');
        $shopObj = &app::get('ecorder')->model('shop');
        $membersObj = &app::get('taocrm')->model('members');
        $rulesObj = &app::get('taocrm')->model('coupon_send_rules');
        
        $shopRes = $shopObj->getList('*');
        $shopList = array();
        foreach ($shopRes as $shop) {
        	$shopList[$shop['shop_id']] = $shop['name'];
        }
        unset($shopRes);
        
        $templateRes = $templateObj->getList('*');
        $templateList = array();
        foreach ($templateRes as $template) {
        	$templateList[$template['theme_id']] = $template['theme_content'];
        }
        unset($templateRes);
        
        $couponsRes = $couponsObj->getList('*');
        $couponsList = array();
        foreach ($couponsRes as $coupon) {
        	$couponsList[$coupon['coupon_id']] = $coupon['coupon_name'];
        }
        unset($couponsRes);
        
        $rulesRes = $rulesObj->getList('*', array('status' => '1'));
        $rulesList = array();
        foreach ($rulesRes as $rule) {
        	$rulesList[$rule['rule_id']] = array(
        		'coupon' => $rule['coupon_id'],
        		'shop' => $rule['shop_id'],
        	);
        }
        unset($rulesRes);
        
        $sentList = $smsSentObj->getList('*', array('send_status'=>'0', 'sms_type' => '1'));
        $msgContents = array();
        foreach($sentList as $sent){
            if($sent['theme_id'] && $sent['mobile']) {
            	
                $member = $membersObj->dump($sent['member_id']);
                $memberName = $member['account']['uname'];
                $tmpRule = $rulesList[$sent['relate_id']];
            	$msgContent = array(
            		'phones' => array($sent['mobile']),
            		'content' => str_replace(array('<{$uname}>', '<{$coupon}>', '<{$shop}>'), array($memberName, $couponsList[$tmpRule['coupon']], $shopList[$tmpRule['shop']]), $templateList[$sent['theme_id']]),
            	);
            	$msgContents[] = $msgContent;
                $smsSentObj->update(array('send_status'=>'1'),array('sent_id'=>$sent['sent_id']));
                unset($sent);
            }
        }
        taocrm_sms::sendMany($msgContents);
        unset($sentList);
        return true;
    }
}