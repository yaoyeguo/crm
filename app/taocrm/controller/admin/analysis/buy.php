<?php
class taocrm_ctl_admin_analysis_buy extends desktop_controller 
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
        $kv = base_kvstore::instance('analysis');
        if($_POST['date_from'] && $_POST['date_to']){
            $kv->store('analysis_date_from',$_POST['date_from']);
            $kv->store('analysis_date_to',$_POST['date_to']);
        }
        if($_POST['shop_id']) $kv->store('analysis_shop_id',$_POST['shop_id']);
        $kv->fetch('analysis_shop_id',$this->shop_id);
        $kv->fetch('analysis_date_from',$this->date_from);
        $kv->fetch('analysis_date_to',$this->date_to);
        if(!$this->date_from) $this->date_from = date('Y-m-d',(time()-86400*7));
        if(!$this->date_to) $this->date_to = date('Y-m-d',(time()-86400*1));
    }
    
	function index()
    {
	 	$args['shop_id'] = $this->shop_id;
        $rs = &app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        
        //调用内存数据库
        $filter = array();
            //$filter['orderCountLimit'] = '1,2,3,4,5,6';
            $filter['shopId'] = $args['shop_id'];
            $filter['ctl'] = $_GET['ctl'];
            $filter['beginTime'] =strtotime($args['date_from']);
            $filter['endTime'] = strtotime($args['date_to']) + 86400;
            $result = self::$middleware_conn->BuyFreqByTime($filter);
            
            foreach($result as $v){
            	if($v['buy_freq'] < 7){
            		$v['key'] = $v['buy_freq'];
            	}else{
            		$v['key'] = '>';
            	}
            	$arr[$v['buy_freq']] = $v;
            }
            
            ksort($arr);
            //$all_sales_data['analysis_data'] = json_decode($result, 1);
            $all_sales_data['analysis_data'] = $arr;
            ksort($all_sales_data['analysis_data']);
            self::_key_maps($all_sales_data['analysis_data']);
            //var_dump($all_sales_data);
        //将数据缓存到kv，用于图形报表
        base_kvstore::instance('analysis')->store('buy_freq',$all_sales_data);
        
        /*
        if(OPEN_MEMO_DATA == true){
            $result = kernel::single('taocrm_analysis_day')->get_buy_freq($args);
        }else{
            $db = kernel::database();
            $sql = "SELECT buy_freq as key, count( distinct member_id ) total_members, sum( total_amount ) total_amount, sum( total_orders ) total_orders
                FROM sdb_taocrm_member_analysis
                WHERE buy_freq >0 and shop_id='".$args['shop_id']."'
                GROUP BY buy_freq";
            $result = $db->select($sql);
        }
        */

        $result = $all_sales_data['analysis_data'];
        $data = array();
        foreach($arr as $k=>$v){
        	if($v['key'] == '>'){
	        	$total_amount += $v['total_amount'];
	        	$mem_num += $v['total_members'];
	        	$order_num += $v['total_orders'];
        	}else{
        		$data[$k] = $v;
        		$data[$k]['key'] = $v['key'];
        		$data[$k]['avg_mem'] = round(($v['total_amount'] / $v['total_members']),2);
        		$data[$k]['avg_order'] = round(($v['total_amount'] / $v['total_orders']),2);
        	}
        	$total_memory += $v['total_amount'];
        	$total_mem += $v['total_members'];
        }
        if($total_amount && $mem_num && $order_num){
			$data[] = array('total_amount'=>$total_amount,'total_members'=>$mem_num,'total_orders'=>$order_num,'key'=>'6次以上',
			'avg_mem' => round(($total_amount / $mem_num),2),'avg_order'=>round(($total_amount/ $order_num),2));
        }
		$analysis_data = array();
		foreach($data as $k=>$v){
			$v['mem_p'] = round(($v['total_members'] * 100 / $total_mem),2);
			$v['total_p'] = round(($v['total_amount'] * 100 / $total_memory),2);
			$analysis_data[] = $v;
		}
        //echo('<pre>');var_dump($data);
		
        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
		$this->pagedata['path']= '购买频次';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
		$this->pagedata['analysis_data'] = $analysis_data;
		$this->pagedata['total_mem'] = $total_mem;
		$this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_buy&act=index';
		$this->page('admin/analysis/sales/freq.html');
	}
	
    private function _key_maps(&$arr, $count_by='DATE')
    {
        foreach($arr as $k=>&$v){
            //if($k=='>') $k=9;
            if(is_array($v)) {
                $arr[$k]['key']=$k;
                self::_key_maps($v);
                continue;
            }        
            if($k=='OrderCount') $arr['total_orders']=$v;
            if($k=='AmountCount') $arr['total_amount']=$v;
            if($k=='MemberCount') $arr['total_members']=$v;

            if($k=='PayOrder') $arr['paid_orders']=$v;
            if($k=='PayAmount') $arr['paid_amount']=$v;
            if($k=='PayMember') $arr['paid_members']=$v;
            
            if($k=='FinishOrder') $arr['finish_orders']=$v;
            if($k=='FinishAmount') $arr['finish_amount']=$v;
            if($k=='FinishMember') $arr['finish_members']=$v;
        }
    }
    
}