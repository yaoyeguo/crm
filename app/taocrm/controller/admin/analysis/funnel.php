<?php
class taocrm_ctl_admin_analysis_funnel extends desktop_controller {
    var $workground = 'taocrm.analysts';

    public function __construct($app){
        parent::__construct($app);
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

    public function index(){

        $args['shop_id'] = $this->shop_id;
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id'])
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
    
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $this->pagedata['r']= $r;
        $this->pagedata['f']= $f;
        $this->pagedata['r_label']= $r_label;
        $this->pagedata['f_label']= $f_label;
        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '成交过程';
        $this->pagedata['service'] = 'taocrm_analysis_rfm';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['chart_data'] = $this->getNewData($args);
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_funnel&act=index';
        $this->pagedata['member_url'] = 'index.php?app=taocrm&ctl=admin_member&act=index&filter_type=rfm';
        $this->page('admin/analysis/funnel.html');
    }
    
    public function getNewData($args)
    {
        $connect = kernel::single('taocrm_middleware_connect');
        $filter = array();
        $filter['shopId'] = $args['shop_id'];
        $filter['beginTime'] = strtotime($args['date_from']);
        $filter['endTime'] = strtotime($args['date_to']) + 86400;
        $result = $connect->ListByOrderStatus($filter);
        $data = array();
        if ($result) {
        	$analysis_data = $result;
            $chart_data = '{"chart": {"showborder": "0","bgcolor": "ffffff","manageresize": "1","caption": "销售漏斗","subcaption": "'.$args['date_from'].' ~ '.$args['date_to'].'","decimals": "1","basefontsize": "12","issliced": "1","usesameslantangle": "1","ishollow": "0","labeldistance": "5"},"data": [{"label": "下单数","value": "'.$analysis_data['total_orders'].'"},{"label": "付款订单数","value": "'.$analysis_data['paid_orders'].'"},{"label": "成功交易订单","value": "'.$analysis_data['finish_orders'].'"},{"label": "关闭订单数","value": "'.$analysis_data['dead_orders'].'"}],"styles": {"definition": [{"type": "font","name": "captionFont","size": "15"}],"application": [{"toobject": "CAPTION","styles": "captionFont"}]}}';
            $data = array('chart_data'=>$chart_data,'analysis_data'=>$analysis_data);
        }
        return $data;
    }
    
    function get_data(&$args){
    
        $db = kernel::database();
        if(strtotime($args['date_from']) == strtotime($args['date_to'])){
        	$args['date_to'] = date('Y-m-d',strtotime($args['date_to'])+86400);
        }
        $where .= ' where (createtime between '.strtotime($args['date_from']).' and '.strtotime($args['date_to']).' ) ';
        $where .= ' and (shop_id ="'.($args['shop_id']).'") ';
        
        $sql = "select count(*) as total,sum(total_amount) as amount from sdb_ecorder_orders $where";
        $rs = $db->selectrow($sql);
        $analysis_data['total_orders'] = $rs['total'];
        $analysis_data['total_amount'] = $rs['amount'];
        
        $sql = "select count(*) as total,sum(payed) as amount,sum(pay_time-createtime) as times from sdb_ecorder_orders  $where and pay_status='1' and pay_time>0 ";
        $rs = $db->selectrow($sql);
        $analysis_data['paid_orders'] = $rs['total'];
        $analysis_data['paid_amount'] = $rs['amount'];
        $analysis_data['paid_avg_time'] = round($rs['times']/($rs['total']*3600),2);
        
        //已发货订单
        $sql = "select count(*) as total,sum(payed) as amount,sum(delivery_time-createtime) as times from sdb_ecorder_orders  $where and pay_status='1' and ship_status='1' and delivery_time>0 ";
        $rs = $db->selectrow($sql);
        $analysis_data['ship_orders'] = $rs['total'];
        $analysis_data['ship_amount'] = $rs['amount'];
        $analysis_data['ship_avg_time'] = round($rs['times']/($rs['total']*3600),2);

        //已完成订单
        $sql = "select count(*) as total,sum(payed) as amount,sum(finish_time-createtime) as times from sdb_ecorder_orders  $where and pay_status='1' and status='finish' and finish_time>0 ";
        $rs = $db->selectrow($sql);
        $analysis_data['finish_orders'] = $rs['total'];
        $analysis_data['finish_amount'] = $rs['amount'];
        $analysis_data['finish_avg_time'] = round($rs['times']/($rs['total']*86400),2);
        
        $sql = "select count(*) as total,sum(payed) as amount from sdb_ecorder_orders  $where and status='dead' ";
        $rs = $db->selectrow($sql);
        $analysis_data['dead_orders'] = $rs['total'];
        $analysis_data['dead_amount'] = $rs['amount'];

        $chart_data = '{"chart": {"showborder": "0","bgcolor": "ffffff","manageresize": "1","caption": "销售漏斗","subcaption": "'.$args['date_from'].' ~ '.$args['date_to'].'","decimals": "1","basefontsize": "12","issliced": "1","usesameslantangle": "1","ishollow": "0","labeldistance": "5"},"data": [{"label": "下单数","value": "'.$analysis_data['total_orders'].'"},{"label": "付款订单数","value": "'.$analysis_data['paid_orders'].'"},{"label": "成功交易订单","value": "'.$analysis_data['finish_orders'].'"},{"label": "关闭订单数","value": "'.$analysis_data['dead_orders'].'"}],"styles": {"definition": [{"type": "font","name": "captionFont","size": "15"}],"application": [{"toobject": "CAPTION","styles": "captionFont"}]}}';

        return array('chart_data'=>$chart_data,'analysis_data'=>$analysis_data);
    }
    
    public function get_filter_member($filter){
        
        $members = array();
        $shop_id = $filter['shop_id'];
        $r = explode('_',$filter['r']);
        $f = explode('_',$filter['f']);
        
        if($r[1]==0 && $r[0]>0) {
            $where .= ' AND datediff(now(),FROM_UNIXTIME(last_buy_time))>='.$r[0].' ';
        }elseif($r[1]>0){
            $where .= ' AND datediff(now(),FROM_UNIXTIME(last_buy_time))>='.$r[0].' 
                        AND datediff(now(),FROM_UNIXTIME(last_buy_time))<='.$r[1].' ';
        }
            
        if($f[1]==0 && (int)$f[0]>0) {
            $where .= ' AND total_orders>='.$f[0].' ';
        }elseif($f[1]>0){
            $where .= ' AND total_orders>='.$f[0].' 
                        AND total_orders<='.$f[1].' ';
        }
    
        $sql = "
            SELECT member_id
            FROM sdb_taocrm_member_analysis
            WHERE shop_id='$shop_id' $where
        ";
        $rs = kernel::database()->select($sql);
        if($rs){
            foreach($rs as $v) {
                $members[] = $v['member_id'];
            }
        }//var_dump($sql);
        
        return array('member_id'=>$members);
    }
}

