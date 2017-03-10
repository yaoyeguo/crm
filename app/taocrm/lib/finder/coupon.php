<?php
class taocrm_finder_coupon{
    var $detail_basic = '优惠券信息';
    public function detail_basic($coupon_id){
        $app = app::get('taocrm');
        $render = $app->render();

        $couponObj = $app->model('coupons');
        $coupon = $couponObj->dump($coupon_id,'*');

        $shopObj = &app::get(ORDER_APP)->model('shop');
        $shop = $shopObj->dump($coupon['shop_id'],'*');
        $coupon['shop_id'] = $shop['name'];

        $render->pagedata['coupon'] = $coupon;

        return $render->fetch('admin/coupon/detail.html');
    }

    var $addon_cols = "coupon_bn";
    var $column_control = '操作';
    var $column_control_width = 50;
    var $column_control_order = COLUMN_IN_HEAD;
    function column_control($row){
        if($row['status'] && $row['status']==1){
            $button = '';
        }else{
            $button = '<a href="index.php?app=taocrm&ctl=admin_coupon&act=edit&p[0]='.$row['coupon_id'].'&finder_id='.$_GET['_finder']['finder_id'].'" target="_blank">'.'编辑'.'</a>';
        }
        return $button;
    }

    var $column_sync_status = '同步状态';
    var $column_sync_status_width = 130;
    var $column_sync_status_order = COLUMN_IN_TAIL;
    function column_sync_status($row){
        if($row[$this->col_prefix.'coupon_bn'] && $row[$this->col_prefix.'coupon_bn'] !=''){
            return '成功';
        }elseif($row['status'] && $row['status']==1){
            return '同步中';
        }
    }
}