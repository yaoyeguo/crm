<?php
class taocrm_middleware_javahttp
{
    public $gatewayUrl = JAVA_NEW_URL;
    protected $format = "json";
    protected function generateSign($params)
    {

    }
    public function exec($params){
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->post($this->gatewayUrl,$params);
        $response = json_decode($response,true);
        return $response;
    }
}