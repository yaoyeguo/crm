<?php
class market_ctl_site_weixin_member extends base_controller{

    function __construct($app){
        parent::__construct($app);
    }
    /*
     * 绑定手机号
     */
    public function index()
    {
        $fromusername = $_GET['fromusername'];
        $my_xy = $_GET['my_xy']; //用户坐标，用于导航使用
        $objWxMember=app::get('market')->model("wx_member");
        $wxMember_data = $objWxMember->dump(array('FromUserName'=>$fromusername));
        if($wxMember_data['status'] == 'true'){
            $url = kernel::base_url(1).'/index.php/market/site_weixin_member/member_center?fromusername='.$fromusername.'&my_xy='.$my_xy;
            header('Location: '.$url);
            exit;
        }

        $this->pagedata['fromusername'] = $fromusername;
        $this->display('site/weixin/mobile_bind.html');
    }

    //创建并发送验证码
    public function send_passcode(){

        $mobile = $_POST['mobile'];
        if( !$mobile ) {
            $result = '请输入手机号码';
        }elseif( !preg_match("/^(1)\d{10}$/",$mobile) ) {
            $result = '手机号码格式错误，请重新输入。';
        }else{
            $kvstore = base_kvstore::instance('market');
            if($_GET['type'] == 'unbind'){
                $kvstore->fetch('passcode_wx_unbind_'.$mobile, $kv_passcode);//解绑
            }else{
                $kvstore->fetch('passcode_wx_'.$mobile, $kv_passcode);//绑定
            }

            if($kv_passcode) {
                $kv_passcode = json_decode($kv_passcode, 1);
            }

            if(isset($kv_passcode['create_time']) && $mobile==$kv_passcode['mobile'] && (time() - $kv_passcode['create_time'] < 600)){
                die('10分钟内只能申请一次验证码，请稍后再试。');
            }

            //获取验证码签名
            $model = app::get('ecorder')->model('sms_sign');
            $code_data = $model->getList('sms_sign',array('is_code_sign'=>'true'),0,1);
            if(empty($code_data)){
                die('缺少验证码短信签名。');
            }else{
                $v_code = $code_data[0]['sms_sign'];
            }

            $passcode = rand(1000,9999).date('s');
            $create_time = time();
            $kv_passcode = array(
                'mobile' => $mobile,
                'passcode' => $passcode,
                'create_time' => $create_time,
            );
            if($_GET['type'] == 'unbind'){
                $kvstore->store('passcode_wx_unbind_'.$mobile, json_encode($kv_passcode));//解绑
            }else{
                $kvstore->store('passcode_wx_'.$mobile, json_encode($kv_passcode));//绑定
            }

            $sms_content = "尊敬的用户，以下是您的手机验证码（6位数字）:{$passcode}。请在一小时内用此验证码进行验证。【".$v_code."】";
            $sms[] = array(
                'phones' => $mobile,
                'content'=>$sms_content
            );
            $data = kernel::single('market_service_smsinterface')->send_self_passcode(json_encode($sms));
            if($data['res'] == 'succ'){
                $result = '验证码已经发送到:'.$mobile;
            }else{
                $result = var_export($data,1);
            }
            //$result = var_export($data,1);
        }
        echo ($result);
    }

    //核对验证码
    public function check_passcode()
    {
        $check = 1;

        $mobile = $_POST['mobile'];
        $passcode = $_POST['passcode'];

        $kvstore = base_kvstore::instance('market');
        $kvstore->fetch('passcode_wx_'.$mobile, $kv_passcode);
        if($kv_passcode) {
            $objWxBindMember = $this->app->model('wx_bind_member_log');
            $cur = date('Y-m-d',time());
            $cur_begin = strtotime($cur." 00:00:00");
            $cur_end = strtotime($cur." 23:59:59");

            $kv_passcode = json_decode($kv_passcode, 1);
            if($kv_passcode['mobile'] != $mobile){
                if($check==1) die('手机号码不匹配:'.$kv_passcode['mobile']);
            }
            if($kv_passcode['passcode'] != $passcode){
                //当天超过三次不准绑定
                $filter = array('FromUserName'=>$_POST['fromusername'],'cur_begin'=>$cur_begin,'cur_end'=>$cur_end);
                $wbm_data = $objWxBindMember->get_log_data($filter);
                if(count($wbm_data) >= 4){
                    if($check==1) die('验证码错误超过四次，不能再次绑定！');
                }
                $log_data = array('FromUserName'=>$_POST['fromusername'],'mobile'=>$mobile,'passcode'=>$passcode,'create_time'=>time());
                $objWxBindMember->insert($log_data);
                $s_num = 3 - count($wbm_data);
                if($s_num){
                    if($check==1) die('验证码错误，请重新输入,还有'.$s_num.'次机会。');
                }else{
                    if($check==1) die('验证码错误，今天没有绑定的机会了。');
                }

            }
            if(isset($kv_passcode['create_time']) && (time() - $kv_passcode['create_time'] > 3600)){
                if($check==1) die('验证码过期，请重新申请。');
            }

            //每天每个手机号只能绑定成功一次
            $filter = array('FromUserName'=>$_POST['fromusername'],'cur_begin'=>$cur_begin,'cur_end'=>$cur_end,'bind_status'=>'true');
            $wbm_data = $objWxBindMember->get_log_data($filter);
            if(count($wbm_data) >= 1){
                if($check==1) die('每天每个手机号只能绑定成功一次!');
            }
        }else{
            if($check==1) die('请先点击获取验证码。');
        }

        $arr = array(
            'mobile'=>$mobile,
            'passcode'=>$passcode
        );
        $objWxMember=app::get('market')->model("wx_member");
        $wxmd =$objWxMember->dump(array('FromUserName'=>$_POST['fromusername']));
        if($wxmd['status'] == 'true'){
            if($mobile == $wxmd['mobile']){
                if($check==1) die('此手机号码已被绑定。');
            }else{
                if($check==1) die('您已经绑定过了，无需再次绑定。');
            }
        }

        //会员识别(判断手机号)
        $members = app::get('taocrm')->model("members");
        $members_data = $members->dump(array('mobile'=>$mobile));
        $data = array('mobile'=>$mobile,'status'=>'true');
        if($members_data){
            $data['member_id'] = $members_data['member_id'];
        }else{
            //如果此手机号在全局会员表中不存在记录，用此手机号新创建一条记录
            $membersObj = kernel::single("taocrm_members");
            $msg = '';
            $sdf = array('mobile'=>$mobile);
            $memberId = $membersObj->add($sdf,$msg);
            $data['member_id'] = $memberId['member_id'];
        }

        $objWxMember->update($data,array('FromUserName'=>$_POST['fromusername']));
        //自动发给微信会员一张会员卡
        $this->autoMemberCard($data['member_id']);
        //把微信会员中积分更新到会员积分中，微信会员表积分清零
        $wxMemberData = $objWxMember->dump(array('member_id'=>$data['member_id']));
        $id = kernel::single('taocrm_member_point')->update('',$data['member_id'],2,$wxMemberData['points'],'手机绑定微信积分',$msg,null,'wechat');
        $id = $objWxMember->updatePoint($wxMemberData['wx_member_id'],2,-$wxMemberData['points'],'手机绑定积分清零',$msg);

        //写入绑定日志
        $log_data = array('FromUserName'=>$_POST['fromusername'],'mobile'=>$mobile,'passcode'=>$passcode,'create_time'=>time(),'bind_status'=>'true');
        $objWxBindMember->insert($log_data);

        //验证后，验证码失效
        $kvstore->store('passcode_wx_'.$mobile, null);//绑定

        echo('succ');
    }
    /**
     * 自动生成会员卡
     * 1、添加会员卡类型；2、添加微信默认模板；3、生成会员卡（按年月为一个批次，如：201506）
     */
    function autoMemberCard($member_id){
        //1、添加会员卡类型
        $memberCardTypeObj = app::get('taocrm')->model('member_card_type');
        $re_type_data = $memberCardTypeObj->dump(array('type_code'=>'wx'));
        if(empty($re_type_data)){
            $typeData = array('type_name'=>'微信','type_code'=>'wx','create_time'=>time(),'update_time'=>time());
            $memberCardTypeObj->save($typeData);

            $re_type_data = $memberCardTypeObj->dump(array('type_code'=>'wx'));
        }

        //2、添加微信默认模板
        $memberCardTypeId = $re_type_data['id'];
        $memberCardTemplateObj = app::get('taocrm')->model('member_card_template');
        $re_template_data = $memberCardTemplateObj->dump(array('card_name'=>'微信会员卡'));
        if(empty($re_template_data)){
            $templateData = array('is_type_code'=>'1','card_name'=>'微信会员卡','member_card_type_id'=>$memberCardTypeId,'card_len'=>6,'card_pwd_len'=>4,'card_pwd_rule'=>'0','card_type'=>'1');
            $memberCardTemplateObj->save($templateData);

            $re_template_data = $memberCardTemplateObj->dump(array('card_name'=>'微信会员卡'));
        }

        //3、生成会员卡（按年月为一个批次，如：201506）
        $memberCardTemplateId = $re_template_data['id'];
        $memberCardObj = app::get('taocrm')->model('member_card');
        $msg = '';
        $re = $memberCardObj->doMakeCard_wx($memberCardTemplateId,1,$member_id,$msg);
        return $re;
    }

    /*
     *显示会员等级、卡号
     * get参数 fromusername
     */
    public function show_levelCard(){
        $fromusername = $_GET['fromusername'];
        $rs = array();
        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));
        if($wxMembeData){
            $objMembers = app::get('taocrm')->model('members');
            $membesData = $objMembers->dump(array('member_id'=>$wxMembeData['member_id']));
            $rs['member_card'] = $membesData['member_card'];
            if($membesData){
                $objMemberLevel = app::get('taocrm')->model('member_level');
                $membesLevelData = $objMemberLevel->dump(array('level_id'=>$membesData['level_id']));
                $rs['level_name'] = $membesLevelData['level_name'];
            }
        }

        //会员卡信息(card_type 为微信自动生成)
        $card_imgs = app::get('taocrm')->model('member_card_template')->dump(array('card_type'=>'1'));
        $rs['card_img'] = base_storager::image_path($card_imgs['card_img'],'s' );

        $this->pagedata['rs'] = $rs;
        $this->pagedata['fromusername'] = $fromusername;
        $this->pagedata['page_type'] = $_GET['page_type'];
        $this->pagedata['img_url'] = 'lottery_manage_img?card_no='.$rs['member_card'];
        $this->display('site/weixin/show_levelCard.html');
    }

    /*
     * 查看会员优惠劵
     * get参数 fromusername
     */
    public function show_coupon(){
        $fromusername = $_GET['fromusername'];
        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));
        $response_arr = $this->getCoupon($wxMembeData['member_id']);
        // if($response_arr['rsp'] == 'succ'){
        $data = $response_arr;
        $this->pagedata['all_num'] = count($data);
        $used_num = 0;
        $using_num = 0;
        $color_arr = array('first','second','third','fourth');
        foreach($data as $key=>$value){
            $data[$key]['from_time'] = date('Y-m-d H:i:s',$value['from_time']);
            $data[$key]['to_time'] = date('Y-m-d H:i:s',$value['to_time']);
            if($data['memc_codememc_used']){
                $used_num++;
            }else{
                $using_num++;
            }
        }
        //颜色数组
        $used_color = array();
        for($i = 0;$i < $used_num/4; $i++){
            $used_color = array_merge($used_color,$color_arr);
        }
        $using_color = array();
        for($i = 0;$i < $using_num/4; $i++){
            $using_color = array_merge($using_color,$color_arr);
        }
        $this->pagedata['coupons'] = $data;
        $this->pagedata['used_color'] = $used_color;
        $this->pagedata['using_color'] = $using_color;
        $this->pagedata['using_num'] = $using_num;
        $this->pagedata['used_num'] = $used_num;
        //}

        $this->display('site/weixin/show_coupon.html');
    }

    //获取优惠券信息
    public function getCoupon($member_id){
        //从会员店铺表中回去相应的shop_id
        $objMemberAnalysis = app::get('taocrm')->model('member_analysis');
        $shop_node_list = $objMemberAnalysis->getList('shop_id',array('member_id'=>$member_id));
        $shop_list = array();
        foreach($shop_node_list as $lk => $lv){
            $shop_list[$lk] = $lv['shop_id'];
        }
        //从会员店铺表查店铺类型
        $objShop = app::get('ecorder')->model('shop');
        $shop_type_list = $objShop->getList('shop_type,name,node_id',array('shop_id'=>$shop_list));
        //店铺类型为ecos.b2c时，查询优惠券
        $node_list = array();
        $i = 0;
        $shop_name_list = array();
        foreach($shop_type_list as $sk => $sv){
            if($sv['shop_type'] == 'ecos.b2c'){
                $node_list[$i] = $sv['node_id'];
                $shop_name_list[$sv['node_id']] = $sv['name'];
                $i++;
            }
        }

        //=================调用接口 start==============================
        $couponObj = kernel::single('market_service_coupon');
        $response_arr = array();
        foreach($node_list as $nk => $nv){
            $data = array('member_id'=>$member_id,'node_id'=>$nv);
            $response = $couponObj->getCouponFromEcstore($data);
            if($response['rsp'] == 'succ'){
                $res_data = json_decode($response['data'],true);
                foreach($res_data as $rkey => $rvalue){
                    $res_data[$rkey]['coupon_source'] = $shop_name_list[$nv];
                }
                $response_arr = array_merge($response_arr,$res_data);
            }
        }
        //=================调用接口 end================================
        return $response_arr;
    }

    /*
     * 签到
     * get参数 fromusername
     */
    public function sign_in(){
        $fromusername = $_GET['fromusername'];
        //获取微信会员表数据
        $objWxmember = $this->app->model('wx_member');
        $wxMember = $objWxmember->dump(array('FromUserName'=>$fromusername));

        //获取微信签到日志
        $cur = date('Y-m-d',time());
        $cur_begin = strtotime($cur." 00:00:00");
        $cur_end = strtotime($cur." 23:59:59");
        $wxSignInLogObj = $this->app->model('wx_sign_in_log');
        $wxSignInLogData = $wxSignInLogObj->getList('*',array('FromUserName'=>$fromusername),0,1,'id desc');
        $data = $wxSignInLogData[0];

        //今天是否已签到
        if($data['create_time'] >= $cur_begin && $data['create_time'] <= $cur_end){
            $data['sign_bool'] = true;
        }else{
            $data['sign_bool'] = false;
            $cur1 = date('Y-m-d',strtotime("$cur -1 day"));
            $cur_begin1 = strtotime($cur1." 00:00:00");
            $cur_end1 = strtotime($cur1." 23:59:59");
            if($data['create_time'] < $cur_begin1 || $data['create_time'] > $cur_end1){
                $data['sign_in_times'] = 0;
            }
        }

        //总积分
        if($wxMember['member_id']){
           // $allPointLogObj = app::get('taocrm')->model('all_points_log');
            //$data = $allPointLogObj->dump(array('member_id'=>$wxMember['member_id'],'point_desc'=>'微信签到送积分','op_time|bthan'=>$cur_begin,'op_time|sthan'=>$cur_end));
            $pointObj=kernel::single("taocrm_member_point");
            $msg = '';
            $sum_points = $pointObj->get($wxMember['member_id'],$msg,'',time());
            $data['sum_points'] = $sum_points['total_point'];
        }else{
          //  $objWxpointLog = $this->app->model('wx_point_log');
          //  $data = $objWxpointLog->dump(array('FromUserName'=>$fromusername,'create_time|bthan'=>$cur_begin,'create_time|sthan'=>$cur_end));
            $data['sum_points'] = $wxMember['points'];
        }

        //获取签到送多少积分
        $data['send_points'] = $this->get_send_points($fromusername);

        $this->pagedata['data'] = $data;
        $this->pagedata['fromusername'] = $fromusername;
        $this->display('site/weixin/sign_in.html');
    }

    public function get_send_points($fromusername){
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        $registRule = $wxuser['registRule'];
        $point = 0;
        if(isset($registRule['regist_point_rule'])){
            $wxSignInLogObj = app::get('market')->model('wx_sign_in_log');
            $wxSignInLogData = $wxSignInLogObj->dump(array('FromUserName'=>$fromusername));
            $registCount = $wxSignInLogData['sign_in_times'];

            if($registRule['regist_point_rule'] == 1){
                $point = $registRule['regist_point_rule_1_point'];
            }else if($registRule['regist_point_rule'] == 2){
                if(  $registCount > 1 && $registCount % $registRule['regist_point_rule_2_times'] == 0){
                    $point = $registRule['regist_point_rule_2_times_point'];
                }else{
                    $point = $registRule['regist_point_rule_2_point'];
                }
            }else{
                if( $registCount > 1 && $registCount >= $registRule['regist_point_rule_3_times']){
                    $point = $registRule['regist_point_rule_3_times_point'];
                }else{
                    $point = $registRule['regist_point_rule_3_point'];
                    if( $registCount > 1){
                        $point += ($registCount-1) * $registRule['regist_point_rule_3_go_point'];
                    }
                }
            }
            return $point;
        }
        return false;
    }
    //签到ajax
    public function do_sign_in(){
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        //获取微信会员的信息
        $fromusername = $_POST['fromusername'];
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));
        if(empty($wxMembeData)){
            $member_id = 0;
        }else{
            $member_id = $wxMembeData['member_id'];
        }
        $wxuser['wx_member_id'] = $wxMembeData['wx_member_id'];
        $point = kernel::single('market_service_weixin')->processRegistPoint($wxuser);
        if($point > 0){
            $re_bool = false;

            //从会员店铺表中回去相应的shop_id
            $objMemberAnalysis = app::get('taocrm')->model('member_analysis');
            $shop_node_list = $objMemberAnalysis->getList('shop_id',array('member_id'=>$member_id));
            if(empty($shop_node_list)){
                $re_bool = true;
            }else{
                //====================把crm签到信息更新到ecstore  调用接口 begin====================
                $shop_list = array();
                foreach($shop_node_list as $lk => $lv){
                    $shop_list[$lk] = $lv['shop_id'];
                }
                //从会员店铺表查店铺类型
                $objShop = app::get('ecorder')->model('shop');
                $shop_type_list = $objShop->getList('shop_type,node_id',array('shop_id'=>$shop_list,'shop_type'=>'ecos.b2c'));
                $couponObj = kernel::single('market_service_coupon');

                foreach($shop_type_list as $nk => $nv){
                    $update_data = array('node_id'=>$nv['node_id'],'member_id'=>$member_id,'signin_time'=>time());
                    $response = $couponObj->store_member_signin($update_data);
                    if(!empty($response['rsp'])){
                        $re_bool = true;
                    }
                }
                //====================把crm签到信息更新到ecstore  调用接口 end====================
            }

            if($re_bool){
                //写入签到日志
                $wxSignInLogObj = $this->app->model('wx_sign_in_log');
                $wxSignInLogObj->saveSignInLog(array('fromusername'=>$fromusername,'member_id'=>$member_id,'create_time'=>time()));

                echo 'succ';
            }else{
               echo '签到信息更新ecstore失败,请重试！';
            }
        }
        exit;
    }
    public function QRCode(){
        $fromusername = $_GET['fromusername'];
        //获取微信会员表数据
        $objWxmember = $this->app->model('wx_member');
        $wxMember = $objWxmember->dump(array('FromUserName'=>$fromusername));
        $objMemberRec = app::get('taocrm')->model('members_recommend');
        $memberRecData = $objMemberRec->dump(array('member_id'=>$wxMember['member_id']));

        //获取设置推荐码的信息
        base_kvstore::instance('desktop')->fetch('recommend_arr', $recommend_arr);

        $info = array('uname'=>$memberRecData['uname'],'text'=>$recommend_arr['recommend_text']);
        $id = $memberRecData['self_code'];
        $this->pagedata['info'] = $info;
        $this->pagedata['img_url'] = 'lottery_manage_img?item_id='.$id;
        $this->display('site/weixin/qrcode.html');
    }
    //会员推荐码（二维码）
    public function lottery_manage_img()
    {
        if(!empty($_GET['item_id'])){
            $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;

            include ROOT_DIR."/script/phpqrcode/phpqrcode.php";//引入PHP QR库文件
            $errorCorrectionLevel = "L";
            $matrixPointSize = "4";
            base_kvstore::instance('desktop')->fetch('recommend_arr', $recommend_arr);
            $url = $recommend_arr['recommend_link'].'/index.php/wap/passport-signup.html?referrals_code='.$id;
        }
        if(!empty($_GET['card_no'])){
            include ROOT_DIR."/script/phpqrcode/phpqrcode.php";//引入PHP QR库文件
            $errorCorrectionLevel = "L";
            $matrixPointSize = "8";
            $url = $_GET['card_no'];
        }

        QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
    }

    /*
     *会员中心
     * get参数 fromusername
     */
    public function member_center()
    {
        $fromusername = $_GET['fromusername'];
        $my_xy = $_GET['my_xy']; //用户坐标，用于导航使用
        $objWxMember=app::get('market')->model("wx_member");
        $wxMember_data = $objWxMember->dump(array('FromUserName'=>$fromusername));
        if($wxMember_data['status'] == 'false'){
            $url = kernel::base_url(1).'/index.php/market/site_weixin_member/index?fromusername='.$fromusername.'&my_xy='.$my_xy;
            header('Location: '.$url);
            exit;
        }

        $rs = array();
        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));
        if($wxMembeData){
            //获取优惠券的信息
            $response_arr = $this->getCoupon($wxMembeData['member_id']);
            $rs['coupon_num'] = count($response_arr);

            //订单信息
            $order_obj = app::get('ecorder')->model('orders');
            $cnt = $order_obj->count(array('member_id'=>$wxMembeData['member_id']));
            $rs['order_num'] = $cnt;

            //积分信息
            $pointObj=kernel::single("taocrm_member_point");
            $msg = '';
            $sum_points = $pointObj->get($wxMembeData['member_id'],$msg,'',time());
            $this->pagedata['sum_points'] = $sum_points['total_point'];

            //储值信息
            $msg = '';
            $membObj = kernel::single("taocrm_members");
            $sum_money = $membObj->get_member_stored_value(array('member_id'=>$wxMembeData['member_id']),$msg);
            $this->pagedata['sum_money'] = $sum_money['taocrm.stored.get']['stored_value'];

            //获取全局会员的信息
            $objMembers = app::get('taocrm')->model('members');
            $membesData = $objMembers->dump(array('member_id'=>$wxMembeData['member_id']));
            $rs['member_card'] = $membesData['member_card'];
            $rs['uname'] = $membesData['account']['uname'];
            if($membesData){
                $objMemberLevel = app::get('taocrm')->model('member_level');
                $membesLevelData = $objMemberLevel->dump(array('level_id'=>$membesData['level_id']));
                $rs['level_name'] = $membesLevelData['level_name'];
            }
        }

        //菜单配置
        base_kvstore::instance('desktop')->fetch('self_service_menu', $self_service_menu);
        $menu = array();
        if(!empty($self_service_menu)){
            foreach($self_service_menu as $v){
                $menu[$v] = true;
            }
        }
        $this->pagedata['menu'] = $menu;

        $this->pagedata['rs'] = $rs;
        $this->pagedata['fromusername'] = $fromusername;
        $this->pagedata['my_xy'] = $my_xy;
        $this->display('site/weixin/member_center.html');
    }

    /*
     * 手机解绑
     *  get参数 fromusername
     */
    public function unbind(){
        $fromusername = $_GET['fromusername'];
        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));

        $wxMembeData['fromusername'] = $fromusername;
        $this->pagedata['wxMembeData'] = $wxMembeData;
        $this->display('site/weixin/mobile_unbind.html');
    }

    public function do_unbind(){
        $check = 1;

        $mobile = $_POST['mobile'];
        $passcode = $_POST['passcode'];

        $kvstore = base_kvstore::instance('market');
        $kvstore->fetch('passcode_wx_unbind_'.$mobile, $kv_passcode);
        if($kv_passcode) {
            $kv_passcode = json_decode($kv_passcode, 1);
            if($kv_passcode['mobile'] != $mobile){
                if($check==1) die('手机号码不匹配:'.$kv_passcode['mobile']);
            }
            if($kv_passcode['passcode'] != $passcode){
                //当天超过三次不准解绑
                $objWxUnBindMember = $this->app->model('wx_unbind_member_log');
                $cur = date('Y-m-d',time());
                $cur_begin = strtotime($cur." 00:00:00");
                $cur_end = strtotime($cur." 23:59:59");
                $filter = array('FromUserName'=>$_POST['fromusername'],'cur_begin'=>$cur_begin,'cur_end'=>$cur_end);
                $wbm_data = $objWxUnBindMember->get_log_data($filter);
                if(count($wbm_data) >= 4){
                    if($check==1) die('验证码错误超过四次，不能再次解绑！');
                }
                $log_data = array('FromUserName'=>$_POST['fromusername'],'mobile'=>$mobile,'passcode'=>$passcode,'create_time'=>time());
                $objWxUnBindMember->insert($log_data);
                $s_num = 3 - count($wbm_data);
                if($s_num){
                    if($check==1) die('验证码错误，请重新输入,还有'.$s_num.'次机会。');
                }else{
                    if($check==1) die('验证码错误，今天没有解绑的机会了。');
                }
            }
            if(isset($kv_passcode['create_time']) && (time() - $kv_passcode['create_time'] > 3600)){
                if($check==1) die('验证码过期，请重新申请。');
            }
            //验证后验证码失效
            $kvstore->store('passcode_wx_unbind', null);//解绑
        }else{
            if($check==1) die('请先点击获取验证码。');
        }

        $fromusername = $_POST['fromusername'];
        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));
        if($wxMembeData){
            //查询是否有积分(1.有积分的，积分回写和微信会员表的status改为否未绑定；2.无积分的，只清空微信会员表的status改为是绑定)
            $objMemberPoints = app::get('taocrm')->model('member_points');
            $points_list = $objMemberPoints->getList('points,id',array('points_type'=>'wechat','member_id'=>$wxMembeData['member_id']));
            if(!empty($points_list)){
                //更新积分
                $msg = '';
                $res = kernel::single('taocrm_member_point')->update_points($wxMembeData,$msg);
                if($res){
                    echo 'succ';
                }else{
                    echo '解绑失败，请重试！';
                }
            }else{
                //微信会员表的status改为否未绑定
                $objWxMember->update(array('status'=>'false'),array('FromUserName'=>$wxMembeData['FromUserName']));
                echo 'succ';
            }

        }else{
            echo '微信会员不存在！';
        }
        exit;
    }
    /*
     * 收货地址管理
     */
    public function receiving_address_list(){
        $fromusername = $_GET['fromusername'];

        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));
        if(empty($fromusername)){
            $wxMembeData['member_id'] = $_GET['member_id'];
        }

        //地址列表
        $addrObj = app::get('taocrm')->model('member_receivers');
        $addrs = $addrObj->db->select('SELECT * FROM sdb_taocrm_member_receivers WHERE member_id='.$wxMembeData['member_id']);
        foreach($addrs as $key => $value){
            $area = explode(':',$value['area']);
            $addrs[$key]['address'] = str_replace('/','',$area[1]).$value['addr'];
            $addrs[$key]['mobile'] = substr_replace($value['mobile'], "****", 3,4);
        }

        $this->pagedata['addrs'] = $addrs;
        $this->pagedata['list_cnt'] = count($addrs);
        $this->pagedata['data'] = $wxMembeData;
        $this->display('site/weixin/receiving_address_list.html');
    }

   /* //默认收货地址 ajax
    public function do_default_addr(){
        $receiver_id = trim($_POST['receiver_id']);
        $member_id = trim($_POST['member_id']);
        $addrObj = app::get('taocrm')->model('member_receivers');
        //旧的默认地址改为非默认
        $addrObj->db->exec('UPDATE sdb_taocrm_member_receivers SET default_addr="false" WHERE `member_id` = '.$member_id.' and default_addr="true" LIMIT 1;');
        //更新新的默认地址
        $addrObj->db->exec('UPDATE sdb_taocrm_member_receivers SET default_addr="true" WHERE `receiver_id` = '.$receiver_id.' LIMIT 1;');
        echo '设置成功！';
        exit;
    }*/

    //新增/编辑收货地址
    public function receiving_address_edit(){
        $member_id = $_GET['member_id'];
        //当订单确认页面无地址时，会请求过来新增地址
        if(!empty($_GET['gift_id'])){
            $this->pagedata['gift_id'] = $_GET['gift_id'];
        }

        if(!empty($_GET['receiver_id'])){
            $addrObj = app::get('taocrm')->model('member_receivers');
            $addrs = $addrObj->db->selectRow('SELECT * FROM sdb_taocrm_member_receivers WHERE receiver_id='.trim($_GET['receiver_id']));
            $area = explode(':',$addrs['area']);
            $addrs['area_show'] = str_replace('/','',$area[1]);

            $this->pagedata['addrs'] = $addrs;
        }

        $wx_data = app::get('market')->model('wx_member')->dump(array('member_id'=>$member_id));
        $this->pagedata['wx_id'] = $wx_data['FromUserName'];

        $this->pagedata['member_id'] = $member_id;
        $this->display('site/weixin/receiving_address_edit.html');
    }

    //收货地址数据保存
    public function receiving_address_save(){
        $taocrm_service_member = kernel::single('taocrm_service_member');
        $save_data = $_POST;
        if($_POST['area']){
            $address = explode(':',$_POST['area']);
            $address_code = explode('/',$address[0]);
            $save_data['state'] = $address_code[0];
            $save_data['city'] = $address_code[1];
            $save_data['district'] = $address_code[3];
        }
        $save_data['create_time'] = time();

        //默认收货地址（新增首个收货地址默认为默认收货地址）
        $addrObj = app::get('taocrm')->model('member_receivers');
        $addrs = $addrObj->db->select('SELECT * FROM sdb_taocrm_member_receivers WHERE member_id='.trim($_POST['member_id']));
        if(empty($addrs) || $save_data['default_addr'] == 'true'){
            $save_data['default_addr'] = 'true';
        }

        if(count($addrs) > 9){
            echo '每个会员最多有10个收货地址！';
            exit;
        }

        //旧的默认地址改为非默认
        if($save_data['default_addr'] == 'true'){
            $addrObj->db->exec('UPDATE sdb_taocrm_member_receivers SET default_addr="false" WHERE `member_id` = '.trim($_POST['member_id']).' and default_addr="true" LIMIT 1;');
        }

        $re = $taocrm_service_member->saveMemberReceiver($save_data);
        echo 'succ';
        exit;
    }

    //收货地址删除
    public function receiving_address_delete(){
        $receiver_id = trim($_POST['receiver_id']);
        $addrObj = app::get('taocrm')->model('member_receivers');
        $addrObj->db->exec('DELETE FROM `sdb_taocrm_member_receivers` WHERE `receiver_id` = '.$receiver_id.' LIMIT 1;');
        echo '删除成功！';
        exit;
    }

    //选择收货地址
    public function select_receiving_address(){
        $wx_id = $_GET['wx_id'];
        $gift_id = $_GET['gift_id'];

        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$wx_id));
        if(!empty($wxMembeData)){
            $member_id = $wxMembeData['member_id'];
        }

        //地址列表
        $addrObj = app::get('taocrm')->model('member_receivers');
        $addrs = $addrObj->db->select('SELECT * FROM sdb_taocrm_member_receivers WHERE member_id='.$member_id);
        $default_addr = array();
        $default_key = 0;
        $is_selected = fasle;
        foreach($addrs as $key => $value){
            $area = explode(':',$value['area']);
            $addrs[$key]['address'] = str_replace('/','',$area[1]).$value['addr'];

            if($value['default_addr'] == 'true'){
                $default_addr = $value;
                $default_addr['address'] = str_replace('/','',$area[1]).$value['addr'];
                $default_key = $key;
            }

            $addrs[$key]['mobile'] = substr_replace($value['mobile'], "****", 3,4);

            //是否有被选中收货地址
            if($value['selected'] == 'true'){
                $is_selected = true;
            }
        }
        //默认收货地址放到第一个
        $addrs[$default_key] = $addrs[0];
        $addrs[0] = $default_addr;

        $this->pagedata['addrs'] = $addrs;
        $this->pagedata['wx_id'] = $wx_id;
        $this->pagedata['gift_id'] = $gift_id;
        $this->pagedata['is_selected'] = $is_selected;
        $this->display('site/weixin/select_receiving_address.html');
    }

    //ajax修改是否被选中
    public function do_selected_addr(){
        $receiver_id = trim($_POST['receiver_id']);
        $member_id = trim($_POST['member_id']);
        $addrObj = app::get('taocrm')->model('member_receivers');
        //旧的选中地址改为非默认
        $re = $addrObj->db->exec('UPDATE sdb_taocrm_member_receivers SET selected="false" WHERE `member_id` = '.$member_id);
        //更新新的选中地址
        $addrObj->db->exec('UPDATE sdb_taocrm_member_receivers SET selected="true" WHERE `receiver_id` = '.$receiver_id.' LIMIT 1;');
        echo '设置成功！';
        exit;
    }

    //设置页面
    public function set_info(){
        $fromusername = $_GET['fromusername'];

        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));
        $wxMembeData['mobile'] = substr_replace($wxMembeData['mobile'], "****", 3,4);

        $this->pagedata['data'] = $wxMembeData;
        $this->pagedata['fromusername'] = $fromusername;
        $this->display('site/weixin/set_info.html');
    }

    /*
   *我的储值
   * get参数 fromusername
   */
    public function my_money(){
        $fromusername = $_GET['fromusername'];
        $db = kernel::database();
        $row = $db->selectrow('select tb_nick,mobile,points,member_id from sdb_market_wx_member where FromUserName="'.$fromusername.'"');
        if($row){
            $memberId = $row['member_id'];

            if($memberId){
                $pointObj=kernel::single("taocrm_member_point");

                //总积分
                $msg = '';
                $membObj = kernel::single("taocrm_members");
                $sum_money = $membObj->get_member_stored_value(array('member_id'=>$memberId),$msg);
                $this->pagedata['sum_money'] = $sum_money['taocrm.stored.get']['stored_value'];

                //全部积分日志
                $msg1 = '';
                $memberPointLog = $membObj->get_member_stored_value_log(array('member_id'=>$memberId),$msg1);
                $memberPointLogList = $memberPointLog['taocrm.storedlog.get']['logList'];

                $memberPointLogList_add = array();
                $memberPointLogList_minus = array();
                $j = 0;
                $i = 0;
                foreach($memberPointLogList as $k=>$v){
                    $member_data = app::get('taocrm')->model('members')->dump(array('member_id'=>$memberId),'uname');
                    $memberPointLogList[$k]['user_name'] = $member_data['account']['uname'];
                    $memberPointLogList[$k]['value_time'] = date('Y-m-d',$v['value_time']);
                    if($v['change_amount'] > 0){
                        $memberPointLogList[$k]['change_amount'] = '+'.$v['change_amount'];
                        $memberPointLogList_add[$j] = $memberPointLogList[$k];
                        $j++;
                    }

                    if($v['change_amount'] < 0){
                        $memberPointLogList_minus[$i] = $memberPointLogList[$k];
                        $i++;
                    }
                }
            }
        }
        $this->pagedata['memberPointLogList'] = $memberPointLogList;
        $this->pagedata['memberPointLogList_add'] = $memberPointLogList_add;
        $this->pagedata['memberPointLogList_minus'] = $memberPointLogList_minus;

        $this->display('site/weixin/my_money.html');
    }
}
