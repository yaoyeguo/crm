<?php

class taocrm_finder_middleware_member_analysis
{
    var $pagelimit = 20;
    var $addon_cols = 'member_id,shop_id';
    var $shop_evaluation =array('good'=>'好评','bad'=>'差评','neutral'=>'中评','unkown'=>'-');
    protected static $hardeWareConnect = null;
    protected static $membersObj = null;
    protected static $membersInfo = array();
    protected static $memberAnalysisObj = '';
    protected static $shopObj = '';

    var $column_edit = '操作';
    var $column_edit_width = 60;
    var $column_edit_order = COLUMN_IN_HEAD;
    public function column_edit($row)
    {
        $user = kernel::single('desktop_user');
        $user_id = $user->get_id();
        $is_super = $user->is_super();
        $users = app::get('desktop')->model('users');
        $sdf_users = $users->dump($user_id);
        if($is_super || $sdf_users['customer_delete']){
            $act = '<a href="index.php?app=taocrm&ctl=admin_member&act=delete_member&member_id='.$row['_0_member_id'].'&tagInfo='.$row['tagInfo'].'&shop_id='.$row['_0_shop_id'].'"  target="dialog::{title:\''.app::get('taocrm')->_('是否删除？').'\', width:360, height:100}">'.app::get('taocrm')->_('删除').'</a>';
        }
        return $act;
    }

    protected function getConnect()
    {
        if (self::$hardeWareConnect == null) {
            self::$hardeWareConnect = new taocrm_middleware_connect;
        }
        return self::$hardeWareConnect;
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
    }

    protected function getMemberAnalysisObj()
    {
        if (self::$memberAnalysisObj == null) {
            self::$memberAnalysisObj = app::get('taocrm')->model('member_analysis');
        }
        return self::$memberAnalysisObj;
    }

    /**
     * 获得店铺对象
     * Enter description here ...
     */
    protected function getShopObj()
    {
        if (self::$shopObj == '') {
            self::$shopObj = &app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
    }

    /**
     * 获得所有店铺ID
     */
    protected function getAllShopId()
    {
        $shopObj = $this->getShopObj();
        $shopList = $shopObj->getList('shop_id,name');
        return $shopList;
    }

    /**
     * 获得店铺ID
     */
    protected function getShopId()
    {
        /*
        if ($this->shop_id == '') {
            $shopList = $this->getAllShopId();
            if(isset($_GET['view']))
            $currentShopInfo = $shopList[intval($_GET['view'])];
            if ($currentShopInfo) {
                $this->shop_id = $currentShopInfo['shop_id'];
            }
            else {
                if (isset($_GET['shop_id'])) {
                    $this->shop_id = $_GET['shop_id'];
                }
                else {
                    if(!isset($_GET['no_shopid']))
                    $this->shop_id = $shopList[0]['shop_id'];
                }

            }

        }
        */
        
        $this->shop_id = $_GET['shop_id'];
        return $this->shop_id;
    }

    protected function setMembersInfo($member_id, $shop_id)
    {
        if ($shop_id == '') {
            $shop_id = $this->getShopId();
        }

        if (isset(self::$membersInfo[$shop_id][$member_id])) {
            self::$membersInfo[$shop_id][$member_id] = $this->getMemberData($member_id);
        }

    }

    protected function getMembersInfo($member_id, $shop_id = '')
    {
        if ($shop_id == '') {
            $shop_id = $this->getShopId();
        }
        $memberInfo = array();
        if (isset(self::$membersInfo[$shop_id][$member_id])) {
            $memberInfo = self::$membersInfo[$shop_id][$member_id];
        }
        else {
            $this->setMembersInfo($member_id, $shop_id);
            $memberInfo = self::$membersInfo[$shop_id][$member_id];
        }
        return $memberInfo;
    }

    function __construct(){
        $this->oMembers = &app::get('taocrm')->model('members');
        $oShop = &app::get('ecorder')->model('shop');
        $rs = $oShop->getList('shop_id,name');
        if(!$rs) return false;
        foreach($rs as $v){
            $this->shops[$v['shop_id']] = $v['name'];
        }
    }

    var $column_tag = '标签';
    var $column_tag_order = 2;
    var $column_tag_width = 60;
    function column_tag($row)
    {
        $tagInfo = $row['tagInfo'];
        if($tagInfo){
            $tagInfo = '<img border=0 title="'.$tagInfo.'" align="absmiddle" src="'.app::get('taocrm')->res_url.'/teg_ico.png" >';
        }

        return $tagInfo;
    }

    var $column_name = '姓名';
    var $column_name_width = 70;
    var $column_name_order = 15;
    function column_name($row)
    {
        return $row['name'];
    }

    /*
    var $column_shop = '来源店铺';
    var $column_shop_order = 20;
    function column_shop($row){
        $shop_id = $row['shop_id'];
        if(!$shop_id)return '';
        return $this->shops[$shop_id];
    }
    */

    public $column_area = '地区';
    public $column_area_width = 140;
    public $column_area_order = 30;
    //public $column_area_order_field = 'district';
    public function column_area($row)
    {
        return $row['area'];
    }

    /*
    public $column_pointshd = '客户积分';
    public $column_pointshd_width = 70;
    public $column_pointshd_order = 110;
    public function column_pointshd($row)
    {
        return $row['points'];
    }
        */

    /*
    public $column_lvidhd = '客户等级';
    public $column_lvidhd_width = 80;
    public $column_lvidhd_order = 120;
    public function column_lvidhd($row)
    {
        $result = $this->getMemberLvInfo($row['lv_id']);
        return $result['name'];
    }
    */

    /*
    public $column_fvipinfohd = '淘宝客户等级';
    public $column_fvipinfohd_width = 80;
    public $column_fvipinfohd_order = 121;
    public function column_fvipinfohd($row)
    {
        $member_id = $row['member_id'];
        $shop_id = $row['shop_id'];
        $model = $this->getMemberAnalysisObj();
        $result = $model->dump(array('shop_id' => $shop_id, 'member_id' => $member_id), 'f_vip_info');
        $defaultLv = 'c';
        $type = $this->getDbShemaColumns('f_vip_info');
        if ($result['f_vip_info'] && (isset($type[$result['f_vip_info']]))) {
            $defaultLv = $result['f_vip_info'];
        }
        return $type[$defaultLv];
    }
    */

    private function getMemberInfo($id)
    {
        $rs = app::get('taocrm')->model('member_analysis')->getUniqueKey($id);
        $this->member_id = $rs['member_id'];
        $this->shop_id = $rs['shop_id'];
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

    /**
     * 获得客户分析表客户信息
     */
    protected function getAnalysisMember($memberId)
    {
        $shopId = $this->getShopId();
        $model = $this->getMemberAnalysisObj();
        $filter = array('member_id' => $memberId, 'shop_id' => $shopId);
        $member_analysis = $model->dump($filter);
        return $member_analysis;
    }

    var $detail_edit = '客户信息';
    function detail_edit($id)
    {
        $app = app::get('taocrm');
        $render = $app->render();
        //$this->getMemberInfo($id);
        $this->member_id = $id;
        $shop_id = $this->getShopId();
        $memberObj = $app->model('members');
        $taocrm_service_member = kernel::single('taocrm_service_member');

        if($_POST){
            $member_id = intval($_POST['member_id']);
            $member_analysisObj = $app->model('member_analysis');
            $data=array();
            $data['member_card']=trim($_POST['member_card']);
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
            $member_analysisObj->update(
                array('is_vip'=>$_POST['is_vip']),
                array('member_id'=>$member_id)
            );
            $memberObj->update(
                $data,
                array('member_id'=>$member_id)
            );
            $memberObj->chkMemberArea($member_id);

            //同步更新到内存
            $connect = $this->getConnect();
            $memoryData = array(
                'shopId' => $shop_id,
                'memberId' => $member_id,
                'mobile' => $data['mobile'],
                'email' => $data['email'],
                'name' => $data['name'],
                'sms_blacklist' => $data['sms_blacklist'],
                'is_vip' => $data['is_vip'],
                'edm_blacklist' => $data['edm_blacklist'],
                'birthday' => $data['birthday'],
            );
            $connect->updateMember($memoryData);

            //保存客户自定义属性
            $prop_name = $_POST['prop_name'];
            $memberObj->save_member_prop_val($prop_name, $member_id, $shop_id);
            
            //保存ext扩展属性
            if($_POST['birthday']){
                $ext_info = array();
                $ext_info['member_id'] = $_POST['member_id'];
                list($ext_info['b_year'],$ext_info['b_month'],$ext_info['b_day']) = 
                    explode('-', $_POST['birthday']);
                $taocrm_service_member->save_member_ext($ext_info);
            }
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

        //获取店铺自定义属性
        $oShop = app::get('ecorder')->model('shop');
        $rs_shop = $oShop->dump($shop_id, '*');
        $shop_config = unserialize($rs_shop['config']);

        //获取保存的自定义属性
        if($shop_config){
            $prop_val = $memberObj->get_member_prop_val($this->member_id, $shop_id);
            if($prop_val==array('','','','','','','','','','')){
            $oMemberProp = app::get('taocrm')->model('member_property');
            $rs = $oMemberProp->getList('*',array('shop_id'=>$this->getShopId(),'uname'=>$mem['account']['uname']));
            if($rs){
                    foreach($rs as $k=>$v){
                        $prop_val[$k] = $v['value'];
                    }
                }
            }
        }

        //$shop_config['prop_name'] = array_unique($shop_config['prop_name']);

        $redirect_prefix = 'index.php?act=index&action=detail&finderview=detail_edit';
        $redirect_uri = '&app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&id='.$id.'&view='.$_GET['view'].'&shop_id='.$rs_shop['shop_id'];
        $render->pagedata['redirect_uri'] = base64_encode($redirect_uri);
        $render->pagedata['prop_val'] = $prop_val;
        $render->pagedata['mem'] = $mem;
        $render->pagedata['shops'] = $shops;
        $render->pagedata['rs_shop'] = $rs_shop;
        $render->pagedata['prop_name'] = $shop_config['prop_name'];
        $render->pagedata['prop_type'] = $shop_config['prop_type'];
        return $render->fetch('admin/member/edit.html');
    }

    var $detail_goods = '买过的商品';
    function detail_goods($id)
    {
        //获取店铺id
        $shopId = $this->getShopId();
        if(!$id) return null;
        $app = app::get('taocrm');
        $render = $app->render();
        $goods = array();
        $orders = array();

        $this->member_id = $id;
        $sql = "select order_id from sdb_ecorder_orders where shop_id = '".$shopId."' and member_id=".$this->member_id." and (pay_status='1' or ship_status='1') ";
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

    var $detail_order = '历史订单';
    function detail_order($id=null){
        //        if(!$id) return null;
        //        $this->getMemberInfo($id);
        $this->getShopId();
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
        //$nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orderObj = &app::get(ORDER_APP)->model('orders');
        $order_cols = 'order_id,order_bn,status,pay_status,ship_status,
            total_amount,createtime,shop_id,payed';
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

    var $detail_contact = '联 系 人';
    function detail_contact($id=null){
        $this->member_id = $id;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_contacts');
        //获取店铺id
        $shopId = $this->getShopId();
        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id,'shop_id'=>$shopId));
        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;
        return $render->fetch('admin/member/contact.html');
        //        if(!$id) return null;
        //        $this->getMemberInfo($id);
        //        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        //        $app = app::get('taocrm');
        //        $addrObj = $app->model('member_contacts');
        //        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));
        //        $render = $app->render();
        //        $render->pagedata['addrs'] = $addrs;
        //        return $render->fetch('admin/member/contact.html');
    }

    var $detail_addr = '收货地址';
    function detail_addr($id=null){
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_receivers');
        $addrs = $addrObj->getList('*',array('member_id' => $id));

        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;

        return $render->fetch('admin/member/addr.html');
        //        if(!$id) return null;
        //        $this->getMemberInfo($id);
        //
        //        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        //        $app = app::get('taocrm');
        //        $addrObj = $app->model('member_receivers');
        //        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));
        //
        //        $render = $app->render();
        //        $render->pagedata['addrs'] = $addrs;
        //
        //        return $render->fetch('admin/member/addr.html');
    }
    /*
     var $detail_remark = '客户备注';
     function detail_remark($id){
     $this->getMemberInfo($id);
     $app = app::get('taocrm');
     $memberObj = $app->model('members');
     if($_POST){
     $sdf['remark'] = $_POST['remark'];
     $sdf['remark_type'] = $_POST['remark_type'];
     if(!$memberObj->update($sdf,array('member_id' => $this->member_id))){
     $msg = app::get('b2c')->_('保存失败!');
     header('Content-Type:text/jcmd; charset=utf-8');
     echo '{error:"'.$msg.'",_:null}';
     exit;
     }
     if($_GET['singlepage']=='true'){
     $msg = app::get('b2c')->_('保存成功!');
     header('Content-Type:text/jcmd; charset=utf-8');
     echo '{success:"'.$msg.'",_:null}';
     exit;
     }
     }
     $remark = $memberObj->getRemarkByMemId($this->member_id);
     $render = $app->render();
     $render->pagedata['remark_type'] = $remark['remark_type'];
     $render->pagedata['remark'] =  $remark['remark'];
     $render->pagedata['res_url'] = $app->res_url;
     return $render->fetch('admin/member/remark.html');
     }
     */

    var $detail_active = '营销活动';
    function detail_active($id)
    {
        if(!$id) $id = $_GET['id'];

        $app = app::get('taocrm');
        $marketApp = app::get('market');
        $orderModel = app::get('ecorder')->model('orders');

        $pagelimit = 3;//分页
        $page = max(1, intval($_GET['page']));
        $offset = ($page - 1) * $pagelimit;
        $activeMemberModel = $marketApp->model('active_member');
        $shopId = $this->getShopId();
        $filter = array(
            'issend' => 1,
            'member_id' => $id,
            'shop_id' => $shopId
        );
        $memberList = $activeMemberModel->getList('*', $filter, $offset, $pagelimit, 'active_id DESC');
        $count = $activeMemberModel->count($filter);
        $render = $app->render();

        $filter = array(
            'member_id' => $id,
            'shop_id' => $shopId
        );
        $rs_orders = $orderModel->getList('order_id,order_bn,member_id,pay_time', $filter);
        if(!$rs_orders) $rs_orders=array();

        if($memberList){
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
            foreach($memberList as $v){
                $activenamelist[$i]['shop_name'] = isset($shopList[$v['shop_id']]) ? $shopList[$v['shop_id']] : '未知店铺';
                $activeInfo = $activeModel->dump(array('active_id' => $v['active_id']));
                if($activeInfo){

                    foreach($rs_orders as $v_order){
                        if($v_order['pay_time']>=$activeInfo['create_time'] && $v_order['pay_time']<=$activeInfo['end_time']){
                            $activenamelist[$i]['orders'][] = $v_order;
                        }
                    }

                    $activenamelist[$i]['active_name'] = $activeInfo['active_name'];
                    $activenamelist[$i]['total_num'] = $activeInfo['total_num'];
                    $activenamelist[$i]['create_time'] = date("Y-m-d H:i:s", $activeInfo['create_time']);
                    $activenamelist[$i]['end_time'] = date("Y-m-d H:i:s", $activeInfo['end_time']);
                }else{
                    $activenamelist[$i]['active_name'] = '活动已经删除';
                    $activenamelist[$i]['total_num'] = '未知';
                    $activenamelist[$i]['create_time'] = '未知';
                    $activenamelist[$i]['end_time'] = '未知';
                }
                $i++;
            }

            $view = $_GET['view'];
            $total_page = ceil($count / $pagelimit);
            $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_active&page=%d&view='.$view.'&shop_id='.$shopId ));
            $render->pagedata['pager'] = $pager;
            $render->pagedata['activenamelist'] = $activenamelist;
        }
        return $render->fetch('admin/member/active.html');
    }

    var $detail_points = '积分日志';
    function detail_points($id)
    {
        if(!$id){
            $id = $_GET['id'];
        }
        $this->member_id = $id;
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
                //重新校对积分
                //kernel::single('taocrm_member_point')->init_member_point($v['shop_id'], $id);
            }
        }
        
        $mdl_points_log = app::get('taocrm')->model('all_points_log');
        $schema = $mdl_points_log->get_schema();
        //$points_type_conf = $schema['columns']['points_type']['type'];

        //$mdl_member_analysis = app::get('taocrm')->model('member_analysis');
        //$analysis_data = $mdl_member_analysis->dump(array('member_id'=>$id));
        $analysis_data = array(
            'shop_id'=>$_GET['shop_id'],
        );
        $mdl_member_points = app::get('taocrm')->model('member_points');
        $points = $mdl_member_points->get_points(array('member_id'=>$this->member_id,'shop_id'=>$analysis_data['shop_id']));
        foreach($points as $k=>$v){
            $points[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            //$points[$k]['points_type'] = $points_type_conf[$v['points_type']];
        }
        $pagelimit = 3;//分页
        $page_log = max(1, intval($_GET['page_log']));
        $offset = ($page_log - 1) * $pagelimit;
        $logs = $mdl_points_log->db->select('select * from sdb_taocrm_all_points_log where member_id='.$this->member_id.' and shop_id="'.$analysis_data['shop_id'].'" order by id desc limit '.$offset.','.$pagelimit);
        $count = $mdl_points_log->count(array('member_id'=>$this->member_id,'shop_id'=>$analysis_data['shop_id']));
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
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page_log, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_points&page_log=%d&id='.$id.'&shop_id='.$analysis_data['shop_id']));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['logs'] = $logs;
        $render->pagedata['points'] = $points;
        $render->pagedata['member_id'] = $id;
        $render->pagedata['finder_id'] = $_GET['finder_id'];
        $render->pagedata['source_page'] = 'member_analysis';
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

    /**
     * 获得客户等级
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

    var $detail_caselog = '服务记录';
    function detail_caselog($member_id)
    {
        if(!$member_id) $member_id = $_GET['member_id'];
        $app = app::get('taocrm');
        $ecorder = app::get('ecorder');
        $member_caselog = $app->model('member_caselog');
        $shop = $ecorder->model('shop');

        $rs_shop = $shop->getList('shop_id,name');
        foreach($rs_shop as $v){
            $shops[$v['shop_id']] = $v['name'];
        }

        $rs_category = $app->model('member_caselog_category')->getList('category_id,category_name');
        foreach($rs_category as $v){
            $categorys[$v['category_id']] = $v['category_name'];
        }
        $shopId = $this->getShopId();
        $rs_caselog = $member_caselog->getList('*',array('member_id'=>$member_id,'shop_id'=>$shopId),0,-1,'id desc');
        foreach($rs_caselog as &$v){
            $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            if($v['alarm_time']>0)
            $v['alarm_time'] = date('Y-m-d H:i', $v['alarm_time']);
            $v['shop_name'] = $shops[$v['shop_id']];
            $v['media'] = $categorys[$v['media']];
            $v['category'] = $categorys[$v['category']];
            $v['content'] = mb_substr($v['content'],0,12,'utf-8');
        }

        $render = $app->render();
        $render->pagedata['caselog'] = $rs_caselog;
        return $render->fetch('admin/member/finder/caselog.html');
    }

    var $detail_mobilemsg = '短信记录';
    public function detail_mobilemsg($member_id)
    {
        //获取店铺id
        $this->getShopId();
        $rs = app::get('taocrm')->model('members')->dump($member_id, 'mobile');
        if($rs) {
        $sms_log_mod = app::get('taocrm')->model('sms_log');
        $params = array(
                'mobile' => $rs['contact']['phone']['mobile'],
                'shop_id' => $this->shop_id,
                //'status' => 'succ',
        );
        $log_list = $sms_log_mod->getList('*',$params,0,10,'send_time desc');
        $source_type = array(
                'active_plan' => '营销计划',
                'active_cycle' => '周期营销',
                'market_active' => '营销活动',
                'plugins_plugins' => '自动插件',
                'taocrm_member_import_batch' => '导入客户',
                'taocrm_member_caselog' => '服务记录',
                'market_callplan' => '呼叫计划',
                'taocrm_member_group' => '自定义分组',
                'market_fx_activity' => '分销活动',
                'sale_model' => '营销模型',
                'weixin' => '微信服务',
                'report' => '运营报表',
                'other' => '其他',
            );
            foreach($log_list as $k => $log){
            $log_list[$k]['source'] = $source_type[$log['source']];
            }
        }
        $render = app::get('taocrm')->render();
        $render->pagedata['log_list'] = $log_list;
        return $render->fetch('admin/member/all/mobilemsg.html');
    }
}
