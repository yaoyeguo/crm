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

class taocrm_messenger_smsTemplate {
	
	public static function convertTemplateToContent($params, $smsTemplate) {
    	$message = str_replace(
    		array('<{$uname}>', '<{$coupon}>', '<{$shop}>'),
    		array($params['uname'], $params['coupon'], $params['name']), 
    		$smsTemplate
    	);
    	return $message;		
	}
}