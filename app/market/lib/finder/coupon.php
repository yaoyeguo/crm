<?php

class market_finder_coupon {

    var $column_edit = '操作';
    var $column_edit_order = 1;
    var $addon_cols = "f_sync_coupon,f_sync_activity,active_id,coupon_id,outer_coupon_id";
    function column_edit($row) {
        $find_id = $_GET['_finder']['finder_id'];
        $result = '';
        $coupon_id = $row[$this->col_prefix.'coupon_id'];
        if($row[$this->col_prefix . 'f_sync_coupon'] == 'n'){
            $result = '<a href="index.php?app=market&ctl=admin_coupon&act=requestCoupon&p[0]='.$coupon_id.'&finder_id='.$find_id.'" target="_blank">同步优惠券</a>';
        }else if($row[$this->col_prefix . 'f_sync_activity'] == 'n'){
            $result = '<a href="index.php?app=market&ctl=admin_coupon&act=requestActivity&p[0]='.$coupon_id.'&finder_id='.$find_id.'" target="_blank">同步活动</a>';
        }else{
            //$result = '<a href="index.php?app=market&ctl=admin_active&act=editer_data&p[0]='.$row[$this->col_prefix.'active_id'].'&p[1]=sel_member&p[2]=1&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('营销活动').'\', width:700, height:355}" >发送</a>';
            //$result = '<a href="index.php?app=market&ctl=admin_active&act=create_active&couponshop_id='.$row['shop_id'].'&cou_coupon_id='.$row['coupon_id'].'&coupon_send=1&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('营销活动').'\', width:700, height:355}" >发送</a>';
        }
        return $result;
    }
    
    var $column_orders = '使用订单数';
    var $column_orders_order = 75;
    function column_orders($row) {
        $find_id = $_GET['_finder']['finder_id'];
        $result = '0';
        $outer_coupon_id = $row[$this->col_prefix.'outer_coupon_id'];
        
        $sql = "select count(distinct order_id) as total from sdb_ecorder_order_pmt where coupon_id='$outer_coupon_id'";
        $rs = kernel::database()->selectrow($sql);
        $result = intval($rs['total']);
        
        return $result;
    }
    
    var $column_amount = '累计订单金额';
    var $column_amount_order = 76;
    function column_amount($row) {
        $find_id = $_GET['_finder']['finder_id'];
        $result = '0';
        $outer_coupon_id = $row[$this->col_prefix.'outer_coupon_id'];
        
        $sql = "select sum(payed) as total from sdb_ecorder_order_pmt as a
        left join sdb_ecorder_orders as b on a.order_id=b.order_id
        where a.coupon_id='$outer_coupon_id' ";
        $rs = kernel::database()->selectrow($sql);
        $result = intval($rs['total']);
        
        return $result;
    }

    var $detail_basic = '基本信息';
    public function detail_basic($coupon_id){
        $app = app::get('market');
        $render = $app->render();
        $coupon = $app->model('coupons')->dump($coupon_id);
        $shop = app::get('ecorder')->model('shop')->dump($coupon['shop_id']);
        $coupon['shop_name'] = $shop['name'];
        //echo '<pre>';var_dump($coupon);
        $render->pagedata['coupon'] = $coupon;
        return $render->fetch('admin/coupon/detail.html');
    }
    
    var $detail_exchange = '积分兑换';
    public function detail_exchange($coupon_id){
        $db = kernel::database();
        $app = &app::get('market');
        $render = $app->render();
        $coupon = $app->model('coupons')->dump($coupon_id);
        $sql = "select name from sdb_ecorder_shop where shop_id='".$coupon['shop_id']."' limit 1";
        $shops = $db->select($sql);
        
        $sql = "select * from sdb_market_exchange_items where relate_id='".$coupon_id."' and item_type='coupon' limit 1";
        $exchange = $db->select($sql);
        
        if(!$exchange[0]){
            $exchange[0] = array(
                'shop_id' => $coupon['shop_id'],
                'relate_id' => $coupon_id,
                'title' => $coupon['coupon_name'],
                'num' => $coupon['coupon_count'],
                'price' => 100,
                'max_buy_num' => 3,
                'item_type' => 'coupon',
                'is_active' => 0,
                'end_time' => date('Y-m-d',strtotime('+1 months'))
            );            
        }
        
        $render->pagedata['coupon'] = $coupon;
        $render->pagedata['shops'] = $shops[0];
        $render->pagedata['exchange'] = $exchange[0];
        //var_dump($coupon['shop_id']);
        return $render->fetch('admin/coupon/exchange.html');
    }

    var $detail_sent = '发送情况';
    public function detail_sent($coupon_id){
       
        if(!$coupon_id) $coupon_id = $_GET['id'];
        
        
        $couponsObj = app::get('market')->model('coupons');
        $couponInfo = $couponsObj->dump(array('coupon_id' => $coupon_id));
        $coupon_outer_id = $couponInfo['outer_coupon_id'];
        
        
        $render = app::get('market')->render();
        $pagelimit = 10;
        $page = $_GET['page'] ? $_GET['page'] : 1;
        $couponDetail = app::get('market')->model('coupon_sent')->getPager(array('coupon_id'=>$coupon_outer_id),'*',$pagelimit * ($page - 1), $pagelimit);
        $status_hash =  array(
        'succ' => '成功',
        'fail' => '失败',
        );
        foreach($couponDetail['data'] as $k=>&$v){
            $couponDetail['data'][$k]['channel'] = $channel_hash[$v['channel']];
            $couponDetail['data'][$k]['state'] = $state_hash[$v['state']];
            
            //处理买家昵称
            $reason = json_decode($v['reason'],true);
            //echo('<pre>');var_dump($reason['promotion_coupon_send_response']);
            //unset($v['reason']);
            
            if(isset($reason['promotion_coupon_send_response']['failure_buyers'])){
                $v['reason'] = '部分失败';
                $v['fail_detail_info'] = $reason['promotion_coupon_send_response']['failure_buyers']["error_message"];
                //echo('<pre>');var_dump($v['fail_detail_info']);
            }else{
                $v['reason'] = '全部成功';
            }
            
            $buyer_nick_arr = explode(',', $v['buyer_nick']);
            $couponDetail['data'][$k]['buyer_nick'] = $buyer_nick_arr[0].'、'.$buyer_nick_arr[1].'、'.$buyer_nick_arr[2].' 等，合计：'.sizeof($buyer_nick_arr).'人';
        }
        $count = $couponDetail ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $render->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=market&ctl=admin_coupon&act=index&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&action=detail&finderview=detail_sent&id='.$coupon_id.'&page=%d' ) );
        $render->pagedata['pager'] = $pager;
        $render->pagedata['couponDetail'] = $couponDetail['data'];
        return $render->fetch('admin/coupon/sent.html');
    }

    var $detail_used = '使用情况';
    public function detail_used($coupon_id){
        $render = app::get('market')->render();
        $pagelimit = 20;
        $page = $page ? $page : 1;
        $couponDetail = app::get('market')->model('coupon_used')->getPager(array('coupon_id'=>$coupon_id),'*',$pagelimit * ($page - 1), $pagelimit);
        $channel_hash =   array (
        'rewardforgifts' => '满就送',
        'marketingMessage' => '营销消息',
        'activityofget' => '活动领取',
        'createActivity' => '创建活动',
        'ISV' => 'ISV',
        'other' => '其他',
        );
        $state_hash =  array(
        'Unused' => '未使用',
        'using' => '使用中',
        'used' => '已使用',
        );
        foreach($couponDetail['data'] as $k=>$v){
            $couponDetail['data'][$k]['channel'] = $channel_hash[$v['channel']];
            $couponDetail['data'][$k]['state'] = $state_hash[$v['state']];
        }
        $count = $couponDetail ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $render->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=market&ctl=admin_coupon&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_used&p[0]='.$coupon_id.'&page=%d' ) );
        $render->pagedata['pager'] = $pager;
        $render->pagedata['couponDetail'] = $couponDetail['data'];
        return $render->fetch('admin/coupon/used.html');
    }
    
    var $detail_order = '订单明细';
    public function detail_order($id){
        if(!$id) $id = $_GET['id'];
        $render = app::get('market')->render();
        $db = kernel::database();
        $pagelimit = 10;
        
        $page = intval($_GET['page']);
        $page = $page ? $page : 1;
        //$couponDetail = app::get('ecorder')->model('orders')->getPager(array(),'*',$pagelimit * ($page - 1), $pagelimit);
        
        //淘宝优惠券id
        $sql = "select outer_coupon_id from sdb_market_coupons where coupon_id=$id ";
        $rs = $db->selectrow($sql);  
        $outer_coupon_id = $rs['outer_coupon_id'];
        
        //总数
        $sql = "select count(*) as total from sdb_ecorder_order_pmt where coupon_id='$outer_coupon_id'";
        $rs = $db->selectrow($sql);  
        $total = $rs['total'];
        $total_page = ceil ( $total / $pagelimit );
        
        //分页代码
        $sql = "select a.oid,b.order_bn,b.total_amount,b.item_num,b.createtime,a.pmt_amount,a.pmt_describe
        from sdb_ecorder_order_pmt as a left join sdb_ecorder_orders as b on a.order_id=b.order_id
        where a.coupon_id='$outer_coupon_id'
        limit ".($pagelimit * ($page - 1)).", $pagelimit ";
        $rs = $db->select($sql);
        foreach((array)$rs as $k=>$v){
            $rs[$k]['createtime'] = date('Y-m-d H:i:s',$rs[$k]['createtime']);
        }

        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=market&ctl=admin_coupon&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_order&id='.$id.'&page=%d' ));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['couponDetail'] = $rs;
        return $render->fetch('admin/coupon/orders.html');
    }

}
