<?php
class taocrm_ctl_admin_analysis_sales extends desktop_controller
{
    static $middleware_conn = null;
    var $workground = 'taocrm.analysts';

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

    // 按日期范围自动设置时间单位
    public function chk_date_diff($date_from,$date_to)
    {

        $date_from = strtotime($date_from);
        $date_to = strtotime($date_to);

        if(!$_POST['count_by']){
            $days = ($date_to - $date_from)/(24*3600);
            if($days>31*30) return 'YEAR';
            if($days>31*7) return 'MONTH';
            if($days>31) return 'WEEK';
            return 'DAY';
        }else{
            return $_POST['count_by'];
        }
    }

    public function index()
    {
        $all_sales_data = array();
        $args['shop_id'] = $this->shop_id;
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id'])
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }

        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $args['count_by'] = $this->chk_date_diff($args['date_from'],$args['date_to']);

        //调用内存数据接口
        $filter = $args;
        $date = $this->format_date($filter);
        $filter['date_from'] = $date['date_from'];
        $filter['date_to'] = $date['date_to'];
        $result = self::$middleware_conn->OrderReportModelByTimeAndType($filter);
        $all_sales_data['analysis_data'] = $result;
        ksort($all_sales_data['analysis_data']);
        self::_key_maps($all_sales_data['analysis_data'], $args['count_by']);
        $all_sales_data['analysis_data'] = array_values($all_sales_data['analysis_data']);

        foreach ($all_sales_data['analysis_data'] as $v) {
            if( ! is_array($v)) continue;
            foreach($v as $kk=>$vv){
                if(is_numeric($vv))
                    $all_sales_data['total_data'][$kk] += $vv;
            }
        }

        //对比时间段的数据
        if($_POST['c_date_from']){
            $args['c_date_from'] = $_POST['c_date_from'];
            $args['c_date_to'] = date('Y-m-d',strtotime($args['c_date_from'])+strtotime($args['date_to'])-strtotime($args['date_from']));
            $filter = $args;
            $filter['date_from'] = strtotime($filter['c_date_from']);
            $filter['date_to'] = strtotime($filter['c_date_to']);
            $result_compare = self::$middleware_conn->OrderReportModelByTimeAndType($filter);

            $all_sales_data['compare_data'] = $result_compare;
            ksort($all_sales_data['compare_data']);
            self::_key_maps($all_sales_data['compare_data'], $args['count_by']);
            $all_sales_data['compare_data'] = array_values($all_sales_data['compare_data']);
        }

        base_kvstore::instance('analysis')->store('sales_index',$all_sales_data);

        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['compare_data'] = $all_sales_data['compare_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '销售统计';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['c_date_from'] = $args['c_date_from'];
        $this->pagedata['c_date_to'] = $args['c_date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_sales&act=index';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/index.html');
    }

    public function hours()
    {
        //判断显示模式：按周或小时
        if($_GET['unit'] == 'week'){
            $this->week();
            exit;
        }

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

        //获取内存数据
        $filter = $args;
            $filter['beginHour'] = 0;
            $filter['endHour'] = 23;
            $filter['date_from'] = strtotime($args['date_from']);
            $filter['date_to'] = strtotime($args['date_to']) + 86400;

            $result = self::$middleware_conn->OrderReportModelByHour($filter);
            //$all_sales_data['analysis_data'] = json_decode($result, 1);
            $all_sales_data['analysis_data'] = $result;
            ksort($all_sales_data['analysis_data']);
            self::_key_maps($all_sales_data['analysis_data'], $args['count_by']);
            foreach ($all_sales_data['analysis_data'] as $v) {
                $all_sales_data['total_data']['total_orders'] += $v['total_orders'];
                $all_sales_data['total_data']['total_amount'] += $v['total_amount'];
                $all_sales_data['total_data']['total_members'] += $v['total_members'];
                $all_sales_data['total_data']['paid_orders'] += $v['paid_orders'];
                $all_sales_data['total_data']['paid_amount'] += $v['paid_amount'];
                $all_sales_data['total_data']['paid_members'] += $v['paid_members'];
                $all_sales_data['total_data']['finish_orders'] += $v['finish_orders'];
                $all_sales_data['total_data']['finish_amount'] += $v['finish_amount'];
                $all_sales_data['total_data']['finish_members'] += $v['finish_members'];
            }
//            echo "<pre>";
//            print_r($all_sales_data);
//            exit;

            base_kvstore::instance('analysis')->store('sales_hours',$all_sales_data);
            //var_dump($result);

        //$oAnalysisDay = kernel::single('taocrm_analysis_day');
        //$all_sales_data = $oAnalysisDay->get_hours_data($args);

        $this->pagedata['sales_data'] = $all_sales_data['sales_data'];
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['compare_data'] = $all_sales_data['compare_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '成交时间';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['c_date_from'] = $args['c_date_from'];
        $this->pagedata['c_date_to'] = $args['c_date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_sales&act=hours';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/hours.html');
    }

    public function week()
    {
        $all_sales_data = array();
        $args['shop_id'] = $this->shop_id;
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id'])
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }

        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $args['count_by'] = 'DAY';

        //调用内存数据接口
        $filter = $args;
        $date = $this->format_date($filter);
        $filter['date_from'] = $date['date_from'];
        $filter['date_to'] = $date['date_to'];
        $filter['count_by'] = 'day_of_week';
        $result = self::$middleware_conn->OrderReportModelByTimeAndType($filter);
        $week_name = array('星期天','星期一','星期二','星期三','星期四','星期五','星期六');
		
		if(count($result<=7)){
			ksort($result);
			foreach($result as $k=>$v){
                $v['week_no'] = $k;
				$all_sales_data['analysis_data'][$week_name[$k-1]] = $v;			
			}
		}else{
        $all_sales_data['analysis_data'] = $result;
        ksort($all_sales_data['analysis_data']);
        self::_key_maps($all_sales_data['analysis_data'], $args['count_by']);
        $all_sales_data['analysis_data'] = array_values($all_sales_data['analysis_data']);

        $week_data = array();
        $week_count = count($all_sales_data['analysis_data']);

        if($week_count>0){
            foreach($all_sales_data['analysis_data'] as $v){
                if( ! is_array($v)) continue;
                foreach($v as $kk=>$vv){
                    if(is_numeric($vv)){
                        $all_sales_data['total_data'][$kk] += $vv;
                        $week_data[date('w',strtotime($v['date']))][$kk] += $vv;
                    }
                }
                $week_data[date('w',strtotime($v['date']))]['count'] += 1;
            }

            ksort($week_data);
            $all_sales_data['analysis_data'] = array();
            foreach($week_data as $k=>$v){
                foreach($v as $kk=>$vv){
                    if(is_numeric($vv)){
                        $all_sales_data['analysis_data'][$week_name[$k]][$kk] += $vv;
                    }
                }
            }
        }
		}

        //echo('<pre>');var_dump($week_data);
        base_kvstore::instance('analysis')->store('sales_week',$all_sales_data);

        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '成交时间';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_sales&act=hours&unit=week';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/week.html');
    }

    //订单状态统计
    public function ostatus()
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
        $args['count_by'] = $this->chk_date_diff($args['date_from'],$args['date_to']);

        //$oAnalysisDay = kernel::single('taocrm_analysis_day');
        //$all_sales_data = $oAnalysisDay->get_all_sales_data($args);

        //调用内存数据接口
        $filter = $args;
            $date = $this->format_date($filter);

            //$filter['date_from'] = strtotime($filter['date_from'].' 00:00:00');
            //$filter['date_to'] = strtotime($filter['date_to'].' 00:00:00');
            $filter['date_from'] = $date['date_from'];
            $filter['date_to'] = $date['date_to'];

            $result = self::$middleware_conn->OrderReportModelByTimeAndType($filter);

            //$all_sales_data['analysis_data'] = json_decode($result, 1);
            $all_sales_data['analysis_data'] = $result;

            ksort($all_sales_data['analysis_data']);
            self::_key_maps($all_sales_data['analysis_data'], $args['count_by']);

        //计算合计
        foreach($all_sales_data['analysis_data'] as $k=>$v) {
            foreach($v as $kk=>$vv) {
                if(is_numeric($vv)){
                    $all_sales_data['total_data'][$kk] += $vv;
                }
            }
        }

        base_kvstore::instance('analysis')->store('sales_ostatus',$all_sales_data);

        $this->pagedata['sales_data'] = $all_sales_data['sales_data'];
        $this->pagedata['analysis_data'] = $all_sales_data['analysis_data'];
        $this->pagedata['compare_data'] = $all_sales_data['compare_data'];
        $this->pagedata['total_data'] = $all_sales_data['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '成交状态';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_sales&act=ostatus';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/order_status.html');
    }

    // 中国地图
    public function area()
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

        $filter = array();
        $filter['shopId'] = $args['shop_id'];
        $filter['beginTime'] = strtotime($args['date_from']);
        $filter['endTime'] = strtotime($args['date_to']) + 86400;
        $filter['stateIds'] = '';
        $rs = $this->getAreaData($filter);
        //$oAnalysisDay = kernel::single('taocrm_analysis_day');
        //$rs = $oAnalysisDay->get_area_data($args);
        $this->pagedata['analysis_data'] = $rs['analysis_data'];
        $this->pagedata['total_data'] = $rs['total_data'];

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['path']= '区域分布';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['count_by'] = $args['count_by'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_sales&act=area';
        $this->pagedata['line_shop'] = 'false';
        $this->page('admin/analysis/sales/area.html');
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
        $filter = array('region_grade'=>1, 'region_id|sthan'=>3266);
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
        $result = self::$middleware_conn->OrderReportModelByState($params);

        $data = array();
        if ($result) {
            $analysisData = array();
            $tmpTotalData = array();
            $AmountCount = array();
            foreach ($result as $k => &$v) {
            	/*
                if ($v['OrderCount'] == '' && $v['AmountCount'] == '') {
                    unset($result[$k]);
                    continue;
                }
                */
            	if ($v['total_orders'] == '' && $v['total_amount'] == '') {
                    unset($result[$k]);
                    continue;
                }
                $v['state_id'] = $k;
                $v['area'] = $regions[$k];
                $v['area'] = str_replace(array('省','市','壮族自治区','维吾尔自治区','回族自治区','自治区','特别行政区'),'',$v['area']);
                $AmountCount[$k] = $v['total_amount'];
                foreach ($v as $k1 => $v1) {
                    $tmpTotalData[$k1] += $v1;
                }
            }
            array_multisort($AmountCount, SORT_DESC, $result);
            $totalData = array();
            if ($tmpTotalData) {
                $totalData['paid_orders'] = $tmpTotalData['paid_orders'];
                $totalData['paid_amount'] = $tmpTotalData['paid_amount'];
                $totalData['paid_members'] = $tmpTotalData['paid_members'];
                $totalData['paid_per_amount'] = round($tmpTotalData['paid_amount'] / $tmpTotalData['paid_orders'],2);
                $totalData['finish_orders'] = $tmpTotalData['finish_orders'];
                $totalData['finish_amount'] = $tmpTotalData['finish_amount'];
                $totalData['finish_members'] = $tmpTotalData['finish_members'];
                $totalData['finish_per_amount'] = round($tmpTotalData['finish_amount'] / $tmpTotalData['finish_orders'],2);
                $totalData['total_orders'] = $tmpTotalData['total_orders'];
                //订单金额
                $totalData['total_amount'] = $tmpTotalData['total_amount'];
                $totalData['total_members'] = $tmpTotalData['total_members'];
                $totalData['per_amount'] = round($tmpTotalData['total_amount'] / $tmpTotalData['total_orders'],2);
            }
            foreach ($result as $value) {
                $analysisData[$value['area']] = array(
                    'paid_orders' => $value['paid_orders'],
                    //付款金额
                    'paid_amount' => number_format($value['paid_amount'], 2, ".", ''),
                    'paid_members' => $value['paid_members'],
                    'state_id' => $value['state_id'],
                    'area' => $value['area'],
                    'paid_per_amount' => number_format($value['paid_amount']/$value['paid_orders'], 2, ".", ''),
                    'finish_orders' => $value['finish_orders'],
                    'finish_amount' => number_format($value['finish_amount'], 2, ".", ''),
                    'finish_members' => $value['finish_members'],
                    'finish_per_amount' => number_format($value['finish_amount']/$value['finish_orders'], 2, ".", ''),
                    'total_orders' => $value['total_orders'],
                    'total_amount' => number_format($value['total_amount'], 2, ".", ''),
                    'total_members' => $value['total_members'],
                    'per_amount' => number_format($value['total_amount']/$value['total_orders'], 2, ".", ''),
                );
                $analysisData[$value['area']]['paid_orders_ratio'] = $totalData['paid_orders'] > 1 ? number_format(($value['paid_orders'] * 100) / $totalData['paid_orders'], 2, ".", '') : 0;
                $analysisData[$value['area']]['paid_amount_ratio'] = $totalData['paid_amount'] > 1 ? number_format(($value['paid_amount'] * 100) / $totalData['paid_amount'], 2, ".", '') : 0;
                $analysisData[$value['area']]['paid_members_ratio'] = $totalData['paid_members'] > 1 ? number_format(($value['paid_members'] * 100) / $totalData['paid_members'], 2, ".", '') : 0;
                $analysisData[$value['area']]['paid_per_amount_ratio'] = $totalData['paid_per_amount'] > 1 ? number_format(($value['paid_amount']/$value['paid_orders'] * 100) / $totalData['paid_per_amount'], 2, ".", '') : 0;
                $analysisData[$value['area']]['finish_orders_ratio'] = $totalData['finish_orders'] > 1 ? number_format(($value['finish_orders'] * 100) / $totalData['finish_orders'], 2, ".", '') : 0;
                $analysisData[$value['area']]['finish_amount_ratio'] = $totalData['finish_amount'] > 1 ? number_format(($value['finish_amount'] * 100) / $totalData['finish_amount'], 2, ".", '') : 0;
                $analysisData[$value['area']]['finish_members_ratio'] = $totalData['finish_members'] > 1 ? number_format(($value['finish_members'] * 100) / $totalData['finish_members'], 2, ".", '') : 0;
                $analysisData[$value['area']]['finish_per_amount_ratio'] = $totalData['finish_per_amount'] > 1 ? number_format(($value['finish_amount']/$value['finish_orders'] * 100) / $totalData['finish_per_amount'], 2, ".", '') : 0;
                $analysisData[$value['area']]['total_orders_ratio'] = $totalData['total_orders'] > 1 ? number_format(($value['total_orders'] * 100) / $totalData['total_orders'], 2, ".", '') : 0;
                $analysisData[$value['area']]['total_amount_ratio'] = $totalData['total_amount'] > 1 ? number_format(($value['total_amount'] * 100) / $totalData['total_amount'], 2, ".", '') : 0;
                $analysisData[$value['area']]['total_members_ratio'] = $totalData['total_members'] > 1 ? number_format(($value['total_members'] * 100) / $totalData['total_members'], 2, ".", '') : 0;
                $analysisData[$value['area']]['per_amount_ratio'] = $totalData['per_amount'] > 1 ? number_format(($value['total_amount']/$value['total_orders'] * 100) / $totalData['per_amount'], 2, ".", '') : 0;
            }
            $data['total_data'] = $totalData;
            $data['analysis_data'] = $analysisData;
        }
        return $data;
    }

    public function city(){
        $args['state'] = $_GET['state'];
        $args['date_from'] = $_GET['date_from'];
        $args['date_to'] = $_GET['date_to'];
        $args['shop_id'] = $this->shop_id;
        $oAnalysisDay = kernel::single('taocrm_analysis_day');
        $rs = $oAnalysisDay->get_city_data($args);
        $this->pagedata['analysis_data'] = $rs['analysis_data'];
        $this->pagedata['total_data'] = $rs['total_data'];
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['shop_id']= $args['shop_id'];

        $this->display('admin/analysis/sales/city.html');
    }

    public function shop(){
        $analysisLogsObj = $this->app->model('analysis_logs');
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopData = $shopObj->getList('shop_id,name');
        foreach($shopData as $k=>$v) {
            $args['shop_id'] = $v['shop_id'];
            $shopData[$k]['sales_data'] = $analysisLogsObj->get_all_sales_data($args);
        }
        $this->pagedata['shopData'] = $shopData;

        $this->pagedata['path']= '店铺构成比';
        $this->pagedata['service'] = 'taocrm_analysis_sales';
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_sales&act=shop';
        $this->page('admin/analysis/sales/shop.html');
    }

    public function view() {
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_sales&act=index';
        $this->page('admin/analysis/sales/view.html');
    }

    private function _key_maps(&$arr, $count_by='DATE')
    {
        foreach($arr as $k=>&$v){
            if(is_array($v)){
                $arr[$k]['date']=$k;
                if($count_by=='WEEK'){
                    $arr[$k]['date']=substr($k,0,5).str_replace('-', '', substr($k,-2));
                }
                self::_key_maps($v);
                continue;
            }
            if($k=='OrderCount') $arr['total_orders']=$v;
            if($k=='AmountCount') $arr['total_amount']=$v;
            if($k=='Members') $arr['total_members']=$v;

            if($k=='PayOrder') $arr['paid_orders']=$v;
            if($k=='PayAmount') $arr['paid_amount']=$v;
            if($k=='PayMember') $arr['paid_members']=$v;

            if($k=='FinishOrder') $arr['finish_orders']=$v;
            if($k=='FinishAmount') $arr['finish_amount']=$v;
            if($k=='FinishMember') $arr['finish_members']=$v;
        }
    }

    private function array_sort(&$arr,$keys,$type='desc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        $i = 1;
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
            $new_array[$k]['order'] = $i++;
        }
        $arr = $new_array;
    }

    private function format_date($args){
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
