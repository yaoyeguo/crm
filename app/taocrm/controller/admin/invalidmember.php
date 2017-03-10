<?php
class taocrm_ctl_admin_invalidmember extends desktop_controller{
	var $workground = 'taocrm.member';
	var $token = 'iloveshopex';
	protected static $middleware = '';
	protected static $shopObj = '';

	public function index()
	{
		//        if(!isset($_GET['view'])){
		//            $this->redirect('index.php?app=taocrm&ctl=admin_member&act=index&view=1');
		//            exit;
		//        }

		$title = '无效手机客户列表';
		$actions = '';
		$baseFilter = array('invalidType'=>'mobile');
		$shops = $this->getShopFullIds();
		$view = (isset($_GET['view'])) ? max(0, intval($_GET['view'])) : 0;
		$baseFilter['shop_id'] = $shops[$view];
		if ($baseFilter['shop_id'] == '') {
			if  (isset($_GET['shop_id'])) {
				$baseFilter['shop_id'] = $_GET['shop_id'];
				unset($_GET['shop_id']);
			}
			else {
				$baseFilter['shop_id'] = $shops[0];
			}
			//$shopId =$this->getShopId();
		}
		//        if ($baseFilter['shop_id'] == '') {
		//            $baseFilter['shop_id'] = trim($_GET['shop_id']);
		//        }
		//$baseFilter['methodName'] = 'SearchMemberAnalysisList';
		$baseFilter['methodName'] = 'SearchInvalidMemberAnalysisByShop';
		$baseFilter['packetName'] = 'ShopMemberAnalysis';
		//$actions = $this->_action();
		$this->finder('taocrm_mdl_middleware_member_analysis',array(
            'title'=> $title,
            //'actions' => $actions,
            'base_filter'=>$baseFilter,
		//去掉默认排序
        	'orderBy' => '',
		//'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
		//暂时去掉高级筛选功能
		//'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
		));
	}

	protected function getShopFullIds()
	{
		$model = $this->getShopObj();
		$shopList = $model->getList('shop_id,name');
		$shops = array();
		foreach ((array)$shopList as $v) {
			$shops[] = $v['shop_id'];
		}
		return $shops;
	}

	public function _views()
	{
		$baseFilter = array();
		$shopObj = $this->getShopObj();
		$shopList = $shopObj->getList('shop_id,name');
		$sub_menu = array();
		foreach($shopList as $shop){
			$sub_menu[] = array(
                'label' => $shop['name'],
                'filter' => array('shop_id' => $shop['shop_id']),
                'optional' => false,
			);
		}

		$result = $this->getDBAllShopInfo();
		$i = 0;
		foreach($sub_menu as $k => $v){
			if (!IS_NULL($v['filter'])){
				$v['filter'] = array_merge($v['filter'], $baseFilter);
			}
			$count = $result[$v['filter']['shop_id']]['MemberCount'];
			$sub_menu[$k]['addon'] = $count;
			if (!isset($_GET['view']) && $count > 0) {
				$this->redirect('index.php?app=taocrm&ctl=admin_invalidmember&act=index&view='. $i++);
				exit;
			}
			$sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
			//            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
		}
		 
		return $sub_menu;
	}

	protected function getShopObj()
	{
		if (self::$shopObj == '') {
			self::$shopObj = &app::get(ORDER_APP)->model('shop');
		}
		return self::$shopObj;
	}

	public function addTag(){
		$oTag= &$this->app->model('member_tag');
		$this->pagedata['taglist'] = $oTag->getTagList();
		$this->pagedata['member_ids'] = implode(',', $_POST['id']);

		//获取当前客户的标签
		$oTag= &$this->app->model('member_tag');
		$tags = $oTag->getTagsByMember($_POST['id']);
		$this->pagedata['tags'] = '0,'.implode(',',$tags).',0';

		//var_dump( $this->pagedata['taglist']);exit;
		$this->display('admin/member/tag/add.html');
	}

	public function saveMemberTag(){
		$oTag= &$this->app->model('member_tag');
		$this->begin();
		$member_ids = trim($_POST['member_ids']);
		$tag_ids = trim($_POST['tag_ids']);
		$old_tag_ids = trim($_POST['old_tag_ids']);
		if($member_ids){
			$member_ids = explode(',', $member_ids);
			$tag_ids ? $tag_ids=explode(',', $tag_ids) : $tag_ids=false;
			$old_tag_ids = explode(',', $old_tag_ids);
			$oTag->saveMemberTag($member_ids, $tag_ids, $old_tag_ids);
			$this->end(true,app::get('taocrm')->_('操作成功'));
		}else{
			$this->end(false,app::get('taocrm')->_('没有选择客户'));
		}
	}

	protected function _action()
	{
		$actions =  array();

		$shopObj = $this->getShopObj();
		$shopList = $shopObj->getList('shop_id,name');
		foreach((array)$shopList as $v){
			$shops[] = $v['shop_id'];
		}
		$view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;

		array_push(
		$actions,
		array(
                'label'=>'创建短信活动',
                'submit'=>'index.php?app=market&ctl=admin_active_sms&act=create_active&create_source=members&send_method=sms&memlist=1&shop_id= '. trim($shops[$view]),
                'target'=>'dialog::{width:700,height:350,title:\'创建短信活动\'}'
                )
                /*
                array(
                'label'=>'创建邮件活动',
                'submit'=>'index.php?app=market&ctl=admin_active_edm&act=create_active&send_method=edm&memlist=1&shop_id= '. trim($shops[$view]),
                'target'=>'dialog::{width:700,height:350,title:\'创建邮件活动\'}'
                )*/
                );

                $a1 = array(
            'label'=>'加入短信黑名单',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=sms_blickname',
                );
                $a2 = array(
            'label'=>'加入邮件黑名单',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=edm_blickname',
                );
                $a3 = array(
            'label'=>'加入贵宾组',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=addvip',
                );
                $a4 = array(
            'label'=>'客户标签',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=addTag',
            'target'=>'dialog::{width:650,height:355,title:\'客户标签\'}'
            );

            array_push($actions, $a1, $a2, $a3, $a4);
            //array_push($actions, $a1, $a2, $a3);

            return $actions;
	}

	/**
	 * 获得所有店铺订单数量及客户数量
	 * Enter description here ...
	 */
	protected function getDBAllShopInfo()
	{
		self::$middleware = kernel::single('taocrm_middleware_connect');
		//$data = json_decode(self::$middleware->DBAllShopInfo(), true);
		$filter = array('status'=>0,'invalidType'=>'mobile');
		$data = self::$middleware->DBAllShopInfo($filter);
		return $data;
	}
}
