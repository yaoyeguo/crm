<?php

class taocrm_wangwangjingling_shop
{
	private static $model = array();
	private static $taobaoapi = null;
	public $appkey = '';
	public $secretKey = '';
	public $format = 'json';
	/**
	 * 执行命令
	 */
	public function run($display = true)
	{
		$nickInfo = $this->getNicks();
		$subNickInfo = $this->getSubsersInfo($nickInfo);
		$result = $this->saveSubsersInfo($subNickInfo);
		return $result;
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
		return $nickInfo;
	}

	private function getModelObj($appName, $modelName)
	{
		if (!isset(self::$model[$appName][$modelName]) && self::$model[$appName][$modelName] == '') {
			self::$model[$appName][$modelName] = app::get($appName)->model($modelName);
		}
		return self::$model[$appName][$modelName];
	}

	/**
	 * 保存子旺旺账号
	 */
	private function saveSubsersInfo($subNickInfo)
	{
		if ($subNickInfo) {
			foreach ($subNickInfo as $shopId => $sub) {
				if (is_array($sub)) {
					foreach ($sub as $k => $v) {
						$this->saveSubsers($v, $shopId);
					}
				}
			}
			return 'ok';
		}
		else {
			return 'sub wangwang empty!\n';
		}

	}

	private function saveSubsers($data, $shopId)
	{
		$wangwangShopModel = $this->getModelObj('taocrm', 'wangwang_shop');
		$saveData = array();
		$saveData = $data;
		$saveData['shop_id'] = $shopId;
		$wangwangShopModel->saveInfo($saveData);
	}

	/**
	 * 获得子旺旺信息
	 */
	private function getSubsersInfo($nickInfo)
	{
		$subserrs = array();
		foreach ($nickInfo as $k => $params) {
			$apiParams = array(
            'nick_name' => $params['nickname'],
			'method' => 'store.sellercenter.subusers.get',
			'to_node_id' => $params['node_id']
			);
			$result = kernel::single('taocrm_matrixapi')->execute($apiParams,$k);
			//$result = $this->taobao_sellercenter_subusers_get($params);
			if (is_object($result)) {
				$result = $this->iteratorToArray($result);
			}
			if(empty($result))continue;
			
			$subserrs[$k] = $result['subusers']['sub_user_info'];
		}
		return $subserrs;
	}

	/**
	 * 调用淘宝子旺旺API
	private function taobao_sellercenter_subusers_get($params)
	{
		$api = $this->getTaobaApi();
		$apiParams = array(
            'nick' => $params['nickname'],
		);
		$method = 'taobao.sellercenter.subusers.get';
		$result = $api->execute($method, $apiParams, $params['session']);
		return $result;
	}
	 */

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

	/**
	 * 重置淘宝API
	 * Enter description here ...
	 */
	public function resetTaobaoApi()
	{
		self::$taobaoapi = null;
	}

	/**
	 * 设置APPKEY
	 */
	public function setAppkey($appkey)
	{
		$this->appkey = $appkey;
	}

	/**
	 * 设置secretKey
	 */
	public function setSecretKey($secretKey)
	{
		$this->secretKey = $secretKey;
	}

	/**
	 * 设置数据格式
	 */
	public function setFormat($format)
	{
		$this->format = $format;
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
