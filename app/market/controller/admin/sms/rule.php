<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
 
class taocrm_ctl_admin_sms_rule extends desktop_controller {
	var $workground = 'taocrm.sales';
	 
	public function index() {
		$this->finder('taocrm_mdl_sms_rule_types', array(
			'title' => '短信通知设置',
			'use_buildin_filter' => true,
			'use_buildin_recycle' => false,
		));
	}
	
	public function edit() {
		$this->_edit();
	}
	
	protected function _edit() {
		$data = $_POST;
		$this->begin();
		if (!is_numeric($data['rule_time']) || $data['rule_time'] < 1) {
			$this->end(false,app::get('b2c')->_('时间需要为整数并且大于(含)1小时'));
		}
		else {
			$shopRs = app::get('ecorder')->model('shop')->getList();
			$shops = array();
			foreach ($shopRs as $value) {
				$shops[] = $value['shop_id'];
			}
			if (isset($data['allShops']) && $data['allShops']) {
				$enabledShops = $shops;
				$disabledShops = array();
			}
			else {
				$enabledShops = isset($data['shops']) ? $data['shops'] : array();
				$disabledShops = array_diff($shops, $enabledShops);				
			}
				
			$ret1 = app::get('taocrm')->model('sms_rule_types')->update(array(
				'type_status' => $data['status'] ? 'enable' : 'disable',
				'type_description' => $data['descriptioin'],
			), array('type_id' => $data['ruleTypeId']));
			
			$ret2 = true;
			if ($disabledShops) {
				$ret2 = app::get('taocrm')->model('sms_rules')->update(array(
					'rule_time' => $data['rule_time'],
					'rule_status' => 0,
				), array(
					'type_id' => $data['ruleTypeId'],
					'shop_id|in' => $disabledShops
				));			
			}
	
			$ret3 = true;
			if ($enabledShops) {
				$ret3 = app::get('taocrm')->model('sms_rules')->update(array(
					'rule_time' => $data['rule_time'],
					'rule_status' => 1,
				), array(
					'type_id' => $data['ruleTypeId'],
					'shop_id|in' => $enabledShops
				));			
			}
			
			if ($ret1 && $ret2 && $ret3) {
				$this->end(true,app::get('b2c')->_('操作成功'));
			}
			else {
				$this->end(false,app::get('b2c')->_('操作失败'));
			}			
		}
	}
	
	public function themeEdit($ruleTypeId) {		
		$this->_buildShops($ruleTypeId);
		
		$ruleType = app::get('taocrm')->model('sms_rule_types')->dump(array('type_id' => $ruleTypeId));
		$rules = app::get('taocrm')->model('sms_rules')->getList('*', array('type_id' => $ruleTypeId));
		$rule = $rules[0];		
		
		$shops = array();
		$allChecked = true;
		foreach ($rules as &$value) {
			$shop = app::get('ecorder')->model('shop')->dump(array('shop_id' => $value['shop_id']));
			if (!$shop) {
				continue;
			}
			$shop['rule_status'] = $value['rule_status'];
			if (!$value['rule_status']) {
				$allChecked = false;
			}
			$shops[] = $shop;
		}
		$this->pagedata['data']['ruleType'] = $ruleType;
		$this->pagedata['data']['rule'] = $rule;
		$this->pagedata['data']['shops'] = $shops; 
		$this->pagedata['data']['allChecked'] = $allChecked;
		$this->display('admin/sms/editRule.html');
	}
	
	public function themeChooseTemplate($ruleTypeId) {
		$app = app::get('taocrm');		
		$msgTemplateObj = $app->model('message_themes');			
		$templateList = $msgTemplateObj->getList();
        $this->pagedata['data']['templateList'] = $templateList;
        
        $ruleObj = $app->model('sms_rules');
        $templateId = $ruleObj->dump(array('type_id' => $ruleTypeId));
        $this->pagedata['data']['templateId'] = $templateId['theme_id'];
        $this->pagedata['data']['ruleTypeId'] = $ruleTypeId;
        $this->display('admin/sms/chooseTemplate.html');
	}
	
	public function chooseTemplate() {
		$app = app::get('taocrm');
		$rulesObj = $app->model('sms_rules');
		$data = $_POST;
		
		$this->begin();
		$this->_buildShops($data['ruleTypeId']);
		$templateId = isset($data['template']) && intval($data['template']) > 0 ? intval($data['template']) : 0;
		$ret = false;
		if ($templateId) {
			$filter = array('type_id' => $data['ruleTypeId']);
			$ret = $rulesObj->update(array(
				'theme_id' => $templateId
			), $filter);
		}
		
        if($ret){
            $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
            $this->end(false,app::get('b2c')->_('操作失败'));
        }
	}
	
	private function _buildShops($ruleTypeId) {
		$shops = app::get('ecorder')->model('shop')->getList();
		$rules = app::get('taocrm')->model('sms_rules')->getList('*', array('type_id' => $ruleTypeId));
	
		$existShops = array();
		foreach ($rules as $rule) {
			$existShops[] = $rule['shop_id'];
		}
		
		foreach ($shops as $shop) {
			if (!in_array($shop['shop_id'], $existShops)) {
				$data = array(
					'type_id' => $ruleTypeId,
					'shop_id' => $shop['shop_id'],
					'rule_time' => 0,
					'rule_status' => 0,
					'theme_id' => 0,
				);
						
				app::get('taocrm')->model('sms_rules')->insert($data);
			}
		}
	}
}