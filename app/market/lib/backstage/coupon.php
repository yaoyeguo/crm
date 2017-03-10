<?php

class market_backstage_coupon{

    // 发送优惠券
    function send($data){

        $db = kernel::database();

        $shop_id = $data['shop_id'];
        $coupon_id = $data['coupon_id'];
        $buyer_nick = $data['buyer_nick'];
        //$session = $data['session'];
        $this->order_id = $data['order_id'];
        $buyer_nick_count = is_array($buyer_nick) ? count($buyer_nick) : 1;
        $buyer_nick = is_array($buyer_nick) ? implode(',', $buyer_nick) : $buyer_nick;
        $coupon = $this->checkCoupon($coupon_id);//获取淘宝优惠券ID

        /*if(!$session) {
         $sentInfo = array(
         'coupon_id'=>$coupon_id,
         'reason' => '无效授权',
         'coupon_number'=>$v['coupon_number'],
         'buyer_nick'=> mb_substr($buyer_nick,0,10,'utf-8')."($buyer_nick_count)",
         'sent_status'=>'fail',
         'send_time'=>time(),
         'shop_id'=>$shop_id,
         );
         $this->_save_coupon_sent($sentInfo);
         return array('status'=>'fail','errmsg'=>'无效授权');
         }
         if(!$coupon) {
         $sentInfo = array(
         'coupon_id'=>$coupon_id,
         'reason' => '无效优惠券',
         'coupon_number'=>$v['coupon_number'],
         'buyer_nick'=> mb_substr($buyer_nick,0,10,'utf-8')."($buyer_nick_count)",
         'sent_status'=>'fail',
         'send_time'=>time(),
         'shop_id'=>$shop_id,
         );
         $this->_save_coupon_sent($sentInfo);
         return array('status'=>'fail','errmsg'=>'无效优惠券');
         }*/

        //发送优惠券
        $coupon_obj=kernel::single("market_rpc_request_taobao_coupon");
        $rturn_mag=$coupon_obj->PromotionCouponSendRequest($shop_id,$coupon_id,$buyer_nick);
        if ($rturn_mag=='finish'){
            $msg = array('status'=>'succ','errmsg'=>$resp);
        }else {
            $sentInfo = array(
                'coupon_id'=>$coupon_id,
                'reason' => $rturn_mag,
                'coupon_number'=>$buyer_nick_count,
                'buyer_nick'=> mb_substr($buyer_nick,0,10,'utf-8')."($buyer_nick_count)",
                'sent_status'=>'fail',
                'send_time'=>time(),
                'shop_id'=>$shop_id,
            );
            $this->_save_coupon_sent($sentInfo);
            $msg = array('status'=>'fail','errmsg'=>$rturn_mag);
        }

        return $msg;
    }

    // 保存优惠券发送日志
    private function _save_coupon_sent(&$sentData) {

        if (empty($sentData)) return false;
        $db = kernel::database();
        /*
         $sendId = null;
         $structs = app::get('market')->model('coupon_sent')->get_structs();
         $sentData = utils::structToArray($structs,$sentInfo);
         */

        $rs = $db->insert("sdb_market_coupon_sent",$sentData);
        $this->_save_order_status($sentData);
        return true;
    }

    // 更新订单状态
    private function _save_order_status(&$sentData){
        if(!$this->order_id) return true;

        $db = kernel::database();
        if($sentData['sent_status'] == 'succ') $arr['status'] = '发送成功';
        if($sentData['sent_status'] == 'fail') $arr['status'] = '发送失败';
        $arr['remark'] = $sentData['reason'];

        return $db->update("sdb_market_exchange_order",$arr,' order_id='.$this->order_id);
    }

    // 获取淘宝优惠券编号
    protected function checkCoupon($couponId){
        $db = kernel::database();
        $sql = 'select * from sdb_market_coupons where coupon_id = '.$couponId;
        $row = $db->selectrow($sql);
        if($row){
            return $row;
        }else{
            return false;
        }
    }
}
