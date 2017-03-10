<?php
class market_shorturl{
	
	function shortenSinaUrl($long_url){
		$apiKey='209678993';//这里是你申请的应用的API KEY，随便写个应用名就会自动分配给你
		$short_url = '';
		$apiUrl='http://api.t.sina.com.cn/short_url/shorten.json?source='.$apiKey.'&url_long='.$long_url;
		$curlObj = curl_init();
		curl_setopt($curlObj, CURLOPT_URL, $apiUrl); 
		curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curlObj, CURLOPT_HEADER, 0);
		curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
		$response = curl_exec($curlObj);
		curl_close($curlObj);
		/*
		$json = json_decode($response);
		return $json[0]->url_short;
		*/
		$json = json_decode($response,true);
		if(!empty($json['error_code']) || isset($json['error_code'])){
			$short_url = $long_url;
		}else{
			$short_url = $json[0]['url_short'];
		}
		return $short_url;
	}
}