<?php

/**
 * 评价同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_taobao_traderates extends ecorder_rpc_request {

    protected $topClient;

    protected $_shopInfo = array();

    protected $_pids = array();

    protected $sync_traderates_start_time = '';

    protected $sync_traderates_end_time = '';

    protected $_badTradeRates = array();

    protected $_neutralTradeRates = array();

    protected $_goodTradeRates = array();

    protected $count = 0;

    protected $_rertyCount = 0;

    protected $_rertyMaxCount = 3;

    public function __construct(){
        $c = new ectools_top_TopClient();
        $c->format = "json";
        $c->appkey = TOP_APP_KEY;
        $c->secretKey = TOP_SECRET_KEY;
        $this->topClient = $c;
    }

    public function download($shop_id=''){
        kernel::ilog(__CLASS__ . ' download start......');
        //$shop_id = '0079cb8b7e61e2269f1ccc8d2ba3f953';
        if (!empty($shop_id)){
            $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao" and shop_id='.'"'.$shop_id.'"');
        }else {
            $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao"');
        }
        $sync_traderates_start_time = app::get(ORDER_APP)->getConf('sync_traderates_lasttime');
        $this->sync_traderates_start_time = $sync_traderates_start_time ? $sync_traderates_start_time : date('Y-m-d H:i:s',strtotime('-1 day'));
        $this->sync_traderates_end_time = date('Y-m-d H:i:s');
        foreach($shopList as $shop){
            $addon = unserialize($shop['addon']);
            if($addon && !empty($addon['session'])){
                kernel::ilog($shop['name'] . ' start......');
                $this->_shopInfo = array('channel_id'=>$shop['channel_id'],'shop_id'=>$shop['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname']);
                $this->getAll('TraderatesGetRequest');

                //设置客户评价
                $this->processMemberRate();
                kernel::ilog($shop['name'] . ' end......');
            }
        }

        //统计商品信息    评价
        kernel::single('ecgoods_service_products')->setTradeRate($this->_pids);

        //设置最后下载时间
        app::get(ORDER_APP)->setConf('sync_traderates_lasttime',$this->sync_traderates_end_time);

        kernel::ilog(__CLASS__ . ' download end......');
    }


    protected function getAll($task,$pageNo=1){
        kernel::database()->dbclose();
        $result = $this->switchTask($task,$pageNo);
        if($result == 'timeout'){
            kernel::ilog($task . '-'. $pageNo . ' is ' . $result);
            kernel::ilog('sleep 3 sec...');
            sleep(3);

            if( $this->_rertyCount < $this->_rertyMaxCount ){
                $this->_rertyCount++;
                $this->getAll($task,$pageNo);
            }else{
                kernel::ilog('rerty finish...');
                $this->_rertyCount = 0;
                $pageNo++;
                $this->getAll($task,$pageNo);
            }
        }else if($result == 'success'){
            $pageNo++;
            $this->getAll($task,$pageNo);
        }else if($result == 'finish'){
             

        }else{
            kernel::ilog($result);
        }
    }

    protected function switchTask($task,$pageNo=1,$pageSize=150){
        return $this->{$task}($pageNo,$pageSize);
    }

    protected function TraderatesGetRequest($pageNo,$pageSize){
        $msg = '';
        $req = new ectools_top_request_TraderatesGetRequest();
        $req->setFields('tid,oid,role,nick,result,created,content,reply');
        $req->setRateType('get');
        $req->setRole('buyer');
        $req->setPageSize($pageSize);
        $req->setPageNo($pageNo);
        //$req->setTid('126676939275142');
        //echo $this->sync_traderates_start_time.'='.$this->sync_traderates_end_time;exit;
        //$this->sync_traderates_end_time = '2012-04-09 00:00:00';
        if($this->sync_traderates_start_time){
            $req->setStartDate($this->sync_traderates_start_time);
        }
        $req->setEndDate($this->sync_traderates_end_time);

        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
        //kernel::ilog(var_export($resp,true));
        //var_dump($resp);exit;
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
            if($resp->code == 'Remote service error' && $resp->msg == 'isp.top-remote-connection-timeout'){
                kernel::ilog($msg);
                $msg = ('timeout');
            }
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            $total_results = $resp->total_results;
            // 循环插入商品数据
            if($resp->trade_rates->trade_rate) {
                foreach($resp->trade_rates->trade_rate as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    echo $v['tid']."\n";
                    $rate_id = $this->processTradeRates($v);
                    if(!$rate_id){
                        kernel::ilog($v['tid'] . ' create failed.');
                        continue;
                    }
                }
            }
            $msg = 'success';
        }

        if($pageSize*$pageNo >= $total_results){
            $msg = 'finish';
        }

        return $msg;
    }


    protected function processTradeRates($tradeRate){
        $orderInfo = $this->checkOrder($tradeRate['tid']);
        if(!$orderInfo){
            return false;
        }
        //var_dump($tradeRate);exit;
        $tradeRate['order_id'] = $orderInfo['order_id'];
        $tradeRate['member_id'] = $orderInfo['member_id'];

        //转换订单评价标准格式sdf
        $sdf = $this->convertRateSdfParams($tradeRate);
        if($this->acceptCreateRate($sdf)){
            $sdf['create_time'] = time();
        }else{
            $sdf = array_merge($sdf, array('rate_id' => $sdf['rate_id']));
        }

        $rate_id =  kernel::single("ecorder_service_traderates")->saveTradeRates($sdf);
        //$this->count++;
        //echo $this->count . ':'.$tradeRate['tid']."\n";

        if($rate_id){
             
            kernel::database()->exec('update sdb_ecorder_order_items set evaluation = "'.$tradeRate['result'].'" where oid = "' . $tradeRate['oid'] . '"');

            //收集评价过的货品
            $row = kernel::database()->selectrow('select product_id from sdb_ecorder_order_items where oid = "' . $tradeRate['oid'] . '"');
            if(!in_array($row['product_id'], $this->_pids)){
                $this->_pids[] = $row['product_id'];
            }

            if($tradeRate['result'] == 'bad'){
                $this->_badTradeRates[] = $tradeRate['tid'];
            }

            if($tradeRate['result'] == 'neutral'){
                $this->_neutralTradeRates[] = $tradeRate['tid'];
            }

            if($tradeRate['result'] == 'good'){
                $this->_goodTradeRates[] = $tradeRate['tid'];
            }
        }


        return $rate_id;
    }

    protected function checkOrder($tid){
        $row = kernel::database()->selectrow('select order_id,member_id from sdb_ecorder_orders where shop_id = "'. $this->_shopInfo['shop_id'] .'" and  order_bn = "'. $tid .'" ');
        if($row){
            return $row;
        }else{
            return false;
        }
    }

    protected function convertRateSdfParams($item){
        $sdf = array(
            'order_id' => $item['order_id'],
            'order_bn' => $item['tid'],
         	'oid' => $item['oid'],
            'role' => $item['role'],
            'nick' => $item['nick'],
            'member_id' => $item['member_id'],
            'result' => $item['result'],
            'created' => strtotime($item['created']),
            'content' => $item['content'],
            //'reply' => $item['reply'],
            'shop_id' => $this->_shopInfo['shop_id'],
            'channel_id' => $this->_shopInfo['channel_id'],
        );

        return $sdf;
    }

    protected function acceptCreateRate(& $rateInfo) {
        $rate = app::get(ORDER_APP)->model('trade_rates')->dump(array('oid'=>$rateInfo['oid']),'rate_id');
        if($rate){
            $rateInfo['rate_id'] = $rate['rate_id'];
            return false;
        }else{
            return true;
        }
    }

    protected function setMemberRate($tradeRates,$result){
        $memberData = array();
        $tids = array();
        $ordersObj = app::get('ecorder')->model('orders');
        if(!empty($tradeRates)){
            $pageNo = 0;
            $pageSize = 100;
            $memberIds = array();
            while(true){
                $arr = array_slice($tradeRates, $pageNo*$pageSize,$pageSize);
                if(!$arr)break;
                $rows = $ordersObj->getList('member_id',array('order_bn'=>$arr),0,-1,'member_id');
                if(!$rows)break;
                foreach($rows as $row){
                    if(!in_array($row['member_id'], $memberIds)){
                        $memberIds[] = $row['member_id'];
                    }
                }
                $pageNo++;
            }

            foreach($memberIds as $memberId){
                kernel::single('taocrm_service_member')->setMemberRate($this->_shopInfo['shop_id'],$memberId,$result);
            }

        }
    }

    protected function processMemberRate(){
        $this->setMemberRate($this->_goodTradeRates, 'good');
        $this->setMemberRate($this->_badTradeRates, 'bad');
        $this->setMemberRate($this->_neutralTradeRates, 'neutral');
    }
     
}