<?php
class market_ctl_admin_fx_activity extends desktop_controller{
    //var $workground = 'taocrm.fxmember';

    var $is_debug = false;
    var $workground = 'taocrm.fxmember';
     public static $middleware_connect = null;


    public function index()
    {
        $title = '营销活动列表';
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[]=$v['shop_id'];
        }
        if($_GET['view']){
            $view=($_GET['view']-1);
            $shop_id=$shops[$view];
        }
        $baseFilter = array();
        $this->finder('market_mdl_fx_activity',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        	'actions'=>array(
        array(
                'label'=>'创建短信营销',
                'href'=>'index.php?app=market&ctl=admin_fx_activity&act=create_activity&shop_id='.$shop_id,
                'target'=>'dialog::{onClose:function(){window.location.reload();},width:650,height:355,title:\'创建活动\'}'
                ),

                ),
                //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
                // 'use_buildin_filter'=>false,
                //  'use_buildin_tagedit'=>false,
                ));
    }

    function _views(){
        $sql = 'select * from sdb_ecorder_shop where (shop_type="taobao" and subbiztype="fx") or shop_type="shopex_b2b" ';
        $memberObj = &app::get('market')->model('fx_activity');
        $base_filter=array('type|nohas'=>'edm','is_active|nohas'=>'dead');
        $sub_menu[] = array(
            'label'=> '全部',
            'filter'=> $base_filter,
            'optional'=>false,	
        );
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->db->select($sql);

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

    function create_activity()
    {
    	$group_id = intval($_GET[p]['group_id']);
		
    	//取出自定义分组的客户
        if ($group_id > 0){
            $member_analysisObj = &app::get('taocrm')->model('fx_member_group');
            $member_group=$member_analysisObj->dump($group_id);
            $this->pagedata['userslist'] = 'group_id:'.$group_id;
            $this->pagedata['activity'] = array('activity_name'=>$member_group['group_name'].'_'.date('Ymd'));
        }
        
        $this->_init_config_arr();
        $shopObj = &app::get('ecorder')->model('shop');
        $sql = 'select * from sdb_ecorder_shop where (shop_type="taobao" and subbiztype="fx") or shop_type="shopex_b2b" ';
        $shopList=$shopObj->db->select($sql);
        $this->pagedata['shopList']=$shopList;//店铺信息

    	$shop_id = trim($_GET['shop_id']);
        if($shop_id){//客户列表中的 shop_id
            $oneshop=$shopObj->dump(array('shop_id'=>$shop_id),'shop_id,name');
            $this->pagedata['oneshop']=$oneshop;
        }
        $this->pagedata['beigin_time'] = date("Y-m-d",time());
        $this->pagedata['end_time'] = date('Y-m-d',strtotime('+15 days'));

        $templates_obj = &app::get('market')->model('sms_templates');
        $templates_data=$templates_obj->getList("*",array('status'=>1));
        
        $this->pagedata['CacheId'] = trim($_GET['CacheId']);
        $this->pagedata['CacheIdCreateTime'] = trim($_GET['CacheIdCreateTime']);
        $this->pagedata['templates_data']=$templates_data;//短信模板
        $this->display('admin/activity/sms/create_activity_new.html');
    }

    function toAdd_new()
    {
        $activity_obj = &app::get('market')->model('fx_activity');
        if ( ! $_GET[p]['activity_id']){
            $rs = $activity_obj->dump(array('activity_name'=>$_POST['activity_name']));
            if($rs){
                $result = array(
                    'res'=>'fail',
                    'msg'=>'活动名称已经存在，请不要重复创建活动。'
                    );
                    echo(json_encode($result));
                    die();
            }
        }

        unset($_POST['filter_sql']);
        
        $_POST['start_time'] = strtotime($_POST['create_time']);
        $_POST['end_time'] = (!empty($_POST['end_time'])) ? strtotime($_POST['end_time']) : ($_POST['create_time'] + 1296000);
        $_POST['create_time'] = time();

        $_POST['is_active']='sel_member';
        //自定义分组
    	if ($_POST['userslist'] != ''){
            $userslist=explode("," , $_POST['userslist']);
            $_POST['member_list']=serialize($userslist);
        }

        //跳转到选择模板步骤
        if ($_POST['userslist']!='' || $_POST['cache_id']){
            $_POST['is_active']='sel_template';
        }
         
        if (!empty($_GET[p]['activity_id'])){
            $activity_obj->update(array('activity_id'=>$_GET[p]['activity_id']),$_POST);
        }else{
             
            $_POST['type'] = serialize(array($_POST['send_method'])); //新增时获取类型短信还是邮件的
            $rs=$activity_obj->save($_POST);
        }
        $activity_id=$_POST['activity_id']?$_POST['activity_id']:$_GET[p]['activity_id'];
        $as_array=array('activity_id'=>$activity_id,'shop_id'=>$_POST['shop_id']);


        $result = array('res'=>'succ','data'=>$as_array);
        echo(json_encode($result));
    }

    protected function _init_config_arr()
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

    public function select_member_data()
    {
        $activity_obj = &app::get('market')->model('fx_activity');
        $filter=array('activity_id'=>$_GET[p][0]);

        if ($_POST['uptag']=='uptag') {
            $filter=array('activity_id'=>$_GET[p][0]);
            $dump_data=$activity_obj->dump($filter);
            $dump_data['create_time']=date('Y-m-d',$dump_data['create_time']);
            $dump_data['end_time']=date('Y-m-d',$dump_data['end_time']);

            echo(json_encode($dump_data));

        }else{
            $activity_id=$_GET[p][0];
            $data=$activity_obj->dump(array('activity_id'=>$activity_id));
            $shop_id=trim($data['shop_id']);
            $filter = $_POST['filter'];
            $filter['shop_id'] = $shop_id;
            //$filter=array('shop_id'=>$shop_id);
            $str_post=serialize($filter);//保存活动对应的筛选条件
            $rs=$activity_obj->update(
            array('filter_mem'=>$str_post,'is_active'=>'sel_template'),
            array('activity_id'=>$_GET[p][0])
            );
             
            echo json_encode(array('res'=>'succ'));
        }
    }

    //step2 评估客户数量
    function assess()
    {	
    	/*
        $shop_id = &$_POST['shop_id'];
        $filter = &$_POST['filter'];
        $filter['shop_id'] = $shop_id;
        $smsInfo = kernel::single('market_mdl_fx_activity')->getSmsTaskInfo($filter);
        echo $smsInfo['totalMembers'];
        die();
        */
        $connect = $this->getConnect();
        $smsInfo = $connect->FxSearchMemberAnalysisList($_POST);
        echo $smsInfo['Count'];
        die();
    }

    function editer_data()
    {
        set_time_limit(360);
        $activity_id=$_GET['p'][0];
        $activity_obj = &app::get('market')->model('fx_activity');
        if(trim($_GET[p]['selectmember'])=='selecemember'){
            $activity_obj->update(array('filter_mem'=>""),array('activity_id'=>$_GET['p'][0]));
        }

        //清空数据库客户的条件
        $oneactivity_data=$activity_obj->dump(array('activity_id'=>$activity_id));
        
        if($oneactivity_data['cache_id']){
        	$result = kernel::single('taocrm_middleware_activity')->getCacheInfo($oneactivity_data['cache_id']);
        	if(!$result){
        		$this->display('admin/active/notice.html');
        		die();
        	}
        		
        }
        

        //营销活动对应的客户数量
        $type_array = unserialize($oneactivity_data['type']);

        //获取短信的客户信息
        $send_method = 'sms';
        //$activityMemberNums = $this->getActivityMemberNums($activity_id);
        $activityMemberNums = $this->geteEditerMemberCount($activity_id,$rmsg,'edit');

        $this->pagedata['activityMemberNums'] = $activityMemberNums;

         
        $shopid=$oneactivity_data['shop_id'];
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shop_id_data=$shopObj->dump(array('shop_id'=>$shopid));
        $oneactivity_data['shop_name'] = $shop_id_data['name'];

        //转换过滤条件
        $filter_mem = unserialize($oneactivity_data['filter_mem']);
        $filter_mem['filter']['goods_id'] = implode(',',$filter_mem['filter']['goods_id']);
        $filter_mem['filter']['regions_id'] = implode(',',$filter_mem['filter']['regions_id']);
        if($filter_mem['filter']['last_buy_time']['min_val']) $filter_mem['filter']['last_buy_time']['min_val'] = date('Y-m-d',$filter_mem['filter']['last_buy_time']['min_val']);
        if($filter_mem['filter']['last_buy_time']['max_val']) $filter_mem['filter']['last_buy_time']['max_val'] = date('Y-m-d',$filter_mem['filter']['last_buy_time']['max_val']);
        if($filter_mem['filter']['birthday']['min_val']) $filter_mem['filter']['birthday']['min_val'] = date('Y-m-d',$filter_mem['filter']['birthday']['min_val']);
        if($filter_mem['filter']['birthday']['max_val']) $filter_mem['filter']['birthday']['max_val'] = date('Y-m-d',$filter_mem['filter']['birthday']['max_val']);

        $this->pagedata['filter_mem']=$filter_mem;
        $this->pagedata['data']=$shop_id_data;

        $this->pagedata["activity_id"]= $_GET[p][0];//活动id 包含客户的条件
        $rs = app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('region_grade'=>1));
        if($rs){
            foreach($rs as $v){
                if(!$v['group_name']) $v['group_name'] = '其它';
                $regions[$v['group_name']][$v['region_id']] = $v['local_name'];
            }
        }
        $shopdata=$shopObj->getList("*",array('shop_type'=>'taobao','subbiztype'=>'fx'));
        $this->pagedata['shopList']=$shopdata;
        $this->pagedata['regions'] = $regions;

        $activity_data=$activity_obj->getList("*");
         
        //if($send_method == 'sms'){
            //短信模板
            $templates_obj = &app::get('market')->model('sms_templates');
            $this->pagedata['reg_data']=$reg_data;
            $templates_data=$templates_obj->getList("*",array('status'=>1));
            if($templates_data){
                foreach($templates_data as $v) {
                    if($v['template_id'] == $oneactivity_data['template_id'])
                    $oneactivity_data['template_name'] = $v['title'];
                }
            }
        //}

        $oneactivity_data['type'] = unserialize($oneactivity_data['type']);
        if($oneactivity_data['type'] ){
            $actity_type = $oneactivity_data['type'];
        }else{
            $actity_type = array();
        }
        $actity_type = json_encode($actity_type);

        //短信模版B信息
        $this->pagedata['send_method']=$send_method;
        $this->pagedata['actity_type'] = $actity_type;
        $this->pagedata['activity']=$oneactivity_data;
        $this->pagedata['activity_data']=$activity_data;
        $this->pagedata['templates_data']=$templates_data;
        $this->pagedata['edm_templates_data']=$edm_templates_data;
        $this->pagedata['open_compare'] = $oneactivity_data['control_group'];//开启对照组
        $this->pagedata['unsubscribe'] = $oneactivity_data['unsubscribe'];//开启退订
        $this->pagedata["tag"]= $_GET['p'][1];
        $this->_init_config_arr();//初始化表单项目
        $this->display('admin/activity/sms/create_activity_new.html');
    }

    protected function geteEditerMemberCount($activity_id)
    {
        return $this->getSMSTaskInfo($activity_id,$msg,'edit');
    }

    protected function getSMSTaskInfo($activity_id,&$msg, $resource = 'create')
    {
        $activityCount = array();
        $db = kernel::database();
        $activity = $db->selectrow('select * from sdb_market_fx_activity where activity_id = ' . $activity_id);
        if(!$activity)return $activityCount;
        if($activity['is_active'] == 'dead'){
            $msg = '活动已作废!';
            return $activityCount;
        }
		$result = kernel::single('taocrm_middleware_activity')->GetFxMarketActivityInfo($activity_id);
       
		 $activeUpdateSql = "UPDATE `sdb_market_fx_activity`
                                SET `total_num` = ".$result['Count'].", `valid_num` = ".$result['Send']." 
                                WHERE `activity_id` = {$activity_id}";

		$db->exec($activeUpdateSql);
		
		$activityCount = array(
        	'totalMembers' => $result['Count'],
            'unvalidMembers' => $result['UnSend'],
            'validMembers' => $result['Send'],
            'WaitSendMember' => $result['Send'],
        );
        
        return $activityCount;
        //return kernel::single('market_mdl_fx_activity')->getSmsTaskInfo($activity['filter_mem']);
    }

    public function active_ex()
    {
        set_time_limit(0);
        $activity_id = intval($_GET[p][0]);
        $active_obj = &app::get('market')->model('active');
        $templates_obj = &app::get('market')->model('sms_templates');
        $edm_templates_obj = app::get('market')->model('edm_templates');
        $filter=array('activity_id' => $activity_id);
        $modiSign = true;

        //上一步:选择模板
        if($_POST['tempup_tag']=='uptag'){
            $mem_filter=$active_obj->dump($filter,'filter_mem,member_list');
            $aa=unserialize($mem_filter['filter_mem']);
            $src=$active_obj->update(array('is_active'=>'sel_member'),array('activity_id'=>$activity_id));
            //下一步:发送短信
        }elseif($_POST['exec_tag']=='uptag'){
            $active_data=$active_obj->dump($filter,'template_id');
            $src=$active_obj->update(array('is_active'=>'sel_template'),array('activity_id'=>$activity_id));
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

            $oldActiveInfo = $active_obj->dump(array('activity_id' => $activity_id));
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
            array('activity_id'=>$activity_id)
            );
        }

        //生成短信队列
        $result = array('res'=>'succ');
        //        print_r($_GET['p'][0]);
        //        exit;
        if(strstr(trim($_POST['send_type']),'sms') && $result['res'] == 'succ' ){
            $smsInfo = $this->getSMSTaskInfo($_GET['p'][0],$msg);
            //            print_r($smsInfo);
            //            exit;
            if(count($smsInfo) == 0){
                $msg = $msg ? $msg : '生成队列失败';
                $result = array('res'=>'fail','msg'=>$msg);
            }
        }

        //        if(strstr($_POST['send_type'],'sms') && $result['res'] == 'succ'){
        //            $nums = $this->checkSendStatus($_GET[p][0]);
        //            if($nums > 0){
        //                $result = array('res'=>'fail','msg'=>'本次营销活动存在之前未发送的客户,请稍后!如有问题,请联系我们的客服!');
        //            }
        //        }
        //获取客户数
        if(strstr($_POST['send_type'],'sms') && $result['res'] == 'succ' ){
            //            $activityMemberQueue = $this->getActivityMemberNums($_GET[p][0]);
            //            $result['data'] = $activityMemberQueue;
            $result['info'] = $smsInfo;
            //              $result['valid_member_count'] = $smsInfo['valid_member_count'];
        }

        //		if($temp){
        //			$result = array_merge($result,$temp);
        //		}
        //        $result = kernel::single('taocrm_middleware_activity')->GetMarketActivityInfo($activity_id);
        echo json_encode($result);
        exit;
    }

    public function activity_ex()
    {
        set_time_limit(0);
        $activity_id = intval($_GET[p][0]);
        $activity_obj = &app::get('market')->model('fx_activity');
        $templates_obj = &app::get('market')->model('sms_templates');
        $filter=array('activity_id' => $activity_id);
        $modiSign = true;

        //上一步:选择模板
        if($_POST['tempup_tag']=='uptag'){
            $mem_filter=$activity_obj->dump($filter,'filter_mem,member_list');
            $aa=unserialize($mem_filter['filter_mem']);
            $src=$activity_obj->update(array('is_active'=>'sel_member'),array('activity_id'=>$activity_id));
            //下一步:发送短信
        }elseif($_POST['exec_tag']=='uptag'){
            $activity_data=$activity_obj->dump($filter,'template_id');
            $src=$activity_obj->update(array('is_active'=>'sel_template'),array('activity_id'=>$activity_id));
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
                $templatesInfo = $templates_obj->dump(array('template_id' => $_GET['p'][1]));
                $templete_title = $templatesInfo['title'];
            }

            $oldActiveInfo = $activity_obj->dump(array('activity_id' => $activity_id));
            if ($_GET['p'][1] != $oldActiveInfo['template_id']) {
                $modiSign = false;
            }

             
            $src=$activity_obj->update(
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
            ),
            array('activity_id'=>$activity_id)
            );
        }

        //生成短信队列
        $result = array('res'=>'succ');
        if(strstr(trim($_POST['send_type']),'sms') && $result['res'] == 'succ' ){
            $smsInfo = $this->getSMSTaskInfo($_GET['p'][0],$msg);
            if(count($smsInfo) == 0){
                $msg = $msg ? $msg : '生成队列失败';
                $result = array('res'=>'fail','msg'=>$msg);
            }
        }


        //获取客户数
        if(strstr($_POST['send_type'],'sms') && $result['res'] == 'succ' ){
            $result['info'] = $smsInfo;
        }

        echo json_encode($result);
        exit;
    }

    //选择短信模板
    function select_template(){
        $template_id=$_GET[p][1];
        $active_obj = &app::get('market')->model('fx_activity');
        $templates_obj = &app::get('market')->model('sms_templates');
        $test = $active_obj->getList("*",array('template_id'=>$template_id),0,-1);
        $content_data=$templates_obj->dump(array('template_id'=>$template_id),"content");
        echo $content_data['content'];
    }

    //编辑后保存模板
    function edit_save(){
        $templates_obj = &app::get('market')->model('sms_templates');
        $templates_obj->update(array('content'=>$_POST[message_text]), array('template_id'=>$_GET[p][1]));
        echo $_POST[message_text];
    }

    function save_template()
    {
        $this->display('admin/activity/sms/template_title.html');
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

    public function getSMSTaskInfoPage(){
        $activity_id = $_GET['p'][0];
        $data = $this->getSMSTaskInfo($activity_id, $msg);
        echo json_encode($data);
        exit;
    }

    public function getSmsActiveStatus(){
        base_kvstore::instance('market')->fetch('account', $account);
        if ($account) {
            $account = unserialize($account);
        }
        return $account;
    }


    //执行活动，发送短信
    public function sms_exec($activity_id=0, $return=0)
    {
        set_time_limit(360);
        $all_send_members = intval($_POST['all_send_members']);
        $result = array('res'=>'fail','msg'=>'未知错误');
        //检查帐号是否存在
        $account = $this->getSmsActiveStatus();
        if (empty($account)) {
            $result = array('res'=>'fail','msg'=>'帐号未绑定');
            echo json_encode($result);
            exit;
        }

        $activityMemberNums = $this->getSmsTaskInfo($activity_id,$msg,'send');
        $is_send_salemember = $_POST['is_send_salemember'];
        $all_send_members = $activityMemberNums['validMembers'];

        $smsInfo=$this->getSmsCount();
		
        if($smsInfo['smscount'] == -1) {
            $result = array('res'=>'fail','msg'=>'您的短信账号出现异常，请检查配置信息');
            echo json_encode($result);
            exit;
        }

        if($smsInfo['overcount'] < $all_send_members) {
            $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
            echo json_encode($result);
            exit;
        }
        
		/*
        //更新客户数量
        $activity_obj = &app::get('market')->model('fx_activity');
        $activity_obj->update(
        array(
                    'total_num'=>$activityMemberNums['totalMembers'],
                    'valid_num'=>$all_send_members,
                    'unvalid_num'=>$activityMemberNums['unvalidMembers'],
                    'is_active'=>'finish'
        ),
        array('activity_id'=>$activity_id)
        );

        $activity_obj = &app::get('market')->model('fx_activity');
        $activity = $activity_obj->dump($activity_id,'shop_id');
        
        $smsObj = app::get('market')->model('fx_sms');
        $sms = array('shop_id'=>$activity['shop_id'],'activity_id'=>$activity_id,'total_num'=>$all_send_members,'send_status'=>'sending');
        $sms_id = $smsObj->addSms($sms);
        $jobarray = array('activity_id'=>$activity_id,'sms_id'=>$sms_id);
        if(kernel::single('taocrm_service_queue')->addJob('market_backstage_fxsms@fetch',$jobarray)){
            $result = array('res'=>'succ','msg'=>'发送成功');
        }else{
            $result = array('res'=>'fail','msg'=>'发送失败');
        }
        echo json_encode($result);
        exit;
        */
        
        //保存短信帐号信息    
        $active_remark = array(
            'shopName'=>$_POST['shopName'],
            'entId'=>$smsInfo['entId'],
            'entPwd'=>$smsInfo['entPwd'],
            'license'=>$smsInfo['license'],
            'taskId'=>'activity'.$activity_id,
            'is_send_salemember' => $_POST['is_send_salemember']
        );
        $active_obj = &app::get('market')->model('fx_activity');
        $active_obj->update(
            array(
                'active_remark'=>json_encode($active_remark),
                'ip'=>$_SERVER['REMOTE_ADDR'],
                'op_user'=>kernel::single('desktop_user')->get_name(),
            ),
            array('activity_id'=>$activity_id)
        );
        $result = array('res'=>'fail','msg'=>json_encode($smsInfo));
        
        $res = kernel::single('taocrm_middleware_activity')->FxExecMarketActivity($activity_id);
        $result = $res;
        if($return == 0){
            echo json_encode($result);
        }else{
            return $result;
        }
        die();
    }

    public function getSmsCount(){
        $activity_id=$_GET[p][0];
        $send=kernel::single('market_service_smsinterface');
        $memsms=kernel::single('market_service_sms');
        $send_info = $send->get_usersms_info();//get_usersms_info

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

    public function sms()
    {
        $title = '发送记录列表';
        $baseFilter = array();
        $this->finder('market_mdl_fx_sms',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        	'actions'=>array(

        ),
        //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
        ));
    }

    //作废活动
    function invalid_active($activity_id)
    {
        $this->pagedata['activity_id']=$activity_id;
        $this->page('admin/activity/invalid.html');
    }

    function invalid(){
        $this->begin('index.php?app=market&ctl=admin_fx_activity&act=index');
        if($_POST['invalid_name']=='on'){
            $active_obj = &app::get('market')->model('fx_activity');
            $rec=$active_obj->update(array('is_active'=>'dead'),array('activity_id'=>$_POST['activity_id_name']));
            $this->end();
        }else {
            $this->end();
        }
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
    

}