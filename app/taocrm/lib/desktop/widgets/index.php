<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_desktop_widgets_index implements desktop_interface_widget
{
    /**
     * 构造方法，初始化此类的某些对象
     * @param object 此应用的对象
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->render = new base_render(app::get('taocrm'));
    }

    /**
     * 获取桌面widgets的标题
     * @param null
     * @return null
     */
    public function get_title()
    {
        return "桌面";
    }

    /**
     * 获取桌面widgets的html内容
     * @param null
     * @return string html内容
     */
    public function get_html()
    {
        //保存需要显示的运营数据统计的项
        if($_POST && isset($_POST['check_tags'])){
            base_kvstore::instance('taocrm')->store('dashboard_check_tags', json_encode($_POST['check_tags']));
        }
    
        $db = kernel::database();
        $this->db = $db;
        $shop_id = $_GET['shop_id'];

        if(!$shop_id)
        base_kvstore::instance('ecorder')->fetch('default_shop_id', $shop_id);

        $shopObj = &app::get('ecorder')->model('shop');
        $shops = $shopObj->getList('*');
        if($shop_id && !empty($shops)){
            $ids = array();
            foreach($shops as $shop){
                $ids[] = $shop['shop_id'];
            }
            if(!in_array($shop_id, $ids)){
                $shop_id = $shops[0]['shop_id'];
            }
        }

        $curr_date = strtotime(date('Y-m-d'));
        $date_to = $curr_date;
        $date_from = strtotime('-30 days',$curr_date);

        $month1_start = $curr_date;
        $month1_from = strtotime('-30 days',$curr_date);
        $month2_from = strtotime('-60 days',$curr_date);
        if(!$shop_id) $shop_id = $shops[0]['shop_id'];

        if($shop_id){
            //订单分析1-30天
            //$month1 = $this->getNewOldMemberAnalysis($shop_id, $month1_from, $curr_date);
            
            //31 - 60 天
            $endTime = strtotime(date("Y-m-d")) - 86400 * 30;
            //$month2 = $this->getNewOldMemberAnalysis($shop_id, $month2_from, $month1_from);
            
            //近3个月客户购买金额排名
            //$hot_members = $this->lastThreeBuyAmountTop($shop_id);
            
            //近3个月热销商品排名
            //$hot_goods = $this->lastThreeTopSale($shop_id);
        }

        $month_to_1 = date("Y-m-d 00:00:00",mktime (0,0,0,date('n')+1,1,date('Y')));
	    $month_from_1 = date("Y-m-d 00:00:00",mktime (0,0,0,date('n')+1,1,date('Y')-1));
        
        $week_name = array('日','一','二','三','四','五','六');
        $run_data_tags = array(
            //'bind_shop_cnt' => '已绑定店铺',
            //'unbind_shop_cnt' => '失效/未绑定店铺',
            'weixin_member_cnt' => '微信关注数',
            'total_member_cnt' => '总客户数',
            'once_buy_member_cnt' => '单次客户数',
            'more_buy_member_cnt' => '多次客户数',
            'active_member_cnt' => '活跃客户数',
            'inactive_member_cnt' => '沉睡客户数',
            'total_tag_member_cnt' => '客户标签数',
            'unorder_member_cnt' => '未下单客户',
            
            'sep1' => 'sep',
            
            'total_amount' => '总成交金额',
            'total_order_cnt' => '总成交订单数',
            'avg_member_amount' => '平均客单价',
            'avg_order_amount' => '平均订单价',
            'avg_member_goods_cnt' => '人均成交件数',
            'avg_member_order_cnt' => '人均成交笔数',
            
            'sep2' => 'sep',
            
            'total_marketing_member_cnt' => '营销客户数',
            'total_marketing_buy_member_cnt' => '营销参与客户数',
            'active_roi' => '营销ROI比',
            'weixin_buy_member_cnt' => '微信参与客户数',
            'avg_buy_times_cnt' => '平均购买次数',
            'avg_buy_days' => '平均回购周期',
        );
        
        base_kvstore::instance('taocrm')->fetch('dashboard_check_tags', $check_tags);
        if($check_tags){
            $check_tags = json_decode($check_tags, true);
        }else{
            $check_tags  = array(
                'weixin_member_cnt','total_member_cnt',
                'once_buy_member_cnt','more_buy_member_cnt',
                'total_amount','avg_member_amount',
                'total_order_cnt','avg_order_amount',
                'total_marketing_member_cnt','total_marketing_buy_member_cnt',
                'active_roi','weixin_buy_member_cnt',
            );
        }

        $render = &$this->render;
        $render->pagedata['today'] = date('Y-m-d').' 星期'.$week_name[date('w')];
        $render->pagedata['check_tags'] = $check_tags;
        $render->pagedata['run_data_tags'] = $run_data_tags;
        $render->pagedata['month1'] = $month1;
        $render->pagedata['month2'] = $month2;
        $render->pagedata['hot_goods'] = $hot_goods;
        $render->pagedata['hot_members'] = $hot_members;
        $render->pagedata['month_to'] = date('Y-m-d',$curr_date);
        $render->pagedata['month_from'] = date('Y-m-d',strtotime('-12 month',$curr_date));
        $render->pagedata['month_to_1'] = $month_to_1;
        $render->pagedata['month_from_1'] = $month_from_1;
        $render->pagedata['date_to'] = date('Y-m-d',$curr_date);
        $render->pagedata['date_from'] = date('Y-m-d',$date_from);
        $render->pagedata['month1_from'] = date('Y-m-d',$month1_from);
        $render->pagedata['month2_from'] = date('Y-m-d',$month2_from);
        $render->pagedata['shop_id'] = $shop_id;
        $render->pagedata['count_by'] = 'homePage';
        //$render->pagedata['summary'] = $summary;
        $render->pagedata['shops'] = $shops;

        //最近7天数据汇总
        $total_data = $this->get_total_data();
        
        //缓存的运营数据统计
        $cache_dashboard = $this->get_cache_dashboard();
        //修正总客户数
        $cache_dashboard['total_member_cnt'] = $total_data['members'];
        
        //当前执行的活动
        $active = $this->get_active();
		
        $render->pagedata['active'] = $active;
        $render->pagedata['total_data'] = $total_data;
        $render->pagedata['cache_dashboard'] = $cache_dashboard;
        return $render->fetch('admin/desktop/index.html');
    }
    
    //获取执行中的活动
    public function get_active()
    {
        $active = array(
            'run'=>array(),
            'wait'=>array(),
            'end'=>array(),
        );
        $db = $this->db;
        $sql = "select active_id,left(active_name,15) as title,valid_num as send_num,if(isnull(exec_time),'-',from_unixtime(exec_time)) as send_time from sdb_market_active where is_active='finish' order by active_id desc limit 3";
        $rs = $db->select($sql);
        $active['end'] = $rs;
        
        $sql = "select active_id,left(active_name,15) as title,valid_num as send_num,from_unixtime(create_time) as send_time from sdb_market_active where is_active in ('sel_member','sel_template','wait_exec') order by active_id desc limit 3";
        $rs = $db->select($sql);
        $active['wait'] = $rs;
        
        $sql = "select plugin_name as title,0 as send_num,if(last_run_time=0 ,'-',from_unixtime(last_run_time)) as send_time,worker from sdb_plugins_plugins  where end_time>=".time()." limit 3";
        $rs = $db->select($sql);
        $active['run'] = $rs;
        
        $sql = "select worker,sum(sms_count) as send_num from sdb_plugins_log where start_time>=".strtotime(date('Y-m-d'))." group by worker";
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                $worker_send_sms[$v['worker']] = $v['send_num'];
            }
            
            foreach($active['run'] as $k=>$v){
                if(isset($worker_send_sms[$v['worker']])){
                    $active['run'][$k]['send_num'] = $worker_send_sms[$v['worker']];
                }
            }
        }
        
        return $active;
    }
    
    //缓存的运营数据统计
    function get_cache_dashboard()
    {
        $db = $this->db;
        //查询首页缓存数据
        $sql = "select * from sdb_taocrm_cache_dashboard order by id desc";
        $rs_cache_dashboard = $db->selectrow($sql);
        $rs_cache_dashboard['active_roi'] = '0';
        
        //查询绑定的淘宝店铺数
        $sql = "select count(*) as total from sdb_ecorder_shop where node_id is not null and node_id!=''";
        $rs = $db->selectrow($sql);
        $rs_cache_dashboard['bind_shop_cnt'] = $rs['total'];
        
        //查询店铺数
        $sql = "select count(*) as total from sdb_ecorder_shop";
        $rs = $db->selectrow($sql);
        $rs_cache_dashboard['unbind_shop_cnt'] = $rs['total'] - $rs_cache_dashboard['bind_shop_cnt'];
        
        return $rs_cache_dashboard;
    }
    
    //最近7天数据汇总
    function get_total_data()
    {
        $db = $this->db;
        $total_data = array(
            'members' => 0,
            'orders' => 0,
            '7_new_members' => 0,
            '7_new_paid_members' => 0,
            '7_old_paid_members' => 0,
            '7_sms_count' => 0,
            
            'active_num' => 0,
            'active_succ_num' => 0,
            'active_fail_num' => 0,
        );
        
        $today_time = strtotime(date('Y-m-d'));
        $start_time = strtotime('-7 days', $today_time);
        $this->render->pagedata['start_time'] = date('Y-m-d',$start_time);
        
        $sql = "select count(*) as total from sdb_market_active where exec_time>=$today_time and is_active='finish' ";
        $rs = $db->selectrow($sql);
        $total_data['active_num'] += $rs['total'];
        
        $sql = "select count(*) as total from sdb_market_active_member as b
                left join sdb_market_active as a on a.active_id=b.active_id
                where a.exec_time>=$today_time and a.is_active='finish' and b.issend=1 ";
        $rs = $db->selectrow($sql);
        $total_data['active_succ_num'] += $rs['total'];
        
        $sql = "select count(*) as total from sdb_market_active_member as b
                left join sdb_market_active as a on a.active_id=b.active_id
                where a.exec_time>=$today_time and a.is_active='finish' and b.issend=0 ";
        $rs = $db->selectrow($sql);
        $total_data['active_fail_num'] += $rs['total'];
        
        $total_data['members'] = kernel::single('taocrm_system')->getSystemMemberTotal();
        
        $sql = "select count(*) as total from sdb_ecorder_orders";
        $rs = $db->selectrow($sql);
        $total_data['orders'] = $rs['total'];
        
        $sql = "select count(*) as total from sdb_taocrm_members where order_first_time>=$start_time ";
        $rs = $db->selectrow($sql);
        $total_data['7_new_members'] = $rs['total'];
        
        $sql = "select count(*) as total from sdb_taocrm_sms_log where send_time>=$start_time and status='succ' ";
        $rs = $db->selectrow($sql);
        $total_data['7_sms_count'] = $rs['total'];
        
        $sql = "select count(distinct member_id) as total from sdb_ecorder_orders where createtime>=$start_time and pay_status='1' ";
        $rs = $db->selectrow($sql);
        $total_data['7_paid_members'] = $rs['total'];
        
        $sql = "select count(distinct a.member_id) as total from sdb_ecorder_orders as a
                left join sdb_taocrm_members as b on a.member_id=b.member_id
                where a.createtime>=$start_time and a.pay_status='1' and b.order_first_time>=$start_time ";
        $rs = $db->selectrow($sql);
        $total_data['7_new_paid_members'] = $rs['total'];
        $total_data['7_old_paid_members'] = $total_data['7_paid_members'] - $total_data['7_new_paid_members'];
        
        return $total_data;
    }

    /**
     * 近3个月客户成功购买金额排名
     */
    protected function lastThreeBuyAmountTop($shopId)
    {
        $threeMonthTime = 90 * 86400;
        $connect = kernel::single('taocrm_middleware_connect');
        $params = array();
        $params['shopId'] = $shopId;
        $params['beginTime'] = strtotime(date("Y-m-d 00:00:00")) - $threeMonthTime;
        $params['endTime'] = time();
        $params['top'] = 7;
        //$result = json_decode($connect->TopAmountMemberIdByTime($params), true);
        $result = $connect->TopAmountMemberIdByTime($params);
        $data = array();
        if ($result) {
        	/*
            $members = array_keys($result);
            $memberModel = $this->app->model('members');
            $memberInfo = $memberModel->getList('*', array('member_id|in' => $members));
            $memberInfoOfMemberIds = array();
            foreach ($memberInfo as $v) {
                $memberInfoOfMemberIds[$v['member_id']] = $v;
            }
            */
            $i = 0;
            foreach ($result as $k => $v) {
                $data[$i]['uname'] = $v['uname'];
                $data[$i]['month3_finish_orders'] = $v['finish_orders'];
                $data[$i]['month3_finish_amount'] = $v['finish_amount'];
                $i++;
            }
        }
        return $data;
    }

    //根据时间获取新老客户统计信息
    protected function getNewOldMemberAnalysis($shopId, $beginTime, $endTime)
    {
        $connect = kernel::single('taocrm_middleware_connect');
        $params = array(
            'shopId' => $shopId,
            'beginTime' => $beginTime,
            'endTime' => $endTime,
        	'ctl' => $_GET['ctl']
        );
       	
        return $connect->NewOldMemberAnalysis($params);
        
    }

    protected function lastThreeTopSale($shopId)
    {
        $threeMonthTime = 90 * 86400;
        $connect = kernel::single('taocrm_middleware_connect');
        $params = array();
        $params['shopId'] = $shopId;
        $params['beginTime'] = strtotime(date("Y-m-d 00:00:00")) - $threeMonthTime;
        $params['endTime'] = time();
        $params['top'] = 10;
        //$result = json_decode($connect->TopSale($params), true);
        $result = $connect->TopSale($params);
        
        $data = array();
        if ($result) {
            $shopModel = app::get('ecorder')->model('shop');
            $shopInfo = $shopModel->dump(array('shop_id' => $shopId));
            $shopType = '';
            if ($shopInfo['shop_type'] == 'taobao') {
                $shopType = 'taobao';
            }
            $goods = array_keys($result);
            $shopGoodsModel = app::get('ecgoods')->model('shop_goods');
            $shopGoodsInfo = $shopGoodsModel->getList('*', array('goods_id' => $goods,'no_use'=>'0'));
            $shopGoodsInfoOfGoods = array();
            foreach ($shopGoodsInfo as $v) {
                $shopGoodsInfoOfGoods[$v['goods_id']] = $v;
            }
            $i = 0;
            foreach ($result as $k => $v) {
                if( ! $shopGoodsInfoOfGoods[$k]['name']) continue;
                if($i >= 7) break;
                $data[$i]['name'] = $shopGoodsInfoOfGoods[$k]['name'];
                $data[$i]['short_name'] = mb_substr($data[$i]['name'], 0, 21, 'utf-8');
                $data[$i]['month3_paid_num'] = $v['paid_num'];
                $data[$i]['month3_paid_amount'] = $v['paid_amount'];
                $data[$i]['pic_url'] = $shopGoodsInfoOfGoods[$k]['pic_url'];
                $data[$i]['outer_id'] = $shopGoodsInfoOfGoods[$k]['outer_id'];
                $data[$i]['shop_type'] = $shopType;
                $i++;
            }
        }
        return $data;
    }

    /**
     * 获取桌面widgets的html内容
     * @param null
     * @return string html内容
     */
    public function get_html_old()
    {
        $shop_id = $_GET['shop_id'];
        $curr_date = strtotime(date('Y-m-d'));
        if(!$shop_id)
        base_kvstore::instance('ecorder')->fetch('default_shop_id',$shop_id);

        $render = $this->render;

        $shopObj = &app::get('ecorder')->model('shop');
        $shops = $shopObj->getList('*');
        $date_to = $curr_date;
        $date_from = strtotime('-30 days',$curr_date);
        if(!$shop_id) $shop_id = $shops[0]['shop_id'];

        $summary['curr'] = $this->get_summary_data($date_from,$date_to,$shop_id);

        //默认店铺的统计数据
        $c_date_to = strtotime('-7 days',$curr_date);
        $c_date_from = strtotime('-14 days',$curr_date);
        $summary['prev'] = $this->get_summary_data($c_date_from,$c_date_to,$shop_id);
        foreach($summary['curr'] as $k=>$v){
            if($v>$summary['prev'][$k]) {
                $summary['curr'][$k.'_trend'] = 'up_bg';
            }elseif($v<$summary['prev'][$k]) {
                $summary['curr'][$k.'_trend'] = 'down_bg';
            }
        }

        $render->pagedata['month_to'] = date('Y-m-d',$curr_date);
        $render->pagedata['month_from'] = date('Y-m-d',strtotime('-12 month',$curr_date));

        $render->pagedata['date_to'] = date('Y-m-d',$curr_date);
        $render->pagedata['date_from'] = date('Y-m-d',$date_from);

        $render->pagedata['shop_id'] = $shop_id;
        $render->pagedata['summary'] = $summary;
        $render->pagedata['shops'] = $shops;

        return $render->fetch('admin/desktop/index.html');
    }

    function get_summary_data($date_from,$date_to,$shop_id){
        $db = kernel::database();

        $c_unit = 7;
        kernel::single('taocrm_analysis_cache')->create_tree($c_unit,$shop_id);

        $sql = "SELECT * FROM sdb_taocrm_cache_tree WHERE date_from=$date_from AND date_to=$date_to AND shop_id='$shop_id'";
        $rs = $db->selectRow($sql);//var_dump($sql);
        return $rs;

        /*
         $sql = "select
         count(order_id) as orders,sum(cost_item) as amount,count(distinct member_id) as members
         from sdb_ecorder_orders
         where shop_id='$shop_id' and (createtime between $date_from and $date_to)
         ";
         $rs = $db->selectRow($sql);
         if($rs) {
         $summary['curr']['total_orders'] = $rs['orders'];
         $summary['curr']['total_amount'] = $rs['amount'];
         $summary['curr']['total_members'] = $rs['members'];
         }

         $sql = "select
         count(order_id) as orders,
         sum(cost_item) as amount,
         count(distinct member_id) as members
         from sdb_ecorder_orders
         where shop_id='$shop_id' and pay_status='1' and (createtime between $date_from and $date_to)
         ";
         $rs = $db->selectRow($sql);
         if($rs) {
         $summary['curr']['paid_amount'] = $rs['amount'];
         $summary['curr']['paid_per_amount'] = $summary['curr']['paid_amount']/$rs['orders'];
         $summary['curr']['paid_per_user_amount'] = $summary['curr']['paid_amount']/$rs['members'];
         $summary['curr']['unpaid_amount'] = $summary['curr']['total_amount'] - $rs['amount'];
         }

         $sql = "select
         member_id
         from sdb_taocrm_member_analysis
         where shop_id='$shop_id' and (first_buy_time between $date_from and $date_to)
         ";
         $rs = $db->select($sql);
         if($rs) {
         foreach($rs as $v)
         $new_members[] = $v['member_id'];
         $summary['curr']['new_members'] = count($new_members);
         $summary['curr']['old_members'] = $summary['curr']['total_members'] - $summary['curr']['new_members'];
         }

         $sql = "select
         sum(cost_item) as amount
         from sdb_ecorder_orders
         where shop_id='$shop_id' and (createtime between $date_from and $date_to) and member_id in (".implode(',',$new_members).")
         ";
         $rs = $db->selectRow($sql);
         if($rs) {
         $summary['curr']['new_amount'] = $rs['amount'];
         $summary['curr']['old_amount'] = $summary['curr']['total_amount'] - $rs['amount'];
         }

         return $summary['curr'];
         */
    }

    /**
     * 获取页面的当前widgets的classname的名称
     * @param null
     * @return string classname
     */
    public function get_className()
    {
        return " l-1 flt";
    }

    /**
     * 显示的位置和宽度
     * @param null
     * @return string 宽度数据
     */
    public function get_width()
    {
        return "l-1";
    }
}