<?php

/**
 * 旺旺客户咨询信息
 *
 */
class taocrm_wangwangjingling_chat_log
{
	private static $exitsUname = null;
	private static $model = array();
	private static $taobaoapi = null;
	private $taobaoUidPre = 'cntaobao';
	private $shopNicks = '';
	public $appkey = '';
	public $secretKey = '';
	public $format = 'xml';
	private $start_date = '';
	private $end_date = '';

	//    public function __construct($params = array())
	//    {
	//        if (isset($params['start_date']) && $params['start_date']) {
	//            $this->start_date = $params['start_date'];
	//        }
	//        else {
	//            $this->start_date = date("Y-m-d", strtotime("-1 day"));
	//        }
	//        if (isset($params['end_date']) && $params['end_date']) {
	//            $this->end_date = $params['end_date'];
	//        }
	//        else {
	//            $this->end_date = date("Y-m-d", strtotime("-1 day"));
	//        }
	//    }

	public function __init($params = array())
	{
		if (isset($params['start_date']) && $params['start_date']) {
			$this->start_date = $params['start_date'];
		}
		else {
			$this->start_date = date("Y-m-d", strtotime("-1 day"));
		}
		if (isset($params['end_date']) && $params['end_date']) {
			$this->end_date = $params['end_date'];
		}
		else {
			$this->end_date = date("Y-m-d", strtotime("-1 day"));
		}
	}

	public function setStartDate($date = '')
	{
		if ($date == '') {
			$date = date("Y-m-d");
		}
		$this->start_date = $date;
	}

	public function setEndDate($date = '')
	{
		if ($date == '') {
			$date = date("Y-m-d");
		}
		$this->end_date = $date;
	}

	public function run($params = array())
	{
		$this->__init($params);
		//        echo "开始时间：" . $this->start_date . "<br />";
		//        echo "结束时间：" . $this->end_date . "<br />";
		$wangwangShopInfo = $this->getWangWangShopInfo();
		//        echo "<pre>";
		//        echo "子旺旺帐号<br />";
		//        print_r($wangwangShopInfo);
		//        echo "<pre>";
		if ($wangwangShopInfo) {
			$this->checkDate();
			$this->shopNicks = $this->getNicks();
			$this->setChatRecords($wangwangShopInfo);
			if (self::$exitsUname) {
				//清除缓存
				self::$exitsUname = null;
			}
		}
	}

	private function checkDate()
	{
		if (strtotime($this->start_date) > strtotime($this->end_date)) {
			$this->end_date = $this->start_date;
		}
		//        echo "<pre>";
		//        echo "检查日期";
		//        echo "开始时间" . $this->start_date . "<br />";
		//        echo "结束时间" . $this->end_date . "<br />";
		//        echo "</pre>";
	}

	/**
	 * 获得聊天记录集合
	 */
	private function setChatRecords($wangwangShopInfo)
	{
		foreach ($wangwangShopInfo as $v) {
			$this->setChatRecord($v);
		}
	}

	/**
	 * 设置聊天记录
	 */
	private function setChatRecord($data)
	{
		$apiParams = array(
            'chat_id' => 'cntaobao'. $data['nick'],
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
			'method'=>'store.wangwang.eservice.chatpeers.get',
			'to_node_id'=>$this->shopNicks[$data['shop_id']]['node_id']
		);
		$result = kernel::single('taocrm_matrixapi')->execute($apiParams,$data['shop_id']);
			
		if (is_object($result)) {
			$result = $this->object_to_array($result);
		}
		if (isset($result['count']) && $result['count'] > 0) {
			$this->saveMembers($data['shop_id'], $result['chatpeers']['chatpeer'],$data['nick']);
		}
	}

	private function saveMembers($shop_id, $data,$seller_nick)
	{
		foreach ($data as $k => $v) {
			//查询本次是否遍历过
			if (!isset(self::$exitsUname[$shop_id][$v['date']][$v['uid']])) {
				$this->saveMember($shop_id, $v,$seller_nick);
				self::$exitsUname[$shop_id][$v['date']][$v['uid']] = true;
			}
		}
	}

	private function saveMember($shop_id, $data,$seller_nick)
	{
		$model = $this->getModelObj('taocrm', 'wangwang_shop_chat_log');
		if (strpos($data['uid'], $this->taobaoUidPre) === 0) {
			$uname = substr($data['uid'], strlen($this->taobaoUidPre));
		}
		else {
			$uname = $data['uid'];
		}
		$time = strtotime($data['date']);
		$filter = array();
		$filter = array('shop_id' => $shop_id, 'uname' => $uname, 'chat_date' => date("Y-m-d H:i:s", $time),'seller_nick'=>$seller_nick);
		$result = $model->dump($filter);
		if ($result) {
			//更新信息
		}
		else {
			//查询客户表中ID
			$member_id = $this->getMembnerIdByUnameAndShopId($uname, $shop_id);
			$filter['member_id'] = $member_id;
			$filter['chat_date'] = $time;
			$model->insert($filter);
		}
		$case_model = $this->getModelObj('taocrm', 'member_caselog');
        $case_info = array(
                'member_id'     => $member_id,
                'title'         => $filter['chat_date'],
                'order_bn'      => '',
                'content'       => '',
                'status'        => $this->_get_casecate_by_name ('完成',4),
                'source'        => $this->_get_casecate_by_name('客户咨询',3),
                'is_finish'     => 1,
                'customer'      => $uname,
                'buyer_nick'    => '',
                'seller_nick'   => $seller_nick,
                'service_nick'  => '',
                'category'      => 0,
                'media'         => $this->_get_casecate_by_name('旺旺',1),
                'create_time'   => time(),
                'modified_time' => time(),
                'agent'         => 'admin',
                'begin_time'    => null,
                'end_time'      => null,
                'alarm_time'    => null,
                'alarm_user_id' => null,
                'shop_id'       => $shop_id,
        );
        $rs = $case_model->insert($case_info);
	}

    function _get_casecate_by_name($name,$type)
    {
        $case_cate_model = $this->getModelObj('taocrm', 'member_caselog_category');
        if(!$this->_cate_list) 
        {
            $this->_cate_list = $case_cate_model->getList('*');
        }
        foreach($this->_cate_list as $cate)
        {
            if($cate['category_name'] == $name && $cate['type'] == $type)
            {
                return $cate; 
            }
        }
        $case_info = array(
                'category' => $name,
                'type' => $type,
                'create_time' => $time,
                'status' => 1,
        );
        $rs = $case_cate_model->insert($case_info);
        if($rs) 
        {
            return $case_info;
        }else{
            return false;
        }
	}

	/**
	 * 获得客户ID
	 */
	public function getMembnerIdByUnameAndShopId($uname, $shop_id)
	{
		$model = $this->getModelObj('taocrm', 'wangwang_shop_chat_log');
		$sql = "SELECT `sdb_taocrm_members`.`member_id` FROM `sdb_taocrm_members` INNER JOIN `sdb_taocrm_member_analysis` ON `sdb_taocrm_members`.`member_id` = `sdb_taocrm_member_analysis`.`member_id`
                WHERE `sdb_taocrm_members`.`uname` = '{$uname}' AND `sdb_taocrm_member_analysis`.`shop_id` = '{$shop_id}' 
                GROUP BY `sdb_taocrm_members`.`member_id` ASC";

		$rs = $model->db->select($sql);
		//        $rs = kernel::database()->select($sql);
		$member_id = 0;
		if ($rs) {
			$member_id = $rs[0]['member_id'];
		}
		return $member_id;
	}

	private function taobao_wangwang_eservice_chatpeers_get($params)
	{
		$api = $this->getTaobaApi();
		$apiParams = array(
            'chat_id' => 'cntaobao'. $params['chat_id'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
            'charset' => 'utf-8'
            );
            $method = 'taobao.wangwang.eservice.chatpeers.get';
            $result = $api->execute($method, $apiParams, $params['session']);
            return $result;
	}

	/**
	 * 获得子旺旺信息
	 * Enter description here ...
	 */
	public function getWangWangShopInfo()
	{
		$model = $this->getModelObj('taocrm', 'wangwang_shop');
		$filter = array('status' => 1);
		$info = $model->getList('*', $filter);
		return $info;
	}

	/**
	 * 获得主旺旺nick信息
	 */
	public function getNicks()
	{
		$shopModel = $this->getModelObj('ecorder', 'shop');
		$filter = array('active' => 'true', 'shop_type' => 'taobao');
		$shopInfo = $shopModel->getList('*', $filter);
		$nickInfo = array();
		foreach ($shopInfo as $k => $v) {
			if ($v['addon']['session'] && $v['addon']['nickname']) {
				$nickInfo[$v['shop_id']] = array('session' => $v['addon']['session'], 'nickname' => $v['addon']['nickname'],'node_id'=>$v['node_id']);
			}

		}
		//        echo "<pre>";
		//        echo "获取店铺SESSION编码";
		//        print_r($nickInfo);
		//        echo "</pre>";
		return $nickInfo;
	}

	/**
	 * 淘宝API句柄
	 * Enter description here ...
	 */
	private function getTaobaApi()
	{
		if (self::$taobaoapi == null) {
			self::$taobaoapi = new taocrm_taobaoapi();
			self::$taobaoapi->appkey = $this->appkey;
			self::$taobaoapi->secretKey = $this->secretKey;
			self::$taobaoapi->format = $this->format;
		}
		return self::$taobaoapi;
	}

	private function getModelObj($appName, $modelName)
	{
		if (!isset(self::$model[$appName][$modelName]) && self::$model[$appName][$modelName] == '') {
			self::$model[$appName][$modelName] = app::get($appName)->model($modelName);
		}
		return self::$model[$appName][$modelName];
	}

	function object_to_array($obj)
	{
		$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		foreach ($_arr as $key => $val) {
			$val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
			$arr[$key] = $val;
		}
		return $arr;
	}

	public function iteratorToArray($obj) {
		$array = array();
		foreach ($obj as $k => $v) {
			if (is_object($v) || is_array($v)) {
				$array[$k] = $this->iteratorToArray($v);
			}
			else {
				$array[$k] = $v;
			}
		}
		return $array;
	}
}
