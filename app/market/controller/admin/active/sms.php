<?php

class market_ctl_admin_active_sms extends market_ctl_admin_active_abstract{

	var $pagelimit = 10;
	//    var $is_debug = true;
	var $is_debug = false;
	public static $middleware_connect = null;

	public function index()
	{
		$shopObj = app::get(ORDER_APP)->model('shop');
		$shopList = $shopObj->get_shops('no_fx');
		foreach((array)$shopList as $v){
			$shops[]=$v['shop_id'];
		}
		if($_GET['view']){
			$view=($_GET['view']-1);
			$shop_id=$shops[$view];
		}
		$param=array(
            'title'=>'短信营销活动',
            'use_buildin_recycle' => false,
            'use_buildin_filter' => true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "if(exec_time is null ,create_time ,exec_time) desc",
            'base_filter'=>array('is_active'=>array('sel_member','sel_template','wait_exec','execute','finish'),'type|nohas'=>'edm', 'pay_type|nohas' => 'market'),
            'actions'=>array(
                array(
                    'label'=>'创建营销活动',
                    'href'=>'index.php?app=market&ctl=admin_active_sms&act=create_active&send_method=sms&shop_id='.$shop_id,
                    'target'=>'dialog::{onClose:function(){window.location.reload();},width:650,height:355,title:\'创建活动\'}'
                ),
            ),
        );
        $this->finder('market_mdl_active',$param);
	}

	function _views()
	{
		$memberObj = app::get('market')->model('active');
		$base_filter=array('is_active'=>array('sel_member','sel_template','wait_exec','execute','finish'),'type|nohas'=>'edm','is_active|nohas'=>'dead');
		$sub_menu[] = array(
            'label'=> '全部',
            'filter'=> $base_filter,
            'optional'=>false,
            'display'=>true,
		);
		$shopObj = app::get(ORDER_APP)->model('shop');
		$shopList = $shopObj->get_shops('no_fx');
		foreach($shopList as $shop){
			$sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,
                'display'=>true,
			);
		}
		$i=0;
		foreach($sub_menu as $k=>$v){
			if (!empty($v['filter'])){
				$v['filter'] = array_merge($v['filter'],$base_filter);
			}
			//$count =$memberObj->count($v['filter']);
			$count = 0;
			$sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
			$sub_menu[$k]['addon'] = $count;
			$sub_menu[$k]['href'] = 'index.php?app=market&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
		}
		return $sub_menu;
	}

	//新增营销活动
	function toAdd_new()
	{
		$shop_id = $_POST['shop_id'];

		$active_obj = app::get('market')->model('active');
		if ( ! $_GET[p]['active_id']){
			$rs = $active_obj->dump(array('active_name'=>$_POST['active_name'],'is_active|noequal'=>'dead'));
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
		}else if($filter_flag == 2){
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
		if (!empty($_GET[p]['member_list']) || $_POST['userslist']!='' || $_POST['report_filter']!=''){
			$_POST['is_active']='sel_template';
		}

		//客户营销模型
		if($_POST['sale_model_id']){
			$id_arr = explode('_',$_POST['sale_model_id']);
			$all_model = kernel::single('taocrm_ctl_admin_sale_model')->get_model();
			$model = $all_model[$id_arr[0]][$id_arr[1]];
			$_POST['filter_mem']['filter'] = $model['filter_mem'];
			$_POST['filter_mem']['shop_id'] = $shop_id;
		}

        $_POST['ceeate_source'] == 'tags' && $_POST['filter_mem'] = serialize(array('filter' => array('tags'=>$_POST['tags_ids']))); //标签id
        //数据过滤
        foreach($_POST as $k => $p_data)
        {
            if(empty($p_data))unset($_POST[$k]);
        }
		if (!empty($_GET[p]['active_id'])){
			$active_obj->update($_POST,array('active_id'=>$_GET[p]['active_id']));
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
        $as_array=array('active_id'=>$active_id);
        if($shop_id)
        {
            $as_array['shop_id']=$shop_id;

            $rs = app::get('ecorder')->model('shop_lv')->getList('lv_id,name',array('shop_id'=>$shop_id));
		if($rs) {
			$levels[] = '';
			foreach($rs as $v){
				$levels[$v['lv_id']] = $v['name'];
			}
		}
		$as_array['levels'] = $levels;
        }

		//店铺短信签名
		$as_array['sms_sign'] = app::get('ecorder')->model('shop')->get_sms_sign($shop_id);

        if($shop_id)
        {
		//淘名片短地址
		$vcard_url = '';
            $shopVcardObj = app::get(ORDER_APP)->model('shop_vcard');
		$rs = $shopVcardObj->dump(array('shop_id'=>$shop_id),'vcard_url');
		if($rs){
			$vcard_url = $rs['vcard_url'];
		}
		$as_array['vcard_url'] = $vcard_url;
        }

        //自定义属性
        $attribute = $this->get_user_attribute($shop_id);
        $as_array['prop_name'] = (array)$attribute['prop_name'];
        $as_array['prop_type'] = (array)$attribute['prop_type'];
        $as_array['attribute'] = $this->get_user_attribute($shop_id);

        if($_POST['tags_ids'])
        {
            //标签
            $tag_mod = app::get('taocrm')->model('member_tag');
            $as_array['tags'] = $tag_mod->getList('tag_id,tag_name',array('tag_id'=>explode(',',$_POST['tags_ids'])));
            $as_array['tags_ids'] = $_POST['tags_ids'];
        }

		$result = array('res'=>'succ','data'=>$as_array);
		echo(json_encode($result));
	}

    function get_user_attribute($shop_id)
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

        $shop = $oShop->dump($shop_id);
        $shop_config = unserialize($shop['config']);
        if($shop_config['prop_name']) $attr['prop_name'] = array_filter($shop_config['prop_name']);
        if($shop_config['prop_type']) $attr['prop_type'] = array_filter($shop_config['prop_type']);
        return $attr;
    }
    
	//创建活动
	function create_active()
	{
        $create_source = $_GET['create_source'];
        
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

		//贵宾组客户
		if($_GET['resource'] == 'vip'){
				$memberObj = app::get('taocrm')->model('member_analysis');
            $post_ids = $_POST['id'];//id已经修改为会员ID
            if ($post_ids) {//部分勾选的VIP
                /*
                $rs = $memberObj->getList('member_id',array('id'=>$post_ids));
                foreach($rs as $v){
                    $member_ids[] = $v['member_id'];
                }
                */
                $member_ids = $post_ids;
            }else{//全部VIP
                $rs = $memberObj->getList('member_id',array('shop_id'=>trim($_GET['shop_id']),'is_vip'=>'true'));
                foreach($rs as $v){
                    $member_ids[] = $v['member_id'];
                }
				}
            
            if($member_ids){
                $this->pagedata['userslist'] = implode(',', $member_ids);
                $this->pagedata['create_source'] = 'members';
			}
		}

		if($_GET['filter_from']=='report'){
			$report_filter = json_encode($_GET);
			$this->pagedata['report_filter'] = $report_filter;//店铺信息
			$this->pagedata['filter_sql'] = 1;
		}

		if($_GET['filter_from']=='market'){
			$report_filter = json_encode($_GET);
			$this->pagedata['report_filter'] = $report_filter;//店铺信息
			$this->pagedata['filter_sql'] = 2;
			$sql = kernel::single('plugins_market')->getMarketSql($_GET['market_id'],$_GET['shop_id']);
			$user_id = kernel::single('desktop_user')->get_id();
			base_kvstore::instance('analysis')->store('filter_sql_market_'.$user_id,$sql);
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

		//取出自定义分组的客户
		if ($group_id > 0){
			$member_analysisObj = app::get('taocrm')->model('member_group');
			$member_group=$member_analysisObj->dump($group_id);
			$this->pagedata['userslist'] = 'group_id:'.$group_id;
			$this->pagedata['active'] = array('active_name'=>$member_group['group_name'].'_'.date('Ymd'));
		}

		//从客户列表中点过去
        if ($create_source == 'members'){
			if ($_GET['memlist'] == 1 && $_GET['resource'] != 'vip') {
				$mem = $_POST['id'];
            }else{
                $memberobj = app::get('taocrm')->model('member_analysis');
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
			$this->pagedata['userslist'] = implode("," , $mem);
			$this->pagedata['create_source']='members';
		}
        
		//从标签点击
        if ($_GET['create_source']=='tags'){
            if($_POST['tag_id']){
                $this->pagedata['tags_ids'] = implode(',',$_POST['tag_id']);
            }else{
                $this->pagedata['tags_ids'] = $_GET['tag_id'];
            }
			$this->pagedata['create_source']='tags';
		}
        
		$mgroup_data = app::get('taocrm')->model('member_group');

		$shop_id_data=$shopObj->dump(array('active_id'=>$_GET[p][0]));
		$this->pagedata['data']=$shop_id_data;
		$templates_obj = app::get('market')->model('sms_templates');
		$templates_data=$templates_obj->getList("*",array('status'=>1),0,15,'template_id DESC');
		$this->pagedata['templates_data']=$templates_data;//短信模板
		$edm_templates_obj = app::get('market')->model('edm_templates');
		$edm_templates_data=$edm_templates_obj->getList("*",array('status'=>1));

		$this->pagedata['edm_templates_data'] = $edm_templates_data;//EDM模板

		$this->pagedata['open_compare'] = 'no';//开启对照组
		$shopList = $shopObj->get_shops('no_fx');
        
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
		$oneactive_data['type'] = array($send_method);

        $plan_send_time = strtotime(date('Y-m-d', strtotime('+1 day')).' 10:35:00');
        $oneactive_data['plan_send_time'] = array();
        $oneactive_data['plan_send_time']['date'] = date('Y-m-d', $plan_send_time);
        $oneactive_data['plan_send_time']['hour'] = date('H', $plan_send_time);
        $oneactive_data['plan_send_time']['min'] = ceil(date('i', $plan_send_time)/5)*5;
        if($oneactive_data['plan_send_time']['min']==60){
            $oneactive_data['plan_send_time']['min'] = 0;
            $oneactive_data['plan_send_time']['hour']++;
        }

        empty($_GET['create_source']) && $this->pagedata['create_source']='normal';
        $this->pagedata['active']=$oneactive_data;
		$this->pagedata['CacheId'] = trim($_GET['CacheId']);
		$this->pagedata['CacheIdCreateTime'] = trim($_GET['CacheIdCreateTime']);
		$this->pagedata['beigin_time'] = date("Y-m-d",time());
		$this->pagedata['end_time'] = date('Y-m-d',strtotime('+15 days'));
		$this->pagedata['actity_type'] = json_encode(array());

		//检测是否需要设置签名
		$need_sign = 'none';
        
        //短信可选签名
        $sign_list = $this->get_sign_list();

        $this->pagedata['sign_list'] = $sign_list;
		$this->pagedata['need_sign'] = $need_sign;
        $this->pagedata['exclude_hours'] = 0;
		$this->display('admin/active/sms/create_active_new.html');
	}

    //获取可用的签名
    public function get_sign_list()
    {
        $oShop = app::get('ecorder')->model("shop");
        $sign_list = $oShop->get_sms_sign_list();
        return $sign_list;
    }

	protected function geteEditerMemberCount($active_id)
	{
		return $this->getSMSTaskInfo($active_id,$msg,'edit');
	}

	//编辑活动内容
	function editer_data()
	{
		set_time_limit(360);
		$active_id=$_GET['p'][0];
		$active_obj = app::get('market')->model('active');
		$oneactive_data=$active_obj->dump(array('active_id'=>$active_id));
		if(trim($_GET[p]['selectmember'])=='selecemember' && $oneactive_data['create_source'] != 'tags'){
            //清空数据库客户的条件
            $active_obj->update(
                array('filter_mem'=>""),
                array('active_id'=>$_GET['p'][0])
            );
		}

        //从报表创建的活动
		if($oneactive_data['cache_id']){
			$result = kernel::single('taocrm_middleware_activity')->getCacheInfo($oneactive_data['cache_id']);
			if(!$result){
				$this->display('admin/active/notice.html');
				die();
			}
		}

		//营销活动对应的客户数量
		$type_array = unserialize($oneactive_data['type']);

		//获取短信的客户信息
		$send_method = 'sms';
		//$activityMemberNums = $this->getActivityMemberNums($active_id);
		$activityMemberNums = $this->geteEditerMemberCount($active_id,$rmsg,'edit');


		$this->pagedata['activityMemberNums'] = $activityMemberNums;

        //是否绑定优惠券
		if (!empty($oneactive_data['coupon_id'])){
			$this->pagedata['coupons_tag']=true;
			$this->pagedata['coupons']=$oneactive_data['coupon_id'];
			$couponsobj=app::get('market')->model('coupons');//优惠券
			$couponslist=$couponsobj->dump(array('coupon_id'=>$oneactive_data['coupon_id']),"coupon_id,coupon_name");
			$this->pagedata['couponslist']=$couponslist;
		}
        
        //当前店铺对应的会员等级
		$shopid=$oneactive_data['shop_id'];
		$rs = app::get('ecorder')->model('shop_lv')->getList('lv_id,name',array('shop_id'=>$shopid));
		if($rs) {
			foreach($rs as $v){
				$levels[$v['lv_id']] = $v['name'];
			}
		}
		$this->pagedata['levels'] = $levels;
        
        $shopObj = app::get('ecorder')->model('shop');
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
        
        //店铺列表
		$shopdata=$shopObj->getList("*");
		$this->pagedata['shopList']=$shopdata;

        //$active_data=$active_obj->getList("*");

        //短信模板
		if($send_method == 'sms'){
			$templates_obj = app::get('market')->model('sms_templates');
            //$this->pagedata['reg_data']=$reg_data;
            $templates_data=$templates_obj->getList("*",array('status'=>1),0,15,'template_id DESC');
			if($templates_data){
				foreach($templates_data as $v) {
					if($v['template_id'] == $oneactive_data['template_id'])
					$oneactive_data['template_name'] = $v['title'];
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

		//淘名片短地址
		$vcard_url = '';
        $shopVcardObj = app::get('ecorder')->model('shop_vcard');
		$rs = $shopVcardObj->dump(array('shop_id'=>$shopid),'vcard_url');
        if($rs) $vcard_url = $rs['vcard_url'];
		$this->pagedata['vcard_url'] = $vcard_url;

        //预约发送时间
        $plan_send_time = $oneactive_data['plan_send_time'];
        if(!$plan_send_time){
            $plan_send_time = strtotime(date('Y-m-d', strtotime('+1 day')).' 10:35:00');
        }
        $oneactive_data['plan_send_time'] = array();
        $oneactive_data['plan_send_time']['date'] = date('Y-m-d', $plan_send_time);
        $oneactive_data['plan_send_time']['hour'] = date('H', $plan_send_time);
        $oneactive_data['plan_send_time']['min'] = ceil(date('i', $plan_send_time)/5)*5;
        if($oneactive_data['plan_send_time']['min']==60){
            $oneactive_data['plan_send_time']['min'] = 0;
            $oneactive_data['plan_send_time']['hour']++;
        }

		//短信模版B信息
		$this->templateTplB($oneactive_data);
		$this->pagedata['send_method']=$send_method;
		$this->pagedata['actity_type'] = $actity_type;
		$this->pagedata['active']=$oneactive_data;
        //$this->pagedata['active_data']=$active_data;
		$this->pagedata['templates_data']=$templates_data;
		$this->pagedata['edm_templates_data']=$edm_templates_data;
		$this->pagedata['open_compare'] = $oneactive_data['control_group'];//开启对照组
		$this->pagedata['unsubscribe'] = $oneactive_data['unsubscribe'];//开启退订
		$this->pagedata['create_source'] = $oneactive_data['create_source'];//创建来源
		$this->pagedata["tag"]= $_GET['p'][1];
		$this->_init_config_arr();//初始化表单项目

		//检测是否需要设置签名
		$need_sign = 'none';

        if($shopid){
            //店铺自定义属性
            $attribute = $this->get_user_attribute($shopid);

            $this->pagedata['prop_name'] = $attribute['prop_name'];
            $this->pagedata['prop_type'] = $attribute['prop_type'];
        }
        
        if($oneactive_data['create_source']=='tags'){
            //标签
            $tag_mod = app::get('taocrm')->model('member_tag');
            $tags_list = $tag_mod->getList('tag_id,tag_name',array('tag_id'=>explode(',',$filter_mem['filter']['tags'])));
            $this->pagedata['tags_list'] = $tags_list;
        }
        
        //短信可选签名
        $sign_list = $this->get_sign_list();
        
        //解析过滤条件
        $exclude_hours = 0;
        if($oneactive_data['filter_mem']){
            $filter_mem = unserialize($oneactive_data['filter_mem']);
            $exclude_filter = $filter_mem['exclude_filter'];
            
            if(in_array(2, $exclude_filter)){
                $exclude_hours = intval($filter_mem['exclude_hours']);
            }
        }
        
        $this->pagedata['exclude_hours'] = $exclude_hours;
        $this->pagedata['sign_list'] = $sign_list;
		$this->pagedata['need_sign'] = $need_sign;
		$this->pagedata['sms_sign'] = $shopObj->get_sms_sign($shopid);

		$this->display('admin/active/sms/create_active_new.html');
	}

	private function templateTplB($data)
	{
		$this->pagedata['template_id_b'] = $data['template_id_b'];
		$this->pagedata['data']['content_b'] = $data['templete_b'];
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
	public function active_ex()
	{
        set_time_limit(360);
		$active_id = intval($_GET[p][0]);
		$active_obj = app::get('market')->model('active');
		$templates_obj = app::get('market')->model('sms_templates');
		$edm_templates_obj = app::get('market')->model('edm_templates');
		$filter=array('active_id' => $active_id);
		$modiSign = true;

		if($_POST['tempup_tag']=='uptag'){
            //上一步:选择模板
			$mem_filter=$active_obj->dump($filter,'filter_mem,member_list');
			$aa=unserialize($mem_filter['filter_mem']);
			$src=$active_obj->update(array('is_active'=>'sel_member'),array('active_id'=>$active_id));
		}elseif($_POST['exec_tag']=='uptag'){
            //下一步:发送短信
			$active_data=$active_obj->dump($filter,'template_id');
			$src=$active_obj->update(array('is_active'=>'sel_template'),array('active_id'=>$active_id));
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
			$templete_b = urldecode($_POST['templete_b']);
			$open_compare = $_POST['open_compare'];
			$unsubscribe = intval($_POST['unsubscribe']);
			if($open_compare != 'yes') $open_compare = 'no';
            
            //短信内容过滤换行符
            $templete = str_replace("\n",'',$templete);
            $templete_b = str_replace("\n",'',$templete_b);

			if (isset($_GET['p'][1]) && $_GET['p'][1]) {
				if($_POST['send_type'] == 'edm'){
					$templete_title = urldecode($_POST['templete_title']);
				}else{
					$templatesInfo = $templates_obj->dump(array('template_id' => $_GET['p'][1]));
					$templete_title = $templatesInfo['title'];
				}
			}

			$oldActiveInfo = $active_obj->dump(array('active_id' => $active_id));
			if ($_GET['p'][1] != $oldActiveInfo['template_id']) {
				$modiSign = false;
			}

            //解析过滤条件
            $exclude_hours = 0;
            if($oldActiveInfo['filter_mem']){
                $filter_mem = unserialize($oldActiveInfo['filter_mem']);
                $exclude_filter = $filter_mem['exclude_filter'];
                
                if(in_array(2, $exclude_filter)){
                    $exclude_hours = intval($filter_mem['exclude_hours']);
                }
            }

			$templete_title_b = '';
			$template_id_b = '';
			if (isset($_GET['pb'][1]) && $_GET['pb'][1]) {
				$templatesInfoB = $templates_obj->dump(array('template_id' => $_GET['pb'][1]));
				$templete_title_b = $templatesInfoB['title'];
				$template_id_b = $templatesInfoB['template_id'];
				if ($_GET['pb'][1] != $oldActiveInfo['template_id_b']) {
					$modiSign = false;
				}
			}

			//更新营销活动
            $update_params = array(
                    'templete_title'=>$templete_title,
                    'templete'=>str_replace(array('&lt;','&gt;'),array('<','>'),$templete),
                    'template_id'=>$_GET[p][1],
                    'type'=>serialize($sentType),
                    'coupon_id'=>$_POST['coupon_id'],
                    'is_active'=>'wait_exec',
                    'sent_time'=>$time_str,
                    'control_group'=>$open_compare,
                    'unsubscribe'=>$unsubscribe,
                    'templete_title_b' => $templete_title_b,
                    'templete_b' => str_replace(array('&lt;','&gt;'),array('<','>'),$templete_b),
                    'template_id_b' => $template_id_b
                );
            $src=$active_obj->update(
                $update_params,
			array('active_id'=>$active_id)
			);
		}

		//生成短信队列
        $result = array(
            'res' => 'succ',
            'exclude_hours' => $exclude_hours
        );
        
		if(strstr(trim($_POST['send_type']),'sms') && $result['res'] == 'succ' ){
			$smsInfo = $this->getSMSTaskInfo($_GET['p'][0],$msg);
            if(count($smsInfo)==0 or !isset($smsInfo['totalMembers'])){
                $msg = $msg ? $msg : '获取目标客户数失败';
				$result = array('res'=>'fail','msg'=>$msg);
			}
		}
        
		//获取客户数
		if(strstr($_POST['send_type'],'sms') && $result['res'] == 'succ' ){
			$result['info'] = $smsInfo;
		}
        
		//创建数据源
        $result['info']['create_source'] = $oldActiveInfo['create_source'];
		echo json_encode($result);
		exit;
	}

	public function getSMSTaskInfoPage()
	{
		$active_id = $_GET['p'][0];
		$data = $this->getSMSTaskInfo($active_id, $msg);
		echo json_encode($data);
		exit;
	}

	/**
	 * 创建短信活动 人数预览
	 */
	protected function getSMSTaskInfo($active_id,&$msg, $resource = 'create')
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
		if ($result) {
            //err_log($result);
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
			if ($personAB && $messageAB) {
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
                    'totalMembers' => $count,
                    'sentMembers' => $reSend,
                    'unvalidMembers' => $unSend,
				//'ReSend' => $reSend,
                    'validMembers' => $valiNum,
                    'controlGroupMembers' => $activeContrast,
                    'validbMembers' => $smsB,
                    'WaitSendMember' => $waitSendMember,
                    'SmsA' => $smsA,
                    'SmsB' => $smsB,
                    'template_id_b' => $activity['template_id_b'],
                    'template_id_b' => $activity['template_id_b'],
                    'personAB' => $personAB,
                    'messageAB' => $messageAB,
				//'activeContrast' => $activeContrast,
				//'smsA' => $smsA,
				//'smsB' => $smsB
				);
            }elseif($resource == 'edit'){
				//$result = array('unvalid_member_count'=>0,'valid_member_count'=>0,'total_member_count'=>0,'sent_member_count'=>0);
				$activeCount = array(
                    'total_member_count' => $count,
                    'totalMembers' => $count,
                    'sentMembers' => $reSend,
                    'sent_member_count' => $reSend,
                    'unvalid_member_count' => $unSend,
                    'valid_member_count' => $valiNum,
                    'controlGroupMembers' => $activeContrast,
                    'validbMembers' => $smsB,
                    'WaitSendMember' => $waitSendMember,
                    'SmsA' => $smsA,
                    'SmsB' => $smsB,
                    'template_id_b' => $activity['template_id_b'],
                    'personAB' => $personAB,
                    'messageAB' => $messageAB,
				//'activeContrast' => $activeContrast,
				//'smsA' => $smsA,
				//'smsB' => $smsB
				);
			}
			elseif ($resource == 'send') {
				$activeCount = array(
                    'count' => $count,
                    'totalMembers' => $count,
                    'send' => $valiNum,
                    'unSend' => $unSend,
                    'reSend' => $send,
                    'valiNum' => $valiNum,
                    'activeContrast' => $activeContrast,
                    'smsA' => $smsA,
                    'smsB' => $smsB,
                    'unMarking' => $unMarking,
                    'WaitSendMember' => $waitSendMember,
                    'SmsA' => $smsA,
                    'SmsB' => $smsB,
                    'controlGroupMembers' => $activeContrast,
                    'template_id_b' => $activity['template_id_b'],
                    'personAB' => $personAB,
                    'messageAB' => $messageAB,
				);
			}
		}
		//        print_r($activeCount);

        //接口错误消息
        if(isset($result['err_msg'])){
            $msg = $result['err_msg'];
        }

		$activeCount['VoidId'] = $result['VoidId'];
		return $activeCount;
	}

	/**
	 * 活动预估人数
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

		echo $result['Count'] ? $result['Count'] : 0;
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

	public function testsms()
	{
		$active_id = 122;
		$msgid='1358859800_286';
		$jobarray = array(
            'active_id'=>$active_id,
            'msgid' => $msgid
		);
		$sms = kernel::single('market_backstage_activity');
		//$sms->sendSms($active_id, $msgid);
		$sms->fetch($jobarray);
	}

	public function getSmsActiveStatus()
	{
		base_kvstore::instance('market')->fetch('account', $account);
		if ($account) {
			$account = unserialize($account);
		}
		return $account;
	}

	//执行活动，发送短信
	public function sms_exec($active_id=0, $return=0)
	{
        if(intval($_POST['page']) == 999){
            //余额不足的时候强制发送
            $check_sms_account = 0;
        }else{
            $check_sms_account = 1;
        }

        $is_timing = intval($_POST['is_timing']);
        $plan_send_time = strtotime(trim($_POST['plan_send_time']));
        if($is_timing==1 && ($plan_send_time-time()<1800)){
            $result = array('res'=>'fail','msg'=>'定时发送时间距现在不足30分钟，请选择立即发送！');
			echo json_encode($result);
			exit;
        }

        $active_obj = app::get('market')->model('active');
        $rs_active = $active_obj->dump($active_id);
        
        //活动从定时修改为实时，先删除活动
        if($is_timing==0 && intval($rs_active['is_timing'])==1){
            kernel::single('taocrm_middleware_activity')->delete_active($active_id);
        }
        
        $save_active = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'op_user' => kernel::single('desktop_user')->get_name(),
            'is_timing' => $is_timing
        );
        $plan_send_time = trim($_POST['plan_send_time']);
        if($plan_send_time){
            $save_active['plan_send_time'] = strtotime($plan_send_time);
        }
        $active_obj->update($save_active, array('active_id'=>$active_id));
        

		set_time_limit(360);
		//$all_send_members = intval($_POST['all_send_members']);
		$result = array('res'=>'fail','msg'=>'未知错误');
		//检查帐号是否存在
		$account = $this->getSmsActiveStatus();
        if ($check_sms_account==1 && empty($account)) {
			$result = array('res'=>'fail','msg'=>'帐号未绑定');
			echo json_encode($result);
			exit;
		}

		$activityMemberNums = $this->getSmsTaskInfo($active_id,$msg,'send');
		$is_send_salemember = $_POST['is_send_salemember'];
        if ($is_send_salemember == 'Y') {
			$all_send_members = $activityMemberNums['valiNum'];
		}
		else {
			$all_send_members = $activityMemberNums['unMarking'];
		}

		$smsInfo=$this->getSmsCount();
        if($check_sms_account==1 && $smsInfo['smscount'] == -1) {
			$result = array('res'=>'fail','msg'=>'您的短信账号出现异常，请检查配置信息');
			echo json_encode($result);
			exit;
		}

        if($check_sms_account==1 && ($smsInfo['overcount'] < $all_send_members)) {
			$result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
			echo json_encode($result);
			exit;
		}

        if($check_sms_account==0) {
            $smsInfo = kernel::single('market_service_smsinterface')->get_sms_account();
        }

        //设置店铺的最后营销时间
        $shop_obj = app::get('ecorder')->model('shop');
        $shop_obj->set_last_market_time($rs_active['shop_id']);

		//保存短信帐号信息
		$active_remark = array(
            'shopName'=>$_POST['shopName'],
            'entId'=>$smsInfo['entId'],
            'entPwd'=>$smsInfo['entPwd'],
            'license'=>$smsInfo['license'],
            'taskId'=>'activity'.$active_id,
            'is_send_salemember' => $_POST['is_send_salemember']
		);

		$active_obj->update(
            array('active_remark'=>json_encode($active_remark)),
            array('active_id'=>$active_id)
        );
		$result = array('res'=>'fail','msg'=>json_encode($smsInfo));

		$res = kernel::single('taocrm_middleware_activity')->ExecMarketActivity($active_id);
		$result = $res;
		if($return == 0){
			echo json_encode($result);
		}else{
			return $result;
		}
		die();
	}

	function sendActivityQueue($active_id,$type,$msgid)
	{
		$jobarray = array(
            'active_id'=>$active_id,
            'msgid'=>$msgid,
		);
		if(kernel::single('taocrm_service_queue')->addJob('market_backstage_activity@fetch',$jobarray)){
			$this->sendSmsQueueSucc($active_id);
			return true;
		}else{
			return false;
		}
	}

	function checkSms($active_id,$msgid)
	{
		//return array('res'=>'succ');
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
		if ($tag['control_group']=='yes')
		$memberNums = ceil($memberNums/2);

		//营销超市
		if($tag['pay_type'] == 'market'){
			if($smsInfo['overcount'] >= $memberNums){
				//$this->sendSmsQueue($tag['active_id'],$sms_config,$msgid);
				//$this->sendSmsQueueSucc($tag['active_id']);
			}else{
				//如果发送短信失败，则删除营销超市创建的活动
				$active_obj->delete($filter);
				$result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
			}
		}elseif($tag['pay_type'] == 'pay'){//按效果付费用户
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

	function sendSmsQueueSucc($active_id)
	{
		$active_obj = app::get('market')->model('active');
		$active_obj->update(
		array('exec_time'=>time(),'is_active'=>'finish'),
		array('active_id'=>$active_id)
		);
	}

	//获取营销活动的客户数
	function getActivityMemberNums($active_id)
	{
		$db = kernel::database();
		$result = array('unvalid_member_count'=>0,'valid_member_count'=>0,'total_member_count'=>0,'sent_member_count'=>0);
		$row_valid = $db->selectrow('select count(*) as total from sdb_market_activity_m_queue where active_id='.$active_id .' and is_send = 1');
		$row_unvalid = $db->selectrow('select count(*) as total from sdb_market_activity_m_queue where active_id='.$active_id .' and is_send = 0');
		$result['unvalid_member_count'] = intval($row_unvalid['total']);
		$result['valid_member_count'] = intval($row_valid['total']);
		$result['total_member_count'] = $result['unvalid_member_count'] + $result['valid_member_count'];

		$e_time = time();
		$s_time = strtotime(date('Y-m-d 00:00:00'));

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



	function freezSms($block_num,$msgid)
	{
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

	function sms_send()
	{
		$active_obj = app::get('market')->model('active');
		$active_id=$_GET[p][0];
		$active_data=$active_obj->dump(array('active_id'=>$active_id),"*");
		echo(unserialize($active_data['filter_mem']));
	}

	function save_template()
	{
		$this->display('admin/active/sms/template_title.html');
	}

	function save_template_b()
	{
		$this->display('admin/active/sms/template_title_b.html');
	}

	function add_template()
	{
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
		echo json_encode($data);
	}

	function add_template_b()
	{
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

	public function getSmsCount()
	{
		$active_id=$_GET[p][0];
		$active_obj = app::get('market')->model('active');
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

	function processMember($active_id,&$msg, $modiSign = true)
	{
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
			}
		}

		//无效1：删除空号码队列
		$db->exec('update sdb_market_activity_m_queue set is_send=0 where active_id='.$active_id.' and mobile =""');

		//无效2：黑名单客户不发送
		$db->exec('update sdb_market_activity_m_queue as a
        inner join  sdb_taocrm_members  as b on a.member_id=b.member_id
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

	// 创建队列判断当前活动队列和之前未发送队列有没有冲突（模板id,member_id）防止发送重复
	function checkSendStatus($active_id)
	{
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

	//发送短信前过滤掉24小时之内已营销的短信
	function filterSentMember($active_id)
	{
		$e_time = time();
		$s_time = strtotime(date('Y-m-d 00:00:00'));
		$page = 0;
		$page_size = 1000;
		$db = kernel::database();
		while(true){
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
		}
	}

	//快速创建活动
	public function one_page()
	{
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
		$this->display('admin/active/sms/one_page.html');
	}

	public function onepage_count()
	{
		$db = kernel::database();

		$market_id = $_GET['market_id'];
		$sql = kernel::single('plugins_market')->getMarketSql($market_id,$_GET['shop_id']);
		$sms_body = kernel::single('plugins_market')->getMarketSmsBody($market_id);
		$user_id = kernel::single('desktop_user')->get_id();
		base_kvstore::instance('analysis')->store('filter_sql_market_'.$user_id,$sql);

		$sql_tmp = "select count(*) as total from ({$sql}) as a";
		$total_member = $db->selectrow($sql_tmp);

		$sql_tmp = "select count(distinct a.mobile) as total from sdb_taocrm_members as a
        inner join ({$sql}) as b on a.member_id=b.member_id
        where a.sms_blacklist='false' and a.mobile<>''";
		$valid_member = $db->selectrow($sql_tmp);

		$e_time = time();
		$s_time = $e_time-86400;
		$sql_tmp = "select count(distinct member_id) as total from sdb_market_activity_m_queue
        where is_send_finish=1 and sent_time>={$s_time} and sent_time<={$e_time}";
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
	public function onepage_run()
	{
		set_time_limit(60*30);

		$data = array('res'=>'succ','msg'=>'成功');
		$active_id = intval($_POST['active_id']);
		$market_id = intval($_POST['market_id']);
		$shop_id = $_POST['shop_id'];
		$active_name = $_POST['active_name'];
        $is_send_salemember = trim($_POST['is_send_salemember']);
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
        if($is_send_salemember == 'N'){
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


	public function selectInvalidMember(){
		$title = '无效手机客户列表';

		$actions = '';
		$baseFilter = array();
		$baseFilter['VoidId'] = $_GET['VoidId'];
		$baseFilter['shop_id'] = $_GET['shopId'];

        empty($_GET['shopId']) && $_GET['no_shopid'] = true;
		$baseFilter['methodName'] = 'SearchInvalidMemberAnalysisByActivity';
		$baseFilter['packetName'] = 'ShopMemberAnalysis';
		$actions = array();
		$this->finder('taocrm_mdl_middleware_member_analysis',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
		//去掉默认排序
        	'orderBy' => '',
		//'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
		//暂时去掉高级筛选功能
		//'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
		));
    }

    function testSendSms(){
        $modelSmsTemplated = app::get('market')->model('sms_templates');
        $template = $modelSmsTemplated->dump($_GET['template_id']);
        $modelShop = app::get('ecorder')->model('shop');

        $shop = $modelShop->dump($_GET['shop_id']);
        
        //积分兑换完整地址
        $url = kernel::base_url(1);
        $url = $url . '/index.php/taocrm/default/index/app/site';
        
        //将积分兑换地址变为短地址
        //$SinaObj = kernel::single('market_shorturl');
        //$shorturl = $SinaObj->shortenSinaUrl($url);
        //$shorturl = 'www';

		$uname = '张三';
		$nickname = 'Z小宝';
		$shopname = $shop['name'];
		$pointurl = $shorturl;
		$pointurl = '';
		$template['content'] = str_replace(array('<{姓名}>','<{昵称}>','<{店铺}>','<{积分兑换}>'), array($uname,$nickname,$shopname,$pointurl), $template['content']);
        
        //短信可选签名
        $sign_list = $this->get_sign_list();

        $this->pagedata['sign_list'] = $sign_list;

		$this->pagedata['sendContent'] = $template['content'];
		$this->display('admin/active/sms/test_send_sms.html');
	}

    //执行发条试试，发送测试短信
	function sendTestSms()
    {
		//$this->begin();
		$smssendobj= kernel::single('market_service_smsinterface');
		$sms_list = array();
		$data = $_POST;
		if(empty($data['phones'])){
			//$this->end(false,'请填写手机号码');
            echo(json_encode(array('res'=>'error','msg'=>'请填写手机号码')));
            die();
		}

		if(empty($data['content'])){
			//$this->end(false,'请填写发送内容');
            echo(json_encode(array('res'=>'error','msg'=>'请填写发送内容')));
            die();
		}

        if(strstr($data['content'],'【') or strstr($data['content'],'】')){
            echo(json_encode(array('res'=>'error','msg'=>'发送内容不能包含【和】')));
            die();
		}

		$mids = array();
        $data['content'] = str_replace("\n",'',$data['content']);//短信内容过滤换行符
        $data['content'] = $data['content'].' 退订回N【'.$data['sms_sign'].'】';
		$sms_list[] = array(
            'phones' => $data['phones'],
            'content' => $data['content']
        );

		$content=json_encode($sms_list);
        
		$type='fan-out';
		$result=$smssendobj->send($content,$type);
		if($result){
			if ($result['res']=='succ'){
				//$this->end(true,'发送成功');
                echo(json_encode(array('res'=>'succss','msg'=>'发送成功')));
                //die();
			}else {
				//$this->end(false,'发送失败('.json_encode($result).')');
                echo(json_encode(array('res'=>'error','msg'=>'<font color=red>发送失败('.$result['info'].')</font>')));
                //die();
			}
		}else{
			//$this->end(false,'发送超时');
            echo(json_encode(array('res'=>'error','msg'=>'发送超时')));
            //die();
		}
        
        //保存全局短信日志
        if($result['res'] != 'succ'){
            $status = 'fail';
            $remark = json_encode($result);
        }else{
            $status = 'succ';
            $remark = '';
        }
        
        $log = array(
            'source'=>'other',
            'source_id'=>0,
            'batch_no'=>date('YmdHis'),
            'mobile'=>$data['phones'],
            'content'=>$data['content'],
            'status'=>$status,
            'send_time'=>time(),
            'create_time'=>time(),
            'sms_size'=>ceil(mb_strlen($data['content'],'utf-8')/67),
            'cyear'=>date('Y'),
            'cmonth'=>date('m'),
            'cday'=>date('d'),
            'op_user'=>kernel::single('desktop_user')->get_name(),
            'ip'=>'发条试试',
            'remark'=>$remark,
        );
        app::get('taocrm')->model('sms_log')->insert($log);
            die();
		}

   function ajax_get_tag(){
        $memberTagObj = app::get('taocrm')->model('member_tag');
        if(empty($_GET['keyword']))
        {
            $tags = $memberTagObj->getTagTop(5);
        }else
        {
            $tags = $memberTagObj->getTagByKeyWord($_GET['keyword']);
        }
        echo json_encode($tags);
        exit;
   }

   function ajax_get_active(){
       $activeObj = app::get('market')->model('active');
       if(empty($_GET['keyword']))
        {
            $activeList = $activeObj->getActiveTop($_GET['shop_id'],5);
        }else
        {
            $activeList = $activeObj->getActiveByKeyWord($_GET['shop_id'],$_GET['keyword']);
        }
       echo json_encode($activeList);
       exit;
   }
}
