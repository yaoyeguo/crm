<?php
class taocrm_sms {
	
	/**
	 * @param array $content
	 * eg. 
	 * array(
	 *     array(
	 *         'phones' => array('13636348683', '13838383838'),
	 *         'content' => '短信测试',
	 *     ),
	 *     array(
	 *         'phones' => array('13100000000', '13900000000'),
	 *         'content' => '端午节快乐',
	 *     )
	 *     ...   
	 * )
	 */
		
	public static function sendMany($content) {
		$account = sms_utils::get_account();
		if (!$account) {
			return false;
		}
		else {
			sms_utils::setLog(true);
			sms_utils::send_fanout($account, $content);
		}
	}
	
	public static function sendOne($content) {
		$account = sms_utils::get_account();
		if (!$account) {
			return false;
		}
		else {
			sms_utils::setLog(true);
			sms_utils::send_notice($account, $content);
		}
	}	
}