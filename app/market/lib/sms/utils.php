<?php
class market_sms_utils {
    const VERSION = '1.0';
    const API_URL = 'http://webapi.sms.shopex.cn'; //注册的线上地址
    const API_URL_IN = 'http://newsms.ex-sandbox.com/sms_webapi/';//注册的内网地址
    
    const TEST_BOOK_URL = 'http://resource.ex-sandbox.com/index.php?c=welcome&m=darkLogin&source=';
    const TEST_SEND_URL = 'http://api.sms.ex-sandbox.com/';//内网短信发送地址
    //const API_USER_IN="http://webpy.ex-sandbox.com/";//内网用户信息
    //	const API_USER_OUT=


    //	http://webpy.ex-sandbox.com/
    //	http://api.sms.shopex.cn
    const SERVICE_URL = 'http://api.sms.shopex.cn/';

    //: http://newsms.ex-sandbox.com/sms_webapi/
    //http://webapi.sms.shopex.cn
    const SINGLE_SMS_LENGTH = 70;
    const tag=0;
    public static $blackList = null;
    public static $sumProcess = 0;
    public static $maxProcess = 30;
    public static $serverTimestamp = null;
    public static $localTimestamp = null;
    public static $writeLog = false;

    public static function make_shopex_ac($arr, $token) {
        $temp_arr = $arr;
        ksort($temp_arr);
        $str = '';
        foreach ($temp_arr as $key => $value) {
            if ($key != 'certi_ac') {
                $str .= $value;
            }
        }
        return md5($str . md5($token));
    }

    public static function base_make_shopex_ac($arr, $token) {
        ksort($arr);
        $str = '';
        foreach ($arr as $key => $value) {
            if ($key != 'ac') {
                $str .= $value;
            }
        }
        return strtolower(md5($str . strtolower(md5($token))));
    }

    public static function no_slashes($temp_arr) {
        foreach ($temp_arr as $key => $value) {
            if (is_array($value)) {
                $value = self::no_slashes($value);
                $array_temp[$key] = $value;
            }
            else {
                $array_temp[$key]=stripslashes($value);
            }
        }
        return $array_temp;
    }

    public static function province_mapping($provinceName) {
        $mapping = array(
			'北京' => '110000',
			'天津' => '120000',
			'上海' => '310000',
			'重庆' => '500000',
			'香港' => '810000',
			'台湾' => '710000',
			'澳门' => '820000',
			'新疆' => '650000',
			'宁夏' => '640000',
			'内蒙古' => '150000',		
			'广西' => '450000',
			'西藏' => '540000',
			'河北' => '130000',
			'河南' => '410000',		
			'陕西' => '610000',
			'辽宁' => '210000',
			'吉林' => '220000',
			'黑龙江' => '230000',		
			'江苏' => '320000',
			'浙江' => '330000',
			'安徽' => '340000',
			'福建' => '350000',		
			'江西' => '360000',
			'山东' => '370000',
			'山西' => '140000',
			'湖北' => '420000',		
			'湖南' => '430000',
			'广东' => '440000',		
			'海南' => '460000',
			'四川' => '510000',
			'贵州' => '520000',
			'云南' => '530000',	
			'陕西' => '610000',
			'甘肃' => '620000',		
			'青海' => '630000',																			
        );
        return $mapping[trim($provinceName)];
    }

    public static function register($arr) {
        //error_log(var_export(APP_TOKEN,true),3,'d:/token.log');
        $param = $arr;
        $param['certi_app'] = 'sms.reg';
        $param['version'] = '1.1';
        $param['format'] = 'json';
        $param['timestamp'] = self::get_server_time();
        $param['certi_ac'] = self::make_shopex_ac($param,'SMS_REG');
        $http = new base_httpclient;
        if (self::tag==1){
            $result = $http->post(self::API_URL_IN, $param);//内网测试地址
        }else{
            $result = $http->post(self::API_URL, $param);//线上地址
        }
         
        return json_decode($result);
    }

    public static function GetEnterpriseByMobile($arr)
    {
        $param = $arr;
        $param['certi_app'] = 'sms.getaccount_info';
        $param['version'] = '1.0';
        $param['format'] = 'json';
        $param['timestamp'] = self::get_server_time();
        $param['certi_ac'] = self::make_shopex_ac($param,'SMS_ACCOUNT');
        $http = new base_httpclient;
        if (self::tag==1){
            $result = $http->post(self::API_URL_IN, $param);//内网测试地址
        }else{
            $result = $http->post(self::API_URL, $param);//线上地址
        }
        return json_decode($result);
    }
    
    public static function activeShopexid($arr)
    {;
        $param = array();
        if (strlen($arr['password']) > 64) {
            $market_edm_des = kernel::single('market_edm_des');
            $param['password'] = $market_edm_des->decrypt($arr['password']);
        }
        else {
            $param['password'] = md5($arr['password'] . 'ShopEXUser');
        }
        $param['certi_app'] = 'sms.shopexid_active';
        $param['entid'] = $arr['entid'];
        $param['source'] = APP_SOURCE;
        $param['version'] = isset($arr['version']) ? $arr['version'] : '1.0';
        $param['format'] = isset($arr['format']) ? $arr['format'] : 'json';
        $param['timestamp'] = self::get_server_time();
        $param['certi_ac'] = self::make_shopex_ac($param,'SHOPEX_ACTIVE');
        $http = new base_httpclient;
        if (self::tag==1){
            $result = $http->post(self::API_URL_IN, $param);//内网测试地址
        }else{
            $result = $http->post(self::API_URL, $param);//线上地址
        }
        return json_decode($result);
    }
    
    public static function registerErrorMsg($code) {
        if(strstr($code, 'is_binded')){
            return '该手机号码已被其他应用绑定，请使用其他手机号码进行激活';
        }
         
        $msg = array(
			'EmailExist' => 'Email已被使用,请更换后再注册',
        );
        return isset($msg[$code]) ? $msg[$code] : $code;
    }


    public static function get_user_info($arr) {
        $param = array(
    		'certi_app' => 'sms.info',
    		'entId' => $arr['entid'],
//    		'entPwd' => md5($arr['password'].'ShopEXUser'),
    		'entPwd' => self::getDecPassword($arr['password']),
    		'source' => APP_SOURCE,
    		'version' => self::VERSION,
    		'format' => 'json',
    		'timestamp' => self::get_server_time(),
        );
        $param['certi_ac'] = self::base_make_shopex_ac($param, APP_TOKEN);
        $http = new base_httpclient;
        $result = $http->post("http://api.sms.shopex.cn", $param);//线上地址
        return json_decode($result);
    }


    public static function login($arr) {
        $param = array(
			'certi_app' => 'sms.login',
			'identifier' => $arr['identifier'],
			'password' => $arr['password'],
			'version' => self::VERSION,
			'format' => 'json',
			'timestamp' => self::get_server_time(),
        );
        $param['certi_ac'] = self::make_shopex_ac($param, 'SMS_LOGIN');

        $http = new base_httpclient;
        if (self::tag==1){
            $result = $http->post(self::API_URL_IN, $param);//内网测试地址
        }else{
            $result = $http->post(self::API_URL, $param);//线上地址
        }
        return json_decode($result);
    }

    /**
     *
     * 短信包购买/赠送接口
     * @param array $arr
     * @param string $pid 短信包id
     */
    public static function buy($arr, $pid, $startTime) {
        $param = array(
			'certi_app' => 'sms.buy',
			'startTime' => $startTime,
			'prdId' => $pid,
			'entId' => $arr['entid'],
			'entPwd' => md5($arr['password'] . 'ShopEXUser'),
			'license' => base_certificate::get('certificate_id') ? base_certificate::get('certificate_id') : 1,
			'source' => APP_SOURCE,
			'version' => self::VERSION,
			'format' => 'json',
			'timestamp' => self::get_server_time()
        );

        $param['certi_ac'] = self::make_shopex_ac($param, APP_TOKEN);
        $http = new base_httpclient;
        if (self::tag==1){
            $result = $http->post(self::API_URL_IN, $param);//内网测试地址
        }else{
            $result = $http->post(self::API_URL, $param);//线上地址
        }
        return json_decode($result);
    }

    /**
     *
     * 发送通知类短信
     * @param array $arr
     * array(
     *     'entid' => '123456789012',
     *     'password' => '123456',
     *     'source' => '616525',
     * )
     * @param array $content
     * 必须是一个手机号码和一条短信内容
     * eg.
     * array(
     *     'phones' => '13636348683',
     *     'content' => '短信测试',
     * )
     * @param string $token
     */
    public static function send_notice($arr, $content, $filter = true) {
        $msgCount = 0;

        //		检查是否包含禁词
        if ($filter && self::is_banned_keywords($content['content'])) {
            if (self::$writeLog) {
                self::writeSpecialLog(-3, array(
    							'phones' => array($content['phones']),
    							'content' => $content['content']
                ));
            }
            return false;
        }

        if (!self::isMobilePhone($content['phones'])) {
            if (self::$writeLog) {
                self::writeSpecialLog(-4, array(
    							'phones' => array($content['phones']),
    							'content' => $content['content']
                ));
            }
            return false;
        }

        //		剩余短信条数,
        $info = self::get_user_info($arr);
        $smsAmount = ceil(self::utf8_strlen($content['content']) / self::SINGLE_SMS_LENGTH);
        if (('succ' == $info->res) && $info->info->month_residual >= $smsAmount) {
            $contents = array();
            $contents[] = $content;
            $result = sms_utils::send($arr, json_encode($contents), 'notice');
            return ('succ' == $result->res) ? true : false;
        }
        return false;
    }

    /**
     *
     * 群发短信
     * @param array $arr
     * @param array $content
     * eg.
     * array(
     *     array(
     *         'phones' => array('13636348683', '13838383838'),
     *         'content' => '短信测试',
     *     ),
     *     array(
     *         'phones' => array('13100000000', '13900000000'),
     *         'content' => '端午节快乐',
     *     )
     *     ...
     * )
     * @param string $token
     *
     */
    public static function send_fanout($arr, $content, $filter = true) {
        $msgCount = 0;
        //		检查是否包含禁词 and 统计有效待发短信数量
        $contents = array();
        foreach ($content as $msg) {
            if ($filter && self::is_banned_keywords($msg['content'])) {
                if (self::$writeLog) {
                    self::writeSpecialLog(-3, $msg);
                }
                continue;
            }
            else {
                foreach ($msg['phones'] as $key => $num) {
                    if (!self::isMobilePhone($num)) {
                        if (self::$writeLog) {
                            self::writeSpecialLog(-4, array(
    							'phones' => array($num),
    							'content' => $msg['content']
                            ));
                        }
                        unset($msg['phones'][$key]);
                    }
                }
                $msgCount += ceil(self::utf8_strlen($msg['content']) / self::SINGLE_SMS_LENGTH) * count($msg['phones']);
                $msg['phones'] = implode(',', $msg['phones']);
                $contents[] = $msg;
            }
        }

        if (!$msgCount) {
            return null;
        }

        //		获取剩余短信条数
        $info = self::get_user_info($arr);
        if (('succ' == $info->res) && ($info->info->month_residual >= $msgCount)) {
            $contentsArr = array_chunk($contents, 30);
            //			Because pcntl_fork cause mysql connection missed, so do not use pcntl_fork
            $flag = false;
            if ($flag && function_exists('pcntl_fork')) {
                foreach ($contentsArr as $value) {
                    sms_utils::send_fanout_multi_process($arr, json_encode($value));
                }
            }
            else {
                foreach ($contentsArr as $value) {
                    sms_utils::send($arr, json_encode($value), 'fan-out');
                }
            }
        }
    }

    /**
     *
     * 短信发送接口
     * @param json $content
     * @param string $type
     * 		  value "fan-out" or "notice"
     * @param string $token
     */
    public static function send($arr, $content, $type) {
        $param = array(
			'certi_app' => 'sms.send',
			'entId' => $arr['entid'],
			'entPwd' => md5($arr['password'] . 'ShopEXUser'),
			'license' => base_certificate::get('certificate_id') ? base_certificate::get('certificate_id') : 1,
			'source' => APP_SOURCE,
			'contents' => $content,
			'version' => self::VERSION,
			'format' => 'json',
			'sendType' => 'fan-out',
			'timestamp' => self::get_server_time(),
        );
        $param['certi_ac'] = self::base_make_shopex_ac($param, APP_TOKEN);

        $http = new base_httpclient;
        if (self::tag==1){
            $result = $http->post(self::TEST_SEND_URL, $param);//内网测试地址
        }else{
            $result = $http->post(self::API_URL, $param);//线上地址
        }
        if (self::$writeLog) {
            self::writeLog($content, $result);
        }
        return json_decode($result);
    }



    public static function update_blacklist() {
        $param = array(
			'certi_app' => 'sms.backlist',
			'version' => self::VERSION,
			'format' => 'json',
			'timestamp' => self::get_server_time(),
        );

        $param['certi_ac'] = self::make_shopex_ac($param, 'SMS_BACK');
        $http = new base_httpclient;
        if (self::tag==1){
            $result = $http->post(self::API_URL_IN, $param);//内网测试地址
        }else{
            $result = $http->post(self::API_URL, $param);//线上地址
        }
        $result = json_decode($result);

        if ('succ' == $result->res) {
            $logFile = self::get_resource_path() . 'blacklistLog.txt';
            $lastUpdateTime = 0;
            if (file_exists($logFile)) {
                $lastUpdateTime = file_get_contents($logFile);
            }

            if ($lastUpdateTime != $result->info->update_time) {
                $remoteUrl =  $result->info->download_url;
                $http = new base_httpclient;
                $words = $http->get($remoteUrl);
                $wordsArr = array();
                if ($words) {
                    $wordsArr = explode("\n", $words);
                }
                 
                $dataFile = self::get_resource_path() . 'blacklist.php';
                $dataHandle = fopen($dataFile, 'w');
                fwrite($dataHandle, json_encode($wordsArr));
                fclose($dataHandle);
                 
                $logHandle = fopen($logFile, 'w');
                fwrite($logHandle, $result->info->update_time);
                fclose($logHandle);
            }
        }
    }

    /**
     *
     * 取禁词
     * @return array
     */
    public static function get_blacklist() {
        if (null === self::$blackList) {
            $dataFile = self::get_resource_path() . 'blacklist.php';
            if (file_exists($dataFile)) {
                $dataHandle = fopen($dataFile, 'r');
                $contents = fread($dataHandle, filesize($dataFile));
                fclose($dataHandle);
                self::$blackList = json_decode($contents);
            }
        }

        return self::$blackList;
    }

    /**
     *
     * 检查是否包含禁词
     * @param string $str
     * @return 如果包含禁词则返回禁词，如果未包含禁词返回false
     */
    public static function is_banned_keywords($str) {
        if ($blackList = self::get_blacklist()) {
            foreach ($blackList as $value) {
                if ($value == '') {
                    continue;
                }
                if (false !== strpos($str, $value)) {
                    return $value;
                }
            }
        }
        return false;
    }

    public static function get_server_time() {
        if (null === self::$serverTimestamp || null === self::$localTimestamp) {
            $param = array(
				'certi_app' => 'sms.servertime',
				'version' => self::VERSION,
				'format' => 'json',
            );

            $param['certi_ac'] = self::make_shopex_ac($param, 'SMS_TIME');

            $http = new base_httpclient;
            if (self::tag==1){
                $result = $http->post(self::API_URL_IN, $param);//内网测试地址
            }else{
                $result = $http->post(self::API_URL, $param);//线上地址
            }
            $result = json_decode($result);
            self::$serverTimestamp = ('succ' == $result->res) ? $result->info : 0;
            self::$localTimestamp = time();
            return self::$serverTimestamp;
        }
        else {
            return self::$serverTimestamp + time() - self::$localTimestamp;
        }
    }
    
    public static function getDecPassword($password)
    {
        //对密码进行解密
        $market_edm_des = kernel::single('market_edm_des');
        if(strlen($password) > 64){
            $password = $market_edm_des->decrypt($password);
        }else{//兼容旧的原始密码
            $password = md5($password.'ShopEXUser');
        }
        return $password;
    }

    public static function get_book_url($arr) {
        
        $url = 'http://newsms.ex-sandbox.com/index.php?ctl=sms&act=prdsList&source=';
        //newsms.ex-sandbox.com/v2/index.php
        
        if (self::tag==1){
            $url = self::TEST_BOOK_URL;//内网测试地址
        }else{
            $url = 'http://sms.shopex.cn/index.php?ctl=sms&act=prdsList&source=';//线上地址
        }

        //免登进入购买套餐页面
        $param = array(
			'biz_id' => base64_encode(APP_SOURCE),
			'entid' => $arr['entid'],
//			'ac' => md5($arr['entid'] . md5($arr['password'] . 'ShopEXUser')),
			'ac' => md5($arr['entid'] . self::getDecPassword($arr['password'])),
			't' => self::get_server_time()
        );

        $source = self::encode($param['biz_id'] . '|' . $param['entid'] . '|' . $param['ac'] . '|' . $param['t']);
        return $url . $source;
    }



    public static function check_account_status() {
        if (!defined('APP_TOKEN') || !defined('APP_SOURCE')) {
            return false;
        }
        base_kvstore::instance('sms')->fetch('account', $account);
        if (unserialize($account)) {
            $param = unserialize($account);
            if ($info = self::get_user_info($param)) {
                if ('succ' == $info->res) {
                    return $info->info->month_residual;
                }
            }
        }
         
        return false;
    }

    public static function get_resource_path() {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    }

    public static function pattern() {
        return array(
	        '+'=>'_1_',
	        '/'=>'_2_',
	        '='=>'_3_',
        );
    }

    public static function encode($str) {
        $str = base64_encode($str);
        return strtr($str, self::pattern());

    }


    public static function decode($str)
    {
        $str = strtr($str, array_flip(self::pattern()));
        return base64_decode($str);
    }

    /**
     *
     * 发送短信过程中使用多进程
     * 主进程作为调度器，子进程负责短信发送
     *
     */
    public function send_fanout_multi_process($arr, $content) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die("could not fork\n");
        }
        elseif ($pid) {
            self::$sumProcess++;
            echo str_pad(">>> [Process/Sum ".self::$sumProcess."/".self::$maxProcess."]", 30);
            echo "Send Sms\n";
            if (self::$sumProcess >= self::$maxProcess) {
                pcntl_wait($status);
                self::$sumProcess--;
            }
            return $pid;
        }
        else {
            self::send($arr, $content, 'fan-out');
            exit();
        }
    }

    public static function hasRegister() {
        base_kvstore::instance('sms')->fetch('hasRegister', $hasRegister);
        return $hasRegister;
    }

    public static function utf8_strlen($string = null) {
        preg_match_all("/./us", $string, $match);
        return count($match[0]);
    }

    public static function get_account() {
        base_kvstore::instance('sms')->fetch('account', $account);
        if (!unserialize($account)) {
            return false;
        }
         
        $param = unserialize($account);
        return $param;
    }

    public static function setLog($writeLog = false) {
        self::$writeLog = $writeLog;
    }

    /**
     *
     * 记录特殊的短信日志，包括短信包含禁词，手机号码不正确。
     * @param int $errno, 值为-3(包含禁词)或-4(手机号码不正确)
     * @param array $content, 一维数组
     */
    public static function writeSpecialLog($errno, $content) {
        $batchno = $errno;	//-3(包含禁词)或-4(手机号码不正确)
        $status = 0;
        $msg = '';

        $logObj = app::get('sms')->model('log');
        foreach ($content['phones'] as $mobile) {
            $log = array(
				'batchno' => $batchno,
				'mobile' => $mobile,
				'content' => $content['content'],
				'status' => $status,
				'msg' => $msg,
				'sendtime' => time()
            );
            $logObj->insert($log);
        }
    }

    /**
     *
     * 记录短信发送日志
     * @param json array $content
     * @param mix type $result
     * 如果请求发送短信api失败, $result为false
     * 如果请求成功，则为api返回的json格式的结果
     */
    public static function writeLog($content, $result) {
        $logObj = app::get('sms')->model('log');
        $content = json_decode($content);
        if (false === $result) {
            //			特殊批次号，api请求不成功时为-1
            $batchno = -1;
            $status = 0;
            $msg = '请求api失败';
        }
        else {
            $result = json_decode($result);
            $batchno = ('succ' == $result->res) ? $result->info->msgid : -2;	//请求api成功但是发送失败的时候，批次号记为-2
            $status = ('succ' == $result->res) ? 1 : 0;
            $msg = ('succ' == $result->res) ? '' : (is_object($result->info) ? $result->info->msg : $result->info);
        }

        foreach ($content as $value) {
            $mobiles = explode(",", $value->phones);
            foreach ($mobiles as $mobile) {
                $log = array(
					'batchno' => $batchno,
					'mobile' => $mobile,
					'content' => $value->content,
					'status' => $status,
					'msg' => $msg,
					'sendtime' => time()
                );
                $logObj->insert($log);
            }
        }
    }
    public static function isMobilePhone($num) {
        $pattern = '/^1\d{10}$/';
        if (preg_match($pattern, $num)) {
            return true;
        }
        else {
            return false;
        }
    }
}