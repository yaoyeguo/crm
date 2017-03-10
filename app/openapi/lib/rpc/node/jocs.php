<?php
/**
 * undocumented class
 *
 * @package default
 * @author 
 **/
class openapi_rpc_node_jocs extends openapi_rpc_request
{
//    public $url = 'http://59.188.80.171:7080/openapi/rpc/crm2jocs/service';  M020150608000003
//    public $token = 'b373bf77d8eb335a0005baad47919579029716cedf7ef9469c70d6935dc269f3';
//    public $url = 'http://59.41.187.147:7083/jocs_ht/msgHttpService';
//    public $token = 'zmkjjsums';
    public function call($data,$method){
        $url                 = JOCS_SERVICE_URL;
        $token               = JOCS_SERVICE_TOKEN;
        $params['content']   = $data;
        $params['timestamp'] = time();
        $params['flag']      = 'CRM';
        $params['charset']   = 'utf-8';
        $params['method']    = $method;
        $params['ver']       = '1';
        $params['type']      = 'json';
        $params['sign']      = self::_gen_sign($params,$token);
        $postData            = json_encode($params);
        $core_http = new openapi_curl();
        $header = array(
            'Content-Type'=>'application/json; charset=utf-8',  
            'Content-Length' => strlen($postData),
        );
        $response = $core_http->post($url,$postData,$header);
        $res = $this->getResult($response);
        $this->log($params,$res,$postData);
        return $res;
    }

    function call_jocs_order($postData,$method){
        $url                 = JOCS_SERVICE_URL;
        $token               = JOCS_SERVICE_TOKEN;
        $params['content']   = $postData['memberorderNo'];
        $params['timestamp'] = time();
        $params['flag']      = 'CRM';
        $params['charset']   = 'utf-8';
        $params['method']    = $method;
        $params['ver']       = '1';
        $params['type']      = 'json';
        $params['sign']      = self::_gen_sign($params,$token);
        $postData            = json_encode($params);
        $core_http = new openapi_curl();
        $header = array(
            'Content-Type'=>'application/json; charset=utf-8',
            'Content-Length' => strlen($postData)
        );
        $response = $core_http->post($url,$postData,$header);
        $res = $this->_getResult($response);
        $this->log($params,$res,$postData);
        return $res;
    }


    function call_mobile_worker($post_data){
        $token = '6322F125381C9576A69C683498CB734F';
        $url   = 'http://test.joylifeglobal.net/crm/core/crmApi/sendMsg';
        $time = time();
        $params = '';
        foreach($post_data as $k=>$v){
            $params.="$k=".urlencode($v)."&";
        }
        $sign = strtoupper(md5($token.$time));
        $core_http = new openapi_curl();
        $header = array(
            //'Content-Type'=>'text/html; charset=utf-8',
            'Authorization' => $sign,
        );
        $response = $core_http->post($url,$params,$header);
        $this->log($params,$response,$post_data);
        return $response;
    }

    static function assemble_1($params)
    {
        if(!is_array($params))  return null;
        $content = $params['method'] == 'getOrderInfos' ? $params['content'] : json_encode($params['content']);
        $sign = 'timestamp='.$params['timestamp'].', content='.$content.', flag='.$params['flag'];
        $sign.= ', charset=utf-8, method='.$params['method'].', ver=1, type=json';
        $sign = str_replace('{','',$sign);
        $sign = str_replace('}','',$sign);
        //error_log(''.var_export($sign,1)."\r\n",3,__FILE__.'.log');
        return $sign;
    }//End Function

    static function assemble_2($params)
    {
        if(!is_array($params))  return null;
        $sign_str = 'sign='.$params['sign'].',';
        $content = $params['method'] == 'getOrderInfos' ? $params['content'] : json_encode($params['content']);
        $sign = $sign_str.' timestamp='.$params['timestamp'].', content='.$content.', flag='.$params['flag'];
        $sign.= ', charset=utf-8, method='.$params['method'].', ver=1, type=json';
        $sign = str_replace('{','',$sign);
        $sign = str_replace('}','',$sign);
        return $sign;
    }//End Function


    static function _gen_sign($params,$token){
        $params['sign'] = md5(self::assemble_1($params));
        $notifyStr = implode(',',array_filter(explode(',',self::assemble_2($params))));
        $notifyStr.= $token;
        return  md5(base64_encode($notifyStr));
    }

    public function log($params,$res){
        $oApi_log = &app::get('crmbase')->model('api_log');
        $log_sdf = array(
            'api_name' => $params['method'],
            'api_flag' => $params['flag'],
            'api_type' => 'request',
            'params' => serialize($params),
            'createtime' => time(),
        );
        $log_sdf['status'] = $res['status'];
        if($res['content']){
            $content = json_decode($res['content'],1);
            $content['msg'] = $res['msg'];
            $log_sdf['msg'] =  serialize($content);
        }else{
            $log_sdf['msg'] =  $res['msg'];
        }
        $oApi_log->save($log_sdf);
    }


    /**
     * undocumented function
     * @param
     * @return void
     **/
    public function _getResult($jsonData)
    {
        $data = json_decode($jsonData,1);
        if (!$data) {
            $res['status'] = 'fail';
            $res['msg'] = $jsonData?$jsonData:'no response';
            return $res;
        }
        if ($data['rsp'] != 'success' ) {
            $res['status'] = 'fail';
            $res['msg'] = !empty($data['sub_msg'])?$data['sub_msg']:$data['msg'];
        }else{
            $res['status'] = 'succ';
            $res['msg'] = 'succ';
            $res['content'] = $data['content'];
        }
        return $res;
    }

    /**
     * undocumented function
     * @param
     * @return void
     **/
    public function getResult($jsonData)
    {
        $data = json_decode($jsonData,1);
        if (!$data) {
            $res['status'] = 'fail';
            $res['msg'] = $jsonData?$jsonData:'no response';
            return $res;
        }
        if ($data['rsp'] != 'succ' ) {
            $res['status'] = 'fail';
            $res['msg'] = !empty($data['sub_msg'])?$data['sub_msg']:$data['msg'];
        }else{
            $res['status'] = 'succ';
            $res['msg'] = 'succ';
        }
        return $res;
    }

    public function gen_sign($params,$token){
        //echo json_encode($params,JSON_UNESCAPED_UNICODE);
        $p['content'] = $params['content'];
        unset($params['content']);
        $pstr1 =  $this->arrayToString($p).', '.$this->arrayToString($params);
        // // 第一次加密，在没有signdata的时候进行md5
        // String noSignStr = MD5.md5(map.toString());
        $sign1 = md5($pstr1);
        // map.put("sign", noSignStr);
        $pstr2 = 'sign='.$sign1.', '.$pstr1;
        // // 第二次加密，增加signdata的xml和中脉国际编码
        $sign2 = $pstr2.$token;
        $sign2 = iconv("UTF-8", "GB2312//IGNORE", $sign2) ;
        $sign2 = base64_encode($sign2);
        $sign3 = md5($sign2);
        // // 第三次加密，整段加密并存入xml
        error_log(''.var_export($pstr1,1)."\r\n",3,__FILE__.'.log');
        error_log(''.var_export($sign1,1)."\r\n",3,__FILE__.'.log');
        error_log(''.var_export($sign3,1)."\r\n",3,__FILE__.'.log');
        return $sign3;
        //return strtoupper(md5(strtoupper(md5(self::assemble($params))).$token ));
    }


    public function arrayToString($data,$isArray=false){
        if(!is_array($data))  return null;
        $res = '';
        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $count = count($data);
            if (is_array($value)) {
                $array_keys = array_keys($value);
                if (is_numeric($key)) {
                    $res.= $this->arrayToString($value,$this->isAllNum($array_keys) );

                }else{
                    $res.= $key.'='.$this->arrayToString($value,$this->isAllNum($array_keys));
                }
            }else{
                $pos = $i == $count?"":", ";

                $res .= $key.'='.$value.$pos;
            }
        }
        //$res = substr($res, 0,strlen($res)-1);
        if ($isArray) {
            $res = '['.$res.'], ';
        }
        return $res;

    }
    /**
     * 检查一个数组是否全为数字
     * @param
     * @return void
     * @author 张学会 <phlv@163.com>
     **/
    public function isAllNum($array)
    {
        #print_r($array);
        $flag = true;
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $k=>$v) {
            if (!is_numeric($v)) {
                $flag = false;
                break;
            }
        }
        return $flag;
    }

    public  function _array2xml($data,&$xml){
        if(is_array($data)){
            foreach($data as $k=>$v){
                if(is_numeric($k)){
                    $xml.=$this->_array2xml($v,$xml);
                }else{
                    $xml.='<'.$k.'>';
                    $xml.=$this->_array2xml($v,$xml);
                    $xml.='</'.$k.'>'."\r\n";
                }
            }
        }elseif(is_numeric($data)){
            $xml.=$data;
        }elseif(is_string($data)){
            $xml.=''.$data.''."";
        }
    }

} // END class openapi_rpc_node_crm
