<?php

class taocrm_finder_middleware_member_report
{
    protected static $memberAnalysisObj = '';
    protected static $membersObj = null;
    protected static $hardeWareConnect = null;
    protected $shop_id = '';
    function __construct(){
        $this->oMembers = &app::get('taocrm')->model('members');
    }

    private function getMemberInfo($id){
        $rs = app::get('taocrm')->model('member_analysis')->getUniqueKey($id);
        $this->member_id = $rs['member_id'];
        $this->shop_id = $rs['shop_id'];
    }

    var $column_tag = '标签';
    var $column_tag_order = 2;
    function column_tag($row)
    {
        $tagInfo = $row['tagInfo'];
        if($tagInfo){
            $tagInfo = '<img border=0 title="'.$tagInfo.'" align="absmiddle" src="'.app::get('taocrm')->res_url.'/teg_ico.png" >';
        }
        return $tagInfo;
    }
    var $column_uname = '客户名';
    var $column_uname_width = 110;
    var $column_uname_order = 20;
    function column_uname($row){
        $member_id = $row['member_id'];
        if(!$member_id)return '';
        $rs = $this->oMembers->dump($member_id,'uname');
        return $rs['account']['uname'];
    }

    public $column_area = '地区';
    public $column_area_width = 110;
    public $column_area_order = 30;
    public $column_area_order_field = 'district';
    public function column_area($row)
    {
        $member_id = $row['member_id'];
        if (self::$membersObj == null) {
            $app = app::get('taocrm');
            self::$membersObj = $app->model('members');
        }
        return self::$membersObj->getAreasInfo($member_id);
    }

	public $column_addr = '地址';
    public $column_addr_width = 110;
    public $column_addr_order = 30;

    public function column_addr($row)
    {
        $member_id = $row['member_id'];
        if (self::$membersObj == null) {
            $app = app::get('taocrm');
            self::$membersObj = $app->model('members');
        }
        return self::$membersObj->getAddrInfo($member_id);
    }

    public $column_realName = '真实姓名';
    public $column_realName_width = 110;
    public $column_realName_order = 35;
    //public $column_realName_order_field = 'district';
    public function column_realName($row)
    {
        $member_id = $row['member_id'];
        if (self::$membersObj == null) {
            $app = app::get('taocrm');
            self::$membersObj = $app->model('members');
        }
        $filter = array('member_id' => $member_id);
        $result = self::$membersObj->dump($filter, 'name');
        return $result['contact']['name'];
    }

    protected function getConnect()
    {
        if (self::$hardeWareConnect == null) {
            self::$hardeWareConnect = new taocrm_middleware_connect;
        }
        return self::$hardeWareConnect;
    }

    /**
     * 获得店铺ID
     */
    protected function getShopId()
    {
        if ($this->shop_id == '') {
            $this->shop_id = $_GET['shop_id'];
        }
        return $this->shop_id;
    }

    /**
     * 获得接口客户数据
     */
    protected function getMemberData($memberId)
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
            $data['first_buy_time'] = $result['MinCreateTime'] ? date("Y-m-d H:i:s", $result['MinCreateTime']) : '-';
            //最后下单时间
            $data['last_buy_time'] = $result['MaxCreateTime'] ? date("Y-m-d H:i:s", $result['MaxCreateTime']) : '-';
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

//        $result = $connect->AnalysisByMemberId();
    }

    /**
     * 打印测试数据
     */
    protected function printTestData($data, $isExit = true)
    {
        echo "<pre>";
        print_r($data);
        if ($isExit) {
            die();
        }
    }

    /**
     * 获得客户等
     */
    protected function getMemberLvInfo($lv_id)
    {
        $data = array();
        if (!$lv_id == '') {
            $model = $this->getMemberAnalysisObj();
            $shopId = $this->getShopId();
            $sql = "SELECT `lv_id`, `name` FROM `sdb_ecorder_shop_lv` WHERE lv_id = {$lv_id}";
            $result = $model->db->select($sql);
            if ($result) {
                $data = $result[0];
            }
        }
        return $data;
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

    /**
     * 获得店铺等级名称
     */
    protected function getShopEvaluation($key)
    {
        $type = $this->getDbShemaColumns('shop_evaluation');
        return (isset($type[$key]) ? $type[$key] : '');
    }

    var $detail_basic = '统计信息';
    public function detail_basic($id)
    {
        $shop_id = $this->getShopId();
        $app = app::get('taocrm');
        $analysis = $app->model('members')->get_analysis($id, $shop_id);

        $render = $app->render();
        $render->pagedata['analysis'] = $analysis;
        return $render->fetch('admin/member/analysis.html');
    }

    var $detail_edit = '客户信息';
    function detail_edit($id){
//        $id = $this->getMemberId($id);
        $this->member_id = $id;
        $app = app::get('taocrm');
        $render = $app->render();
//        $this->getMemberInfo($id);
        $memberObj = $app->model('members');
        $member_analysisObj = $app->model('member_analysis');
        $taocrm_service_member = kernel::single('taocrm_service_member');
        if($_POST){
            $data=array();
            $data['email']=$_POST['email'];
            $data['name']=$_POST['name'];
            $data['sex']=$_POST['gender'];
            $data['birthday']=strtotime($_POST['birthday']);
            $data['area']=$_POST['area'];
            $data['addr']=$_POST['addr'];
            $data['zip']=$_POST['zipcode'];
            $data['mobile']=$_POST['mobile'];
            $data['tel'] = $data['telephone']=$_POST['telephone'];
            $data['alipay_no']=$_POST['alipay_no'];
            $data['is_vip']=$_POST['is_vip'];
            $data['sms_blacklist']=$_POST['sms_blacklist'];
            $data['edm_blacklist']=$_POST['edm_blacklist'];
            $data['remark']=$_POST['remark'];
            $member_analysisObj->update(array('is_vip'=>$_POST['is_vip']),array('member_id'=>$_POST['member_id']));
            $rs =  $memberObj->update($data,array('member_id'=>$_POST['member_id']));
            $data['member_id'] = $_POST['member_id'];

            $taocrm_service_member->saveOverallMember($data,$_POST['prop_name']);
            $memberObj->chkMemberArea($_POST['member_id']);
            
            //保存ext扩展属性
            if($_POST['birthday']){
                $ext_info = array();
                $ext_info['member_id'] = $_POST['member_id'];
                list($ext_info['b_year'],$ext_info['b_month'],$ext_info['b_day']) = 
                    explode('-', $_POST['birthday']);
                $taocrm_service_member->save_member_ext($ext_info);
            }
            /*
            //同步更新到内存
            $connect = $this->getConnect();
            $memoryData = array(
                'shopId' => $this->getShopId(),
                'memberId' => $_POST['member_id'],
                'mobile' => $data['mobile'],
                'mail' => $data['email']
            );
            $connect->updateMember($memoryData);
            */
        }
	  $mem = $memberObj->dump(array('member_id'=>$this->member_id),'*');
        
        //处理扩展属性的生日
        $ext_info = $taocrm_service_member->get_member_ext($this->member_id);
        if($ext_info){
            if($ext_info['b_year']){
                $mem['profile']['birthday'] = 
                    $ext_info['b_year'].'-'.$ext_info['b_month'].'-'.$ext_info['b_day'];
            }
        }
        if(!$mem['profile']['birthday']) $mem['profile']['birthday'] = '';
        
	  $render->pagedata['mem'] = $mem;
//	  $render->pagedata['shops'] = $shops;
        base_kvstore::instance('ecorder')->fetch('overall_member_props',$overall_member_props);
        $overall_member_props = json_decode($overall_member_props,true);
        if($overall_member_props){
            /*
            $oMemberProp = app::get('taocrm')->model('member_overall_property');
            $rs = $oMemberProp->getList('*',array('member_id'=>$member_id));
            if($rs){
                foreach($rs as $v){
                    $prop_val[$v['property']] = $v['value'];
                }
            }
             */
            $oMemberProp = app::get('taocrm')->model('member_attr');
            $filter = array(
                'member_id'=>$this->member_id,
                'shop_id' => 'all',
            );
            $rs_prop = $oMemberProp->dump($filter);
            if($rs_prop){
                $prop_val = array(
                    $rs_prop['attr1'],
                    $rs_prop['attr2'],
                    $rs_prop['attr3'],
                    $rs_prop['attr4'],
                    $rs_prop['attr5'],
                    $rs_prop['attr6'],
                    $rs_prop['attr7'],
                    $rs_prop['attr8'],
                    $rs_prop['attr9'],
                    $rs_prop['attr10'],
                );
            }
        }

        //$overall_member_props = array_unique($overall_member_props);
        $render->pagedata['prop_val'] = $prop_val;
        $render->pagedata['prop_name'] = $overall_member_props['prop_name'];
        $render->pagedata['prop_type'] = $overall_member_props['prop_type'];

        $redirect_uri = 'index.php?act=index&app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&id='.$id.'&view='.$_GET['view'];
        $render->pagedata['redirect_uri'] = base64_encode($redirect_uri);
   	  return $render->fetch('admin/member/edit.html');
    }

    var $detail_goods = '买过的商品';
    function detail_goods($id)
    {
        if(!$id) return null;
        $app = app::get('taocrm');
        $render = $app->render();
        $goods = array();
        $orders = array();

        $this->member_id = $id;
        $sql = "select order_id from sdb_ecorder_orders where member_id=".$this->member_id." and pay_status='1' ";
        $rs = kernel::database()->select($sql);
        if($rs){
            foreach($rs as $v){
               $orders[] = $v['order_id'];
            }
        }

        if($orders) {
            $sql = "select bn,name,nums,amount from sdb_ecorder_order_items where order_id in (".implode(',',$orders).") ";
            $rs = kernel::database()->select($sql);
            foreach($rs as $v){
                $k = $v['bn'] ? $v['bn'] : $v['name'];
                if(isset($goods[$k])){
                    $goods[$k]['nums'] += $v['nums'];
                    $goods[$k]['amount'] += $v['amount'];
                }else{
                    $goods[$k] = $v;
                }
            }
        }
        $render->pagedata['goods'] = $goods;
        return $render->fetch('admin/member/goods.html');
    }

    var $detail_order = '历史订单';
    function detail_order($id=null)
    {   
        $this->member_id = $id;
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }

        //订单报表
        $order_report = array();
        $order_max_amount = 0;

        $filter = array();
        $filter['member_id'] = $this->member_id;
        if (isset($this->shop_id) && $this->shop_id) {
            $filter['shop_id'] = $this->shop_id;
        }
        
        //$data = $this->getMemberData($id);
        //$OrderIdList = $data['OrderIdList'];
        //$filter = array('order_id|in' => $OrderIdList);
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orderObj = &app::get(ORDER_APP)->model('orders');
        $order_cols = 'order_id,order_bn,status,pay_status,ship_status,total_amount,createtime,shop_id,payed';
        $orders = $orderObj->getList($order_cols, $filter, 0, -1, 'createtime DESC');
        //$row = $orderObj->getList('order_id', $filter);
        //$count = count($row);
        foreach($orders as $key=>$order){
        
            $rk = date('Y-m', $order['createtime']);
            if($order['pay_status']=='1'){
                if(date('Y', $order['createtime']) != date('Y')){
                    $order_report[$rk]['bgcolor'] = '#F4F4F4';
                }
                $order_report[$rk]['amount'] = intval($order_report[$rk]['amount']) + $order['total_amount'];
                $order_report[$rk]['orders'] = intval($order_report[$rk]['orders']) + 1;
                $order_report[$rk]['avg_amount'] = round($order_report[$rk]['amount']/$order_report[$rk]['orders'], 2);
                $order_max_amount = max($order_max_amount, $order['total_amount']);
            }
        
            $orders[$key]['shop_name'] = $shops[$orders[$key]['shop_id']]['name'];
            $orders[$key]['status'] = $orderObj->trasform_status('status',$orders[$key]['status']);
            $orders[$key]['pay_status'] = $orderObj->trasform_status('pay_status',$orders[$key]['pay_status'] );
            $orders[$key]['ship_status'] = $orderObj->trasform_status('ship_status', $orders[$key]['ship_status']);
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['orders'] = $orders;
        $render->pagedata['order_max_amount'] = $order_max_amount;
        $render->pagedata['order_report'] = ($order_report);
        return $render->fetch('admin/member/order.html');
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

    var $detail_contact = '联 系 人';
    function detail_contact($id=null){
//        $id = $this->getMemberId($id);
//        if(!$id) return null;
//        $this->getMemberInfo($id);
        $this->member_id = $id;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_contacts');
        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));
        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;
        return $render->fetch('admin/member/contact.html');
    }

    var $detail_addr = '收货地址';
    function detail_addr($id=null){
//        $id = $this->getMemberId($id);
//        if(!$id) return null;
//        $this->getMemberInfo($id);
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_receivers');
        $addrs = $addrObj->getList('*',array('member_id' => $id));

        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;

        return $render->fetch('admin/member/addr.html');
    }

    var $detail_active = '营销活动';
    function detail_active($id){
        if (!$id) $id = $_GET['id'];
        $app = app::get('taocrm');
        $marketApp = app::get('market');
        $orderModel = app::get('ecorder')->model('orders');
        //分页
        $pagelimit = 8;
        $page = max(1, intval($_GET['page']));
        $offset = ($page - 1) * $pagelimit;
        $activeMemberModel = $marketApp->model('active_member');
        $shopId = $this->getShopId();
        $filter = array('issend' => 1, 'member_id' => $id, 'shop_id' => $shopId);
        $memberList = $activeMemberModel->getList('*', $filter, $offset, $pagelimit, 'active_id DESC');
        $count = $activeMemberModel->count($filter);
        $render = $app->render();

        $filter = array(
            'member_id' => $id,
            'shop_id' => $shopId
        );
        $rs_orders = $orderModel->getList('order_id,order_bn,member_id,pay_time', $filter);
        if(!$rs_orders) $rs_orders=array();

        if ($memberList) {
            $activeModel = $marketApp->model('active');
            $ecorderApp = app::get('ecorder');
            $shopModel = $ecorderApp->model('shop');
            $shopInfo = $shopModel->getList('shop_id,name');
            $shopList = array();
            foreach ($shopInfo as $v) {
                $shopList[$v['shop_id']] = $v['name'];
            }
            $activenamelist = array();
            $i = 0;
            foreach ($memberList as $v) {
                $activenamelist[$i]['shop_name'] = isset($shopList[$v['shop_id']]) ? $shopList[$v['shop_id']] : '未知店铺';
                $activeInfo = $activeModel->dump(array('active_id' => $v['active_id']));
                if ($activeInfo) {

                    foreach($rs_orders as $v_order){
                        if($v_order['pay_time']>=$activeInfo['create_time'] && $v_order['pay_time']<=$activeInfo['end_time']){
                            $activenamelist[$i]['orders'][] = $v_order;
                        }
                    }

                    $activenamelist[$i]['active_name'] = $activeInfo['active_name'];
                    $activenamelist[$i]['total_num'] = $activeInfo['total_num'];
                    $activenamelist[$i]['create_time'] = date("Y-m-d H:i:s", $activeInfo['create_time']);
                    $activenamelist[$i]['end_time'] = date("Y-m-d H:i:s", $activeInfo['end_time']);
                }
                else {
                    $activenamelist[$i]['active_name'] = '活动已经删除';
                    $activenamelist[$i]['total_num'] = '未知';
                    $activenamelist[$i]['create_time'] = '未知';
                    $activenamelist[$i]['end_time'] = '未知';
                }
                $i++;
            }
            $total_page = ceil($count / $pagelimit);
            $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id . '&view='.$_GET['view'].'&shop_id='.$shopId.'&finderview=detail_active&page=%d' ));
            $render->pagedata['pager'] = $pager;
            $render->pagedata['activenamelist'] = $activenamelist;
        }
        return $render->fetch('admin/member/active.html');
/**
        $this->member_id = $id;
        $app = app::get('taocrm');
        $render = $app->render();
        $member_analysisObj = $app->model('member_analysis');
        $sms_log_obj = app::get('market')->model('sms_log');
        $shop_obj = app::get('ecorder')->model('shop');
        $active_obj = app::get('market')->model('active');
        //分页
        $pagelimit =3;
        $page = intval($_GET['page']);
        $page = $page ? $page : 1;
//        $member_id = $member_analysisObj->dump(array('id'=>$id),"member_id");
//        $m_id=$member_id['member_id'];
        $m_id = $id;
        $activelist=$sms_log_obj->getList("*");
        $activeid=array();
        foreach ($activelist as $k=>$v){
        	$aclist=json_decode($v['member_id']);
        	if (in_array($m_id, $aclist)){
        		$activeid[]=$v['active_id'];
        	}
        }
        $filter=array('active_id|in'=>$activeid);
        $activenamelist=$active_obj->getList("*",$filter);
        $orderItems = $active_obj->getPager($filter,'*',$pagelimit * ($page - 1), $pagelimit);
        foreach ($orderItems['data'] as $k=>$v){
            $shop_name=$shop_obj->dump(array("shop_id"=>$v["shop_id"]),"name");
            $active_id=$v['active_id'];
            $count=$sms_log_obj->dump(array('active_id'=>$active_id));
            $cou=count(json_decode($count['member_id']));
            $orderItems['data'][$k]['total_num']=$cou;
            $orderItems['data'][$k]['shop_name']=$shop_name['name'];
            $orderItems['data'][$k]['create_time']=date('Y-m-d',$v['create_time']);//end_time
            $orderItems['data'][$k]['end_time']=date('Y-m-d',$v['end_time']);
        }
        $count = $orderItems ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $render->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&finderview=detail_active&id='.$id.'&page=%d' ));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['activenamelist'] = $orderItems['data'];
        return $render->fetch('admin/member/active.html');
        **/
    }

   var $detail_points = '积分日志';
    function detail_points($id)
    {
        $this->member_id = $id;
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }
        
        $mdl_points_log = app::get('taocrm')->model('all_points_log');
        $schema = $mdl_points_log->get_schema();
       // $points_type_conf = $schema['columns']['points_type']['type'];
        
        $mdl_member_points = app::get('taocrm')->model('member_points');
        $points = $mdl_member_points->getList('*', array('member_id'=>$this->member_id));
        foreach($points as $k=>$v){
            $points[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            //$points[$k]['points_type'] = $points_type_conf[$v['points_type']];
        }
        
        $logs = $mdl_points_log->db->select('select * from sdb_taocrm_all_points_log where member_id='.$this->member_id.' order by log_id desc limit 10');
        foreach($logs as $k=>$v){
            $logs[$k]['user_name'] = app::get('taocrm')->model('members')->dump(array('member_id'=>$v['member_id']),'uname');
            $logs[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            //$logs[$k]['point_type'] = $point_type[$v['point_type']];
            //$logs[$k]['points_type'] = $points_type_conf[$v['points_type']];
            $logs[$k]['order_bn'] = app::get('ecorder')->model('orders')->dump($v['order_id'],'order_bn');
            $logs[$k]['refund_bn'] = app::get('ecorder')->model('refunds')->dump($v['refund_id'],'refund_bn');
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['logs'] = $logs;
        $render->pagedata['points'] = $points;
        $render->pagedata['member_id'] = $id;
        return $render->fetch('admin/member/points_log.html');
    }

    //var $detail_service = '接待日志';
    //function detail_service($id){
    //     if (!$id) $id = $_GET['id'];
    //    //        echo $id;
    //    $app = app::get('taocrm');
    //    $memObj = $app->model('members');
    //    $memInfo = $memObj->dump(array('member_id'=>$id),'uname');
    //    $uname = trim($memInfo['account']['uname']);
    //    $chatObj = $app->model('wangwang_shop_chat_log');
    //    //分页
    //    $pagelimit = 3;
    //    $page = max(1, intval($_GET['page']));
    //    $offset = ($page - 1) * $pagelimit;
    //    $shopId = $this->getShopId();

    //    $filter = array('uname' => $uname, 'shop_id' => $shopId);
    //    $memberList = $chatObj->getList('*', $filter, $offset, $pagelimit,'chat_date desc');
    //    $count = $chatObj->count($filter);
    //    $render = $app->render();
    //    //        echo "<pre>";
    //    //        print_r($_GET);
    //    if ($memberList) {

    //        $i = 0;
    //        $servicenamelist = array();
    //        foreach ($memberList as $v) {
	//			$servicenamelist[$i]['mark'] = '';
    //            $servicenamelist[$i]['nick'] = $v['seller_nick'];
    //            $servicenamelist[$i]['date'] = date('Y-m-d',$v['chat_date']);
    //           	$servicenamelist[$i]['type'] = '旺旺接待';
    //            $i++;
    //        }

    //        $view = $_GET['view'];
    //        $total_page = ceil($count / $pagelimit);
    //        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_service&page=%d&view='.$view.'&shop_id='.$shopId ));
    //        $render->pagedata['pager'] = $pager;
    //        $render->pagedata['servicenamelist'] = $servicenamelist;
    //    }

    //    return $render->fetch('admin/member/service.html');

    //}


    public function getMemberId($id)
    {
        $obj = $this->getMemberAnalysisObj();
        $memberInfo = $obj->dump($id);
        return $memberInfo['member_id'];
    }

    protected function getMemberAnalysisObj()
    {
        if (self::$memberAnalysisObj == null) {
            self::$memberAnalysisObj = app::get('taocrm')->model('member_analysis');
        }
        return self::$memberAnalysisObj;
    }
}
