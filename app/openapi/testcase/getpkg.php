<?php
class getpkg extends PHPUnit_Framework_TestCase
{
    function setUp() {

    }

    private function gen_sign($params){
        $token = 'JclHQUADuVwOSfxVdzCObWhgkfjoRPkl';
        if(!$token){
            return false;
        }
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

	/**
     *
     * 签名参数组合函数
     * @param array $params
     */
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

    public function testGetPkg(){

        $url = 'http://192.168.41.16/taoguan/branches/stable_bugfix/index.php/openapi/rpc/service/';

        $params = array('flag' => 't1','method' => 'goods.getList','ver'=>'1','page_no'=>1,'page_size'=>100);
        $params['sign'] = $this->gen_sign($params);
        
        $time_out = 10;
        
        foreach ($params as $k => $v){
            $arg[] = urlencode($k);
            $arg[] = urlencode($v);
        }

        $url .= implode('/',$arg);
        $headers['Accept-Charset'] = 'gbk';

        $http = kernel::single('base_httpclient');
        $response = $http->set_timeout($time_out)->post($url,$_POST,$headers);
        error_log(var_export($response,true),3,DATA_DIR.'/log.log');
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
