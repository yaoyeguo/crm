<?php

class taocrm_matrixapi
{

	//public $gatewayUrl = "http://rpc.ex-sandbox.com/sync";
	public  $gatewayUrl = 'http://matrix.ecos.shopex.cn/sync';

	function execute($sdf,$shop_id=''){
		$app_exclusion = app::get('base')->getConf('system.main_app');
		$certi = base_certificate::get('certificate_id');
		$node_id = base_shopnode::node_id($app_exclusion['app_id']);

		$sdf['certi_id'] = $certi;
		$sdf['from_node_id'] = $node_id;
		$sdf['from_api_v'] = '1.0';
		$sdf['to_api_v'] = '1.0';
		$sdf['v'] = '1.0';
		$sdf['timestamp'] = '';
		$sdf['format'] = 'JSON';
		$sdf['charset'] = 'utf-8';
		$sdf['sign'] = $this->gen_matrix_sign($sdf, base_certificate::token());
        if($shop_id == '')
        {
            //理论上使用else部分的代码，鉴于兼容老代码，此段暂留
            $httpClient = kernel::single('base_httpclient');
            $result = $httpClient->post($this->gatewayUrl,$sdf);
        }else
        {
            $result = $this->get_api($sdf,$shop_id);
        }
        $result = json_decode($result,true);

		return isset($result['data']) ? json_decode($result['data'],true) : array();
	}

    private function get_api($param,$shop_id)
    {
        $api_obj = new ectools_api_prism_request();
        $result = $api_obj->get_api($param,$shop_id);
        return $result;
    }


	function gen_matrix_sign($params,$token){
		return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
	}


	function assemble($params){
		if(!is_array($params)){
			return null;
		}

		ksort($params,SORT_STRING);
		$sign = '';
		foreach($params AS $key=>$val){
			$sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
		}
		return $sign;
	}

}
