<?php

/**
 * 评价同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_taobao_orders extends ecorder_rpc_request {

    protected $topClient;

    protected $_shopInfo = array();

    protected $sync_refunds_start_time = '';

    protected $sync_refunds_end_time = '';

    protected $count = 0;

    public function __construct(){
        $c = new ectools_top_TopClient();
        $c->format = "xml";
        $c->appkey = TOP_APP_KEY;
        $c->secretKey = TOP_SECRET_KEY;
        $this->topClient = $c;
    }

    public function get_unpaid_orders($start_created, $end_created, $shopInfo)
    {
        $req = new ectools_top_request_TradesSoldGetRequest();
        $page_size = 100;
        $req->setFields('tid,seller_nick, buyer_nick, receiver_name, receiver_state, receiver_city, receiver_district, receiver_mobile,payment');
        $req->setType('fixed,auction,hotel_trade,auto_delivery,ec,cod,b2c_cod,eticket,tmall_i18n');
        $req->setPageSize($page_size);
        $req->setStatus('ALL_WAIT_PAY');
        $req->setStartCreated($start_created);
        $req->setEndCreated($end_created);

        //最少有一页
        $maxPageNum = 1;
        $orders = array();
        for ($page = 1; $page <= $maxPageNum; $page++) {

            $req->setPageNo($page); 
            $apiResult = $this->topClient->execute($req,$shopInfo['session']);
            $apiResult = json_encode($apiResult);
            $apiResult = json_decode($apiResult, true);
            
            if (is_array($apiResult) && !isset($apiResult['code']) && !empty($apiResult['trades']['trade'])) {

                if ($page == 1) {
                    //如果是第一页，重设$maxPageNum
                    $totalNum = $apiResult['total_results'];
                    $maxPageNum = ceil($totalNum / $page_size);
                }

                if(!isset($apiResult['trades']['trade']['tid'])){
                    foreach ($apiResult['trades']['trade'] as $value) {
                        $value['shop_id'] = $shopInfo['shop_id'];
                        $orders[] = $value;
                    }
                }else{
                    $apiResult['trades']['trade']['shop_id'] = $shopInfo['shop_id'];
                    $orders[] = $apiResult['trades']['trade'];
                }
            }
        }

        return $orders;
    }
}