<?php
class market_ctl_admin_coupon_ecstore extends desktop_controller{
    var $workground = 'market.sales';

    var $pagelimit = 10;

    var $is_debug = false;

    public function __construct($app){
        parent::__construct($app);
        $this->interfacePacketName = 'ShopMemberAnalysis';
        $this->interfaceMethodName = 'SearchMemberAnalysisList';
        $this->interfaceTableName = 'taocrm_mdl_middleware_member_analysis';
    }

    public function index(){
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name',array('node_type'=>'ecos.b2c'));
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }
        $view = (isset($_GET['view']) && intval($_GET['view']) >= 0 ) ? intval($_GET['view']) : 0;
        if ($_GET['view']!=0){
            $view=$view-1;
            $shop_id =$shops[$view];
        }

        $actions = array(
        array(
                'label'=>'获取优惠券',
                'href'=>'index.php?app=market&ctl=admin_coupon_ecstore&act=getCoupon',
                'target'=>'dialog::{onClose:function(){window.location.reload();},width:650,height:355,title:\'获取优惠券\'}'
                ),
                );
                $baseFilter = array();
                $baseFilter =   array('shop_id|in' => $shops);
                //        if ($view == 0) {
                //            $baseFilter =   array('shop_id|in' => $shops);
                //        }
                //        else {
                //            $baseFilter =   array('shop_id|in' => $shops);
                //        }
                $this->finder('market_mdl_coupon_ecstore',array(
            'title'=>'Ecstore优惠券',
            'actions'=>$actions,
            'use_buildin_recycle'=>false,
            'base_filter' => $baseFilter,
                ));
    }

    function getCoupon(){
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name',array('node_type'=>'ecos.b2c'));
        $this->pagedata['shopList'] = $shopList;
        $this->display("admin/coupon/ecstore.html");
    }

    function toGetCoupon(){

        if(!isset($_POST['shop_id']) || empty($_POST['shop_id'])){
            echo json_encode(array('status'=>false,'msg'=>'请选择店铺!'));
            exit;
        }

        $msg = '';
        $shopId = $_POST['shop_id'];
        $couponList = kernel::single('market_rpc_request_coupon_ecstore')->getCoupon($shopId,$msg);
        $result = array();
        if(!empty($msg)){
            $result['status'] = false;
            $result['msg'] = $msg;
        }else{
            $result['status'] = true;
            $objCouponEcstore = app::get('market')->model('coupon_ecstore');
            $result['info'] =  $objCouponEcstore->saveCoupon($shopId,$couponList);
        }

        echo json_encode($result);
        exit;
    }

    public function _views(){
        $oRecord = $this->app->model('coupon_ecstore');

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name',array('node_type'=>'ecos.b2c'));
        foreach((array)$shopList as $v){
            $shops[] = $v['shop_id'];
        }

        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> array('shop_id|in' => $shops),
            'optional'=>false,	
        );

        foreach((array)$shopList as $v){
            $sub_menu[] = array(
            'label'=>$v['name'],
            'filter'=> array('shop_id' => $v['shop_id']),
            'optional'=>false,	
            );
        }

        $i=0;
        foreach($sub_menu as $k=>$v){
            $count =$oRecord->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=market&ctl=admin_coupon_ecstore&act=index&view='.$i++;
        }
        return $sub_menu;
    }

    function sendCoupon($shopId,$couponId){
        if(empty($shopId)){
            echo 'no shop id';
            exit;
        }

        if(empty($couponId)){
            echo 'no shop id';
            exit;
        }

        $couponEcstoreObj = app::get('market')->model('coupon_ecstore');
        $coupon = $couponEcstoreObj->dump($couponId);
        if($coupon['is_del'] == 'y'){
            echo '优惠劵已删除';
            exit;
        }
        $this->pagedata['coupon'] = $coupon;


        $filter = array();
        $filter['parent_id'] = 0;
        $filter['shop_id'] = $shopId;
        $groups = app::get('taocrm')->model('member_group')->getList('*', $filter, 0, -1, 'group_id ASC');
        $this->pagedata['groups'] = $groups;
        $this->pagedata['shopId'] = $shopId;
        $this->pagedata['couponId'] = $couponId;

        $templates_obj = app::get('market')->model('sms_templates');
        $templates_data=$templates_obj->getList("*",array('status'=>1));
        $this->pagedata['templates_data']=$templates_data;//短信模板

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shop_id_data=$shopObj->dump(array('shop_id'=>$shopId));
        $this->pagedata['shop_name'] = $shop_id_data['name'];
        $this->pagedata['sms_sign'] = $shopObj->get_sms_sign($shopId);


        //淘名片短地址
        $vcard_url = '';
        $shopVcardObj = app::get(ORDER_APP)->model('shop_vcard');
        $rs = $shopVcardObj->dump(array('shop_id'=>$shopid),'vcard_url');
        if($rs){
            $vcard_url = $rs['vcard_url'];
        }

        $this->pagedata['vcard_url'] = $vcard_url;

        $this->display('admin/coupon/send_coupon.html');
    }

    function countChilds($group_id){
        $group_id = intval($group_id);
        $oMemberGroup = app::get('taocrm')->model('member_group');
        $rs = $oMemberGroup->count(array('parent_id'=>$group_id));
        $oMemberGroup->update(array('childs'=>$rs),array('group_id'=>$group_id));
    }

    function getChildGroup() {
        $parent_id = intval($_POST['parent_id']);
        $oMemberGroup = app::get('taocrm')->model('member_group');
        $rs = $oMemberGroup->getList('*',array('parent_id'=>$parent_id));
        if($rs) {
            foreach($rs as $k=>$v) {
                $rs[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
        }
        echo(json_encode($rs));
    }

    public function refresh(){
        $group_id = intval($_POST['group_id']);
        $shop_id = trim($_POST['shop_id']);
        $countMembers = 0;
        if($group_id>0) {
            $countMembers = $this->refresh_group_member($group_id);
        }

        echo $countMembers;exit;
    }

    public function refresh_group_member($group_id){
        $oGroup = app::get('taocrm')->model('member_group');
        $data = $oGroup->dump($group_id);
        $data['filter'] = unserialize($data['filter']);
        //获得客户数量
        $middlewareContent = kernel::single('taocrm_middleware_connect');
        $tableName = $this->interfaceTableName;
        $data['packetName'] = $this->interfacePacketName;
        $data['methodName'] = $this->interfaceMethodName;
        $countMembers = $middlewareContent->count($tableName, $data);
        $filter = array('group_id' => $group_id,);
        $ret = $oGroup->update(array(
            'members' => $countMembers,
            'update_time' => time()
        ),$filter);

        return $countMembers;
    }

    function selectTemplate($template_id){
        $templates_obj = app::get('market')->model('sms_templates');
        $content_data=$templates_obj->dump(array('template_id'=>$template_id),"content");
        echo $content_data['content'];
    }

    //编辑后保存模板
    function edit_save($template_id){
        $templates_obj = app::get('market')->model('sms_templates');
        $templates_obj->update(array('content'=>$_POST['message_text']), array('template_id'=>$template_id));
        echo $_POST['message_text'];
    }

    function goStep3(){
        $shopObj = app::get('ecorder')->model('shop');
        $shop = $shopObj->dump(array('shop_id'=>$_POST['shop_id']),'name');

        $activeObj = app::get('market')->model('active');
        $active_name = '优惠劵营销活动【'.$shop['name'].'】-'.date('Y-m-d H:i:s');

        $coupon_id = $_POST['coupon_id'];
        $shop_id = $_POST['shop_id'];
        $group_id = $_POST['group_id'];
        $active_id = $_POST['active_id'];


        if(!$active_id){
            $data = array(
        'active_name'=>$active_name,
        'shop_id'=>$shop_id,
        'is_active'=>'sel_coupon',
        'create_time'=>time(),
        'start_time'=>time(),
        'end_time'=>time() + 1296000,
        'type'=>serialize(array('sms')),
        'member_list'=> serialize(array('group_id:'.$group_id))
            );
            $activeObj->save($data);
            $active_id = $data['active_id'];
        }

        if(!empty($active_id)){
            $previewInfo = kernel::single('taocrm_middleware_activity')->GetMarketActivityInfo($active_id);
            $result = array();
            if ($previewInfo) {
                $info = array('count'=>$previewInfo['Count'],'send'=>$previewInfo['Send'],'unsend'=>$previewInfo['UnSend']);
                $result = array('status'=>'succ','info'=>$info,'active_id'=>$active_id);
            }else{
                $result = array('status'=>'fail','msg'=>'获取预览结果失败,稍后请重试!');
            }
        }else{
            $result = array('status'=>'fail','msg'=>'创建营销活动结束');
        }

        echo json_encode($result);
        exit;
    }

    public function getSmsActiveStatus()
    {
        base_kvstore::instance('market')->fetch('account', $account);
        if ($account) {
            $account = unserialize($account);
        }
        return $account;
    }

    function execSend(){
        set_time_limit(360);

        $coupon_id = $_POST['coupon_id'];
        $shop_id = $_POST['shop_id'];
        $group_id = $_POST['group_id'];
        $active_id = $_POST['active_id'];
        $unsubscribe = intval($_POST['unsubscribe']);
        $template_id = $_POST['template_id'];
        $templete = urldecode($_POST['templete']);
        $shopName = $_POST['shopName'];


        $result = array('status'=>'fail','msg'=>'未知错误');
        //检查帐号是否存在
        $account = $this->getSmsActiveStatus();
        if (empty($account)) {
            $result = array('status'=>'fail','msg'=>'短信帐号未绑定');
            echo json_encode($result);
            exit;
        }

        $previewInfo = kernel::single('taocrm_middleware_activity')->GetMarketActivityInfo($active_id);
        $is_send_salemember = $_POST['is_send_salemember'];
        if ($is_send_salemember == 1) {
            $all_send_members = $previewInfo['Send'];
        }
        else {
            $all_send_members = $previewInfo['Send'] - $previewInfo['ReSend'];
        }

        if($all_send_members <= 0){
            $result = array('status'=>'fail','msg'=>'您的客户数为0，无法发送');
            echo json_encode($result);
            exit;
        }

        $smsInfo=$this->getSmsCount($active_id);
        if($smsInfo['smscount'] == -1) {
            $result = array('status'=>'fail','msg'=>'您的短信账号出现异常，请检查配置信息');
            echo json_encode($result);
            exit;
        }

        if($smsInfo['overcount'] < $all_send_members) {
            $result = array('status'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            echo json_encode($result);
            exit;
        }

        $active_obj = app::get('market')->model('active');
        $rs_active = $active_obj->dump($active_id);

        //设置店铺的最后营销时间
        $shop_obj = app::get('ecorder')->model('shop');
        $shop_obj->set_last_market_time($shop_id);

        $templates_obj = app::get('market')->model('sms_templates');
        $smsTemplate = $templates_obj->dump(array('template_id'=>$template_id),"title");

        //优惠劵发送日志
        $couponEcstoreSendlogObj = app::get('market')->model('coupon_ecstore_sendlog');
        $data = array(
            'coupon_id'=>$coupon_id,
            'shop_id'=>$shop_id,
            'is_send'=>'sending',
            'coupon_total_num'=>$all_send_members,
            'created'=>time()
        );
        $couponEcstoreSendlogObj->save($data);
        if(!isset($data['log_id'])){
            $result = array('status'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            echo json_encode($result);
            exit;
        }
        
        //保存短信帐号信息
        $couponEcstorelogObj = app::get('market')->model('coupon_ecstore');
        $coupon = $couponEcstorelogObj->dump($coupon_id,'ecstore_coupon_id');
        $active_remark = array(
            'shopName'=>$shopName,
            'entId'=>$smsInfo['entId'],
            'entPwd'=>$smsInfo['entPwd'],
            'license'=>$smsInfo['license'],
            'taskId'=>'activity'.$active_id,
            'coupon_id'=>$coupon_id,
        	'ecstore_coupon_id'=>$coupon['ecstore_coupon_id'],
            'channelType'=>'ecstore',
            'coupon_log_id'=>$data['log_id']
        );

        $active_obj->update(
        array(
            'active_remark'=>json_encode($active_remark),
            'total_num'=>$previewInfo['Count'],
            'valid_num'=>$all_send_members,
            'is_send_salemember' => $is_send_salemember,
        //'is_active'=>'finish',
            'unsubscribe'=>$unsubscribe,
        	'templete_title'=>$smsTemplate['title'],
            'templete'=>$templete,
            'template_id'=>$template_id,
        ),
        array('active_id'=>$active_id)
        );
        $result = array('status'=>'fail','msg'=>json_encode($smsInfo));

        $result = kernel::single('taocrm_middleware_activity')->ExecMarketActivity($active_id);
        if($result['res'] == 'success'){
            $result['status'] = 'succ';
        }

        echo json_encode($result);
        exit;
    }

    public function getSmsCount($active_id){
        /*return $infoarray=array(
         'smscount'=>1000,
         'blocknum'=>0,
         'overcount'=>1000,
         'entId'=>1111,
         'entPwd'=>2222,
         'license'=>3333,
         );*/

        $active_obj = app::get('market')->model('active');
        $sendObj = kernel::single('market_service_smsinterface');
        $memsms=kernel::single('market_service_sms');
        $send_info = $sendObj->get_usersms_info();//get_usersms_info

        if ($send_info['res']=='succ'){
            $month_residual=$send_info['info']['month_residual']; //短信总条数 all_residual
            $blocknums=intval($send_info['info']['block_num']);//冻结短信条数
        }else{
            error_log(var_export($send_info,1), 3, DATA_DIR.'/log.sms_error.php');
            $month_residual=-1; //当前可用的短信数
            $blocknums=-1; //冻结短信条数
        }

        //测试信息
        if($this->is_debug == true) {
            $month_residual = 10000*100;
            $blocknums = 100;
            $infoarray=array(
                'smscount'=>$month_residual,
                'blocknum'=>$blocknums,
                'overcount'=>$month_residual- $blocknums,
                'entId'=> isset($send_info['info']['account_info']['entid']) ? $send_info['info']['account_info']['entid'] : '2bcefef',
                'entPwd'=>$send_info['entPwd'],
                'license'=>$send_info['license'],
            );
        }
        else {
            //entId,entPwd,license
            $infoarray=array(
                'smscount'=>$month_residual,
                'blocknum'=>$blocknums,
                'overcount'=>$month_residual- $blocknums,
                'entId'=>$send_info['info']['account_info']['entid'],
                'entPwd'=>$send_info['entPwd'],
                'license'=>$send_info['license'],
            );
        }
        return $infoarray;
    }

    public function getSendlogItemList($logId,$page){
        $pagelimit = 20;
        $page = $page ? $page : 1;
        $orderItems = app::get('market')->model('coupon_ecstore_sendlog_item')->getPager(array('log_id'=>$logId),'member_id,is_sms,is_coupon,reason,sendtime',$pagelimit * ($page - 1), $pagelimit);
        $objMembers = app::get('taocrm')->model('members');
        $isType = array('0'=>'未发送','1'=>'已发送');
        foreach($orderItems['data'] as $k=>$v){
            $member = $objMembers->dump($v['member_id'],'uname');
            $orderItems['data'][$k]['uname'] = $member['account']['uname'];
            $orderItems['data'][$k]['is_sms'] = $isType[$v['is_sms']];
            $orderItems['data'][$k]['is_coupon'] = $isType[$v['is_coupon']];
            $v['reason'] = str_replace('""', '"', stripslashes($v['reason']));
            $reason = json_decode($v['reason'],true);
            $orderItems['data'][$k]['reason'] = $reason['data'];
        }

        $count = $orderItems ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $this->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=market&ctl=admin_coupon_ecstore&act=getSendlogItemList&p[0]='.$logId.'&p[2]=%d' ) );
        $this->pagedata['pager'] = $pager;

        $this->pagedata['items'] = $orderItems['data'];
        $this->display('admin/coupon/ecstore_sendlog_item.html');
    }

}
