<?php
class taocrm_ctl_admin_analysis_member extends desktop_controller {
    var $workground = 'taocrm.analysts';
    private static $middleware_conn = null;

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

    public function buy_times()
    {
        $args['shop_id'] = $this->shop_id;
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
    
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $args['count_by'] = $_POST['count_by'] ? $_POST['count_by'] : 'date'; 

        /*
        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $all_sales_data = $oAnalysisDay->get_member_buy_times($args);
        */
        
        //调用内存数据库
        $connect = $this->getConnect();
        $filter = array();
            $filter['orderCountLimit'] = '1,2,3,4,5,6';
            $filter['shopId'] = $args['shop_id'];
            $filter['beginTime'] = strtotime($args['date_from']);
            $filter['endTime'] =  strtotime($args['date_to']) + 86400;

            $filter['ctl'] = $_GET['ctl'];
            
            $result = $connect->BuyFreqByTime($filter);
            
            //数据转换
            $total_amount = 0;
            $total_orders = 0;
            $total_items = 0;
            $total_members = 0;
     		foreach($result as $v){
            	if($v['buy_freq'] < 7){
            		$v['key'] = $v['buy_freq'];
            		$arr[$v['buy_freq']] = $v;
            	}else{
            		$total_amount += $v['total_amount'];
            		$total_orders += $v['total_orders'];
            		$total_members += $v['total_members'];
            		$total_items += $v['total_items'];
            	}
            }
            if($total_amount || $total_orders || $total_members || $total_items){
	            $arr['>']['total_amount'] = $total_amount;
	            $arr['>']['total_orders'] = $total_orders;
	            $arr['>']['total_members'] = $total_members;
	            $arr['>']['total_items'] = $total_items;
            }
            //$all_sales_data['analysis_data'] = json_decode($result, 1);
            $all_sales_data['analysis_data'] = $arr;
//            echo "<pre>";
//            print_r($all_sales_data);
//            exit;
            ksort($all_sales_data['analysis_data']);
            
            //处理 “>” 符号
            if($all_sales_data['analysis_data']['>']){
	            $all_sales_data['analysis_data']['大于6'] = $all_sales_data['analysis_data']['>'];
	            unset($all_sales_data['analysis_data']['>']);    
            }        
            self::_key_maps($all_sales_data['analysis_data']);
            //echo('<pre>');var_dump($all_sales_data['analysis_data']);
        //计算合计
        foreach($all_sales_data['analysis_data'] as $k=>&$v) {
            $remain_members += $v['total_members'];
            $v['remain_members'] = $remain_members;
            foreach($v as $kk=>$vv) {
                if(is_numeric($vv)){
                    $all_sales_data['total_data'][$kk] += $vv;
                }
            }
        }
//        echo "<pre>";
//        print_r($all_sales_data);
//        exit;
        //$all_sales_data['total_orders']['sum_orders'] = $all_sales_data['total_orders']['OrderCount'] > 0 ? $all_sales_data['total_orders']['OrderCount'] : 0;
        //将数据缓存到kv
        base_kvstore::instance('analysis')->store('member_buy_times',$all_sales_data);

        $this->pagedata['sales_data'] = $all_sales_data['sales_data'];
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['compare_data'] = $all_sales_data['compare_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];
        $this->pagedata['funnel_data'] = $all_sales_data['funnel_data'];
        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '客户下单次数';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_member&act=buy_times';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/buy_times.html');
    }
    
    public function level()
    {
        $args['shop_id'] = $this->shop_id;
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
    
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $args['count_by'] = $_POST['count_by'] ? $_POST['count_by'] : 'date'; 
        
        //对比时间段
        $args['c_date_from'] = $_POST['c_date_from'];
        $args['c_date_to'] = date('Y-m-d',strtotime($args['c_date_from'])+strtotime($args['date_to'])-strtotime($args['date_from']));

//        $oAnalysisDay = kernel::single('taocrm_analysis_day');
//        $all_sales_data = $oAnalysisDay->get_member_level($args);
        $all_sales_data = $this->getMemberCountByShopId($args);

        $this->pagedata['sales_data'] = $all_sales_data['sales_data'];
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['compare_data'] = $all_sales_data['compare_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '客户等级';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['c_date_from'] = $args['c_date_from'];
        $this->pagedata['c_date_to'] = $args['c_date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_member&act=level';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/level.html');
    }
    
    public function getMemberCountByShopId($params)
    {
        if (empty($params)) {
            die ("visit error");
        }
        $connect = $this->getConnect();
        $memberLvModel = $connect->getDataModel('ecorder', 'shop_lv');
        $memberLvInfo = $memberLvModel->getList('lv_id,name', array('shop_id' => $params['shop_id']));
        $memberLvs = array();
        foreach ($memberLvInfo as $value) {
            $memberLvs[$value['lv_id']] = $value['name'];
        }
        $filter = array('shopId' => $params['shop_id']);;
        //$result = json_decode($connect->MemberInfoBySyslv($filter), true);
        $result = $connect->MemberInfoBySyslv($filter);
   		
//        echo "<pre>";
//        print_r($result);
        $data = array();
        if ($result) {
            $analysisData = array();
            $totalData = array();
            $i = 0;
            foreach ($result as $k => $v) {
                if ($k < 0 or ! $memberLvs[$k]) continue;
                $analysisData[$i]['lv_id'] = $k;
                $analysisData[$i]['total_members'] = $v['MemberCount'];
                $analysisData[$i]['total_amount'] = $v['AmountCount'];
                $analysisData[$i]['order'] = ($i + 1);
                $analysisData[$i]['lv_name'] = $memberLvs[$k];
                $totalData['total_members'] += $v['MemberCount'];
                $totalData['total_amount'] += $v['AmountCount'];
                $i++;
            }
            
            kernel::single('taocrm_analysis_day')->array_sort($analysisData,'lv_id','asc');
            
            $data = array('analysis_data' => $analysisData, 'total_data' => $totalData);
        }
       
        return $data;
    }

    public function old_new()
    {
        $args['shop_id'] = $this->shop_id;
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
    
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        //$args['count_by'] = kernel::single('taocrm_ctl_admin_analysis_sales')->chk_date_diff($args['date_from'],$args['date_to']); 
        $args['count_by'] = kernel::single('taocrm_ctl_admin_analysis_sales')->chk_date_diff($this->date_from,$this->date_to); 
        $filter = $args;
		$date = $this->format_date($args);
		$filter['date_from'] = $date['date_from'];
		$filter['date_to'] = $date['date_to'];
		
//        $oAnalysisDay = kernel::single('taocrm_analysis_day');
//        $all_sales_data = $oAnalysisDay->get_member_old_new($args);
        $all_sales_data = $this->getOldNewData($filter);
		
        $this->pagedata['sales_data'] = $all_sales_data['sales_data'];
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['compare_data'] = $all_sales_data['compare_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '新老客户价值';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_member&act=old_new';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/old_new.html');
    }
	
    public function lose(){
    	
    	$args['shop_id'] = $this->shop_id;
        $rs = &app::get('ecorder')->model('shop')->getList('*',array('node_type'=>'taobao'));
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
    
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        //$args['count_by'] = kernel::single('taocrm_ctl_admin_analysis_sales')->chk_date_diff($args['date_from'],$args['date_to']); 
        $args['count_by'] = kernel::single('taocrm_ctl_admin_analysis_sales')->chk_date_diff($this->date_from,$this->date_to); 
        $filter = $args;
		$date = $this->format_date($args);
		$filter['date_from'] = $date['date_from'];
		$filter['date_to'] = $date['date_to'];
		
//        $oAnalysisDay = kernel::single('taocrm_analysis_day');
//        $all_sales_data = $oAnalysisDay->get_member_old_new($args);
        $all_sales_data = $this->getLoseData($filter);
		
        $this->pagedata['sales_data'] = $all_sales_data['sales_data'];
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '旺旺流失客户分析';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_member&act=lose';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/lose.html');
    }
    
    public function getOldNewData($params)
    {
        $connect = $this->getConnect();
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = $params['date_from'];
        $filter['endTime'] = $params['date_to'];

        $filter['type'] = $params['count_by'];
        $result = $connect->NewOldMemberAnalysisByTimeType($filter);
        $data = array();
        if ($result) {
            $totalData = array();
            $analysisData = array();
            foreach ($result as $k => $v) {
                $key = $k;
                /*
                $analysisData[$key]['old_member'] = $v['OldMemberCount'];
                $totalData['old_member'] += $v['OldMemberCount'];
                $analysisData[$key]['old_amount'] = $v['OldAmountCount'];
                $totalData['old_amount'] += $v['OldAmountCount'];
                $analysisData[$key]['new_member'] = $v['NewMemberCount'];
                $totalData['new_member'] += $v['NewMemberCount'];
                $analysisData[$key]['new_amount'] = $v['NewAmountCount'];
                $totalData['new_amount'] += $v['NewAmountCount'];
                $analysisData[$key]['old_ratio'] = ($analysisData[$key]['old_member'] + $analysisData[$key]['new_member']) > 1 ? ($analysisData[$key]['old_member'] * 100) / ($analysisData[$key]['old_member'] + $analysisData[$key]['new_member']) : 0;
                $analysisData[$key]['old_amount_ratio'] = ($analysisData[$key]['old_amount'] + $analysisData[$key]['new_amount']) > 1 ? ($analysisData[$key]['old_amount'] * 100) / ($analysisData[$key]['old_amount'] + $analysisData[$key]['new_amount']) : 0;
                */
                $old_members = $v['total_members'] - $v['new_member'];
                $old_amount = $v['total_amount'] - $v['new_amount'];
                
                $analysisData[$key]['old_member'] = $old_members;
                $totalData['old_member'] += $old_members;
                $analysisData[$key]['old_amount'] = $old_amount;
                $totalData['old_amount'] += $old_amount;
                $analysisData[$key]['new_member'] = $v['new_member'];
                $totalData['new_member'] += $v['new_member'];
                $analysisData[$key]['new_amount'] = $v['new_amount'];
                $totalData['new_amount'] += $v['new_amount'];
                $analysisData[$key]['old_ratio'] = ($analysisData[$key]['old_member'] + $analysisData[$key]['new_member']) > 1 ? ($analysisData[$key]['old_member'] * 100) / ($analysisData[$key]['old_member'] + $analysisData[$key]['new_member']) : 0;
                $analysisData[$key]['old_amount_ratio'] = ($analysisData[$key]['old_amount'] + $analysisData[$key]['new_amount']) > 1 ? ($analysisData[$key]['old_amount'] * 100) / ($analysisData[$key]['old_amount'] + $analysisData[$key]['new_amount']) : 0;
                
            }
            $data['analysis_data'] = $analysisData;
            $data['total_data'] = $totalData;
            //echo "<pre>";
            //print_r($analysisData);exit;
        }
        return $data;
    }
    
 	public function getLoseData($params)
    {
        $connect = $this->getConnect();
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = $params['date_from'];
        $filter['endTime'] = $params['date_to'];

        $filter['type'] = $params['count_by'];
        $result = $connect->LoseMemberAnalysisByTimeType($filter);
        $data = array();
        if ($result) {
            $totalData = array();
            $analysisData = array();
            foreach ($result as $k => $v) {
                $key = $k;
                
                $analysisData[$key]['total_member'] = $v['total_member'];
                $totalData['total_member'] += $v['total_member'];
                $analysisData[$key]['order_amount'] = $v['order_amount'];
                $totalData['order_amount'] += $v['order_amount'];
                $analysisData[$key]['order_member'] = $v['order_member'];
                $totalData['order_member'] += $v['order_member'];
                $analysisData[$key]['contact_member'] = $v['contact_member'];
                $totalData['contact_member'] += $v['contact_member'];
                $analysisData[$key]['uncontact_member'] = $v['uncontact_member'];
                $totalData['uncontact_member'] += $v['uncontact_member'];
                $analysisData[$key]['order_ratio'] = $analysisData[$key]['order_member'] > 1 ? ($analysisData[$key]['order_member'] * 100) / $analysisData[$key]['total_member'] : 0;
                $totalData['order_ratio'] = $totalData['order_member'] > 1 ? ($totalData['order_member'] * 100) / $totalData['total_member'] : 0;
            }
            $data['analysis_data'] = $analysisData;
            $data['total_data'] = $totalData;
            
        }
        return $data;
    }
    
    public function getConnect() {
        if (self::$middleware_conn == null)
            self::$middleware_conn = kernel::single('taocrm_middleware_connect');
        return self::$middleware_conn;
    }
    
    private function _key_maps(&$arr, $count_by='DATE')
    {
        foreach($arr as $k=>&$v){
            //if($k=='>') $k=9;
            if(is_array($v)) {
                $arr[$k]['key_name']=$k;
                self::_key_maps($v);
                continue;
            }
            
            if($k=='TotalItems') $arr['total_items']=$v;
            
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
	
	public function format_date($args){
    	$start_time = strtotime($args['date_from'].' 00:00:00');
    	$end_time = strtotime($args['date_to'].' 00:00:00');
    	
    	switch($args['count_by']){
    		case 'DAY':{
    			$date['date_from'] = $start_time;
    			$date['date_to'] = $end_time + 86400;
    			break;
    		}
    		case 'WEEK':{
    			   $sw= date("w",$start_time);
    			   $ew = date("w",$end_time);
    			   if($sw==0){
    			   		$date['date_from'] = $start_time - 6*86400;
    			   }else{
    			   		$date['date_from'] = $start_time - ($sw-1)*86400;
    			   }
    			   
    			   if($ew==0){
    			   		$date['date_to'] = $end_time + 86400;
    			   }else{
    			   		$date['date_to'] = $end_time + (8-$ew)*86400;
    			   }
    			   
    			   break;
    		}
    		case 'MONTH':{
    			$st = date('j',$start_time);
    			$date['date_from'] = $start_time - ($st-1)*86400;
    			$et = date('j',$end_time);
    			$em = date('t',$end_time);
    			$date['date_to'] = $end_time + ($em-$et+1) * 86400;
    			break;
    		}
    		case 'YEAR':{
    			$syear = date('Y',$start_time);
    			$eyear = date('Y',$end_time);
    			$date['date_from'] = mktime(0,0,0,1,1,$syear);
    			$date['date_to'] = mktime(0,0,0,12,31,$eyear) + 86400;
    			break;
    		}
  
    	}
    	
    	return $date;
    }
}
