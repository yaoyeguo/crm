<?php
class taocrm_ctl_admin_fx_analysis extends desktop_controller 
{
	static $middleware_conn = null;
    var $workground = 'taocrm.fxmember';

    public function __construct($app)
    {
        parent::__construct($app);
        
        if (self::$middleware_conn == null)
            self::$middleware_conn = kernel::single('taocrm_middleware_connect');
        
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
        $kv = base_kvstore::instance('fx_analysis');
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
    
    //分销商贡献度
	public function contribution()
    {	
        $all_sales_data = array();
        $args['shop_id'] = $this->shop_id;
        $shopObj = &app::get('ecorder')->model('shop');
        
        $sql = "select name,shop_id from sdb_ecorder_shop where (shop_type='taobao' and subbiztype='fx') or shop_type='shopex_b2b' ";
        $rs = $shopObj->db->select($sql);
        
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
		
        //调用内存数据接口
        $filter = $args;
        //每页显示条数
        $pagelimit = 100;
        //页数
        $page = max(1, intval($_GET['page']));
        
        $filter['pageIndex'] = $page;
        $filter['pageSize'] = $pagelimit;
        //开始时间与结束时间
        $filter['date_from'] = strtotime($filter['date_from'].' 00:00:00');
        $filter['date_to'] = strtotime($filter['date_to'].' 00:00:00');
        
        $data = self::$middleware_conn->FxOrderReportModelByContribution($filter);
        //总记录数
        $count = $data['Count'];
        //数据明细
        $result = $data['Value'];
    	$analysis_data = array_values($result);
    
        foreach ($analysis_data as $v) {
            if( ! is_array($v)) continue;
            foreach($v as $kk=>$vv){
                if(is_numeric($vv))
                    $all_sales_data['total_data'][$kk] += $vv;
            }
        }
       
        $this->pagedata['count'] = $count;
        $this->pagedata['page'] = $page;
        $render = app::get('taocrm')->render();
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 
        	'link' =>'index.php?app=taocrm&ctl=admin_fx_analysis&act=contribution&page=%d' ));
        $render->pagedata['pager'] = $pager;
            
        $this->pagedata['analysis_data'] = $result;
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '分销商贡献度';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_fx_analysis&act=contribution';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/fx/analysis/contribution.html');
    }
    
    
	// 中国地图
    public function area(){
    
       $all_sales_data = array();
        
        $args['shop_id'] = $this->shop_id;
        $shopObj = &app::get('ecorder')->model('shop');
        
        $sql = "select name,shop_id from sdb_ecorder_shop where (shop_type='taobao' and subbiztype='fx') or shop_type='shopex_b2b' ";
        $rs = $shopObj->db->select($sql);
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $args['count_by'] = $_POST['count_by'] ? $_POST['count_by'] : 'date';
        
        $filter = array();
        $filter['shopId'] = $args['shop_id'];
        $filter['beginTime'] = strtotime($args['date_from']);
        $filter['endTime'] = strtotime($args['date_to']) + 86400;
        $filter['stateIds'] = '';
        $rs = $this->getAreaData($filter);

        $this->pagedata['analysis_data'] = $rs['analysis_data'];
        $this->pagedata['total_data'] = $rs['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '地域分析';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_fx_analysis&act=area';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/fx/analysis/area.html');
    }
    
    
	/**
     * 获得地区数据
     */
    public function getAreaData($params)
    {
        if (empty($params)) {
            die("error visit");
        }
        //地区列表
        $dbModel = self::$middleware_conn->getDataModel('ectools', 'regions');
        $field = 'region_id,local_name,group_name';
        $filter = array('region_grade' => 1);
        $areaInfo = $dbModel->getList($field, $filter);
        if($areaInfo){
            foreach($areaInfo as $v){
                $regions[$v['region_id']] = trim($v['local_name']);
            }
        }
        if ($params['stateIds'] == '') {
            foreach ($areaInfo as $v) {
                $params['stateIds'] .= $v['region_id'] .','; 
            }
            $params['stateIds'] = rtrim($params['stateIds'], ',');
        }
        //$result = json_decode(self::$middleware_conn->OrderReportModelByState($params), true);
        $result = self::$middleware_conn->FxOrderReportModelByState($params);
        $data = array();
        if ($result) {
            $analysisData = array();
            $tmpTotalData = array();
            $AmountCount = array();
            foreach ($result as $k => &$v) {
            	
            	if ($v['TotalOrders'] == '' && $v['TotalAmount'] == '') {
                    unset($result[$k]);
                    continue;
                }
                $v['state_id'] = $k;
                $v['area'] = $regions[$k];
                $v['area'] = str_replace(array('省','市','壮族自治区','维吾尔自治区','回族自治区','自治区','特别行政区'),'',$v['area']);
                $AmountCount[$k] = $v['TotalAmount'];
                foreach ($v as $k1 => $v1) {
                    $tmpTotalData[$k1] += $v1;
                }
            }
           	
            array_multisort($AmountCount, SORT_DESC, $result);
            $totalData = array();
            if ($tmpTotalData) {
                $totalData['paid_orders'] = $tmpTotalData['PayOrders'];
                $totalData['paid_amount'] = $tmpTotalData['PayAmount'];
                $totalData['paid_members'] = $tmpTotalData['PayMembers'];
                $totalData['paid_per_amount'] = round($tmpTotalData['PayAmount'] / $tmpTotalData['PayOrders'],2);
               
                $totalData['total_orders'] = $tmpTotalData['TotalOrders'];
                //订单金额
                $totalData['total_amount'] = $tmpTotalData['TotalAmount'];
                $totalData['total_members'] = $tmpTotalData['TotalMembers'];
            }
            
            foreach ($result as $value) {
                $analysisData[$value['area']] = array(
                    'paid_orders' => $value['PayOrders'],
                    //付款金额
                    'paid_amount' => number_format($value['PayAmount'], 2, ".", ''),
                    'paid_members' => $value['PayMembers'],
                    'state_id' => $value['state_id'],
                    'area' => $value['area'],
                    'paid_per_amount' => number_format($value['PayAmount']/$value['PayOrders'], 2, ".", ''),
                   
                    'total_orders' => $value['TotalOrders'],
                    'total_amount' => number_format($value['TotalAmount'], 2, ".", ''),
                    'total_members' => $value['TotalMembers'],
                    
                );
                $analysisData[$value['area']]['paid_orders_ratio'] = $totalData['paid_orders'] > 1 ? number_format(($value['PayOrders'] * 100) / $totalData['paid_orders'], 2, ".", '') : 0;
                $analysisData[$value['area']]['paid_amount_ratio'] = $totalData['paid_amount'] > 1 ? number_format(($value['PayAmount'] * 100) / $totalData['paid_amount'], 2, ".", '') : 0;
                $analysisData[$value['area']]['paid_members_ratio'] = $totalData['paid_members'] > 1 ? number_format(($value['PayMembers'] * 100) / $totalData['paid_members'], 2, ".", '') : 0;
                $analysisData[$value['area']]['total_orders_ratio'] = $totalData['total_orders'] > 1 ? number_format(($value['TotalOrders'] * 100) / $totalData['total_orders'], 2, ".", '') : 0;
                $analysisData[$value['area']]['total_amount_ratio'] = $totalData['total_amount'] > 1 ? number_format(($value['TotalAmount'] * 100) / $totalData['total_amount'], 2, ".", '') : 0;
                $analysisData[$value['area']]['total_members_ratio'] = $totalData['total_members'] > 1 ? number_format(($value['TotalMembers'] * 100) / $totalData['total_members'], 2, ".", '') : 0;
            }
            $data['total_data'] = $totalData;
            $data['analysis_data'] = $analysisData;
        }
        
        return $data;
    }
    
    /*
     *终端客户排行榜 
     */
    public function rank(){
   		$all_sales_data = array();
        
        $args['shop_id'] = $this->shop_id;
        $shopObj = &app::get('ecorder')->model('shop');
        
        $sql = "select name,shop_id from sdb_ecorder_shop where (shop_type='taobao' and subbiztype='fx') or shop_type='shopex_b2b' ";
        $rs = $shopObj->db->select($sql);
        
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
		
        //调用内存数据接口
        $filter = $args;
        
        //每页显示条数
        $pagelimit = 15;
        //页数
        $page = max(1, intval($_GET['page']));
        
        $filter['pageIndex'] = $page;
        $filter['pageSize'] = $pagelimit;
            
        $filter['date_from'] = strtotime($filter['date_from'].' 00:00:00');
        $filter['date_to'] = strtotime($filter['date_to'].' 00:00:00');
        
        $data = self::$middleware_conn->FxOrderReportModelByRank($filter);
        $count = $data['Count'];
        $result = $data['Value'];
        $this->pagedata['analysis_data'] = $result;
        $this->pagedata['count'] = $count;
        $this->pagedata['page'] = $page;
        //$this->pagedata['total_data'] = $all_sales_data['total_data'];

        
        $render = app::get('taocrm')->render();
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 
        	'link' =>'index.php?app=taocrm&ctl=admin_fx_analysis&act=rank&page=%d' ));
        $render->pagedata['pager'] = $pager;
        
        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '终端客户排行(按订单总额)';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_fx_analysis&act=rank';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/fx/analysis/rank.html');
    }
    
    /**
     * 多次购买客户分布
     */
    
    public function buy_time(){
    	$all_sales_data = array();
        
        $args['shop_id'] = $this->shop_id;
        $shopObj = &app::get('ecorder')->model('shop');
        
        $sql = "select name,shop_id from sdb_ecorder_shop where (shop_type='taobao' and subbiztype='fx') or shop_type='shopex_b2b' ";
        $rs = $shopObj->db->select($sql);
        
        foreach($rs as $v){
            if(!$args['shop_id']) 
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
		
        //调用内存数据接口
        $filter = $args;
            
        $filter['date_from'] = strtotime($filter['date_from'].' 00:00:00');
        $filter['date_to'] = strtotime($filter['date_to'].' 00:00:00');
        
        $result = self::$middleware_conn->FxOrderReportModelByFreq($filter);
        base_kvstore::instance('analysis')->store('fx_buy_freq',$result);
    	//数据转换
            $total_amount = 0;
            $total_orders = 0;
            $total_items = 0;
            $total_members = 0;
     		foreach($result as $k=>$v){
            	if($k < 7){
            		$v['key'] = $k;
            		$arr[$k] = $v;
            	}else{
            		$total_orders += $v['TotalOrders'];
            		$total_members += $v['TotalMembers'];
            		$total_agents1 += $v['TotalAgents1'];
            		$total_agents2 += $v['TotalAgents2'];
            		$total_agents3 += $v['TotalAgents3'];
            	}
            }
            if($total_orders || $total_members || $total_agents1 || $total_agents2 || $total_agents3){
	            $arr['>']['TotalOrders'] = $total_orders;
	            $arr['>']['TotalMembers'] = $total_members;
	            $arr['>']['TotalAgents1'] = $total_agents1;
	            $arr['>']['TotalAgents2'] = $total_agents2;
	            $arr['>']['TotalAgents3'] = $total_agents3;
	            $arr['>']['key'] = '>';
            }
            $all_sales_data['analysis_data'] = $arr;
            //$all_sales_data['analysis_data'] = json_decode($result, 1);
            
//            echo "<pre>";
//            print_r($all_sales_data);
//            exit;
            ksort($all_sales_data['analysis_data']);
            
            //处理 “>” 符号
            if($arr['>']){
	            $all_sales_data['analysis_data']['大于6'] = $all_sales_data['analysis_data']['>'];
	            unset($all_sales_data['analysis_data']['>']);  
            } 
        
        //计算合计
        foreach($all_sales_data['analysis_data'] as $k=>&$v) {

            foreach($v as $kk=>$vv) {
                if(is_numeric($vv)){
                    $all_sales_data['total_data'][$kk] += $vv;
                }
            }
        }
        
       
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '多次购买客户分布(按下单统计)';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_fx_analysis&act=buy_time';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/fx/analysis/buy_time.html');
    }
    
    public function city(){
        $args['state'] = $_GET['state'];
        $args['date_from'] = $_GET['date_from'];
        $args['date_to'] = $_GET['date_to'];
        $args['shop_id'] = $this->shop_id;
        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $rs = $oAnalysisDay->get_fx_city_data($args);
        $this->pagedata['analysis_data'] = $rs['analysis_data'];
        $this->pagedata['total_data'] = $rs['total_data'];
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['shop_id']= $args['shop_id'];
        
        $this->display('admin/fx/analysis/city.html');
    }
}