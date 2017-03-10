<?php

class taocrm_finder_middleware_fx_member_report
{
    protected static $memberAnalysisObj = '';
    protected static $membersObj = null;
    protected static $hardeWareConnect = null;
    protected $shop_id = '';
    function __construct(){
        $this->oMembers = &app::get('taocrm')->model('fx_members');
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
        
//        $result = $connect->AnalysisByMemberId();
    }
   
   
    var $detail_goods = '买过的商品';
    function detail_goods($id){

        $orderObj = app::get('ecorder')->model('fx_orders');
        $orders = $orderObj->getList('order_id',array('member_id'=>$id));
        foreach($orders as $v){
        	$arr[] = $v['order_id'];
        }
       
        $data = $this->mergeGoods($arr);
        $goods = $data['item'];
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['goods'] = $goods;
        return $render->fetch('admin/fx/member/goods.html');
    }   

    var $detail_order = '历史订单';
    function detail_order($id=null){
//        $id = $this->getMemberId($id);
//        if(!$id) return null;
//        $this->getMemberInfo($id);
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }
       	$filter['member_id'] = $id;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orderObj = &app::get(ORDER_APP)->model('fx_orders');
        $order_cols = 'order_id,order_bn,status,pay_status,ship_status,agent_uname,payed,total_amount,createtime,shop_id';
        $orders = $orderObj->getList($order_cols, $filter);
        $row = $orderObj->getList('order_id', $filter);
        $count = count($row);
        foreach($orders as $key=>$order){
            $orders[$key]['status'] = $orderObj->trasform_status('status',$orders[$key]['status']);
            $orders[$key]['pay_status'] = $orderObj->trasform_status('pay_status',$orders[$key]['pay_status'] );
            $orders[$key]['ship_status'] = $orderObj->trasform_status('ship_status', $orders[$key]['ship_status']);      
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['orders'] = $orders;
        return $render->fetch('admin/fx/member/order.html');
        
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
            $sql = "SELECT order_id, bn, `name`, nums, amount FROM `sdb_ecorder_fx_order_items` WHERE order_id in (".implode(',',$orders).")";
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
   
    var $detail_addr = '收货地址';
    function detail_addr($id=null){
        $this->member_id = $id;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = app::get('ecorder')->model('fx_orders');
        $addrs = $addrObj->getList('distinct ship_name,ship_area,ship_addr,ship_email,ship_mobile,ship_tel',array('member_id' => $this->member_id));
        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;
        return $render->fetch('admin/fx/member/addr.html');
    }
    
}