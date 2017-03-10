<?php
class ecorder_mdl_member_products extends dbeav_model{
public function member_product($_POST){
		$product_obj = app::get('ecorder')->model('member_products');
		$product_array=array();
		$product = array();
		if (!empty($_POST['product'])){
			$product_array['goods_id|in']=$_POST['product'];
			$product_data=$product_obj->getList('member_id',$product_array);
			foreach ($product_data as $k=>$v){
				$product[]=$v['member_id'];
			}
				return $product;
		}else {
			$memberanaly_obj = &app::get('taocrm')->model('member_analysis');
			$shopid_filter['shop_id']=$_POST['shop_id'];
			$product_data=$memberanaly_obj->getList('member_id',$shopid_filter);
			$product=array();
			foreach ($product_data as $k=>$v){
				$product[]=$v['member_id'];
			}
			return $product;
		}
		
	}

}

