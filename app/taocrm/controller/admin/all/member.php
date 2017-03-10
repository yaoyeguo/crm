<?php
class taocrm_ctl_admin_all_member extends desktop_controller{

    var $workground = 'taocrm.all_member';
    var $token = 'iloveshopex';
    protected static $middleware = '';
    protected static $shopObj = '';

    public function index()
    {
        $title = '全局客户列表';
        $actions = array();
        $baseFilter = array('parent_member_id'=>0);

        $actions[] = array(
            'label'=>'客户标签',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=addTag',
            'target'=>'dialog::{width:650,height:355,title:\'客户标签\'}'
        );
        
        $actions[] = array(
            'label'=>'添加客户',
            'href'=>'index.php?app=taocrm&ctl=admin_all_member&act=add_member',
        );
        
        $actions[] = array(
            'label'=>'加入黑名单',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=sms_blickname',
        );
        
        $actions[] = array(
            'label'=>'加入贵宾组',
            'submit'=>'index.php?app=taocrm&ctl=admin_member&act=addvip',
        );

        $params = array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'orderBy' => 'create_time DESC',//默认排序
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            //'use_buildin_filter'=>true,//暂时去掉高级筛选功能
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'finder_cols'=>'column_edit,member_id,column_tag,name,uname,mobile,order_total_num,points,tel,email,area,order_first_time,order_last_time,last_contact_time',
            //'force_index'=>' FORCE INDEX (ind_create_time) ',
        );
        
        if(count($_POST)<=1){
            $params['force_index'] = ' FORCE INDEX (ind_create_time) ';
        }
        $this->finder('taocrm_mdl_members', $params);
    }
    
    public function no_order()
    {
        $title = '无购物客户列表';
        $actions = array();
        $baseFilter = array('order_total_num'=>0,'parent_member_id'=>0);

        $this->finder('taocrm_mdl_members',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'orderBy' => '',//去掉默认排序
            //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            //'use_buildin_filter'=>true,//暂时去掉高级筛选功能
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
            'finder_cols'=>'member_id,name,uname,mobile,tel,email,addr,last_contact_time',
        ));
    }
    
    public function no_mobile(){

        $title = '无联系方式客户列表';
        $actions = array();
        $baseFilter = array('parent_member_id'=>0,'mobile'=>'');

        $this->finder('taocrm_mdl_members',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'orderBy' => '',//去掉默认排序
            //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            //'use_buildin_filter'=>true,//暂时去掉高级筛选功能
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
            'finder_cols'=>'member_id,name,uname,mobile,tel,email,addr,last_contact_time',
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

    protected function getShopObj()
    {
        if (self::$shopObj == '') {
            self::$shopObj = &app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
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

    public function _views_disabled()
    {
        $baseFilter = array('parent_member_id'=>0);
        $sub_menu = array();
        $sub_menu[] = array(
                'label' => '全部',
                'filter' => array(),
                'optional' => false,
        );



        $i = 0;
        $memberObj = &$this->app->model('members');
        foreach($sub_menu as $k => $v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $baseFilter);
            }
            $sub_menu[$k]['addon'] = $memberObj->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
            //            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&shop_id='.$v['filter']['shop_id'];
        }
         
        return $sub_menu;
    }

    public function add_member()
    {    
        $source = $_GET['source'];
        $mobile = $_GET['mobile'];
        
        if ($_POST){
            $this->save_all_member();
        }

        $shopObj = app::get('ecorder')->model('shop');
        $shopList=$shopObj->getList("*");
        $this->pagedata['shop_list']=$shopList;//店铺信息
        $this->pagedata['from'] = $_GET['from'];
        base_kvstore::instance('ecorder')->fetch('overall_member_props',$overall_member_props);
        $overall_member_props = json_decode($overall_member_props,true);

        //$overall_member_props = array_unique($overall_member_props);
        $this->pagedata['prop_name'] = $overall_member_props['prop_name'];
        $this->pagedata['prop_type'] = $overall_member_props['prop_type'];
        $this->pagedata['mem'] = array('contact'=>array('phone'=>array('mobile'=>$mobile)));

        $redirect_uri = 'index.php?app=taocrm&ctl=admin_all_member&act=add_member';
        $this->pagedata['source'] = $source;
        $this->pagedata['redirect_uri'] = base64_encode($redirect_uri);
        $this->page('admin/member/all/add.html');
    }
    
    //保存全局会员
    function save_all_member()
    {
            $mobile = trim($_POST['mobile']);
            $redirect_uri = 'index.php?app=taocrm&ctl=admin_all_member&act=index';
            
            if($_POST['source'] == 'callcenter'){
                $redirect_uri = 'index.php?app=market&ctl=admin_callcenter_callin&act=index&mobile='.$mobile;
            }
            $this->begin($redirect_uri);
            if(empty($_POST['uname'])){
                $this->end(false,'请输入客户昵称(ID)!');
            }

            if(empty($_POST['mobile'])){
                $this->end(false,'请输入手机号码!');
            }

            if(strlen($_POST['mobile']) != 11){
                $this->end(false,'输入的手机号码不是11位!');
            }

            $data=array();
            $channel_type = 'manual_entry';
            $data['uname']=$_POST['uname'];
            $data['channel_type']=$channel_type;
            $data['name']=$_POST['name'];
            $data['member_card']=$_POST['member_card'];
            $data['sex']=$_POST['gender'];
            $data['birthday']=strtotime($_POST['birthday']);
            $data['mobile']=$mobile;
            $data['email']=$_POST['email'];
            $data['tel']=$_POST['tel'];
            $data['qq']=$_POST['qq'];
            $data['wangwang']=$_POST['wangwang'];
            $data['weibo']=$_POST['weibo'];
            $data['weixin']=$_POST['weixin'];
            //$data['other_contact']=json_encode($_POST['other_contact']);
            $data['alipay_no']=$_POST['alipay_no'];
            $data['area']=$_POST['area'];
            $data['addr']=$_POST['addr'];
            $data['zip']=$_POST['zipcode'];
            $data['shop_id']=$_POST['shop_id'];

            //推荐验证开始
            $rec_mobile = !empty($_POST['commend_mobile']) ? trim($_POST['commend_mobile']) : false;
            $rec_code = !empty($_POST['commend_code']) ? trim($_POST['commend_code']) : false;

        if($rec_mobile || $rec_code){
                $rec_mod = app::get('taocrm')->model('members_recommend');
                if($rec_mobile && $rec_code)
                {
                    $comm_info = $rec_mod->dump(array('mobile'=>trim($rec_mobile)));
                    if(!$comm_info || $comm_info['self_code'] != $rec_code)
                        $this->end(false,'推荐人不存在!');
                }elseif($rec_mobile && !$rec_code)
                {
                    $comm_info = $rec_mod->count(array('mobile'=>trim($rec_mobile)));
                    if(!$comm_info)
                        $this->end(false,'推荐人不存在!');
                    else
                        $rec_code = $comm_info['self_code'];
                }
                $comm_info = $rec_mod->dump(array('self_code'=>trim($rec_code)));
                if(!$comm_info)
                    $this->end(false,'推荐人不存在!');
                $data['parent_code']=$rec_code;
            }

            //$data['is_vip']=$_POST['is_vip'];
            //$data['sms_blacklist']=$_POST['sms_blacklist'];
            //$data['edm_blacklist']=$_POST['edm_blacklist'];
            $data['remark']=$_POST['remark'];

            $prop = isset($_POST['prop_name']) ? $_POST['prop_name'] : '';
             
            if( ! kernel::single('taocrm_service_member')->acceptCreateMember($data,$channel_type)){
                $member_id = kernel::single('taocrm_service_member')->saveOverallMember($data,$prop);
                $e = kernel::single('taocrm_service_member')->error;
                if($e['status'] === false)
                    $this->end(false,$e['msg']);

                $this->end(true,'创建成功',$redirect_uri);
            }else{
                $this->end(false,'客户已存在!');
            }
        }

    //自定义客户属性
    function add_member_prop()
    {
        $this->pagedata['redirect_uri'] = $_GET['redirect_uri'];
         
        $overall_member_props = $this->app->model('members')->get_member_prop();

        $conf_prop_type = array(
            'text'=>'文字',
            'num'=>'数字',
            'date'=>'日期',
        );

        //var_dump($overall_member_props);
        $this->pagedata['conf_prop_type'] = $conf_prop_type;
        $this->pagedata['prop_name'] = $overall_member_props['prop_name'];
        $this->pagedata['prop_type'] = $overall_member_props['prop_type'];
        $this->page("admin/member/all/terminal.html");
    }

    function saveterminal(){
        if($_POST['redirect_uri']){
            $url = base64_decode($_POST['redirect_uri']);
        }else{
            $url = 'index.php?app=taocrm&ctl=admin_all_member&act=add_member_prop';
        }
        $this->begin($url);

        $overall_member_props = array(
            'prop_name'=>$_POST['prop_name'],
            'prop_type'=>$_POST['prop_type'],
        );
        base_kvstore::instance('ecorder')->store('overall_member_props',json_encode($overall_member_props));

        $rt = true;
        $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));
    }

    function member_prop()
    {
        //base_kvstore::instance('ecorder')->fetch('overall_member_props',$overall_member_props);
        //$overall_member_props = json_decode($overall_member_props,true);
        $memberOverAllPropertyObj = &$this->app->model('member_overall_property');
        $field = $memberOverAllPropertyObj->getTypeTagField();

        if($field){

            $searchFileds = $memberOverAllPropertyObj->getTypeSearch($field);

            $page = isset($_GET['page']) ? max (1, $_GET['page']) : 1;
            $pageSize = 20;

            if ($_POST) {
                $result = $memberOverAllPropertyObj->getTypeTagAllInfo( $page, $pageSize, $field, $_POST['search']);
                $data = $result['data'];
                $count = $result['count'];
                if (empty($data)) {
                    $message = '没有查询到相关记录';
                }
                $this->pagedata['wangwang_search_field'] = $_POST['search'];
            }
            else {
                $result = $memberOverAllPropertyObj->getTypeTagAllInfo($page, $pageSize, $field);
                 
                $data = $result['data'];
                $count = $result['count'];
            }

            $link = "index.php?app=taocrm&ctl=admin_all_member&act=member_prop&page=%d";
            $total_page = ceil($count / $pageSize);
            $pager = $this->app->render()->ui()->pager( array ('current' => $page, 'total' => $total_page, 'link' => $link ));
            $this->pagedata['wangwang_data'] = empty($data) ? '' : $data;
            $this->pagedata['pager'] = $pager;

            array_unshift($field,'客户名');
            $this->pagedata['wangwang_field'] = $field;
            $this->pagedata['wangwang_search_fields'] = $searchFileds;
            $redirect_uri = 'index.php?app=taocrm&ctl=admin_all_member&act=member_prop';
            $this->pagedata['redirect_uri'] = base64_encode($redirect_uri);

            $this->page("admin/member/all/member_prop.html");
        }else{
            $this->add_member_prop();
        }
    }

    function bind_member()
    {
        $cur_tab = isset($_GET['tab']) ? $_GET['tab'] : 'auto';
        $dimensions = isset($_GET['dimensions']) ? $_GET['dimensions'] : 'mobile';
        $members = array();
        $membersObj = $this->app->model('members');
        if($cur_tab == 'assign'){

        }else{
            $members = $membersObj->showBindMembers($dimensions);
            $this->pagedata['members'] = $members;
        }
   
        $this->pagedata['dimensions'] = $dimensions;
        $this->pagedata['cur_tab'] = $cur_tab;
        $this->pagedata['dimensionsList'] = array('qq'=>'QQ号','weixin'=>'微信号','weibo'=>'新浪微博','addr'=>'收货地址','alipay_no'=>'支付宝账号');
        $this->page("admin/member/all/bind_member.html");
    }

    function do_request_bind()
    {
        $membersObj = $this->app->model('members');
        $result = array('status'=>'succ');
        $dimensions = isset($_GET['dimensions']) ? $_GET['dimensions'] : 'mobile,email';
        $msg = '';
        $membersObj->doRequestBind($dimensions,& $msg);
        if(!empty($msg)){
            $result = array('status'=>'fail','msg'=>$msg);
        }

        echo json_encode($result);
    }

    function do_assign_bind()
    {
        $membersObj = $this->app->model('members');
        $msg = '';
        $membersObj->doAssignBind(json_decode($_POST['from_member_ids'],true),$_POST['to_member_id'],$msg);
        $result = array('status'=>'succ');
        if(!empty($msg)){
            $result = array('status'=>'fail','msg'=>$msg);
        }         
        echo json_encode($result);
    }

    function ajax_search_member(){
        $membersObj = &$this->app->model('members');
        $members = $membersObj->searchMemberByKeywords($_POST['search_keywords']);
        $result = array('status'=>'succ','data'=>$members);
        echo json_encode($result);
        exit;
    }

    //删除客户
    function delete_member(){
        $this->pagedata['member_id']=$_GET['member_id'];
        $this->pagedata['tagInfo']=$_GET['tagInfo'];
        $membersObj = $this->app->model('members');
        $data = $membersObj->getlist('parent_member_id,is_merger,uname,points,order_total_num',array('member_id'=>trim($_GET['member_id'])));
       //print_r($data);
        if($data[0]['is_merger']){
            $data_merger = $membersObj->getlist('uname',array('member_id'=>$data[0]['parent_member_id']));
            $this->pagedata['member_data']=array('uname'=>$data[0]['uname'],'is_merger'=>$data[0]['is_merger'],'parent_uname'=>$data_merger[0]['uname']);
        }
        $this->pagedata['data']=$data[0];
        $this->page('admin/member/delete_member.html');
    }

    function to_delete_member(){
        $this->begin('index.php?app=taocrm&ctl=admin_all_member&act=index');
        if($_POST['invalid_name']=='on'){
            //判断该用户是否有删除权限
            $user = kernel::single('desktop_user');
            $user_id = $user->get_id();
            $is_super = $user->is_super();
            $users = app::get('desktop')->model('users');
            $sdf_users = $users->dump($user_id);
            if(!$is_super && !$sdf_users['customer_delete']){
                $this->end(false,'抱歉，您没有删除客户的权限！');
            }

            $membersObj = $this->app->model('members');
            $data = $membersObj->getlist('points,order_total_num',array('member_id'=>trim($_POST['member_id'])));
            if(!empty($data)){
               if($data[0]['points']){
                    $this->end(false,'您好，该会员有积分，不支持删除！');
                }
                if($data[0]['order_total_num']){
                    $this->end(false,'您好，该会员有订单，不支持删除！');
                }
                if($_POST['tagInfo']){
                    $this->end(false,'您好，该会员有标签，不支持删除！');
                }
                $res = $membersObj->delete(array('member_id'=>trim($_POST['member_id'])));
                if($res){
                    $member_analysis_num = $this->app->model('member_analysis')->count(array('member_id'=>trim($_POST['member_id'])));
                    if($member_analysis_num > 0){
                        $res2 = $this->app->model('member_analysis')->delete(array('member_id'=>trim($_POST['member_id'])));
                        if($res2){
                            $this->end(true,'删除成功');
                        }else{
                            $this->end(false,'店铺客户表删除失败！');
                        }
                    }else{
                        $this->end(true,'删除成功');
                    }
                }else{
                    $this->end(false,'全局客户表删除失败！');
                }

            }else{
                $this->end(false,'此客户不存在！');
            }
        }else{
            $this->end(true);
        }
}
    //预览链接
    public function lottery_manage_href()
    {
        $id = !empty($_GET['self_code']) ? intval($_GET['self_code']) : false;

        //$url = "index.php?app=market&ctl=admin_weixin&act=lottery_manage";
       // $this->begin($url);
        base_kvstore::instance('desktop')->fetch('recommend_arr', $recommend_arr);
        $url = $recommend_arr['recommend_link'].'?code='.$id;

        $this->pagedata['url'] = $url;
        $this->pagedata['item_id'] = $id;
        $this->pagedata['img_url'] = 'index.php?app=taocrm&ctl=admin_all_member&act=lottery_manage_img&item_id='.$id;
        $this->display('admin/member/href.html');
    }
    //二维码
    public function lottery_manage_img()
    {
        $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

        if($_GET['m'] == 'd')
        {
            header('Content-Disposition: attachment; filename="two_dimension_code_'.$id.'.jpg"');
        }

        include ROOT_DIR."/script/phpqrcode/phpqrcode.php";//引入PHP QR库文件
        $errorCorrectionLevel = "L";
        $matrixPointSize = "4";
       // $url = kernel::base_url(1).'/index.php/market/site_weixin_ucenter/lottery?lottery_id='.$id;
        base_kvstore::instance('desktop')->fetch('recommend_arr', $recommend_arr);
        $url = $recommend_arr['recommend_link'].'?code='.$id;

        QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
    }
}
