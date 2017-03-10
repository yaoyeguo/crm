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
 
class taocrm_finder_sms_rules {
	public static $templates = array(); 
	
	
	public $column_edit = '编辑';
	public $column_edit_order = 2;
	public function column_edit($row) {
		return '<a href="index.php?app=taocrm&ctl=admin_sms_rule&act=themeEdit&p[0]='.$row['type_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑规则').'\', width:680, height:350}">编辑</a>';
	}
	
	public $column_smsTemplate = '短信模板';
	public $column_smsTemplate_width = 200;
	public $column_smsTemplate_order = 40;
	public function column_smsTemplate($row) {
		$msgTemplate = $this->_getTemplateByRuleType($row);
		return $msgTemplate['theme_title'];
	}
	
	public $column_smsTemplateOperation = '模板操作';
	public $column_smsTemplateOperation_order = 41;
	public function column_smsTemplateOperation($row) {
		$msgTemplate = $this->_getTemplateByRuleType($row);
		if ($msgTemplate) {
			$button = '<a href="index.php?app=taocrm&ctl=admin_sms_rule&act=themeChooseTemplate&p[0]='.$row['type_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('选择模板').'\', width:680, height:280}">'.'修改'.'</a>';
		}
		else {
			$button = '<a href="index.php?app=taocrm&ctl=admin_sms_rule&act=themeChooseTemplate&p[0]='.$row['type_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('选择模板').'\', width:680, height:280}">'.'添加'.'</a>';	
		}
		return $button;
	}
	
	public $column_smsTemplateView = '预览';
	public $column_smsTemplateView_order = 42;
	public function column_smsTemplateView($row) {
		$msgTemplate = $this->_getTemplateByRuleType($row);
		if ($msgTemplate) {
			$params = array(
				'uname' => '张三',
				'coupon' => '满100送5元',
				'name' => '李四的店铺',
			);
			
			$smsTemplate = $msgTemplate['theme_content'];
			
			$button = '<a href="javascript:void(0);" title="'
					. taocrm_messenger_smsTemplate::convertTemplateToContent($params, $smsTemplate)
					. '">' . '预览' . '</a>';
		}
		else {
			$button = '';
//			$button = '<a href="index.php?app=taocrm&ctl=admin_sms_rule&act=themeChooseTemplate&p[0]='.$row['type_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('选择模板').'\', width:680, height:280}">'.'预览'.'</a>';	
		}
		return $button;
	}	
	
	private function _getTemplateByRuleType($row) {
		if (!self::$templates[$row['type_id']]) {
			$app = app::get('taocrm');
			$ruleObj = $app->model('sms_rules');
			$filter = array('type_id' => $row['type_id']);
			$rule = $ruleObj->dump($filter);
			
			$msgTemplateObj = $app->model('message_themes');
			$filter = array('theme_id' => $rule['theme_id']);
			self::$templates[$row['type_id']] = $msgTemplateObj->dump($filter);
		}
		
		return self::$templates[$row['type_id']];
	}
} 