<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_ctl_default extends desktop_controller{

    var $workground = 'desktop_ctl_dashboard';

    function index()
    {
        //兼容呼叫中心设置
        if($_GET['mobile']){
            $mobile = trim($_GET['mobile']);
        }else{
            $mobile = kernel::single('market_cti')->get_mobile($_GET);
        }
        if($mobile){
            $url = kernel::base_url(0)."?app=market&ctl=admin_callcenter_callin&act=index&mobile=".$mobile;
            header('Location:' . $url);
            exit;
        }

        $this->_init_keyboard_setting();

        $desktop_user = kernel::single('desktop_user');

        $menus = $desktop_user->get_work_menu();
        $user_id = $this->user->get_id();
        $desktop_user->get_conf('fav_menus',$fav_menus);
        //默认显示5个workground
        $workground_count = (app::get('desktop')->getConf('workground.count'))?(app::get('desktop')->getConf('workground.count')-1):5;
        if(!$fav_menus){
            $i = 0;
            foreach((array)$menus['workground'] as $key=>$value){
                //if($i++>$workground_count) break;
                $fav_menus[] = $key;
            }
        }

        $obj = kernel::service('desktop_index_seo');
        if(is_object($obj) && method_exists($obj, 'title')){
            $title = $obj->title();
        }else{
            $title = app::get('desktop')->_('管理后台');
        }
        if(is_object($obj) && method_exists($obj, 'title_desc')){
            $title_desc = $obj->title_desc();
        }else{
            $title_desc = 'Powered By ShopEx';
        }


        $this->pagedata['title'] = $title;
        $this->pagedata['title_desc'] = $title_desc;
        $this->pagedata['session_id'] = kernel::single('base_session')->sess_id();
        $this->pagedata['uname'] = $this->user->get_login_name();
        $this->pagedata['param_id'] = $user_id;
        $this->pagedata['menus'] = $menus;
        $this->pagedata['fav_menus'] = (array)$fav_menus;
        $this->pagedata['shop_base']  = kernel::base_url(1);
        $this->pagedata['shopadmin_dir'] = ($_SERVER['REQUEST_URI']);
        $desktop_user->get_conf('shortcuts_menus',$shortcuts_menus);
        $this->pagedata['shortcuts_menus'] = (array)$shortcuts_menus;
        $desktop_menu = array();
        foreach(kernel::servicelist('desktop_menu') as $service){
            $array = $service->function_menu();
            $desktop_menu = (is_array($array)) ? array_merge($desktop_menu, $array) : array_merge($desktop_menu, array($array));
        }
        $this->pagedata['desktop_menu'] = (count($desktop_menu)) ? '<span>'.join('</span>|<span>', $desktop_menu).'</span>' : '';
        list($this->pagedata['theme_scripts'],$this->pagedata['theme_css']) =
        desktop_application_theme::get_files($this->user->get_theme());

        $this->Certi = base_certificate::get('certificate_id');
        $confirmkey = $this->setEncode($this->pagedata['session_id'],$this->Certi);
        $this->pagedata['certificate_url'] = "http://service.shopex.cn/info.php?sess_id=".urlencode($this->pagedata['session_id'])."&certi_id=".urlencode($this->Certi)."&version=ecstore&confirmkey=".urlencode($confirmkey)."&_key_=do";

        /*
         //判断当前用户是否已阅读且同意条款条件
         $op_id = kernel::single('desktop_user')->get_id();
         base_kvstore::instance('market')->fetch('legal_copy_info_'.$op_id,$legal_copy);
         $data = unserialize($legal_copy);
         //判断当前用户是按效果付费还是月租
         $systemType = kernel::single('taocrm_system')->getSystemType();
         $system_type = $systemType['system_type'];

         $this->pagedata['stat'] = $data['stat'];
         $this->pagedata['system_type'] = $system_type;
         //$this->pagedata['system_type'] = 2;
         */
         
        //菜单logo
        $version_code = app::get('taocrm')->getConf('system.version_code');
        if(!$version_code) $version_code = 'High_Ver';

       	$this->pagedata['version_logo'] = "version/{$version_code}.gif";


       	if(app::get('bizcloud')->is_actived()){
       	    $uid = $_SESSION['account']['user_params']['passport_uid'];
       	    $server_name = $_SERVER['SERVER_NAME'];
       	    $arr_domain = explode('.',$server_name);
       	    $domain = $arr_domain[0];
       	    $this->pagedata['header_html'] =  '<iframe src="http://i.shopex.cn/toolbar.php" width="100%" height="30px" scrolling="no" frameborder="no" border="0" id="topbar_iframe"></iframe>';
        }
       	     
        base_kvstore::instance('market')->fetch('wx_info', $wx_info);
        $wx_info = json_decode($wx_info,true);
        $wx_version = 0;
        if(!empty($wx_info)){
            if( $wx_info['version']!=2 ) {
                if($wx_info['start_time']<=time() && $wx_info['end_date']>= time()){
                    $wx_version = 1;
                }
                else
                {
                    $wx_info = array(
                        'is_trial'      => '1',
                        'version'       => '2',
                        'start_time'    => '',
                        'end_date'      => ''
                    );
                    base_kvstore::instance('market')->store('wx_info',json_encode($wx_info));
        }
            }
        }
        $this->pagedata['is_weibaolai'] = $wx_version;
        $this->display('index.html');

    }

    function setEncode($sess_id,$certi_id){
        $ENCODEKEY='ShopEx@License';
        $confirmkey = md5($sess_id.$ENCODEKEY.$certi_id);
        return $confirmkey;
    }

    public function set_open_api()
    {
        echo $this->openapi();exit;
    }

    private function openapi() {
        $params['certi_app']       = 'open.login';
        $this->Certi = base_certificate::get('certificate_id');
        $this->Token = base_certificate::get('token');
        $params['certificate_id']  = $this->Certi;
        $params['format'] = 'image';
        /** 增加反查参数result和反查基础地址url **/
        $code = md5(microtime());
        base_kvstore::instance('ecos')->store('net.login_handshake',$code);
        $params['result'] = $code;
        /** 得到框架的总版本号 **/
        //$app_xml = kernel::single('base_xml')->xml2array(file_get_contents(app::get('base')->app_dir.'/app.xml'),'base_app');
        $obj_apps = app::get('base')->model('apps');
        $tmp = $obj_apps->getList('*',array('app_id'=>'base'));
        $app_xml = $tmp[0];
        $params['version'] = $app_xml['local_ver'];
        $params['url'] = kernel::base_url(1);
        /** end **/
        $token = $this->Token;
        $str   = '';
        ksort($params);
        foreach($params as $key => $value){
            $str.=$value;
        }
        $params['certi_ac'] = md5($str.$token);
        $http = kernel::single('base_httpclient');
        $http->timeout = 6;
        $result = $http->post(
        LICENSE_CENTER_V,
        $params
        );
        //$this->pagedata['open_api_url'] = LICENSE_CENTER_V .'?'. utils::http_build_query( $params );
        $tmp_res = json_decode($result, 1);
        if ($tmp_res)
        {
            // 存在异常
            if ($tmp_res['res'] == 'fail')
            {
                $this->pagedata['open_api_url'] = $tmp_res['msg'];
            }
            else
            {
                if ($tmp_res['res'] == 'succ')
                $this->pagedata['open_api_url'] = stripslashes($tmp_res['info']);
                else
                $this->pagedata['open_api_url'] = stripslashes($tmp_res);
            }
        }
        else
        $this->pagedata['open_api_url'] = stripslashes($tmp_res);
         
        return $this->pagedata['open_api_url'];
    }



    function set_main_menu(){
        $desktop_user = new desktop_user();
        $workground = $_POST['workgrounds'];
        $desktop_user->set_conf('fav_menus',$workground);
        header('Content-Type:text/jcmd; charset=utf-8');

        echo '{success:"'.app::get('desktop')->_("保存成功！").'"
        }';
    }





    function allmenu(){
        $desktop_user = new desktop_user();
        $menus = $desktop_user->get_work_menu();
        $desktop_user->get_conf('shortcuts_menus',$shortcuts_menus);

        foreach($menus['workground'] as $k=>$v){
            $v['menu_group'] = $menus['menu'][$k];
            $workground_menus[$k]  = $v;
        }
        $this->pagedata['menus'] = $workground_menus;
        $this->pagedata['shortcuts_menus'] = (array)$shortcuts_menus;
        $this->display('allmenu.html');

    }

    function main_menu_define(){
        $desktop_user = kernel::single('desktop_user');

        $menus = $desktop_user->get_work_menu();
        $user_id = $this->user->get_id();
        $desktop_user->get_conf('fav_menus',$fav_menus);
        //默认显示5个workground
        $workground_count = (app::get('desktop')->getConf('workground.count'))?(app::get('desktop')->getConf('workground.count')-1):5;
        if(!$fav_menus){
            $i = 0;
            foreach((array)$menus['workground'] as $key=>$value){
                //if($i++>$workground_count) break;
                $fav_menus[] = $key;
            }
        }

        $this->pagedata['fav_menus'] = (array)$fav_menus;
        $this->pagedata['menus'] = $menus;
        $this->display('main_menu_define.html');

    }


    private function _init_keyboard_setting() {
        $desktop_user = kernel::single('desktop_user');
        $desktop_user->get_conf('keyboard_setting',$keyboard_setting);
        $o = kernel::single('desktop_keyboard_setting');
        $json = $o->get_setting_json( $keyboard_setting );
        $this->pagedata['keyboard_setting_json'] = $json;
    }


    public function keyboard_setting() {
        $desktop_user = kernel::single('desktop_user');
        if( $_POST['keyboard_setting'] ) {
            $desktop_user->set_conf('keyboard_setting',$_POST['keyboard_setting']);
            $this->_init_keyboard_setting();
            echo $this->pagedata['keyboard_setting_json'];exit;
        }

        $desktop_user->get_conf('keyboard_setting',$keyboard_setting);

        //初始化数据
        $o = kernel::single('desktop_keyboard_setting');
        $o->init_keyboard_setting_data( $setting,$keyword,$keyboard_setting );

        foreach( $setting as $key => &$_setting ) {
            foreach( $_setting as &$row ) {
                if( $key!='导航菜单上的栏目' ) {
                    $default = array('ctrl','shift');
                    $o->set_default_control( $default,$row );
                } else {
                    $default = array('alt');
                    $o->set_default_control( $default,$row );
                }
            }
        }

        $this->pagedata['form_action_url'] = $this->app->router()->gen_url( array('app'=>'desktop','act'=>'keyboard_setting','ctl'=>'default') );
        $this->pagedata['keyword'] = $keyword;
        $this->pagedata['setting'] = $setting;
        $this->display('keyboard_setting.html');
    }


    function workground(){
        $wg = $_GET['wg'];
        if(!$wg){
            echo app::get('desktop')->_("参数错误");exit;
        }
        $user = new desktop_user();
        $menus = $this->app->model('menus');
        $group = $user->group();
        $aPermission = array();
        foreach((array)$group as $val){
            #$sdf_permission = $menus->dump($val);
            $aPermission[] = $val;
        }

        if($user->is_super()){
            $sdf = $menus->getList('*',array('menu_type' => 'menu','workground' => $wg));
        }
        else{
            $sdf = $menus->getList('*',array('menu_type' => 'menu','workground' => $wg,'permission' => $aPermission));
        }

        foreach((array)$sdf as $value){
            $url = $value['menu_path'];
            if($value['display'] == 'true'){
                $url_params = unserialize($value['addon']);
                if(count($url_params['url_params'])>0){
                    foreach((array)$url_params['url_params'] as $key => $val){
                        $parmas =$params.'&'.$key.'='.$val;
                    }
                }
                $url = $value['menu_path'].$parmas; break;
            }

        }
        $this->redirect('index.php?'.$url);

    }


    function alertpages(){
        $this->pagedata['goto'] = urldecode($_GET['goto']);
        $this->singlepage('loadpage.html');
    }



    function set_shortcuts(){
        $desktop_user = new desktop_user();
        $_POST['shortcuts'] = ($_POST['shortcuts']?$_POST['shortcuts']:array());
        foreach($_POST['shortcuts'] as $k=>$v){
            list($k,$v) = explode('|',$v);
            $shortcuts[$k] = $v;
        }
        $desktop_user->set_conf('shortcuts_menus',$shortcuts);
        header('Content-Type:text/jcmd; charset=utf-8');
        echo '{success:"'.app::get('desktop')->_("设置成功").'"}';
    }






    function status(){
        //return true;

        set_time_limit(0);
        /*  ob_start();
         if($_POST['events']){
         foreach($_POST['events'] as $worker=>$task){
         foreach(kernel::servicelist('desktop_task.'.$worker) as $object){
         $object->run($task,$this);
         }
         }
         }

         $flow = &$this->app->model('flow');
         if($flow->fetch_role_flow($this->user)){
         echo '<script>alert("'.app::get('desktop')->_("您有新短消息！").'");</script>';
         }


         //系统通知 desktop  未读条数
         $this->_get_notify_num();

         $output = ob_get_contents();
         ob_end_clean();
         header('Content-length: '.strlen($output));
         header('Connection: close');
         echo $output;*/

        #检测新套件登陆状态
        if(app::get('bizcloud')->is_actived()){
            $obj_prevent = kernel::single('bizcloud_login_prevent');
            #到中心检测，套件是否已退出登陆
            $rs = $obj_prevent->session_check();
            if(!$rs){
                kernel::single('base_session')->destory();
            }
        }


        app::get('base')->model('queue')->flush();
        //kernel::single('base_misc_autotask')->trigger();
        kernel::single('base_session')->close(false);
    }

    function desktop_events(){

        if($_POST['events']){
            foreach($_POST['events'] as $worker=>$task){
                foreach(kernel::servicelist('desktop_task.'.$worker) as $object){
                    $object->run($task,$this);
                }
            }
        }
    }


    function sel_region($path,$depth)
    {
        $path = $_GET['p'][0];
        $depth = $_GET['p'][1];

        header('Content-type: text/html;charset=utf8');
        //$local = app::get('ectools')->model('regions');
        //$ret = $local->get_area_select($path,array('depth'=>$depth));
        $local = kernel::single('ectools_regions_select');
        $ret = $local->get_area_select(app::get('ectools'),$path,array('depth'=>$depth));
        if($ret){
            echo '&nbsp;-&nbsp;'.$ret;
        }else{
            echo '';
        }
    }


    public function _get_notify_num() {
        $count = app::get('base')->model('rpcnotify')->count( array('status'=>'false') );
        if( $count ) {
            $js = '$$("#topbar .notify_num").getParent().setStyle("display","inline");';
        }
        echo '<script>'. $js .'$$("#topbar .notify_num")[0].set(\'text\',"'. ($count ? "($count)": '') .'");</script>';
    }

    public function about_blank(){
        echo '<html><head></head><body>ABOUT_BLANK_PAGE</body></html>';
    }

    public function checkVersion(){
        //kernel::single('taocrm_system')->setVersion(array('shop_nums'=>1,'order_nums'=>1,'member_nums'=>1));
        //kernel::single('taocrm_system')->setDefaultVersion();
        $str = '';
        $arr = array();
        $limit_title = '';
        $obj_taocrm_system = kernel::single('taocrm_system');

        if(!$obj_taocrm_system->checkShopNums($msg,$arr)){
            $limit_title = '店铺数';
            $str = "<h3>您的{$limit_title}达到".$arr['num']."！</h3>";
        }
        if(!$obj_taocrm_system->checkOrderNums($msg,$arr)){
            $limit_title = '订单数';
            $str = "<h3>恭喜，您的{$limit_title}突破".intval($arr['num']/10000)."0000！</h3>";
        }
        if(!$obj_taocrm_system->checkMemberNums($msg,$arr)){
            $limit_title = '客户数';
            $str = "<h3>恭喜，您的{$limit_title}突破".intval($arr['num']/10000)."0000！</h3>";
        }

        if(!empty($str)){
            $str = '<div class="sys_limit_tips"><div class="sub_div">'.$str.'当前您正在使用的CRM系统的'.$limit_title.'只支持到'.$arr['max_num'].'，<br/>为了防止您的新客户数据丢失，请尽快进行客户包升级！<br/>客户升级包按年收取，现在购买多年限时9折优惠!
            
            <!--div class="sub_contact">
                客服咨询：
                <a target="_blank" href="http://wpa.qq.com/msgrd?V=1&amp;Uin=1909839979Site=taocrm&amp;Menu=yes"><img border="0/" src="http://wpa.qq.com/pa?p=1:1909839979:47"></a>
            
                <a href="http://www.taobao.com/webww/ww.php?ver=3&amp;touid=taoex淘易:辰砂&amp;siteid=cntaobao&amp;status=2&amp;charset=utf-8" target="_blank"><img border="0" alt="点这里给我发消息" src="http://amos.alicdn.com/online.aw?v=2&amp;uid=taoex淘易:辰砂&amp;site=cntaobao&amp;s=2&amp;charset=utf-8">taoex淘易:辰砂</a>
                <br />联系电话：021-61972181
            </div-->
            
            </div></div>';
        }

        echo $str;
    }

    public function checkBindSms(){
        $db = kernel::database();
        $row = $db->selectrow('select shop_id from sdb_ecorder_shop where node_id!=""');
        $str = '';
        if(!$row){
            $objSms=kernel::single('market_service_smsinterface');
            if(!$objSms->isBind()){
                $str = '请绑定短信帐号,并充值!点击这里进行<a href="index.php?app=market&ctl=admin_sms_account&act=index"  target="_top">绑定</a>';
            }
        }
         
        echo $str;
    }


}
