<?php
class market_finder_active_assess {

    public $addon_cols = "active_id,is_control,active_members_res_b,data_update_time,end_time,total_members";
    protected static $activeObj = '';

    //营销活动评估
    var $back_detail_basic = '详细信息';
    public function back_detail_basic($id)
    {
        $app = &app::get('market');
        $assessobj=$app->model('active_assess');
        $render = $app->render();
        $assess_data=$assessobj->dump(array('id'=>$id));

        $this->db = kernel::database();
        kernel::single("market_backstage_activity")->processAsses($assess_data['active_id']);

        $assess_data=$assessobj->dump(array('id'=>$id));
        $assess_data['active_members_res']=unserialize($assess_data['active_members_res']);//参加活动客户
        $assess_data['control_members_res']=unserialize($assess_data['control_members_res']);//对照组客户（短信A组）
//        $assess_data['active_members_res_b']=unserialize($assess_data['active_members_res_b']);//短信B组
        $assess_data['end_time']=!empty($assess_data['end_time']) ? date("Y-m-d H:i:s" ,$assess_data['end_time']) : '';
        $assess_data['exec_time']=!empty($assess_data['exec_time']) ? date("Y-m-d H:i:s" ,$assess_data['exec_time']) : '';
        $activeObj = $app->model('active');
        $activeInfo = $activeObj->dump(array('active_id' => $assess_data['active_id']));
        //短信标题
        $assess_data['active_members_res']['sms_info']['templete_title'] = $activeInfo['templete_title'];
        //短信内容
        $assess_data['active_members_res']['sms_info']['templete'] = $activeInfo['templete'];

        if ($activeInfo['template_edm_id'] > 0) {
            $assess_data['active_members_res']['sms_info']['templete'] = '';
            $assess_data['active_members_res']['sms_info']['template_edm_id'] = $activeInfo['template_edm_id'];

        }
        if ($activeInfo['templete_title_b'] != '') {
            $assess_data['active_members_res_b']=unserialize($assess_data['active_members_res_b']);//短信B组
            $assess_data['active_members_res_b']['sms_info']['templete_title'] = $activeInfo['templete_title_b'];
            $assess_data['active_members_res_b']['sms_info']['templete'] = $activeInfo['templete_b'];
        }else {
            $assess_data['active_members_res_b'] = '';
        }

        $render->pagedata['assess_data'] = $assess_data;
        return $render->fetch('admin/active/assess.html');
    }

    //销售总金额
    public function sale_money($memlist=array(),$time=""){
        $data_from=$time;
        $data_to=$time+(15*86400);
        $ordersobj=&app::get('ecorder')->model('orders');
        $sql="select sum(total_amount) as total_money from sdb_ecorder_orders where member_id in (".implode("," , $memlist).") and createtime>=".$data_from." and createtime<=".$data_to;
        $total_money=$ordersobj->db->select($sql);
        $all_money=$total_money[0]['total_money'];
        return $all_money;
    }

    //二次购买客户数
    public function re_nums($members=array(),$time="",$pay_status="",$status=""){
        $ordersobj=&app::get('ecorder')->model('orders');
        switch ($pay_status) {
            case 1:
                $statue_fliter['pay_status']=$pay_status;
                break;
        }
        switch ($status){
            case 'finish':
                $statue_fliter['status']=$status;
                break;
        }
        $date_from=$time;
        $date_to=$time+(15*86400);
        $fliter_time=array('createtime|between'=>array($date_from,$date_to));
        if (!empty($statue_fliter)){
            $fliter=array_merge($fliter_time,$statue_fliter);
        }else {
            $fliter=array('createtime|between'=>array($date_from,$date_to));

        }
        $re_nums=$ordersobj->getList('member_id',$fliter);
        foreach ($re_nums as $k=>$v){
            $re_numss[]=$v['member_id'];
        }
        $re_unm=array_intersect($re_numss, $members);
        return $re_unm;
    }

    
    var $column_lost_members = '未下单人数';
    var $column_lost_members_order = 11;
    var $column_lost_members_width = 100;
    function column_lost_members($row) {
        return  $row['lost_members'];
    }

    var $column_order_ratio = '下单比例';
    var $column_order_ratio_order = 12;
    var $column_order_ratio_width = 80;
    function column_order_ratio($row) {
        return  $row['order_ratio'];
    }

    var $column_io_ratio = '投资回报率';
    var $column_io_ratio_order = 13;
    var $column_io_ratio_width = 80;
    function column_io_ratio($row) {
        return  $row['io_ratio'];
    }

    var $column_effect_img = '效果度';
    var $column_effect_img_order = 14;
    var $column_effect_img_width = 100;
    function column_effect_img($row) {
        return  $row['effect_img'];
    }

    //重复营销
    var $column_invalid = '操作';
    var $column_invalid_order = 150;
    var $column_invalid_width = 150;
    function column_invalid($row) {
        $active_id = intval($row[$this->col_prefix . 'active_id']);
        if (self::$activeObj == '') {
            self::$activeObj = &app::get('market')->model('active');
        }
        $activeInfo =  self::$activeObj->dump(array('active_id' => $active_id));
        if ($activeInfo['template_edm_id'] <= 0) {
            if ($activeInfo['pay_type'] != 'market') {
                $typeInfo = unserialize($activeInfo['type']);
                if (count($typeInfo) == 1) {
                    $type = $typeInfo[0];
                }
                elseif (count($typeInfo) > 1) {
                    $type = $typeInfo[1];
                }
                $disp = '<a href="index.php?app=market&ctl=admin_active_'.$type.'&act=repeat_active&p[0]='.$active_id.'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('重复营销').'\', width:700, height:355}">重复营销</a>';
            }
            else {
                $disp = '已完成';
            }

        }
        else {
            $disp = '';
        }
        return $disp;
    }

    //是否开启对照组
    var $column_aliasIsControl = '是否对照';
    var $column_aliasIsControl_order = 130;
    var $column_aliasIsControl_width = 130;
    var $column_aliasIsControl_order_field = 'is_control';
    function column_aliasIsControl($row) {
        $active_id = intval($row[$this->col_prefix . 'active_id']);
        if (self::$activeObj == '') {
            self::$activeObj = &app::get('market')->model('active');
        }
        $activeInfo =  self::$activeObj->dump(array('active_id' => $active_id));
        $title = '否';
        if ($activeInfo['control_group'] == 'yes' || $activeInfo['template_id_b'] > 0) {
            $title = '是';
        }
        return $title;
    }

    function processAsses($active_id){
        $assess = $this->db->selectrow('select * from sdb_market_active_assess where active_id='.$active_id);
        if($assess['state'] == 'unfinish'){
            if($assess['is_control'] == 0){
                $active_members_res = $this->getAssess($assess['active_id'],0,$assess['exec_time']);
                $active_members_res = serialize($active_members_res);
                $values = array('active_members_res'=>$active_members_res);
            }else if($assess['is_control'] == 1){
                $active_members_res = $this->getAssess($assess['active_id'],1,$assess['exec_time']);
                $active_members_res = serialize($active_members_res);
                $control_members_res = $this->getAssess($assess['active_id'],2,$assess['exec_time']);
                $control_members_res = serialize($control_members_res);
                $values = array('active_members_res'=>$active_members_res,'control_members_res'=>$control_members_res);
            }

            //$this->db->update('sdb_market_active_assess', $values, 'active_id='.$assess['active_id']);
            $oActiveAssess = &app::get('market')->model('active_assess');
            $oActiveAssess->update($values,array('active_id'=>$assess['active_id']));
        }
    }


    function getAssess($active_id,$status=0,$exec_time){

        $this->assessTime = 1296000;
        $page = 0;
        $page_size = 1000;
        $date_from=$exec_time;
        $date_to=$exec_time+$this->assessTime;
        $acmembers = 0;
        $ordernums = 0;//下单数
        $apaynums = 0;//支付单数
        $afinish_members = 0;//订单完成数
        $aratio = 0;//回头数
        $asale_money = 0;//总金额

        $all_member = array();
        $paid_member = array();
        $finish_member = array();

        $acmembers = $this->db->selectrow('select count(*) as total from sdb_market_active_member where status='.$status.' and active_id='.$active_id);
        $acmembers = $acmembers['total'];

        while(true){
            $rows = $this->db->select('select member_id from sdb_market_active_member where status='.$status.' and active_id='.$active_id .' order by member_id limit '.($page*$page_size).','.$page_size);
            if(!$rows)break;
            $mids = array();
            foreach($rows as $row){
                $mids[] = $row['member_id'];
            }

            $sql = 'select member_id,pay_status,status,total_amount from sdb_ecorder_orders where member_id in('.implode(',', $mids).') and createtime >='.$date_from.' and createtime <='.$date_to.' ';
            $orders = $this->db->select($sql);
            if($orders){

                //$ordernums += count($orders);
                foreach($orders as $order){

                    $all_member[$order['member_id']] = 1;

                    if($order['pay_status'] == '1'){
                        $paid_member[$order['member_id']] = 1;
                    }
                    if($order['status'] == 'finish'){
                        $finish_member[$order['member_id']] = 1;
                    }
                    $asale_money += $order['total_amount'];
                }

            }

            $page++;
        }

        $ordernums = sizeof($all_member);
        $apaynums = sizeof($paid_member);
        $afinish_members = sizeof($finish_member);

        if($acmembers > 0){
            $aratio = (($ordernums/$acmembers))?(round(($ordernums/$acmembers),4))*100:0;
        }else{
            $aratio = 0;
        }

        return array('acmembers'=>$acmembers,'ordernums'=>$ordernums,'apaynums'=>$apaynums,'afinish_members'=>$afinish_members,'aratio'=>$aratio,'asale_money'=>$asale_money);
    }

    //营销活动评估
    var $detail_basic = '营销活动详情';
    public function detail_basic($id)
    {
        $app = app::get('market');
        $assessModel = $app->model('active_assess');
        $assessInfo = $assessModel->dump(array('id' => $id));
        $params = array(
            'shopId' => $assessInfo['shop_id'],
            'taskId' => $assessInfo['active_id'],
            'execTime' => $assessInfo['exec_time']
        );
        $activeModel = $app->model('active');
        $activeInfo = $activeModel->dump(array('active_id' => $assessInfo['active_id']));
        $control = $activeInfo['control_group'];
        $activeType = $activeInfo['type'];
        if ($activeType) {
            $activeType = unserialize($activeType);
            if (count($activeType) == 1) {
                $activeType = $activeType[0];
            }
            elseif (count($activeType) > 1) {
                $activeType = $activeType[1];
            }
        }
        
        $activeInfo['start_time'] = intval($activeInfo['start_time']);
        $activeInfo['end_time'] = intval($activeInfo['end_time']);
        $params['beginTime'] = ($activeInfo['start_time']==0 ? $activeInfo['exec_time'] : $activeInfo['start_time']);
        $params['endTime'] = ($activeInfo['end_time']==0 ? ($activeInfo['exec_time']+86400*15) : $activeInfo['end_time']);
        
        if($params['endTime'] > time()){
            //从java读取
        $connect = new taocrm_middleware_connect;
        $accessActiveInfo = $connect->ActiveTotalInfo($params);
        }else{
            //改从本地数据缓存读取
            $accessActiveInfo = $app->model('active_abc')->getABC($assessInfo['active_id']); 
        }
        
        $active_assess = array(
            'order_members' => 0,
            'paid_members' => 0,
        );
        
        $data = array();
        $i = 0;
        foreach ($accessActiveInfo as $k => $v) {
            if ($k == 'A') {
                //名称
                $data[$i]['title'] = ($activeType == 'sms') ? '短信A组' : '邮件组';
                //标题
                $data[$i]['message'] = $activeInfo['templete_title'];
                $data[$i]['desc'] = $activeInfo['templete'];
            }
            elseif ($k == 'B') {
                $data[$i]['title'] = '短信B组';
                $data[$i]['message'] = $activeInfo['templete_title_b'];
                $data[$i]['desc'] = $activeInfo['templete_b'];
            }
            elseif ($k == 'C') {
                $data[$i]['title'] = '活动对照组';
                $data[$i]['message'] = '';
            }
            //参与活动客户数
            $data[$i]['MemberCount'] = $v['MemberCount'];
            //下单人数
            $data[$i]['BuyMember'] = $v['BuyMember'];
            $active_assess['order_members'] += floatval($v['BuyMember']);
             //未下单人数
            $data[$i]['UnBuyMember'] = $v['MemberCount'] - $v['BuyMember'];
            //付款人数
            $data[$i]['PayMember'] = $v['PayMember'];
            $active_assess['paid_members'] += floatval($v['PayMember']);
            //交易完成人数
            $data[$i]['FinishMember'] = $v['FinishMember'];
            //回头客比率
            $data[$i]['ratio'] = $v['MemberCount'] > 0 ?  number_format(($v['BuyMember'] / $v['MemberCount']) * 100, 2) . '%' : 0;
            //投资回报率
        	if($v['AmountCount']){
            	$data[$i]['ReturnRatio']='1:'.round($v['AmountCount']/($v['MemberCount']*0.05));
            }else{
            	$data[$i]['ReturnRatio']=round($v['MemberCount']*0.05).":0";
            }
            //总销售额
            $data[$i]['AmountCount'] = $v['AmountCount'];
            //执行时间
            $data[$i]['exec_time'] = $activeInfo['exec_time'];
            //开始时间
            $data[$i]['begin_time'] = $activeInfo['start_time'];
            //结束时间
            $data[$i]['end_time'] = $activeInfo['end_time'];
            //营销组类型
            $data[$i]['group_type'] = $k;
            $i++;
        }
        
        if($params['endTime'] > time() && $active_assess['order_members']){
            $assessModel->update($active_assess, array('id' => $id));
        }

        $render = $app->render();
        $render->pagedata['activeType'] = $activeType;
        $render->pagedata['control'] = $control;
        $render->pagedata['data'] = $data;
        $render->pagedata['assessInfo'] = $assessInfo;
        return $render->fetch('admin/active/assess_new.html');
    }

    var $detail_daily_orders = '每日下单记录';
    public function detail_daily_orders($id)
    {
        $data['targets'] = 830010;
        $res = kernel::single('taocrm_middleware_connect')->createCallplanActiveOrder($id,$data);

        $app = app::get('market');
        $render = $app->render();

        $render->pagedata['data'] = $res[$data['targets']]['data'];
        return $render->fetch('admin/active/assess_orders.html');
    }

    var $detail_order_items = '下单商品分析';
    public function detail_order_items($id)
    {
        $java_db = kernel::single('taocrm_middleware_connect');
        $params['targets'] = 830011;
        $res = $java_db->createCallplanActiveOrder($id,$params);
        $data = $res[$params['targets']]['data'];

        //echo('<pre>');var_dump($data);
        $rs = app::get('ecgoods')->model('shop_goods')->getList('goods_id,outer_id,name',array_keys($data));
        foreach($rs as $v){
            $goods_list[$v['goods_id']] = $v;
        }
        
        foreach($data as $k=>$v){
            $data[$k]['outer_id'] = $goods_list[$k]['outer_id'];
            $data[$k]['name'] = $goods_list[$k]['name'];
        }

        $app = app::get('market');
        $render = $app->render();
        $render->pagedata['data'] = $data;
        return $render->fetch('admin/active/assess_items.html');
    }

    var $detail_member_list = '下单客户分析';
    public function detail_member_list($id)
    {
        $params['targets'] = 830012;
        $res = kernel::single('taocrm_middleware_connect')->createCallplanActiveOrder($id,$params);
        $data = $res[$params['targets']]['data'];

        $app = app::get('market');
        $render = $app->render();
        
        //echo('<pre>');var_dump($data);

        $render->pagedata['data'] = $data;
        return $render->fetch('admin/active/assess_member.html');
    }

    var $detail_active_info = '活动快照';
    public function detail_active_info($id)
    {
        $app = app::get('market');
        $render = $app->render();
        $active_assess = $app->model('active_assess')->dump($id);;
        $active = $app->model('active')->dump($active_assess['active_id']);
        
        if($active){
            $active['start_time'] = date('Y-m-d', $active['start_time']);
            $active['end_time'] = date('Y-m-d', $active['end_time']);
            $active['plan_send_time'] = date('Y-m-d H:i:s', $active['plan_send_time']);
            
            if($active['exec_time'] == 0){
                $active['exec_time'] = '<font color=red>未执行</font>';
            }else{
                $active['exec_time'] = date('Y-m-d H:i:s', $active['exec_time']);
            }
            
            $active['exclude_filter'] = '无';
            if($active['filter_mem']){
                $filter_mem = unserialize($active['filter_mem']);
                //echo('<pre>');var_dump($filter_mem);
                $active['include_filter'] = $this->get_include_filter($filter_mem);
                $active['exclude_filter'] = $this->get_exclude_filter($filter_mem);
            }elseif($active['filter_sql']){
                $active['include_filter'] = '快捷营销';
            }elseif($active['report_filter']){
                $active['include_filter'] = '报表营销';
            }elseif($active['member_list']){
                $member_list = unserialize($active['member_list']);
                //var_dump($member_list[0]);
                if(strstr($member_list[0],'group_id:')>=0){
                    $group = app::get('taocrm')->model('member_group')->dump(str_replace('group_id:','',$member_list[0]));
                    $active['include_filter'] = '会员分组：'.$group['group_name'];
                }else{
                    $active['include_filter'] = '直选会员';
                }
            }
        }
        
        $render->pagedata['active'] = $active;
        return $render->fetch('admin/active/monitor/snap_shoot.html');
    }
    
    function get_include_filter($filter)
    {
        $filter_arr = array();
        $keys = array(
            'finish_orders' => '成功的订单数',
            'total_amount' => '订单总金额',
            'buy_freq' => '购买频次',
            'buy_products' => '购买商品总数',
            'create_time' => '下单时间',
            'points' => '客户积分',
            'goods_id' => '购买过的商品',
            'regions_id' => '收货区域',
            'lv_id' => '客户等级',
        );
        $signs = array(
            'nequal' => '等于',
            'bthan' => '大于等于',
            'sthan' => '小于等于',
            'between' => '介于',
        );
        foreach($filter['filter'] as $k=>$v){
            switch($k){
                case 'goods_id':
                    $filter_arr[] = '购买过指定商品';
                break;
                
                case 'lv_id':
                    $filter_arr[] = '指定客户等级';
                break;
                
                case 'regions_id':
                    $filter_arr[] = '指定收货区域';
                break;
                
                default:
                    if($v['sign']=='between'){
                        $filter_arr[] = $keys[$k].' '.$signs[$v['sign']].' '.$v['min_val'].'~'.$v['max_val'];
                    }elseif(is_array($v) && $v['sign']){
                        $filter_arr[] = $keys[$k].' '.$signs[$v['sign']].' '.$v['min_val'];
                    }
                break;
            }
        }
        return implode('<br/>', $filter_arr);
    }
    
    function get_exclude_filter($filter)
    {
        $filter_arr = array();
        if(in_array('2', $filter['exclude_filter'])){
            if($filter['exclude_hours']){
                $filter_arr[] = $filter['exclude_hours'].'小时内营销过的客户'; 
            }
        }
        
        if(in_array('3', $filter['exclude_filter'])){
            if($filter['exclude_tag_id']){
                $filter_arr[] = '标签组 '.$filter['exclude_tag'].'的客户'; 
            }
        }
        
        if(in_array('4', $filter['exclude_filter'])){
            if($filter['exclude_active_id']){
                $filter_arr[] = '参加过 '.$filter['exclude_active'].' 营销活动的客户'; 
            }
        }
        
        if(!$filter_arr) $filter_arr[] = '无';
        return implode('<br/>', $filter_arr);
    }
    
}

