<?php

/**
 * 优惠券同步请求
 * @author sy
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class market_rpc_request_taobao_coupon extends market_rpc_request {

    protected $_shopInfo = array();

    protected $_rertyCount = 0;

    protected $_rertyMaxCount = 3;

    protected $_activityList = array();

    public function download($shopId=null){
        kernel::ilog(__CLASS__ . ' download start......');

        if($shopId){
            $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao" and shop_id="'.$shopId.'"');
        }else{
            $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao"');
        }
        foreach($shopList as $shop){
            $addon = unserialize($shop['addon']);
            if($addon && !empty($addon['session'])){
                kernel::ilog($shop['name'] . ' start......');
                $this->_shopInfo = array('channel_id'=>$shop['channel_id'],'shop_id'=>$shop['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname'],'name'=>$shop['name']);
                $this->getAll('PromotionActivityGetRequest');
                kernel::ilog($shop['name'] . ' end......');
            }
        }


        kernel::ilog(__CLASS__ . ' download end......');
    }


    protected function getAll($task,$pageNo=1){
        //echo $pageNo."\n";
        kernel::database()->dbclose();
        $result = $this->switchTask($task,$pageNo);

        if($result == 'timeout'){
            kernel::ilog($task . '-'. $pageNo . ' is ' . $result);
            kernel::ilog('sleep 3 sec...');
            sleep(3);
            //echo '$this->_rertyCount:'.$this->_rertyCount."\n";
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
            if($task == 'PromotionActivityGetRequest'){
                $this->getAll('PromotionCouponsGetRequest');
            }

            if($task == 'PromotionCouponsGetRequest'){
                $this->getAll('PromotionCoupondetailGetRequest');
            }
        }else{
            kernel::ilog($result);
        }
    }

    protected function switchTask($task,$pageNo=1,$pageSize=100){
        return $this->{$task}($pageNo,$pageSize);
    }

    protected function PromotionActivityGetRequest($pageNo,$pageSize){
        $msg = '';
        $req = new ectools_top_request_PromotionActivityGetRequest();
        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
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
            //$total_results = $resp->total_results;
            // 循环插入数据
            if($resp->activitys->activity) {
                foreach($resp->activitys->activity as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $this->_activityList[$v['coupon_id']] = $v;
                }
            }
            $msg = 'finish';
        }

        return $msg;
    }

    protected function PromotionCouponsGetRequest($pageNo,$pageSize){
        $pageSize = 40;
        //echo '$pageNo:'.$pageNo."\n";
        $msg = '';
        $req = new ectools_top_request_PromotionCouponsGetRequest();
        $req->setPageNo($pageNo);
        $req->setPageSize($pageSize);
        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
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
            if($resp->coupons->coupon) {
                foreach($resp->coupons->coupon as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    //echo $v['coupon_id']."\n";
                    $couponId = $this->processCoupon($v);
                    if(!$couponId){
                        kernel::ilog($v['coupon_id'] . ' coupon create failed.');
                        continue;
                    }
                }
            }
            $msg = 'success';
        }

        if($pageSize*$pageNo >= $total_results){
            $msg = 'finish';
        }

        kernel::ilog('coupon pageSize: '.$pageSize.' is sleep 3 sec...');
        sleep(3);

        return $msg;
    }

    protected function PromotionCoupondetailGetRequest($pageNo,$pageSize){
        $pageNo -= 1;
        $coupon = app::get('market')->model('coupons')->getList('coupon_id,outer_coupon_id,end_time',array('status'=>1),$pageNo,1,'coupon_id');
        if(!$coupon){
            return 'finish';
        }
        $coupon = $coupon[0];

        //echo $coupon['coupon_id']."\n";

        $msg = '';
        $req = new ectools_top_request_PromotionCoupondetailGetRequest();
        //$req->setState('used');
        $req->setCouponId($coupon['outer_coupon_id']);
        $req->setEndTime(date('Y-m-d H:i:s', $coupon['end_time']));
        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
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
            $used_num = 0;
            $applied_count = 0;
            if($resp->coupon_details->coupon_detail) {
                foreach($resp->coupon_details->coupon_detail as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    //echo $v['coupon_id']."\n";
                    $v['coupon_id'] = $coupon['coupon_id'];
                    $couponId = $this->processCouponDetail($v);
                    if(!$couponId){
                        kernel::ilog($v['coupon_id'] . ' coupon detail create failed.');
                        continue;
                    }
                    if($v['state'] == 'used'){
                        $used_num++;
                    }
                    $applied_count++;
                }
                //更新优惠券使用数
                if($used_num){
                    $this->updateCoupon(array('coupon_id'=>$coupon['coupon_id'],'used_num'=>$used_num));
                }
                //更新优惠券领用
                if($applied_count){
                    $this->updateCoupon(array('coupon_id'=>$coupon['coupon_id'],'applied_count'=>$applied_count));
                }

            }
            $msg = 'success';
        }
        if($pageNo % 5 == 0){
            kernel::ilog('coupon detail % 5 is sleep 3 sec...');
            sleep(3);
        }
        return $msg;
    }


    /**
     *
     * 发送优惠券
     *
     * @param unknown_type $couponId
     * @param unknown_type $buyer_nick
     */
    public function PromotionCouponSendRequest($shop_id,$couponId,$buyer_nick){

        $msg = '';
        $shopInfo = $this->fetchShopInfo($shop_id);
        if(!$shopInfo['addon'] || empty($shopInfo['addon']['session'])){
            return 'session no exist';
        }
        $coupon = $this->checkCoupon($couponId);
        if(!$coupon){
            return 'coupon no exist';
        }
        $buyer_nick = is_array($buyer_nick) ? implode(',', $buyer_nick) : $buyer_nick;
        $req = new ectools_top_request_PromotionCouponSendRequest();
        $req->setBuyerNick($buyer_nick);
        $req->setCouponId($coupon['outer_coupon_id']);
        $resp = $this->topClient->execute($req,$shopInfo['addon']['session']);
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
            $used_num = 0;
            $send_time = time();
            if($resp->coupon_results->coupon_result) {
                foreach($resp->coupon_results->coupon_result as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $sentInfo = array('coupon_id'=>$couponId,
                        'coupon_number'=>$v['coupon_number'],
                        'buyer_nick'=> $v['buyer_nick'],
                        'sent_status'=>'succ',
                        'send_time'=>$send_time,
                        'shop_id'=>$shop_id,
                    );
                    kernel::single('market_service_coupon')->saveCouponSent($sentInfo);
                }
            }

            if($resp->failure_buyers->error_message) {
                foreach($resp->failure_buyers->error_message as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $sentInfo = array('coupon_id'=>$couponId,
                        'reason'=>$v['reason'],
                        'buyer_nick'=> $v['buyer_nick'],
                        'sent_status'=>'fail',
                        'send_time'=>$send_time,
                    	'shop_id'=>$shop_id,
                    );
                    kernel::single('market_service_coupon')->saveCouponSent($sentInfo);
                }
            }
            $msg = 'finish';
        }
        return $msg;
    }

    protected function processCouponDetail($coupon){
        $c = $this->checkCouponDetail($coupon['coupon_id'],$coupon['coupon_number']);
        if($c){
            $coupon['id'] = $c['id'];
            return $this->updateCouponDetail($coupon);
        }else{
            return $this->createCouponDetail($coupon);
        }
    }

    protected function createCouponDetail($coupon){

        $coupon_id = kernel::single('market_service_coupon')->saveCouponDetail($coupon);
        if(!$coupon_id){
            kernel::ilog($coupon['coupon_id'] . ' detail create failed.');
            return false;
        }

        return $coupon_id;
    }

    protected function checkCouponDetail($couponId,$coupon_number){
        $row = kernel::database()->selectrow('select * from sdb_market_coupon_used where coupon_id = '.$couponId .' and coupon_number='.$coupon_number);
        if($row){
            return $row;
        }else{
            return false;
        }
    }

    protected function updateCouponDetail($couponInfo){

        return kernel::single('market_service_coupon')->saveCouponDetail($couponInfo);
    }

    protected function processCoupon($coupon){
        if(!isset($this->_activityList[$coupon['coupon_id']])){
            return false;
        }

        $coupon['creat_time'] = strtotime($coupon['creat_time']);
        $coupon['end_time'] = strtotime($coupon['end_time']);
        $coupon['denominations'] = $coupon['denominations']/100;
        $coupon['condition'] = $coupon['condition']/100;
        //$coupon['applied_count'] = $this->_activityList[$coupon['coupon_id']]['applied_count'];

        $c = $this->checkCouponByOuterCouponId($coupon['coupon_id']);
        if($c){
			//err_log('更新');err_log($c);err_log($coupon);
			
			//修正数据
			$coupon['conditions'] = $coupon['condition'];
			$coupon['coupon_id'] = $c['coupon_id'];
			$coupon['created'] = $coupon['creat_time'];
			
            return $this->updateCoupon($coupon);
        }else{
            if($this->_activityList[$coupon['coupon_id']]['status'] == 'enabled'){
                return $this->createCoupon($coupon);
            }else{
                kernel::ilog($this->_activityList[$coupon['coupon_id']]['activity_id'] . ' activity is '.$this->_activityList[$coupon['coupon_id']]['status']);
                return false;
            }
        }

    }

    protected function updateCoupon($couponInfo){
        $activity = $this->_activityList[$couponInfo['coupon_id']];
        $couponInfo['coupon_count'] = $activity['total_count'];
        $couponInfo['shop_id'] = $this->_shopInfo['shop_id'];
        
        return kernel::single('market_service_coupon')->saveCoupon($couponInfo);
    }

    protected function createCoupon($coupon){
        $activity = $this->_activityList[$coupon['coupon_id']];
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shop = $shopObj->dump($this->_shopInfo['shop_id']);
        if($coupon['condition'] && $coupon['condition']>0){
            $coupon['coupon_name'] = $this->_shopInfo['name'].'满'.$coupon['condition'].'元减'.$coupon['denominations'].'元优惠券活动('.date("Y-m-d H:i",$coupon['end_time']).')';
        }else{
            $coupon['coupon_name'] = $this->_shopInfo['name'].$coupon['denominations'].'元优惠券活动('.date("Y-m-d H:i",$coupon['end_time']).')';
        }
        /*$activityInfo = array(
         'active_name' => $coupon['coupon_name'],
         'shop_id' => $this->_shopInfo['shop_id'],
         'type' => 'coupon',
         'is_activity' => 'sel_member',
         'create_time' => $coupon['creat_time'],
         'end_time' => $coupon['end_time'],
         );

         $active_id = kernel::single('market_service_activity')->saveActivity($activityInfo);
         if(!$active_id){
         kernel::ilog($activityInfo['activity_id'] . ' activity create failed.');
         return false;
         }*/

        $couponInfo = array(
            'outer_coupon_id' => $coupon['coupon_id'],
            'active_id' => $active_id,
            'outer_activity_id' => $activity['activity_id'],
            'outer_activity_url' => $activity['activity_url'],
            'coupon_name' => $coupon['coupon_name'],
            'shop_id' => $this->_shopInfo['shop_id'],
            'status' => 1,
			//'created' => $coupon['creat_time'],
			//'updated' => time(),
            'end_time' => $coupon['end_time'],
            'denominations' => $coupon['denominations'],
            'conditions' => $coupon['condition'],
            'coupon_count' => $activity['total_count'],
            'person_limit_count' => $activity['person_limit_count'],
            'applied_count' => $activity['applied_count'],
            'f_sync_coupon' => 1,
            'f_sync_activity' => 1,
            'source' => 'taobao',
        );

        return kernel::single('market_service_coupon')->saveCoupon($couponInfo);
    }

    protected function checkCoupon($couponId){
        $row = kernel::database()->selectrow('select * from sdb_market_coupons where coupon_id = '.$couponId);
        if($row){
            return $row;
        }else{
            return false;
        }
    }

    protected function checkCouponByOuterCouponId($couponId){
        $row = kernel::database()->selectrow('select * from sdb_market_coupons where outer_coupon_id = '.$couponId);
        if($row){
            return $row;
        }else{
            return false;
        }
    }

     
}