<?php
class addtransfer extends PHPUnit_Framework_TestCase
{
    function setUp() {

    }

    public function testAddtransfer(){

        $url = 'http://192.168.41.16/taoguan/branches/stable_bugfix/index.php/openapi/rpc/service/';
        $token = base_certificate::token('ome');
        $time_out = 10;

        $system = array(
            'flag' => 't1',
            'method' => 'transfer.add',
            'type' => 'json',
        );
        //jsona 07867C5F3E9F64372E59798F3F9CB577
        //json DD190C4244521E76AD7F16CE737B5CC8
        //xml C86732F85599E09D76A6BF45F18D5328
        $params = array(
            'name' => '20130523入库单API',
            'vendor' => 'sto',
            't_type' => 'E',
            'branch_bn' => 'stockhouse',
            'delivery_cost' => '10',
            'operator' => 'system',
            'memo' => '123',
            'items' => json_encode(array(
                array(
                    'bn' => 'MJB-3004-Z',
                    'name' => '几何毛巾被',
                    'nums' => '2',
                    'price' => '10',
                ),
            )),
        );

        $_params = array_merge($system,$params);
        $_params['sign'] = $this->gen_sign($_params);

        $_POST = $_params;
        foreach ($_params as $k => $v){
            $arg[] = urlencode($k);
            $arg[] = urlencode(str_replace('/','%2F',$v));
        }
        
        //$url .= implode('/',$arg);

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

    private function gen_sign($params){
        $token = 'JclHQUADuVwOSfxVdzCObWhgkfjoRPkl';
        if(!$token){
            return false;
        }
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

    private function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }
}
