<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class market_api_abstract {
    //会话SESSION中每二句话的最小间隔时间，以秒为单位
    const SESSION_INTERVAL = 1800;

    /**
     * 客服数据
     *
     * @var array
     */
    protected $serviceCats = null;
    /**
     * 客户旺旺ID数组
     *
     * @var Arrays
     */
    protected $clientCats = array();
    /**
     * 中心数据表中对应的数据
     *
     * @var Array
     */
    protected $centerInfo = array();
    /**
     * HTTP访问对像
     *
     * @var Object httpclient
     */
    private static $httpObejct = null;

    /**
     * 转换对话数据
     *
     * @param $msg
     * @param $startTime
     * @param $endTime
     * @return array
     */
    protected function toSession($msg, $startTime, $endTime) {

        //初始化数据
        $catSessions = array();
        $s_cats = array();
        foreach ($msg as $s_cat => $v) {

            $s_cats[] =  $this->getServiceCatId($s_cat);
        }
        $preCatSessions = $this->initPrevMsgData($s_cats, $startTime);

        //开始本次数据处理
        foreach ($msg as $s_cat => $s_cat_msgs) {

            $s_cat_id = $this->getServiceCatId($s_cat);

            if ($s_cat_id == 0) {
                //如果客服在表中不存在，跳过
                continue;
            }
            if (!isset($catSessions[$s_cat_id])) {

                $catSessions[$s_cat_id] = array();
            }

            foreach ($s_cat_msgs as $c_cat => $s_c_msgs) {

                $c_cat_id = $this->getClientCatId($c_cat);
                if ($c_cat_id == 0) {
                    //如果客户在表中不存在，跳过
                    continue;
                }

                //开始分段数据
                if (isset($preCatSessions[$s_cat_id][$c_cat_id])) {
                    //有未结束
                    $catSessions[$s_cat_id][$c_cat_id] = $this->mergeMsg($s_c_msgs, $startTime, $endTime, $preCatSessions[$s_cat_id][$c_cat_id]);
                } else {
                    //无
                    $catSessions[$s_cat_id][$c_cat_id] = $this->mergeMsg($s_c_msgs, $startTime, $endTime);
                }
            }
        }

        return $catSessions;
    }

    /**
     * 合并对话为一个个的片段
     *
     * @param Array $msgs 会话内容
     * @param Integer $startTime
     * @param Integer $endTime
     * @param Array $preSession 上一次没结束的会话
     * @return Array
     */
    private function mergeMsg($msgs, $startTime, $endTime, $preSession = array()) {

        $resultSession = array();
        if (!empty($preSession)) {
            //取最后的消息时间并初始化
            $curSession = $preSession;
            $curLastMsgTime = 0;
            foreach ($preSession['msgs'] as $key => $msg) {

                $tmpTime = strtotime($msg['time']);
                $curLastMsgTime = $curLastMsgTime < $tmpTime ? $tmpTime : $curLastMsgTime;
            }
        } else {

            $curLastMsgTime = 0;
            $curSession = array('sessionId' => 0, 'msgs' => array());
        }

        //对当前取到的内容进行处理
        foreach ($msgs as $msg) {

            $tmpTime = strtotime($msg['time']);
            if ($tmpTime <= $startTime || $tmpTime > $endTime) {
                //如会话时间小时开始时间，跳过
                continue;
            }

            if (($tmpTime - self::SESSION_INTERVAL) > $curLastMsgTime && $curLastMsgTime > 0) {
                //要重新分会话
                $resultSession[] = $curSession;
                $curLastMsgTime = $tmpTime;
                $curSession = array('sessionId' => 0, 'msgs' => array($msg));
            } else {
                //累加到上一第会话
                $curLastMsgTime = $tmpTime;
                $curSession['msgs'][] = $msg;
            }
        }

        //处理最后一条
        if (!empty($curSession['msgs'])) {

            $resultSession[] = $curSession;
        }

        return $resultSession;
    }

    /**
     * 创建所有客户IM号的ID
     *
     * @param Array $cats 所有IM号
     * @return void
     */
    protected function initClientCatId($cats) {

        if (empty($cats)) {

            return;
        }

        $c_cats = array();
        $c_crc32s = array();
        foreach ($cats as $catName => $time) {

            $c_crc32 = sprintf('%u', crc32($catName));
            $c_crc32s[] = $c_crc32;
            $c_cats[$catName] = array('customer_ww_nick' => $catName, 'crc32' => $c_crc32);
        }

        $db = $this->getDB();

        $res = $db->query(sprintf("SELECT * FROM sdb_market_customer WHERE crc32 IN (%s)", join(',', $c_crc32s)));
        //去除已有的IM号
        while ($row = $db->fetchArray($res)) {
            if (isset($c_cats[$row['customer_ww_nick']])) {
                $this->clientCats[$row['customer_ww_nick']] = $row['customer_id'];
                unset($c_cats[$row['customer_ww_nick']]);
            }
        }

        if (!empty($c_cats)) {
            //有要新增的客户ID
            $c_crc32s = array();
            $db->query('START TRANSACTION;');
            foreach ($c_cats as $cat) {

                $db->query(sprintf("INSERT INTO sdb_market_customer SET customer_ww_nick='%s', crc32='%s';",
                                mysql_escape_string($cat['customer_ww_nick']), $cat['crc32']));
                $c_crc32s[] = $cat['crc32'];
            }
            $db->query('COMMIT; ');

            $res = $db->query(sprintf("SELECT * FROM sdb_market_customer WHERE crc32 IN (%s)", join(',', $c_crc32s)));
            while ($row = $db->fetchArray($res)) {
                $this->clientCats[$row['customer_ww_nick']] = $row['customer_id'];
            }
        }

		$db->close();
		unset($db);
    }

    /**
     * 获取客户旺旺的数字ID
     *
     * @param String $s_cat 客服旺旺ID
     * @return Integer
     */
    private function getClientCatId($c_cat) {

        if (empty($this->clientCats)) {

            return 0;
        } else {

            if (isset($this->clientCats[$c_cat])) {

                return $this->clientCats[$c_cat];
            } else {

                return 0;
            }
        }
    }

    /**
     * 获取客服旺旺的数字ID
     *
     * @param String $s_cat 客服旺旺ID
     * @return Integer
     */
    private function getServiceCatId($s_cat) {

        if ($this->serviceCats === null) {

            $this->getServiceCats();
        }
        foreach ($this->serviceCats as $id => $catName) {

            if ($catName == $s_cat) {

                return $id;
            }
        }

        return 0;
    }

    /**
     * 初始化上次会话中的数据
     *
     * @param String $s_cat 客服旺旺
     * @param Integer $startTime 本次下拉旺旺的开始时间
     * @return void
     */
    private function initPrevMsgData($s_cats, $startTime) {

        $preSession = array();
        $msgHash = array();

        if (empty($s_cats) || !is_array($s_cats)) {

            return $preSession;
        }

        $limitTime = $startTime - self::SESSION_INTERVAL;
        $sessionIds = array();
        $session = array();
        $db = $this->getDB(); 
        $res = $db->query(sprintf("SELECT * FROM sdb_market_session WHERE ww_s_id in (%s) AND end_time > $limitTime and end_time < $startTime ", join(',', $s_cats)));
        while ($row = $db->fetchArray($res)) {

            $sessionIds[] = $row['id'];
            $session[$row['id']] = $row;
        }

        if (!empty($sessionIds)) {

            $res = $db->query(sprintf('SELECT * FROM sdb_market_session_content WHERE id in(%s)', join(',', $sessionIds)));
            while ($row = $db->fetchArray($res)) {

                $sessionId = $row['id'];
                $s_id = $session[$sessionId]['ww_s_id'];
                $c_id = $session[$sessionId]['ww_c_id'];
                if (!isset($preSession[$s_id][$c_id]['sessionId'])) {
                    if ($session[$preSession[$s_id][$c_id]['sessionId']]['end_time'] < $session[$sessionId]['end_time']) {
                        $preSession[$s_id][$c_id] = array('sessionId' => $sessionId, 'msgs' => json_decode($row['content'] ,true));
                    }
                } else {
                    $preSession[$s_id][$c_id] = array('sessionId' => $sessionId, 'msgs' => json_decode($row['content'] ,true));
                }
            }
        }
        
        $db->close();
        unset($db);
        return $preSession;
    }

    /**
     * 从数据库记录中获取数据库连接信息
     *
     * @param Array $centerInfo 中心数据库的一条记录
     * @return void
     */
    public function setCenterInfo($centerInfo) {

        if (is_array($centerInfo)) {

            $this->centerInfo = $centerInfo;
        } else {
            $this->centerInfo = array();
        }
    }

    /**
     * 获取当前Server中的所有旺旺的ID
     *
     * @return Array
     */
    protected function getServiceCats() {

        if ($this->serviceCats === null) {

            $this->serviceCats = array();
            $db = $this->getDB();
            //$res = $db->query("SELECT account_id,login_name FROM sdb_pam_account WHERE disabled='false'");
            $res = $db->query("SELECT ww_s_id,ww_s_name FROM sdb_market_service");
            while ($row = $db->fetchArray($res)) {

                //if ($row['login_name'] <> 'admin' && $row['login_name'] <> 'nobody')
                //    $this->serviceCats[$row['account_id']] = $row['login_name'];
                if ($row['ww_s_name'] <> 'admin' && $row['ww_s_name'] <> 'nobody')
                    $this->serviceCats[$row['ww_s_id']] = $row['ww_s_name'];
            }
            $db->close();
            unset($db);
        }

        return $this->serviceCats;
    }

    /**
     * 获取数据库链接
     *
     * @param void
     * @return object
     */
    function getDB() {

        if (!class_exists('database')) {

            require_once(LIB_DIR . 'db/mysql.php');
        }

        $db = new database();
        $db->connect($this->centerInfo['db_host'] . ':' . $this->centerInfo['db_port'],
                $this->centerInfo['db_user'], $this->centerInfo['db_passwd'],
                $this->centerInfo['db_name']);

        return $db;
    }

    /**
     * XML 转为数组
     * @param String $xml
     * @return Array
     */
    public function xml2array($xml) {
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            $arr = array();

            for ($i = 0; $i < $count; $i++) {
                $key = $matches[1][$i];
                $val = $this->xml2array($matches[2][$i]);

                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        if (!array_key_exists(0, $arr[$key])) {
                            $arr[$key] = array($arr[$key]);
                        }
                    } else {
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                } else {
                    $arr[$key] = $val;
                }
            }

            return $arr;
        } else {
            return $xml;
        }
    }

}
