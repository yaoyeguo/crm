<?php

class taocrm_ctl_admin_wangwangchat extends desktop_controller
{
    private static $shopObj = null;
    public $_extra_view;
    public $baseFilter = array();
    public $type = 1;
    public $shop_id = '';
    public $pageLimit = 20;
    protected static $hardeWareConnect = null;
    protected static $memberAnalysisObj = null;
    
    public function __construct($app)
    {
        parent::__construct($app);
        $type = isset($_GET['type']) ? $_GET['type'] : 1;
        $typeData = array(1, 2);
        if (!in_array($type, $typeData)) {
            $type = 1;
        }
        $this->type = $type;
        if ($type == 1) {
            $this->baseFilter['member_id|than'] = 0;
        }
        else {
            $this->baseFilter['member_id'] = 0;
        }
        $this->pagedata['type'] = $type;
        $this->_init();
    }
    
    public function _init()
    {
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
    
    public function index()
    {
        $title = '';
        if ($this->type == 1) {
            $title = '旺旺咨询下单客户';
        }
        else {
            $title = '旺旺咨询客户';
        }
        $shops = $this->getTaobaoShop();
        if ($this->shop_id == '') {
            $args['shop_id'] = $this->shop_id = $shops[0]['shop_id'];
        }
        else {
            $args['shop_id'] = $this->shop_id;
        }
        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        
        $page = isset($_GET['page']) ? max(1, $_GET['page']) : 1;
        
        //数据处理
        if ($_POST) {
            $params['shop_id'] = $_POST['shop_id'];
            $params['date_from'] = $_POST['date_from'];
            $params['date_to'] = $_POST['date_to'];
            $params['type'] = $this->type;
            $params['page'] = 1;
            $params['pageLimit'] = $this->pageLimit;
        }
        else {
            $params['shop_id'] = $args['shop_id'];
            $params['date_from'] = $args['date_from'];
            $params['date_to'] = $args['date_to'];
            $params['type'] = $this->type;
            $params['page'] = $page;
            $params['pageLimit'] = $this->pageLimit;
        }
        
        $data = $this->getMemberDataList($params);
        if ($data) {
            $pager = $this->getPager($params);
            $this->pagedata['pager'] = $pager;
        }
        $this->pagedata['shop_id']= $args['shop_id'];
        $this->pagedata['date_from'] = $args['date_from'];
        $this->pagedata['date_to'] = $args['date_to'];
        $this->pagedata['c_date_from'] = $args['c_date_from'];
        $this->pagedata['c_date_to'] = $args['c_date_to'];
        
        $this->pagedata['shops']= $shops;
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_wangwangchat&act=index&type='.$this->type;
        $this->pagedata['path']= $title;
        $this->pagedata['line_shop'] = 'false';
        $this->pagedata['wangwang_chat_data'] = $data; 
        $this->page('admin/wangwangjingling/chat_log_list.html');
    }
    
    private function getMemberDataList($params)
    {
        $model = $this->app->model('wangwang_shop_chat_log');
        $limit = $params['pageLimit'];
        $offset = ($params['page'] - 1) * $limit;
        $baseFilter = $this->baseFilter;
        $baseFilter['shop_id'] = $params['shop_id'];
        $baseFilter['chat_date|lthan'] = strtotime($params['date_to']) + 86400;
        $baseFilter['chat_date|bthan'] = strtotime($params['date_from']);
        $orderBy =  "chat_date desc";
        $data = $model->getList('*', $baseFilter, $offset, $limit, $orderBy);
        return $data;
    }
    
    private function getPager($params)
    {
        $model = $this->app->model('wangwang_shop_chat_log');
        $baseFilter = $this->baseFilter;
        $baseFilter['shop_id'] = $params['shop_id'];
        $baseFilter['chat_date|lthan'] = strtotime($params['date_to']) + 86400;
        $baseFilter['chat_date|bthan'] = strtotime($params['date_from']);
        $count = $model->count($baseFilter);
        $pageSize = $params['pageLimit'];
        $link = "index.php?app=taocrm&ctl=admin_wangwangchat&act=index&type={$params['type']}&page=%d";
        $total_page = ceil($count / $pageSize);
        $pager = $this->app->render()->ui()->pager( array ('current' => $params['page'], 'total' => $total_page, 'link' => $link ));
        return $pager;
    }
    
    public function index_back()
    {
        $baseFilter = $this->baseFilter;
        $extraFilter = $this->timeSearchBox();
        if ($extraFilter) {
            $baseFilter = array_merge($baseFilter, $extraFilter);
        }
        
        $view = (isset($_GET['view'])) ? max(0, intval($_GET['view'])) : 0;
        //店铺ID
        $shopIds = $this->getShopFullIds();
        if ($view != 0) {
            if (isset($shopIds[$view-1])) {
                $baseFilter['shop_id'] = $shopIds[$view-1];
            }
        }
        
        if ($_GET['type'] == 1) {
            $title = '旺旺咨询下单客户';
        }
        else {
            $title = '旺旺咨询客户';
        }
        $actions = array();
        $this->finder('taocrm_mdl_wangwang_shop_chat_log',array(
            'title'=> $title,
            'actions' => $actions,
            'base_filter'=>$baseFilter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'top_extra_view' => $this->_extra_view,
            'orderBy' => "chat_date desc",
        ));
    }
    
    public function _extra_view($filter)
    {
        
    }
    
    /**
     * 获得淘宝店铺
     * Enter description here ...
     */
    protected function getTaobaoShop()
    {
        $shopObj = $this->getShopObj();
        $filter = array('shop_type' => 'taobao', 'active' => 'true');
        $shopList = $shopObj->getList('shop_id,name', $filter);
        $taobaoShop = array();
        foreach ($shopList as $v) {
            $taobaoShop[$v['shop_id']] = $v['name'];
        }
        return $taobaoShop;
    }
    
    protected function getShopFullIds()
    {
        $model = $this->getShopObj();
        $shopList = $model->getList('shop_id,name');
        $shops = array();
        foreach ((array)$shopList as $v) {
            $shops[] = $v['shop_id'];
        }
        return $shops;
    }
    
    public function  _views()
    {
        $shopObj = $this->getShopObj();
        $wangwangShopChatLogModel = $this->app->model('wangwang_shop_chat_log');
        $filter = array('shop_type' => 'taobao', 'active' => 'true');
        $shopList = $shopObj->getList('shop_id,name', $filter);
        $baseFilter = $this->baseFilter;
        $sub_menu = array();
        $sub_menu[] = array(
            'label' => '全部',
            'filter' => array(),
            'optional' => false
        );
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label' => $shop['name'],
                'filter' => array('shop_id' => $shop['shop_id']),
                'optional' => false,
            );
        }
        $i = 0;
        foreach($sub_menu as $k => $v) {
            $v['filter'] = array_merge($v['filter'], $baseFilter);
            $sub_menu[$k]['addon'] = $wangwangShopChatLogModel->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++.'&type='.$_GET['type'];
        }
        return $sub_menu;
    }
    
    private function getShopObj()
    {
        if (self::$shopObj == null) {
            self::$shopObj = &app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
    }
    
    public function timeSearchBox()
    {
        $base_filter = array();
        if (!empty($_REQUEST)) {
            $base_filter['chat_date|lthan'] = $_REQUEST['time_to'] ? strtotime(date('Ymd', strtotime($_REQUEST['time_to'])))+24*60*60 : strtotime(date('Y-m-d'))+24*60*60;
            $base_filter['chat_date|bthan'] = $_REQUEST['time_from'] ? strtotime(date('Ymd', strtotime($_REQUEST['time_from']))) : strtotime(date('Y-m-d'))-24*60*60;
            $this->pagedata['time_to'] = $_REQUEST['time_to'] ? $_REQUEST['time_to'] : date('Y-m-d');
            $this->pagedata['time_from'] = $_REQUEST['time_from'] ? $_REQUEST['time_from'] : date('Y-m-d',time()-24*60*60);
        }
        //初始化日期
        $this->pagedata['datetype'] = 'day';
        $this->pagedata['today'] = date("Y-m-d");
        $this->pagedata['yesterday'] = date("Y-m-d", strtotime("1 days ago"));
        $now = date('Y-m-d');
        $this->pagedata['threedaysago_from'] = date ("Y-m-d",strtotime($now)-2*24*60*60);
        $this->pagedata['threedaysago_to'] = date ("Y-m-d",strtotime($now)+1*24*60*60);
        $this->pagedata['sevendaysago_from'] = date ("Y-m-d",strtotime($now)-6*24*60*60);
        $this->pagedata['sevendaysago_to'] = date ("Y-m-d",strtotime($now)+1*24*60*60);
        $this->pagedata['thirtydaysago_from'] = date ("Y-m-d",strtotime($now)-13*24*60*60);
        $this->pagedata['thirtydaysago_to'] = date ("Y-m-d",strtotime($now)+1*24*60*60);
        $this->pagedata['this_month_from'] = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $this->pagedata['this_month_to'] = date("Y-m-d", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
        $this->pagedata['last_month_from'] = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
        $this->pagedata['last_month_to'] = date("Y-m-d", mktime(23, 59, 59, date("m"), 0, date("Y")));
        $this->pagedata['every_from'] = '2010-01-01';
        $this->pagedata['every_to'] = $now;
        
        
        $this->_extra_view = array (
            'taocrm' => 'admin/wangwangjingling/time_header.html'
        );
        return $base_filter;
    }
    
    public function getMemberInfo()
    {
        $data = array();
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $app = app::get("taocrm");
            $chatLogModel = $app->model('wangwang_shop_chat_log');
            $info = $chatLogModel->dump(array('id' => $id));
            $member_id = $info['member_id'];
            if ($member_id > 0) {
                $this->shop_id = $info['shop_id'];
                $data = $this->getMemberData($member_id);
                $analysisMember = $this->getAnalysisMember($member_id);
                $lvInfo = $this->getMemberLvInfo($data['SysLv']);
                $data['lv_id'] = $lvInfo['name'];
                $analysisMember = $this->getAnalysisMember($member_id);
                $data['points'] = $analysisMember['points'];
                $data['is_vip'] = $analysisMember['is_vip'] == 'true' ?  '是' : '否';
                $data['shop_evaluation'] = $this->getShopEvaluation($analysisMember['shop_evaluation']);
            }
        }
//        echo json_encode($data);
//        exit;
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['analysis'] = $data;
        return $render->display('admin/member/analysis.html');
    }
    
    /**
     * 获得接口客户数据
     */
    private function getMemberData($memberId)
    {
        $connect = $this->getConnect();
        $shopId = $this->getShopId();
        $filter = array('shopId' => $shopId, 'memberId' => $memberId);
        $result = $connect->AnalysisByMemberId($filter);
        $data = array();
        if ($result) {
            //订单总数
            $data['total_orders'] = $result['TotalOrders'];
            //订单总金额
            $data['total_amount'] = $result['TotalAmount'];
            //平均订单价
            $data['total_per_amount'] = number_format($result['TotalPerAmount'], 2);
            //客户积分
            //客户等级
            //退款订单数
            $data['refund_orders'] = $result['RefundOrders'];
            //退款订单金额
            $data['refund_amount'] = $result['RefundAmount'];
            //成功的订单数
            $data['finish_orders'] = $result['FinishOrders'];
            //成功的订单金额
            $data['finish_total_amount'] = $result['FinishTotalAmount'];
            //成功平均订单价
            $data['finish_per_amount'] = number_format($result['FinishPerAmount'], 2);
            //未付款订单数
            $data['unpay_orders'] = $result['UnpayOrders'];
            //未付款订单金额
            $data['unpay_amount'] = $result['UnpayAmount'];
            //未支付平均订单价
            $data['unpay_per_amount'] = $result['UnpayOrders'] > 0 ? number_format($result['UnpayAmount'], 2) / $result['UnpayOrders'] : 0;
            //购买频次(天)
            $data['buy_freq'] = $result['BuyFreq'];
            //平均购买间隔(天)
            $data['avg_buy_interval'] = $result['AvgBuyInterval'];
            //购买月数
            $data['buy_month'] = $result['BuyMonth'];
            //下单商品种数
            //$data['buy_skus'] = $result['BuyProductsCount'];
//            if (count($result['GoodsId']) == 1 && $result['GoodsId'][0] == 0) {
//                $data['buy_skus'] = count($result['OrderIdList']);
//            }
//            else {
//                $data['buy_skus'] = $result['BuyProductsCount'];
//            }
            //$data['buy_skus'] = $this->mergeGoods($result['OrderIdList']);
            $goodsArr = $this->mergeGoods($result['OrderIdList']);
            $data['buy_skus'] = $goodsArr['count'];
            //下单商品总数
            $data['buy_products'] = $result['BuyProductsNum'];
            //平均下单商品种数
            $data['avg_buy_skus'] = $result['TotalOrders'] > 0 ? number_format($data['buy_skus'] / $result['TotalOrders'], 2) : 0;
            //平均下单商品件数
            $data['avg_buy_products'] = $result['TotalOrders'] > 0 ? number_format($result['BuyProductsNum'] / $result['TotalOrders'], 2) : 0;
            //第一次下单时间
            $data['first_buy_time'] = date("Y-m-d H:i:s", $result['MinCreateTime']);
            //最后下单时间
            $data['last_buy_time'] = date("Y-m-d H:i:s", $result['MaxCreateTime']);
            //购买商品商品ID
            $data['GoodsId'] = $result['GoodsId'];
            //系统等级
            $data['SysLv'] = $result['SysLv'];
            //店铺评价
            //VIP
            //订单列表
            $data['OrderIdList'] = $result['OrderIdList'];
        }
        return $data;
    }
    
    protected function getConnect()
    {
        if (self::$hardeWareConnect == null) {
            self::$hardeWareConnect = new taocrm_middleware_connect;
        }
        return self::$hardeWareConnect;
    }
    
    protected function getShopId()
    {
        return $this->shop_id;
    }
    
    /**
     * 合并商品
     */
    protected function mergeGoods($orderIdList)
    {
        $orders = $orderIdList;
        $goods = array();
        $goodsArr = array();
        if($orders) {
//            $sql = "select bn,name,sum(nums) as nums,sum(amount) as amount from sdb_ecorder_order_items where order_id in (".implode(',',$orders).")
//            group by goods_id";
            $sql = "SELECT order_id, bn, `name`, nums, amount FROM `sdb_ecorder_order_items` WHERE order_id in (".implode(',',$orders).")";
            $goods = kernel::database()->select($sql);
        }
        $num = 0;
        if ($goods) {
            $formatGoods = array();
            foreach ($goods as $k => $v) {
                if (isset($formatGoods[$v['name']])) {
                    if ($formatGoods[$v['name']]['bn'] == '' && $v['bn'] != '') {
                        $formatGoods[$v['name']]['bn'] = $v['bn'];
                    }
                    $formatGoods[$v['name']]['nums'] += $v['nums'];
                    $formatGoods[$v['name']]['amount'] += $v['amount'];
                }
                else {
                    $formatGoods[$v['name']] = $v;
                }
            }
            $goodsArr['count'] = count($formatGoods);
            foreach ($formatGoods as $v) {
                $goodsArr['item'][] = $v;
            }
        }
        return $goodsArr;
    }
    /**
     * 获得客户分析表客户信息
     */
    protected function getAnalysisMember($memberId)
    {
        $shopId = $this->getShopId();
        $model = $this->getMemberAnalysisObj();
        $filter = array('member_id' => $memberId, 'shop_id' => $shopId);
        return $model->dump($filter);
        
    }
    
    protected function getMemberAnalysisObj()
    {
        if (self::$memberAnalysisObj == null) {
            self::$memberAnalysisObj = app::get('taocrm')->model('member_analysis');
        }
        return self::$memberAnalysisObj;
    }
    
    /**
     * 获得客户等
     */
    protected function getMemberLvInfo($lv_id)
    {
        $data = array();
        if (!$lv_id == '') {
            $model = $this->getMemberAnalysisObj();
            $sql = "SELECT `lv_id`, `name` FROM `sdb_ecorder_shop_lv` WHERE lv_id = {$lv_id}";
            $result = $model->db->select($sql);
            if ($result) {
                $data = $result[0];
            }
        }
        return $data;
    }
    /**
     * 获得店铺等级名称
     */
    protected function getShopEvaluation($key)
    {
        $type = $this->getDbShemaColumns('shop_evaluation');
        $data =  (isset($type[$key]) ? $type[$key] : '');
        return $data;
    }
    /**
     * 获得客户分析表字段
     */
    protected function getDbShemaColumns($field)
    {
        $model = $this->getMemberAnalysisObj();
        $columns = $model->_columns();
        $type = array();
        if (isset($columns[$field])) {
            $type = $columns[$field]['type'];
        }
        return $type;
    }
}
