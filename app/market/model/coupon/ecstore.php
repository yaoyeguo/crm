<?php
class market_mdl_coupon_ecstore extends dbeav_model {

    var $defaultOrder = array('created',' DESC');
    var $no_recycle = true;


    function saveCoupon($shopId,$conponList){
        $rows = $this->db->select('select coupon_id from sdb_market_coupon_ecstore where shop_id = "'.$shopId.'" ');
        $couponIds = array();
        foreach($rows as $row){
            $couponIds[$row['coupon_id']] = 1;
        }

        if($conponList){
            foreach($conponList as $coupon){
                if($coupon['coupon_type'] == 'A'){
                    continue;
                }
                
                $row = $this->db->selectRow('select coupon_id from sdb_market_coupon_ecstore where shop_id = "'.$shopId.'" and ecstore_coupon_id='.$coupon['coupon_id']);
                $data = array(
                'coupon_name'=>$coupon['coupon_name'],
                'coupon_bn'=>$coupon['coupon_bn'],
                'coupon_type'=>$coupon['coupon_type'],
                'sync_last_time'=>time(),
                'start_time'=>$coupon['start_time'],
                'end_time'=>$coupon['end_time'],
                'description'=>$coupon['description'],
                'user_lv_id'=>$coupon['user_lv_id'],
                'coupon_status'=>$coupon['coupon_status'] == '1' ? 'y' : 'n',
                'is_del'=>'n',
                );

                if($row){
                    if(isset($couponIds[$row['coupon_id']])){
                        unset($couponIds[$row['coupon_id']]);
                    }

                    $data['coupon_id'] = $row['coupon_id'];
                }else{
                    $data['shop_id']= $shopId;
                    $data['ecstore_coupon_id'] = $coupon['coupon_id'];
                    $data['created'] = time();
                }
                $this->save($data);
            }
        }
        if(!empty($couponIds)){
            $this->db->exec('update sdb_market_coupon_ecstore set is_del="y" where coupon_id in('.implode(',', array_keys($couponIds)).')');
        }

        return array('total'=>count($conponList),'closeTotal'=>count($couponIds));
    }
}