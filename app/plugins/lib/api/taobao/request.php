<?php
define('LIB_DIR', realpath(dirname(__FILE__).'/../') );
require_once(LIB_DIR . '/httpclient.php');

//require_once(LIB_DIR . 'http/httpclient.php');
/*
 * taobao 聊于记录获取
 *
 * @author
 * @version 0.1
 */

abstract class plugins_api_taobao_request extends plugins_api_abstract {
	//API访问地址
	const SIP_URL = 'http://gw.api.matrix.shopex.cn/router/rest';
	//证书
	const SIP_APPSECRET = '2fc426b0109908169017efb33a71f15c';//'876f5c4d7f0e734189973b0b5bb5bc13';
	//KEY
	const SIP_APPKEY = '';//'12132354';
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
	 * 转换旺旺ID
	 *
	 * @param String $wwId
	 * @return String
	 */
	protected function getRealTaobaoId($wwId) {

		if (!preg_match('/^cntaobao/is', $wwId)) {

			return sprintf('cntaobao%s', $wwId);
		} else {

			return $wwId;
		}
	}

	/**
	 * 获取taobao API 的SESSION KEY
	 *
	 * @param void
	 * @return String
	 */
	/*protected function getSessionKey() {
	 $sessions = '';
	 //$preFix = $this->centerInfo['host_name'];
	 //增加获取 SESSION KEY 的功能
	 //return '2506264d2baf9cc321a1f275b4e85e304ac1b';

	 $db = $this->getDB();
	 $res = $db->query("SELECT * FROM sdb_base_kvstore WHERE prefix='taobukpi' AND `key`='taobusession'");
	 while ($row = $db->fetchArray($res)) {
	 $session = unserialize($row['value']);
	 }

	 $db->close();
	 return $session;
	 }*/

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
        //$result = plugins_api_httpclient::quickPost(self::SIP_URL, $params);
        $result = $this->get_api($params,$shop_id);
        return $this->xml2array($result);
    }

    private function get_api($param,$shop_id)
    {
        $api_obj = new ectools_api_prism_request();
        $result = $api_obj->get_api($param,$shop_id);
        return $result;
    }
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

	public function isFailRequest($apiResult) {

		// if($apiResult)
	}

}
