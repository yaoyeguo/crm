<?php
/**
 * 支付方式同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class market_rpc_request_activity extends market_rpc_request {

    public function add(& $coupon){
        $msg = 'fail';
        if($coupon['shop_id'] && $coupon['status']==1){
            $shopInfo = $this->fetchShopInfo($coupon['shop_id']);
            $req = new ectools_top_request_PromotionActivityAddRequest();
            $req->setCouponCount($coupon['coupon_count']);
            $req->setCouponId($coupon['outer_coupon_id']);
            $req->setPersonLimitCount($coupon['person_limit_count']);
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
                if(is_object($resp->activity)) {
                    $activity = get_object_vars($resp->activity);
                    $updateData = array(
                        'coupon_id' => $coupon['coupon_id'],
                        'outer_activity_id'=>$activity['activity_id'],
                    	'outer_activity_url'=>$activity['activity_url'],
                        'f_sync_activity'=>'y',
                        'f_sync_coupon_msg'=>'',
                    );

                }else{
                    $updateData = array(
                        'coupon_id'=>$coupon['coupon_id'],
                        'f_sync_coupon'=>'n',
                        'f_sync_coupon_msg'=>$msg,
                    );
                }

                $activeObj = &app::get('market')->model('coupons');
                if($activeObj->save($updateData)){
                    $msg = 'success';
                    $coupon = array_merge($coupon,$updateData);
                }else{
                    $msg = 'local db error';
                }
            }

        }

        return $msg;
    }


}