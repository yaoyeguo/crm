<?php

class market_finder_coupon_ecstore {

    var $column_edit = '操作';
    var $column_edit_order = 1;
    var $column_edit_width = 150;

    var $addon_cols = "coupon_id,shop_id,is_del";
    function column_edit($row) {
        $find_id = $_GET['_finder']['finder_id'];
        $result = '';
        $couponId = $row[$this->col_prefix.'coupon_id'];
        $shopId = $row[$this->col_prefix.'shop_id'];
        $is_del = $row[$this->col_prefix.'is_del'];
        if($is_del == 'n'){
            $result = '<a href="index.php?app=market&ctl=admin_coupon_ecstore&act=sendCoupon&p[0]='.$shopId.'&p[1]='.$couponId.'&finder_id='.$find_id.'" target="dialog::{width:650,height:355,title:\'发送优惠券\'}">发送优惠券</a>';
        }
        return $result;
    }


    var $detail_sent = '发送情况';
    public function detail_sent($coupon_id){
       
        if(!$coupon_id) $coupon_id = $_GET['id'];
        
        
        $couponsObj = app::get('market')->model('coupon_ecstore');
        $couponInfo = $couponsObj->dump(array('coupon_id' => $coupon_id));
        $coupon_outer_id = $couponInfo['ecstore_coupon_id'];
        
        
        $render = app::get('market')->render();
        $pagelimit = 10;
        $page = $_GET['page'] ? $_GET['page'] : 1;
        $couponDetail = app::get('market')->model('coupon_ecstore_sendlog')->getPager(array('coupon_id'=>$coupon_outer_id),'*',$pagelimit * ($page - 1), $pagelimit);
        $status_hash =  array (
	        'succ' => '发送成功',
	        'fail' => '发送失败',
	        'unsend' => '未发送',
    		'sending' => '发送中',
	      );
        foreach($couponDetail['data'] as $k=>&$v){
            $couponDetail['data'][$k]['is_send'] = $status_hash[$v['is_send']];
        }
        $count = $couponDetail ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $render->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=market&ctl=admin_coupon_ecstore&act=index&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&action=detail&finderview=detail_sent&id='.$coupon_id.'&page=%d' ) );
        $render->pagedata['pager'] = $pager;
        $render->pagedata['couponDetail'] = $couponDetail['data'];
        return $render->fetch('admin/coupon/ecstore_sendlog.html');
    }

    /*var $detail_used = '使用情况';
    public function detail_used($coupon_id){
        $render = app::get('market')->render();
        $pagelimit = 20;
        $page = $page ? $page : 1;
        $couponDetail = app::get('market')->model('coupon_ecstore_used')->getPager(array('coupon_id'=>$coupon_id),'*',$pagelimit * ($page - 1), $pagelimit);
       
        $count = $couponDetail ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $render->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=market&ctl=admin_coupon&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_used&p[0]='.$coupon_id.'&page=%d' ) );
        $render->pagedata['pager'] = $pager;
        $render->pagedata['couponDetail'] = $couponDetail['data'];
        return $render->fetch('admin/coupon/ecstore_used.html');
    }*/
    
}
