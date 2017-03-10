<?php

/*
 * taobao 聊于记录获取
 *
 * @author
 * @version 0.1
 */

abstract class ectools_api_taobao_request extends ectools_api_abstract {

    //API访问地址
    //const SIP_URL = 'http://gw.api.matrix.shopex.cn/router/rest';
    const SIP_URL = 'http://localhost/taoda_api/api.php';

    const SIP_APPSECRET = '7013e7c53e8f9134f1fe315f0181a102';
    const SIP_APPKEY = '';
    //API 版本
    const API_VERSION = '2.0';
    //SIGN 算法
    const SIGN_METHOD = 'md5';
    //返回格式
    const RETURN_FORMAT = 'xml';

    /**
     * taobao Session Key
     *
     * @var String
     */
    protected $sessionKey = '';

    /**
     * 设置SESSION KEY
     *
     * @param String $sessionKey
     * @return void
     */
    public function setSessionKey($sessionKey) {

        $this->sessionKey = $sessionKey;
    }

    /**
     * 获取taobao API 的SESSION KEY
     *
     * @param void
     * @return String
     */
    protected function getSessionKey() {

        if ($this->sessionKey) {

            return $this->sessionKey;
        } else {

            //使用矩阵提供接口来获取 SESSIONKEY
        }
    }

    /**
     * 获取基本的 API REQUEST 信息
     *
     * @param void
     * @return Array
     */
    protected function getBaseApiRequestParam() {

        $result = array('app_key' => self::SIP_APPKEY,
            'session' => $this->sessionKey,
            'timestamp' => date('Y-m-d H:i:s', time() + 300),
            'v' => self::API_VERSION,
            'format' => self::RETURN_FORMAT);
        return $result;
    }


    /**
     * 调用taobao API接口
     *
     * @param String $method 要调用的taobao API 接口方法
     * @param Array	$params 所调用API方法要用到的参数数组
     */
    protected function apiRequest($method, $params,$shop_id = '') {
        if (!is_array($params)) {
            return array();
        }

        $params = array_merge($this->getBaseApiRequestParam(), $params);
        //设置要调用的方法
        $params['method'] = $method;

        //获取Sign
        ksort($params);
        $signString = self::SIP_APPSECRET;
        foreach ($params as $key => $value) {

            $signString = $signString . $key . $value;
        }
        $signMethod = self::SIGN_METHOD;
        $params['sign'] = strtoupper($signMethod($signString));
        unset($params['app_key']);

        //$result = httpclient::quickPost(self::SIP_URL.'?'.time(), $params);
        $result = $this->get_api($params,$shop_id);
        return $this->xml2array($result);
    }

    private function get_api($param,$shop_id)
    {
        $api_obj = new ectools_api_prism_request();
        $result = $api_obj->get_api($param,$shop_id);
        return $result;
    }
}
