<?php

class market_ctl_admin_active_cycle extends market_ctl_admin_active_abstract{

	var $pagelimit = 10;
	public static $middleware_connect = null;

	public function index()
	{
        if($_GET['view']==1) {
            $this->log();
            exit;
        }

        $actions = array(
            array(
            'label'=>'创建周期营销',
            'href'=>'index.php?app=market&ctl=admin_active_cycle&act=edit',
            'target'=>'dialog::{width:800,height:400,title:\'周期营销活动\'}'
            ),
        );

		$param=array(
            'title'=>'周期自动营销',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=> true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "if(exec_time is null ,create_time ,exec_time) desc",
            'base_filter'=>array(),
            'actions'=>$actions,
        );
        $this->finder('market_mdl_active_cycle',$param);
	}

    public function log()
	{
        $actions = array();

		$param=array(
            'title'=>'周期自动营销日志',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=> true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "log_id desc",
            'base_filter'=>array(),
            'actions'=>$actions,
        );
        $this->finder('market_mdl_active_cycle_log',$param);
	}

	function _views()
	{
		$model = $this->app->model('active_cycle');
		$base_filter=array();
		$sub_menu[] = array(
            'label'=> '活动列表',
            'filter'=> $base_filter,
            'addon'=> $model->count(),
            'href' => 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act=index&view=0',
            'optional'=>false,
		);

        $model = $this->app->model('active_cycle_log');
		$base_filter=array();
		$sub_menu[] = array(
            'label'=> '日志列表',
            'filter'=> $base_filter,
            'addon'=> $model->count(),
            'href' => 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act=index&view=1',
            'optional'=>false,
		);

		return $sub_menu;
	}

    //周期营销活动
    function edit()
    {
        if($_POST){
            $this->save();
        }

        $group_id = !empty($_GET['group_id']) ? $_GET['group_id'] : 0;
        $shop_id = !empty($_GET['shop_id']) ? $_GET['shop_id'] : 0;
        //获取商品分组
        $m_ecgoods_group = app::get('ecgoods')->model('group');
        $rs_group = $m_ecgoods_group->getList('group_id,group_name,goods_id');
        foreach((array)$rs_group as $k=>$v){
            $goods_group[$v['group_id']] = $v['group_name'];
            $rs_group[$k]['goods_num'] = count(explode(',', $v['goods_id']));
        }

        //获取会员分组
            //店铺列表
        $shops = app::get('ecorder')->model('shop')->getList('shop_id,name,subbiztype',array('subbiztype|neqAndNotNull' => 'fx'));
        $curr_shop = current($shops);
        foreach($shops as $k => $shop)
        {
            $g_shops[$shop['shop_id']] = $shop['name'];
        }
        $this->pagedata['g_shops'] = $g_shops;

        $filter['parent_id'] = 0;
        $groups = app::get('taocrm')->model('member_group')->getList('*', $filter, 0, -1, 'group_id ASC');
        foreach($groups as $gk => $group)
        {
            $groups_list[$group['shop_id']][] = $group;
        }
        $this->pagedata['groups'] = $groups_list;
        //获取会员标签
        $tags = app::get('taocrm')->model('member_tag')->getList('tag_id,tag_name,members,tag_type', array(), 0, -1, 'mobile_valid_nums desc');
        $tag_types =  array(
                'system_a' => '系统标签',
                'system_b' => '系统标签',
                'system_c' => '系统标签',
                'system_d' => '系统标签',
                'store' => '商家自动打标',
                'hand' => '手动打标',
            );
        foreach($tags as $tk => $tag)
        {
            $tag['type_msg'] = $tag_types[$tag['tag_type']];
            $tags[$tk] = $tag;
        }
        $this->pagedata['tags'] = $tags;
        
        //修改活动
        $active_id = intval($_GET['active_id']);
        if($active_id>0){
            $rs_active = $this->app->model('active_cycle')->dump($active_id);

            $rs_active['exclude_filter'] = json_decode($rs_active['exclude_filter'], true);

            if($rs_active['goods_id']){
                $goods_id = explode(',', $rs_active['goods_id']);
                $rs_goods = app::get('ecgoods')->model('shop_goods')->getList('goods_id,bn,name',array('goods_id'=>$goods_id));
                foreach($rs_goods as $k=>$v){
                    $rs_goods[$k]['org_name'] = $v['name'];
                    $rs_goods[$k]['name'] = mb_substr($v['name'],0,20,'utf-8');
                }
                $this->pagedata['rs_goods'] = $rs_goods;
            }
        }else{
            //初始化默认参数
            $rs_active['auto_cycle_type'] = 'auto';
            $rs_active['auto_run_hour'] = 11;
            $rs_active['auto_run_min'] = 20;
            $rs_active['shop_id'] = $shop_id;
            $rs_active['group_id'] = $group_id;
        }

        //短信模板
        $templates_obj = $this->app->model('sms_templates');
        $rs_templetes = $templates_obj->getList("template_id,title",array('status'=>1));
        if($rs_templetes){
            $this->pagedata['rs_templetes'] = $rs_templetes;
        }

        //店铺列表
        $rs_shops = array();
        $model = app::get('ecorder')->model('shop');
        $rs = $model->getList('shop_id,name');
        foreach((array)$rs as $v){
            $rs_shops[$v['shop_id']] = $v['name'];
        }
        $this->pagedata['shops'] = $rs_shops;

        //短信可选签名
        $oShop = app::get('ecorder')->model("shop");
        $rs = $oShop->getList();
        foreach($rs as $k => $v)
        {
            $v_coinfig = unserialize($v['config']);
            if(empty($v_coinfig['sms_sign']) || empty($v_coinfig['extend_no']) || in_array($v_coinfig['extend_no'],$sign_list_check))
                continue;

            $sign_list_check[] = $v_coinfig['extend_no'];
            $sign_list[$k]['sign'] = $v_coinfig['sms_sign'];
            $sign_list[$k]['extend_no'] = $v_coinfig['extend_no'];
        }

        $this->pagedata['g_shop_id'] = !empty($rs_active['shop_id']) ? substr($rs_active['shop_id'],0,32) : $curr_shop['shop_id'];
        $this->pagedata['sign_list'] = $sign_list;
        $this->pagedata['rs_active'] = $rs_active;
        $this->pagedata['rs_group'] = $rs_group;
        $this->pagedata['goods_group'] = $goods_group;
        $this->display('admin/active/cycle/edit.html');
    }

    //开启或关闭周期活动
    function edit_status()
    {
        if($_POST){
            $url = 'index.php?app=market&ctl=admin_active_cycle&act=index';
            $this->begin($url);

            $save_data = $_POST;
            $save_data['status'] = intval($save_data['status']);
            $save_data['op_user'] = kernel::single('desktop_user')->get_name();
            $save_data['ip'] = $_SERVER['REMOTE_ADDR'];
            $filter = array('active_id'=>$save_data['active_id']);
            $this->app->model('active_cycle')->update($save_data, $filter);

            //营销设置发送到内存计算平台
            $this->run_active_cycle($save_data['active_id']);
            $this->end(true,'保存成功');
        }

        //修改活动
        $active_id = intval($_GET['active_id']);
        if($active_id>0){
            $rs_active = $this->app->model('active_cycle')->dump($active_id);
            $rs_active['status']=='1' ? $rs_active['new_status']=0 : $rs_active['new_status']=1;
        }

        $this->pagedata['rs_active'] = $rs_active;
        $this->display('admin/active/cycle/edit_status.html');
    }

    function save()
    {
        $url = 'index.php?app=market&ctl=admin_active_cycle&act=index';
        $this->begin($url);

        $save_data = $_POST;
        $save_data['shop_id'] = !empty($save_data['shop_id']) ? implode(',', $save_data['shop_id']) : $save_data['g_shopid'];
        $save_data['exclude_filter'] = json_encode($save_data['exclude_filter']);
        $save_data['active_id'] = intval($save_data['active_id']);
        $save_data['start_time'] = strtotime($save_data['start_time']);
        $save_data['end_time'] = strtotime($save_data['end_time']);
        $save_data['fixed_cycle_days'] = intval($save_data['fixed_cycle_days']);
        !empty($save_data['goods_id']) && $save_data['goods_id'] = implode(',', $save_data['goods_id']);
        $save_data['op_user'] = kernel::single('desktop_user')->get_name();
        $save_data['ip'] = $_SERVER['REMOTE_ADDR'];
        if($save_data['active_id']==0){
            $this->app->model('active_cycle')->save($save_data);
        }else{
            $filter = array('active_id'=>$save_data['active_id']);
            $this->app->model('active_cycle')->update($save_data, $filter);
        }

        $this->run_active_cycle($save_data['active_id']);

        $this->end(true,'保存成功');
    }

    function run_active_cycle($active_id=0)
    {
        $rs = $this->app->model('active_cycle')->dump($active_id);
        if($rs){
            $exclude_filter = json_decode($rs['exclude_filter'], true);

            $params['goodsId'] = $rs['goods_id'];
            if($exclude_filter['hours_open']=='1'){
                $params['excludeHours'] = intval($exclude_filter['hours']);
            }
            $params['excludeUniqueUser'] = 1;
            if($exclude_filter['goods_days_open']=='1'){
                $params['excludeGoodsDays'] = intval($exclude_filter['goods_days']);
                $params['excludeGoodsId'] = '0';

                //过滤本次活动商品
                if($exclude_filter['the_goods']==1){
                    $params['excludeGoodsId'] .= ','.$rs['goods_id'];
                }

                //过滤商品分组
                $goods_group_id = intval($exclude_filter['goods_group_id']);
                if($goods_group_id>0){
                    $rs_goods_group = app::get('ecgoods')->model('group')->dump($goods_group_id);
                    if($rs_goods_group['goods_id']){
                        $params['excludeGoodsId'] .= ','.$rs_goods_group['goods_id'];
                    }
                }

                //过滤商品的商家编码
                $goods_bn = trim($exclude_filter['goods_bn']);
                if($goods_bn){
                    $rs_goods = app::get('ecgoods')->model('shop_goods')->dump(array('bn'=>$goods_bn));
                    if($rs_goods['goods_id']){
                        $params['excludeGoodsId'] .= ','.$rs_goods['goods_id'];
                    }
                }
            }
            $group_mod = app::get('taocrm')->model('member_group');
            $group_data = $group_mod->dump(array('group_id'=>$rs['group_id']));
            $group_data['filter'] =  unserialize($group_data['filter']);
            $params['groupData'] = $group_data;
            //$params['groupId'] = $rs['group_id'];
            $params['tagId'] = $rs['tag_id'];
            $params['type'] = substr($rs['source'],6);
            $params['cycleType'] = $rs['cycle_type'];
            $params['autoCycleType'] = $rs['auto_cycle_type'];
            $params['autoCycleDays'] = intval($rs['auto_cycle_days']);
            $params['fixedCycleDays'] = intval($rs['fixed_cycle_days']);
            $params['autoRunTime'] = $rs['auto_run_hour'].':'.$rs['auto_run_min'];
            $params['smsTemplateA'] = $rs['templete'].' 退订回N【'.$rs['sms_sign'].'】';
            $params['isTiming'] = 1;
            $params['shopId'] = $rs['shop_id'];
            $params['opUser'] = $rs['op_user'];
            $params['ip'] = $rs['ip'];
            $params['status'] = intval($rs['status']);
            $params['beginTime'] = $rs['start_time'];
            $params['endTime'] = $rs['end_time'];
            $params['taskId'] = $rs['active_id'];
            $params['sourceTable'] = 'sdb_market_active_cycle';

            switch($params['type'])
            {
                case 'group':
                    unset($params['tagId']); 
                    unset($params['goodsId']); 
                    break;
                case 'tags':
                    unset($params['groupData']); 
                    unset($params['goodsId']); 
                    break;
                case 'goods':
                    unset($params['tagId']); 
                    unset($params['groupData']); 
                    break;
            }
            $send = kernel::single('market_service_smsinterface');
            $send_info = $send->get_usersms_info();
            if( ! is_array($send_info['info'])){
                $this->end(false, '请检查您的短信帐号('.$send_info['info'].')');
            }
            $params['entId'] = $send_info['info']['account_info']['entid'];
            $params['entPwd'] = $send_info['entPwd'];
            $params['license'] = $send_info['license'];

            kernel::single('taocrm_middleware_connect')->requestActiveCycle($params);
        }
    }
}
