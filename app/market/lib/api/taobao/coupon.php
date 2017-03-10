<?php


class market_api_taobao_coupon extends market_api_taobao_request implements market_api_interface{

	const FIELDS = '';

	const PAGESIZE = '50';
    
    public function & fetch($startTime, $endTime){
        ;        
    }

	public function send(&$params) {
        
        $method = "taobao.promotion.coupon.send";

		//获取 sessionKey , 如取不到，则直接返加空数组
		$this->sessionKey = $this->getSessionKey();
		if (empty($this->sessionKey)) return array();

		$apiResult = $this->apiRequest($method, $params);

		return $apiResult;
	}

}