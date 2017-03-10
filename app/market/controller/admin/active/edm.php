<?php

class market_ctl_admin_active_edm extends market_ctl_admin_active_abstract{

    var $pagelimit = 10;
    var $is_debug = false;
//    var $is_debug = true;
    public static $middleware_connect = null;

    public function index()
    {
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach ((array)$shopList as $v){
            $shops[]=$v['shop_id'];
        }
        if ($_GET['view']){
            $view=($_GET['view']-1);
            $shop_id=$shops[$view];
        }
        $param=array(
            'title'=>'邮件营销',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => false,
            'orderBy' => "if(exec_time is null ,create_time ,exec_time) desc",
            'base_filter'=>array('is_active'=>array('sel_member','sel_template','wait_exec','finish'),'type|has'=>'edm'),
            'actions'=>array(
                array(
                    'label'=>'创建邮件营销',
                    'href'=>'index.php?app=market&ctl=admin_active_edm&act=create_active&send_method=edm&shop_id='.$shop_id,
                    'target'=>'dialog::{onClose:function(){window.location.reload();},width:650,height:355,title:\'创建活动\'}'
                ),
                array(
                    'label'=>'查看EDM制作规范',
                    'href'=>'index.php?app=market&ctl=admin_edm_doc&act=rule',
                    'target'=>'_blank'
                ),
            ),
        );
        $this->finder('market_mdl_active',$param);
    }

    function _views()
    {
        $memberObj = &app::get('market')->model('active');
        $base_filter=array('type|has'=>'edm','is_active|nohas'=>'dead');
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> $base_filter,
            'optional'=>false,
        );
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,	
            );
        }
        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!empty($v['filter'])){
                $v['filter'] = array_merge($v['filter'],$base_filter);
            }
            $count =$memberObj->count($v['filter']);
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $count;
            $sub_menu[$k]['href'] = 'index.php?app=market&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }

    //新增营销活动
    function toAdd_new()
    {
        $active_obj = &app::get('market')->model('active');
        if ( ! $_GET[p]['active_id']){
            $rs = $active_obj->dump(array('active_name'=>$_POST['active_name']));
            if($rs){
                $result = array(
                    'res'=>'fail',
                    'msg'=>'活动名称已经存在，请不要重复创建活动。'
                );
                echo(json_encode($result));
                die();
            }
        }    
    
        $filter_flag = intval($_POST['filter_sql']);
        if($filter_flag == 1){
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->fetch('filter_sql_'.$user_id,$_POST['filter_sql']);
        }elseif($filter_flag == 2){
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->fetch('filter_sql_market_'.$user_id,$_POST['filter_sql']);
        }else{
            unset($_POST['filter_sql']);
        }

        $_POST['start_time'] = strtotime($_POST['create_time']);
        $_POST['end_time'] = (!empty($_POST['end_time'])) ? strtotime($_POST['end_time']) : ($_POST['create_time'] + 1296000);
        $_POST['control_group'] = $_POST['control_group'][0];
        $_POST['create_time'] = time();

        if($_POST['report_filter']!=''){
            $report_filter = json_decode($_POST['report_filter'],1);
        }

        if($report_filter['relation']!='' || $report_filter['filter_type'] == 'goods'){
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->fetch('filter_member_'.$user_id,$_POST['userslist']);
        }
        
        //旺旺精灵下单客户
        if ($_POST['wangwang_model'] != '') {
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('taocrm')->fetch('wangwang_memberids_'.$user_id, $wangwangMemberList);
            $_POST['member_list']=serialize($wangwangMemberList);
        }

        if ($_POST['userslist'] != ''){
            $userslist=explode("," , $_POST['userslist']);
            $_POST['member_list']=serialize($userslist);
        }

        //跳转到选择模板步骤
        if(!empty($_GET[p]['member_list']) || $_POST['userslist']!='' || $_POST['report_filter']!=''){
            $_POST['is_active']='sel_template';
        }
        
        //客户营销模型
        if($_POST['sale_model_id']){
            $id_arr = explode('_',$_POST['sale_model_id']);
            $all_model = kernel::single('taocrm_ctl_admin_sale_model')->get_model();
            $model = $all_model[$id_arr[0]][$id_arr[1]];
            $_POST['filter_mem']['filter'] = $model['filter_mem'];
            $_POST['filter_mem']['shop_id'] = $_POST['shop_id'];
        }

        if(!empty($_GET[p]['active_id'])){
            $active_obj->update(array('active_id'=>$_GET[p]['active_id']),$_POST);
        }else{
            $systemType = kernel::single('taocrm_system')->getSystemType();
            if($filter_flag == 2){
                $_POST['pay_type'] = 'market';//营销超市
            }else{
                if ($systemType['system_type']==2){
                    $_POST['pay_type'] = 'pay';//按效果计费
                }
            }
            //新增时获取类型短信还是邮件的
            $_POST['type'] = serialize(array($_POST['send_method'])); 
            $rs=$active_obj->save($_POST);
        }
        $active_id=$_POST['active_id']?$_POST['active_id']:$_GET[p]['active_id'];
        $as_array=array('active_id'=>$active_id,'shop_id'=>$_POST['shop_id']);

        $result = array('res'=>'succ','data'=>$as_array);
        echo(json_encode($result));
    }

    //创建活动
    function create_active()
    {        
        //营销模型
        if(isset($_GET['model_id'])){
            $id_arr = explode('_',$_GET['model_id']);
            $taocrm_ctl_admin_sale_model = kernel::single('taocrm_ctl_admin_sale_model');
            $all_model = $taocrm_ctl_admin_sale_model->get_model();
            $model = $all_model[$id_arr[0]][$id_arr[1]];
            if(isset($model['filter_mem']['filter_func'])){
                $filter_func = $model['filter_mem']['filter_func'];
                $members = $taocrm_ctl_admin_sale_model->$filter_func('list', $_GET['shop_id']);
                $this->pagedata['userslist'] = implode(",", $members);
            }else{
                $this->pagedata['sale_model_id'] = $_GET['model_id'];
            }
            $this->pagedata['active'] = array('active_name'=>'[营销模型] '.$model['label'].'_'.date('Ymd'));
        }
        
        //旺旺精灵--客户
        if (isset($_GET['wangwang_model'])) {
            $wanwang_model = $_GET['wangwang_model'];
            $this->pagedata['wangwang_model'] = $wanwang_model;
            $this->pagedata['active'] = array('active_name'=>'[旺旺属性下单客户]_'.date('Ymd'));
        }
    
//        echo('<pre>');var_dump($_GET);die();
        if($_GET['filter_from']=='report'){
            $report_filter = json_encode($_GET);
            $this->pagedata['report_filter'] = $report_filter;//店铺信息
            $this->pagedata['filter_sql'] = 1;
        }

        if($_GET['filter_from']=='market'){
            $report_filter = json_encode($_GET);
            $this->pagedata['report_filter'] = $report_filter;//店铺信息
            $this->pagedata['filter_sql'] = 2;
            $sql = kernel::single('plugins_market')->getMarketSql($_GET['market_id'],trim($_GET['shop_id']));
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->store('filter_sql_market_'.$user_id,$sql);
        }

        $group_id = intval($_GET[p]['group_id']);
        $shopObj = &app::get('ecorder')->model('shop');

        //优惠券发送
        if($_GET['coupon_send']==1){
            $shop_id=$_GET['couponshop_id'];
            $shoplist=$shopObj->dump(array('shop_id'=>$shop_id));
            $this->pagedata['coushoplist']=$shoplist;
            $this->pagedata['coupons_tag']=true;
            $this->pagedata['coupons']=$_GET['cou_coupon_id'];
        }

        if($group_id > 0){//取出分组id为 所有客户
            $member_analysisObj = &app::get('taocrm')->model('member_group');
            $this->pagedata['userslist'] = 'group_id:'.$group_id;
        }

        //从客户列表中点过去
        if(!empty($_POST)){
            if ($_GET['memlist'] == 1) {
                $mem = $_POST['id'];
            }
            else {
                $memberobj = &app::get(taocrm)->model('member_analysis');
                $memberlist_id=array();
                foreach ($_POST as $k=>$v){
                    $memberlist_id=$v;
                }
                $fliter=array("id|in"=>$memberlist_id);
                $memberlist=$memberobj->getList("member_id",$fliter);
                $mem=array();
                foreach ($memberlist as $k=>$v){
                    $mem[]=$v['member_id'];
                }
            }
            $user_str = implode("," , $mem);
            $this->pagedata['userslist']=$user_str;
        }
        $mgroup_data = &app::get('taocrm')->model('member_group');

        $shop_id_data = array();
        if (isset($_GET['p'][0]) && $_GET['p'][0]) {
            $shop_id_data=$shopObj->dump(array('active_id'=>$_GET[p][0]));
        }
        $this->pagedata['data']=$shop_id_data;

        //EDM模板
        $edm_templates_obj = &app::get('market')->model('edm_templates');
        $edm_templates_data=$edm_templates_obj->getList("*");
        $this->pagedata['edm_templates_data'] = $edm_templates_data;

        //是否对照组
        $this->pagedata['open_compare'] = 'no';
        $shopList=$shopObj->getList("*");

        //营销超市
        if($_GET['filter_from']=='market'){
            $shopdata = array();
            foreach($shopList as $shop){
                if($shop['shop_id'] == trim($_GET['shop_id'])){
                    $shopdata[] = $shop;
                }
            }
            $rule = kernel::single('plugins_market')->getRule($_GET['market_id']);
            $this->pagedata['active'] = array('active_name'=>'[营销超市]'.$rule['title'].'-'.date('Ymd'));
        }else{
            $shopdata = $shopList;
        }

        $this->pagedata['shopList']=$shopdata;//店铺信息

        if(!empty($_GET[p][0])){
            $group_data=$this->member_group($_GET[p][0]);
            $this->pagedata['groupdata']=$group_data[0]['group_id'];//客户分组
        }

        //客户列表中的 shop_id
        $shop_id = trim($_GET['shop_id']);
        if($shop_id){
            $oneshop = $shopObj->dump(array('shop_id' => $shop_id));
//            if ($oneshop == '') {
//                $shopSql = "select shop_id, name from sdb_ecorder_shop where shop_id = '{$shop_id}'";
//                echo $shopSql;
//                $shopInfo = $shopObj->db->select($shopSql);
//                echo "<pre>";
//                print_r($shopInfo);
//            }
//            $this->pagedata['oneshop']=$oneshop['shop_id'];
            $this->pagedata['oneshop'] = $oneshop;
        }

        //客户列表中的 shop_id
        if(!empty($_GET[shop_id])&& $_GET['memlist']==1){
            $this->pagedata['member_list']='member_list';
        }
        //初始化表单项目
        $this->_init_config_arr();
        
        $this->pagedata['CacheId'] = trim($_GET['CacheId']);
        $this->pagedata['CacheIdCreateTime'] = trim($_GET['CacheIdCreateTime']);
        $send_method = $_GET['send_method']=='edm'? 'edm':'sms';
        $this->pagedata['send_method'] = $send_method;//定义发送类型 短信还是邮件
        $this->pagedata['beigin_time'] = date("Y-m-d",time());
        $this->pagedata['end_time'] = date('Y-m-d',strtotime('+15 days'));
        $this->pagedata['actity_type'] = json_encode(array());
        $this->display('admin/active/edm/create_active_new.html');
    }

    //编辑活动内容
    function editer_data()
    {
        $active_id=$_GET['p'][0];
        $active_obj = &app::get('market')->model('active');
        if (trim($_GET[p]['selectmember'])=='selecemember'){
            $active_obj->update(array('filter_mem'=>""),array('active_id'=>$_GET['p'][0]));
        }

        //清空数据库客户的条件
        $oneactive_data=$active_obj->dump(array('active_id'=>$active_id));

        //营销活动对应的客户数量
        $type_array = unserialize($oneactive_data['type']);
        if(in_array('edm',$type_array)) {
            //获取邮件的客户信息
            $send_method = 'edm';
//            $activityMemberNums = $this->getEdmActivityMemberNums($active_id);
            //$activityMemberNums = $this->getEdmActivityMemberNums($active_id);
            $activityMemberNums = $this->geteEditerMemberCount($active_id,$rmsg,'edit');
        }
        
        $this->pagedata['activityMemberNums'] = $activityMemberNums;

        if(!empty($oneactive_data['coupon_id'])){
            $this->pagedata['coupons_tag']=true;
            $this->pagedata['coupons']=$oneactive_data['coupon_id'];
            $couponsobj=&app::get('market')->model('coupons');//优惠券
            $couponslist=$couponsobj->dump(array('coupon_id'=>$oneactive_data['coupon_id']),"coupon_id,coupon_name");
            $this->pagedata['couponslist']=$couponslist;
        }
        $shopid=$oneactive_data['shop_id'];
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shop_id_data=$shopObj->dump(array('shop_id'=>$shopid));
        $oneactive_data['shop_name'] = $shop_id_data['name'];

        //转换过滤条件
        $filter_mem = unserialize($oneactive_data['filter_mem']);
        $filter_mem['filter']['goods_id'] = implode(',',$filter_mem['filter']['goods_id']);
        $filter_mem['filter']['regions_id'] = implode(',',$filter_mem['filter']['regions_id']);
        if($filter_mem['filter']['last_buy_time']['min_val'])
            $filter_mem['filter']['last_buy_time']['min_val'] = date('Y-m-d',$filter_mem['filter']['last_buy_time']['min_val']);
        if($filter_mem['filter']['last_buy_time']['max_val'])
            $filter_mem['filter']['last_buy_time']['max_val'] = date('Y-m-d',$filter_mem['filter']['last_buy_time']['max_val']);
        if($filter_mem['filter']['birthday']['min_val'])
            $filter_mem['filter']['birthday']['min_val'] = date('Y-m-d',$filter_mem['filter']['birthday']['min_val']);
        if($filter_mem['filter']['birthday']['max_val'])
            $filter_mem['filter']['birthday']['max_val'] = date('Y-m-d',$filter_mem['filter']['birthday']['max_val']);

        $this->pagedata['filter_mem']=$filter_mem;
        $this->pagedata['data']=$shop_id_data;
        $this->pagedata["active_id"]= $_GET[p][0];//活动id 包含客户的条件
        
        //全国地区列表
        $rs = app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('region_grade'=>1));
        if($rs){
            foreach($rs as $v){
                if(!$v['group_name']) $v['group_name'] = '其它';
                $regions[$v['group_name']][$v['region_id']] = $v['local_name'];
            }
        }
        $shopdata=$shopObj->getList("*");
        $this->pagedata['shopList']=$shopdata;
        $this->pagedata['regions'] = $regions;
        //短信模板
        $templates_obj = &app::get('market')->model('sms_templates');
        $this->pagedata['reg_data']=$reg_data;
        $templates_data=$templates_obj->getList("*");
        //edm模板
        $templates_obj = &app::get('market')->model('edm_templates');
        $edm_templates_data=$templates_obj->getList("*");

        $active_data=$active_obj->getList("*");

        if($templates_data){
            foreach($templates_data as $v) {
                if($v['template_id'] == $oneactive_data['template_id'])
                $oneactive_data['template_name'] = $v['title'];
            }
        }

        //短信定时发送
        if($oneactive_data['sent_time']){
            $oneactive_data['timing_hour'] = (date('H',$oneactive_data['sent_time']));
            $oneactive_data['timing_date'] = (date('Y-m-d',$oneactive_data['sent_time']));
        }
        $oneactive_data['type'] = unserialize($oneactive_data['type']);
        if($oneactive_data['type'] ){
            $actity_type = $oneactive_data['type'];
        }else{
            $actity_type = array();
        }
        $actity_type = json_encode($actity_type);
        
        //var_dump($actity_type);exit;
        $this->pagedata['send_method']=$send_method;
        $this->pagedata['actity_type'] = $actity_type;
        $this->pagedata['active']=$oneactive_data;
        $this->pagedata['active_data']=$active_data;
        $this->pagedata['templates_data']=$templates_data;
        $this->pagedata['edm_templates_data']=$edm_templates_data;
        $this->pagedata['open_compare'] = $oneactive_data['control_group'];//开启对照组
        $this->pagedata['unsubscribe'] = $oneactive_data['unsubscribe'];//开启退订        
        $this->pagedata["tag"]= $_GET['p'][1];

        $this->_init_config_arr();//初始化表单项目

        $this->display('admin/active/edm/create_active_new.html');
    }
    
    protected function geteEditerMemberCount($active_id)
    {
        return $this->getEdmTaskInfo($active_id,$msg,'edit');
    }
    
    /**
     * 邮件预估人数
     */
    public function assess()
    {
        $active_id = $_GET[p]['0'];
        $connect = $this->getConnect();
        if (isset($_GET['CacheId']) && $_GET['CacheId'] > 0) {
            $_POST['cacheId'] = $_GET['CacheId'];
            $num = $connect->SearchMemberAnalysisCountByCacheId($_POST);
            unset($_POST['cacheId']);
            $result['Count'] = $num;
        }
        else {
            $result = $connect->SearchMemberAnalysisList($_POST);
        }
        echo $result['Count'];
        exit;
    }
    
    /**
     * 获得链接
     * Enter description here ...
     */
    public function getConnect()
    {
        if (self::$middleware_connect == null) {
            self::$middleware_connect = new taocrm_middleware_connect;
        }
        return self::$middleware_connect;
    }

    //选择EDM模板
    function edm_select_template()
    {
        $template_id=$_GET[p][1];
        $active_obj = &app::get('market')->model('active');
        $templates_obj = &app::get('market')->model('edm_templates');
        $test = $active_obj->getList("*",array('template_id'=>$template_id),0,-1);
        $content_data=$templates_obj->dump(array('theme_id'=>$template_id),"theme_title,theme_content");
        echo Stripslashes($content_data['theme_content']).'|@|'.$content_data['theme_title'];
    }

    //编辑后保存模板
    function edm_edit_save()
    {
        $templates_obj = &app::get('market')->model('edm_templates');
        $templates_obj->update(array('theme_content'=> addslashes (urldecode($_POST[edm_message_text])),'theme_title'=>$_POST[edm_message_title]), array('theme_id'=>$_GET[p][1]));
        echo urldecode($_POST[edm_message_text]);
    }

    //活动待执行
    function active_ex()
    {
        $active_obj = &app::get('market')->model('active');
        $templates_obj = &app::get('market')->model('sms_templates');
        $filter=array('active_id'=>$_GET[p][0]);

        //上一步
        if ($_POST['tempup_tag']=='uptag') {
            $mem_filter=$active_obj->dump($filter,'filter_mem,member_list');
            $aa=unserialize($mem_filter['filter_mem']);
            //echo (json_encode($aa));
            $src=$active_obj->update(array('is_active'=>'sel_member'),array('active_id'=>$_GET[p][0]));
        //下一步
        }elseif($_POST['exec_tag']=='uptag'){
            $active_data=$active_obj->dump($filter,'template_id,templete_title');
            $src=$active_obj->update(array('is_active'=>'sel_template'),array('active_id'=>$_GET[p][0]));
        //保存活动信息
        }else{
            if (empty($_POST['timing_date'])){
                $time_str='0';
            }else {
                $time=$_POST['timing_date']." ".$_POST['timing_hour'].":00:00";
                $time_str=strtotime($time);
            }
            $sentType = explode(',', $_POST['send_type']);
            //标题
            $templete_title = urldecode($_POST['templete_title']);
            //内容
            $templete = urldecode($_POST['templete']);
            
            $open_compare = $_POST['open_compare'];
            $unsubscribe = intval($_POST['unsubscribe']);
            if($open_compare != 'yes') $open_compare = 'no';

            $src=$active_obj->update(
                array('template_id'=>$_GET[p][1],
                      'type'=>serialize($sentType),
                      'coupon_id' => $_POST['coupon_id'],
                      'is_active' => 'wait_exec',
                      'sent_time' => $time_str,
                      'control_group' => $open_compare,
                      'unsubscribe'=> $unsubscribe,
                      'templete_title' => $templete_title,
                      'templete' => $templete
                ),
                array('active_id'=>$_GET[p][0])
            );
        }
        
        //生成邮件队列
        $result = array('res'=>'succ');
        $send_type = trim($_POST['send_type']);
        if(stristr($send_type, 'edm') && $result['res'] == 'succ' ){
            $edmInfo = $this->getEdmTaskInfo($_GET['p'][0],$msg);
            if (count($edmInfo) == 0) {
                 $msg = $msg ? $msg : '生成队列失败';
                  $result = array('res'=>'fail','msg'=>$msg);
            }
//            if(!$this->edmProcessMember($_GET[p][0],$msg)){
//                $msg = $msg ? $msg : '生成邮件队列失败';
//                $result = array('res'=>'fail','msg'=>$msg);
//            }
        }

//        if($_POST['send_type']=='edm' && $result['res'] == 'succ' ){
//            $nums = $this->edmCheckSendStatus($_GET[p][0]);
//            if($nums > 0){
//                $result = array('res'=>'fail','msg'=>'本次营销活动存在之前未发送的客户,请稍后!如有问题,请联系我们的客服!');
//            }
//        }

        if(stristr($send_type, 'edm') && $result['res']=='succ'){
            $result['data'] = $edmInfo;
//            $edmActivityMemberQueue = $this->getEdmActivityMemberNums($_GET[p][0]);
//            $result['data'] = $edmActivityMemberQueue;
        }
        if ($_POST['exec_tag']=='uptag') {
            $result['templete_title'] = $active_data['templete_title'];
            $result['template_id'] = $active_data['template_id'];
        }
        
        echo json_encode($result);
        exit;
    }
    
    /**
     *  
     * 创建短信活动 人数预览
     */
    public function getEdmTaskInfo($active_id,&$msg, $resource = 'create')
    {
        $activeCount = array();
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_active where active_id = ' . $active_id);
        if(!$activity)return $activeCount;
        if($activity['is_active'] == 'dead'){
            $msg = '活动已作废!';
            return $activeCount;
        }
        //是否是活动人数对照组
        $personAB = $activity['control_group'] == 'no' ? 0 : 1;
        //是否开启短信对照组
        $messageAB = 0 >= $activity['template_id_b'] ? 0 : 1;
        $result = kernel::single('taocrm_middleware_activity')->GetMarketActivityInfo($active_id);
//        print_r($result);
//        exit;
        if ($result) {
//            print_r($result);
            //活动人数总数
            $count = $result['Count'];
            //有效客户数
            $send = $result['Send'];
            //无效客户数
            $unSend = $result['UnSend'];
            //重复发送客户数
            $reSend = $result['ReSend'];
            //有效客户数
//            $valiNum = $result['p1'];
            //开启活动对照数
            $activeContrast = $result['p2'];
            //A短信照
            $smsA = $messageAB == 0 ? $result['p1'] : $result['p3'];
            //B短信组
            $smsB = $result['p4'];
            //开启活动对照组及短信组
            if ($messageAB && $messageAB) {
                $smsA = $result['p3'];
                $smsB = $result['p4'];
                $valiNum = $send;
                $activeContrast = $result['p2'];
            }
            elseif ($personAB) {
                //只开启活动人数对照组
                $smsA = $result['p1'];
                $smsB = 0;
                $valiNum = $send;
                $activeContrast = $result['p2'];
            }
            elseif ($messageAB) {
                //只开启短信对照组
                $smsA = $result['p3'];
                $smsB = $result['p4'];
                $valiNum = $send;
                $activeContrast = 0;
            }
            else {
                //什么都没有开启
                $smsA = $result['p1'];
                $smsB = 0;
                $valiNum = $send;
                $activeContrast = 0;
            }
            //发送的客户数量
            $waitSendMember = $smsA + $smsB;
            //开启活动对照组及短信对照组
//            if ($messageAB && $messageAB) {
//                //有效客户数
//                $valiNum = $result['p3'] + $result['p4'];
//            }
//            elseif ($personAB) {
//                //活动人数对照组
//                $valiNum = $result['p3'];
//            }
//            else {
//                $valiNum = $result['p1'];
//            }
            //未营销客户数
            $unMarking = $valiNum - $reSend;
            
            $activeUpdateSql = "UPDATE `sdb_market_active`
                                SET `total_num` = {$count}, `valid_num` = {$valiNum} 
                                WHERE `active_id` = {$active_id}";
            $db->exec($activeUpdateSql);
            if ($resource == 'create') {
                $activeCount = array(
                    'total_member_count' => $count,
                    'valid_member_count' => $valiNum,
                    'unvalid_member_count' => $unSend,
                    'sent_member_count' => $reSend,
                    'controlGroupMembers' => $activeContrast,
                    'WaitSendMember' => $waitSendMember,
//                    'valid_member_count' => $valiNum,
//                    'controlGroupMembers' => $activeContrast,
//                    'validbMembers' => $smsB,
                    //'activeContrast' => $activeContrast,
                    //'smsA' => $smsA,
                    //'smsB' => $smsB
                );
            }
            elseif ($resource == 'edit') {
                //$result = array('unvalid_member_count'=>0,'valid_member_count'=>0,'total_member_count'=>0,'sent_member_count'=>0);
                $activeCount = array(
                    'unvalid_member_count' => $unSend,
                    'valid_member_count' => $valiNum,
                    'total_member_count' => $count,
                    'sent_member_count' => $reSend,
                    'controlGroupMembers' => $activeContrast,
                    'WaitSendMember' => $waitSendMember,
//                    'valid_member_count' => $valiNum,
//                    'controlGroupMembers' => $activeContrast,
//                    'validbMembers' => $smsB,
                    //'activeContrast' => $activeContrast,
                    //'smsA' => $smsA,
                    //'smsB' => $smsB
                );
            }
            elseif ($resource == 'send') {
                $activeCount = array(
                    'count' => $count,
                    'send' => $valiNum,
                    'unSend' => $unSend,
                    'reSend' => $reSend,
                    'valiNum' => $valiNum,
                    'activeContrast' => $activeContrast,
                    'smsA' => $smsA,
                    'smsB' => $smsB,
                    'unMarking' => $unMarking,
                    'controlGroupMembers' => $activeContrast,
                    'WaitSendMember' => $waitSendMember,
                );
            }
        }
        return $activeCount;
    }
    
    function sendActivityQueue($active_id,$type,$msgid)
    {
        $jobarray = array(
            'active_id'=>$active_id,
        );
        if(kernel::single('taocrm_service_queue')->addJob('market_backstage_activity@fetch',$jobarray)){
            $this->sendSmsQueueSucc($active_id);
            return true;
        }else{
            return false;
        }
    }

    function sendSmsQueueSucc($active_id){
        $active_obj = &app::get('market')->model('active');
        $active_obj->update(array('exec_time'=>time(),'is_active'=>'finish'),array('active_id'=>$active_id));
    }

    //获取营销活动的客户数
    function getActivityMemberNums($active_id){
        $db = kernel::database();
        $rows = $db->select('select count(*) as total,is_send from sdb_market_activity_m_queue where active_id='.$active_id.' group by is_send');
        $result = array('unvalid_member_count'=>0,'valid_member_count'=>0,'total_member_count'=>0,'sent_member_count'=>0);
        foreach($rows as $row){
            if($row['is_send'] == 0){
                $result['unvalid_member_count'] = $row['total'];
            }else if($row['is_send'] == 1){
                $result['valid_member_count'] = $row['total'];
            }
        }
        $result['total_member_count'] = $result['unvalid_member_count'] + $result['valid_member_count'];

        $e_time = time();
        $s_time = $e_time-86400;

        $row = $db->selectrow('select count(DISTINCT a.queue_id) as total
        from sdb_market_activity_m_queue as a 
        inner join sdb_market_activity_m_queue as b 
        on a.template_id=b.template_id 
        and a.member_id = b.member_id 
        where b.is_send_finish=1 
         and a.is_send = 1 
         and a.active_id='.$active_id.' 
         and b.active_id!='.$active_id .' 
         and b.sent_time>='.$s_time.' 
         and b.sent_time<='.$e_time
        );

        $result['sent_member_count'] = intval($row['total']);

        return $result;
    }

    function sms_send()
    {
        $active_obj = &app::get('market')->model('active');
        $active_id=$_GET[p][0];
        $active_data=$active_obj->dump(array('active_id'=>$active_id),"*");
        echo(unserialize($active_data['filter_mem']));
    }

    function edmProcessMember($active_id,&$msg){
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_active where active_id='.$active_id);
        if(!$activity)return false;
        if($activity['is_active'] == 'dead'){
            $msg = '活动已作废!';
            //exit('ssssss');
            return false;
        }

        //队列已存在就不重新创建了
        $row = $db->selectrow('select count(*) as total from sdb_market_activity_edm_queue where active_id='.$active_id);
        if($row['total'] > 0)return true;

        $shop = $db->selectrow('select name from sdb_ecorder_shop where shop_id="'.$activity['shop_id'].'"');
        $activity['shop_name'] = $shop['name'];
        if($activity){
            $objMemberGroup = &app::get('taocrm')->model('member_group');
            if($activity['member_list']!='') {
                $member_list = unserialize($activity['member_list']);
                if(strstr($member_list[0],'group_id')){
                    // 1.自定义分组
                    $group_id = str_replace('group_id:','',$member_list[0]);
                    $sql = "SELECT filter FROM sdb_taocrm_member_group WHERE group_id=$group_id";
                    $rs = $db->selectrow($sql);
                    if($rs){
                        $sql = $objMemberGroup->gmEdmBuildFilterSQL(unserialize($rs['filter']),$activity['shop_id'],$active_id);
                    }else{
                        //exit('ssssssm');
                        $sql = false;
                    }
                }else{
                    // 2.直接勾选客户
                    $sql = "SELECT $active_id as active_id,member_id,uname,name as truename,email FROM sdb_taocrm_members WHERE member_id in (".implode(',',$member_list).")";
                }
            }elseif($activity['filter_sql']!=''){
                // 4.报表sql语句
                $sql = $activity['filter_sql'];
                $sql = "SELECT $active_id as active_id,a.member_id,a.uname,a.name as truename,a.email
                FROM sdb_taocrm_members as a
                inner join ($sql) as b on a.member_id=b.member_id";
            }else{
                // 3.自定义筛选条件
                $filter_mem = unserialize($activity['filter_mem']);
                $sql = $objMemberGroup->gmEdmBuildFilterSQL($filter_mem['filter'],$activity['shop_id'],$active_id);
            }

            if($sql){
                //先清空活动之前的短信记录
                $db->exec('delete from sdb_market_activity_edm_queue where active_id='.$active_id);

                $insertSql = 'INSERT INTO sdb_market_activity_edm_queue(active_id,member_id,uname,truename,email) '.$sql;
                if(!$db->exec($insertSql)){
                    //exit($insertSql);
                    return false;
                }

                //更新模板id
                $db->exec('update sdb_market_activity_edm_queue set template_id='.$activity['template_id'].' where active_id='.$active_id);
            }
        }

        //删除空号码队列
        $db->exec('update sdb_market_activity_edm_queue set is_send=0 where active_id='.$active_id.' and  email =""');

        //过滤重复数据
        $mobile_re_rows = $db->select('select count(*) as total,email from sdb_market_activity_edm_queue where active_id = '.$active_id.' group by mobile having total>1');
        if($mobile_re_rows){
            $ids = array();
            foreach($email_re_rows as $row){
                $email_info_rows = $db->select('select queue_id from sdb_market_activity_edm_queue where active_id = '.$active_id .' and email="'.$row['email'].'"');
                foreach($email_info_rows as $k=>$email_row){
                    if($k == 0)continue;
                    $ids[] = $email_row['queue_id'];
                }
            }
            $db->exec('update sdb_market_activity_edm_queue set is_send = 0 where queue_id in('.implode(',', $ids).')');
        }

        return true;
    }

    // 创建队列判断当前活动队列和之前未发送队列有没有冲突（模板id,member_id）防止发送重复
    function checkSendStatus($active_id){
        $db = kernel::database();
        $row = $db->selectrow('select count(DISTINCT a.queue_id) as total
        from sdb_market_activity_m_queue as a 
        inner join sdb_market_activity_m_queue as b 
        on a.template_id=b.template_id and a.member_id = b.member_id
        where b.is_active="finish" 
        and b.is_send_finish=0 
        and a.is_send = 1 
        and a.active_id='.$active_id.' 
        and b.active_id!='.$active_id);
         
        return intval($row['total']);
    }

    // 创建队列判断当前活动队列和之前未发送队列有没有冲突（模板id,member_id）防止发送重复
    function edmCheckSendStatus($active_id){
        $db = kernel::database();
        $row = $db->selectrow('select count(DISTINCT a.queue_id) as total
        from sdb_market_activity_edm_queue as a 
        inner join sdb_market_activity_edm_queue as b 
        on a.template_id=b.template_id and a.member_id = b.member_id
        where b.is_active="finish" 
        and b.is_send_finish=0 
        and a.is_send = 1 
        and a.active_id='.$active_id.' 
        and b.active_id!='.$active_id);
         
        return intval($row['total']);
    }

    //发送短信前过滤掉24小时之内已营销的短信
    function filterSentMember($active_id){
        $e_time = time();
        $s_time = $e_time-86400;
        $page = 0;
        $page_size = 1000;
        $db = kernel::database();
        while(true){
            $rows = $db->select('select DISTINCT a.queue_id
        from sdb_market_activity_m_queue as a 
        inner join sdb_market_activity_m_queue as b 
        on a.template_id=b.template_id 
         and a.member_id = b.member_id 
        where b.is_send_finish=1 
         and a.is_send = 1 
         and a.active_id='.$active_id.' 
         and b.active_id!='.$active_id .' 
         and b.sent_time>='.$s_time.' 
         and b.sent_time<='.$e_time .' limit '.($page * $page_size).','.$page_size
            );
            if(!$rows){
                break;
            }

            $ids = array();
            foreach($rows as $row){
                $ids[] = $row['queue_id'];
            }
            $db->exec('update sdb_market_activity_m_queue set is_send=0 where queue_id in('.implode(',', $ids).')');
            $page++;
        }
    }

    //edm 操作
    public function edm_exec(){
        set_time_limit(360);
        $active_obj = &app::get('market')->model('active');
        $edm_obj = kernel::single('market_service_edm');
        $active_id = $_GET[p][0];
        $filter=array('active_id'=>$active_id);
        $tag=$active_obj->dump($filter);
        $result = array('res'=>'succ');
        $msgid=time().'_'.rand(100,999);

        //过滤24小时发送过的客户
//        if($_POST['is_send_salemember'] == 0){
//            $this->filterSentMember($active_id);
//        }

//        if($result['res'] == 'succ'){
//            $nums = $this->checkSendStatus($active_id);
//            if($nums > 0){
//                $result = array('res'=>'fail','msg'=>'本次营销活动存在之前未发送的客户('.$nums.'),请稍后!如有问题,请联系我们的客服!');
//            }
//        }
       
        $tag["type"] = unserialize($tag["type"]);

        //设置店铺的最后营销时间
        $shop_obj = &app::get('ecorder')->model('shop');
        $shop_obj->set_last_market_time($tag['shop_id']);
        
        if($tag["type"]){
            //检查发送短信
            if($result['res'] == 'succ'){
                if (in_array('edm', $tag["type"])){
                    $result = $this->checkEdm($active_id,$msgid);
                    if ($result['res'] != 'succ') {
                        echo json_encode($result);
                        exit;
                    }
                    else {
                        $result = array('res'=>'succ');
                    }
//                   print_r($result);
//                   exit();
                    
                }
            }

            //发送队列
            if($result['res'] == 'succ'){
                $res = kernel::single('taocrm_middleware_activity')->ExecMarketActivity($active_id);
//                if(!$this->sendEdmActivityQueue($active_id,$tag['type'],$msgid)){
//                    $result = array('res'=>'fail','msg'=>'发送队列失败');
//                }
            }
            //print_r($result);
            //exit();
        }else{
            $result = array('res'=>'fail','msg'=>'请选择发送短信或者优惠券');
        }
         
        echo json_encode($result);
    }

    public function getEdmCount(){
        $active_id=$_GET[p][0];
        $active_obj = &app::get('market')->model('active');
        $send=kernel::single('market_service_edminterface');
        $memsms=kernel::single('market_service_edm');
        $send_info=$send->useredm_info();//get_usersms_info
        if ($send_info['res']=='succ'){
            $month_residual=$send_info['info']['month_residual']; //短信总条数 all_residual
        }else {
            $month_residual=-1; //当前可用的短信数
        }
        //测试信息
        if($this->is_debug == true) {
            $month_residual = 10000*100;
            $blocknums = 100;
            $infoarray = array(
                'entId' => '3434343',
                'entPwd' => 'werwrwrw2342342',
                'license' => 'werwerwerwrwerw',
                'edmcount' => $month_residual
            );
        }
        else {
            $accountInfo = $this->getEdmAccount();
            $infoarray = array(
                'account' => array(
                    'entId' => $send_info['info']['account_info']['entid'],
                    'biz_user_id' => $send_info['info']['account_info']['biz_user_id'],
                    'mobile' => $send_info['info']['account_info']['mobile'],
                    'edm_email' => $send_info['info']['account_info']['edm_email'],
                    'contact' => $send_info['info']['account_info']['contact'],
                    'active' => $send_info['info']['account_info']['active']
                ),
                'entId' => $accountInfo['entId'],
                'entPwd' => $accountInfo['entPwd'],
                'license' => $accountInfo['license'],
                'edmcount' => $month_residual,
            );
        }
        return $infoarray;
    }
    
    
    protected function getEdmAccount()
    {
        base_kvstore::instance('market')->fetch('account', $arr);
        if( ! $arr){
            $edmAccount = market_edm_utils::update_edm_kv();
        }else{
            $edmAccount = unserialize($arr);
        }
        //对密码进行解密
       $market_edm_des = kernel::single('market_edm_des');
        if(strlen($edmAccount['password']) > 64){
            $edmAccount['password'] = $market_edm_des->decrypt($edmAccount['password']);
        }else{//兼容旧的原始密码
            $edmAccount['password'] = md5($edmAccount['password'].'ShopEXUser');
        }
//        print_r($edmAccount);
//        exit;
        $accountInfo = array(
            'entPwd' => $edmAccount['password'],
            'entId' => $edmAccount['entid'],
            'license' => base_certificate::get('certificate_id') ? base_certificate::get('certificate_id') : 1
        );
        return $accountInfo;
    }

    function checkEdm($active_id,$msgid){
        //return  array('res'=>'succ');
        $smsInfo=$this->getEdmCount();
//        print_r($smsInfo);
//        exit;
        if($smsInfo['edmcount'] == -1){
            return array('res'=>'fail','msg'=>'您的短信账号出现异常，请检查配置信息');
        }
        
        //检查客户发数量
        $activityMemberNums = $this->getEdmTaskInfo($active_id,$msg,'send');
        $is_send_salemember = $_POST['is_send_salemember'];
        if ($is_send_salemember == 1) {
            $memberNums = $activityMemberNums['valiNum'];
        }
        else {
            $memberNums = $activityMemberNums['unMarking'];
        }
        
        $active_obj = &app::get('market')->model('active');
        $activeInfo = $active_obj->dump(array('active_id'=>$active_id));
        $shopId = $activeInfo['shop_id'];
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach ((array)$shopList as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
//        print_r($smsInfo);
//        exit;
        //保存短信帐号信息    
        $active_remark = array(
            'shopName'=>$shops[$shopId],
            'entId'=>$smsInfo['entId'],
            'entPwd'=>$smsInfo['entPwd'],
            'license'=>$smsInfo['license'],
            'taskId'=>'activity'.$active_id,
            'is_send_salemember' => $is_send_salemember,
            'account' => $smsInfo['account'],
        );
        $active_obj->update(array('active_remark'=>json_encode($active_remark)),array('active_id'=>$active_id));
//        $activityMemberQueue = $this->getEdmActivityMemberNums($active_id);
//        $memberNums = $activityMemberQueue['valid_member_count'];
//        
//        if($memberNums <= 0){
//            return array('res'=>'fail','msg'=>'没有要发送的客户');
//        }
        $result = array('res'=>'succ');
        $systemType = kernel::single('taocrm_system')->getSystemType();
        $filter=array('active_id'=>$active_id);
        $tag=$active_obj->dump($filter);
        //营销超市
        if ($tag['pay_type'] == 'market'){
            //检查是否开启营销评估
            if ($tag['control_group']=='yes'){
                $overcount=$smsInfo['smscount']-(ceil($memberNums/2)*$systemType['market_freeze_rule'])-$smsInfo['blocknum'];
            }else {
                $overcount=$smsInfo['smscount']-($memberNums*$systemType['market_freeze_rule'])-$smsInfo['blocknum'];
            }

            if ($overcount >= $memberNums){
                $blockNum = $smsInfo['selemem']*$systemType['market_freeze_rule'];
                if($this->freezSms($blockNum,$msgid)){
                    //$this->sendSmsQueue($tag['active_id'],$sms_config,$msgid);
                    //$this->sendSmsQueueSucc($tag['active_id']);
                }else{
                    $result = array('res'=>'fail','msg'=>'冻结短信超时');
                }

            }else{
                $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            }

        }else if ($tag['pay_type'] == 'pay'){//按效果付费用户
            //检查是否开启营销评估
            if ($tag['control_group']=='yes'){
                $overcount=$smsInfo['smscount']-(ceil($memberNums/2)*$systemType['freeze_rule'])-$smsInfo['blocknum'];
            }else {
                $overcount=$smsInfo['smscount']-($memberNums*$systemType['freeze_rule'])-$smsInfo['blocknum'];
            }

            if ($overcount >= $memberNums){
                $blockNum = $smsInfo['selemem']*$systemType['freeze_rule'];
                if($this->freezSms($blockNum,$msgid)){
                    //$this->sendSmsQueue($tag['active_id'],$sms_config,$msgid);
                    //$this->sendSmsQueueSucc($tag['active_id']);
                }else{
                    $result = array('res'=>'fail','msg'=>'冻结短信超时');
                }
            }else{
                $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            }

        }else{
            //测试参数(可以发送邮件的记录数量)
            //$smsInfo['edmcount'] = 10000000000;
            if($smsInfo['edmcount'] >= $memberNums && $memberNums > 0){
                //$this->sendSmsQueue($tag['active_id'],$sms_config,$msgid);
                //$this->sendSmsQueueSucc($tag['active_id']);
            }
            elseif (empty($memberNums)) {
                $result = array('res'=>'fail','msg'=> '您发送的人数少于0人');
            }
            else{
                $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            }
        }
        return $result;
    }

    //获取edm营销活动的客户数
    function getEdmActivityMemberNums($active_id)
    {
        $db = kernel::database();
        $rows = $db->select('select count(*) as total,is_send from sdb_market_activity_edm_queue where active_id='.$active_id.' group by is_send');
        $result = array('unvalid_member_count'=>0,'valid_member_count'=>0,'total_member_count'=>0,'sent_member_count'=>0);
        foreach($rows as $row){
            if($row['is_send'] == 0){
                $result['unvalid_member_count'] = $row['total'];
            }else if($row['is_send'] == 1){
                $result['valid_member_count'] = $row['total'];
            }
        }
        $result['total_member_count'] = $result['unvalid_member_count'] + $result['valid_member_count'];

        $e_time = time();
        $s_time = $e_time-86400;

        $row = $db->selectrow('select count(DISTINCT a.queue_id) as total
        from sdb_market_activity_edm_queue as a 
        inner join sdb_market_activity_edm_queue as b 
        on a.template_id=b.template_id 
        and a.member_id = b.member_id 
        where b.is_send_finish=1 
         and a.is_send = 1 
         and a.active_id='.$active_id.' 
         and b.active_id!='.$active_id .' 
         and b.sent_time>='.$s_time.' 
         and b.sent_time<='.$e_time
        );

        $result['sent_member_count'] = intval($row['total']);

        return $result;
    }

    function sendEdmActivityQueue($active_id,$type,$msgid)
    {
        $jobarray = array(
            'active_id'=>$active_id,
        );
        if(kernel::single('taocrm_service_queue')->addJob('market_backstage_edmactivity@fetch',$jobarray)){
            $this->sendSmsQueueSucc($active_id);
            return true;
        }else{
            return false;
        }
    }

}