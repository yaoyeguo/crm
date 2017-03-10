<?php
class taocrm_command_sms extends base_shell_prototype{
    
    public $command_sendAll = 'Send Sms';
	public static $sumProcess = 0;
	public static $maxProcess = 30;
    
	/**
	 * 
	 * 发送所有的订单流程短信 
	 */
    public function command_sendAll() {
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
//        			下单未付款订单
        			$value['orderList'] = $ecorder->model('orders')->getList('*', array(
        				'pay_status' => 0,
        				'shop_id' => $value['shop_id'],
        				'create_time|sthan' => time() - intval($value['rule_time'])*3600,
        				'create_time|bthan' => 0,
        			));
        			
        			$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
        			break;
        		case 2:
//        			付款后订单`
					$value['orderList'] = $ecorder->model('orders')->getList('*', array(
						'pay_status' => 1,
						'shop_id' => $value['shop_id'],
        				'pay_time|sthan' => time() - intval($value['rule_time'])*3600,
        				'pay_time|bthan' => 0,						
					));
					
					$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
        			break;
        		case 3:
//        			已发货订单
					$value['orderList'] = $ecorder->model('orders')->getList('*', array(
						'pay_status' => 1,
						'shop_id' => $value['shop_id'],
        				'delivery_time|sthan' => time() - intval($value['rule_time'])*3600,
        				'delivery_time|bthan' => 0,			
					));
					
					$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
        			break;
        		case 4:
//        			购买成功订单
					$value['orderList'] = $ecorder->model('orders')->getList('*', array(
						'pay_status' => 1,
						'shop_id' => $value['shop_id'],
        				'finish_time|sthan' => time() - intval($value['rule_time'])*3600,
        				'finish_time|bthan' => 0,
					));
					
					$value['smsTemplate'] = $smsTemplates[$value['theme_id']];
        			break;
        	}
        }
        
        if (function_exists('pcntl_fork')) {
	        foreach ($rulesList as $rule) {
	        	foreach ($rule['orderList'] as $value) {
	        		$this->_process($value, $rule['smsTemplate']);
	        	}
	        }        	
        }
        else {
	        foreach ($rulesList as $rule) {
	        	foreach ($rule['orderList'] as $value) {
	        		$this->_sendOne($value, $rule['smsTemplate']);
	        	}
	        }         	
        }
    }
    
    /**
     * 
     * 发送短信过程中使用多进程
     * 主进程作为调度器，子进程负责短信发送
     * 
     * @order array 一条订单
     * @smsTemplate string 一个短信模板
     */
    protected function _process($order, $smsTemplate) {
        if (function_exists('pcntl_fork')) {
	        $pid = pcntl_fork();
	        if ($pid == -1) {
	                die("could not fork\n");
	        }
	        elseif ($pid) {
	        	self::$sumProcess++;
                echo str_pad(">>> [Process/Sum ".self::$sumProcess."/".self::$maxProcess."]", 30);
                echo "Send to {$order['ship_mobile']}\n";
                if (self::$sumProcess >= self::$maxProcess) {
					pcntl_wait($status);
					self::$sumProcess--;
                }
                return $pid;
	        }
	        else {
				self::_sendOne($order, $smsTemplate);
				exit();
	        }
    	}    	
    }
    
    private function _sendOne($order, $smsTemplate) {
//		check if send before
		if (!$hasSend && $order['ship_mobile']) {
//			send sms
			$smsObj = new taocrm_messenger_sms();
			$smsObj->send($order['ship_mobile'], $this->_buildMessage($order, $smsTemplate));
//			write history
			app::get('taocrm')->model('sms_sent')->insert();
		} 
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
    	$message = str_replace(array('<{$uname}>', '<{$coupon}>', '<{$shop}>'), array($member['account']['uname'], '', $shop['name']), $smsTemplate);
    	return $message;
    }
	
    public $command_test = 'test';
    public function command_test() {
    	$order = app::get('ecorder')->model('orders')->dump(array('order_id' => 1));
    	$template = app::get('taocrm')->model('message_themes')->dump(array('theme_id' => 1));
    	$smsTemplate = $template['theme_content'];
    	$content = $this->_buildMessage($order, $smsTemplate);
    	$smsObj = new taocrm_messenger_sms();
    	$smsObj->send('13636348683', $content);
    }
}