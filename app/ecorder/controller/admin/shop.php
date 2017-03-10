<?php
class ecorder_ctl_admin_shop extends desktop_controller{

	var $name = "店铺管理";
	//var $workground = "setting_tools";
	var $pa ;

	function index()
    {
		//setcookie("bind_shop", '');
		//setcookie("bind_shop", "succ", time()+3600);
		//$q = kernel::single('base_certificate')->register();

		kernel::single('taocrm_service_shop')->save_redis();

		$app_exclusion = app::get('base')->getConf('system.main_app');
		$certi = base_certificate::get('certificate_id');
		$node_id = base_shopnode::node_id($app_exclusion['app_id']);
		$title = '已开通店铺(证书：'.$certi.'&nbsp;&nbsp;节点：'.$node_id.')';

		$actions = array(
		array(
                'label' => '添加店铺',
                'href' => 'index.php?app=ecorder&ctl=admin_shop&act=addterminal&finder_id=' . $_GET['finder_id'],
                'target' => 'dialog::{width:700,height:380,title:\'添加店铺\'}'
                ),
                array(
                'label' => '查看绑定关系',
                'href' => 'index.php?app=ecorder&ctl=admin_shop&act=view_bindrelation',
                'target' => 'dialog::{width:960,height:460,title:\'查看绑定关系\'}'
                ),
                );

        $this->finder(
            'ecorder_mdl_shop',
            array(
                'title'=>$title,
                'actions'=>$actions,
                'use_buildin_new_dialog' => false,
                'use_buildin_set_tag'=>false,
                'use_buildin_recycle'=>true,
                'use_buildin_export'=>false,
                'use_buildin_import'=>false,
            'orderBy'=>'orders DESC,last_download_time DESC',
        ));
    }

	/**
	 * 网店节点显示
	 * @param null
	 * @return null
	 */
	public function shopnode(){
		$app_exclusion = app::get('base')->getConf('system.main_app');
		$this->pagedata['node_id'] = base_shopnode::node_id($app_exclusion['app_id']);
		$this->pagedata['node_type'] = base_shopnode::node_type($app_exclusion['app_id']);

		$this->page('admin/system/shopnode.html');
	}

	function addparam () {
		if($pa==0) {
			return 'add';
		}elseif($pa==1) {
			return 'editer';
		}
	}

	/*
	 * 添加前端店铺
	 */
	function addterminal(){
		$this->_editterminal();
	}

	/**
	 * 编辑前端店铺
	 */
	function editterminal($shop_id)
	{
		$this->pagedata['is_member_prop'] = false;
		$this->_editterminal($shop_id);
	}

	/**
	 * 客户自定义属性
	 */
	function member_prop_edit($shop_id)
	{
		$this->pagedata['redirect_uri'] = $_GET['redirect_uri'];
		$this->pagedata['is_member_prop'] = true;
		$this->_editterminal($shop_id);
	}

	function _editterminal($shop_id=NULL,$para="")
	{
		$oShop = app::get('ecorder')->model("shop");
		$shoptype = ecorder_shop_type::get_shop_type();
		$shop_type = array();
		$i = 0;
		if ($shoptype)
		foreach ($shoptype as $k=>$v){
			$shop_type[$i]['type_value'] = $k;
			$shop_type[$i]['type_label'] = $v;
			$i++;
		}

		$prop_name = array('','','','','','','','','','');
		$prop_type = array('','','','','','','','','','');
		if($shop_id){
			$shop = $oShop->dump($shop_id);
			$shop_config = unserialize($shop['config']);
            if($shop_config['prop_name']) $prop_name = $shop_config['prop_name'];
            if($shop_config['prop_type']) $prop_type = $shop_config['prop_type'];
			if($shop_config['password'])
			$shop_config['password'] =
			$oShop->aes_decode($shop_config['password']);

			$shop_tel = explode('-',strval($shop['tel']));
			$shop['tel_code'] = $shop_tel[0];
			$shop['tel_phone'] = strval($shop_tel[1]);
			$shop['tel_extension'] = strval($shop_tel[2]);

            $this->pagedata['shop']=$shop;
			$this->pagedata['shop_config'] = $shop_config;
		}else{
			if(isset($_GET['shop_prop'])){
				$shop['shop_prop'] = $_GET['shop_prop'];
			}else{
				$shop['shop_prop'] = 'online';
			}
		}

		$this->pagedata['shop']=$shop;


		//默认店铺
		$is_default = 0;
		base_kvstore::instance('ecorder')->fetch('default_shop_id',$default_shop_id);
		if($default_shop_id && $default_shop_id==$shop_id){
			$is_default = 1;
		}
		$this->pagedata['is_default'] = $is_default;
        
        //主帐号店铺
        $is_main_accout = 0;
        base_kvstore::instance('ecorder')->fetch('main_account_shop',$main_account_shop);
        if($main_account_shop && $main_account_shop==$shop_id){
            $is_main_accout = 1;
        }
        $this->pagedata['is_main_accout'] = $is_main_accout;

		//店铺渠道
		$rs = app::get('ecorder')->model('shop_channel')->getList('channel_id,channel_name');
		if($rs) {
			foreach($rs as $v) {
				$shop_channel[$v['channel_id']] = $v['channel_name'];
			}
		}
        
        $conf_prop_type = array(
            'text'=>'文字',
            'num'=>'数字',
            'date'=>'日期',
        );
        
		$this->pagedata['from'] = $_GET['from'];
		$this->pagedata['prop_name'] = $prop_name;
		$this->pagedata['prop_type'] = $prop_type;
		$this->pagedata['conf_prop_type'] = $conf_prop_type;
		$this->pagedata['shop_channel'] = $shop_channel;
		$this->pagedata['shop_type'] = $shop_type;
		$this->display("admin/system/terminal.html");
	}

	/*
	 * 查看绑定关系
	 */
	function view_bindrelation(){
		$app_exclusion = app::get('base')->getConf('system.main_app');
		$this->Certi = base_certificate::get('certificate_id');

		//跟独立部署作兼容，方便合并代码，独立部署申请证书走的是另外流程
		$this->Token = base_shopnode::get('token',$app_exclusion['app_id']);
		if(empty($this->Token)){
			$this->Token = base_certificate::get('token');
		}

		$this->Node_id = base_shopnode::node_id($app_exclusion['app_id']);
		$token = $this->Token;
		$sess_id = kernel::single('base_session')->sess_id();
		$apply['certi_id'] = $this->Certi;
		$apply['node_id'] = $this->Node_id;
		$apply['sess_id'] = $sess_id;
		$str   = '';
		ksort($apply);
		foreach($apply as $key => $value){
			$str.=$value;
		}
		$apply['certi_ac'] = md5($str.$token);
		$callback = (kernel::openapi_url('openapi.ome.shop','shop_callback',array('shop_id'=>$shop_id)));
		$api_url = ("http://".$_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api");

		//解决ie兼容性问题
		if( ! stristr($_SERVER['HTTP_USER_AGENT'],'MSIE')){
			$callback = urlencode($callback);
			$api_url = urlencode($api_url);
		}

		echo '<iframe width="100%" height="100%" frameborder="0" src='.MATRIX_RELATION_URL.'?source=accept&certi_id='.$apply['certi_id'].'&node_id=' . $this->Node_id . '&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback='.$callback.'&api_url='.$api_url.' ></iframe>';
	}

	/**
	 * 申请绑定关系
	 * @param string $app_id
	 * @param string $callback 异步返回地址
	 * @param string $api_url API通信地址
	 */
	function apply_bindrelation($app_id='ome', $callback='', $api_url='') {

		$app_exclusion = app::get('base')->getConf('system.main_app');
		$this->Certi = base_certificate::get('certificate_id');

		//跟独立部署作兼容，方便合并代码，独立部署申请证书走的是另外流程
		$this->Token = base_shopnode::get('token',$app_exclusion['app_id']);
		if(empty($this->Token)){
			$this->Token = base_certificate::get('token');
		}

		$this->Node_id = base_shopnode::node_id($app_id);
		$token = $this->Token;
		$sess_id = kernel::single('base_session')->sess_id();
		$apply['certi_id'] = $this->Certi;
		if ($this->Node_id)
		$apply['node_id'] = $this->Node_id;
		$apply['sess_id'] = $sess_id;
		$str = '';
		ksort($apply);
		foreach ($apply as $key => $value) {
			$str.=$value;
		}
		$apply['certi_ac'] = md5($str . $token);

		$bind_type = '';
		if(strstr($_SERVER['SERVER_NAME'], '.mcrm.taoex.com')){
			$bind_type = 'taobao';
		}

		$this->pagedata['license_iframe'] = '<iframe width="100%" frameborder="0" height="99%" id="iframe" onload="this.height=document.documentElement.clientHeight-4" src="' . MATRIX_RELATION_URL . '?source=apply&certi_id=' . $apply['certi_id'] . '&node_id=' . $apply['node_id'] . '&sess_id=' . $apply['sess_id'] . '&certi_ac=' . $apply['certi_ac'] . '&callback=' . $callback . '&api_url=' . $api_url . '&bind_type='.$bind_type.'" ></iframe>';
		$this->display('admin/system/apply_terminal.html');
	}

	function saveterminal()
	{
		if($_POST['redirect_uri']){
			$url = 'index.php?act=index'.base64_decode($_POST['redirect_uri']);
		}elseif($_POST['from'] == 'add_member'){
			$url = 'index.php?app=taocrm&ctl=admin_member&act=add_member';
		}else{
			$url = 'index.php?app=ecorder&ctl=admin_shop&act=index';
		}
		$this->begin($url);

		$oShop = app::get('ecorder')->model("shop");
		$svae_data = $_POST['shop'];
		if ($svae_data['name']){
			$shop_detail = $oShop->dump(array('name'=>$svae_data['name']), 'shop_id,name');
			if ($shop_detail['shop_id'] != $svae_data['shop_id'] && $shop_detail['name']){
				$this->end(false,app::get('base')->_('店铺名称已存在，请重新输入'));
			}
		}

		/*
		 if (!$svae_data['old_shop_bn']){
		 $shop_detail = $oShop->dump(array('shop_bn'=>$svae_data['shop_bn']), 'shop_bn');
		 if ($shop_detail['shop_bn']){
		 $this->end(false,app::get('base')->_('编码已存在，请重新输入'));
		 }
		 }
		 */

		if (!$svae_data['old_shop_bn']){
			$svae_data['shop_bn'] = time().rand(0,1000);
		}else{
			$svae_data['shop_bn'] = $svae_data['old_shop_bn'];
		}

		//表单验证
		if (strlen($svae_data['zip']) <> '6'){
			//$this->end(false,app::get('base')->_('请输入正确的邮编'));
		}
		//固定电话与手机必填一项
		$gd_tel = str_replace(" ","",$svae_data['tel']);
		$mobile = str_replace(" ","",$svae_data['mobile']);
		if (1==0 && !$gd_tel && !$mobile){
			//$this->end(false,app::get('base')->_('固定电话与手机号码必需填写一项'));
		}

		/*
		 $pattern1 = "/^\d{1,4}-\d{7,8}(-\d{1,6})?$/i";
		 if ($gd_tel){
		 if (!preg_match($pattern1, $gd_tel)){
		 $this->end(false,app::get('base')->_('请填写正确的固定电话号码'));
		 }
		 }
		 $pattern2 = "/^\d{8,15}$/i";
		 if ($mobile){
		 if (!preg_match($pattern2, $mobile)){
		 $this->end(false,app::get('base')->_('请输入正确的手机号码'));
		 }
		 if ($mobile[0] == '0'){
		 $this->end(false,app::get('base')->_('手机号码前请不要加0'));
		 }
		 }
		 */

		//默认店铺
		if(intval($_POST['is_default']) == 1){
			if(empty($svae_data['shop_id'])){
				$shop_id = $oShop->gen_id($svae_data['shop_bn']);
				base_kvstore::instance('ecorder')->store('default_shop_id',$shop_id);
			}else{
				base_kvstore::instance('ecorder')->store('default_shop_id',$svae_data['shop_id']);
			}
		}
        
        //主帐号店铺
        if(intval($_POST['is_main_accout']) == 1){
            if(empty($svae_data['shop_id'])){
                $shop_id = $oShop->gen_id($svae_data['shop_bn']);
                base_kvstore::instance('ecorder')->store('main_account_shop',$shop_id);
            }else{
                base_kvstore::instance('ecorder')->store('main_account_shop',$svae_data['shop_id']);
            }
        }        
        
        base_kvstore::instance('analysis')->store('analysis_shop_id',$svae_data['shop_id']);

		if($svae_data['shop_id'] == ''){
			$svae_data['create_time'] = time();
		}elseif($svae_data['config']){
            //防止config被覆盖
            $rs_shop = $oShop->dump($svae_data['shop_id']);
            $shop_config = unserialize($rs_shop['config']);
            foreach($svae_data['config'] as $k=>$v){
                $shop_config[$k] = $v;
            }
            $svae_data['config'] = $shop_config;
		}

		$svae_data['modified_time'] = time();

		if(empty($svae_data['shop_id']) && $svae_data['shop_prop'] == 'offline'){
			$svae_data['node_id'] = date('YmdHis');
			$svae_data['shop_type'] = 'offlinepos';
			$svae_data['node_type'] = 'offlinepos';
		}

        $sid = $oShop->save($svae_data);
        
        //自定义分组初始化
        if($sid && isset($svae_data['create_time']) && $svae_data['shop_prop'] != 'wechat'){
            kernel::single('taocrm_service_queue')->addJob('taocrm_service_group@init_group',array('shop_id' => $svae_data['shop_id']));
        }
        
        $rt = $sid ? true : false;
        if(!$_POST['shop']['shop_id'])
            $this->app->model('shop_lv')->set_default_lv($svae_data['shop_id']);

        $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));
    }

	function applyAuth($shop_id,$jumpto)
	{
		$app_exclusion = app::get('base')->getConf('system.main_app');
		$app_id = $app_exclusion['app_id'];
		$this->Certi = base_certificate::get('certificate_id');

		//跟独立部署作兼容，方便合并代码，独立部署申请证书走的是另外流程
		$this->Token = base_shopnode::get('token',$app_exclusion['app_id']);
		if(empty($this->Token)){
			$this->Token = base_certificate::get('token');
		}

		$this->Node_id = base_shopnode::node_id($app_id);
		$token = $this->Token;
		$sess_id = kernel::single('base_session')->sess_id();
		$apply['certi_id'] = $this->Certi;
		if ($this->Node_id)
		$apply['node_id'] = $this->Node_id;
		$apply['sess_id'] = $sess_id;

		$oShop = app::get('ecorder')->model("shop");
		$shop = $oShop->dump($shop_id,'node_id');
		$callback = (kernel::openapi_url('openapi.ome.shop','shop_auth_callback',array('shop_id'=>$shop_id,'jumpto'=>$jumpto)));
		$params = array(
            'node_id'=> $shop['node_id'],
            'license_id'=> $apply['certi_id'],
            'timestamp'=> time(),
            'callback_url'=> $callback,
            'app'=> $app_id,
            'type'=> 'highrisk',
            'scope'=> 'promotion,item,usergrade',
		);
		$params['sign'] = ecorder_func::createSign($params,$token);
		$params['callback_url'] = urlencode($params['callback_url']);
		$this->pagedata['license_iframe'] = '<iframe width="100%" frameborder="0" height="99%" id="iframe" onload="this.height=document.documentElement.clientHeight-4" src="'.ecorder_func::createCallBackUrl(AUTH_OPEN_URL,$params).'" ></iframe>';
		//$this->pagedata['license_iframe'] = '<iframe width="100%" frameborder="0" height="99%" id="iframe" onload="this.height=document.documentElement.clientHeight-4" src="' . AUTH_OPEN_URL . '?license_id=' . $params['license_id'] . '&node_id=' . $params['node_id'] . '&timestamp=' . $params['timestamp'] . '&callback_url=' . $params['callback_url'] . '&app=' . $params['app'] . '&type='.$params['type'].'&scope='.$params['scope'].'&sign='.$sign.'" ></iframe>';
		$this->display('admin/system/apply_auth.html');
	}

	//保存短信签名
	function save_sign()
	{
		$shop_id = $_POST['shop_id'];
		$sms_sign = trim($_POST['sms_sign']);
		$oShop = app::get('ecorder')->model("shop");
		$rs = $oShop->dump($shop_id);
		$config = unserialize($rs['config']);
		#code:接口调用
		base_kvstore::instance('market')->fetch('account', $account);
		$account = unserialize($account);
		$shopex_id = $account['entid'];

		//对密码进行解密
		$market_edm_des = kernel::single('market_edm_des');
		if(strlen($account['password']) > 64){
			$password = $market_edm_des->decrypt($account['password']);
		}else{//兼容旧的原始密码
			$password = md5($account['password'].'ShopEXUser');
		}

		$api = SMS_SIGN_API.'/new';
		$pai_params = array(
            'client_id' => SMS_SIGN_KEY,
            'client_secret' => SMS_SIGN_SECRET,
            'shopexid' => $shopex_id,
            'passwd' => $password,
            'content' =>  '【'.$sms_sign.'】'
            );

            $result = $this->_get_url_content($api,$pai_params,1);
            $result = json_decode($result,true);
            if(!$result || $result['code'] != 0){
            	echo '<font color=red>'.$result['data'].'</font>';
            	exit;
            }

            $config['review'] = $result['data']['review'];
            $config['extend_no'] = $result['data']['extend_no'];
            #apicode over
            $config['sms_sign'] = $sms_sign;

            $data = array(
            'shop_id'=>$shop_id,
            'config'=>serialize($config),
            );
            $oShop->save($data);
            echo('<font color=green>√ 保存成功</font>');
	}

	/**
	 * 从给定的url获取内容
	 *
	 * @param string $url
	 * @return string
	 */
	private function _get_url_content($url,$params = '',$is_post = false)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		if($is_post)
		{
			curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		}
		$content = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpcode >= 400)
		{
			return null;
		}
		return $content;
	}

	//管理短信签名列表
	function signs(){
		$from = $_GET['from'];
		$oShop = app::get('ecorder')->model("shop");
		$signs = $oShop->getList();
		foreach($signs as &$v){
			$config = unserialize($v['config']);
			if(!$config['sms_sign']){
				//$v['sms_sign'] = $v['name'];
			}else{
				$v['sms_sign'] = $config['sms_sign'];
				$v['extend_no'] = $config['extend_no'];
				$v['review'] = $config['review'];
			}
		}

		$this->pagedata['signs'] = $signs;
		$this->pagedata['from'] = $from;
		if($from=='dialog'){
			$this->display('admin/shop/signs.html');
		}else{
			$this->page('admin/shop/signs.html');
		}
	}

	//登录日志
	function loginLog()
	{
		$actions = array();
		$params = array ('title' => '系统登录日志',
			'actions'=>$actions,
			'use_buildin_new_dialog' => false,
			'top_extra_view' => $this->_extra_view,
			'base_filter' => $this->base_filter,
			'use_buildin_recycle' => false, //删除操作
			'orderBy' => "login_time desc",
			'use_buildin_import' => false, //导入
			'use_buildin_filter' => false, //高级筛选
			'use_buildin_setcol' => true, //列配置
			'use_buildin_refresh' => false, //刷新
		);
		$this->finder ( 'ecorder_mdl_login_log', $params );
	}

    public function erp_bind()
    {
        $this->pagedata['import_tools_download_url'] = kernel::base_url(true).'/CRMM导入工具.zip';
		$this->pagedata['import_host']  = kernel::base_url(true);
        $this->pagedata['import_pwd']  = md5($_SERVER['SERVER_NAME'].$this->token);
        //$this->pagedata['token']  = base_certificate::get('token');
        $this->pagedata['token']  = base_shopnode::get_token();
        $this->page('admin/shop/erp_bind.html');
    }

	public function import_member()
	{
		$this->redirect('index.php?app=taocrm&ctl=admin_member&act=import');
	}

	public function get_shop_prop()
	{
		$shop_id = $_POST['shop_id'];

		$prop_name = array();
		if($shop_id){
			$shop = $this->app->model('shop')->dump($shop_id);
			$shop_config = unserialize($shop['config']);
			if($shop_config['prop_name']){
				$prop_name = $shop_config['prop_name'];
			}
		}

		$prop_name = array_unique($prop_name);
		foreach($prop_name as $k=>$v){
			if(!$v) unset($prop_name[$k]);
		}
		$res = json_encode($prop_name);
		echo($res);
	}

	//ajax获取签名列表
	public function get_sms_sign_list()
	{
		$oShop = $this->app->model("shop");
		$sign_list = $oShop->get_sms_sign_list();
		echo(json_encode(array_values($sign_list)));
	}

    //ajax请求返回值
    private function  reture_data($msg,$domain,$res)
    {
        $return_date = array(
            'msg'    => $msg,
            'domain' => $domain,
            'res'    => $res
        );
        echo json_encode($return_date);exit;
    }

    //管理微信提示信息
    public function prompt()
    {
        $msg = $_GET['msg'];
        $res = $_GET['res'];
        $this->pagedata["res"] = $res;
        $this->pagedata["msg"] = $msg;
        $this->display('admin/shop/prompt.html');
    }

    public function standardWeixin()
    {
        base_kvstore::instance('market')->fetch('wx_info',$wx_info);
        $wx_info = json_decode($wx_info, true);
            
        $version = intval($_GET['wx_version']);
        $domain = $_SERVER['SERVER_NAME'];
        if($version){
            if(!empty($wx_info)){
                if($version == 1){
                    if($wx_info['version'] == 2)
                        $this->reture_data('你现在已经是标准版微信服务','',1);

                        $wx_info['version'] = 2;
                    }
                    elseif($version == 2){
                        if($wx_info['version'] != 2)
                            $this->reture_data('你现在的微信服务已经是增强版,请不要重复操作！','',1);

                        if($wx_info['end_date']<time())
                            $this->reture_data('你现在的增强版微信服务已过期,请选择购买！','',1);

                        if($wx_info['is_trial'] == 1 ){
                            $wx_info['version'] = 1;
                        }
                        else{
                            $wx_info['version'] = 3;
                        }
                    }
                    elseif($version == 3){
                            base_kvstore::instance('market')->fetch('wx_info',$date);
                            $date = json_decode($date,true);
                            echo date('Y-m-d h:i:s',$date['start_time']);
                            echo date('Y-m-d h:i:s',$date['end_date']);
                            print_r($date);exit;
                    }
                base_kvstore::instance('market')->store('wx_info',json_encode($wx_info));
                $this->reture_data('切换成功',$domain,0);
            }else{
                $this->reture_data('你还没有试用或者购买过增强版','',1);
            }
        }else{
            if(empty($wx_info)){
                $this->pagedata['url'] = kernel::base_url(1);
                $this->page('admin/weixin/trial.html', 'market');
                exit;
            }
        }
        
        $need_buy = 0;
        if($wx_info['end_date']<time()) $need_buy = 1;
        
        $this->pagedata['need_buy'] = $need_buy;
        $this->page('admin/shop/weixin.html');
        exit;
    }

    function chk_unnormal_shop()
    {
        $unnormal_shops = $this->app->model('shop')->get_unnormal_shops();
        foreach($unnormal_shops as &$v){
            $v['last_download_time'] = date('Y-m-d H:i:s', $v['last_download_time']);
        }
        $this->pagedata['unnormal_shops'] = $unnormal_shops;
        $this->pagedata['hour'] = 24;
        $this->display('admin/shop/unnormal_shops.html');
    }

}
