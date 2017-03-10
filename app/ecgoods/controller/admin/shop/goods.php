<?php 

class ecgoods_ctl_admin_shop_goods extends desktop_controller{

    var $workground = 'ecgoods.goods';
    var $unuse_words = array('补运费','补差价','不拍不送','邮费专拍','拍下无效');
    private static $taocrm_middleware_connect = null;
    
    public function __construct($app)
    {
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

    function index()
    {
        $actions[] = array(
            'label'=>'筛选客户',
            'submit'=>'index.php?app=taocrm&ctl=admin_member_report&act=filter_by_goods',
            'target'=>'dialog::{width:650,height:300,title:\'筛选客户\'}'
        );
        
        $actions[] = array(
            'label'=>'设置品牌',
            'submit'=>'index.php?app=ecgoods&ctl=admin_brand&act=sel_brand',
            'target'=>'dialog::{width:400,height:200,title:\'选择品牌\'}'
        );
        
        $actions[] = array(
            'label'=>'加入过滤',
            'submit'=>'index.php?app=ecgoods&ctl=admin_shop_goods&act=join_filter',
            'confirm'=>'被过滤的商品只显示在过滤商品列表中，不参与数据统计。确定要加入过滤吗？',
        );
        
        $actions[] = array(
            'label'=>'已过滤商品',
            'href'=>'index.php?app=ecgoods&ctl=admin_shop_goods&act=index_filter',
        );

        $base_filter['no_use'] = 0;
    
        $this->finder('ecgoods_mdl_shop_goods',array(
            'title'=>'销售商品统计',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'orderBy'=>'goods_id DESC',
        ));
    }
    
    function index_all() {
    
        $actions = array();
        $base_filter = array('no_use'=>'0');
        
        if($_GET['is_filter'] == 'yes'){
            $oGroup = $this->app->model('group');
            $rs = $oGroup->dump(intval($_GET['group_id']));
            $goods_ids = json_decode($rs['goods_ids'], true);
            $base_filter['goods_id'] = $goods_ids;
        }
    
        $this->finder('ecgoods_mdl_shop_goods',array(
            'title'=>'商品列表',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
        ));
    }
    
    function index_filter()
    {  
        $actions[] = array(
            'label'=>'商品列表',
            'href'=>'index.php?app=ecgoods&ctl=admin_shop_goods&act=index',
        );
    
        $actions[] = array(
            'label'=>'过滤关键词',
            'href'=>'index.php?app=ecgoods&ctl=admin_shop_goods&act=set_filter',
            'target'=>'dialog::{width:500,height:240,title:\'商品过滤关键词\'}'
        );
        
        $actions[] = array(
            'label'=>'取消过滤',
            'submit'=>'index.php?app=ecgoods&ctl=admin_shop_goods&act=remove_filter',
        );
        
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach($shopList as $shop){
            $shop_id_arr[] = $shop['shop_id'];
        }
        $base_filter['shop_id'] = $shop_id_arr;
        $base_filter['no_use'] = '1';
    
        $this->finder('ecgoods_mdl_shop_goods',array(
            'title'=>'商品过滤',
            'actions'=>$actions,
            'base_filter'=>$base_filter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>false,
        ));
    }
    
    public function join_filter(){
        $this->begin('index.php?app=ecgoods&ctl=admin_shop_goods&act=index_filter');
        $goods_id = $_POST['goods_id'];
        if($goods_id){
            $sql = "update sdb_ecgoods_shop_goods set no_use=1 where goods_id in (".implode(',', $goods_id).") ";
            $this->app->model('shop_goods')->db->exec($sql);
        }
        $this->end(true,'设置成功');
    }
    
    public function remove_filter(){
        $this->begin('index.php?app=ecgoods&ctl=admin_shop_goods&act=index_filter');
        $goods_id = $_POST['goods_id'];
        if($goods_id){
            $sql = "update sdb_ecgoods_shop_goods set no_use=0 where goods_id in (".implode(',', $goods_id).") ";
            $this->app->model('shop_goods')->db->exec($sql);
        }
        $this->end(true,'设置成功');
    }
    
    public function set_filter(){
        if(isset($_POST['filter_words'])){
            $this->begin('index.php?app=ecgoods&ctl=admin_shop_goods&act=index_filter');
            
            $sql = "update sdb_ecgoods_shop_goods set no_use=0 ";
            $this->app->model('shop_goods')->db->exec($sql);
            
            $filter_words = trim($_POST['filter_words']);
            if($filter_words){
                $filter_words = str_replace('，',',',$filter_words);
                base_kvstore::instance('ecgoods')->store('filter_words',$filter_words);
                
                $filter_words_arr = explode(',',$filter_words);
                foreach($filter_words_arr as $v){
                    $sql = "update sdb_ecgoods_shop_goods set no_use=1 where name like '%$v%' ";
                    $this->app->model('shop_goods')->db->exec($sql);
                }
            }
            
            $this->end(true,'保存成功');
        }
    
        base_kvstore::instance('ecgoods')->fetch('filter_words',$filter_words);
        $this->pagedata['filter_words'] = $filter_words;
        $this->display('goods/set_filter.html');
    }
    
    public function count_filter(){
        $counter = 0;
        $filter_words = trim($_POST['filter_words']);
        if($filter_words){
            $filter_words = str_replace('，',',',$filter_words);
            $filter_words_arr = explode(',',$filter_words);
            foreach($filter_words_arr as $v){
                $sql = "select count(a.goods_id) as total from sdb_ecgoods_shop_goods as a left join sdb_ecorder_shop as b on a.shop_id=b.shop_id
                where a.name like '%$v%' and b.shop_id<>'' ";
                $rs = $this->app->model('shop_goods')->db->selectRow($sql);
                $counter += $rs['total'];
            }
        }
        echo($counter);
    }
     
    function _views()
    {
        $oGoods = $this->app->model('shop_goods');
        $base_filter = array();
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false
        );

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id'],'no_use'=>'0'),
                'optional'=>false
            );
            $shop_id_arr[] = $shop['shop_id'];
        }
        
        $sub_menu[0]['filter'] = array('shop_id'=>$shop_id_arr,'no_use'=>'0');

        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }else{
                $v['filter'] = array('shop_id'=>$shop_id_arr); 
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $oGoods->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=ecgoods&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }
    
    protected function getTaocrmMiddlewareConnect()
    {
        if (self::$taocrm_middleware_connect == null) {
            self::$taocrm_middleware_connect = kernel::single('taocrm_middleware_connect');
        }
        return self::$taocrm_middleware_connect;
    }
    //AB商品关联度分析
    function relation(){
        
        $goods_id = $_POST['goods_id'];//A商品
        $shop_id = $this->shop_id;
        $max_num = 15;//最多返回前15个分析结果
        $db = kernel::database();
        
        $date_from = $this->date_from;
        $date_to = $this->date_to;
        $filter['date_from'] = strtotime($date_from);
        $filter['date_to'] = strtotime($date_to);
        $filter['shop_id'] = $shop_id;
        
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $rs = $shopObj->getList('shop_id,name,node_type');
        foreach($rs as $v){
            if($v['node_type'] != 'taobao')continue;
            
            $shops[$v['shop_id']] = $v['name'];
        }
        // 销量Top20
        $rs = $this->getTopSale($filter);
//        $where = '';
//        if ($filter['shop_id']) $where .= " and shop_id='$shop_id' ";
//        else $where .= " and 1=0  ";
//        $where .= " and (create_time between ".$filter['date_from']." and ".$filter['date_to'].") ";
//        $sql = "
//            select goods_id,name,sum(nums) as num from sdb_ecorder_order_items
//            where 1=1 $where AND goods_id>0
//            group by goods_id
//            order by num desc
//            LIMIT 40
//        ";
        $i = 0;
        foreach($rs as $v){
            if($i>=$max_num) break;
            $unuse = 0;
            foreach($this->unuse_words as $vv){
                if(strstr($v['name'],$vv)){$unuse = 1;break;}
            }
            if($unuse == 1) continue;
        
            $hot_products[$v['goods_id']] = $v['name'];
            $i++;
        }
        
        if($goods_id) {
            $filter['goods_id'] = $goods_id;
            $analysis_data = $this->getTopOrderItemXY($filter);
//            $total_orders = app::get('ecorder')->model('shop')->get_shop_orders($shop_id);
//            if(($filter['date_to'] - $filter['date_from']) <= 86400*31 || $total_orders<10000){
//                $analysis_data = kernel::single('market_backstage_report')->get_goods_relation($filter);
//            }else{//进入后台队列运算
//                unset($filter['count_by']);
//                $func = 'get_goods_relation';
//                $oCacheReport = kernel::single('taocrm_cache_report');
//                $cache_id = $oCacheReport->getCacheId($func,$filter);
//                $cache_status = $oCacheReport->get($cache_id);
//                if($cache_status['status'] == 'REQ_CACHE'){
//                    die($this->loading_tips(2));
//                }elseif($cache_status['status'] == 'PRE_CACHE'){
//                    die($this->loading_tips(3));
//                }elseif($cache_status['status'] == 'SUCC'){
//                    $analysis_data = $cache_status['data'];//报表数据
//                }else{
//                    $oCacheReport->fetch($func,$filter);
//                    die($this->loading_tips(1));
//                }
//            }
        }
        
        $this->pagedata['form_action'] = '?app=ecgoods&ctl=admin_shop_goods&act=relation';
        $this->pagedata['hot_products'] = $hot_products;
        $this->pagedata['goods_a'] = $hot_products[$goods_id];
        $this->pagedata['goods_id'] = $goods_id;
        $this->pagedata['analysis_data'] = $analysis_data;
        $this->pagedata['shops'] = $shops;
        $this->pagedata['path'] = '热销商品关联';
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['date_from'] = $date_from;
        $this->pagedata['date_to'] = $date_to;
        $this->page('goods/relation.html');        
    }
    
    protected function getTopOrderItemXY($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = $params['date_from'];
        $filter['endTime'] = $params['date_to'];
        $filter['top'] = 100;
        $filter['goodsId'] = $params['goods_id'];
        $filter['ctl'] = $_GET['ctl'];
        $connect = $this->getTaocrmMiddlewareConnect();
        //$result = json_decode($connect->TopOrderItemXY($filter), true);
        $result = $connect->TopOrderItemXY($filter);
        $data = array();
        if ($result) {
            $goodsIds = array();
            foreach ($result as $k => $v) {
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
            foreach ($result as $k => $v) {
                if ($v['X'] == $v['Y']) {
                    continue;
                }
                $data[$i]['goods_a'] = $v['X'];
                $data[$i]['goods_b'] = $v['Y'];
                $data[$i]['name'] = $ecgoodsInfoOfGoodsIds[$v['Y']]['name'];
                $data[$i]['times'] = $v['xyOrderCount'];
                $data[$i]['ab_members'] = $v['xyMemberCount'];
                $data[$i]['order_a'] = $v['xOrderCount'];
                $data[$i]['order_b'] = $v['yOrderCount'];
                $data[$i]['ab_ratio'] = $v['xyOrderCount'] / ($v['xOrderCount'] + $v['yOrderCount'] - $v['xyOrderCount'] );
                $data[$i]['b_ratio'] = $v['xyOrderCount'] / $v['xOrderCount'];
                $data[$i]['a_ratio'] = $v['xyOrderCount'] / $v['yOrderCount'];
                $data[$i]['a_members'] = $v['xMemberCount'] - $v['xyMemberCount'];
                $i++;
            }
        }
        return $data;
    }
    
    protected function getTopSale($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = $params['date_from'];
        $filter['endTime'] = $params['date_to'];
        $filter['top'] = 20;
        $filter['ctl'] = $_GET['ctl'];
        $connect = $this->getTaocrmMiddlewareConnect();
        //$result = json_decode($connect->TopSale($filter), true);
        $result = $connect->TopSale($filter);
       
        $data = array();
        if ($result) {
            $goods = array_keys($result);
            $shopGoodsModel = app::get('ecgoods')->model('shop_goods');
            $shopGoodsInfo = $shopGoodsModel->getList('*', array('goods_id' => $goods));
            $shopGoodsInfoOfGoods = array();
            foreach ($shopGoodsInfo as $v) {
                $shopGoodsInfoOfGoods[$v['goods_id']] = $v;
            }
            $i = 0;
            foreach ($result as $k => $v) {
                $data[$i]['name'] = $shopGoodsInfoOfGoods[$k]['name'];
                $data[$i]['goods_id'] = $k;
                $data[$i]['num'] = $v['num'];
                $i++;
            }
        }
        return $data;
    }
    
    //获取相关商品的订单数和用户数
    public function get_goods_relate($goods_id,$filter){
    
        $db = kernel::database();
        $where = '';
        if ($filter['shop_id']) $where .= " and shop_id='".$filter['shop_id']."' ";
        $where .= " and (create_time between ".$filter['date_from']." and ".$filter['date_to'].") ";
        
        //购买B商品的订单编号
        $sql = "select order_id from sdb_ecorder_order_items where goods_id=$goods_id $where ";
        $rs = $db->select($sql);
        foreach($rs as $v){
           $orders[] = $v['order_id']; 
        }
        unset($rs);
        
        //购买B商品的用户
        $sql = "select member_id from sdb_ecorder_orders where order_id in (".implode(',',$orders).") ";
        $rs = $db->select($sql);
        foreach($rs as $v){
            $members[$v['member_id']] = $v['member_id']; 
        }
        unset($rs);
        
        $res = array('orders'=>$orders,'members'=>$members);
        return $res;
    }
    
    private function loading_tips($msg){
        return kernel::single('taocrm_analysis_cache')->loading_tips($msg);
    }
    
    public function ajaxGetGoodsList()
    {
        $res = "<li class='goods_header'><span class='bn'>商家编码</span>商品名称</li>";
        $name = trim($_POST['name']);
        $sel_goods = trim($_POST['sel_goods']);
        $group_id = intval($_POST['group_id']);
        
        if($group_id>0){
            $rs_group = $this->app->model('group')->dump($group_id, 'goods_id');
        }
        
        $sql = "select goods_id,bn,name from sdb_ecgoods_shop_goods where 1=1 ";
        if($rs_group['goods_id']){
            $sql .= " and goods_id in (".$rs_group['goods_id'].",".$sel_goods.") ";
        }else{
        if($name != ''){
            $sql .= " and (name like '%{$name}%' or bn like '%{$name}%') ";
        }
        if($sel_goods != '0'){
            $sql .= " and goods_id not in ({$sel_goods}) ";
        }
        }
        $sql .= 'limit 200';
        //var_dump($sql);
        
        $rs = $this->app->model('shop_goods')->db->select($sql);
        foreach($rs as $v){
            $res .= "<li><input type='hidden' name='goods_id[]' value='".$v['goods_id']."' /><span class='bn'>".$v['bn']."&nbsp;</span>".mb_substr($v['name'],0,18,'utf-8')."</li>";
        }
        echo($res);
    }
    
    /*
    function cal_relate_goods(){
        
        $sql = "select order_id,createtime from sdb_ecorder_orders";
        $rs = $this->app->model('shop_goods')->db->select($sql);
        foreach($rs as $v) {
            $order_id = $v['order_id'];
            $createtime = date('Y-m-d',$v['createtime']);
            $createtime = strtotime($createtime);
            $sql = "select name from sdb_ecorder_order_items where order_id=$order_id";
            $rs_order = $this->app->model('shop_goods')->db->select($sql);
            if(count($rs_order)>1) {
                foreach($rs_order as $kk=>$vv) {
                    for($i=$kk+1;$i<count($rs_order);$i++){
                        $this->save_relate_goods($vv['name'],$rs_order[$i]['name'],$createtime);
                    }
                }
            }
            unset($rs_order);
        }
    }
    */
    
    /*
    function save_relate_goods($goods_a,$goods_b,$create_time){
        
        if($goods_a == $goods_b) return false;
        
        $oRelateProducts = &app::get('ecorder')->model('relate_products');
        $where = " where create_time=$create_time and goods_a='$goods_a' and goods_b='$goods_b'";
        $sql = "update sdb_ecorder_relate_products set times=times+1 $where limit 1";
        $q = $oRelateProducts->db->exec($sql);
        if(mysql_affected_rows()) return true;
        
        $where = " where create_time=$create_time and goods_b='$goods_a' and goods_a='$goods_b'";
        $sql = "update sdb_ecorder_relate_products set times=times+1 $where limit 1";
        $q = $oRelateProducts->db->exec($sql);
        if(mysql_affected_rows()) return true;
        
        $arr = array(
            'goods_a'=>$goods_a,'goods_b'=>$goods_b,
            'create_time'=>$create_time,'update_time'=>$create_time,
            'times'=>1
        );
        $oRelateProducts->save($arr);
        return true;
    }
    */
	
}

