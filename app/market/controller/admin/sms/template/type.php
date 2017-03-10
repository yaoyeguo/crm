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

class market_ctl_admin_sms_template_type extends desktop_controller {
	public function index() {
		$this->finder('market_mdl_sms_template_type', array(
			'title' => '模板分类',
			'actions' => array(
				array(
					'label' => '添加模板分类',
					'href' => 'index.php?app=market&ctl=admin_sms_template_type&act=themeAdd',
					'target' => 'dialog::{width:680,height:250,title:\'添加模板分类\'}',
				)
			),
			'use_buildin_filter' => true,
			'use_buildin_recycle' => false,
		));
	}
	
	public function themeAdd() {
		$this->pagedata['data']['action'] = 'add';
		$this->_themeEdit();
	}
	
	public function themeEdit($groupId) {
		$this->pagedata['data']['action'] = 'edit';
		$this->pagedata['data']['group'] = $this->app->model('sms_template_type')->dump(array('type_id' => $groupId));
		$this->_themeEdit($groupId);
	}
	
	private function _themeEdit($groupId = 0) {
		$this->display('admin/sms/template/themeGroup.html');
	}
	
	public function save() {
		$sourceAction = $_POST['sourceAction'];
		$this->begin();
		$ret = false;
		if ('add' == $sourceAction) {
			$title = $_POST['title'];
			$description = $_POST['description'];
			if (!$title) {
				$this->end(false, '请输入模板分类名称');
			}
			else if (!$description) {
				//$this->end(false, '请输入模板分类备注');
			}
			$data = array(
				'title' => $title,
				'remark' => $description,
				'create_time' => time(),
			);
			$ret = $this->app->model('sms_template_type')->insert($data);
		}
		elseif ('edit' == $sourceAction) {
			$id = trim($_POST['typeId']);
			$title = trim($_POST['title']);
			$description = trim($_POST['description']);
			if (!$title) {
				$this->end(false, '请输入模板分类名称');
			}
			else if (!$description) {
				//$this->end(false, '请输入模板分类备注');
			}
			$data = array(
				'title' => $title,
				'remark' => $description,
				'create_time' => time(),
			);
			$ret = $this->app->model('sms_template_type')->update($data, array('type_id' => $id));			
		}
		if ($ret) {
			$this->end(true, '操作成功');	
		}
		else {
			$this->end(true, '操作失败');
		}
	}
	

}