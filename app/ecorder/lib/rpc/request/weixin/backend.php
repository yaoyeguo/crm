<?php
/**
 * 微信注册服务同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_weixin_backend extends ecorder_rpc_request {

    var $api_url = MATRIX_SYNC_URL_M;
    var $api_version = '2.0';
    var $api_format = 'json';

    public function wx_api_request($params)
    {
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $from_node_id = base_shopnode::node_id($app_exclusion['app_id']);
        $token = base_shopnode::get('token',$app_exclusion['app_id']);

        $sys_params = array(
            'from_node_id' => $from_node_id,
            'format' => $this->api_format,
            'v' => $this->api_version,
            'timestamp' => date('Y-m-d H:m:s'),
        );
        $params = array_merge($sys_params, $params);
        $params['sign'] = $this->gen_matrix_sign($params, $token);

        $headers = array('Connection' => 5);
        $core_http = kernel::single('base_httpclient');
        
        //$resp = $core_http->post($this->api_url, $params, $headers);
        $resp = $this->curl($this->api_url, $params);
        //elog($resp);elog($params);
        
        $data = json_decode($resp, true);
        if( ! $data) $data = $resp;
        
        $logTitle = '微信请求:'.$params['method'];
        $logInfo = '提交参数:'.var_export($params, true) . '<BR>返回结果:'.var_export($data, true) . '<BR>';
        $log = app::get('ecorder')->model('api_log');
        if($data && $data['rsp'] == 'succ'){
            //正常返回的结果
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'request', 'success', $logInfo, array('task_id'=>$data['msg_id']));
            return $data['data'];
        }else{
            //返回错误
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'request', 'fail', $logInfo, array('task_id'=>$data['msg_id']));
            return $resp;
        }
    }
    
    function curl($url, $postFields = null)
    {
        $purl = parse_url($url);   
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if($purl['scheme'] == 'https')   {   
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);   
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);   
        }

        if(is_array($postFields) && 0 < count($postFields)){
            $postBodyString = "";
            $postMultipart = false;
            foreach ($postFields as $k => $v){
                if("@" != substr($v, 0, 1))//判断是不是文件上传
                {
                    $postBodyString .= "$k=" . urlencode($v) . "&"; 
                }
                else//文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                }
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
            }
        }
        $reponse = curl_exec($ch);
        
        if(curl_errno($ch)){
            throw new Exception(curl_error($ch),0);
        }else{
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if(200 !== $httpStatusCode){
                throw new Exception($reponse,$httpStatusCode);
            }
        }
        curl_close($ch);
        return $reponse;
    }

    //nouse
    public function wxHttpsRequest($url,$data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    public function genSign($params, $token)
    {
        ksort($params);
        $str = '';
        foreach ($params as $key =>$value) {
            if ($key != 'certi_ac') {
                $str .= $value;
            }
        }
        $signString = md5($str.$token);
        return $signString;
    }

    function gen_matrix_sign($params,$token)
    {
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

    function assemble($params)
    {
        if(!is_array($params)){
            return null;
        }
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? assemble($val) : $val);
        }
        return $sign;
    }

}