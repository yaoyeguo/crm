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

class taocrm_messenger_sms {
	public $requestGatewayUrl = 'http://idx.sms.shopex.cn/service.php';
	public $version = '1.0';
	public $source = 'ecos_taocrm';
	protected static $_gatewayUrl = null;
//	protected static $_token = null;
//	protected static $_certiId = null;
	protected static $_token = '6c6b5fbfd484c831364a6303cc297dec10233e25a21b96b800cb21d0a3f7d6cb';
	protected static $_certiId = 1;	
	protected static $_httpService = null;
	
	public function send($to, $message) {
		if (!$this->_checkLicense()) {
			return '';
		}
		
		$gatewayUrl = $this->_getGateway();
		if (!$gatewayUrl) {
			return 'you must to active the code';
		}
		
		$content = array(array($to, $message, 'Now'));
		$param = array();
		$param['certi_id'] = $this->_getCertiId();
		$param['ex_type'] = 1;
		$param['content'] = json_encode($content);
		$param['encoding'] = 'utf8';
		$param['version'] = $this->version;
		$param['source'] = $this->source;
		$param['ac'] = md5($param['certi_id'] . $param['ex_type'] . $param['content'] . $param['encoding'] . $this->_getToken());
        $results = $this->_getHttpService()->post($gatewayUrl, $param);
        $result = explode('|', $results);
        if ('true' == $result[0]) {
            $index = 200;
        }
        elseif('false' == $result[0]){
            $index = $result[1];
        }
        return $this->_msg($index);
	}
	
	public function notifyInsufficientMessage($to) {
		$message = '亲爱的用户，你的短信配额不足，请及时充值。';
		return $this->send($to, $message);
	}
	
	public function getAvailableMessageAmount() {
	    $url = 'http://service.shopex.cn/';
	    $param = array();
        $param['certi_app'] = 'sms.get_off';
        $param['certi_id'] = $this->_getCertiId();
        $param['format'] = 'json';
        $param['certi_ac'] = md5($param['certi_app'] . $param['certi_id'] . $param['format'] . $this->_getToken());

        if($result = $this->_getHttpService()->post($url, $param)) {
            $result = json_decode($result, true);
            return $result['info'];
        }
        return '';
	}
	
	private function _getGateway() {
		if (null === self::$_gatewayUrl) {
			$data = array();
			$data['certi_id'] = $this->_getCertiId();
			$data['version'] = $this->version;
			$data['source'] = $this->source;	//if is necessay		
			$data['ac'] = md5($this->_getCertiId() . $this->_getToken());
			
	        if($result = $this->_getHttpService()->post($this->requestGatewayUrl, $data)) {
	            $result = explode('|', $result);
	            self::$_gatewayUrl = $result[1];
	        }
		}
		return self::$_gatewayUrl;
	}
	
	private function _getToken() {
		if (null === self::$_token) {
			self::$_token = base_certificate::get('token'); 
		}
		return self::$_token;
	}
	
	private function _getCertiId() {
		if (null === self::$_certiId) {
			self::$_certiId = base_certificate::get('certificate_id');
		}
		return self::$_certiId;
	}
	
	private function _checkLicense() {
		if (!$this->_getCertiId() || !$this->_getToken()) {
			return false;
		}
		else {
			return true;
		}
	}
	
	private function _getHttpService() {
		if (null === self::$_httpService) {
			self::$_httpService = new base_httpclient();
		}
		return self::$_httpService;
	}

	private function _msg($index) {
        $aMsg = array(
            '200' => 'true',
            '1' => 'Security check can not pass!',
            '2' => 'Phone number format is not correct.',
            '3' => 'Lack of content or content coding error.',
            '4' => 'Lack of balance.',
            '5' => 'Information packets over limited.',
            '6' => 'You must recharge before write message!',
            '901' => 'Write sms_log error!',
            '902' => 'Write sms_API error!'
		);
        return $aMsg[$index];
    }
}