<?php 

class ecgoods_ctl_admin_shop_basket extends desktop_controller{

    var $workground = 'ecgoods.goods';
    var $unuse_words = array('补运费','补差价','不拍不送','邮费专拍','拍下无效');
    
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
        
        if($_POST['date_from']==$_POST['date_to']){
            $_POST['date_to'] = date('Y-m-d', strtotime('+1 days', strtotime($_POST['date_from'])));
        }
        
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
        //暂时改走数据库
        //$this->index_back();die();
        
        $shop_id = $this->shop_id;
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $rs = $shopObj->getList('shop_id,name,node_type');
        foreach($rs as $v){
            if($v['node_type'] != 'taobao')continue;
            
            if(!$shop_id) $shop_id = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $data = $this->getTopOrderItemXYData($shop_id);
        $this->pagedata['form_action'] = '?app=ecgoods&ctl=admin_shop_basket&act=index';
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['shops'] = $shops;
        $this->pagedata['analysis_data'] = $data;
        $this->pagedata['date_from'] = $this->date_from;;
        $this->pagedata['date_to'] = $this->date_to;
        $this->page('goods/basket.html');
    }
    
    protected function getTopOrderItemXYData($shopId)
    {
        $connect = kernel::single('taocrm_middleware_connect');
        $params = array();
        $date_from = $this->date_from;
        $date_to = $this->date_to;
        $params['beginTime'] = strtotime($date_from);
        $params['endTime'] = strtotime($date_to);
        $params['shopId'] = $shopId;
        $params['top'] = 100;
        //$params['goodsId'] = -1;
        //$result = json_decode($connect->TopOrderItemXY($params), true);
        $result = $connect->TopOrderItemXY($params);
        $data = array();
        if ($result) {
            $goodsIds = array();
            foreach ($result as $k => $v) {
                array_push($goodsIds, $v['X']);
                array_push($goodsIds, $v['Y']);
            }
            $goodsIds = array_unique($goodsIds);
            sort($goodsIds);
            $ecgoodsModel = app::get('ecgoods')->model('shop_goods');
            $ecgoodsInfo = $ecgoodsModel->getList('*', array('goods_id|in' => $goodsIds));
            $ecgoodsInfoOfGoodsIds = array();
            foreach ($ecgoodsInfo as $v) {
                $ecgoodsInfoOfGoodsIds[$v['goods_id']] = $v;
            }
            $i = 0;
//            foreach ($result as $v) {
//                $data[$i]['count'] = $v['xyOrderCount'];
//                $data[$i]['members'] = $v['xyMemberCount'];
//                $data[$i]['order'] = $i + 1;
//                $data[$i]['good_x']['pic_url'] = $ecgoodsInfoOfGoodsIds[$v['X']]['pic_url'];
//                $data[$i]['good_x'][0] = $v['X'];
//                $data[$i]['good_x'][1] = $ecgoodsInfoOfGoodsIds[$v['X']]['outer_id'];
//                $data[$i]['good_x'][2] = mb_substr($ecgoodsInfoOfGoodsIds[$v['X']]['name'], 0, 25, "UTF-8");
//                $data[$i]['good_y']['pic_url'] = $ecgoodsInfoOfGoodsIds[$v['Y']]['pic_url'];
//                $data[$i]['good_y'][0] = $v['Y'];
//                $data[$i]['good_y'][1] = $ecgoodsInfoOfGoodsIds[$v['Y']]['outer_id'];
//                $data[$i]['good_y'][2] = mb_substr($ecgoodsInfoOfGoodsIds[$v['Y']]['name'], 0, 25, "UTF-8");
//                $data[$i]['xy'] = array(max($v['X'], $v['Y']), min($v['X'], $v['Y']));
//                $xyKey = max($v['X'], $v['Y']) . "-" .min($v['X'], $v['Y']);
//                $data[$i][$xyKey] = array(max($v['X'], $v['Y']), min($v['X'], $v['Y']));
//                $i++;
//            }
            foreach ($result as $v) {
                $xyKey = max($v['X'], $v['Y']) . "-" .min($v['X'], $v['Y']);
                if (!isset($data[$xyKey])) {
                    $data[$xyKey]['count'] = $v['xyOrderCount'];
                    $data[$xyKey]['members'] = $v['xyMemberCount'];
                    $data[$xyKey]['order'] = $i + 1;
                    $data[$xyKey]['good_x']['pic_url'] = $ecgoodsInfoOfGoodsIds[$v['X']]['pic_url'];
                    $data[$xyKey]['good_x'][0] = $v['X'];
                    $data[$xyKey]['good_x'][1] = $ecgoodsInfoOfGoodsIds[$v['X']]['outer_id'];
                    $data[$xyKey]['good_x'][2] = mb_substr($ecgoodsInfoOfGoodsIds[$v['X']]['name'], 0, 25, "UTF-8");
                    $data[$xyKey]['good_y']['pic_url'] = $ecgoodsInfoOfGoodsIds[$v['Y']]['pic_url'];
                    $data[$xyKey]['good_y'][0] = $v['Y'];
                    $data[$xyKey]['good_y'][1] = $ecgoodsInfoOfGoodsIds[$v['Y']]['outer_id'];
                    $data[$xyKey]['good_y'][2] = mb_substr($ecgoodsInfoOfGoodsIds[$v['Y']]['name'], 0, 25, "UTF-8");
                    $data[$xyKey]['xy'] = array(max($v['X'], $v['Y']), min($v['X'], $v['Y']));
                    $i++;
                }
            }
            $newData = array();
            foreach ($data as $v) {
                $newData[] = $v;
            }
            $data = $newData;
        }
        return $data;
    }
    
    //AB商品关联度分析
    public function index_back(){

        $goods_id = $_POST['goods_id'];//A商品
        $shop_id = $this->shop_id;
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $rs = $shopObj->getList('shop_id,name,node_type');
        foreach($rs as $v){
            if($v['node_type'] != 'taobao')continue;
            
            if(!$shop_id) $shop_id = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $date_from = $this->date_from;
        $date_to = $this->date_to;
        $filter['date_from'] = strtotime($date_from);
        $filter['date_to'] = strtotime($date_to);
        $filter['shop_id'] = $shop_id;
        
        //检查时间间隔是否超过3个月
        $date_diff = $filter['date_to'] - $filter['date_from'];
        if($date_diff > 86400*31*3){
            $filter['date_from'] = $filter['date_to'] - 86400*31*3;
        }
        
        $total_orders = app::get('ecorder')->model('shop')->get_shop_orders($shop_id);
        if(1==1 or ($filter['date_to'] - $filter['date_from']) <= 86400*31 or $total_orders<10000){
            $analysis_data = kernel::single('market_backstage_report')->get_basket($filter);
        }else{//进入后台队列运算
            unset($filter['count_by']);
            $func = 'get_basket';
            $oCacheReport = kernel::single('taocrm_cache_report');
            $cache_id = $oCacheReport->getCacheId($func,$filter);
            $cache_status = $oCacheReport->get($cache_id);
            if($cache_status['status'] == 'REQ_CACHE'){
                die(kernel::single('taocrm_analysis_cache')->loading_tips(2));
            }elseif($cache_status['status'] == 'PRE_CACHE'){
                die(kernel::single('taocrm_analysis_cache')->loading_tips(3));
            }elseif($cache_status['status'] == 'SUCC'){
                $analysis_data = $cache_status['data'];//报表数据
            }else{
                $oCacheReport->fetch($func,$filter);
                die(kernel::single('taocrm_analysis_cache')->loading_tips(1));
            }
        }        
        //echo('<pre>');var_dump($analysis_data);die();
        
        $this->pagedata['form_action'] = '?app=ecgoods&ctl=admin_shop_basket&act=index';
        $this->pagedata['hot_products'] = $hot_products;
        $this->pagedata['goods_id'] = $goods_id;
        $this->pagedata['analysis_data'] = $analysis_data;
        $this->pagedata['shops'] = $shops;
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['date_from'] = $date_from;
        $this->pagedata['date_to'] = $date_to;
        $this->page('goods/basket.html');        
    }
    
}
