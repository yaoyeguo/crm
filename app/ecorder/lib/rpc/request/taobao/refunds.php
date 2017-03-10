<?php

/**
 * 评价同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_taobao_refunds extends ecorder_rpc_request {

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

    public function download($shop_id='', $date_from='', $date_to=''){
        set_time_limit(360);        
        kernel::ilog(__CLASS__ . ' download start......');
        $sync_refunds_start_time = app::get(ORDER_APP)->getConf('sync_refunds_lasttime');
        if(!$sync_refunds_start_time){
            $sync_refunds_start_time = date('Y-m-d H:i:s', strtotime('-30 days'));
        }
        $this->sync_refunds_start_time = $sync_refunds_start_time;
        $this->sync_refunds_end_time = date('Y-m-d H:i:s');
        
        if($date_from) $this->sync_refunds_start_time=$date_from;
        if($date_to) $this->sync_refunds_end_time=$date_to;
        
        $sql = 'select * from sdb_ecorder_shop where node_id is not null and node_type="taobao"';
        if($shop_id != '') $sql .= " and shop_id='$shop_id' ";
        $shopList = kernel::database()->select($sql);
        foreach($shopList as $shop){
            $addon = unserialize($shop['addon']);
            if($addon && !empty($addon['session'])){
                kernel::ilog($shop['name'] . ' start......');
                $this->_shopInfo = array('channel_id'=>$shop['channel_id'],'shop_id'=>$shop['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname']);
                $this->getAll('RefundsReceiveGetRequest');

                kernel::ilog($shop['name'] . ' end......');
            }
        }

        //设置最后下载时间
        app::get(ORDER_APP)->setConf('sync_refunds_lasttime',$this->sync_refunds_end_time);
        
        kernel::ilog(__CLASS__ . ' download end......');
    }

    protected function getAll($task,$pageNo=1){
        $result = $this->switchTask($task,$pageNo);
        if($result == 'timeout'){
            kernel::ilog($task . '-'. $pageNo . ' is ' . $result);
            $result = $this->getAll($task,$pageNo);
        }else if($result == 'success'){
            $pageNo++;
            $this->getAll($task,$pageNo);
        }else if($result == 'finish'){
             

        }else{
            kernel::ilog($result['msg']);
        }
    }

    protected function switchTask($task,$pageNo=1,$pageSize=100){
        return $this->{$task}($pageNo,$pageSize);
    }

    protected function RefundsReceiveGetRequest($pageNo,$pageSize){
        //$this->sync_refunds_start_time = '2013-05-25 00:00:00';
        //$this->sync_refunds_end_time = '2013-11-25 23:59:59';
        $msg = '';
        $req = new ectools_top_request_RefundsReceiveGetRequest();
        $req->setFields('refund_id, tid, title, buyer_nick, seller_nick, total_fee, status, created, refund_fee, oid, good_status, company_name, sid, payment, reason, desc, has_good_return, modified, order_status, num');
        //$req->setStatus('SUCCESS');
        $req->setPageSize($pageSize);
        $req->setPageNo($pageNo);
        if($this->sync_refunds_start_time){
            $req->setStartModified($this->sync_refunds_start_time);
        }
        $req->setEndModified($this->sync_refunds_end_time);

        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
        $resp = json_encode($resp);
        $resp = json_decode($resp, true);
        //kernel::ilog(var_export($resp,true));
        //var_dump($resp);exit;
        if($resp['code'] || $resp['msg']){
            $msg = ('【code】'.$resp['code'].'<br/>【msg】'.$resp['msg']);
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            $total_results = $resp['total_results'];
            // 循环插入商品数据
            if($resp['refunds']['refund']){
                if(isset($resp['refunds']['refund']['tid'])){
                    $refunds[] = $resp['refunds']['refund'];
                }else{
                    $refunds = $resp['refunds']['refund'];
                }
                foreach($refunds as $v) {
                    //if(is_object($v)) $v = get_object_vars($v);
                    //echo $v['tid']."\n";
                    $id = $this->save_tb_refunds($v);
                    if(!$id){
                        kernel::ilog($v['tid'].':'.$id.' create failed.');
                        continue;
                    }else{
                        //echo($v['tid'].':'.$id.' success.<br/>');
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

    //保存到crm原来的退款表
    public function save_refunds($data){
        $order_id = 0;
        $oOrders = app::get('ecorder')->model('orders');
        $rs_order = $oOrders->dump(array('order_bn'=>$data['tid']), 'order_id');
        if($rs_order){
            $order_id = $rs_order['order_id'];
            $oOrders->update(
                array('pay_status'=>'5'), 
                array('order_id'=>$order_id)
            );
        }
    
        $sdf = array(
            'refund_bn' => $data['refund_id'],
            'account' => $data['seller_nick'],
            'pay_account' => $data['buyer_nick'],
            'currency' => 'RMB',
            'money' => $data['refund_fee'],
            'paycost' => 0,
            'cur_money' => $data['payment'],
            'pay_type' => 'online',
            'payment' => 0,
            'paymethod' => '支付宝',
            'download_time' => $data['created'],
            'status' => 'succ',
            'trade_no' => $data['tid'],
            'order_id' => $order_id,
            'shop_id' => $this->_shopInfo['shop_id'],
        );
        
        $oRefunds = app::get('ecorder')->model('refunds');
        $rs = $oRefunds->dump(array('refund_bn'=>$sdf['refund_bn']), 'refund_bn');
        if($rs){
            $oRefunds->update($sdf, array('refund_bn'=>$sdf['refund_bn']));
        }else{
            $oRefunds->insert($sdf);
        }
    }

    protected function save_tb_refunds($data){
        $sql = "select member_id from sdb_taocrm_members where uname='".$data['buyer_nick']."' ";
        $rs = kernel::database()->selectrow($sql);
        //var_dump($rs);
    
        if($rs){
            $data['member_id'] = $rs['member_id'];
        }else{
            $data['member_id'] = 0;
        }

        //转换标准格式sdf
        $sdf = $this->convertSdfParams($data);
        $oRefunds = app::get('ecorder')->model('tb_refunds');
        $rs = $oRefunds->dump(array('refund_id'=>$sdf['refund_id']), 'refund_id');
        if($rs){
            $oRefunds->update($sdf, array('refund_id'=>$sdf['refund_id']));
        }else{
            $oRefunds->insert($sdf);
        }
        
        if($sdf['status']=='SUCCESS') $this->save_refunds($sdf);
        
        return $sdf['refund_id'];
    }

    
    protected function convertSdfParams($data){
        $sdf = array(
            'refund_id' => $data['refund_id'],
            'tid' => $data['tid'],
         	'title' => $data['title'],
            'buyer_nick' => $data['buyer_nick'],
            'seller_nick' => $data['seller_nick'],
            'total_fee' => $data['total_fee'],
            'status' => $data['status'],
            'created' => strtotime($data['created']),
            'modified' => strtotime($data['modified']),
            'down_time' => date('Y-m-d H:i:s'),
            'refund_fee' => $data['refund_fee'],
            'oid' => $data['oid'],
            'good_status' => $data['good_status'],
            'company_name' => $data['company_name'],
            'sid' => $data['sid'],
            'member_id' => $data['member_id'],
            'payment' => $data['payment'],
            'reason' => $data['reason'],
            'desc' => (string)$data['desc'],
            'num' => $data['num'],
            'has_good_return' => $data['has_good_return'],
            'order_status' => $data['order_status'],
            'shop_id' => $this->_shopInfo['shop_id'],
        );

        return $sdf;
    }
}