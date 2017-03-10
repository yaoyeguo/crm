<?php
class getaftersales extends PHPUnit_Framework_TestCase
{
    function setUp() {

    }

    public function testGetaftersales(){

        $url = 'http://www/taoguan/index.php/openapi/rpc/service/';
        $token = base_certificate::token();
        $time_out = 10;

        //json 9FFA44BE2220EF062CC2C3B9553730A9;
        $params = array('start_time'=>'2012-12-01','end_time'=>'2012-12-08','method'=>'aftersales.getList','type'=>'xml','flag'=>'aaa','sign'=>'F9798C235682BCE5D1804296CA70519F');
        foreach ($params as $k => $v){
            $arg[] = urlencode($k);
            $arg[] = urlencode(str_replace('/','%2F',$v));
        }

        $url .= implode('/',$arg);

        $http = kernel::single('base_httpclient');
        $response = $http->set_timeout($time_out)->post($url,$_POST);
        if($response === HTTP_TIME_OUT){
            return false;
        }else{
            echo $response;
            error_log($response,3,__FILE__.".log");
            //$result = json_decode($response,true);print_r($result);
            //$result = json_decode($response,true);
            //return $result;
        }
    }
}
