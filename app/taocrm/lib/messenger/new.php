<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */

class taocrm_messenger_new {
	
	public function sendOrderSms() {
		
		if ($info = sms_utils::check_account_status()) {
		    $taocrm = app::get('taocrm');
	     
	        $smsTemplatesList = $taocrm->model('message_themes')->getList('*');
	        $smsTemplates = array();
	        foreach ($smsTemplatesList as $value) {
	        	$smsTemplates[$value['theme_id']] = $value;
	        }
	                
	        $ruleTypeList = $taocrm->model('sms_rule_types')->getList('*', array('type_status' => 'enable'));
	        $ruleTypes = array();
	        foreach ($ruleTypeList as $value) {
	        	$ruleTypes[] = $value['type_id'];
	        }        
	        
	        $rulesList = $taocrm->model('sms_rules')->getList('*', array(
	        	'type_id|in' => $ruleTypes,
	        	'rule_status' => 1
	        ));
	        
	        $ecorder = app::get('ecorder');
	        foreach ($rulesList as &$value) {
	        	switch ($value['type_id']) {
	        		case 1:
//             			下单未付款订单
	        			$value['orderList'] = $ecorder->model('orders')->getList('*', array(
	        				'pay_status' => '0',
	        				'shop_id' => $value['shop_id'],
	        				'status' => 'active',
	        				'createtime|sthan' => time() - intval($value['rule_time'])*3600,
	        				'createtime|bthan' => time() - (intval($value['rule_time']) + 24)*3600,
	        			));
	        			
	        			$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
	        			break;
	        		case 2:
//	        			付款后订单`
						$value['orderList'] = $ecorder->model('orders')->getList('*', array(
							'pay_status' => '1',
							'shop_id' => $value['shop_id'],
							'status' => 'active',
	        				'pay_time|sthan' => time() - intval($value['rule_time'])*3600,
	        				'pay_time|bthan' => time() - (intval($value['rule_time']) + 24)*3600,					
						));
						
						$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
	        			break;
	        		case 3:
//	        			已发货订单
						$value['orderList'] = $ecorder->model('orders')->getList('*', array(
							'pay_status' => '1',
							'shop_id' => $value['shop_id'],
							'status' => 'active',
	        				'delivery_time|sthan' => time() - intval($value['rule_time'])*3600,
	        				'delivery_time|bthan' => time() - (intval($value['rule_time']) + 24)*3600,		
						));
						
						$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
	        			break;
	        		case 4:
//	        			购买成功订单
						$value['orderList'] = $ecorder->model('orders')->getList('*', array(
							'pay_status' => '1',
							'shop_id' => $value['shop_id'],
	        				'finish_time|sthan' => time() - intval($value['rule_time'])*3600,
	        				'finish_time|bthan' => time() - (intval($value['rule_time']) + 24)*3600,
						));
						
						$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
	        			break;
	        	}
	        }
	      	        
	        $contents = array();
	        foreach ($rulesList as $rule) {
	        	foreach ($rule['orderList'] as $value) {
	        		$data = array(
		        		'order_id' => $value['order_id'],
		        		'type_id' => $rule['type_id'],	        		
	        		);
	        		if (!app::get('taocrm')->model('sms_order_log')->dump($data)) {
		        		$content = array();
		        		$content['phones'] = array($value['ship_mobile']);
		        		$content['content'] = $this->_buildMessage($value, $rule['smsTemplate']);
		        		$contents[] = $content;
		        		$data['sendTime'] = time();
		        		app::get('taocrm')->model('sms_order_log')->insert($data);
	        		}
	        	}
	        }
	        
	        if ($contents) {
	        	taocrm_sms::sendMany($contents);
	        }
		}
		return null;
	}
    
    /**
     * 
     * 把短信模板转变为具体的短信内容
     * @order array 一条订单
     * @smsTemplate string 一个短信模板
     */
    private function _buildMessage($order, $smsTemplate) {
    	$member = app::get('taocrm')->model('members')->dump(array('member_id' => $order['member_id']));
    	$shop = app::get('ecorder')->model('shop')->dump(array('shop_id' => $order['shop_id']));
    	$message = str_replace(array('<{$uname}>', '<{$coupon}>', '<{$shop}>'), array($member['account']['uname'], '', $shop['name']), $smsTemplate['theme_content']);
    	return $message;
    }    
}