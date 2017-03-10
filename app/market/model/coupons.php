<?php
class market_mdl_coupons extends dbeav_model {

    var $defaultOrder = array('created',' DESC');
    var $no_recycle = true;

    function pre_recycle($data =NULL){
        $couponsObj = &$this->app->model('coupons');
        foreach ($data as $coupon){
            if($coupon['coupon_id']){
                $row = $couponsObj->dump($coupon['coupon_id']);
                if($row['f_sync_coupon'] == 'y' || $row['f_sync_activity'] == 'y'){
                    $this->recycle_msg = app::get('desktop')->_('已经同步到淘宝的优惠券不能删除('.$coupon['coupon_name'].')');
                    return false;
                }
            }
        }

        foreach ($data as $coupon){
            if($coupon['coupon_id']){
                kernel::database()->exec('delete from sdb_market_coupon_sent where coupon_id='.$coupon['coupon_id']);
                kernel::database()->exec('delete from sdb_market_coupon_used where coupon_id='.$coupon['coupon_id']);
            }
        }

        return true;
    }
}