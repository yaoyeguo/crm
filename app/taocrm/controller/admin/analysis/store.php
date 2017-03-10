<?php
class taocrm_ctl_admin_analysis_store extends desktop_controller
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

    public function stores()
    {
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,name,node_type');
        $type_list = $this->getChannelTypeList();
        foreach($rs as $v){
            $shop_ids[] = $v['shop_id'];
            $shop_names[$v['shop_id']] = $v['name']; 
            if(!is_null($v['node_type']))
            {
                $node_type[$v['node_type']] = $type_list[$v['node_type']];
            }
        }
        $shop_ids = implode(',',$shop_ids);
        $params = array(
                'shop_ids' => $shop_ids, 
                'date_from' => strtotime($this->date_from),
                'date_to' => strtotime($this->date_to),
                'date_type' => strtolower(!empty($_POST['count_by']) ? $_POST['count_by'] : 'WEEK'),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanStores($params);
        $all_data = $all_res[800028]['data'];
        foreach($all_data as $key =>  $store_list)
        {
            foreach($store_list as $k => $store)
            {   
               $all_data[$key][$k]['name'] = $shop_names[$store['shopId']];
            }
        }
        $this->pagedata['all_data'] = $all_data; 
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_store&act=stores';
        $this->pagedata['date_from'] = $this->date_from;
        $this->pagedata['date_to']	= $this->date_to;
        $this->pagedata['count_by'] = !empty($_POST['count_by']) ? $_POST['count_by'] : 'WEEK';

        $this->page('admin/analysis/store/stores.html');
    }

    function getChannelTypeList(){

        return array(
            'unknow'=>'未知',
            'manual_entry'=>'手动录入',
            'taobao'=>'淘宝', 
            'paipai'=>'拍拍', 
            '360buy'=>'京东商城',
            'shopex_b2c'=>'48体系网店',
            'ecos.b2c'=>'ec-store',
            'shopex_b2b'=>'shopex分销王',
            'ecos.dzg'=>'shopex店掌柜',
            'yihaodian'=>'一号店',
            'fenxiao'=>'淘宝分销',
            'amazon'=>'亚马逊',
            'dangdang'=>'当当',
            'alibaba'=>'阿里巴巴',
            'ecos.ome'=>'后端业务处理系统',
            'ecshop_b2c' => 'ecshop',
            'other'=>'其它'
        );
    }
    
    public function stores_map()
    {
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,name,node_type');
        $type_list = $this->getChannelTypeList();
        foreach($rs as $v){
            $shop_ids[] = $v['shop_id'];
            $node_type[$v['shop_id']] = $v['name'];
        }
        $shop_ids = implode(',',$shop_ids);
        $params = array(
                'shop_ids' => $shop_ids, 
                'date_from' => strtotime($_GET['date_from']),
                'date_to' => strtotime($_GET['date_to']),
                'date_type' => strtolower($_GET['count_by']),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanStores($params);
        $all_data = $all_res[800028]['data'];
        empty($_GET['order_status']) && $_GET['order_status'] = 'totalMembers';
        foreach($all_data as $k => $v){
            foreach($v as $sk => $store)
            {
                $data_store[$store['shopId']] = $store[$_GET['order_status']];
            }
            
            $dataset[] = array_merge(array('x' => $k),$data_store);
        }
        foreach($dataset as $k=>$v)
        {
            $dataset[$k] = $v;
        }

        $chartData = json_encode($dataset);
        $this->pagedata['chartLabel'] = $node_type;
        $this->pagedata['chartData'] = $chartData;

        $this->display("admin/analysis/store/stores_map.html");
    }

    //店铺成交价格分析
    public function price()
    {
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        $shops[] = '全部店铺';

        $args['date_from'] = $this->date_from;
        $args['date_to'] = $this->date_to;
        $params = array(
            'shop_id' => $_POST['shop_id'] ? $_POST['shop_id'] : '', 
            'order_status' => $_POST['order_status'] ? $_POST['order_status'] : 'all', 
            'date_from' => strtotime($this->date_from),
            'date_to' => strtotime($this->date_to),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanPrice($params);
        $all_data = $all_res[800031]['data'];
        foreach($all_data as $key =>  $data)
        {
            $amount = explode('-',$key);
            $all_data[$key]['totalAmount1'] = $amount[0];
            $all_data[$key]['totalAmount2'] = $amount[1];
        }

        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']	= $_POST['shop_id'] ? $_POST['shop_id'] : '0';
        $this->pagedata['order_status']	= $_POST['order_status'] ? $_POST['order_status'] : 'all';
        $this->pagedata['all_data'] = $all_data; 
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_store&act=price';
        $this->pagedata['date_from'] = $this->date_from;
        $this->pagedata['date_to']	= $this->date_to;
        $this->page('admin/analysis/store/price.html');
    }

    public function price_map()
    {
        $params = array(
                'order_status' => $_GET['order_status'],
                'shop_id' => $_GET['shop_id'],
                'date_from' => strtotime($_GET['date_from']),
                'date_to' => strtotime($_GET['date_to']),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanPrice($params);
        $all_data = $all_res[800031]['data'];
        if(!$all_data)
        {
            switch($params['order_status'])
            {
                case 'all':
                    $orders = $data['TotalOrders'];
                    $members = $data['TotalMembers'];
                    break;
                case 'pay':
                    $orders = $data['PayOrders'];
                    $members = $data['PayMembers'];
                    break;
                case 'finish':
                    $orders = $data['FinishOrders'];
                    $members = $data['FinishMembers'];
                    break;
            }
            $dataset[] = array(
                'x' => $k,
                'y1' => $orders,
                'y2' => $members,
                'y3' => $data['OrderRate']*100,

            );
            echo '没有数据';exit;
        }
        $chartLabel = array('y1'=>'订单数','y2'=>'会员数','y3'=>'订单占比');
        foreach($all_data as $k => $data)
        {
            $dataset[] = array(
                'x' => $k,
                'y1' => $data['TotalOrders'],
                'y2' => $data['TotalMembers'],
                'y3' => $data['OrderRate']*100,

            );
        }
        $chartData = json_encode($dataset);
        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;

        $this->display("admin/analysis/store/price_map.html");
    }

    public function channel()
    {
        $params = array(
            'date_from' => strtotime($this->date_from),
            'date_to' => strtotime($this->date_to),
            'date_type' => strtolower(!empty($_POST['count_by']) ? $_POST['count_by'] : 'WEEK'),
        );
        $type_list = $this->getChannelTypeList();
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanChannel($params);
        $all_data = $all_res[800029]['data'];
        foreach($all_data as $key =>  $store_list)
        {
            foreach($store_list as $k => $store)
            {   
               $all_data[$key][$k]['type'] = $type_list[$k];
            }
        }

        $this->pagedata['all_data'] = $all_data; 
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_store&act=channel';
        $this->pagedata['date_from'] = $this->date_from;
        $this->pagedata['date_to']	= $this->date_to;
        $this->pagedata['count_by'] = !empty($_POST['count_by']) ? $_POST['count_by'] : 'WEEK';
        $this->page('admin/analysis/store/channel.html');
    }

    public function channel_map()
    {
        $type_list = $this->getChannelTypeList();
        $params = array(
                'date_from' => strtotime($_GET['date_from']),
                'date_to' => strtotime($_GET['date_to']),
                'date_type' => strtolower($_GET['count_by']),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanChannel($params);
        $all_data = $all_res[800029]['data'];
        empty($_GET['order_status']) && $_GET['order_status'] = 'totalMembers';
        foreach($all_data as $k => $v){
            foreach($v as $sk => $store)
            {
                $data_store[$sk] = $store[$_GET['order_status']];
                $node_type[$sk] = $type_list[$sk];
            }
            $dataset[] = array_merge(array('x' => $k),$data_store);
        }
        foreach($dataset as $k=>$v)
        {
            $dataset[$k] = $v;
        }

        $chartData = json_encode($dataset);
        $this->pagedata['chartLabel'] = $node_type;
        $this->pagedata['chartData'] = $chartData;

        $this->display("admin/analysis/store/stores_map.html");
    }

    public function forecast()
    {
        $_POST['use_months'] = 100;
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,node_type,name');
        $type_list = $this->getChannelTypeList();
        foreach($rs as $v){
            if(!$args['shop_id'])
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
            if(!is_null($v['node_type']))
            {
                $shop_types[$v['node_type']] = $type_list[$v['node_type']];
            }
        }
        $shops[] = '全部店铺';
        $shop_types[] = '全部渠道';
        $use_months = array(
            '0' => '全部数据',
            '3' => '近3个月数据',
            '6' => '近6个月数据',
            '12' => '近12个月数据',
            '24' => '近24个月数据',
        );
        $scopes = array(
            'month' => '月预测',
            'quarter' => '季度预测',
            'year' => '年度预测',
        );
        $this->pagedata['shop_types']= $shop_types;
        $this->pagedata['shops']= $shops;
        $this->pagedata['use_months']= $use_months;
        $this->pagedata['scopes']= $scopes;
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_store&act=forecast';
        $this->pagedata['shop_id']	= $_POST['shop_id'] ? $_POST['shop_id'] : '0';
        $this->pagedata['shop_type']	= $_POST['shop_type'] ? $_POST['shop_type'] : '0';
        $this->pagedata['use_month']	= $_POST['use_month'] ? $_POST['use_month'] : '0';
        $this->pagedata['scope']	= $_POST['scope'] ? $_POST['scope'] : 'month';
        
        $params = array(
            'shop_id' => $_POST['shop_id'] ? $_POST['shop_id'] : '', 
            'status' => !empty($_POST['status']) ? $_POST['status'] : 'pay',
            'shop_type' => !empty($_POST['shop_type']) ? $_POST['shop_type'] : '',
            'use_months' => !empty($_POST['use_month']) ? intval($_POST['use_month']) : '',
            'scope' => !empty($_POST['scope']) ? $_POST['scope'] : 'month',
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanForecast($params);
        $all_data = $all_res[860001]['data'];

        $this->pagedata['all_data'] = $all_data; 
        $this->page('admin/analysis/store/forecast.html');
    }

    public function forecast_map()
    {
        $params = array(
            'shop_id' => !empty($_GET['shop_id']) ? $_GET['shop_id'] : '',
            'status' => !empty($_GET['status']) ? $_GET['status'] : 'pay',
            'targets' => !empty($_GET['report_type']) ? $_GET['report_type'] : 'a',
            'shop_type' => !empty($_GET['shop_type']) ? $_GET['shop_type'] : '',
            'use_months' => !empty($_GET['use_month']) ? intval($_GET['use_month']) : '',
            'scope' => !empty($_GET['scope']) ? $_GET['scope'] : 'month',
        );
        switch($params['targets'])
        {
             case 'a':
                 $params['targets'] = '860001';
                 break;
             case 'b':
                 $params['targets'] = '860002';
                 break;
             case 'c':
                 $params['targets'] = '860003';
                 break;
        }
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanForecast($params);
        $all_data = $all_res[$params['targets']]['data'];
        if(!$all_data)
        {
            echo '没有数据';exit;
        }
        $count = count($all_data);
        foreach($all_data as $k => $v){
            $data_type = $v['flag'] == 'src' ? '（基准值）' : '（预测值）';
            $dataset[] = array(
                'lineColor' => $v['flag'] == 'src' ? '#b7e021' : '#2498d2',
                'date' => $k.$data_type,
                'duration' => $v['totalAmount'],
            );
        }
        $dataset[$count-2]['lineColor'] = '#2498d2';

        $chartData = json_encode($dataset);
        $this->pagedata['chartData'] = $chartData;

        $this->display("admin/analysis/store/forecast_map.html");
    }
}
