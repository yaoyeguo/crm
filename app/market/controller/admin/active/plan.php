<?php

class market_ctl_admin_active_plan extends desktop_controller{

    var $pagelimit = 10;
    var $is_debug = false;

    public function index()
    {
        $actions = array(
            array(
                'label'=>'营销计划设计',
                'href'=>'index.php?app=market&ctl=admin_active_plan&act=edit',
            ),
        );

        $base_filter = array('is_active'=>array('sel_member','sel_template','wait_exec','finish'),'type|nohas'=>'edm');

        $orderBy = 'active_id desc';

        $params = array(
            'title' => '自动营销计划',
            'use_buildin_recycle' => false,
            'use_buildin_filter' =>true,
        	'use_buildin_selectrow' => true,
            'orderBy' => $orderBy,
            'base_filter' => $base_filter,
            'actions' => $actions,
        );
        $this->finder('market_mdl_active_plan',$params);
    }

    public function edit()
    {
        $active_id = intval($_GET['active_id']);

        if($active_id > 0){
            $model = app::get('market')->model('active_plan');
            $rs_active = $model->dump($active_id);
        }else{
            $rs_active = array(
                'ab_compare' => 0,
                'half_compare' => 0,
                'valid_num' => 0,
            );
        }

        $this->pagedata['rs_active'] = $rs_active;
        $this->pagedata['active_id'] = $active_id;
        $this->page('admin/active/plan/edit.html');
    }

    public function _period($id = 0)
    {
        if($_POST)
        {
            $not_empty = array(
                    'active_name'   => '活动名称',
                    'start_time'    => '开始时间',
                    'end_time'      => '结束时间',
                    'plan_send_time'     => '执行时间',
                    'auto_run_hour' => '执行时间：时',
                    'auto_run_min'  => '执行时间：分',
                    );
            $post_info = $_POST['info'];

            $info = array(
                    'active_name'   => !empty($post_info['active_name'])    ? trim($post_info['active_name'])   : false,
                    'start_time'    => !empty($post_info['start_time'])     ? strtotime($post_info['start_time'])    : false,
                    'end_time'      => !empty($post_info['end_time'])       ? strtotime($post_info['end_time'])      : false,
                    'plan_send_time'=> !empty($post_info['plan_send_time'])      ? strtotime($post_info['plan_send_time'])     : false,
                    'auto_run_hour' => !empty($post_info['auto_run_hour'])  ? trim($post_info['auto_run_hour']) : false,
                    'auto_run_min'  => !empty($post_info['auto_run_min'])   ? trim($post_info['auto_run_min'])  : false,
                    'remark'        => !empty($post_info['remark'])         ? trim($post_info['remark'])        : '',
                );
           !empty($post_info['active_id']) && $info['active_id']     = !empty($post_info['active_id'])      ? intval($post_info['active_id'])   : 0;


            if($info['start_time'] >= $info['end_time']){
                $msg = array(
                        'type' => false,
                        'msg' => '结束时间必须大于开始时间',
                    );
                echo json_encode($msg);
                exit;
            }

            if($info['auto_run_hour'] > 23 || $info['auto_run_hour'] < 0){
                $msg = array(
                        'type' => false,
                        'msg' => '执行时间小时有错误，取值范围是0-23',
                    );
                echo json_encode($msg);
                exit;
            }

            if($info['auto_run_min'] > 59 || $info['auto_run_min'] < 0){
                $msg = array(
                        'type' => false,
                        'msg' => '执行时间分钟有错误，取值范围是0-59',
                    );
                echo json_encode($msg);
                exit;
            }

            foreach($not_empty as $k => $v){
                if($info[$k] === false){
                    $msg = array(
                        'type' => false,
                        'msg' => $not_empty[$k].'不能为空',
                    );
                    echo json_encode($msg);
                    exit;
                }
            }

            //保存到本地数据库
            $info['shop_ids'] = implode(',', $post_info['shop_ids']);
            $info['create_time'] = time();
            $mod_obj = app::get('market')->model("active_plan");
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            $msg = array(
                'type' => $rt,
                'active_id' => $info['active_id'],
                'msg' => $rt ? '保存成功' : '保存失败',
            );
            echo json_encode($msg);
            exit;
        }else{
            $render = app::get('market')->render();

            if(!$id){
                $info = array(
                    'start_time' => date('Y-m-d'),
                    'end_time' => date('Y-m-d', strtotime('+15 days')),
                    'exec_time' => date('Y-m-d', strtotime('+1 days')),
                    'auto_run_hour' => '09',
                    'auto_run_min' => '30',
                );
            }else{
                $mod_obj = app::get('market')->model('active_plan');
                $info = $mod_obj->dump($id);
            }

            $model = app::get('ecorder')->model('shop');
            $rs = $model->getList('shop_id,name');
            foreach((array)$rs as $v){
                $shops[$v['shop_id']] = $v['name'];
            }

            $render->pagedata['shops'] = $shops;
            $render->pagedata['info'] = $info;
            $this->display('admin/active/plan/step/period.html');
        }
    }

    public function save_filter($id=0)
    {
        if(in_array('2',$_POST['exclude_filter']) && empty($_POST['exclude_hours']) || in_array('3',$_POST['exclude_filter']) && empty($_POST['exclude_tag']) || in_array('4',$_POST['exclude_filter']) && empty($_POST['exclude_active']))
        {
            $msg = array(
                'type' => false,
                'msg' => '排除客户，有勾选项目未填写'
            );
            echo json_encode($msg);
            exit;
        }
        
        if(isset($_POST['filter']['chk_goods_id']) && $_POST['filter']['chk_goods_id']==2){
            if(isset($_POST['filter']['good_name']) && $_POST['filter']['good_name']){

                $good_name_sign = $_POST['filter']['good_name_sign'];
                $good_name = trim($_POST['filter']['good_name']);
                $good_name2 = trim($_POST['filter']['good_name2']);

                if($good_name_sign != 'or') $good_name_sign='and';

                $_POST['filter']['goods_id'] = array();

                $sql = "select goods_id from sdb_ecgoods_shop_goods where (name like '%$good_name%' ";
                if($good_name2)
                    $sql .= " $good_name_sign name like '%$good_name2%' ";
                $sql .= ')';
                $goods_id_list = kernel::database()->select($sql);
                foreach($goods_id_list as $v){
                    $_POST['filter']['goods_id'][] = $v['goods_id'];
                }
                $_POST['filter']['goods_id'] = array_unique($_POST['filter']['goods_id']);
            }
        }
        
        $save_arr = array(
            'filter_mem' => serialize($_POST),
            'active_id' => intval($id),
        );
        $mod_obj = app::get('market')->model("active_plan");
        $rt = $mod_obj->save($save_arr);
        $rt = $rt ? true : false;

        //$res = $this->get_active_members($save_arr['active_id']);

        $msg = array(
            'type' => $rt,
            'msg' => $rt ? '保存成功' : '保存失败',
        );

        return $msg;
    }

    public function _filter($id = 0)
    {
        if($_POST){
            //保存到本地数据库
            $msg = $this->save_filter($id);
            echo json_encode($msg);
            exit;
        }

        $m_active_plan = app::get('market')->model('active_plan');
        $rs_active_plan = $m_active_plan->dump($id);
        $filter_mem = unserialize($rs_active_plan['filter_mem']);
        $filter_mem['filter']['goods_id'] = implode(',',$filter_mem['filter']['goods_id']);
		$filter_mem['filter']['regions_id'] = implode(',',$filter_mem['filter']['regions_id']);

        $this->_init_config_arr();

        in_array('2',$filter_mem['exclude_filter']) && $filter_mem['exclude_filter_c'][2]['checked'] = 'checked';
        in_array('3',$filter_mem['exclude_filter']) && $filter_mem['exclude_filter_c'][3]['checked'] = 'checked';
        in_array('4',$filter_mem['exclude_filter']) && $filter_mem['exclude_filter_c'][4]['checked'] = 'checked';

        $shop_ids = explode(',',$rs_active_plan['shop_ids']);
        $attr_list = $this->get_user_attribute($shop_ids);
        $this->pagedata['attr_list'] = $attr_list;

        $this->pagedata['filter_mem'] = $filter_mem;
        $this->pagedata['rs_active_plan'] = $rs_active_plan;
        $this->display('admin/active/plan/step/filter.html');
    }

    function get_user_attribute($shop_ids)
    {
		$oShop = app::get('ecorder')->model("shop");
        foreach($shop_ids as $id)
        {
            $shop = $oShop->dump($id,'name,config');
            $shop_config = unserialize($shop['config']);
            if($shop_config['prop_name']) 
            {
                $attr[$id]['prop_name'] = array_filter($shop_config['prop_name']);
                $attr[$id]['prop_type'] = array_filter($shop_config['prop_type']);
            }
            $attr[$id]['shop_name'] = $shop['name'];
        }
        return $attr;
    }

    public function _wait_days($id = 0)
    {
        if($_POST)
        {
            $not_empty = array(
                'fixed_cycle_days'   => '活动名称',
            );
            $post_info = $_POST['info'];
            $active_id = intval($post_info['active_id']);
            $box_id = intval($_POST['box_id']);

            $model = app::get('market')->model('active_plan');
            $info = $model->dump($post_info['active_id']);
            $wait_days = explode(',', $info['wait_days']);
            $wait_days[$box_id] = intval($post_info['wait_days']);

            //保存到本地数据库
            $rt = $model->update(
                array('wait_days'=>implode(',',$wait_days)),
                array('active_id'=>$active_id)
            );
            $rt = $rt ? true : false;

            $msg = array(
                'type' => $rt,
                'msg' => $rt ? '保存成功' : '保存失败',
            );
            echo json_encode($msg);
            exit;
        }else{
            $box_id = intval($_GET['box_id']);
            $render = app::get('market')->render();

            $info = array();
            if($id){
                $mod_obj = app::get('market')->model('active_plan');
                $info = $mod_obj->dump($id);
                $wait_days = explode(',', $info['wait_days']);
            }
            $render->pagedata['wait_days'] = intval($wait_days[$box_id]);
            $render->pagedata['info'] = $info;
            $render->pagedata['box_id'] = $box_id;
            $this->display('admin/active/plan/step/wait_days.html');
        }
    }

    public function _report($id = 0)
    {
        $box_id = intval($_GET['box_id']);
        $days = intval($_GET['days']);
        $id = !empty($_POST['id']) ? intval($_POST['id']) : $id;

        if($_POST['box_id']){
            $box_id = intval($_POST['box_id']);
        }

        if(!$id){
            die('<p align="center">请选择一个营销活动</p>');
        }

        $rs_active = $this->app->model('active_plan')->dump($id);

        if($_POST['dead_date']){
            $dead_date = date('Y-m-d', strtotime($_POST['dead_date']));
        }else{
            $dead_date = date('Y-m-d', strtotime("+$days  days", $rs_active['exec_time']));
        }

        //综合
        $params = array(
            'targets' => 830013,
            'shop_id' => $rs_active['shop_ids'],
            'start_time' => $rs_active['start_time'],
            'end_time' => strtotime($dead_date),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->getActivePlanReport($params);
        //err_log($all_res);
        $all_item = $all_res[$all_data['targets']]['data'];
        $sms_info = app::get('market')->model('sms')->dump($id);

        //下单客户占活动客户比例=下单客户数/活动目标客户数
        $all_item['order_member_ratio'] = round($all_item['TotalMembers']*100 / $sms_info['success_num'],2);

        //付款客户占活动客户比例=付款客户数/活动目标客户数
        $all_item['pay_member_ratio'] = round($all_item['PayMembers']*100 / $sms_info['success_num'],2);

        //多笔付款客户占付款客户比例=多笔付款客户数/付款客户数
        $all_item['muti_pay_member_ratio'] = round($all_item['MutiPayMembers']*100 / $all_item['PayMembers'],2);

        //人均下单订单数=下单订单数/下单客户数
        $all_item['per_capita_placing_orders'] = round($all_item['TotalOrders'] / $all_item['TotalMembers'],4);

        //人均下单金额=下单金额/下单客户数
        $all_item['per_capita_amount_order'] = round($all_item['TotalAmount'] / $all_item['TotalMembers'],4);

        //人均付款订单数=付款订单数/付款客户数
        $all_item['per_capita_payment_orders'] = round($all_item['PayOrders'] / $all_item['PayMembers'],4);

        //人均付款金额=付款金额/付款客户数
        $all_item['per_capita_amount_payment'] = round($all_item['PayAmount'] / $all_item['PayMembers'],4);

        //人均付款商品数=付款订单商品数/付款客户数
        $all_item['payment_goods_per_person'] = round($all_item['TotalGoods'] / $all_item['PayMembers'],4);

        //平均订单付款商品数=付款订单商品数/付款订单数
        $all_item['average_number_order_payment_goods'] = round($all_item['TotalGoods'] / $all_item['PayOrders'],4);

        //投入回报比例=（活动目标客户*0.05）/下单金额
        $all_item['investment_returns_ratio'] = '1 : '.round( $all_item['TotalAmount']/($sms_info['success_num'] * 0.05) ,1);
        $this->pagedata['all_item'] = $all_item;

        //订单分析
        $params = array();
        $params['targets'] = 830014;
        $params['shop_id'] = $rs_active['shop_ids'];
        $params['start_time'] = $rs_active['start_time'];
        $params['end_time'] = strtotime($dead_date);
        $res = kernel::single('taocrm_middleware_connect')->getActivePlanReport($params);
        //err_log($res);
        $res = $res[$params['targets']]['data']['value'];

        $rs_goods = app::get('ecgoods')->model('shop_goods')->getList('goods_id,outer_id,name',array_keys($res));
        foreach($rs_goods as $v){
            $goods_list[$v['goods_id']] = $v;
        }

        foreach($res as $k=>$v){
            $res[$k]['outer_id'] = $goods_list[$k]['outer_id'];
            $res[$k]['name'] = $goods_list[$k]['name'];
        }
        $this->pagedata['goods_list'] = $res;


        $rs_active['exec_date'] = date('Y-m-d', $rs_active['plan_send_time']).' '.$rs_active['auto_run_hour'].':'.$rs_active['auto_run_min'];
        $rs_active['exec_time'] = strtotime($rs_active['exec_date']);
        $rs_active['now_time'] = time();

        $this->pagedata['box_id'] = $box_id;
        $this->pagedata['id'] = $id;
        $this->pagedata['active'] = $rs_active;
        $this->pagedata['dead_date'] = $dead_date;
        $this->display('admin/active/plan/step/report.html');
    }

    public function _sms($id = 0)
    {
        if($_POST){
            $save_arr = $_POST;
            if(!$save_arr['half_compare']) $save_arr['half_compare'] = 0;
            if(!$save_arr['ab_compare']) $save_arr['ab_compare'] = 0;
            $save_arr['op_user'] = kernel::single('desktop_user')->get_name();
            $save_arr['ip'] = $_SERVER['REMOTE_ADDR'];

            //保存到本地数据库
            $model = app::get('market')->model("active_plan");
            $res = $model->save($save_arr);
            $res = $res ? true : false;

            if($res == true){
                $this->run_active_plan($save_arr['active_id']);
            }

            $msg = array(
                'type' => $res,
                'msg' => $res ? '保存成功' : '保存失败',
            );
            echo json_encode($msg);
            exit;
        }

        $sms_templates = app::get('market')->model('sms_templates');
		$rs_templetes = $sms_templates->getList("*",array('status'=>1));

        $model = app::get('market')->model('active_plan');
        $rs_active = $model->dump($id);

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

        $this->pagedata['sign_list'] = $sign_list;

        $this->pagedata['rs_active'] = $rs_active;
        $this->pagedata['rs_templetes'] = $rs_templetes;
        $this->display('admin/active/plan/step/sms.html');
    }

    public function get_member_count()
    {
        $active_id = $_GET['active_id'];
        $this->save_filter($active_id);
        $this->get_active_members($active_id);
    }

    public function get_active_members($active_id)
    {
        $model = app::get('market')->model('active_plan');
        $activity = $model->dump($active_id);

        $personAB = $activity['half_compare'];//是否是活动人数对照组
        $messageAB = $activity['ab_compare'];//是否开启短信对照组
        $queryFilter = unserialize($activity['filter_mem']);//条件过滤器
        $params['sourceTable'] = 'sdb_market_active_plan';
        $params['filter'] = $queryFilter['filter'];
        $params['exclude_filter'] = $queryFilter['exclude_filter'];
        $params['exclude_hours'] = $queryFilter['exclude_hours'];
        $params['exclude_tag_id'] = $queryFilter['exclude_tag_id'];
        $params['exclude_active_id'] = $queryFilter['exclude_active_id'];
        $params['shop_id'] = $activity['shop_ids'];
        $params['personAB'] = $personAB;
        $params['messageAB'] = $messageAB;
        $params['reSendTime'] = 0;//不发送的时间
        $params['smsOrMail'] = 'sms';//邮件短信标识

        //err_log($activity);
        //err_log($params);

        $result = kernel::single('taocrm_middleware_connect')->TaskInfo($params);
        //err_log($result);

        //更数据库字段
        $save_arr = array(
            'total_num' => ceil($result['Count']),
            'valid_num' => ceil($result['Send']),
        );
        $filter = array(
            'active_id' => $active_id
        );
        $model->update($save_arr,$filter);

        echo(json_encode($result));
        /*
        array (
          'p3' => 229,
          'p4' => 228,
          'p2' => 457,
          'VoidId' => 1015,
          'Count' => 925,
          'Send' => 914,
          'UnSend' => 11,
          'ReSend' => 35,
        )
        */
    }

    public function get_active_report()
    {
        //综合
        $all_data['targets'] = 830013;
        $all_data['start_time'] = $rs_active['start_time'];
        $all_data['end_time'] = strtotime($dead_date);
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanActiveOrder($id[0],$all_data);
        $all_item = $all_res[$all_data['targets']]['data'];
        $sms_info = app::get('market')->model('sms')->dump($id[0]);

        //下单客户占活动客户比例=下单客户数/活动目标客户数
        $all_item['order_member_ratio'] = round($all_item['TotalMembers']*100 / $sms_info['success_num'],2);

        //付款客户占活动客户比例=付款客户数/活动目标客户数
        $all_item['pay_member_ratio'] = round($all_item['PayMembers']*100 / $sms_info['success_num'],2);

        //多笔付款客户占付款客户比例=多笔付款客户数/付款客户数
        $all_item['muti_pay_member_ratio'] = round($all_item['MutiPayMembers']*100 / $all_item['PayMembers'],2);

        //人均下单订单数=下单订单数/下单客户数
        $all_item['per_capita_placing_orders'] = round($all_item['TotalOrders'] / $all_item['TotalMembers'],4);

        //人均下单金额=下单金额/下单客户数
        $all_item['per_capita_amount_order'] = round($all_item['TotalAmount'] / $all_item['TotalMembers'],4);

        //人均付款订单数=付款订单数/付款客户数
        $all_item['per_capita_payment_orders'] = round($all_item['PayOrders'] / $all_item['PayMembers'],4);

        //人均付款金额=付款金额/付款客户数
        $all_item['per_capita_amount_payment'] = round($all_item['PayAmount'] / $all_item['PayMembers'],4);

        //人均付款商品数=付款订单商品数/付款客户数
        $all_item['payment_goods_per_person'] = round($all_item['TotalGoods'] / $all_item['PayMembers'],4);

        //平均订单付款商品数=付款订单商品数/付款订单数
        $all_item['average_number_order_payment_goods'] = round($all_item['TotalGoods'] / $all_item['PayOrders'],4);

        //投入回报比例=（活动目标客户*0.05）/下单金额
        $all_item['investment_returns_ratio'] = '1 : '.round( $all_item['TotalAmount']/($sms_info['success_num'] * 0.05) ,1);
        $this->pagedata['all_item'] = $all_item;

        //订单分析
        $params = array();
        $params['targets'] = 830014;
        $params['start_time'] = $rs_active['start_time'];
        $params['end_time'] = strtotime($dead_date);
        $res = kernel::single('taocrm_middleware_connect')->createCallplanActiveOrder($id[0],$params);
        $res = $res[$params['targets']]['data']['value'];
        //echo('<pre>');var_dump($or_res);

        $rs_goods = app::get('ecgoods')->model('shop_goods')->getList('goods_id,outer_id,name',array_keys($res));
        foreach($rs_goods as $v){
            $goods_list[$v['goods_id']] = $v;
        }

        foreach($res as $k=>$v){
            $res[$k]['outer_id'] = $goods_list[$k]['outer_id'];
            $res[$k]['name'] = $goods_list[$k]['name'];
        }
        $this->pagedata['goods_list'] = $res;
    }

    public function run_active_plan($active_id)
    {
        $model = app::get('market')->model('active_plan');
        $activity = $model->dump($active_id);

        $personAB = $activity['half_compare'];//是否是活动人数对照组
        $messageAB = $activity['ab_compare'];//是否开启短信对照组
        $filter = array();
        $member_list = array();
        $queryFilter = unserialize($activity['filter_mem']);//条件过滤器
        $filter['sourceTable'] = 'sdb_market_active_plan';
        $filter['filter'] = $queryFilter;

        $filter['exclude_filter'] = $queryFilter['exclude_filter'];
        $filter['exclude_hours'] = $queryFilter['exclude_hours'];
        $filter['exclude_tag_id'] = $queryFilter['exclude_tag_id'];
        $filter['exclude_active_id'] = $queryFilter['exclude_active_id'];

        $filter['opUser'] = $activity['op_user'];
        $filter['ip'] = $activity['ip'];
        $filter['shop_id'] = $activity['shop_ids'];
        $filter['shopId'] = $activity['shop_ids'];
        $filter['taskId'] = $activity['active_id'];
        $filter['isTiming'] = 1;
        $filter['planTimestamp'] = strtotime(date('Y-m-d', $activity['plan_send_time']).' '.$activity['auto_run_hour'].':'.$activity['auto_run_min'].':00');
        //err_log($activity);
        $filter['personAB'] = $personAB;
        $filter['messageAB'] = $messageAB;
        $filter['tamplateA'] = $activity['template_id'];
        $filter['tamplateB'] = $activity['template_id_b'];
        $filter['smsTemplateA'] = $activity['templete'].' 退订回N'.'【'.$activity['sms_sign'].'】';
        $filter['smsTemplateB'] = $activity['templete_b'].' 退订回N'.'【'.$activity['sms_sign_b'].'】';
        $filter['shopName'] = '';
        $filter['smsOrMail'] = 'sms';

        //短信帐号信息
        $send = kernel::single('market_service_smsinterface');
        $send_info = $send->get_usersms_info();
        if( ! is_array($send_info['info'])){
            die('请检查您的短信帐号('.$send_info['info'].')');
        }
        $filter['entId'] = $send_info['info']['account_info']['entid'];
        $filter['entPwd'] = $send_info['entPwd'];
        $filter['license'] = $send_info['license'];

        $result = kernel::single('taocrm_middleware_connect')->createTask($filter);
    }

    private function _init_config_arr()
    {
        //店铺客户等级
        $rs = app::get('ecorder')->model('shop_lv')->getList('lv_id,name');
		if($rs) {
			foreach($rs as $v){
				$levels[$v['lv_id']] = $v['name'];
			}
		}
		$this->pagedata['levels'] = $levels;

        //地区列表
        $rs = app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('region_grade'=>1, 'region_id|sthan'=>3242));
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
}
