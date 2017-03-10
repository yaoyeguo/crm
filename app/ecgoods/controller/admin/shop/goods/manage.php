<?php

class ecgoods_ctl_admin_shop_goods_manage extends desktop_controller{

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
            'label'=>'设置品牌',
            'submit'=>'index.php?app=ecgoods&ctl=admin_brand&act=sel_brand',
            'target'=>'dialog::{width:400,height:200,title:\'选择品牌\'}'
        );

        $actions[] = array(
            'label'=>'添加商品',
            'href'=>'index.php?app=ecgoods&ctl=admin_shop_goods_manage&act=goods_add',
            'target'=>'dialog::{width:650,height:500,title:\'添加商品\'}'
        );

        $base_filter = array();

        //商家编码,商品名称,品牌,销售量,销售金额,订单数,客户数,更新时间
        $this->finder('ecgoods_mdl_shop_goods',array(
            'title'=>'商品管理',
            'actions'=>$actions,
            //'base_filter'=>$base_filter,
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
            'orderBy'=>'goods_id desc',
            'finder_cols'=>'bn,name,brand_id,total_num,sale_money,buyperson,last_modify',
        ));
    }

    /**
     * goods_add 
     * 
     * @access public
     * @author liuqi 
     * @return void
     */
    function goods_add()
    {
        $mod = $this->app->model('brand');
        $brand = $mod->getList('brand_id,brand_name');
        $render = app::get('ecgoods')->render();
        $brand_list = array();
        foreach($brand as $k => $v)
        {
            $brand_list[$v['brand_id']] = $v['brand_name'];
        }
        $render->pagedata['brand_list'] = $brand_list;
        $render->display('shop/goods/edit.html');
    }

    function edit_post()
    {
        $url = "index.php?app=ecgoods&ctl=admin_shop_goods&act=index";
        $this->begin($url);

        $info = array(
                'goods_id' => !empty($_POST['info']['goods_id'] ) ? trim($_POST['info']['goods_id'] ) : 0,
                'name'     => !empty($_POST['info']['name']     ) ? trim($_POST['info']['name']     ) : false,
                'bn' => !empty($_POST['info']['bn']) ? trim($_POST['info']['bn']) : false,
                'brand_id' => !empty($_POST['info']['brand_id'] ) ? trim($_POST['info']['brand_id'] ) : false,
                'pic_url'  => !empty($_POST['info']['pic_url']  ) ? trim($_POST['info']['pic_url']  ) : false,
                'info_url' => !empty($_POST['info']['info_url'] ) ? trim($_POST['info']['info_url'] ) : false,
                'price'    => !empty($_POST['info']['price']) ? floatval($_POST['info']['price']  ) : 0,
                'code'     => !empty($_POST['info']['code']     ) ? trim($_POST['info']['code']     ) : false,
                'spec'     => !empty($_POST['info']['spec']     ) ? trim($_POST['info']['spec']     ) : false,
                'desc'     => !empty($_POST['info']['desc']     ) ? trim($_POST['info']['desc']     ) : false,
        );
        if(empty($info['goods_id']))
        {
            unset($info['goods_id']);
        } 

        $mod_obj = app::get('ecgoods')->model('shop_goods');
        $rt = $mod_obj->save($info);
        $rt = $rt ? true : false;

        $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));
    }
    function edit()
    {

        $id = !empty($_GET['id']) ? intval($_GET['id']) : false;
        $render = app::get('ecgoods')->render();

        if(!$id){
            $render->pagedata['info'] = false;
        }else{
            $mod_obj = &app::get('ecgoods')->model('shop_goods');
            $info = $mod_obj->dump($id);
            $render->pagedata['info'] = $info;
        }
        $mod = $this->app->model('brand');
        $brand = $mod->getList('brand_id,brand_name');
        $render = app::get('ecgoods')->render();
        $brand_list = array();
        foreach($brand as $k => $v)
        {
            $brand_list[$v['brand_id']] = $v['brand_name'];
        }
        $render->pagedata['brand_list'] = $brand_list;

        $render->display('shop/goods/edit.html');
    }

    function _views()
    {
        $oGoods = $this->app->model('shop_goods');
        $base_filter = array('no_use'=>'0');

        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>$base_filter,
            'optional'=>false
        );

        $sub_menu[] = array(
            'label'=>'可识别编码商品',
            'filter'=>array('bn|noequal'=>''),
            'optional'=>false
        );

        $sub_menu[] = array(
            'label'=>'无编码商品',
            'filter'=>array('bn|nequal'=>''),
            'optional'=>false
        );

        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $oGoods->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=ecgoods&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        //echo('<pre>');var_dump($sub_menu);
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
    function relation()
    {
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
    public function get_goods_relate($goods_id,$filter)
    {
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

    private function loading_tips($msg)
    {
        return kernel::single('taocrm_analysis_cache')->loading_tips($msg);
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
    function set_goods()
    {

        $id = !empty($_GET['id']) ? intval($_GET['id']) : false;
        $render = app::get('ecgoods')->render();

        if(!$id)
        {
            $render->pagedata['info'] = false;
        }else{
            $mod_obj = &app::get('ecgoods')->model('shop_goods');
            $info = $mod_obj->dump($id);
            if($info['fixed_point_num'] == 0){
                $info['fixed_point_num'] = '';
            }
            $render->pagedata['info'] = $info;
            if(!empty($info['point_rule'])){
                $point_rule = explode(',',$info['point_rule']);
                $point_rule_bool['point_rule_1'] = in_array("1", $point_rule) ? true : false;
                $point_rule_bool['point_rule_2'] = in_array("2", $point_rule) ? true : false;
                $point_rule_bool['point_rule_3'] = in_array("3", $point_rule) ? true : false;
                $render->pagedata['point_rule_bool'] = $point_rule_bool;
                //print_r($point_rule_bool);
            }
        }
       // print_r($info);
        $render->display('shop/goods/set_goods.html');
    }
    function set_goods_post()
    {
        $url = "index.php?app=ecgoods&ctl=admin_shop_goods_manage&act=index";
        $this->begin($url);
        $info = array(
            'is_full_price' => $_POST['info']['is_full_price'],
            'fixed_point_num' => !empty($_POST['info']['fixed_point_num']) ? trim($_POST['info']['fixed_point_num']) : false,
            'goods_id' => !empty($_POST['info']['goods_id']) ? trim($_POST['info']['goods_id']) : false,
        );
        if(!empty($_POST['info']['point_rule'])){
            $info['point_rule'] = implode(',',$_POST['info']['point_rule']);
        }
        $mod_obj = &app::get('ecgoods')->model('shop_goods');
        $rt = $mod_obj->save($info);
        $rt = $rt ? true : false;

        $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));
    }
}
