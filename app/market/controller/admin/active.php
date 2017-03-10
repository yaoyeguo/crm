<?php

class market_ctl_admin_active extends desktop_controller{

    var $pagelimit = 10;
    var $is_debug = false;

    public function index()
    {
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach ((array)$shopList as $v){
            $shops[]=$v['shop_id'];
        }
        if ($_GET['view']){
            $view=($_GET['view']-1);
            $shop_id=$shops[$view];
        }
        $param=array(
            'title'=>'短信营销',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => false,
        //'orderBy' => "field( is_active, 'sel_member', 'sel_template', 'wait_exec', 'finish', 'dead' )",
            'orderBy' => "if(exec_time is null ,create_time ,exec_time) desc",
            'base_filter'=>array('is_active'=>array('sel_member','sel_template','wait_exec','finish'),'type|nohas'=>'edm'),
            'actions'=>array(
        array(
                'label'=>'创建短信营销',
                'href'=>'index.php?app=market&ctl=admin_active&act=create_active&send_method=sms&shop_id='.$shop_id,
                'target'=>'dialog::{onClose:function(){window.location.reload();},width:650,height:355,title:\'创建活动\'}'
                ),
                ),
                );
                $this->finder('market_mdl_active',$param);
    }

    function _views(){
        $memberObj = app::get('market')->model('active');
        $base_filter=array('type|nohas'=>'edm','is_active|nohas'=>'dead');
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> $base_filter,
            'optional'=>false,
        );
        $shopObj = app::get(ORDER_APP)->model('shop');
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
    function toAdd_new() {
        $filter_flag = intval($_POST['filter_sql']);
        if($filter_flag == 1){
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->fetch('filter_sql_'.$user_id,$_POST['filter_sql']);
            //var_dump($_POST['filter_sql']);
        }else if($filter_flag == 2){
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->fetch('filter_sql_market_'.$user_id,$_POST['filter_sql']);
            //var_dump($_POST['filter_sql']);
        }else{
            unset($_POST['filter_sql']);
        }

        $active_obj = app::get('market')->model('active');
        $_POST['create_time']=strtotime($_POST['create_time']);
        $_POST['end_time']= (!empty($_POST['end_time'])) ? strtotime($_POST['end_time']) : ($_POST['create_time'] + 1296000);
        $_POST['control_group']=$_POST['control_group'][0];


        if($_POST['report_filter']!=''){
            $report_filter = json_decode($_POST['report_filter'],1);
        }

        if($report_filter['relation']!='' || $report_filter['filter_type'] == 'goods'){
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->fetch('filter_member_'.$user_id,$_POST['userslist']);
        }

        if ($_POST['userslist'] != ''){
            $userslist=explode("," , $_POST['userslist']);
            $_POST['member_list']=serialize($userslist);
        }

        //跳转到选择模板步骤
        if (!empty($_GET[p]['member_list']) || $_POST['userslist']!='' || $_POST['report_filter']!=''){
            $_POST['is_active']='sel_template';
        }

        if (!empty($_GET[p]['active_id'])){
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
            $_POST['type'] = serialize(array($_POST['send_method'])); //新增时获取类型短信还是邮件的
            $rs=$active_obj->save($_POST);
        }
        $active_id=$_POST['active_id']?$_POST['active_id']:$_GET[p]['active_id'];
        $as_array=array('active_id'=>$active_id,'shop_id'=>$_POST['shop_id']);

        $rs = app::get('ecorder')->model('shop_lv')->getList('lv_id,name',array('shop_id'=>$_POST['shop_id']));
        if($rs) {
            foreach($rs as $v){
                $levels[$v['lv_id']] = $v['name'];
            }
        }
        $as_array['levels'] = $levels;
        $result = array('res'=>'succ','data'=>$as_array);

        echo(json_encode($result));
    }

    //优惠券
    function coupon_send(){
        $active_id=$_GET[p][0];
        $active_obj = app::get('market')->model('coupons');
        $active_data=$active_obj->dump(array('active_id'=>$active_id),'shop_id,coupon_name,coupon_count,used_num');
        echo(json_encode($active_data));
    }

    //客户分组
    function getmember_group(){
        $active_obj = app::get('market')->model('active');
        $membergroup_obj = app::get('taocrm')->model('member_group');
        $active_id=$_GET['p'][0];
        $shop_id=$active_obj->dump(array('active_id'=>$active_id),'shop_id,filter_mem');
        $filter_array=unserialize($shop_id['filter_mem']);
        $group_data=$membergroup_obj->getList("group_id,group_name",array('shop_id'=>$shop_id['shop_id']));
        foreach ($group_data as $k=>$v){
            $group_data[$k]['group_selected']=!empty($filter_array['group_id'])?intval($filter_array['group_id']) : 0;
        }
        echo(json_encode($group_data));
    }

    //创建活动
    public function create_active()
    {
        //贵宾组客户营销
        if($_GET['resource'] == 'vip'){
            $memberObj = app::get('taocrm')->model('member_analysis');
            $members = $memberObj->getList('id',array('shop_id'=>trim($_GET['shop_id']),'is_vip'=>'true'));
            $ids = array();
            foreach($members as $v){
                $id[] = $v['id'];
            }
            $_POST['id'] = $id;
        }

        if($_GET['filter_from']=='report'){
            $report_filter = json_encode($_GET);
            $this->pagedata['report_filter'] = $report_filter;//店铺信息
            $this->pagedata['filter_sql'] = 1;
            //$this->pagedata['member_count'] = $_GET['total'];
        }

        if($_GET['filter_from']=='market'){
            $report_filter = json_encode($_GET);
            $this->pagedata['report_filter'] = $report_filter;//店铺信息
            $this->pagedata['filter_sql'] = 2;
            $sql = kernel::single('plugins_market')->getMarketSql($_GET['market_id'],$_GET['shop_id']);
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->store('filter_sql_market_'.$user_id,$sql);
            //$this->pagedata['member_count'] = $_GET['total'];
        }

        $group_id = intval($_GET[p]['group_id']);
        $shopObj = app::get('ecorder')->model('shop');

        //优惠券发送
        if ($_GET['coupon_send']==1){
            $shop_id=$_GET['couponshop_id'];
            $shoplist=$shopObj->dump(array('shop_id'=>$shop_id));
            $this->pagedata['coushoplist']=$shoplist;
            $this->pagedata['coupons_tag']=true;
            $this->pagedata['coupons']=$_GET['cou_coupon_id'];
        }

        if ($group_id > 0){//取出分组id为 所有客户
            $member_analysisObj = app::get('taocrm')->model('member_group');
            $member_group=$member_analysisObj->dump($group_id);
            //$memberlist=$member_analysisObj->getMemberList($_GET['p']['group_id']);
            //$user_str=implode("," , $memberlist);
            $this->pagedata['userslist'] = 'group_id:'.$group_id;
            $this->pagedata['active'] = array('active_name'=>$member_group['group_name'].'_'.date('Ymd'));
        }

        //从客户列表中点过去
        if (!empty($_POST)){
            $memberobj = app::get(taocrm)->model('member_analysis');
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
            $user_str=implode("," , $mem);
            $this->pagedata['userslist']=$user_str;
        }
        $mgroup_data = app::get('taocrm')->model('member_group');

        $shop_id_data=$shopObj->dump(array('active_id'=>$_GET[p][0]));
        $this->pagedata['data']=$shop_id_data;
        $templates_obj = app::get('market')->model('sms_templates');
        $templates_data=$templates_obj->getList("*",array('status'=>1));
        $this->pagedata['templates_data']=$templates_data;//短信模板
        $edm_templates_obj = app::get('market')->model('edm_templates');
        $edm_templates_data=$edm_templates_obj->getList("*",array('status'=>1));

        $this->pagedata['edm_templates_data'] = $edm_templates_data;//EDM模板

        $this->pagedata['open_compare'] = 'no';//开启对照组
        $shopList=$shopObj->getList("*");
        //营销超市
        if ($_GET['filter_from']=='market'){
            $shopdata = array();
            foreach($shopList as $shop){
                if($shop['shop_id'] == $_GET['shop_id']){
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

        $shop_id = trim($_GET['shop_id']);
        if($shop_id){//客户列表中的 shop_id
            $oneshop=$shopObj->dump(array('shop_id'=>$shop_id),'shop_id,name');
            $this->pagedata['oneshop']=$oneshop;
        }
        if(!empty($_GET[shop_id])&& $_GET['memlist']==1){//客户列表中的 shop_id
            $this->pagedata['member_list']='member_list';
        }

        $this->_init_config_arr();//初始化表单项目

        $send_method = $_GET['send_method']=='edm'? 'edm':'sms';

        $this->pagedata['send_method']   = $send_method; //定义发送类型 短信还是邮件


        if(isset( $this->pagedata['active'])){
            $this->pagedata['active']['type'] = array($send_method);
        }else{
            $this->pagedata['active'] = array('type'=>array($send_method));
        }

        $this->pagedata['beigin_time'] = date("Y-m-d",time());
        $this->pagedata['end_time'] = date('Y-m-d',strtotime('+15 days'));
        $this->pagedata['actity_type'] = json_encode(array());
        $this->display('admin/active/create_active_new.html');
    }

    //编辑活动内容
    function editer_data() {
        set_time_limit(360);
        $active_id=$_GET['p'][0];
        $active_obj = app::get('market')->model('active');
        if (trim($_GET[p]['selectmember'])=='selecemember'){
            $active_obj->update(array('filter_mem'=>""),array('active_id'=>$_GET['p'][0]));
        }

        //清空数据库客户的条件
        $oneactive_data=$active_obj->dump(array('active_id'=>$active_id));

        //print_r($oneactive_data);

        //营销活动对应的客户数量
        $type_array = unserialize($oneactive_data['type']);
        if(in_array('edm',$type_array)) {
            //获取邮件的客户信息
            $send_method = 'edm';
            $activityMemberNums = $this->getEdmActivityMemberNums($active_id);
        }else{
            //获取短信的客户信息
            $send_method = 'sms';
            $activityMemberNums = $this->getActivityMemberNums($active_id);
        }

        $this->pagedata['activityMemberNums'] = $activityMemberNums;
        /*if($oneactive_data['control_group'] == 'yes'){
         $this->pagedata['member_count'] = ceil($member_count/2).'；对照客户数：'.($member_count-ceil($member_count/2));
         }else{
         $this->pagedata['member_count'] = $member_count;
         }*/

        //是否绑定优惠价
        if (!empty($oneactive_data['coupon_id'])){
            $this->pagedata['coupons_tag']=true;
            $this->pagedata['coupons']=$oneactive_data['coupon_id'];
            $couponsobj=app::get('market')->model('coupons');//优惠券
            $couponslist=$couponsobj->dump(array('coupon_id'=>$oneactive_data['coupon_id']),"coupon_id,coupon_name");
            $this->pagedata['couponslist']=$couponslist;
        }
        $shopid=$oneactive_data['shop_id'];
        $rs = app::get('ecorder')->model('shop_lv')->getList('lv_id,name',array('shop_id'=>$shopid));
        if($rs) {
            foreach($rs as $v){
                $levels[$v['lv_id']] = $v['name'];
            }
        }
        $this->pagedata['levels'] = $levels;
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shop_id_data=$shopObj->dump(array('shop_id'=>$shopid));
        $oneactive_data['shop_name'] = $shop_id_data['name'];

        //转换过滤条件
        $filter_mem = unserialize($oneactive_data['filter_mem']);
        $filter_mem['filter']['goods_id'] = implode(',',$filter_mem['filter']['goods_id']);
        $filter_mem['filter']['regions_id'] = implode(',',$filter_mem['filter']['regions_id']);
        if($filter_mem['filter']['last_buy_time']['min_val']) $filter_mem['filter']['last_buy_time']['min_val'] = date('Y-m-d',$filter_mem['filter']['last_buy_time']['min_val']);
        if($filter_mem['filter']['last_buy_time']['max_val']) $filter_mem['filter']['last_buy_time']['max_val'] = date('Y-m-d',$filter_mem['filter']['last_buy_time']['max_val']);
        if($filter_mem['filter']['birthday']['min_val']) $filter_mem['filter']['birthday']['min_val'] = date('Y-m-d',$filter_mem['filter']['birthday']['min_val']);
        if($filter_mem['filter']['birthday']['max_val']) $filter_mem['filter']['birthday']['max_val'] = date('Y-m-d',$filter_mem['filter']['birthday']['max_val']);

        $this->pagedata['filter_mem']=$filter_mem;
        $this->pagedata['data']=$shop_id_data;

        $this->pagedata["active_id"]= $_GET[p][0];//活动id 包含客户的条件
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

        $active_data=$active_obj->getList("*");

        if($send_method == 'sms'){
            //短信模板
            $templates_obj = app::get('market')->model('sms_templates');
            $this->pagedata['reg_data']=$reg_data;
            $templates_data=$templates_obj->getList("*",array('status'=>1));
            if($templates_data){
                foreach($templates_data as $v) {
                    if($v['template_id'] == $oneactive_data['template_id'])
                    $oneactive_data['template_name'] = $v['title'];
                }
            }
        }elseif($send_method == 'edm'){
            //edm模板
            $templates_obj = app::get('market')->model('edm_templates');
            $edm_templates_data=$templates_obj->getList("*",array('status'=>1));
            if($edm_templates_data){
                foreach($edm_templates_data as $v) {
                    if($v['theme_id'] == $oneactive_data['template_id']){
                        $oneactive_data['edm_template_name'] = $v['theme_title'];
                    }
                }
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

        //短信模版B信息
        $this->templateTplB($oneactive_data);
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
        $this->display('admin/active/create_active_new.html');
    }

    private function templateTplB($data)
    {
        $this->pagedata['template_id_b'] = $data['template_id_b'];
        $this->pagedata['data']['content_b'] = $data['templete_b'];
    }

    private function _init_config_arr()
    {
        //地区列表
        $rs = app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('region_grade'=>1));
        if($rs){
            foreach($rs as $v){
                if(!$v['group_name']) $v['group_name'] = '其它';
                $regions[$v['group_name']][$v['region_id']] = $v['local_name'];
            }
        }
        $this->pagedata['regions'] = $regions;

        //客户等级
        /*
        $rs = app::get('ecorder')->model('shop_lv')->getList('lv_id,name');
        if($rs) {
        foreach($rs as $v){
        $levels[$v['lv_id']] = $v['name'];
        }
        }
        $this->pagedata['levels'] = $levels;
        */
        $taobaolv = array(
            'c'=>'普通客户',
            'asso_vip'=>'荣誉客户',
            'vip1'=>'vip1',
            'vip2'=>'vip2',
            'vip3'=>'vip3',
            'vip4'=>'vip4',
            'vip5'=>'vip5',
            'vip6'=>'vip6'
        );
        $select_sign = array(
            'nequal' => '等于',
            'sthan' => '小于等于',
            'bthan' => '大于等于',
            'between' => '介于'
        );
        $select_sign_time = array(
            'than' => '晚于',
            'lthan' => '早于',
            'nequal' => '等于',
            'between' => '介于'
        );
        $select_date = array(
            '7' => '最近一周',
            '30' => '最近一个月',
            '60' => '最近二个月',
            '90' => '最近三个月',
            '180' => '最近半年',
            '360' => '最近一年',
        );

        for($i=0;$i<24;$i++){
            $select_hour[$i] = $i.':00';
        }

        $this->pagedata['taobaolv'] = $taobaolv;
        $this->pagedata['select_sign'] = $select_sign;
        $this->pagedata['select_sign_time'] = $select_sign_time;
        $this->pagedata['select_date'] = $select_date;
        $this->pagedata['select_hour'] = $select_hour;
    }

    //客户的函数
    function select_member_data(){

        $memberanaly_obj = app::get('taocrm')->model('member_analysis_day');
        $couponsobj=app::get('market')->model('coupons');//优惠券
        $active_obj = app::get('market')->model('active');
        $members_group_obj = app::get('taocrm')->model('member_group');
        $members_obj = app::get('taocrm')->model('members');
        $filter=array('active_id'=>$_GET[p][0]);

        if ($_POST['uptag']=='uptag') {
            $filter=array('active_id'=>$_GET[p][0]);
            $dump_data=$active_obj->dump($filter);
            $dump_data['create_time']=date('Y-m-d',$dump_data['create_time']);
            $dump_data['end_time']=date('Y-m-d',$dump_data['end_time']);
            $filter_mem = unserialize($dump_data['filter_mem']);
            $dump_data['filter_mem']['tags']=$filter_mem['tags'];

            echo(json_encode($dump_data));

        }else{
            $active_id=$_GET[p][0];
            $data=$active_obj->dump(array('active_id'=>$active_id));
            $shop_id=trim($data['shop_id']);
            $filter=array('shop_id'=>$shop_id);
            $str_post=serialize($_POST);//保存活动对应的筛选条件
            $rs=$active_obj->update(
            array('filter_mem'=>$str_post,'is_active'=>'sel_template'),
            array('active_id'=>$_GET[p][0])
            );
            if (!empty($data['coupon_id'])){
                $coupononedata=$couponsobj->dump(array('coupon_id'=>$data['coupon_id']),'coupon_name,coupon_id');
            }else{
                $coupononedata = array('coupon_name'=>'','coupon_id'=>0);
            }
            echo json_encode($coupononedata);
        }

    }

    //创建活动step3 ajax获取优惠券列表
    public function coupons_selected()
    {
        $couponsobj=app::get('market')->model('coupons');//优惠券
        $active_obj = app::get('market')->model('active');
        $active_id=$_GET[p][0];
        $shop_id=$active_obj->dump(array('active_id'=>$active_id),'shop_id');
        $shop_id=trim($shop_id['shop_id']);
        $filter = array();
        $filter['shop_id'] = $shop_id;
        $filter['end_time|than'] = time();
        $filter['source'] = 'local';
        $couponslist=$couponsobj->getList("coupon_id,coupon_name",$filter);
        if (empty($couponslist)){
            $tsts=array();
            $tsts[0]['active_id']=$_GET[p][0];
            $tsts[0]['cou_tag']=true;
            echo json_encode($tsts);
        }else{
            foreach ($couponslist as $k=>$v){
                $couponslist[$k]['active_id']=$_GET[p][0];
                $couponslist[$k]['cou_tag']=false;
            }
            echo json_encode($couponslist);
        }
    }

    //评估客户数量
    function assess() {

        $oMemberGroup = app::get('taocrm')->model('member_group');
        $oMemberAnalysis = app::get('taocrm')->model('member_analysis');
        //转换过滤条件
        $filter = $oMemberGroup->buildFilter($_POST['filter'],$_POST['shop_id']);
        if(strstr($filter,'select')){
            $db = kernel::database();
            $count = $db->selectRow($filter);
            echo $count['_count'];
        }else{
            $count = $oMemberAnalysis->count($filter);
            echo $count;
        }

        //echo('<pre>');var_dump($filter);
        die();
    }

    //选择短信模板
    function select_template(){
        $template_id=$_GET[p][1];
        $active_obj = app::get('market')->model('active');
        $templates_obj = app::get('market')->model('sms_templates');
        $test = $active_obj->getList("*",array('template_id'=>$template_id),0,-1);
        $content_data=$templates_obj->dump(array('template_id'=>$template_id),"content");
        echo $content_data[content];
    }

    //选择短信模板B
    function select_template_b(){
        $template_id=$_GET[p][1];
        $active_obj = app::get('market')->model('active');
        $templates_obj = app::get('market')->model('sms_templates');
        $test = $active_obj->getList("*",array('template_id'=>$template_id),0,-1);
        $content_data=$templates_obj->dump(array('template_id'=>$template_id),"content");
        echo $content_data['content'];
    }

    //选择EDM模板
    function edm_select_template(){
        $template_id=$_GET[p][1];
        $active_obj = app::get('market')->model('active');
        $templates_obj = app::get('market')->model('edm_templates');
        $test = $active_obj->getList("*",array('template_id'=>$template_id),0,-1);
        $active_obj = app::get('market')->model('active');
        $data=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'templete_title');
        $content_data=$templates_obj->dump(array('theme_id'=>$template_id),"theme_content,theme_title");
        if($data['templete_title']){
            $content_data['theme_title'] = $data['templete_title'];
        }
        $content_data['theme_content']=str_replace(array("\r\n", "\r", "\n"),"",$content_data['theme_content']);

        echo addslashes($content_data['theme_content']).'|@|'.$content_data['theme_title'];
    }

    //编辑后保存save_template模板
    function edm_edit_save(){
        $templates_obj = app::get('market')->model('edm_templates');
        $templates_obj->update(array('theme_content'=> addslashes (urldecode($_POST[edm_message_text])),'theme_title'=>$_POST[edm_message_title]), array('theme_id'=>$_GET[p][1]));
        echo urldecode($_POST[edm_message_text]);
    }

    //编辑后保存模板
    function edit_save(){
        $templates_obj = app::get('market')->model('sms_templates');
        $templates_obj->update(array('content'=>$_POST[message_text]), array('template_id'=>$_GET[p][1]));
        echo $_POST[message_text];
    }

    //编辑后保存模板
    function edit_save_b(){
        $templates_obj = app::get('market')->model('sms_templates');
        $templates_obj->update(array('content'=>$_POST[message_text]), array('template_id'=>$_GET['p'][1]));
        echo $_POST['message_text'];
    }

    //活动待执行
    function active_ex() {
        set_time_limit(0);
        $active_obj = app::get('market')->model('active');
        $templates_obj = app::get('market')->model('sms_templates');
        $edm_templates_obj = app::get('market')->model('edm_templates');
        $filter=array('active_id'=>$_GET[p][0]);
        $modiSign = true;
        //        echo "<Pre>";
        //        print_r($_POST);
        //        exit;
        //上一步
        if ($_POST['tempup_tag']=='uptag') {
            $mem_filter=$active_obj->dump($filter,'filter_mem,member_list');
            $aa=unserialize($mem_filter['filter_mem']);
            $src=$active_obj->update(array('is_active'=>'sel_member'),array('active_id'=>$_GET[p][0]));
            //下一步
        }elseif($_POST['exec_tag']=='uptag'){
            $active_data=$active_obj->dump($filter,'template_id');
            $src=$active_obj->update(array('is_active'=>'sel_template'),array('active_id'=>$_GET[p][0]));
            if($_GET['send_method'] == 'edm'){
                $data=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'templete_title');
                $temp['templete_title'] = $data['templete_title'];
            }
            //保存活动信息
        }else{
            if (empty($_POST['timing_date'])){
                $time_str='0';
            }else {
                $time=$_POST['timing_date']." ".$_POST['timing_hour'].":00:00";
                $time_str=strtotime($time);
            }
            $sentType = explode(',', $_POST['send_type']);
            $templete_title = urldecode($_POST['templete_title']);
            $templete = urldecode($_POST['templete']);
            $open_compare = $_POST['open_compare'];
            $unsubscribe = intval($_POST['unsubscribe']);
            if($open_compare != 'yes') $open_compare = 'no';

            if (isset($_GET['p'][1]) && $_GET['p'][1]) {
                if($_POST['send_type'] == 'edm'){
                    //$templatesInfo = $edm_templates_obj->dump(array('theme_id' => $_GET['p'][1]));
                    //$templete_title = $templatesInfo['theme_title'];
                    $templete_title = urldecode($_POST['templete_title']);
                }else{
                    $templatesInfo = $templates_obj->dump(array('template_id' => $_GET['p'][1]));
                    $templete_title = $templatesInfo['title'];
                }
            }

            $oldActiveInfo = $active_obj->dump(array('active_id' => $_GET['p'][0]));
            if ($_GET['p'][1] != $oldActiveInfo['template_id']) {
                $modiSign = false;
            }

            $templete_title_b = '';
            $templete_b = '';
            $template_id_b = '';
            if (isset($_GET['pb'][1]) && $_GET['pb'][1]) {
                $templatesInfoB = $templates_obj->dump(array('template_id' => $_GET['pb'][1]));
                $templete_title_b = $templatesInfoB['title'];
                $templete_b = $templatesInfoB['content'];
                $template_id_b = $templatesInfoB['template_id'];
                if ($_GET['pb'][1] != $oldActiveInfo['template_id_b']) {
                    $modiSign = false;
                }
            }

            $src=$active_obj->update(
            array(
                    'templete_title'=>$templete_title,
                    'templete'=>$templete,
                    'template_id'=>$_GET[p][1],
                    'type'=>serialize($sentType),
                    'coupon_id'=>$_POST['coupon_id'],
                    'is_active'=>'wait_exec',
                    'sent_time'=>$time_str,
                    'control_group'=>$open_compare,
                    'unsubscribe'=>$unsubscribe,
                    'templete_title_b' => $templete_title_b,
                    'templete_b' => $templete_b,
                    'template_id_b' => $template_id_b
            ),
            array('active_id'=>$_GET[p][0])
            );
        }

        //生成短信队列
        $result = array('res'=>'succ');
        if(strstr($_POST['send_type'],'sms') && $result['res'] == 'succ' ){
            if(!$this->processMember($_GET[p][0],$msg, $modiSign)){
                $msg = $msg ? $msg : '生成队列失败';
                $result = array('res'=>'fail','msg'=>$msg);
            }
        }

        if(strstr($_POST['send_type'],'sms') && $result['res'] == 'succ'){
            $nums = $this->checkSendStatus($_GET[p][0]);
            if($nums > 0){
                $result = array('res'=>'fail','msg'=>'本次营销活动存在之前未发送的客户,请稍后!如有问题,请联系我们的客服!');
            }
        }
        //error_log('111',3,__file__.'1.log');

        //生成邮件队列
        $result = array('res'=>'succ');
        if(strstr($_POST['send_type'],'edm') && $result['res'] == 'succ' ){
            if(!$this->edmProcessMember($_GET[p][0],$msg)){
                $msg = $msg ? $msg : '生成邮件队列失败';
                $result = array('res'=>'fail','msg'=>$msg);
            }
        }
        //error_log('111',3,__file__.'2.log');
        if(strstr($_POST['send_type'],'edm') && $result['res'] == 'succ' ){
            $nums = $this->edmCheckSendStatus($_GET[p][0]);
            if($nums > 0){
                $result = array('res'=>'fail','msg'=>'本次营销活动存在之前未发送的客户,请稍后!如有问题,请联系我们的客服!');
            }
        }
        //error_log('111',3,__file__.'3.log');

        //获取客户数
        if(strstr($_POST['send_type'],'sms') &&  $result['res'] == 'succ' ){
            $activityMemberQueue = $this->getActivityMemberNums($_GET[p][0]);
            $result['data'] = $activityMemberQueue;
        }

        if(strstr($_POST['send_type'],'edm') &&  $result['res'] == 'succ'){
            $edmActivityMemberQueue = $this->getEdmActivityMemberNums($_GET[p][0]);
            $result['data'] = $edmActivityMemberQueue;
        }
        //error_log('111',3,__file__.'4.log');
        if($temp){
            $result = array_merge($result,$temp);
        }
        echo json_encode($result);
        exit;
    }


    public function testsms()
    {
        /**
         *         $jobarray = array(
         'active_id'=>$active_id,
         'msgid'=>$msgid,
         );
         * Enter description here ...
         * @var unknown_type
         */

        $active_id = 122;
        $msgid='1358859800_286';
        $jobarray = array(
            'active_id'=>$active_id,
            'msgid' => $msgid
        );
        $sms = kernel::single('market_backstage_activity');
        //        $sms->sendSms($active_id, $msgid);
        $sms->fetch($jobarray);
    }
    public function sms_exec($active_id=0, $return=0){
        set_time_limit(360);
        $active_obj = app::get('market')->model('active');
        $sms_obj = kernel::single('market_service_sms');
        if($active_id == 0) $active_id = $_GET[p][0];
        $filter=array('active_id'=>$active_id);
        $tag=$active_obj->dump($filter);
        $result = array('res'=>'succ');
        $msgid=time().'_'.rand(100,999);

        //过滤24小时发送过的客户
        if($_POST['is_send_salemember'] == 0){
            $this->filterSentMember($active_id);
        }
        //记录用户选择是否营销24小时客户操作
        $active_obj->update(
        array(
            'is_send_salemember'=>$_POST['is_send_salemember'],
        ),
        array('active_id'=>$active_id)
        );


        if($result['res'] == 'succ'){
            $nums = $this->checkSendStatus($active_id);
            if($nums > 0){
                $result = array('res'=>'fail','msg'=>'本次营销活动存在之前未发送的客户('.$nums.'),请稍后!如有问题,请联系我们的客服!');
            }
        }

        $tag["type"] = unserialize($tag["type"]);
        if($tag["type"]){

            //检查发送优惠券
            if($result['res'] == 'succ'){
                if (in_array('coupon', $tag["type"])){
                    $result = $this->checkCoupon($tag['shop_id']);
                }
            }

            //检查发送短信
            if($result['res'] == 'succ'){
                if (in_array('sms', $tag["type"])){
                    $result = $this->checkSms($active_id,$msgid);
                    //$result = array('res'=>'succ');
                }
            }

            //发送队列
            if($result['res'] == 'succ'){
                if(!$this->sendActivityQueue($active_id,$tag['type'],$msgid)){
                    $result = array('res'=>'fail','msg'=>'发送队列失败');
                }
            }
        }else{
            $result = array('res'=>'fail','msg'=>'请选择发送短信或者优惠券');
        }

        if($return == 0){
            echo json_encode($result);
        }else{
            return $result;
        }
    }

    function sendActivityQueue($active_id,$type,$msgid){
        $jobarray = array(
            'active_id'=>$active_id,
            'msgid'=>$msgid,
        );
        /*if (!in_array('coupon', $type)){
         base_kvstore::instance('market')->fetch('account', $arr);
         $arr= unserialize($arr);
         $sms_config = array(
         'entid'=>$arr['entid'],
         'password'=>$arr['password'],
         'license'=>base_certificate::get('certificate_id') ? base_certificate::get('certificate_id') : 1,
         'source'=>APP_SOURCE,
         'app_token'=>APP_TOKEN,
         );
         $jobarray['sms_config'] = $sms_config;
         $jobarray['msgid'] = $msgid;
         }*/
        //var_export($jobarray);exit;
        if(kernel::single('taocrm_service_queue')->addJob('market_backstage_activity@fetch',$jobarray)){
            $this->sendSmsQueueSucc($active_id);
            return true;
        }else{
            return false;
        }
    }

    function checkSms($active_id,$msgid){
        //return  array('res'=>'succ');
        $smsInfo=$this->getSmsCount();
        if($smsInfo['smscount'] == -1){
            //如果发送短信失败，则删除营销超市创建的活动
            $this->market_active($active_id);
            return array('res'=>'fail','msg'=>'您的短信账号出现异常，请检查配置信息');
        }

        $activityMemberQueue = $this->getActivityMemberNums($active_id);
        $memberNums = $activityMemberQueue['valid_member_count'];
        if($memberNums <= 0){
            //如果发送短信失败，则删除营销超市创建的活动
            $this->market_active($active_id);
            return array('res'=>'fail','msg'=>'没有要发送的客户');
        }

        $active_obj = app::get('market')->model('active');
        $result = array('res'=>'succ');
        $systemType = kernel::single('taocrm_system')->getSystemType();
        $filter=array('active_id'=>$active_id);
        $tag=$active_obj->dump($filter);

        //开启对照组，数量减半
        if ($tag['control_group']=='yes') $memberNums = ceil($memberNums/2);

        //营销超市
        if ($tag['pay_type'] == 'market'){

            //检查是否开启营销评估
            /*if ($tag['control_group']=='yes'){
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
            }*/

            if($smsInfo['overcount'] >= $memberNums){
                //$this->sendSmsQueue($tag['active_id'],$sms_config,$msgid);
                //$this->sendSmsQueueSucc($tag['active_id']);
            }else{
                //如果发送短信失败，则删除营销超市创建的活动
                $active_obj->delete($filter);
                $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            }
        }else if ($tag['pay_type'] == 'pay'){//按效果付费用户
            //检查是否开启营销评估
            $overcount=$smsInfo['smscount']-($memberNums*$systemType['freeze_rule'])-$smsInfo['blocknum'];

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
            if($smsInfo['overcount'] >= $memberNums){
                //$this->sendSmsQueue($tag['active_id'],$sms_config,$msgid);
                //$this->sendSmsQueueSucc($tag['active_id']);
            }else{
                $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            }
        }

        return $result;
    }

    function sendSmsQueueSucc($active_id){
        $active_obj = app::get('market')->model('active');
        $active_obj->update(array('exec_time'=>time(),'is_active'=>'finish'),array('active_id'=>$active_id));
    }

    //获取营销活动的客户数
    function getActivityMemberNums($active_id){
        $db = kernel::database();
        $result = array('unvalid_member_count'=>0,'valid_member_count'=>0,'total_member_count'=>0,'sent_member_count'=>0);
        $row_valid = $db->selectrow('select count(*) as total from sdb_market_activity_m_queue where active_id='.$active_id .' and is_send = 1');
        $row_unvalid = $db->selectrow('select count(*) as total from sdb_market_activity_m_queue where active_id='.$active_id .' and is_send = 0');
        $result['unvalid_member_count'] = intval($row_unvalid['total']);
        $result['valid_member_count'] = intval($row_valid['total']);
        $result['total_member_count'] = $result['unvalid_member_count'] + $result['valid_member_count'];

        $e_time = time();
        $s_time = strtotime(date('Y-m-d 00:00:00'));

        /*
         *   $row = $db->selectrow('select count(*) as total
         from sdb_market_activity_m_queue as a
         left join active_member_sms_sent24 as b
         on a.member_id = b.member_id
         and a.is_send  = 1');
         */
        $row = $db->selectrow('select count(DISTINCT a.queue_id) as total
        from sdb_market_activity_m_queue as a
        inner join sdb_market_activity_m_queue as b
        on a.member_id = b.member_id
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

    function checkCoupon($shop_id){
        $result = array('res'=>'succ');
        $shopInfo = app::get('ecorder')->model('shop')->dump(array('shop_id'=>$shop_id),'*');
        if($shopInfo['node_type'] != 'taobao' || empty($shopInfo['addon'])){
            $result = array(
                'res'=>'fail',
                'msg'=>'请重新绑定'.$shopInfo['name'].',非淘宝店铺不能发送优惠券'
                );
        }
        //$shopInfo['addon'] = unserialize($shopInfo['addon']);
        elseif(empty($shopInfo['addon']['session'])){
            $result = array(
                'res'=>'fail',
                'msg'=>$shopInfo['name'].'登录失效，请到店铺管理内重新登录'
                );
        }

        return $result;
    }

    /*function sendCoupon($data){
     $result = $this->sendCouponQueue($data);

     return $result;
     }
     //优惠券发送任务
     function sendCouponQueue($data){
     $result = array('res'=>'succ');
     $shopInfo = app::get('ecorder')->model('shop')->dump(array('shop_id'=>$data['shop_id']),'*');
     if(!$shopInfo['addon'] || empty($shopInfo['addon']['session'])){
     return array('fail'=>'succ');
     }

     $jobarray = array(
     //'order_id'=>$data['order_id'],
     'shop_id'=>$data['shop_id'],
     'coupon_id'=>$data['coupon_id'],
     'buyer_nick'=>$data['buyer_nick'],
     'session'=>$shopInfo['addon']['session']
     );
     kernel::single('taocrm_service_gearman')->addJob('coupon_send',$jobarray);
     return true;
     }*/

    //短信发送任务
    /*function sendSmsQueue($active_id,$sms_config,$msgid){
    $jobarray = array(
    'active_id'=>$data['active_id'],
    'sms_config'=>$sms_config,
    'msgid'=>$msgid,
    );
    kernel::single('taocrm_service_gearman')->addJob('activity_fetch',$jobarray);
    return true;
    }*/

    function freezSms($block_num,$msgid){
        $smsintobj=kernel::single('market_service_smsinterface');
        $resunt=$smsintobj->freeze_sms($msgid,$block_num);
        if ($resunt['res']=='succ'){
            $op_obj = app::get('market')->model('sms_op_record');
            $para=array(
	            'action'=>'freeze',
	            'msgid'=>$msgid,
	            'nums'=>$block_num,
	            'remark'=>'短信冻结',
	            'create_time'=>time(),
            );
            $op_obj->save($para);

            return true;
        }else {
            return false;
        }
    }

    //作废活动
    function invalid_active($active_id)
    {
        $this->pagedata['active_id']=$active_id;
        $this->display('admin/active/invalid.html');
    }

    //重复营销
    public function repeat_active($active_id)
    {
        $activeObj = $this->app->model('active');
        $activeInfo = $activeObj->dump(array("active_id" => $active_id));
        $afterTime = 86400 * 15;
        $pageParams['create_time'] = strtotime(date("Y-m-d 00:00:00"));
        $pageParams['end_time'] = $pageParams['create_time'] + $afterTime;
        $is_active = 'sel_template';
        $pageParams['is_active'] = $is_active;
        $subArray = array('create_time', 'end_time', 'is_active', 'active_id', 'total_num', 'valid_num');
        foreach ($activeInfo as $k => $v) {
            if (in_array($k, $subArray)) {
                continue;
            }
            if ($k == 'active_name') {
                $pageParams[$k] = $v . '(重复营销)';
            }else {
                $pageParams[$k] = $v;
            }
        }
        $active_id = $activeObj->insert($pageParams);
        $_GET['p'][0] = $active_id;
        $_GET['p'][1] = $is_active;
        $this->editer_data();
    }

    function invalid(){
        $this->begin();
        if($_POST['invalid_name']=='on'){
            $active_id = floatval($_POST['active_id_name']);
        
            //调用java接口删除活动
            kernel::single('taocrm_middleware_activity')->delete_active($active_id);
        
            $active_obj = app::get('market')->model('active');
            $rec=$active_obj->update(
                array('is_active'=>'dead'),
                array('active_id'=>$active_id)
            );
            $this->end();
        }else {
            $this->end();
        }
    }

    function sms_send(){
        $active_obj = app::get('market')->model('active');
        $active_id=$_GET[p][0];
        $active_data=$active_obj->dump(array('active_id'=>$active_id),"*");
        echo(unserialize($active_data['filter_mem']));
    }

    //条款条件
    function legal_copy(){
        $op_id = kernel::single('desktop_user')->get_id();
        base_kvstore::instance('market')->fetch('legal_copy_info_'.$op_id,$legal_copy);
        $data = unserialize($legal_copy);
        $this->pagedata['data'] = $data['stat'];
        $this->display('admin/active/legal_copy.html');
    }

    //发送提醒
    function legal_notice(){
        $data = $_GET;
        $this->pagedata['active_id'] = $data['active_id'];
        $this->display('admin/active/legal_notice.html');
    }

    //保存条款条件同意状态
    function legal_store(){
        $data = $_POST;
        $op_id = kernel::single('desktop_user')->get_id();
        $data = serialize($data);
        base_kvstore::instance('market')->store('legal_copy_info_'.$op_id,$data);

    }

    //保存发送提醒同意状态
    function legal_save(){
        $data = $_POST;
        $active_id = $data['active_id'];
        unset($data['active_id']);
        $data = serialize($data);
        base_kvstore::instance('market')->store('legal_copy_info_'.$active_id,$data);
    }

    //判断是否已同意条款条件及发送提醒
    function get_legal(){
        $flag = 0;
        $active_id = $_POST['active_id'];
        $systemType = kernel::single('taocrm_system')->getSystemType();
        $system_type = $systemType['system_type'];
        $system_type = 2;
        if($system_type == 2){

            base_kvstore::instance('market')->fetch('legal_copy_info_'.$active_id,$legal_copy);
            $legal_copy = unserialize($legal_copy);

            $op_id = kernel::single('desktop_user')->get_id();
            //base_kvstore::instance('market')->store('legal_copy_info_'.$op_id,'');
            base_kvstore::instance('market')->fetch('legal_copy_info_'.$op_id,$data);
            $data = unserialize($data);
            if($data['stat'] == 'agree'){
                if($legal_copy['status'] != 'agree'){
                    $flag = 1;
                }
            }else{
                $flag = 2;
            }
        }
        echo $flag;
    }

    function save_template()
    {
        $this->pagedata['from'] = trim($_GET['from']);
        $this->display('admin/active/template_title.html');
    }

    function save_template_b()
    {
        $this->pagedata['from'] = trim($_GET['from']);
        $this->display('admin/active/template_title_b.html');
    }

    function add_template(){
        $type_obj = app::get('market')->model('sms_template_type');
        $template_obj = app::get('market')->model('sms_templates');
        $res = $template_obj->dump(array('title'=>urldecode($_POST['message_title'])));
        if($res){
        	$data['flag'] = true;
        }else{
	        $type = $type_obj->getList('*',array('is_fixed'=>1),0,1);
	        if(empty($type)){
	            $data = array('title'=>'系统模板','remark'=>'系统内置模板','is_fixed'=>1,'create_time'=>time());
	            $type_obj->save($data);
	            $type_id = $data['type_id'];
	        }else{
	            $type_id = $type[0]['type_id'];
	        }
	        $arr = array('title'=>urldecode($_POST['message_title']),'content'=>urldecode($_POST['message_text']),
	    				'type_id'=>$type_id,'create_time'=>time());
	        $template_obj->save($arr);
	        $template_id = $arr['template_id'];

	        $templates_obj = app::get('market')->model('sms_templates');
	        $templates_data=$templates_obj->getList("*");
	        $this->pagedata['templates_data']=$templates_data;//短信模板
	        $html = "<option value=\"0\">-请选择短信模板-</option>";
	        foreach($templates_data as $v){
	            if($v['template_id'] == $template_id){
	                $content = $v['content'];
	            }
	            $html .= "<option value='".$v['template_id']."'>".$v['title']."</option>";
	        }
	        $data['html'] = $html;
	        $data['content'] = $content;
	        $data['template_id'] = $template_id;
        }
        echo json_encode($data);
    }

    function add_template_b(){
        $type_obj = app::get('market')->model('sms_template_type');
        $template_obj = app::get('market')->model('sms_templates');
        $type = $type_obj->getList('*',array('is_fixed'=>1),0,1);
        if(empty($type)){
            $data = array('title'=>'系统模板','remark'=>'系统内置模板','is_fixed'=>1,'create_time'=>time());
            $type_obj->save($data);
            $type_id = $data['type_id'];
        }else{
            $type_id = $type[0]['type_id'];
        }
        $arr = array('title'=>urldecode($_POST['message_title_b']),'content'=>urldecode($_POST['message_text_b']),
    				'type_id'=>$type_id,'create_time'=>time());
        $template_obj->save($arr);
        $template_id = $arr['template_id_b'];

        $templates_obj = app::get('market')->model('sms_templates');
        $templates_data=$templates_obj->getList("*");
        $this->pagedata['templates_data']=$templates_data;//短信模板
        $html = "<option value=\"0\">-请选择短信模板-</option>";
        foreach($templates_data as $v){
            if($v['template_id'] == $template_id){
                $content = $v['content'];
            }
            $html .= "<option value='".$v['template_id']."'>".$v['title']."</option>";
        }
        $data['html'] = $html;
        $data['content'] = $content;
        $data['template_id_b'] = $template_id;
        echo json_encode($data);
    }

    function member_group($group_id){
        $mgroup_data = app::get('taocrm')->model('member_group');
        $fiter=array('group_id'=>$group_id);
        $group_data=$mgroup_data->getList("*",$fiter,0,-1);
        return $group_data;
    }

    //客户等级
    function member_lv(){
        $active_obj = app::get('market')->model('active');
        $memberlv_obj = app::get('ecorder')->model('shop_lv');
        $shop_id=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'shop_id,filter_mem');
        $lv_data=$memberlv_obj->getList("lv_id,name",array('shop_id'=>$shop_id['shop_id']));
        $filter_array=array();
        $filter_array=unserialize($shop_id['filter_mem']);
        foreach ($lv_data as $k=>$v){
            $lv_data[$k]['seletag']=$filter_array['lv_id'];
        }
        echo json_encode($lv_data);
    }

    //商品选择
    function product_select(){
        $active_obj = app::get('market')->model('active');
        $productlist=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'filter_mem');
        $productlist=unserialize($productlist['filter_mem']);
        $list=$productlist['product'];
        echo json_encode($list);
    }

    //地区选择
    function area_select(){
        $active_obj = app::get('market')->model('active');
        $productlist=$active_obj->dump(array('active_id'=>$_GET['p'][0]),'filter_mem');
        $productlist=unserialize($productlist['filter_mem']);
        $list=$productlist['area'];
        echo json_encode($list);
    }

    //店铺类型
    function shop_type(){
        $shop_id=$_GET['shop_id'];
        $shopObj = app::get('ecorder')->model('shop');
        $shop_type=$shopObj->dump(array('shop_id'=>$shop_id),'node_type');
        echo $shop_type['node_type'];
    }

    public function getSmsCount(){

        $active_id=$_GET[p][0];
        $active_obj = app::get('market')->model('active');
        $send=kernel::single('market_service_smsinterface');
        $memsms=kernel::single('market_service_sms');
        $send_info=$send->get_usersms_info();//get_usersms_info

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
        }

        $infoarray=array(
            'smscount'=>$month_residual,
            'blocknum'=>$blocknums,
            'overcount'=>$month_residual- $blocknums,
        );
        return $infoarray;
    }

    function processMember($active_id,&$msg, $modiSign = true){
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_active where active_id='.$active_id);
        if(!$activity)return false;
        if($activity['is_active'] == 'dead'){
            $msg = '活动已作废!';
            return false;
        }
        //队列已存在就不重新创建了
        $row = $db->selectrow('select count(*) as total from sdb_market_activity_m_queue where active_id='.$active_id);
        if($row['total'] > 0 && $modiSign)return true;

        //如果短信模板被修改，删除短信队列
        if ($modiSign == false) {
            $db->exec('delete from sdb_market_activity_m_queue where active_id = ' . $active_id);
        }

        $shop = $db->selectrow('select name from sdb_ecorder_shop where shop_id="'.$activity['shop_id'].'"');
        $activity['shop_name'] = $shop['name'];
        if($activity){
            $objMemberGroup = app::get('taocrm')->model('member_group');
            if($activity['member_list']!='') {
                $member_list = unserialize($activity['member_list']);
                if(strstr($member_list[0],'group_id')){
                    // 1.自定义分组
                    $group_id = str_replace('group_id:','',$member_list[0]);
                    $sql = "SELECT filter FROM sdb_taocrm_member_group WHERE group_id=$group_id";
                    $rs = $db->selectrow($sql);
                    if($rs){
                        $sql = $objMemberGroup->gmBuildFilterSQL(unserialize($rs['filter']),$activity['shop_id'],$active_id);
                    }else{
                        $sql = false;
                    }
                }else{
                    // 2.直接勾选客户
                    if ($member_list == null) {
                        $market_user_id = kernel::single('desktop_user')->get_id();
                        base_kvstore::instance('analysis')->fetch('filter_member_' . $market_user_id, $membersList);
                        if ($membersList) {
                            $member_list = explode(',', $membersList);
                        }
                    }
                    $sql = "SELECT $active_id as active_id,member_id,uname,name as truename,mobile FROM sdb_taocrm_members WHERE member_id in (".implode(',',$member_list).")";
                }
            }elseif($activity['filter_sql']!=''){
                // 4.报表sql语句
                $sql = $activity['filter_sql'];
                $sql = "SELECT $active_id as active_id,a.member_id,a.uname,a.name as truename,a.mobile
                FROM sdb_taocrm_members as a
                inner join ($sql) as b on a.member_id=b.member_id";
            }else{
                // 3.自定义筛选条件
                $filter_mem = unserialize($activity['filter_mem']);
                $sql = $objMemberGroup->gmBuildFilterSQL($filter_mem['filter'],$activity['shop_id'],$active_id);
            }

            if($sql){
                //先清空活动之前的短信记录
                $db->exec('delete from sdb_market_activity_m_queue where active_id='.$active_id);
                $insertSql = 'INSERT INTO sdb_market_activity_m_queue(active_id,member_id,uname,truename,mobile) '.$sql;
                if(!$db->exec($insertSql)){
                    return false;
                }

                //更新模板id
                $db->exec('update sdb_market_activity_m_queue set template_id='.$activity['template_id'].' where active_id='.$active_id);
                //                if ($activity['template_id_b']) {
                //                    $result = $db->select('select count(*) as _count from sdb_market_activity_m_queue where active_id = ' . $active_id);
                //                    echo "sss";
                //                    print_r($result);
                //                }
            }
        }

        //无效1：删除空号码队列
        $db->exec('update sdb_market_activity_m_queue set is_send=0 where active_id='.$active_id.' and mobile =""');

        //无效2：黑名单客户不发送
        $db->exec('update sdb_market_activity_m_queue as a
inner join  sdb_taocrm_members  as b
on a.member_id=b.member_id
 set a.is_send=0
where b.sms_blacklist="true"');

        //无效3：过滤重复数据
        $mobile_re_rows = $db->select('select count(*) as total,mobile from sdb_market_activity_m_queue where active_id = '.$active_id.' group by mobile having total>1');
        if($mobile_re_rows){
            $ids = array();
            foreach($mobile_re_rows as $row){
                $mobile_info_rows = $db->select('select queue_id from sdb_market_activity_m_queue where active_id = '.$active_id .' and mobile="'.$row['mobile'].'"');
                foreach($mobile_info_rows as $k=>$moble_row){
                    if($k == 0)continue;
                    $ids[] = $moble_row['queue_id'];
                }
            }
            $db->exec('update sdb_market_activity_m_queue set is_send = 0 where queue_id in('.implode(',', $ids).')');
        }


        //有效客户数量
        $row = $db->selectrow('select count(*) as member_nums from sdb_market_activity_m_queue where is_send=1 and active_id='.$active_id);
        $member_nums = intval($row['member_nums']);
        //如果开启活动对照组
        if($activity['control_group'] == 'yes'){
            //短信组起始位置
            $length=$member_nums/2;
            if ( ceil($length) != $length) {
                $length = ceil($length);
            }
            $activeMemberNums = $length;
            $assessMemberNums = $member_nums - $length;
        }
        else {
            $activeMemberNums = $member_nums;
            $assessMemberNums = 0;
        }

        //更新模板B
        if ($activity['template_id_b']) {
            //短信B模板起始位置
            $offset_b = ceil($activeMemberNums / 2) + $assessMemberNums;
            //查询B短信第一条记录的前一条记录
            $lastSql = 'select * from sdb_market_activity_m_queue where  active_id = ' .$active_id . ' AND is_send = 1 limit ' . ($offset_b - 1) . ' , 1';
            $lastRecord = $db->select($lastSql);
            //获得当前记录的队列ID号
            $lastQueueId = $lastRecord[0]['queue_id'];
            $updateSql = 'update sdb_market_activity_m_queue set template_id = ' . intval($activity['template_id_b'])
            . ' WHERE active_id = ' .$active_id . ' AND is_send = 1 AND  queue_id > ' . $lastQueueId;
            $db->exec($updateSql);
        }
        if ($active_id) {
            $activityMqueue = $this->app->model('activity_m_queue');
            $totalNum = $activityMqueue->getTotal($active_id);
            $validNum = $activityMqueue->getIsSendNum($active_id);
            $activeUpdateSql = "UPDATE `sdb_market_active`
                                SET `total_num` = {$totalNum}, `valid_num` = {$validNum}
                                WHERE `active_id` = {$active_id}";
            $db->exec($activeUpdateSql);
        }

        return true;
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
            $objMemberGroup = app::get('taocrm')->model('member_group');
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
                    if ($member_list == null) {
                        $market_user_id = kernel::single('desktop_user')->get_id();
                        base_kvstore::instance('analysis')->fetch('filter_member_' . $market_user_id, $membersList);
                        if ($membersList) {
                            $member_list = explode(',', $membersList);
                        }
                    }
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

        if ($active_id) {
            $activityMqueue = $this->app->model('activity_edm_queue');
            $totalNum = $activityMqueue->getTotal($active_id);
            $validNum = $activityMqueue->getIsSendNum($active_id);
            $activeUpdateSql = "UPDATE `sdb_market_active`
                                SET `total_num` = {$totalNum}, `valid_num` = {$validNum}
                                WHERE `active_id` = {$active_id}";
            $db->exec($activeUpdateSql);
        }
        return true;
    }

    // 创建队列判断当前活动队列和之前未发送队列有没有冲突（模板id,member_id）防止发送重复
    function checkSendStatus($active_id){
        $db = kernel::database();
        $row = $db->selectrow('select count(DISTINCT a.queue_id) as total
        from sdb_market_activity_m_queue as a
        inner join sdb_market_activity_m_queue as b
        on a.member_id = b.member_id
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
        on a.member_id = b.member_id
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
        $s_time = strtotime(date('Y-m-d 00:00:00'));
        $page = 0;
        $page_size = 1000;
        $db = kernel::database();
        $db->exec('update sdb_market_activity_m_queue as a
         inner join sdb_market_activity_m_queue as b
         on a.member_id = b.member_id set a.is_send=0
         where b.is_send_finish=1
         and a.is_send = 1
         and a.active_id='.$active_id.'
         and b.active_id!='.$active_id .'
         and b.sent_time>='.$s_time.'
         and b.sent_time<='.$e_time);


        /*while(true){
         $rows = $db->select('select DISTINCT a.queue_id
         from sdb_market_activity_m_queue as a
         inner join sdb_market_activity_m_queue as b
         on a.member_id = b.member_id
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
         }*/
    }

    //edm 操作
    public function edm_exec(){
        set_time_limit(360);
        $active_obj = app::get('market')->model('active');
        $edm_obj = kernel::single('market_service_edm');
        $active_id = $_GET[p][0];
        $filter=array('active_id'=>$active_id);
        $tag=$active_obj->dump($filter);
        $result = array('res'=>'succ');
        $msgid=time().'_'.rand(100,999);

        //过滤24小时发送过的客户
        if($_POST['is_send_salemember'] == 0){
            $this->filterSentMember($active_id);
        }

        if($result['res'] == 'succ'){
            $nums = $this->checkSendStatus($active_id);
            if($nums > 0){
                $result = array('res'=>'fail','msg'=>'本次营销活动存在之前未发送的客户('.$nums.'),请稍后!如有问题,请联系我们的客服!');
            }
        }

        //edm_obj
        $tag["type"] = unserialize($tag["type"]);

        if($tag["type"]){

            //检查发送短信
            if($result['res'] == 'succ'){
                if (in_array('edm', $tag["type"])){
                    $result = $this->checkEdm($active_id,$msgid);
                    //print_r($result);
                    //exit();
                    //$result = array('res'=>'succ');
                }
            }

            //发送队列
            if($result['res'] == 'succ'){
                if(!$this->sendEdmActivityQueue($active_id,$tag['type'],$msgid)){
                    $result = array('res'=>'fail','msg'=>'发送队列失败');
                }
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
        $active_obj = app::get('market')->model('active');
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
        }

        $infoarray=array(
            'edmcount'=>$month_residual,
        );
        return $infoarray;
    }

    function checkEdm($active_id,$msgid){
        //return  array('res'=>'succ');
        $smsInfo=$this->getEdmCount();
        if($smsInfo['edmcount'] == -1){
            return array('res'=>'fail','msg'=>'您的邮件账号出现异常，请检查配置信息');
        }

        $activityMemberQueue = $this->getEdmActivityMemberNums($active_id);
        $memberNums = $activityMemberQueue['valid_member_count'];

        if($memberNums <= 0){
            return array('res'=>'fail','msg'=>'没有要发送的客户');
        }

        $active_obj = app::get('market')->model('active');
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
            if($smsInfo['edmcount'] >= $memberNums){
                //$this->sendSmsQueue($tag['active_id'],$sms_config,$msgid);
                //$this->sendSmsQueueSucc($tag['active_id']);
            }else{
                $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            }
        }

        return $result;
    }

    //获取edm营销活动的客户数
    function getEdmActivityMemberNums($active_id){
        $db = kernel::database();
        //$count = $this->app->model('active')->get_member_count($active_id);
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
        //error_log('select count(DISTINCT a.queue_id) as total from sdb_market_activity_edm_queue as a inner join sdb_market_activity_edm_queue as b on a.template_id=b.template_id and a.member_id = b.member_id where b.is_send_finish=1 and a.is_send = 1 and a.active_id='.$active_id.' and b.active_id!='.$active_id .' and b.sent_time>='.$s_time.' and b.sent_time<='.$e_time ,3,__file__.'s001.log');
        $row = $db->selectrow('select count(DISTINCT a.queue_id) as total
        from sdb_market_activity_edm_queue as a
        inner join sdb_market_activity_edm_queue as b
        on a.member_id = b.member_id
        where b.is_send_finish=1
         and a.is_send = 1
         and a.active_id='.$active_id.'
         and b.active_id!='.$active_id .'
         and b.sent_time>='.$s_time.'
         and b.sent_time<='.$e_time
        );
        //error_log('3333',3,__file__.'s002.log');

        $result['sent_member_count'] = intval($row['total']);

        return $result;
    }

    function sendEdmActivityQueue($active_id,$type,$msgid){

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

    //快速创建活动
    public function one_page() {

        $market_id = $_GET['market_id'];

        if($_GET['filter_from']=='market'){
            $report_filter = json_encode($_GET);
            $this->pagedata['report_filter'] = $report_filter;//店铺信息
            $this->pagedata['filter_sql'] = 2;
            $sql = kernel::single('plugins_market')->getMarketSql($market_id,$_GET['shop_id']);
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->store('filter_sql_market_'.$user_id,$sql);
        }

        $shopObj = app::get('ecorder')->model('shop');
        $shop_id_data=$shopObj->dump(array('active_id'=>$_GET[p][0]));
        $this->pagedata['data']=$shop_id_data;
        $shopList=$shopObj->getList("*");

        //营销超市
        if ($_GET['filter_from']=='market'){
            $shopdata = array();
            foreach($shopList as $shop){
                if($shop['shop_id'] == $_GET['shop_id']){
                    $shopdata[] = $shop;
                }
            }
            $rule = kernel::single('plugins_market')->getRule($market_id);
            $this->pagedata['active'] = array('active_name'=>'[营销超市]'.$rule['title'].'-'.date('Ymd'));
        }

        if($_GET['filter_from']=='market'){

            $report_filter = json_encode($_GET);
            $this->pagedata['report_filter'] = $report_filter;//店铺信息
            $this->pagedata['filter_sql'] = 2;
            $sql = kernel::single('plugins_market')->getMarketSql($market_id,$_GET['shop_id']);
            $sms_body = kernel::single('plugins_market')->getMarketSmsBody($market_id);
            $user_id = kernel::single('desktop_user')->get_id();
            base_kvstore::instance('analysis')->store('filter_sql_market_'.$user_id,$sql);

            $this->pagedata['sms_body'] = $sms_body;
            $this->pagedata['market_id'] = $market_id;
        }

        $this->pagedata['shopList']=$shopdata;//店铺信息

        if(!empty($_GET[p][0])){
            $group_data=$this->member_group($_GET[p][0]);
            $this->pagedata['groupdata']=$group_data[0]['group_id'];//客户分组
        }

        $shop_id = $_GET['shop_id'];
        if($shop_id){//客户列表中的 shop_id
            $rs_shop = $shopObj->dump($shop_id);
            $this->pagedata['shop'] = $rs_shop;
        }

        if(!empty($_GET[shop_id])&& $_GET['memlist']==1){//客户列表中的 shop_id
            $this->pagedata['member_list']='member_list';
        }

        //echo('<pre>');var_dump($_GET);

        $this->_init_config_arr();//初始化表单项目

        $send_method = $_GET['send_method']=='edm'? 'edm':'sms';
        $this->pagedata['send_method']   = $send_method; //定义发送类型 短信还是邮件

        $this->pagedata['beigin_time'] = date("Y-m-d",time());
        if(isset( $this->pagedata['active'])){
            $this->pagedata['active']['type'] = array($send_method);
        }else{
            $this->pagedata['active'] = array('type'=>array($send_method));
        }

        $this->pagedata['actity_type'] = json_encode(array());
        $this->display('admin/active/one_page.html');
    }

    public function onepage_count(){
        //防止页面超时
        set_time_limit(60*30);
        $db = kernel::database();

        $market_id = $_GET['market_id'];
        $sql = kernel::single('plugins_market')->getMarketSql($market_id,$_GET['shop_id']);
        $sms_body = kernel::single('plugins_market')->getMarketSmsBody($market_id);
        $user_id = kernel::single('desktop_user')->get_id();
        base_kvstore::instance('analysis')->store('filter_sql_market_'.$user_id,$sql);
        //var_dump($sms_body);

        $sql_tmp = "select count(*) as total from ({$sql}) as a";
        $total_member = $db->selectrow($sql_tmp);
        //var_dump($total_member);

        $sql_tmp = "select count(distinct a.mobile) as total from sdb_taocrm_members as a
        inner join ({$sql}) as b on a.member_id=b.member_id
        where a.sms_blacklist='false' and a.mobile<>''";
        $valid_member = $db->selectrow($sql_tmp);
        //var_dump($sql);

        $e_time = time();
        $s_time = $e_time-86400;
//        $sql_tmp = "select count(distinct member_id) as total from sdb_market_activity_m_queue
//        where is_send_finish=1 and sent_time>={$s_time} and sent_time<={$e_time}";
        //修正为本次营销下选中的客户数
//        $sql_tmp = "select count(distinct member_id) as total from sdb_market_activity_m_queue
//        where is_send_finish=1 and sent_time>={$s_time} and sent_time<={$e_time} and member_id in ({$sql})";
        $sql_tmp = "select count(distinct member_id) as total from sdb_market_activity_m_queue
        where is_send_finish=1 and sent_time>={$s_time} and sent_time<={$e_time} and member_id in ( select h.member_id from ({$sql}) as h )";
        $sent_member = $db->selectrow($sql_tmp);

        $data = array();
        $data['res'] = 'succ';
        $data['count'] = array(
            'total_member' => $total_member['total'],
            'valid_member' => $valid_member['total'],
            'unvalid_member' => $total_member['total'] - $valid_member['total'],
            'sent_member' => $sent_member['total'],
        );

        echo(json_encode($data));
    }

    //执行一键发送
    public function onepage_run(){

        set_time_limit(60*30);

        $data = array('res'=>'succ','msg'=>'成功');
        $active_id = intval($_POST['active_id']);
        $market_id = intval($_POST['market_id']);
        $shop_id = $_POST['shop_id'];
        $active_name = $_POST['active_name'];
        $is_send_salemember = intval($_POST['is_send_salemember']);
        $unsubscribe = intval($_POST['unsubscribe']);
        $sms_body = $_POST['sms_body'];

        //判断是否已经存在同样的活动
        if($active_id === 0){
            $filter_sql = kernel::single('plugins_market')->getMarketSql($market_id,$shop_id);


            //初始化默认参数
            $report_filter = array(
                'app' => 'market',
                'ctl' => 'admin_active',
                'act' => 'create_active',
                'filter_from' => 'market',
                'send_method' => 'sms',
                'market_id' => $market_id,
                'shop_id' => $shop_id,
            );
            $active_obj = app::get('market')->model('active');
            $save_arr = array();
            $save_arr['create_time'] = time();
            $save_arr['end_time'] = time() + 86400*15;
            $save_arr['control_group'] = 'no';
            $save_arr['is_active'] = 'wait_exec';//等待执行
            $save_arr['pay_type'] = 'market';
            $save_arr['report_filter'] = json_encode($report_filter);
            $save_arr['type'] = serialize(array('sms'));
            $save_arr['active_name'] = $active_name;
            $save_arr['shop_id'] = $shop_id;
            $save_arr['filter_sql'] = $filter_sql;
            $save_arr['templete'] = $sms_body;
            //新增营销短信模板标题
            $save_arr['templete_title'] = mb_substr($sms_body, 0, 4, "utf-8");
            $save_arr['unsubscribe'] = $unsubscribe;
            $active_obj->save($save_arr);
            $active_id = $save_arr['active_id'];
        }

        //生成短信队列
        if(!$this->processMember($active_id,$msg)){
            $msg = $msg ? $msg : '生成队列失败';
            $data = array(
                'res'=>'fail',
                'msg'=>$msg,
                'active_id'=>$active_id
            );
            echo json_encode($data);
            die();
        }

        //过滤24小时发送过的客户
        if($is_send_salemember == 0){
            $this->filterSentMember($active_id);
        }

        $nums = $this->checkSendStatus($active_id);
        if($nums > 0){
            $data = array(
                'res'=>'fail',
                'msg'=>'本次营销活动存在之前未发送的客户,请稍后再试!如有疑问请联系客服!',
                'active_id'=>$active_id
            );
            echo json_encode($data);
            die();
        }

        $data = $this->sms_exec($active_id, 1);
        $data['active_id'] = $active_id;
        echo json_encode($data);
        die();
    }

    //判断活动是否来自营销超市
    public function market_active($active_id)
    {
        $active_obj = app::get('market')->model('active');
        $filter=array('active_id'=>$active_id);
        $tag=$active_obj->dump($filter);
        if($tag['pay_type'] == 'market'){
            $active_obj->delete($filter);
        }
    }

    public function guide()
    {
        $this->page('admin/active/guide.html');
    }
}
