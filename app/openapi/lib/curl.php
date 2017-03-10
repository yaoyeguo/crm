<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

define('HTTP_TIME_OUT',-3);
class openapi_curl{

    var $timeout = 30;
    var $defaultChunk = 4096;
    var $http_ver = '1.1';
    var $hostaddr = null;
    var $debug = false;
    public $rCoodkie = '';
    var $default_headers = array(
        'Pragma'=>"no-cache",
        'Cache-Control'=>"no-cache",
        'Connection'=>"close"
        );

    
    function set_timeout($timeout){
        $this->timeout = $timeout;
        return $this;
    }
    function get($url,$headers=null,$callback=null,$ping_only=false){
        return $this->action(__FUNCTION__,$url,$headers,$callback,array(),$ping_only);
    }

    function post($url,$data,$headers=null,$callback=null,$ping_only=false){
        return $this->action(__FUNCTION__,$url,$headers,$callback,$data,$ping_only);
    }

    function action($action,$url,$headers=null,$callback=null,$data=array(),$ping_only=false){

        $action = $action=='post'?true:false;
        $headers = array_merge($this->default_headers,(array)$headers);
        $set_headers = array();
        foreach((array)$headers as $k=>$v){
            $set_headers[] .= $k.': '.$v;
        }

        $this->responseBody = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        #echo $this->timeout;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'callback_header'));
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this,'callback_body'));
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if($set_headers)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $set_headers);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->http_ver);
        curl_setopt($ch, CURLOPT_POST, $action=='post'?true:false);
        //https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_exec($ch);

        curl_close($ch);

        $this->callback = $callback;
        preg_match('/\d{3}/',$this->responseHeader,$match);
        $this->initCookie();

        $this->responseCode = $match[0];
        switch($this->responseCode){
            case 301:
            case 302:
            #kernel::log(" Redirect \n\t--> ".$responseHeader['location']);
            return false;

            case 200:
            #kernel::log(' OK');
            if($this->callback){
                if(!call_user_func_array($this->callback,array($this,$this->responseBody))){
                    break;
                }
            }
            return $this->responseBody;

            case 404:
            #kernel::log(' file not found');
            return false;

            default:
            return false;
        }

    }
    /**
     * undocumented function
     *
     * @param  void
     * @return void
     * @author 
     **/
    public function initCookie($isReturn = false)
    {
        $headerArr = explode("\r\n", $this->responseHeader);
        $rcookie = '';
        foreach ($headerArr as $row) {
            if ($this->debug) {
                echo $headerArr."\r\n";
            }
            if (strpos($row, 'et-Cookie:') > 0) {
                $rows = str_replace('Set-Cookie: ', '', $row);
                $tmp = explode(';', $rows);

                if (!strpos($tmp[0], 'deleted')) {
                    if ($rcookie == '') {
                        $rcookie = $tmp[0];
                    }else{
                        $rcookie = $rcookie.'; '.$tmp[0];
                    }
                }
            }
        }
        if ($isReturn) {
            return $isReturn;
        }else{
            $this->rCookie = $rcookie;
            
        }
        
    }

    function callback_header($curl,$header){
        $this->responseHeader .= $header;
        return strlen($header);
    }
    function callback_body($curl,$content){
        $this->responseBody .= $content;
        return strlen($content);
    }
    function is_addr($ip){
        return preg_match('/^[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}$/',$ip);
    }

    private function microtime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}
