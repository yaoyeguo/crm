<?php
class getsales extends PHPUnit_Framework_TestCase
{
    function setUp() {

    }

    public function testGetsales(){

        $url = 'http://www/taoguan/index.php/openapi/rpc/service/';
        $token = base_certificate::token();
        $time_out = 10;


        //jsona 07867C5F3E9F64372E59798F3F9CB577
        //json DD190C4244521E76AD7F16CE737B5CC8
        //xml C86732F85599E09D76A6BF45F18D5328
        $params = array('start_time'=>'2012-11-11','end_time'=>'2012-11-12','method'=>'sales.getList','type'=>'xml','flag'=>'aaa','sign'=>'C86732F85599E09D76A6BF45F18D5328');
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
            //print_r($response);
            //$result = json_decode($response,true);print_r($result);
            //return $result;
            //$xml_data = kernel::single('taoexlib_xml')->xml2array($response);
		    //var_dump($xml_data);exit;
        }
    }
}
