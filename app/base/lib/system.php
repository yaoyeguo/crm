<?php
class base_system{


	public function installInit(){
		$version_code = app::get('taocrm')->getConf('system.version_code');

		if(empty($version_code)){
			$this->setDefaultVersion('',SYSTEM_VERSION);
		}
	}

	public function setDefaultVersion($domain='', $version_code='High_Ver')
	{
		$defaultVersion = array(
            'member_nums'=>0,
            'order_nums'=>0,//不限
            'shop_nums'=>0,
		);

		$this->set_version_code($version_code, 'init');

		$this->setVersion($defaultVersion);
	}

	/**
	 * 设置菜单版本代码
	 * Base_Ver High_Ver HighAll_Ver Pro_Ver
	 */
	function set_version_code($version_code, $status="init")
	{
		app::get('taocrm')->setConf('system.version_code', $version_code);

		//更新系统菜单
		$this->update_desktop_menu();

		//非初始化调用saas接口
		if($status != 'init'){
			$api = kernel::single('taocrm_saas');
			$api->appkey = SASS_APP_KEY;
			$api->secretKey = SAAS_SECRE_KEY;
			$api->format = 'json';
			$server_name = $_SERVER['SERVER_NAME'];

			$params = array(
                'server_name' => $server_name,
                'version_code' => $version_code,
			);
			$result = $api->execute('host.change_version', $params);
			unset($api);
			if($result->success == 'true'){
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * 获取CRM版本，默认企业版
	 * Base_Ver High_Ver HighAll_Ver Pro_Ver
	 */
	public function get_version_code()
	{
		$version_code = app::get('taocrm')->getConf('system.version_code');
		if(!$version_code){
			$version_code = 'High_Ver';
		}
		return $version_code;
	}

	/**
	 * 根据version_code处理菜单
	 * Base_Ver
	 * High_Ver
	 * HighAll_Ver
	 * Pro_Ver
	 * @$menu_type : top_sub_menu,top_menu,left_pannel
	 */
	public function set_system_menu(&$menu, $menu_type='')
	{
		return false;

		$filter['Base_Ver']['top_menu'] = array(
            'market.weixin',
            'taocrm.analysis',
            'taocrm.fxmember'
            );
            $filter['Base_Ver']['top_sub_menu'] = array(
            'app=ecorder&ctl=admin_shop_lv&act=index',
            'app=ecorder&ctl=admin_shop_credit&act=index',
            'app=plugins&ctl=admin_vcard&act=index',
            'app=taocrm&ctl=admin_points_log&act=index',
            'app=ecorder&ctl=admin_analysis&act=member',
            'app=taocrm&ctl=admin_member_tag&act=index',
            'app=taocrm&ctl=admin_member_wwchat&act=index',
            'app=taocrm&ctl=admin_analysis_member&act=lose',
            'app=market&ctl=admin_weixin&act=openId',
            'app=taocrm&ctl=admin_wangwangjingling&act=index&type=1',
            'app=ecorder&ctl=admin_help_support&act=fx_support',
            );
            $filter['Base_Ver']['left_pannel'] = $filter['Base_Ver']['top_sub_menu'];

            $version_code = $this->get_version_code();
            //$version_code = 'High_Ver';
            if($version_code == 'Base_Ver'){
            	foreach($menu as $k=>$v){
            		if(in_array($v['workground'], $filter[$version_code][$menu_type])){
            			unset($menu[$k]);
            		}

            		if(in_array($v['menu_path'], $filter[$version_code][$menu_type])){
            			unset($menu[$k]);
            		}
            	}
            }

            if($version_code == 'High_Ver' or $version_code == 'HighAll_Ver'){

            }

            if($version_code == 'Pro_Ver'){

            }
	}

	public function setVersion($data)
	{
		if(isset($data['member_nums'])){
			app::get('taocrm')->setConf('system.limit.member',intval($data['member_nums']));
		}

		if(isset($data['order_nums'])){
			app::get('taocrm')->setConf('system.limit.order',intval($data['order_nums']));
		}

		if(isset($data['shop_nums'])){
			app::get('taocrm')->setConf('system.limit.shop',intval($data['shop_nums']));
		}

	}

	public function getVersion()
	{
        return array(
            'member_nums'=> app::get('taocrm')->getConf('system.limit.member'),
            'order_nums'=>app::get('taocrm')->getConf('system.limit.order'),
            'shop_nums'=>app::get('taocrm')->getConf('system.limit.shop'),
		);

	}

	//检查店铺是否超过限制
	public function checkShopNums(&$msg, &$arr)
	{
		$versionInfo = $this->getVersion();
		if($versionInfo['shop_nums']){
			$row = kernel::database()->selectrow('select count(*) as total from sdb_ecorder_shop where node_id is not null');
			if($row['total'] > $versionInfo['shop_nums']){
				$msg = '当前系统店铺数:'.$row['total'] .',可管理上限为:'.$versionInfo['shop_nums'];
				$arr = array(
                    'num'=>$row['total'],
                    'max_num'=>$versionInfo['shop_nums'],
				);
				return false;
			}
		}
		return true;
	}

	public function getSystemMemberTotal()
    {
        $sql = "select count(*) as total_members from sdb_taocrm_members where parent_member_id=0 ";
        $rs = kernel::database()->selectRow($sql);
        return $rs['total_members'];
    
		$middleware = kernel::single('taocrm_middleware_connect');
		//$data = json_decode(self::$middleware->DBAllShopInfo(), true);
		$filter = array('status'=>1);
		$data = $middleware->DBAllShopInfo($filter);
		$total = 0;
		if($data){
			foreach($data as $k=>$v){
				$total += $v['MemberCount'];
			}
		}
		return $total;
	}

	//检查客户数是否超过限制
	public function checkMemberNums(&$msg, &$arr){
		$versionInfo = $this->getVersion();
		if($versionInfo['member_nums']){
			$total = $this->getSystemMemberTotal();
			if($total > $versionInfo['member_nums']){
				$msg = '当前系统客户数:'.$total .',可管理上限为:'.$versionInfo['member_nums'];
				$arr = array(
                    'num'=>$total,
                    'max_num'=>$versionInfo['member_nums'],
				);
				return false;
			}
		}

		return true;
	}

	//检查订单数是否超过限制
	public function checkOrderNums(&$msg, &$arr){
		$versionInfo = $this->getVersion();
		if($versionInfo['order_nums']){
			$row = kernel::database()->selectrow('select count(*) as total from sdb_ecorder_orders');
			if($row['total'] > $versionInfo['order_nums']){
				$msg = '当前系统订单数:'.$row['total'] .',可管理上限为:'.$versionInfo['order_nums'];
				$arr = array(
                    'num'=>$row['total'],
                    'max_num'=>$versionInfo['order_nums'],
				);
				return false;
			}
		}
		return true;
	}

	public function setDefaultSystemType(){
		$defaultSystemType = array('system_type'=>1,'pay_rule'=>20,'freeze_rule'=>0,'market_pay_rule'=>20,'market_freeze_rule'=>0);
		$this->setSystemType($defaultSystemType);
	}

	//1:月租客户,2:按效果计费客户
	public function setSystemType($data){
		if(isset($data['system_type'])){
			app::get('taocrm')->setConf('system.type',intval($data['system_type']));

			if($data['system_type'] == 2){
				app::get('taocrm')->setConf('system.pay_rule',intval($data['pay_rule']));
				app::get('taocrm')->setConf('system.freeze_rule',intval($data['freeze_rule']));
			}

			app::get('taocrm')->setConf('system.market_pay_rule',intval($data['market_pay_rule']));
			app::get('taocrm')->setConf('system.market_freeze_rule',intval($data['market_freeze_rule']));
		}

	}

	public function getSystemType(){
		return array(
        			'system_type'=> app::get('taocrm')->getConf('system.type'),
                 	'pay_rule'=>app::get('taocrm')->getConf('system.pay_rule'),
        	        'freeze_rule'=>app::get('taocrm')->getConf('system.freeze_rule'),
        			'market_pay_rule'=>app::get('taocrm')->getConf('system.market_pay_rule'),
        	        'market_freeze_rule'=>app::get('taocrm')->getConf('system.market_freeze_rule'),
		);
	}

	/**
	 * 仅更新菜单文件
	 */
	function update_desktop_menu()
	{
		$rows = app::get('base')->model('apps')->getList('app_id',array('installed'=>1));
		foreach($rows as $r){
			if($r['app_id'] == 'base')  continue;
			$args[] = $r['app_id'];
		}

		array_unshift($args, 'base');//todo:总是需要先更新base
		$args = array_unique($args);

		foreach($args as $app_id){
			kernel::single('base_application_manage')->update_app_menu($app_id);
		}
	}

	//产品线代码
	function getProlineCode()
	{
		$codes = array(
		0=>'C-0006',
		);
		return $codes[0];
	}

	//产品代码
	function getProductCode()
	{
		$codes = array(
		1=>'product_0042',  //标准
		2=>'product_0043',  //企业
		3=>'product_0300',  //旗舰
		4=>'product_0043',  //默认:企业
		);
		$version_code = app::get('taocrm')->getConf('system.version_code');
		switch($version_code){
			case 'Base_Ver':
				$site_ver=1;
				break;

			case 'High_Ver':
			case 'HighAll_Ver':
				$site_ver=2;
				break;

			case 'Pro_Ver':
				$site_ver=3;
				break;

			default:
				$site_ver=4;
				break;
		}
		return $codes[$site_ver];
	}
}