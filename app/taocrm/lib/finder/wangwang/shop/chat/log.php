<?php

class taocrm_finder_wangwang_shop_chat_log
{
    protected static $hardeWareConnect = null;
    protected static $memberAnalysisObj = null;
    
    var $addon_cols = "uname";
    
    public $shop_id = null;
    
    var $column_buyer_nick = "买家旺旺";
    var $column_buyer_nick_width = 180;
    var $column_buyer_nick_order = 10;
    function column_buyer_nick($row)
    {
        $buyer_nick = $row[$this->col_prefix.'uname'];
        $button1 = ' <a target="_blank" href="http://www.taobao.com/webww/ww.php?ver=3&touid='.$buyer_nick.'&siteid=cntaobao&status=2&charset=utf-8" ><img align=absmiddle border="0" src="http://amos.alicdn.com/online.aw?v=2&uid='.$buyer_nick.'&site=cntaobao&s=2&charset=utf-8" alt="点击发消息" width=16 height=16 /> &nbsp;'.$buyer_nick.'</a>';
        return  $button1;
    }
    
    var $detail_basic = '统计信息';
    public function detail_basic($id)
    {
        $info = $this->getChatLogById($id);
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
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['analysis'] = $data;
        return $render->fetch('admin/member/analysis.html');
    }
    
    private function getChatLogById($id)
    {
        $app = app::get("taocrm");
        $chatLogModel = $app->model('wangwang_shop_chat_log');
        $info = $chatLogModel->dump(array('id' => $id));
        return $info;
    }
    
    protected function getShopId()
    {
        return $this->shop_id;
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
     * 获得店铺等级名称
     */
    protected function getShopEvaluation($key)
    {
        $type = $this->getDbShemaColumns('shop_evaluation');
        return (isset($type[$key]) ? $type[$key] : '');
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
    
    protected function getConnect()
    {
        if (self::$hardeWareConnect == null) {
            self::$hardeWareConnect = new taocrm_middleware_connect;
        }
        return self::$hardeWareConnect;
    }
    protected function getMemberAnalysisObj()
    {
        if (self::$memberAnalysisObj == null) {
            self::$memberAnalysisObj = app::get('taocrm')->model('member_analysis');
        }
        return self::$memberAnalysisObj;
    }
}
