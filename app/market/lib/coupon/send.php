<?php 
class market_coupon_send{
    
	public function coupon_send($params,&$msg){
		$coupons_array=unserialize($params);
		$shop_id=$coupons_array['coupon_tag']['shop_id'];
		$coupon_id=$coupons_array['coupon_tag']['coupon_id'];
		foreach ($coupons_array as $k=>$v){
			$buyer_nick[]=$v['buyer_nick'];
		}
		$coupon_obj=kernel::single("market_rpc_request_taobao_coupon");
		$rturn_mag=$coupon_obj->PromotionCouponSendRequest($shop_id,$coupon_id,$buyer_nick);
		if ($rturn_mag=='finish'){
			return true;
		}else {
			$msg=$rturn_mag;
			return false;
		}
	}
    
    public function ex_coupon_send($params,&$msg)
    {
		$shop_id = $params['shop_id'];
		$coupon_id = $params['coupon_id'];
		$buyer_nick[]=$params['buyer_nick'];
		$coupon_obj=kernel::single("market_rpc_request_taobao_coupon");
		$rturn_mag=$coupon_obj->PromotionCouponSendRequest($shop_id,$coupon_id,$buyer_nick);
		if ($rturn_mag=='finish'){
			return true;
		}else {
			$msg=$rturn_mag;
			return false;
		}
		
	}    
}




