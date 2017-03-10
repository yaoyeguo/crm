<?php

class taocrm_middleware_member extends taocrm_middleware_abstract{

	public function SearchMemberAnalysisList($filter)
    {
		$param = $this->packFilter($filter);
		$http = new base_httpclient;
		$result  = $http->post(self::SEARCH_MEMBER_LIST_URL, $param);

		return $result;
	}

	/**
	 * Enter description here ...
	 * @param string $shop_id
	 * @param array $filter
	 */
	public function SearchMemberAnalysisCount($shopId,$filter)
    {
		//var_dump($shopId);exit;
		if(!$shopId){
			return 0;
		}

		$param = $this->packFilter($shopId,$filter);
		$http = new base_httpclient;
		//echo '<pre>';var_export($param);exit;
		$result  = $http->post(self::SEARCH_MEMBER_COUNT_URL, $param);
		//var_dump($result);;exit;
		$result = json_decode($result,true);
		$nums = -1;
		if($result['rsp'] == 'succ'){
			$nums = $result['info'];
		}
		return $nums;
	}

}
