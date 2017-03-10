<?php
class taocrm_ctl_admin_analysis_tree extends desktop_controller 
{
    var $workground = 'taocrm.analysts';
    static $middleware_conn = null;

    public function __construct($app)
    {
        parent::__construct($app);
        
        if(self::$middleware_conn == null){
            self::$middleware_conn = kernel::single('taocrm_middleware_connect');
        }
        
        $timeBtn = array(
            'today' => date("Y-m-d"),
            'yesterday' => date("Y-m-d", time()-86400),
            'this_month_from' => date("Y-m-" . 01),
            'this_month_to' => date("Y-m-d"),
            'this_week_from' => date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400),
            'this_week_to' => date("Y-m-d"),
            'sevenday_from' => date("Y-m-d", time()-6*86400),
            'sevenday_to' => date("Y-m-d"),
        );
        $this->pagedata['timeBtn'] = $timeBtn;

        //初始化统计时间段
        if($_POST['date_from'] && $_POST['date_to']){
            base_kvstore::instance('analysis')->
                store('analysis_date_from',$_POST['date_from']);
            base_kvstore::instance('analysis')->
                store('analysis_date_to',$_POST['date_to']);
        }
        if($_POST['shop_id']) 
            base_kvstore::instance('analysis')->store('analysis_shop_id',$_POST['shop_id']);
            base_kvstore::instance('analysis')->fetch('analysis_shop_id',$this->shop_id);
        base_kvstore::instance('analysis')->
            fetch('analysis_date_from',$this->date_from);
        base_kvstore::instance('analysis')->
            fetch('analysis_date_to',$this->date_to);
        if(!$this->date_from) 
            $this->date_from = date('Y-m-d',(time()-86400*7));
        if(!$this->date_to)
            $this->date_to = date('Y-m-d',(time()-86400*1));
    }

    public function index()
    {
        @set_time_limit(300);

        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $curr_date = strtotime(date('Y-m-d'));
        $shop_id = $this->shop_id;
        $c_unit = intval($_POST['c_unit']);
        if($c_unit ==0) $c_unit = 7;
        
        //判断缓存是否存在
        //kernel::single('taocrm_analysis_cache')->create_tree($c_unit,$shop_id);
        
        $c_units = array(
            7=>'最近一周',
            30=>'最近一月',
            90=>'最近一季度',
            180=>'最近半年'
        );
        
        $rs = &app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$shop_id) $shop_id = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
    
        //最近周期数据
        $date_to = $curr_date;
        $date_from = strtotime('-'.$c_unit.' days',$curr_date);
        
        $rs = $this->get_summary_data($date_from,$date_to,$shop_id);
        $summary['curr'] = $rs;
        //上一个周期数据
        $c_date_to = strtotime('-'.($c_unit).' days',$curr_date);
        $c_date_from = strtotime('-'.($c_unit*2).' days',$curr_date);
        $rs = $this->get_summary_data($c_date_from,$c_date_to,$shop_id);
        $summary['prev'] = $rs;
        foreach($summary['curr'] as $k=>$v){
            if($v>$summary['prev'][$k]) {
                $summary['curr'][$k.'_percent'] = round(100*($v-$summary['prev'][$k])/$summary['prev'][$k],2);
                if($summary['curr'][$k.'_percent']>100)
                    $summary['curr'][$k.'_percent'] = intval($summary['curr'][$k.'_percent']);
                $summary['curr'][$k.'_trend'] = 'up_bg';
            }elseif($v<$summary['prev'][$k]) {
                $summary['curr'][$k.'_percent'] = round(-100*($v-$summary['prev'][$k])/$summary['prev'][$k],2);
                if($summary['curr'][$k.'_percent']>100)
                    $summary['curr'][$k.'_percent'] = intval($summary['curr'][$k.'_percent']);
                $summary['curr'][$k.'_trend'] = 'down_bg';
            }
        }
        
        $this->pagedata['summary'] = $summary;

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $shop_id;
        $this->pagedata['path']= '运营决策';
        $this->pagedata['service'] = 'taocrm_analysis_rfm';
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_tree&act=index';
        $this->pagedata['member_url'] = 'index.php?app=taocrm&ctl=admin_member&act=index&filter_type=rfm';
        $this->pagedata['date_from'] = date('Y-m-d',$date_from);
        $this->pagedata['date_to'] = date('Y-m-d',$date_to);
        $this->pagedata['c_unit'] = $c_unit;
        $this->pagedata['c_units'] = $c_units;
        $this->page('admin/analysis/tree.html');
    }
    
    private function get_summary_data($date_from,$date_to,$shop_id)
    {
        //if(1 == 1){
            $params = array(
                'shopId'=>$shop_id,
                'startDate'=>$date_from,
                'endDate'=>$date_to
            );

            /*
            $data = kernel::single('taocrm_middleware_connect')->getDecisionTree($params);
            $data = json_decode($data, 1);
            if($data['rsp'] != 'succ') return false;
            $data = $data['info']['data'];
            */
            
            //调用内存数据库
            $filter = array();
                $filter['shopId'] = $shop_id;
                $filter['beginTime'] = $date_from;
                $filter['endTime'] = $date_to;
                $filter['ctl'] = $_GET['ctl'];
                $rs = self::$middleware_conn->NewOldMemberAnalysis($filter);
                
                //$data = json_decode($result, 1);
                //self::_key_maps($data);
                //echo('<pre>');var_dump($data);
            /*
            $rs = array(
                'total_items'=>$data['newItems'] + $data['oldItems'],
                'total_orders'=>$data['newOrders'] + $data['oldOrders'],
                'total_amount'=>$data['newAmount'] + $data['oldAmount'],
                'total_per_amount'=>0,
                'total_members'=>$data['newMembers'] + $data['oldMembers'],
                'new_members'=>$data['newMembers'],
                'new_orders'=>$data['newOrders'],
                'new_amount'=>$data['newAmount'],
                'new_items'=>$data['newItems'],
                'new_per_amount'=>0,
                'new_per_items'=>0,
                'new_per_price'=>0,
                'old_members'=>$data['oldMembers'],
                'old_amount'=>$data['oldAmount'],
                'old_orders'=>$data['oldOrders'],
                'old_items'=>$data['oldItems'],
                'old_per_amount'=>0,
                'old_per_items'=>0,
                'old_per_price'=>0,
            );
            */
            
            $rs['old_members'] = $rs['total_members'] - $rs['new_members'];
            $rs['old_orders'] = $rs['total_orders'] - $rs['new_orders'];
            $rs['old_items'] = $rs['total_items'] - $rs['new_items'];
            $rs['old_amount'] = $rs['total_amount'] - $rs['new_amount'];
            $rs['total_per_amount'] = 0;
            $rs['new_per_amount'] = 0;
            $rs['new_per_items'] = 0;
            $rs['new_per_price'] = 0;
            
            if($rs['new_members']>0){
                $rs['new_per_amount'] = round($rs['new_amount']/$rs['new_members'],2);
                $rs['new_per_items'] = round($rs['new_items']/$rs['new_members'],2);
            }
            if($rs['new_items']>0){
                $rs['new_per_price'] = round($rs['new_amount']/$rs['new_items'],2);
            }
            if($rs['old_members']>0){
                $rs['old_per_amount'] = round($rs['old_amount']/$rs['old_members'],2);
                $rs['old_per_items'] = round($rs['old_items']/$rs['old_members'],2);
            }
            if($rs['old_items']>0){
                $rs['old_per_price'] = round($rs['old_amount']/$rs['old_items'],2);
            }
            //var_dump($rs);
            /*
        }else{
            $db = kernel::database();
            $sql = "SELECT * FROM sdb_taocrm_cache_tree WHERE date_from=$date_from AND date_to=$date_to AND shop_id='$shop_id'";
            $rs = $db->selectRow($sql);
            //var_dump($rs);
        }
        */
        return $rs;
        
        /*
        $sql = "select 
        sum(item_num) as items,count(order_id) as orders,sum(cost_item) as amount,count(distinct member_id) as members 
        from sdb_ecorder_orders 
        where shop_id='$shop_id' and (createtime between $date_from and $date_to)
        ";
        $rs = $db->selectRow($sql);
        if($rs) {
            $summary['curr']['total_orders'] = $rs['orders'];
            $summary['curr']['total_amount'] = $rs['amount'];
            $summary['curr']['total_members'] = $rs['members'];
            $summary['curr']['total_items'] = $rs['items'];
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
        
        $sql = "select count(member_id) as new_members from sdb_taocrm_member_analysis 
        where shop_id='$shop_id' and (first_buy_time between $date_from and $date_to) ";
        $rs = $db->selectRow($sql);
        if($rs) {
            $new_members = $rs['new_members'];
            $summary['curr']['new_members'] = $new_members;
        }
        
        //分页处理
        $sql = "select 
        sum(a.cost_item) as amount,
        sum(a.item_num) as items,
        count(a.order_id) as orders
        from sdb_ecorder_orders as a
        left join sdb_taocrm_member_analysis as b on a.member_id=b.member_id
        where a.shop_id='$shop_id' and (a.createtime between $date_from and $date_to) 
        and (b.first_buy_time between $date_from and $date_to) 
        ";//die($sql);
        $rs = $db->selectRow($sql);
        if($rs) {
            $summary['curr']['new_items'] = $rs['items'];
            $summary['curr']['new_orders'] = $rs['orders'];
            $summary['curr']['new_amount'] = $rs['amount'];
        }
        
        $summary['curr']['new_per_amount'] = $summary['curr']['new_amount']/$summary['curr']['new_members'];
        $summary['curr']['new_per_items'] = $summary['curr']['new_items']/$summary['curr']['new_members'];
        $summary['curr']['new_per_price'] = $summary['curr']['new_amount']/$summary['curr']['new_items'];
        $summary['curr']['old_members'] = $summary['curr']['total_members'] - $summary['curr']['new_members'];
        $summary['curr']['old_amount'] = $summary['curr']['total_amount'] - $summary['curr']['new_amount'];
        $summary['curr']['old_orders'] = $summary['curr']['total_orders'] - $summary['curr']['new_orders'];
        $summary['curr']['old_items'] = $summary['curr']['total_items'] - $summary['curr']['new_items'];
        $summary['curr']['old_per_amount'] = $summary['curr']['old_amount']/$summary['curr']['old_members'];
        $summary['curr']['old_per_items'] = $summary['curr']['old_items']/$summary['curr']['old_members'];
        $summary['curr']['old_per_price'] = $summary['curr']['old_amount']/$summary['curr']['old_items'];
        */
        
        return $summary;
    }
    
    private function _key_maps(&$arr, $count_by='DATE')
    {
        foreach($arr as $k=>&$v){
            if(is_array($v)) {
                $arr[$k]['key']=$k;
                self::_key_maps($v);
                continue;
            }        
            if($k=='NewAmountCount') $arr['newAmount']=$v;
            if($k=='NewItemCount') $arr['newItems']=$v;
            if($k=='NewMemberCount') $arr['newMembers']=$v;
            if($k=='NewPayOrderCount') $arr['newOrders']=$v;
            /*
            if($k=='NewPayAmountCount') $arr['1111']=$v;
            
            if($k=='NewUnPayMemberCount') $arr['1111']=$v;
            if($k=='NewUnpayAmountCount') $arr['1111']=$v;
            */
            
            if($k=='OldAmountCount') $arr['oldAmount']=$v;
            if($k=='OldItemCount') $arr['oldItems']=$v;
            if($k=='OldMemberCount') $arr['oldMembers']=$v;
            if($k=='OldPayOrderCount') $arr['oldOrders']=$v;
            /*
            if($k=='OldPayAmountCount') $arr['1111']=$v;
            
            if($k=='OldUnPayMemberCount') $arr['1111']=$v;
            if($k=='OldUnpayAmountCount') $arr['1111']=$v;
            */
        }
    }
    
}

