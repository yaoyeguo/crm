<?php
/**
 * 同步优惠券
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class market_rpc_request_coupon extends market_rpc_request {

    public function add(& $coupon){
        $msg = 'fail';
        if($coupon['shop_id'] && $coupon['status']==1){
            $shopInfo = $this->fetchShopInfo($coupon['shop_id']);

            if($shopInfo['addon']['session']){
                $req = new ectools_top_request_PromotionCouponAddRequest();
                $req->setCondition($coupon['conditions']);
                $req->setDenominations($coupon['denominations']);
                $req->setEndTime(date('Y-m-d H:i:s',$coupon['end_time']));
                $req->setStartTime(date('Y-m-d H:i:s',$coupon['start_time']));
                $resp = $this->topClient->execute($req,$shopInfo['addon']['session']);

                if($resp->code || $resp->msg){
                    $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
                    if($resp->sub_code){
                        $msg .= '<br/>【sub_code】' . $resp->sub_code;
                    }
                    if($resp->sub_msg){
                        $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
                    }
                    /*if($resp->msg == 'Remote service error'){
                     $msg = ('timeout');
                     }*/
                }else if(!$resp){ // 超时错误
                    $msg = ('timeout');
                }else{
                    if($resp->coupon_id) {
                        $updateData = array(
                        'coupon_id'=>$coupon['coupon_id'],
                    	'outer_coupon_id'=>$resp->coupon_id,
                        'f_sync_coupon'=>'y',
                        'f_sync_coupon_msg'=>'',
                        );

                    }else{
                        $updateData = array(
                        'coupon_id'=>$coupon['coupon_id'],
                        'f_sync_coupon'=>'n',
                        'f_sync_coupon_msg'=>$msg,
                        );
                    }

                    $couponObj = &app::get('market')->model('coupons');
                    if($couponObj->save($updateData)){
                        $msg = 'success';
                        $coupon = array_merge($coupon,$updateData);
                    }else{
                        $msg = 'local db error';
                    }
                }
            }else{
                $msg = 'session no exist';
            }
        }

        return $msg;
    }

    /**
     * 同步店铺支付方式
     * @access public
     * @param int $shop_id 店铺ID
     * @return boolean
     */
    public function add_back($coupon){
        if($coupon['shop_id'] && $coupon['status']==1){
            if(!is_array($coupon['config'])){
                $coupon['config'] = unserialize($coupon['config']);
            }
            $params = array();
            $params['ext_coupon_id'] = $coupon['coupon_id'];
            $params['denominations'] = $coupon['config']['denominations'];
            $params['end_time'] = date('Y-m-d H:i:s',$coupon['end_time']);
            $params['condition'] = $coupon['config']['condition'] ? $coupon['config']['condition'] : 0;

            if($params['denominations'] && $coupon['end_time']>time()){
                $callback = array(
                    'class' => 'market_rpc_request_coupon',
                    'method' => 'add_callback',
                );

                $title = '添加优惠券('.$coupon['coupon_name'].')';
                $api_name = 'store.promotion.coupon.add';

                $this->request($api_name,$params,$callback,$title,$coupon['shop_id']);
            }
        }else{
            return false;
        }
    }

    function add_callback($result){
        $status = $result->get_status();
        if($status == 'succ'){
            $apiLogObj = &app::get('market')->model('api_log');
            $couponObj = &app::get('market')->model('coupons');
            $shopObj = &app::get(ORDER_APP)->model('shop');

            $callback_params = $result->get_callback_params();
            $log_id = $callback_params['log_id'];
            $apilog_detail = $apiLogObj->dump(array('log_id'=>$log_id), 'params');
            $apilog_detail = unserialize($apilog_detail['params']);

            $node_id = $apilog_detail[1]['to_node_id'];
            $coupon_id = $apilog_detail[1]['ext_coupon_id'];
            $shop = $shopObj->dump(array('node_id'=>$node_id));
            $shop_id = $shop['shop_id'];

            $data = $result->get_data();
            $coupon['coupon_bn'] = $data['coupon']['coupon_id'];
            if($coupon['coupon_bn']){
                $filter['shop_id'] = $shop_id;
                $filter['coupon_id'] = $coupon_id;
                $couponObj->update($coupon,$filter);
            }else{
                $msg = 'fail' . market_api_func::api_code2msg('re001', '', 'public');
                $apiLogObj->update_log($log_id, $msg, 'fail');
                return array('rsp'=>'fail', 'res'=>$msg, 'msg_id'=>$apilog_detail['msg_id']);
            }
        }
        return $this->callback($result);
    }

    public function send($data){
        $couponObj = &app::get('market')->model('coupons');
        $coupon = $couponObj->dump($data['coupon_id']);

        $memberObj = &app::get('market')->model('members');
        $member = $memberObj->dump($data['member_id']);

        if($coupon['shop_id'] && $coupon['coupon_bn'] && $member['account']['uname']){
            $params = array();
            $params['ext_sent_id'] = $data['sent_id'];
            $params['coupon_id'] = $coupon['coupon_bn'];
            $params['buyer_nick'] = $member['account']['uname'];

            if($coupon['end_time']>time()){
                $callback = array(
                    'class' => 'market_rpc_request_coupon',
                    'method' => 'send_callback',
                );

                $title = '发送优惠券('.$coupon['coupon_name'].'给'.$params['buyer_nick'].')';
                $api_name = 'store.promotion.coupon.send';

                $this->request($api_name,$params,$callback,$title,$coupon['shop_id']);
            }else{
                $sentObj = &app::get('market')->model('coupon_sent');
                $sent['coupon_status'] = '3';
                $filter['sent_id'] = $data['sent_id'];
                $sentObj->update($sent,$filter);
            }
        }else{
            return false;
        }
    }

    public function send_callback($result){
        $status = $result->get_status();
        if($status == 'succ'){
            $apiLogObj = &app::get('market')->model('api_log');
            $sentObj = &app::get('market')->model('coupon_sent');
            $shopObj = &app::get(ORDER_APP)->model('shop');

            $callback_params = $result->get_callback_params();
            $log_id = $callback_params['log_id'];
            $apilog_detail = $apiLogObj->dump(array('log_id'=>$log_id), 'params');
            $apilog_detail = unserialize($apilog_detail['params']);

            $node_id = $apilog_detail[1]['to_node_id'];
            $sent_id = $apilog_detail[1]['ext_sent_id'];

            $shop = $shopObj->dump(array('node_id'=>$node_id));
            $shop_id = $shop['shop_id'];

            $data = $result->get_data();
            if($data['is_success']=='true' && $data['coupon_results'][0]['coupon_number']){
                $sent['coupon_status'] = '1';
                $sent['coupon_number'] = $data['coupon_results'][0]['coupon_number'];
                $filter['sent_id'] = $sent_id;
                $sentObj->update($sent,$filter);
            }else{
                $msg = 'fail' . market_api_func::api_code2msg('re001', '', 'public');
                $apiLogObj->update_log($log_id, $msg, 'fail');
                return array('rsp'=>'fail', 'res'=>$msg, 'msg_id'=>$apilog_detail['msg_id']);
            }
        }
        return $this->callback($result);
    }

    public function getDetail($data){
        $couponObj = &app::get('market')->model('coupons');
        $coupon = $couponObj->dump($data['coupon_id']);

        $memberObj = &app::get('market')->model('members');
        $member = $memberObj->dump($data['member_id']);

        if($coupon['shop_id'] && $coupon['coupon_bn'] && $member['account']['uname']){
            $params = array();
            $params['ext_sent_id'] = $data['sent_id'];
            $params['ext_coupon_number'] = $data['coupon_number'];
            $params['coupon_id'] = $coupon['coupon_bn'];
            $params['buyer_nick'] = $member['account']['uname'];
            //$params['state'] = 'used';

            $callback = array(
                'class' => 'market_rpc_request_coupon',
                'method' => 'getDetail_callback',
            );

            $title = '查询优惠券('.$coupon['coupon_name'].'给'.$params['buyer_nick'].')';
            $api_name = 'store.promotion.coupondetail.get';

            $this->request($api_name,$params,$callback,$title,$coupon['shop_id']);
        }else{
            return false;
        }
    }

    public function getDetail_callback($result){
        $status = $result->get_status();
        if($status == 'succ'){
            $apiLogObj = &app::get('market')->model('api_log');
            $couponObj = &app::get('market')->model('coupons');
            $sentObj = &app::get('market')->model('coupon_sent');
            $shopObj = &app::get(ORDER_APP)->model('shop');

            $callback_params = $result->get_callback_params();
            $log_id = $callback_params['log_id'];
            $apilog_detail = $apiLogObj->dump(array('log_id'=>$log_id), 'params');
            $apilog_detail = unserialize($apilog_detail['params']);

            $node_id = $apilog_detail[1]['to_node_id'];
            $sent_id = $apilog_detail[1]['ext_sent_id'];
            $coupon_number = $apilog_detail[1]['ext_coupon_number'];

            $shop = $shopObj->dump(array('node_id'=>$node_id));
            $shop_id = $shop['shop_id'];

            $sendData = $sentObj->dump($sent_id);
            $coupon = $couponObj->dump($sendData['coupon_id']);
            $data = $result->get_data();
            if($coupon['end_time']<time()){
                foreach($data['coupon_details'] as $coupon_detail){
                    if($coupon_detail['coupon_number']==$coupon_number && ($coupon_detail['state']=='used' || $coupon_detail['state']=='using')){
                        $coupon_status = '2';
                    }
                }
                $sent['coupon_status'] = $coupon_status ? $coupon_status : '3';
                $filter['sent_id'] = $sent_id;
                $sentObj->update($sent,$filter);
            }else{
                foreach($data['coupon_details'] as $coupon_detail){
                    if($coupon_detail['coupon_number']==$coupon_number && ($coupon_detail['state']=='used' || $coupon_detail['state']=='using')){
                        $sent['coupon_status'] = '2';
                        $filter['sent_id'] = $sent_id;
                        $sentObj->update($sent,$filter);
                    }
                }
            }
        }
        return $this->callback($result);
    }
}