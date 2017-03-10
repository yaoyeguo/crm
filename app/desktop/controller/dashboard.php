<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_ctl_dashboard extends desktop_controller{

    var $workground = 'desktop_ctl_dashboard';

    public function __construct($app)
    {
        parent::__construct($app);
        //$this->member_model = $this->app->model('members');
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index()
    {
        //更新和安装后第一次登陆,调用monitor接口采集信息
        base_kvstore::instance('desktop')->fetch('firstLogin', $firstLogin);
        if(empty($firstLogin)){
            $java_obj = app::get('taocrm')->model('java_info');
            $java_data = $java_obj->getlist("java_class,java_version");
            $java_data_json = json_encode($java_data);
            //调用monitor接口
            $arr = array(
                'domain'=>$_SERVER['SERVER_NAME'],'version'=>CRM_VERSION,'php_version'=>PHP_VERSION,'java_version'=>$java_data_json,'update_time'=>time()
            );
            $http = new base_httpclient;
            $http->timeout = 5;
            //$result = $http->post('http://localhost/monitor/index.php/openapi/seller.addVersionInfo/index/',$arr);//测试用
            $result = $http->post('http://monitor.crmm.taoex.com/index.php/openapi/seller.addVersionInfo/index/',$arr);
            //print_r($result);
        }
        base_kvstore::instance('desktop')->store('firstLogin','no');

    	//每天第一次登录时重新加载内存
    	base_kvstore::instance('taocrm')->fetch('loadStatus', $loadStatus);
    	$load = json_decode($loadStatus,true);

    	if($load['date'] !== date('Y-m-d')){
    		$res = $this->checkLoadMemory('data');
            if ($res['status'] == 2) {
                $connect = kernel::single('taocrm_middleware_connect');
                $connect->removeDBIndex();
            }
            base_kvstore::instance('taocrm')->store('loadStatus', json_encode(array('date'=>date('Y-m-d'))));
    	}

        $memroyStatus = $this->loadMemoryDb();
        if ($memroyStatus) {
            return;
        }

        //$this->getTipsInfo();
        //申请证书
            $this->Certi = base_certificate::get('certificate_id');
            $this->Token = base_certificate::get('token');
            if(empty($this->Certi) ||empty($this->Token)){
                $certificate = kernel::single('base_certificate');
                $certificate->register();
            }
        $this->pagedata['tip'] = base_application_tips::tip();
        $user = kernel::single('desktop_user');
        $is_super = $user->is_super();

        $group = $user->group();
        $group = (array)$group;

        //桌面挂件排序，用户自定义
        $user->get_conf( 'arr_dashboard_sort',$arr_dashboard_sort );

        foreach(kernel::servicelist('desktop.widgets') as $key => $obj){
            if($is_super || in_array(get_class($obj),$group)){
                $class_full_name = get_class($obj);
                $key = $obj->get_width();
                $tmp = array(
                    'title'=>$obj->get_title(),
                    'html'=>$obj->get_html(),
                    'width'=>$obj->get_width(),
                    'className'=>$obj->get_className(),
                    'class_full_name' => $class_full_name,
                    );
                foreach( (array)$arr_dashboard_sort as $__dashboard_sort_key => $__dashboard_sort ) {
                    if( is_array($__dashboard_sort) && (false!==($hk=array_search($class_full_name,$__dashboard_sort))) ) {
                        $sort_with[$__dashboard_sort_key][] = $hk;
                        $key = $__dashboard_sort_key;
                        $continue = true;
                        break;
                    }
                }
                if( !$continue ) $sort_with[$key][] = $obj->order?$obj->order:1;
                $widgets[$key][] = $tmp;
            }
        }
        foreach((array)$widgets as $key=>$arr){
            array_multisort($sort_with[$key], SORT_ASC,$arr);
            $widgets[$key] = $arr;
        }
        $this->pagedata['widgets_1'] = $widgets['l-1'];
        $this->pagedata['widgets_2'] = $widgets['l-2'];
        $this->pagedata['widgets_3'] = $widgets['t-1'];
        $this->pagedata['widgets_4'] = $widgets['b-1'];
        $deploy = kernel::single('base_xml')->xml2array(file_get_contents(ROOT_DIR.'/config/deploy.xml'),'base_deploy');
        $this->pagedata['deploy'] = $deploy;

        //获取系统公告
        //$notice_id = kernel::single('taocrm_ctl_admin_notice')->get_notice_id();
        //$this->pagedata['notice_id'] = $notice_id;

        $this->pagedata['dashboard_sort_url'] = $this->app->router()->gen_url( array('app'=>'desktop','ctl'=>'dashboard','act'=>'dashboard_sort') );
        /*
        * 判断是否操作
        */
        /*
            $db = kernel::database();
            $row = $db->selectrow('select shop_id,shop_bn,name,node_id from sdb_ecorder_shop');
            if($row){
                $steps['shop_bind'] = 1;
                if($row['node_id']){
                     $steps['shop_checked'] = 1;
                }else{
                     $steps['shop_checked'] = 0;
                }
            }
        */
        //base_kvstore::instance('steps')->fetch('asteps', $asteps);  //获取缓存
        //$asteps = unserialize($asteps);
        //$this->pagedata['steps'] = $asteps;


		/*$serven_expired = kernel::single('taocrm_service_redis')->redis->get('tgcrm_serven_expired:OP_HOST');
		$serven_expired = json_decode($serven_expired,true);
		$flag = 0;

		$host = $_SERVER['SERVER_NAME'];

		if($serven_expired[$host]){
			//判断该域名是否已续费
			$info = $this->getInfoByHost($host);
			$day = (strtotime($info->cycle_end) - strtotime(date('Y-m-d',time()))) / 86400;
			if($day <= 7){
				$flag = 1;
				$this->pagedata['day'] = $day;
			}

		}else{
			$db = kernel::database();
	        $sql = 'select * from sdb_ecorder_shop';
	        $rs = $db->selectrow($sql);
	        if($rs) $bind_shop = 1;
	        base_kvstore::instance('market')->fetch('account', $account);
	        $account = unserialize($account);
	        if($account['entid']) $bind_sms = 1;
	        $init_finish = 0;
	        if($bind_shop && $bind_sms){
	        	$init_finish = 1;
	        	$flag = 2;
	        }
		}

       	$this->pagedata['flag'] = $flag;
		$this->pagedata['init_finish'] = $init_finish;*/

        /*

        $db = kernel::database();
        $sql = 'select * from sdb_ecorder_shop';
        $rs = $db->selectrow($sql);
        if($rs) $bind_shop = 1;

        $this->activeShopexid();
        base_kvstore::instance('market')->fetch('account', $account);
        $account = unserialize($account);
        if($account['entid']) $bind_sms = 1;

        $init_finish = 0;
        if($bind_shop && $bind_sms) $init_finish = 1;

        $this->pagedata['init_finish'] = $init_finish;
        */
        $this->page('dashboard.html');
    }

    protected function activeShopexid()
    {
        base_kvstore::instance('market')->fetch('account', $account);
        if (unserialize($account)) {
            $param = unserialize($account);
            $info = market_sms_utils::GetEnterpriseByMobile($param);
            if ($info->info->active != 1) {
                $param['password'] = $info->info->password;
                $result = market_sms_utils::activeShopexid($param);
                if ($result->res == 'succ') {
                    $param['password'] = $info->info->password;
                    $param['state'] = 1;
                    base_kvstore::instance('market')->store('account', serialize($param));
                }
            }
        }
        return true;
    }

    /**
     * 加载内存数据库
     */
    public function loadMemoryDb()
    {
        $status = $this->checkLoadMemory('data');
        if ($status['status'] == 1) {
            $connect = kernel::single('taocrm_middleware_connect');
            $result = json_decode($connect->addDBIndex(), true);
        }
        $wait = false;
        if ($status['status'] == 1 || $status['status'] == 3) {
            $wait = true;
            $tips = $this->getTipsInfo(false);
            $this->pagedata['tips'] = $tips['data'];
            $this->pagedata['total_tips'] = count($tips['data']) - 1;
            $this->page('loadMemory.html');
        }
        return $wait;
    }

//加载数据接口
    public function checkLoadMemory($return = 'json')
    {
        $connect = new taocrm_middleware_connect;
        //加载方法
        $result = $connect->DbIndexState();

        $data = array();
        switch ($result) {
            case 'NULL':
                $data = array('status' => 1);
                break;
            case 'READY':
                $data = array('status' => 2);
                break;
            case 'LOADING':
                $data = array('status' => 3);
                break;
            default:
                $data = array('status' => 1);
                break;
        }

        if ($return != 'data') {
            echo json_encode($data);
            exit;
        }
        else {
            return $data;
        }
    }

    //获取温馨贴士信息
    public function getTipsInfo($disp = true)
    {
        $tips = kernel::single('taocrm_ctl_admin_tips')->get_tips_info();
        $data = array('res' => 'succ', 'data' => '');
        if ($tips) {
            $data['data'] = $tips;
        }
        if ($disp) {
            echo json_encode($data);
            exit;
        }
        else {
            return $data;
        }
    }

    public function checkBindOver(){
        /*
        * 判断是否操作
        */
        /*$db = kernel::database();
        $row = $db->selectrow('select shop_id,shop_bn,name,node_id from sdb_ecorder_shop');
        if($row){
            $steps['shop_bind'] = 1;
            if($row['node_id']){
                 $steps['shop_checked'] = 1;
            }else{
                 $steps['shop_checked'] = 0;
            }
        }
        //$steps['shop_checked'] = 1;
        base_kvstore::instance('market')->fetch('account', $account);  //检测是否绑定短信
        $account = unserialize($account);
        if($account){
            $steps['sms_bind'] = 1;
        }
        base_kvstore::instance('steps')->fetch('asteps', $asteps);  //获取缓存
        $asteps = unserialize($asteps);
        if($asteps['sms_buy'] == 1){
            $steps['sms_buy'] = 1;
        }*/
        base_kvstore::instance('steps')->fetch('asteps', $asteps);  //获取缓存
        $steps = unserialize($asteps);
        if($steps['shop_bind'] !=1 || $steps['shop_checked'] !=1 || $steps['sms_bind'] !=1 || $steps['sms_buy'] !=1){
            echo $steps;
        }
    }

    /*
     * 桌面排序
     * 桌面挂件排序，用户自定义
     */
    public function dashboard_sort( )
    {
        $desktop_user = kernel::single('desktop_user');
        $arr = explode(' ',trim($_POST['sort']));
        $conf = array();
        if( $arr && is_array($arr) ) {
            foreach( $arr as $value ) {
                if( !($hk=strpos($value,':')) ) continue;
                $key = substr($value,0,$hk);
                $conf[$key] = explode(',',substr($value,($hk+1)));
            }
        }
        $desktop_user->set_conf( 'arr_dashboard_sort',$conf );
    }
    #End Func


    function advertisement(){
		//$conf = base_setup_config::deploy_info();
		//$this->pagedata['product_key'] = $conf['product_key'];
		/*
		$server_name = $_SERVER['SERVER_NAME'];
		$tt_obj = memcache_connect(SERVER_TT_HOST, SERVER_TT_PORT);
		$preFix = md5(md5(sprintf('%s_%s', $server_name, SERVICE_IDENT)));
        $data = unserialize(memcache_get($tt_obj, $preFix));
        $this->pagedata['username'] = $data['USERNAME'];
        $this->pagedata['time'] = time();
        */
        /*
        $this->pagedata['cross_call_url'] =base64_encode( kernel::single('base_component_request')->get_full_http_host().$this->app->base_url().
        'index.php?ctl=dashboard&act=cross_call'
        );
        */
        $this->display('advertisement.html');
    }

    function cross_call(){
        header('Content-Type: text/html;charset=utf-8');
        echo '<script>'.str_replace('top.', 'parent.parent.', base64_decode($_REQUEST['script'])).'</script>';
	//echo '<script>'.base64_decode($_REQUEST['script']).'</script>';
    }


    function appmgr() {
        $arr = app::get('base')->model('apps')->getList('*', array('status'=>'active'));
        foreach( $arr as $k => $row ) {
            if( $row['remote_ver'] <= $row['local_ver'] ) unset($arr[$k]);
        }
        $this->pagedata['apps'] = $arr;

        $this->display('appmgr/default_msg.html');


    }



    function fetch_tip(){
        echo $this->pagedata['tip'] = base_application_tips::tip();
    }

    function profile(){

        //获取该项记录集合
        $users = $this->app->model('users');
        $roles=$this->app->model('roles');
        $workgroup=$roles->getList('*');
        $sdf_users = $users->dump($this->user->get_id());

        if($_POST){
            $this->user->set_conf('desktop_theme',$_POST['theme']);
            $this->user->set_conf('timezone',$_POST['timezone']);
             header('Content-Type:text/jcmd; charset=utf-8');
             echo '{success:"'.app::get('desktop')->_("设置成功").'",_:null}';
             exit;
        }

        $themes = array();
        foreach(app::get('base')->model('app_content')
            ->getList('app_id,content_name,content_path'
        ,array('content_type'=>'desktop theme')) as $theme){
            $themes[$theme['app_id'].'/'.$theme['content_name']] = $theme['content_name'];
        }

        //返回无内容信息
        $this->pagedata['themes'] = $themes;

        $this->pagedata['current_theme'] = $this->user->get_theme();

        $this->pagedata['name'] = $sdf_users['name'];
        $this->pagedata['super'] = $sdf_users['super'];
        $this->display('users/profile.html');
    }

    ##非超级管理员修改密码
    function chkpassword()
    {
        if($_POST){

            $account_id = $this->user->get_id();
            $users = $this->app->model('users');
            $sdf = $users->dump($account_id,'*',array( ':account@pam'=>array('*'),'roles'=>array('*') ));
            $old_password = $sdf['account']['login_password'];
            $filter['account_id'] = 1;
            $filter['account_id'] = $account_id;
            $filter['account_type'] = pam_account::get_account_type($this->app->app_id);
            $filter['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['old_login_password']),pam_account::get_account_type($this->app->app_id));
            $pass_row = app::get('pam')->model('account')->getList('account_id',$filter);

            $this->begin();
            if(!$pass_row){
                $this->end(false, app::get('desktop')->_('原密码不正确'));
            }
            elseif($_POST['new_login_password']!=$_POST[':account@pam']['login_password']){
                $this->end(false, app::get('desktop')->_('两次密码不一致'));
            }
            else{
                $_POST['pam_account']['account_id'] = $account_id;
                $_POST['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['new_login_password']),pam_account::get_account_type($this->app->app_id));

                $users->save($_POST);
                $this->end(true, app::get('desktop')->_('密码修改成功'));
            }
        }
        $ui= new base_component_ui($this);

        $arrGroup=array(
            array( 'title'=>app::get('desktop')->_('原密码'),'type'=>'password', 'name'=>'old_login_password', 'required'=>true,),
            array( 'title'=>app::get('desktop')->_('新密码'),'type'=>'password', 'name'=>'new_login_password', 'required'=>true,),
            array( 'title'=>app::get('desktop')->_('再次输入新密码'),'type'=>'password', 'name'=>':account@pam[login_password]', 'required'=>true,),
            );
        $html .= $ui->form_start(array('method' => 'POST'));
        foreach($arrGroup as  $arrVal){
            $html .= $ui->form_input($arrVal);
        }
        $html .= $ui->form_end();
        echo $html;
    }

     function redit(){
        $desktop_user = kernel::single('desktop_user');
        if($desktop_user->is_super()){
            $this->redirect('index.php?ctl=adminpanel');
        }
        else{
            $aData = $desktop_user->get_work_menu();
            $aMenu = $aData['menu'];
            foreach($aMenu as $val){
                foreach($val as $value){
                    foreach($value as $v){
                        if($v['display']==='true'){
                            $url = $v['menu_path'];break;
                        }
                    }
                    break;
                }
                break;
            }
            if(!$url) $url = "ctl=adminpanel";
            $this->redirect('index.php?'.$url);
        }
    }

    public function get_license_html()
    {
        $this->display('license.html');
    }

    public function application(){
        $certificate = kernel::single('base_certificate');
        if($certificate->register()===false)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('desktop')->_("申请失败").'",_:null}';
            //$this->end(false,app::get('desktop')->_('申请失败'));
        }
        else
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('desktop')->_("申请成功").'",_:null}';
            //$this->end(true,app::get('desktop')->_('申请成功'));
        }
    }


	function getInfoByHost($host) {
	    $api = kernel::single('taocrm_saas');
	    $api->appkey = 'taocrm';
	    $api->secretKey = '5EB2B5FF9F8DBD6C583281E326F66D9B';
	    $api->format = 'json';

	    $params = array('server_name' => $host);
	    $result = $api->execute('host.getinfo_byservername', $params);
	    unset($api);
	    if ($result->success == 'true') {
	        if ($result->data == 'QUEUE_END') {
	            return null;
	        } else {
	            return $result->data;
	        }
	    } else {
	        return null;
	    }

	}
}
