<?php

class taocrm_middleware_connect extends taocrm_middleware_abstract implements taocrm_interface_middleware
{
    protected  static $http = null;
    protected  $shopId = '';
    protected  static $count = array();

    public function __construct()
    {
        //parent::__construct();
        if (self::$http == null) {
            $this->setHttp();
        }
    }

    public function getHttp()
    {
        if (self::$http == null) {
            self::$http = $this->setHttp();
        }
        return self::$http;
    }

    protected function setHttp()
    {
        self::$http = new base_httpclient;
    }

    //对java返回的会员数据做格式化，添加java缺少的字段
    public function get_report_member_list($data, $orderType, $shop_id='')
    {
        $model = $this->getDataModel('taocrm', 'members');
        $member_analysis_mdl = $this->getDataModel('taocrm', 'member_analysis');
        $memberIds = array();
        if($data['memberIds']){
            $memberIds = $data['memberIds'];
            $data_from = 'members';
        }else{
        $memberIds = array_keys($data);
            $data_from = 'java';
        }        
        if ($memberIds) {
            $search['member_id|in'] = $memberIds;
            $memberList = $model->getList('*', $search);
            if($memberIds){                
                //获取每个会员的标签
                $oTag = app::get('taocrm')->model('member_tag');
                $tagInfo = $oTag->getMemberTagInfo($memberIds);
            }            
            
            $member_analysis_list = $member_analysis_mdl->getList('f_created,member_id,points,shop_id', $search);
            foreach($member_analysis_list as $v){
                $f_created[$v['member_id']] = $v['f_created'];
                $m_points[$v['member_id']][$v['shop_id']] = $v['points'];
            }
            foreach($memberList as $k=>$v){
                if(isset($tagInfo[$v['member_id']]))
                    $v['tagInfo'] = implode('；', $tagInfo[$v['member_id']]);

                if($data_from == 'members'){
                    $data[$v['member_id']]['MinCreateTime'] = $v['order_first_time'];
                    $data[$v['member_id']]['MaxCreateTime'] = $v['order_last_time'];
                    $data[$v['member_id']]['TotalOrders'] = $v['order_total_num'];
                    $data[$v['member_id']]['FinishTotalAmount'] = $v['order_succ_amount'];
                    $data[$v['member_id']]['FinishOrders'] = $v['order_succ_num'];
                }

                $v['id'] = $v['member_id'];
                $v['area'] = $model->clear_area($v['area']);
                //积分
                $v['points'] = $m_points[$v['member_id']][$shop_id];
                //第一次下单时间
                $v['first_buy_time'] = $data[$v['member_id']]['MinCreateTime'];
                //订单总金额
                $v['total_amount'] = number_format($data[$v['member_id']]['TotalAmount'], 2, '.', '');
                //最后下单时间
                $v['last_buy_time'] = $data[$v['member_id']]['MaxCreateTime'];
                $v['total_orders'] = $data[$v['member_id']]['TotalOrders'];
                $v['finish_total_amount'] = $data[$v['member_id']]['FinishTotalAmount'];
                $v['shop_id'] = $this->shopId;
                $v['lv_id'] = $data[$v['member_id']]['SysLv'];
                //成功的订单数
                $v['finish_orders'] = $data[$v['member_id']]['FinishOrders'];
                //购买频次
                $v['buy_freq'] = $data[$v['member_id']]['BuyFreq'];
                //平均购买间隔(天)
                $v['avg_buy_interval'] = $data[$v['member_id']]['AvgBuyInterval'];
                //平均订单价
                $v['total_per_amount'] = number_format($data[$v['member_id']]['TotalPerAmount'], 2, '.', '');
                //未付款的订单数量
                $v['unpay_orders'] = $data[$v['member_id']]['UnpayOrders'];
                //未付款的订单金额
                $v['unpay_amount'] = number_format($data[$v['member_id']]['UnpayAmount'], 2, '.', '');
                //客户注册时间
                $v['f_created'] = $f_created[$v['member_id']];
                //店铺最后访问时间
                $v['f_last_visit'] = $data[$v['member_id']]['MaxCreateTime'];
                //退款订单数
                $v['refund_orders'] = $data[$v['member_id']]['RefundOrders'];
                //未支付平均订单价
                $v['unpay_per_amount'] = $data[$v['member_id']]['UnpayOrders'] > 0 ? number_format($data[$v['member_id']]['UnpayAmount'] / $data[$v['member_id']]['UnpayOrders'], 2, '.', '') : 0;
                //$data['unpay_per_amount'] = $result['UnpayOrders'] > 0 ? number_format($result['UnpayAmount'], 2) / $result['UnpayOrders'] : 0;

                $memberList[$k]=$v;
            }

            if(!$orderType) $orderType = 'last_buy_time DESC';
            if($orderType){
                $order = explode(" ",trim($orderType));
                kernel::single("taocrm_analysis_day")->array_sort($memberList,$order[0],$order[1]);
            }
        }

        // var_dump($memberList);
        return $memberList;
    }

    public function get_report_fx_member_list($data, $orderType)
    {
        $memberList = array();
        foreach($data as $k=>$v){
            $v['id'] = $v['member_id'];
            //首次下单时间
            $v['first_buy_time'] = $data[$v['member_id']]['MinCreateTime'];
            //最后下单时间
            $v['last_buy_time'] = $data[$v['member_id']]['MaxCreateTime'];
            $v['total_orders'] = $data[$v['member_id']]['TotalOrders'];
            $v['total_amount'] = $data[$v['member_id']]['TotalAmount'];
            $v['finish_orders'] = $data[$v['member_id']]['FinishOrders'];
            $v['finish_amount'] = $data[$v['member_id']]['FinishTotalAmount'];
            $v['mobile'] = $data[$v['member_id']]['shipMobile'];
            $v['ship_name'] = $data[$v['member_id']]['ShipName'];
            $memberList[] = $v;
        }
        return $memberList;
    }

    /**
     * 获得客户列表
     */
    public function getMemberList($data, $returnOther = false)
    {
        $shopId = $data['shop_id'];
        $filter = $data['filter'];
        $params = $this->packFilter($shopId, $filter);
        $params['pageIndex'] = max(1, ($data['pageIndex'] + 1));
        $params['pageSize'] = max(0, $data['pageSize']);
        $result = json_decode($this->post(self::GET_MEMBELIST_URL, $params), true);
        $count = 0;
        $data = array();
        if ($result['rsp'] == 'succ') {
            $value = $result['info']['value'];
            $count = $result['info']['count'];
            if (self::DATA_SOURCE == 'DATABASE') {
                $model = $this->getDataModel('taocrm', 'member_analysis');
                $memberIds = array();
                foreach ($value as $memberId) {
                    $memberIds[] = $memberId;
                }
                if ($memberIds) {
                    $search['member_id|in'] = $memberIds;
                    $data = $model->getList('*', $search);
                }
            }
            else {
                //后续。。。。。
            }
        }

        if ($returnOther == false) {
            $memberList =  $data;
        }
        else {
            $memberList = array('data' => $data, 'count' => $count);
        }
        return $memberList;
    }


    public function createCallplanMembers($data)
    {
        $params = $this->getMemberPackFilter($data);

        $params['dbName'] = $this->getDbName();
        $params['method'] = 'sdop.statistics.download';
        $params['targets'] = '850002';
        $params['callplanId'] = $data['callplan_id'];
        $params['assignUserId'] = $data['assign_user_id'];
        $params['createTime'] = $data['create_time'];
        $params['updateTime'] = $data['update_time'];

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }

    public function createCallplanActiveOrder($id,$data)
    {
        $model = app::get('market')->model('active');

        $sql = "select * from sdb_market_active_assess where id={$id} ";
        $rs_active_assess = $model->db->selectRow($sql);

        $rs_active = $model->dump(intval($rs_active_assess['active_id']), 'shop_id,start_time,end_time,exec_time');
        if(!$rs_active['end_time'])
            $rs_active['end_time'] = $rs_active['exec_time'] + 86400*15;

        $params['shopId'] = $rs_active['shop_id'];
        $params['dbName'] = $this->getDbName();
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['taskId'] = intval($rs_active_assess['active_id']);
        $params['taskType'] = 'active';

        $params['beginTime'] = isset($data['start_time']) ? $data['start_time'] : $rs_active['start_time'];
        $params['endTime'] = isset($data['end_time']) ? $data['end_time'] : $rs_active['end_time'];
        $params['targets'] = $data['targets'];

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);

        //err_log($params);
        //err_log($data);

        return $data;
    }

    public function getActivePlanReport($data)
    {
        $params['shopId'] = $data['shop_id'];
        $params['dbName'] = $this->getDbName();
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['taskId'] = $data['active_id'];
        $params['taskType'] = 'active_plan';
        $params['beginTime'] = $data['start_time'];
        $params['endTime'] = $data['end_time'];
        $params['targets'] = $data['targets'];

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }

    /*店铺成交价格分析*/
    public function createCallplanPrice($params = array())
    {
        $params = array(
                'dbName' => $this->getDbName(),
                //'dbName' => 'db_5_309146',
                'method' => 'sdop.report.statistics.get',
                'beginTime' => $params['date_from'],
                'endTime' => $params['date_to'],
                'targets' => 800031,
                'shopId' => !empty($params['shop_id']) ? $params['shop_id'] : '',
                'orderStatus' => !empty($params['order_status']) ? $params['order_status'] : '',
            );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }
    /*全渠道分析报表渠道销量统计*/
    public function createCallplanChannel($params = array())
    {
        $params = array(
                'dbName' => $this->getDbName(),
                'method' => 'sdop.report.statistics.get',
                'beginTime' => $params['date_from'],
                'endTime' => $params['date_to'],
                'targets' => 800029,
                'dateType'=> !empty($params['date_type']) ? $params['date_type'] : 'week', 
            );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }
    /*全渠道分析报表店铺销量统计*/
    public function createCallplanStores($params = array())
    {
        $params = array(
                'shopIds' => $params['shop_ids'],//'e5191c274efb3e2a851bc659fb4989ad',
                'dbName' => $this->getDbName(),
                'method' => 'sdop.report.statistics.get',
                'beginTime' => $params['date_from'],
                'endTime' => $params['date_to'],
                'targets' => 800028,
                'dateType'=> !empty($params['date_type']) ? $params['date_type'] : 'week', 
        );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }

    /*全渠道分析报表渠道销量统计*/
    public function createCallplanForecast($params = array())
    {
        $params = array(
                'shopId' => $params['shop_id'],
                'dbName' => $this->getDbName(),
                'method' => 'sdop.estimate.statics.get',
                'targets' => !empty($params['targets']) ? $params['targets'] : '860001',
                'status' => !empty($params['status']) ? $params['status'] : 'pay',
                'shopType' => !empty($params['shop_type']) ? $params['shop_type'] : '',
                'useMonths' => !empty($params['use_months']) ? intval($params['use_months']) : '',
                'scope' => !empty($params['scope']) ? $params['scope'] : 'month',
                //'containsl2' => false,
                //'containsll' => false,
            );
        //trace($params);
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }
    /*  运营报表-生命周期分析接口调用*/
    public function createCallplanLca($params = array())
    {
        $params = array(
                'shopId' => $params['shop_id'],//'e5191c274efb3e2a851bc659fb4989ad',
                'combineRange' => $params['range'],
                'dbName' => $this->getDbName(),
                'method' => 'sdop.report.statistics.get',
                'beginTime' => $params['date_from'],
                'endTime' => $params['date_to'],
                'targets' => 800026,
        );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }

    /*  运营报表-生命周期分析接口调用-放大镜*/
    public function createCallplanMemberList($params = array())
    {
        switch($params['targets'])
        {
            case 1:
                $params['targets'] = 810015;
            break;
            default:
                $params['targets'] = 810016;
            break;
        }
        $params = array(
                'method' => 'sdop.report.zoom.statistics.get',
                'pageIndex' => $params['pageIndex'] ? $params['pageIndex'] : 1,
                'beginDays' => $params['beginDays'] ? $params['beginDays'] : 0,
                'pageSize' => 300,
                'shopId' => $params['shopId'],
                'endDays' => $params['endDays'],
                'targets' => $params['targets'],
                'dbName' => $this->getDbName(),
        );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data;
    }
    /**
     * 获取活动发送清单
     */
    public function getMarketActivity($data)
    {

    }

    /**
     * 执行活动
     */
    public function execActivity($data)
    {

    }

    public function FxAnalysisByFinishOrderCount($data)
    {
        if($data['orderCountType'] == '='){
            $orderSymbol = 'eq';
        }else{
            $orderSymbol = 'gt';
        }

        if($data['agentCountType'] == '='){
            $agentSymbol = 'eq';
        }else{
            $agentSymbol = 'ge';
        }

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopId' => $data['shopId'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'finishOrderCount' => $data['finishOrderCount'],
        	'agentCount' => $data['agentCount'],
            'agentCountType' => $agentSymbol,
        	'orderCountType' => $orderSymbol,
        	'targets' => '810054',
        	'method' => 'sdop.report.zoom.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'AnalysisByFinishOrderCount');
        	$data = $this->getReturnData($this->memoServiceUrl,$params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];

    }

    /**
     * 分销自定义客户分组
     */
    public function FxSearchMemberAnalysisList($data)
    {
        $filter = $data['filter'];
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }

        $params = $this->getFxMemberPackFilter($filter);
        //$params['dbName'] =  $this->getDbName();
        $params['targets'] = '810051';
        $pageIndex = 1;
        if (isset($data['pageIndex'])) {
            $pageIndex = max(intval($data['pageIndex']), $pageIndex);
        }

        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 20;
        
        $params['pageSize'] = $pageSize;
        $params['pageIndex'] = $pageIndex;
        $params['method'] = 'sdop.report.zoom.statistics.get';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'SearchMemberAnalysisList');
        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 根据时间、地域及付款状态获取客户 分析信息                      分销  销售报表 地域分析 放大镜
     */

    public function FxMemberAnalysisByTimeAndOrderStatus($data)
    {
        $params = array(
			'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
			'status' => $data['status'],
        	'agentName' => $data['agent_name'],
			'targets' => '810052',
			'method' => 'sdop.report.zoom.statistics.get'
			);
			//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberAnalysisByTimeAndOrderStatus');
			$data = $this->getReturnData($this->memoServiceUrl, $params);
			$data = json_decode($data,true);

			return $data[$params['targets']]['data'];
    }
    /**
     * 获得销售统计
     */
    public function OrderReportModelByTimeAndType($data)
    {
        $dateType = strtolower($data['count_by']);
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shop_id'],
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'dateType' => $dateType,
        	'targets' => '800007',
        	'method' => 'sdop.report.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('Report', 'OrderReportModelByTimeAndType');
        	$data = $this->getReturnData($this->memoServiceUrl,$params);
        	$data = json_decode($data,true);
        	$list = $data[$params['targets']]['data'];

        	foreach($list as $v){
        	    foreach($v as $k=>$val){
        	        $result[$k] = $val;
        	    }
        	}
        	return $result;
    }

    /**
     * 分销报表贡献度
     *
     */

    public function FxOrderReportModelByContribution($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shop_id'],
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800051',
        	'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
        );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        $list = $data[$params['targets']]['data'];
        return $list;
    }

    /*
     * 分销订单按地区统计
     *
     */
    public function FxOrderReportModelByState($data){
        $params = array(
			'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800052',
			'areas' => $data['stateIds']
        );

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        $list = $data[$params['targets']]['data'];

        return $list;
    }

    /*
     * 终端客户排行榜
     *
     */

    public function FxOrderReportModelByRank($data){
        $params = array(
			'dbName' => $this->getDbName(),
        	'shopID' => $data['shop_id'],
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800053',
			'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],

        );

        $data = $this->getReturnData($this->memoServiceUrl, $params);

        $data = json_decode($data,true);

        return  $data[$params['targets']]['data'];

    }

    /*
     * 多次购买客户分布
     *
     */

    public function FxOrderReportModelByFreq($data){
        $params = array(
			'dbName' => $this->getDbName(),
        	'shopID' => $data['shop_id'],
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800054',
        );

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        $list = $data[$params['targets']]['data'];
        return $list;
    }
    public function addDBIndex()
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'load' => 'true',
            'dbHost' => $this->getHostName(),
            'dbUser' => $this->getUserName(),
            'dbPass' => $this->getDbPasswd(),
        	'method' => 'sdop.loader.DBLoader.load',
			'hostName' => $_SERVER['SERVER_NAME'],
        	'targets' => '100001',
        );

        //$uri = $this->getInterfaceUrl('DBIndexManager', 'addDBIndex');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        return $data;
    }

    public function OrderReportModelByHour($data)
    {

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shop_id'],
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'dateType' => 'hour',
        	'targets' => '800007',
        	'method' => 'sdop.report.statistics.get'
        	);

        	//$uri = $this->getInterfaceUrl('Report', 'OrderReportModelByHour');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);

        	$list = $data[$params['targets']]['data'];
        	foreach($list as $v){
        	    foreach($v as $k=>$val){
        	        $result[$k] = $val;
        	    }
        	}

        	return $result;
    }

    public function getShopGoodsCount($data){

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'payStatus' => $data['pay_status'],
			'hasBuy' => $data['has_buy'],
			'allBuy' => $data['all_buy'],
			'goodsId' => implode(',',$data['goods_id']),
        	'targets' => '810012',
			'pageIndex' => $data['pageIndex'],
			'pageSize' => $data['pageSize'],
        	'method' => 'sdop.report.zoom.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('Report', 'OrderReportModelByHour');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);

        	$data = json_decode($data,true);
        	return  $data[$params['targets']]['data'];


    }
    /**
     * 获取一段时间内 每月 订单数及客户数(首页)
     */
    public function OrderMemberCountByMonth($data)
    {

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'targets' => '800002',
        	'dateType' => 'month',
        	'method' => 'sdop.report.statistics.get'
        	);

        	//$uri = $this->getInterfaceUrl('SdbEcorderOrders', 'OrderMemberCountByMonth');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);

        	$list = $data[$params['targets']]['data'];
        	ksort($list);
        	return $list;
    }

    /**
     * 获取一段时间内每天的订单总数 订单客户数 销售金额（首页）
     */
    public function OrderMemberAmountCountByDay($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'targets' => '800006',
        	'dateType' => 'day',
        	'method' => 'sdop.report.statistics.get'
        );
        
        if(!$params['shopId']){
            unset($params['shopId']);
        }

        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);
        $list = $data[$params['targets']]['data'];

        foreach($list as $v){
            foreach($v as $k=>$val){
                $key = date('Y-m-d',strtotime($k));
                $result[$key] = $val;
            }
        }
        ksort($result);
        return $result;
        //$uri = $this->getInterfaceUrl('SdbEcorderOrders', 'OrderMemberAmountCountByDay');
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 获取一段时间内的客户购买金额排名（首页）
     */
    public function TopAmountMemberIdByTime($data)
    {

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'targets' => '800003',
        	'method' => 'sdop.report.statistics.get',
        	'top' => $data['top']
        );
        //$uri = $this->getInterfaceUrl('SdbEcorderOrders', 'TopAmountMemberIdByTime');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);


        return $data[$params['targets']]['data'];
    }
    /**
     * 获取一段时间内客户购买频次（首页）
     */
    public function BuyFreqByTime($data)
    {
        if($data['ctl'] == 'admin_chart_member'){
            $targets = '800004';
        }else if($data['ctl'] == 'admin_analysis_buy'){
            $targets = '800009';
        }else if($data['ctl'] == 'admin_analysis_member'){
            $targets = '800011';
        }

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'targets' => $targets,
        	'method' => 'sdop.report.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('SdbEcorderOrders', 'BuyFreqByTime');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);

        	return $data[$targets]['data'];
    }

    /**
     * 获取一段时间内热销商品排名（首页）
     */
    public function TopSale($data)
    {
        if($data['ctl'] == 'admin_shop_goods'){
            $targets = '800017';
        }else{
            $targets = '800005';
        }
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'targets' => $targets,
        	'method' => 'sdop.report.statistics.get',
        	'top' => $data['top'],
        );

        //$uri = $this->getInterfaceUrl('SdbEcorderOrderItem', 'TopSale');
        $data = $this->getReturnData($this->memoServiceUrl,$params);

        $data = json_decode($data,true);
        $list = $data[$targets]['data'];

        foreach($list as $v){
            $result[$v['goods_id']] = $v;
        }

        return $result;

    }

    /**
     * 获取购物篮XY商品推荐
     */
    public function TopOrderItemXY($data)
    {

        if($data['ctl'] == 'admin_shop_goods'){
            $targets = '800018';
        }else{
            $targets = '800019';
        }
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'targets' => $targets,
        	'method' => 'sdop.report.statistics.get',
        	'top' => $data['top'],
        	'goodsId' => $data['goodsId'],
        );
        //$uri = $this->getInterfaceUrl('SdbEcorderOrderItem', 'TopOrderItemXY');
        $data = $this->getReturnData($this->memoServiceUrl, $params);

        $data = json_decode($data,true);
        $list = $data[$targets]['data'];
        foreach($list as $v){
            $arr[$v['X_Y']] = $v;
        }
        return $arr;
    }

    /**
     * 获取RFM模型报表
     */
    public function RFM($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
        	'targets' => '800015',
            'rTag' => $data['Rmain'],
            'fTag' => $data['Fmain'],
            'mTag' => $data['Mmain'],
        	'method' => 'sdop.report.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('Report', 'RFM');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];
    }

    /**
     * 获取RF模型报表
     */
    public function RF($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'targets' => '800014',
        	'method' => 'sdop.report.statistics.get',
        	'r1' => $data['r1'],
        	'r2' => $data['r2'],
            'r3' => $data['r3'],
            'f1' => $data['f1'],
        	'f2' => $data['f2'],
            'f3' => $data['f3']
        );

        //$uri = $this->getInterfaceUrl('Report', 'RF');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);

        return $data[$params['targets']]['data'];
    }

    /**
     * 根据时间获取新老客户统计信息
     */
    public function NewOldMemberAnalysis($data)
    {
        if($data['ctl'] == 'dashboard'){
            $targets = '800001';
        }else if($data['ctl'] == 'admin_analysis_tree'){
            $targets = '800010';
        }
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'targets' => $targets,
        	'method' => 'sdop.report.statistics.get'
        	);

        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'NewOldMemberAnalysis');
        	$data= $this->getReturnData($this->memoServiceUrl,$params);

        	$data = json_decode($data,true);
        	return $data[$targets]['data'];

    }

    public function AnalysisByFinishOrderCount($data)
    {
        if($data['greaterOrLess'] == '='){
            $symbol = 'eq';
        }else{
            $symbol = 'gt';
        }

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopId' => $data['shopId'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'finishOrderCount' => $data['finishOrderCount'],
            'greaterOrLess' => $symbol,
        	'targets' => '810005',
        	'method' => 'sdop.report.zoom.statistics.get'
        	);

        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'AnalysisByFinishOrderCount');
        	$data = $this->getReturnData($this->memoServiceUrl,$params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];

    }

    public function MemberAnalysisByTimeAndOrderStatus($data)
    {
        $params = array(
			'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
			'status' => $data['status'],
			'targets' => '810002',
			'method' => 'sdop.report.zoom.statistics.get'
			);
			//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberAnalysisByTimeAndOrderStatus');
			$data = $this->getReturnData($this->memoServiceUrl, $params);

			$data = json_decode($data,true);
			return $data[$params['targets']]['data'];
    }

    /**
     * 流失客户放大镜效果
     * Enter description here ...
     * @param unknown_type $data
     */
    public function MemberAnalysisByLose($data)
    {
        $params = array(
			'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
			'type' => $data['status'],
			'targets' => '810011',
			'method' => 'sdop.report.zoom.statistics.get'
			);
			$data = $this->getReturnData($this->memoServiceUrl, $params);
			$data = json_decode($data,true);
			return $data[$params['targets']]['data'];
    }

    /**
     * 
     * 客户销售订单单价分布图报表明细放大镜
     */
    public function MemberAnalysisByPrice($params)
    {
        $params = array(
                'method' => 'sdop.report.zoom.statistics.get',
                'targets' => 810017,
                'dbName' => $this->getDbName(),
                //'dbName' => 'db_5_309146',
                'beginTime' => $params['beginTime'] ? $params['beginTime'] : 0,
                'endTime' => $params['endTime'] ? $params['endTime'] : 0,
                'pageIndex' => $params['pageIndex'] ? $params['pageIndex'] : 1,
                'pageSize' => $params['pageSize'],
                'totalAmount1' => $params['totalAmount1'] ? $params['totalAmount1'] : 0,
                'totalAmount2' => $params['totalAmount2'] ? $params['totalAmount2'] : 0,
                'orderStatus' => $params['orderStatus'] ? $params['orderStatus'] : 'all',
                'shopId' => $params['shopId'] ? $params['shopId'] : '',
        );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[810017]['data'];
    }
    /**
     *复购周期明细/生命周期放大镜 
     */
    public function MemberAnalysisByLca($params)
    {
        switch($params['targets'])
        {
            case 1:
                $params['targets'] = 810015;
            break;
            default:
                $params['targets'] = 810016;
            break;
        }
        $params = array(
                'method' => 'sdop.report.zoom.statistics.get',
                'combineRange' => $params['combineRange'] ? $params['combineRange'] : 45,
                'beginTime' => $params['beginTime'] ? $params['beginTime'] : 0,
                'endTime' => $params['endTime'] ? $params['endTime'] : 0,
                'daysType' => $params['daysType'] ? $params['daysType'] : 45,
                'preType' => $params['preType'] ? $params['preType'] : 30,
                
                'pageIndex' => $params['pageIndex'] ? $params['pageIndex'] : 1,
                //'beginDays' => $params['beginDays'] ? $params['beginDays'] : 0,
                //'endDays' => $params['endDays'],
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : 100,
                'shopId' => $params['shopId'],
                'targets' => $params['targets'],
                'dbName' => $this->getDbName(),
        );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
    }
    /**
     * 根据时间类型及分组类型获取一组新老客户统计信息（新老客户数据）
     */
    public function MemberAnalysisByNewOldAndTime($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'newOrOld' => $data['newOrOld'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
        	'targets' => '810001',
        	'method' => 'sdop.report.zoom.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberAnalysisByNewOldAndTime');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);

        	$data = json_decode($data,true);

        	return $data[$params['targets']]['data'];
    }

    /**
     * 自定义客户分组
     */
    public function SearchMemberAnalysisList($data)
    {
        if ( ! $data['filter']['shop_id']) {
            $data['filter']['shop_id'] = $data['shop_id'];
        }

        $params = $this->getMemberPackFilter($data);
        $params['tagCalculation'] = (string)$data['filter']['tag_calculation'];
        $params['tagIdPlus'] = (string)$data['filter']['tags'];

        $params['targets'] = '810008';
        $params['method'] = 'sdop.report.zoom.statistics.get';
        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
    }

    /*
     * 客户列表客户明细
     */
    public function SearchMemberAnalysisByShop($data,$orderType){

        $params = array(
    		'dbName' => $this->getDbName(),
    		'method' => 'sdop.member.statistics.get',
    		'targets' => '820002',
    		'shopId' => $data['shop_id'],
        );

        //高级筛选条件
        if($data['filter']){
            $first_buy_time = $data['filter']['first_buy_time'];
            if($first_buy_time['min_val']){
                $data['filter']['first_buy_time']['min_val'] = strtotime($first_buy_time['min_val']);
            }
            if($first_buy_time['max_val']){
                $data['filter']['first_buy_time']['max_val'] = strtotime($first_buy_time['max_val']);
            }

            $last_buy_time = $data['filter']['last_buy_time'];
            if($last_buy_time['min_val']){
                $data['filter']['last_buy_time']['min_val'] = strtotime($last_buy_time['min_val']);
            }
            if($last_buy_time['max_val']){
                $data['filter']['last_buy_time']['max_val'] = strtotime($last_buy_time['max_val']);
            }
            $params['filter'] = json_encode($data['filter']);
        }

        $pageIndex = 1;
        if (isset($data['pageIndex'])) {
            $pageIndex = max(intval($data['pageIndex']), $pageIndex);
        }

        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $data['pageSize'];
        $order = explode(" ",trim($orderType));

        $params['orderType'] = $order[0];
        $params['order'] = $order[1];
        $result = $this->getReturnData($this->memoServiceUrl,$params);
        $result = json_decode($result,true);
        $arr['Count'] = $result[$params['targets']]['data']['Count'];

        foreach($result[$params['targets']]['data']['Value'] as $v){
            $arr['Value'][$v['MemberId']] = $v;
        }
        return $arr;
    }


    /*
     * 无效客户列表客户明细
     */
    public function SearchInvalidMemberAnalysisByShop($data,$orderType){

        $params = array(
    		'dbName' => $this->getDbName(),
    		'method' => 'sdop.member.statistics.get',
    		'targets' => '820005',
    		'shopId' => $data['shop_id'],
        );

        if(isset($data['invalidType'])){
            $params['invalidType'] = $data['invalidType'];
        }

        //高级筛选条件
        if($data['filter']){
            $first_buy_time = $data['filter']['first_buy_time'];
            if($first_buy_time['min_val']){
                $data['filter']['first_buy_time']['min_val'] = strtotime($first_buy_time['min_val']);
            }
            if($first_buy_time['max_val']){
                $data['filter']['first_buy_time']['max_val'] = strtotime($first_buy_time['max_val']);
            }

            $last_buy_time = $data['filter']['last_buy_time'];
            if($last_buy_time['min_val']){
                $data['filter']['last_buy_time']['min_val'] = strtotime($last_buy_time['min_val']);
            }
            if($last_buy_time['max_val']){
                $data['filter']['last_buy_time']['max_val'] = strtotime($last_buy_time['max_val']);
            }
            $params['filter'] = json_encode($data['filter']);
        }

        $pageIndex = 1;
        if (isset($data['pageIndex'])) {
            $pageIndex = max(intval($data['pageIndex']), $pageIndex);
        }

        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $data['pageSize'];
        $order = explode(" ",trim($orderType));

        $params['orderType'] = $order[0];
        $params['order'] = $order[1];
        $result = $this->getReturnData($this->memoServiceUrl,$params);
        $result = json_decode($result,true);
        $arr['Count'] = $result[$params['targets']]['data']['Count'];

        foreach($result[$params['targets']]['data']['Value'] as $v){
            $arr['Value'][$v['MemberId']] = $v;
        }
        return $arr;
    }

    /*
     * 营销活动中的无效客户列表客户明细
     */
    public function SearchInvalidMemberAnalysisByActivity($data,$orderType){

        $params = array(
    		'dbName' => $this->getDbName(),
    		'method' => 'sdop.member.statistics.get',
    		'targets' => '820006',
    		'shopId' => $data['shop_id'],
            'VoidId'=>$data['VoidId']
        );

        //高级筛选条件
        if($data['filter']){
            $first_buy_time = $data['filter']['first_buy_time'];
            if($first_buy_time['min_val']){
                $data['filter']['first_buy_time']['min_val'] = strtotime($first_buy_time['min_val']);
            }
            if($first_buy_time['max_val']){
                $data['filter']['first_buy_time']['max_val'] = strtotime($first_buy_time['max_val']);
            }

            $last_buy_time = $data['filter']['last_buy_time'];
            if($last_buy_time['min_val']){
                $data['filter']['last_buy_time']['min_val'] = strtotime($last_buy_time['min_val']);
            }
            if($last_buy_time['max_val']){
                $data['filter']['last_buy_time']['max_val'] = strtotime($last_buy_time['max_val']);
            }
            $params['filter'] = json_encode($data['filter']);
        }

        $pageIndex = 1;
        if (isset($data['pageIndex'])) {
            $pageIndex = max(intval($data['pageIndex']), $pageIndex);
        }

        $params['pageIndex'] = $pageIndex;
        $params['pageSize'] = $data['pageSize'];
        $order = explode(" ",trim($orderType));

        $params['orderType'] = $order[0];
        $params['order'] = $order[1];
        $result = $this->getReturnData($this->memoServiceUrl,$params);
        $result = json_decode($result,true);
        $arr['Count'] = $result[$params['targets']]['data']['Count'];

        foreach($result[$params['targets']]['data']['Value'] as $v){
            $arr['Value'][$v['MemberId']] = $v;
        }
        return $arr;
    }


    /**
     * 根据地域获取订单统计数据          销售报表页 地域分析
     */
    public function OrderReportModelByState($data)
    {

        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'areas' => $data['stateIds'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800008'
        	);

        	//$uri = $this->getInterfaceUrl('Report', 'OrderReportModelByState');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);

        	$list = $data[$params['targets']]['data'];
        	foreach($list as $v){
        	    foreach($v as $k=>$val){
        	        $result[$k] = $val;
        	    }
        	}

        	return $result;

    }

    /**
     * 根据时间、地域及付款状态获取客户 分析信息                        销售报表 地域分析 放大镜
     */
    public function MemberAnalysisByTimeStateAndOrderStatus($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'status' => $data['status'],
            'stateId' => $data['stateId'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
        	'targets' => '810003',
        	'isCity' => $data['is_city'],
        	'method' => 'sdop.report.zoom.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberAnalysisByTimeStateAndOrderStatus');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];
    }

    /**
     * 获取店铺客户数
     */
    public function MemberCountByShopId($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId']
        );
        $uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberCountByShopId');
        return $this->getReturnData($uri, $params);
    }

    /**
     * 获取客户等级统计信息
     */
    public function MemberInfoBySyslv($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800012'
        	);

        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberInfoBySyslv');
        	$data =  $this->getReturnData($this->memoServiceUrl, $params);

        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];


    }

    /**
     * 根据客户ID获取分析信息
     */
    public function AnalysisByMemberId($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'memberId' => $data['memberId'],
        	'targets' => '820003',
        	'method' => 'sdop.member.statistics.get',
        );

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'AnalysisByMemberId');
        $data =  $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);

        return $data[$params['targets']]['data'];
    }

    /**
     * 根据客户ID（字符串）获得客户数据
     */
    public function MemberAnalysisByMemberId($data,$orderType)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'memberIdStr' => $data['memberIdStr'],
        	'method' => 'sdop.member.statistics.get',
        	'targets' => '820004',
        );
        $order = explode(" ",trim($orderType));

        $params['orderType'] = $order[0];
        $params['order'] = $order[1];
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberAnalysisByMemberId');
        $result = $this->getReturnData($this->memoServiceUrl,$params);
        $result = json_decode($result,true);
        $arr['Count'] = $result[$params['targets']]['data']['Count'];

        foreach($result[$params['targets']]['data']['Value'] as $v){
            $arr['Value'][$v['MemberId']] = $v;
        }

        return $arr;
    }

    public function MemberAnalysisByTimeHourAndOrderStatus($data)
    {
        $week_name = array('星期天','星期一','星期二','星期三','星期四','星期五','星期六');
        if(array_search($data['day'], $week_name) === false){
            $data['day'] = intval($data['day']);
        }else{
            $data['day'] = array_search($data['day'], $week_name) + 1;
        }
    
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'status' => $data['status'],
            'hour' => $data['hour'],
            'day' => $data['day'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
            'timeType' => $data['timeType'],
        	'targets' => '810004',
        	'method' => 'sdop.report.zoom.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberAnalysisByTimeHourAndOrderStatus');
        	$data = $this->getReturnData($this->memoServiceUrl,$params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];
    }


    /**
     * 根据时间类型及分组类型获取一组新老客户统计信息                销售报表页 新老客户
     */
    public function NewOldMemberAnalysisByTimeType($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'dateType' => strtolower($data['type']),
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800013'
        	);
        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'NewOldMemberAnalysisByTimeType');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);
        	$list = $data[$params['targets']]['data'];

        	ksort($list);
        	return $list;
    }
    /**
     * 流失客户报表
     */
    public function LoseMemberAnalysisByTimeType($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'dateType' => strtolower($data['type']),
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800020'
        	);
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);
        	$list = $data[$params['targets']]['data'];
        	ksort($list);
        	return $list;
    }

    /**
     * 根据RFM值获取客户分析信息                   销售报表页 RFM分析 放大镜
     */
    public function AnalysisByRFM($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'r' => $data['r'],
            'rc' => $data['rc'],
            'f' => $data['f'],
            'fc' => $data['fc'],
            'm' => $data['m'],
            'mc' => $data['mc'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
        	'targets' => '810007',
        	'method' => 'sdop.report.zoom.statistics.get',

        );

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'AnalysisByRFM');
        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
    }

    /**
     * 根据RF值获取客户分析信息                      销售报表页 RF分析 放大镜
     */
    public function AnalysisByRF($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize']
        );
        $params['r1'] = empty($data['r1']) ? -1 : $data['r1'];
        $params['r2'] = empty($data['r2']) ? -1 : $data['r2'];
        $params['f1'] = empty($data['f1']) ? -1 : $data['f1'];
        $params['f2'] = empty($data['f2']) ? -1 : $data['f2'];
        $params['targets'] = '810006';
        $params['method'] = 'sdop.report.zoom.statistics.get';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'AnalysisByRF');
        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);

        return $data[$params['targets']]['data'];
    }
    /**
     * 根据商品XY获取客户统计信息  ---- 商品分析 关联商品 放大镜  --- 商品分析 购物篮分析 放大镜
     */
    public function AnalysisByXY($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'xGoodsId' => $data['xGoodsId'],
            'yGoodsId' => $data['yGoodsId'],
            'inOrOut' => $data['inOrOut'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
        	'method' => 'sdop.report.zoom.statistics.get',
        	'targets' => '810010',
        );
        $params['inOrOut'] = isset($data['inOrOut']) ? $data['inOrOut'] : 'IN';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'AnalysisByXY');
        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
    }

    /**
     * 销售漏斗          销售报表页 销售漏斗
     */
    public function ListByOrderStatus($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shopId'],
        	'beginTime' => $data['beginTime'],
        	'endTime' => $data['endTime'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800016'
        	);
        	$data = $this->getReturnData($this->memoServiceUrl,$params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];
    }

    /**
     * 获取店铺是否存在 "true" "false"
     */
    public function isExistsShop($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
        );
        $uri = $this->getInterfaceUrl('DBIndexManager', 'isExistsShop');
        return $this->getReturnData($uri, $params);
    }

    /**
     * 删除数据库索引 "true" "false"
     */
    public function removeDBIndex()
    {
        $params = array(
            'dbName' => $this->getDbName(),
        	'targets' => '100004',
        	'method' => 'sdop.loader.DBLoader.load'
        	);
        	//$uri = $this->getInterfaceUrl('DBIndexManager', 'removeDBIndex');
        	return $this->getReturnData($this->memoServiceUrl, $params);
    }

    /**
     * 添加订单
     */
    public function addOrder($data)
    {
        $params = array(

            'dbName' => $this->getDbName(),
            'orderId' => $data['orderId'],
            'shopId' => $data['shopId'],
            'memberId' => $data['memberId'],
            'status' => $data['status'],
            'payStatus' => $data['payStatus'],
            'createTime' => $data['createTime'],
            'totalAmount' => $data['totalAmount'],
            'itemNum' => $data['itemNum'],
            'stateId' => $data['stateId'],
            'shipStatus' => $data['shipStatus'],
        	'method' => 'sdop.trade.statistics.get',
        	'targets' => '600001',
        );
        //$uri = $this->getInterfaceUrl('DBIndexManager', 'addOrder');
        return $this->getReturnData($this->memoServiceUrl, $params);
    }

    /**
     * 添加订单明细
     */
    public function addOrderItem($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'itemId' => $data['itemId'],
            'memberId' => $data['memberId'],
            'amount' => $data['amount'],
            'createTime' => $data['createTime'],
            'orderId' => $data['orderId'],
            'goodsId' => $data['goodsId'],
            'nums' => $data['nums'],
        	'method' => 'sdop.trade.statistics.get',
        	'targets' => '600002',
        );
        //$uri = $this->getInterfaceUrl('DBIndexManager', 'addOrderItem');
        return $this->getReturnData($this->memoServiceUrl, $params);
    }

    /**
     * 获取单个数据库索引状态 "NULL" "READY" "LOADING"
     */
    public function DbIndexState()
    {
        $params = array(
            'dbName' => $this->getDbName(),
        	'method' => 'sdop.loader.DBLoader.load',
        	'targets' => '100002',
        );
        //$uri = $this->getInterfaceUrl('DBIndexManager', 'DbIndexState');
        $data = $this->getReturnData($this->memoServiceUrl, $params);

        $data = json_decode($data,true);
        return $data['status'];

    }

    /**
     * 获取数据所有店铺的订单及客户数
     */
    public function DBAllShopInfo($filter)
    {
        $params = array(
            'dbName' => $this->getDbName(),
        	'targets' => '820001',
        	'method' => 'sdop.member.statistics.get',
        //0 无效客户		1 所有客户
        	'status' => $filter['status'],
        );

        if(isset($filter['invalidType'])){
            $params['invalidType'] = $filter['invalidType'];
        }

        if(!self::$count){
            //$uri = $this->getInterfaceUrl('DBIndexManager', 'DBAllShopInfo');
            $data = $this->getReturnData($this->memoServiceUrl, $params);
            $data = json_decode($data,true);
            $list = $data[$params['targets']]['data'];
            foreach($list as $k=>$v){
                $arr[$k] = array('MemberCount'=>$v);
            }
            self::$count = $arr;
        }
        return self::$count;

        //return $this->getReturnData($uri, $params);
    }

    /**
     * 创建短信活动 人数预览
     */
    public function SMSTaskInfo($data)
    {
        $filter = $data['filter'];
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $params = $this->getMemberPackFilter($data);
        $params['personAB'] = $data['personAB'];
        $params['messageAB'] = $data['messageAB'];
        //不发送的时间
        $params['reSendTime'] = isset($data['reSendTime']) ? $data['reSendTime'] : time() - 86400;

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'SMSTaskInfo');
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 创建邮件、短信活动 人数预览
     */
    public function TaskInfo($data)
    {
        $filter = $data['filter'];
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $params = $this->getMemberPackFilter($data);
        $params['personAB'] = $data['personAB'];
        $params['messageAB'] = $data['messageAB'];
        //不发送的时间
        $params['reSendTime'] = $data['reSendTime'];
        //邮件短信标识
        $params['smsOrMail'] = $data['smsOrMail'];
        if (isset($params['pageIndex'])) {
            unset($params['pageIndex']);
        }
        if (isset($params['pageSize'])) {
            unset($params['pageSize']);
        }
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '830002';

        //err_log($params);

        $data = $this->getReturnData($this->memoServiceUrl, $params);

        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'TaskInfo');
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 创建短信活动
     */
    public function createSMSTaskInfo($data)
    {
        $filter = $data['filter'];
        if (isset($filter['filter'])) {
            $filter = $filter['filter'];
            $data['filter'] = $filter;

        }
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $params = $this->getMemberPackFilter($data);
        $params['personAB'] = $data['personAB'];
        $params['messageAB'] = $data['messageAB'];
        $params['tamplateA'] = $data['tamplateA'];
        $params['tamplateB'] = $data['tamplateB'];
        $params['tamplateB'] = $data['tamplateB'];
        $params['entId'] = $data['entId'];
        $params['entPwd'] = $data['entPwd'];
        $params['license'] = $data['license'];
        //$params['entId'] = '131303650372';
        //$params['entPwd'] = 'e4df808470cd95374ff687f4109a7f5c';
        //$params['license'] = '1322734439';
        $params['smsTemplateA'] = $data['smsTemplateA'];
        //$params['smsTemplateB'] = $data['smsTemplateB'];
        $params['smsTemplateB'] = $data['smsTemplateB'];
        $params['shopName'] = $data['shopName'];
        $params['taskId'] = $data['taskId'];
        //不发送的时间
        $params['reSendTime'] = $data['reSendTime'];
        //是否重复发送   1 = 重复发送， 0 = 不发送
        $params['reSend'] = $data['reSend'];

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'createSMSTaskInfo');
        return $this->getReturnData($this->memoServiceUrl, $params);
    }


    /**
     * 创建营销活动（邮件、短信）
     */
    public function createTask($data, &$err_msg)
    {
        $filter_mem = $data['filter'];
        if (isset($filter_mem['filter']) && $filter_mem['filter']) {
            $data['filter'] = null;
            $data = array_merge($data, $filter_mem);
        }

        if (!isset($filter_mem['shop_id'])) {
            $filter_mem['shop_id'] = $data['shop_id'];
        }

        //解析会员过滤条件
        $params = $this->getMemberPackFilter($data);
        
        //选择排除自定义小时后，24小时不发送默认为O
        if($params['recentSentHours'] > 0){
            $data['reSend'] = 'O';
        }
        
        $params['opUser'] = $data['opUser'];
        $params['ip'] = $data['ip'];
        $params['personAB'] = $data['personAB'];
        $params['messageAB'] = $data['messageAB'];
        $params['tamplateA'] = $data['tamplateA'];
        $params['tamplateB'] = $data['tamplateB'];
        $params['entId'] = $data['entId'];
        $params['entPwd'] = $data['entPwd'];
        $params['license'] = $data['license'];
        //$params['entId'] = '121202214684';
        //$params['entPwd'] = '7aac98b37a17cebbf45ae1c4ea82ee9f';
        //$params['license'] = '1322734439';
        $params['smsTemplateA'] = $data['smsTemplateA'];
        $params['smsTemplateB'] = $data['smsTemplateB'];
        $params['shopName'] = $data['shopName'];
        $params['taskId'] = $data['taskId'];
        //不发送的时间
        $params['reSendTime'] = $data['reSendTime'];
        //是否重复发送   1 = 重复发送， 0 = 不发送
        $params['reSend'] = $data['reSend'];
        //发送方式  fan-out  notice
        $params['sendType'] = 'fan-out';
        //邮件短信标识
        $params['smsOrMail'] = $data['smsOrMail'];
        //邮件标题mailTitle
        $params['mailTitle'] = $data['mailTitle'];
        //邮件发送者
        $params['mailFrom'] = $data['mailFrom'];
        //店铺SESSION KEY
        $params['sessionKey'] = $data['sessionKey'];
        //优惠价ID
        $params['couponId'] = $data['couponId'];
        $params['sourceTable'] = $data['sourceTable'] ? $data['sourceTable'] : 'sdb_market_active';
        if(isset($data['couponId'])){
            $params['fromNodeId'] = $data['fromNodeId'];
            $params['toNodeId'] = $data['toNodeId'];
            $params['channelType'] = $data['channelType'];
            $params['couponToken'] = $data['couponToken'];
            $params['logId'] = $data['logId'];
        }

        //定时发送
        if($data['isTiming']!=0 && $data['planTimestamp']){
            $params['isTiming'] = 1;
            $params['planTimestamp'] = ceil($data['planTimestamp']);
        }else{
            $params['isTiming'] = 0;
        }

        if($data['quartzAction']){
            $params['quartzAction'] = $data['quartzAction'];
        }

        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '840002';

        //err_log($params);die();

        //return $this->getReturnData($this->memoServiceUrl,$params);
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'createTask');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $err_msg = $data;
        $data = json_decode($data,true);
        return $data[$params['targets']]['data']['status'];
    }

    /**
     * 根据放大镜查询缓查询发送客户数量
     */
    public function SearchMemberAnalysisCountByCacheId($data)
    {
        $filter = $data['filter'];
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $cacheId = $data['cacheId'];
        unset($data['cacheId']);
        $params = $this->getReportMemberPackFilter($data);
        $params['cacheId'] = $cacheId;
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '830005';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'SearchMemberAnalysisCountByCacheId');
        $data =  $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);

        return $data[$params['targets']]['data']['Count'];

    }

    /**
     * 根据放大镜缓存ID查询活动预览信息
     */
    public function TaskInfoByCacheId($data)
    {
        $filter = $data['filter'];
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $params = $this->getReportMemberPackFilter($data);
        $params['personAB'] = $data['personAB'];
        $params['messageAB'] = $data['messageAB'];
        //不发送的时间
        $params['reSendTime'] = $data['reSendTime'];
        //邮件短信标识
        $params['smsOrMail'] = $data['smsOrMail'];
        $params['cacheId'] = $data['cacheId'];
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '830001';

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'TaskInfoByCacheId');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
        //return $this->getReturnData($uri, $params);
    }

    /*
     * 判断cache_id是否存在
     */
    public function TaskExsByCacheId($data)
    {
        $params['dbName'] = $this->getDbName();
        $params['cacheId'] = $data['cacheId'];
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '830006';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'TaskInfoByCacheId');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['exists'];
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 分销根据放大镜缓存ID查询活动预览信息
     */
    public function FxTaskInfoByCacheId($data)
    {
        $params['dbName'] = $this->getDbName();
        $params['shopId'] = $data['shop_id'];
        $params['cacheId'] = $data['cacheId'];
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '830051';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'TaskInfoByCacheId');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 创建邮件、短信活动 分销人数预览
     */
    public function FxTaskInfo($data)
    {
        $filter = $data['filter'];
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $params = $this->getFxMemberPackFilter($filter);
        //邮件短信标识
        //$params['smsOrMail'] = $data['smsOrMail'];
        if (isset($params['pageIndex'])) {
            unset($params['pageIndex']);
        }
        if (isset($params['pageSize'])) {
            unset($params['pageSize']);
        }
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '830052';
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data'];
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'TaskInfo');
        //return $this->getReturnData($uri, $params);
    }
    /**
     * 根据放大镜缓存创建营销活动
     */
    public function createTaskByCahceId($data, &$err_msg)
    {
        $filter = $data['filter'];
        if (isset($filter['filter'])) {
            $filter = $filter['filter'];
            $data['filter'] = $filter;

        }
        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $params = $this->getReportMemberPackFilter($data);
        $params['opUser'] = $data['opUser'];
        $params['ip'] = $data['ip'];
        $params['cacheId'] = $data['cacheId'];
        $params['taskId'] = $data['taskId'];
        $params['personAB'] = $data['personAB'];
        $params['messageAB'] = $data['messageAB'];
        $params['tamplateA'] = $data['tamplateA'];
        $params['tamplateB'] = $data['tamplateB'];
        $params['tamplateB'] = $data['tamplateB'];
        $params['entId'] = $data['entId'];
        $params['entPwd'] = $data['entPwd'];
        $params['license'] = $data['license'];
        $params['smsTemplateA'] = $data['smsTemplateA'];
        $params['smsTemplateB'] = $data['smsTemplateB'];
        $params['smsTemplateB'] = $data['smsTemplateB'];
        $params['shopName'] = $data['shopName'];
        //邮件标题mailTitle
        $params['mailTitle'] = $data['mailTitle'];
        //不发送的时间
        $params['reSendTime'] = $data['reSendTime'];
        //是否重复发送   1 = 重复发送， 0 = 不发送
        $params['reSend'] = $data['reSend'];
        //发送方式  fan-out  notice
        $params['sendType'] = 'fan-out';
        //邮件短信标识
        $params['smsOrMail'] = $data['smsOrMail'];
        //邮件人FROM
        $params['mailFrom'] = $data['mailFrom'];
        //店铺SESSION KEY
        $params['sessionKey'] = $data['sessionKey'];
        //优惠价ID
        $params['couponId'] = $data['couponId'];

        //定时发送
        if($data['isTiming']!=0 && $data['planTimestamp']){
            $params['isTiming'] = 1;
            $params['planTimestamp'] = ceil($data['planTimestamp']);
        }else{
            $params['isTiming'] = 0;
        }

        if($data['quartzAction']){
            $params['quartzAction'] = $data['quartzAction'];
        }

        $params['targets'] = '840001';
        $params['method'] = 'sdop.marketing.statistics.get';

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'createTaskByCahceId');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $err_msg = $data;
        $data = json_decode($data,true);
        return $data[$params['targets']]['data']['status'];
        //return $this->getReturnData($uri, $params);
    }

    /*
     * 分销短信发送
     */

    /**
     * 创建营销活动（邮件、短信）
     */
    public function FxcreateTask($data)
    {
        $filter = $data['filter'];

        if (!isset($filter['shop_id'])) {
            $filter['shop_id'] = $data['shop_id'];
        }
        $params = $this->getFxMemberPackFilter($filter);
        $params['tamplateA'] = $data['tamplate'];
        $params['entId'] = $data['entId'];
        $params['entPwd'] = $data['entPwd'];
        $params['license'] = $data['license'];
        $params['opUser'] = $data['opUser'];
        $params['ip'] = $data['ip'];
        //$params['entId'] = '121202214684';
        //$params['entPwd'] = '7aac98b37a17cebbf45ae1c4ea82ee9f';
        //$params['license'] = '1322734439';
        $params['smsTemplateA'] = $data['smsTemplate'];
        $params['dbName'] = $this->getDbName();
        $params['shopName'] = $data['shopName'];
        $params['taskId'] = $data['taskId'];
        //发送方式  fan-out  notice
        $params['sendType'] = 'fan-out';
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '840052';

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data']['status'];
    }

    /**
     * 分销根据放大镜缓存创建营销活动
     */
    public function FxcreateTaskByCahceId($data)
    {
        $params['shopId'] = $data['shop_id'];
        $params['cacheId'] = $data['cacheId'];
        $params['taskId'] = $data['taskId'];
        $params['tamplateA'] = $data['tamplate'];
        $params['opUser'] = $data['opUser'];
        $params['ip'] = $data['ip'];

        $params['entId'] = $data['entId'];
        $params['entPwd'] = $data['entPwd'];
        $params['license'] = $data['license'];
        $params['smsTemplateA'] = $data['smsTemplate'];
        $params['shopName'] = $data['shopName'];
        $params['dbName'] = $this->getDbName();
        //发送方式  fan-out  notice
        $params['sendType'] = 'fan-out';
        $params['targets'] = '840051';
        $params['method'] = 'sdop.marketing.statistics.get';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'createTaskByCahceId');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']]['data']['status'];
        //return $this->getReturnData($uri, $params);
    }


    /**
     * 根据时间、地域及付款状态获取客户 分析信息                      分销  销售报表 地域分析 放大镜
     */
    public function FxMemberAnalysisByTimeStateAndOrderStatus($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'status' => $data['status'],
        	'isCity' => $data['is_city'],
            'stateId' => $data['stateId'],
            'pageIndex' => $data['pageIndex'],
            'pageSize' => $data['pageSize'],
        	'targets' => '810053',
        	'method' => 'sdop.report.zoom.statistics.get'
        	);
        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'MemberAnalysisByTimeStateAndOrderStatus');
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];
    }
    /**
     * 根据用户ID创建营销活动人数预览
     */
    public function TaskInfoByMemberId($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'memberIdStr' => $data['memberIdStr'],
            'personAB' => $data['personAB'],
            'messageAB' => $data['messageAB'],
            'reSendTime' => $data['reSendTime'],
            'smsOrMail' => $data['smsOrMail'],
        	'method' => 'sdop.marketing.statistics.get',
        	'targets' => '830003'
        	);

        	//$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'TaskInfoByMemberId');
        	//$this->getReturnData($this->memoServiceUrl,$params);
        	$data = $this->getReturnData($this->memoServiceUrl, $params);
        	$data = json_decode($data,true);
        	return $data[$params['targets']]['data'];
    }

    //分销报表数据
    public function middleware_fx_member_report_data($filter, $offset, $limit, $orderType, $other = false)
    {
        $func = $filter['methodName'];
        if (isset($filter['packetName'])) {
            unset($filter['packetName']);
        }

        if (isset($filter['Resource']) && $filter['Resource']) {
            $resourceFunc = 'getResource' . ucfirst(strtolower($filter['Resource']));
            $memberIdStr = $this->$resourceFunc($filter);
            if (empty($memberIdStr)) {
                return '';
            }
            $filter['memberIdStr'] = $memberIdStr;
            if (isset($filter['memberInfoListMethod']) && $filter['memberInfoListMethod']) {
                $func = $filter['memberInfoListMethod'];
            }
        }

        $result = $this->$func($filter);
        if (isset($result['CacheId']) && $result['CacheId']) {
            $this->setCacheId($result['CacheId']);
            $this->setCacheIdCreateTime();
        }
        $data = array();
        if ($result) {
            $resultValue = $result['Value'];
            if (isset($result['Value']['Value'])) {
                $resultValue = $result['Value']['Value'];
            }
            if ($memberIdStr) {
                $resultValue = $result;
            }
            $data = $this->get_report_fx_member_list($resultValue);
        }
        return $data;
    }

    /**
     * 根据用户ID创建营销活动
     */
    public function createTaskByMemberIdList($data, &$err_msg)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'taskId' => $data['taskId'],
            'personAB' => $data['personAB'],
            'messageAB' => $data['messageAB'],
            'tamplateA' => $data['tamplateA'],
            'tamplateB' => $data['tamplateB'],
            'entId' => $data['entId'],
            'entPwd' => $data['entPwd'],
            'license' => $data['license'],
            'smsTemplateA' => $data['smsTemplateA'],
            'smsTemplateB' => $data['smsTemplateB'],
            'mailTitle' => $data['mailTitle'],
            'shopName' => $data['shopName'],
            'memberIdStr' => $data['memberIdStr'],
            'reSendTime' => $data['reSendTime'],
            'reSend' => $data['reSend'],
            'smsOrMail' => $data['smsOrMail'],
            'mailFrom' => $data['mailFrom'],
            'sessionKey' => $data['sessionKey'],
            'couponId' => $data['couponId'],
        	'method' => 'sdop.marketing.statistics.get',
        	'targets' => '840003',
        	'sendType' => 'fan-out',//发送方式  fan-out  notice
        );
        $params['opUser'] = $data['opUser'];
        $params['ip'] = $data['ip'];

        //定时发送
        if($data['isTiming']!=0 && $data['planTimestamp']){
            $params['isTiming'] = 1;
            $params['planTimestamp'] = ceil($data['planTimestamp']);
        }else{
            $params['isTiming'] = 0;
        }

        if($data['quartzAction']){
            $params['quartzAction'] = $data['quartzAction'];
        }

        //$params['entId'] = '121202214684';
        //$params['entPwd'] = '7aac98b37a17cebbf45ae1c4ea82ee9f';
        //$params['license'] = '1322734439';
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'createTaskByMemberIdList');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $err_msg = $data;
        $data = json_decode($data,true);
        return $data[$params['targets']]['data']['status'];
    }

    /**
     * 添加店铺ID
     */
    public function addShop($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId']
        );
        $uri = $this->getInterfaceUrl('DBIndexManager', 'addShop');
        return $this->getReturnData($uri, $params);
    }

    /**
     * 更新订单
     */
    public function updateOrder($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'orderId' => $data['orderId'],
            'shopId' => $data['shopId'],
            'memberId' => $data['memberId'],
            'status' => $data['status'],
            'payStatus' => $data['payStatus'],
            'createTime' => $data['createTime'],
            'totalAmount' => $data['totalAmount'],
            'itemNum' => $data['itemNum'],
            'stateId' => $data['stateId'],
            'shipStatus' => $data['shipStatus'],
        	'method' => 'sdop.trade.statistics.get',
        	'targets' => '600003',
        );
        //$uri = $this->getInterfaceUrl('DBIndexManager', 'updateOrder');
        return $this->getReturnData($this->memoServiceUrl, $params);
    }

    /**
     * 更新订单明细
     */
    public function updateOrderItem($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'itemId' => $data['itemId'],
            'memberId' => $data['memberId'],
            'amount' => $data['amount'],
            'createTime' => $data['createTime'],
            'orderId' => $data['orderId'],
            'goodsId' => $data['goodsId'],
            'nums' => $data['nums'],
        	'method' => 'sdop.trade.statistics.get',
        	'targets' => '600004',
        );
        //$uri = $this->getInterfaceUrl('DBIndexManager', 'updateOrderItem');
        return $this->getReturnData($this->memoServiceUrl, $params);
    }

    /**
     * 营销评估活动详情
     */
    public function ActiveTotalInfo($data)
    {
        $params = array(
			'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'taskId' => $data['taskId'],
            'execTime' => $data['execTime'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
        	'method' => 'sdop.marketing.statistics.get',
        	'targets' => '830004'
        );

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);

        return $data[$params['targets']]['data'];
    }

    /**
     * 营销评估活动客户详情
     */
    public function ActiveMemberInfo($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'taskId' => $data['taskId'],
            'execTime' => $data['execTime'],
            'orderStatus' => $data['orderStatus'],
            'beginTime' => $data['beginTime'],
            'endTime' => $data['endTime'],
            'group' => $data['group'],
            'pageIndex' => $data['pageIndex'],
        	'pageSize' => $data['pageSize'],
        	'method' => 'sdop.report.zoom.statistics.get',
        	'targets' => '810009',
        );

        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);

        return $data[$params['targets']]['data'];
        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'ActiveMemberInfo');
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 更新客户
     */
    public function updateMember($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopId' => $data['shopId'],
            'memberId' => $data['memberId'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
        	'name' => $data['name'],
        	'smsBlacklist' => $data['sms_blacklist'],
            'isVip' => $data['is_vip'],
            'edmBlacklist' => $data['edm_blacklist'],
            'birthday' => $data['birthday'],
        	'method' => 'sdop.member.statistics.get',
        	'targets' => '820007',
        );
        //$uri = $this->getInterfaceUrl('DBIndexManager', 'updateMember');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        return $data;
        //return $this->getReturnData($uri, $params);
    }

    /**
     * 返回接口数据
     * @param String $uri URI地址
     * @param array $params 接口参数
     */
    protected function getReturnData($uri, $params)
    {
        //$stime = time();
        $result = $this->post($uri, $params);

        //debug
        //elog($params);elog($result);

        /*if(strstr($uri, 'updateOrder')){
         error_log(date('Y-m-d H:i:s').'=====>'.(time()-$stime)."\n",3,DATA_DIR.'/middleware_updateOrder.log');
         }

         if(strstr($uri, 'addOrder')){
         error_log(date('Y-m-d H:i:s').'=====>'.(time()-$stime)."\n",3,DATA_DIR.'/middleware_addOrder.log');
         }*/

        if ($result == 'null') {
            $result = '';
        }
        return $result;
    }
    /**
     * 数据结果集合
     */
    public function getList($table_name, $filter, $offset, $limit, $orderType)
    {
         $func = $table_name . '_data';
         $result = $this->$func($filter, $offset, $limit, $orderType);

        return $result;
    }

    /**
     * 客户分析表数据
     */
    public function middleware_member_analysis_data($filter, $offset, $limit, $orderType, $other = false)
    {
        $external = array('pageIndex', 'pageSize', 'packetName', 'methodName', 'shop_id');
        $func = $filter['methodName'];
        if (isset($filter['packetName'])) {
            unset($filter['packetName']);
        }
        $page = max(0, ceil($offset / $limit)) + 1;
        $filter['pageIndex'] = $page;
        $filter['pageSize'] = $limit;
        if (isset($filter['shop_id']) && $filter['shop_id']) {
            $_GET['shop_id'] = $filter['shop_id'];
        }

        if (isset($filter['filter']['member_uname']) or isset($filter['filter']['member_name']) or isset($filter['filter']['member_mobile']) ) {
            $memberStr = $this->getMembersIdsStr($filter['filter'], $filter['shop_id'], $offset, $limit);
            $func = 'MemberAnalysisByMemberId';
            if ($memberStr) {
                $filter['shopId'] = $filter['shop_id'];
                $filter['memberIdStr'] = $memberStr;
            }
            else {
                return null;
            }
        }
        $result = $this->$func($filter,$orderType);

        $data = array();
        if ($result) {
            $resultValue = $result['Value'];
            if (isset($result['Value']['Value'])) {
                $resultValue = $result['Value']['Value'];
            }
            if ($memberStr) {
                $resultValue = $result['Value'];
            }
            if( ! $resultValue){
                $resultValue = $result;
            }
            $data = $this->get_report_member_list($resultValue, $orderType, $filter['shop_id']);
        }
        return $data;
    }

    //客户分析表数量
    public function middleware_member_analysis_count($filter)
    {
        $data = array();
        if (isset($filter['shop_id']) && $filter['shop_id']) {
            $data['shop_id'] = $filter['shop_id'];
            unset($filter['shop_id']);
        }
        $data['filter'] = $filter;
        $data['methodName'] = 'SearchMemberAnalysisList';
        $data['packetName'] = 'ShopMemberAnalysis';
        $result = $this->count($data);
    }

    //报表数据
    public function middleware_member_report_data($filter, $offset, $limit, $orderType, $other = false)
    {
        $func = $filter['methodName'];
        if (isset($filter['packetName'])) {
            unset($filter['packetName']);
        }

        if (isset($filter['Resource']) && $filter['Resource']) {
            $resourceFunc = 'getResource' . ucfirst(strtolower($filter['Resource']));
            $memberIdStr = $this->$resourceFunc($filter);
            if (empty($memberIdStr)) {
                return '';
            }
            $filter['memberIdStr'] = $memberIdStr;
            if (isset($filter['memberInfoListMethod']) && $filter['memberInfoListMethod']) {
                $func = $filter['memberInfoListMethod'];
            }
        }

        $result = $this->$func($filter);
        if (isset($result['CacheId']) && $result['CacheId']) {
            $this->setCacheId($result['CacheId']);
            $this->setCacheIdCreateTime();
        }
        
        $data = array();
        if ($result) {
            $resultValue = $result['Value'];
            if (isset($result['Value']['Value'])) {
                $resultValue = $result['Value']['Value'];
            }
            if ($memberIdStr) {
                $resultValue = $result;
            }
            if( ! $resultValue){
                $resultValue = $result;
            }
            $data = $this->get_report_member_list($resultValue);
        }
        return $data;
    }

    //报表数量
    public function middleware_member_report_count($filter)
    {
        $data = array();
        if (isset($filter['shop_id']) && $filter['shop_id']) {
            $data['shop_id'] = $filter['shop_id'];
            unset($filter['shop_id']);
        }
        $data['filter'] = $filter;
        return $this->getMemberCount($data);
    }

    public function formatPageFilter($data, $offset, $limit, $orderType)
    {
        $data['offset'] = $offset;
        $data['limit'] = $limit;
        return $data;
    }

    public function count($table, $filter = array())
    {
        if (isset($filter['methodName']) && $filter['methodName']) {
            if (method_exists(__CLASS__, $filter['methodName'])) {
                if (isset($filter['filter']['member_uname']) && $filter['filter']['member_uname']) {
                    $count = $this->getMembersIdsCount($filter['filter']['member_uname'], $filter['shop_id']);
                    return $count;
                }
                if (isset($filter['Count']) && $filter['Count'] > 0) {
                    return $filter['Count'];
                }
                $func = $filter['methodName'];
                unset($filter['methodName']);
                if (isset($filter['packetName'])) {
                    unset($filter['packetName']);
                }
                //用以下条件来区分统计总数
                $filter['pageSize'] = 0;
                //$filter['pageIndex'] = 1;
                $result = $this->$func($filter);
                if (isset($result['CacheId']) && $result['CacheId']) {
                    $this->setCacheId($result['CacheId']);
                    $this->setCacheIdCreateTime();
                }
                $count = 0;
                if ($result) {
                    $count = $result['Count'];
                }
                return $count;
            }
            else {
                die ($filter['methodName'] . " : method is not exist");
            }
        }
        
        if($filter['filter_type'] == 'analysis'){
            $func = 'get_member_list';
            $rs = $this->$func($filter);
            $rs = json_decode($rs, true);
            if (isset($rs['CacheId']) && $rs['CacheId']) {
                $this->setCacheId($rs['CacheId']);
            }
            return $rs['Count'];
        }else{
            $func = $table .'_count';
            $result = $this->$func($filter);
            return $result;
        }
    }

    public function testAddDBIndex()
    {
        //        $data = array(
        //            'dbName' => '01test',
        //            'load' => 'true',
        //            'dbHost' => '192.168.61.109:3306',
        //            'dbUser' => 'root',
        //            'dbPass' => '123456'
        //        );
        $data = array(
	            'dbName' => 'db_5_279635',
	            'load' => 'true',
	            'dbHost' => '192.168.10.60:3306',
	            'dbUser' => 'db_5_279635',
	            'dbPass' => '8c3a7ab8'
	            );
	            $result = $this->addDBIndex($data);
	            var_dump($result);
	            exit;
    }

    public function post($uri, $param)
    {
        isset($param['shopId']) && $this->shopId = $param['shopId'];

        if ($uri == '') {
            return '';
        }
        //        echo "URL:". $uri;
        //        echo "<br />参数：";
        //        echo "<pre>";
        //        print_r($param);
        //        exit;
        $http = $this->getHttp();
        //        $result = $http->post($uri, $param);
        return $http->post($uri, $param);
    }

    public function get($param, $uri)
    {
        if ($uri == '') {
            return '';
        }
        $http = $this->getHttp();
        return $http->get($uri, $param);
    }

    public function exportMemberList($data)
    {

        $params = $this->getMemberPackFilter($data);
        $params['method'] = 'sdop.statistics.download.get';
        $params['targets'] = '850001';
        $params['export_id'] = $data['export_id'];
        $data = $this->getReturnData($this->memoServiceUrl,$params);
        $data = json_decode($data,true);

        if(isset($data[$params['targets']]['errcode']) && $data[$params['targets']]['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }

    public function GoodsGroupCount($data)
    {
        $params = array(
			'dbName' => $this->getDbName(),
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800021',
        	'groupIds' => $data['group_ids'],
        );

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        $list = $data[$params['targets']]['data'];
        return $list;
    }

    public function GoodsBrandCount($data)
    {
        $params = array(
			'dbName' => $this->getDbName(),
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800021',
        	'brandIds' => $data['brand_ids'],
        );

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        $list = $data[$params['targets']]['data'];
        return $list;
    }

    public function GoodsBuyTimes($data)
    {
        $params = array(
			'dbName' => $this->getDbName(),
        	'beginTime' => $data['date_from'],
        	'endTime' => $data['date_to'],
        	'method' => 'sdop.report.statistics.get',
        	'targets' => '800022',
        	'goodsIds' => $data['goods_ids'],
        );

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        $list = $data[$params['targets']]['data'];
        return $list;
    }

    /**
     * 发起合并全局客户请求
     */
    public function requestBindMember($queueId, $fields, $shop_id='')
    {
        $params = array(
            'fields' => $fields,
            'method' => 'sdop.member.statistics.get',
            'targets' => '820021',
            'queueId'=>$queueId,
            'dbName'=>$this->getDbName(),
        );
        
        if($shop_id){
            $params['shopId'] = $shop_id;
        }

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'ActiveTotalInfo');
        $res = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($res,true);
        if($data[$params['targets']]){
            return $data[$params['targets']];
        }else{
            return $res;
        }
    }

    //执行周期营销
    public function requestActiveCycle($params)
    {
        $sys_params = array(
        	'method' => 'sdop.marketing.statistics.get',
        	'targets' => '840004',
        	'sendType' => 'fan-out',
            'dbName'=>$this->getDbName(),
        );
        $params['groupData'] = json_encode($this->getMemberPackFilter($params['groupData']));
        $params = array_merge($params, $sys_params);

        //err_log($params,'requestActiveCycle');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        //err_log($data,'requestActiveCycle');
        return $data[$params['targets']];
    }

    public function requestMemberInfo($memberId){
        $params = array(
			'memberId' => $memberId,
        	'method' => 'sdop.member.statistics.get',
        	'targets' => '820009',
            'queueId'=>$queueId,
            'dbName'=>$this->getDbName(),
        );

        //$uri = $this->getInterfaceUrl('ShopMemberAnalysis', 'ActiveTotalInfo');
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);
        return $data[$params['targets']];

    }
    
    /**
     * 自定义属性实时更新接口
     */
    public function SetMemberAttrInfo($data)
    {
        $params = array(
        	'dbName' => $this->getDbName(),
        	'shopID' => $data['shop_id'],
        	'targets' => '820010',
        	'method' => 'sdop.member.statistics.get',
        	'attrId' => $data['attrId'],
            'memberId' => $data['member_id'],
            'memberAttr1' => $data['attr1'],
            'memberAttr2' => $data['attr2'],
            'memberAttr3' => $data['attr3'],
            'memberAttr4' => $data['attr4'],
            'memberAttr5' => $data['attr5'],
            'memberAttr6' => $data['attr6'],
            'memberAttr7' => $data['attr7'],
            'memberAttr8' => $data['attr8'],
            'memberAttr9' => $data['attr9'],
            'memberAttr10' => $data['attr10']
        );
        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($data,true);

        return $data[$params['targets']]['data'];
    }
    
    //自动计算RFM配置参数
    public function getRFMConf($data)
    {
        $params = array(
            'dbName' => $this->getDbName(),
            'shopID' => $data['shop_id'],
            'targets' => '800060',
            'method' => 'sdop.report.statistics.get'
        );

        $resp = $this->getReturnData($this->memoServiceUrl, $params);
        $data = json_decode($resp,true);
        
        if(isset($data[$params['targets']]['data'])){
            $result = $data[$params['targets']]['data'];
        }else{
            $result = $resp;
        }
        
        return $result;
    }
    /**
     * 发起更新积分
     */
    public function requestUpdatePoint($shop_id,$member_id,$type=-1,$point,$point_desc='', $invalid_time,$points_type='other')
    {
        $params = array(
            'member_id' => $member_id,
            'point'=>$point,
            'point_desc'=>$point_desc,
            'invalid_time'=>$invalid_time,
            'points_type'=>$points_type,
            //'msg'=>$msg,
            'method' => 'taocrm.point.update',
            'type'=>$type
        );

        if($shop_id){
            $params['shop_id'] = $shop_id;
        }

        $res = $this->getReturnData(JAVA_NEW_URL, $params);
        $data = json_decode($res,true);
        return $data;
    }
    /**
     * 发起获取积分
     */
    public function requestGetPoint($member_id,& $msg,$shop_id,$node_id,$invalid_time)
    {
        $params = array(
            'member_id' => $member_id,
            'invalid_time'=>$invalid_time,
            'node_id'=>$node_id,
            'msg'=>$msg,
            'method' => 'taocrm.point.get'
        );

        if($shop_id){
            $params['shop_id'] = $shop_id;
        }

        $res = $this->getReturnData(JAVA_NEW_URL, $params);
        $data = json_decode($res,true);
        return $data;
    }
    /**
     * 发起获取积分
     */
    public function requestGetPointLog($shop_id,$member_id,$page_size,$page,& $msg,$p_type)
    {
        $params = array(
            'member_id' => $member_id,
            'page_size'=>$page_size,
            'page'=>$page,
            'msg'=>$msg,
            'p_type'=>$p_type,
            'method' => 'taocrm.pointlog.getlist'
        );

        if($shop_id){
            $params['shop_id'] = $shop_id;
        }

        $res = $this->getReturnData(JAVA_NEW_URL, $params);
        $data = json_decode($res,true);
        return $data;
    }
}
