<?php

class taocrm_ctl_site_exchange extends base_controller{
    
    var $page_size = 12;
    var $succ_info = '恭喜您，兑换成功！<br/> 优惠券稍后会发放到您的淘宝账户。<br/>请注意查收。';
    var $error_info = '非常抱歉，兑换失败！<br/> 由于网络原因优惠券没有发送成功！<br/>请稍后再试。';
    var $login_info = '您还没有登录！<br/> 请登录后再进行该操作！';
    
    public function __construct($app){
        
        parent::__construct($app);
        $this->OAUTH = &kernel::single('taocrm_service_oauth');
        $this->account_info = $this->OAUTH->get_login_info();
        $tb_url = $this->OAUTH->get_tb_url();
        
        $this->nick = $this->account_info['nick'];
        $this->user_id = $this->account_info['user_id'];
        
        //查询用户积分
        $db = kernel::database();
        $this->member_id = array();
        $this->points = 0;
        $sql = "select member_id from sdb_taocrm_members where uname='".$this->nick."' ";
        $rs = $db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $this->member_id[] = $v['member_id'];
            }
        }
        
        if($this->member_id){
            $sql = 'select points,shop_id from sdb_taocrm_member_analysis where member_id in ('.implode(',',$this->member_id).') ';
            $rs = $db->select($sql);
            foreach($rs as $v){
                $this->points += $v['points'];
                $this->shop_ids[] = $v['shop_id'];
            }
        }
        
        $this->pagedata['tb_url'] = $tb_url;
        $this->pagedata['user_id'] = $this->user_id;
        $this->pagedata['nick'] = $this->nick;
        $this->pagedata['points'] = $this->points;
    }
    
    // 展示兑换商品的详细信息
    public function index(){
        
        if($_POST){
            $this->exchange();
            die();
        }
        
        $item_id = intval($this->params['item_id']);
        if($item_id==0) return false;
        
        $oItems = &app::get('market')->model('exchange_items');
        $item = $oItems->dump($item_id);//var_dump($item);
        if($item['max_buy_num'] == 0) $item['max_buy_num'] = 99;
        $this->pagedata['item'] = $item;
        
        //展示其它优惠券
        $sql = "select a.item_id,a.title from sdb_market_exchange_items as a
            inner join sdb_market_coupons as b on a.relate_id=b.coupon_id
        where a.is_active=1 and b.end_time>".time()." and b.source='local' ";
        if($this->shop_ids){
            //$sql .= " and a.shop_id in ('".implode("','",$this->shop_ids)."') ";
        }
        $sql .= " group by a.relate_id ";
        //echo($sql);
        $items = $oItems->db->select($sql);
        $this->pagedata['items'] = $items;

        $this->display('site/header.html');
        $this->display('site/view.html');
        $this->display('site/footer.html');
    }
    
    public function exchange(){
        
        $member_id = $this->member_id;
        $buyer_nick = $this->nick;
        //echo('<pre>');var_dump($this->params);
        $item_id = intval($this->params['item_id']);
        $num = intval($_POST['num']);
        if($item_id==0) return false;
    
        $oOrder = &app::get('market')->model('exchange_order');
        $oItems = &app::get('market')->model('exchange_items');
        
        $item = $oItems->dump($item_id);//var_dump($item);
        $payment = $item['price']*$num;
        
        //判断用户积分余额
        if($payment > $this->points){
            die('积分余额不足。');
        }
        
        //保存订单信息
        $arr['member_id'] = $member_id[0];
        $arr['item_id'] = $item_id;
        $arr['num'] = $num;
        $arr['item_relate_id'] = $item['relate_id'];
        $arr['item_type'] = $item['item_type'];
        $arr['shop_id'] = $item['shop_id'];
        $arr['create_time'] = time();
        $arr['is_active'] = 1;
        $arr['payment'] = $payment;
        $arr['status'] = '发送中';
        $oOrder->insert($arr);
        $order_id = $arr['order_id'];
        unset($arr);
        
        //发送优惠券
        $result = array('type'=>'succ_info','msg'=>$this->succ_info);
        $data = array(
            'shop_id' => $item['shop_id'],
            'coupon_id' => $item['relate_id'],
            'buyer_nick' => $buyer_nick,
            'order_id' => $order_id,
        );
        
        /*
        $res = kernel::single('market_service_sms')->coupon_send_queue($data);
        if(!$res)
            $result = array('type'=>'error_info','msg'=>$this->error_info);
        
        if($result['type'] == 'succ_info'){            
            // 扣除积分
            $this->points_paid($member_id,$payment);
        }
        */
        
        $error_info = '';
        $res = kernel::single('market_coupon_send')->ex_coupon_send($data, $error_info);
        if(!$res){
            $result = array('type'=>'error_info','msg'=>$error_info);
            $status = '发送失败';
        }else{
            $this->points_paid($member_id,$payment);// 扣除积分
            $status = '发送完成'; 
        }
        
        //设置发送状态
        $sql = "update sdb_market_exchange_order set status='$status' where order_id=$order_id ";
        $oOrder->db->exec($sql);
        
        $this->pagedata['res'] = $result;
        $this->display('site/header.html');
        $this->display('site/succ.html');
        $this->display('site/footer.html');
    }
    
    private function points_paid(&$member_id,$payment){
        
        $oMemberAnalysis = $this->app->model('member_analysis');
        $rs = $oMemberAnalysis->getList('id,points,shop_id,member_id',array('member_id'=>$member_id));
        foreach($rs as $v){
            if($v['points'] == 0) continue;
            $cur_payment = $payment;
            if($v['points']<$payment) $cur_payment = $v['points'];
            $payment -= $cur_payment;
            
            $sql = 'update sdb_taocrm_member_analysis set points=points-'.$cur_payment.' WHERE id="'.$v['id'].'" ';
            kernel::database()->exec($sql);
            //var_dump($sql);

            $arr_log['shop_id'] = $v['shop_id'];
            $arr_log['member_id'] = $v['member_id'];
            $arr_log['op_time'] = time();
            $arr_log['op_user'] = 'system';
           // $arr_log['point_type'] = 'exchange';
            $arr_log['points'] = (-1)*$cur_payment;
            //var_dump($arr_log);die();
            $this->app->model('all_points_log')->insert($arr_log);
            unset($arr_log);
            
            if($payment<=0) break;
        }
        return true;
    }
    
    public function points(){
        
        if(!$this->nick) $this->show_error($this->login_info);
        
        $db = kernel::database();
        $rs = app::get('ecorder')->model('shop')->getList('*');
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $page = (isset($_GET['page']))?intval($_GET['page']):1;
        $page_size = $this->page_size;
        $rs = $db->selectrow('select count(*) as total_num from sdb_taocrm_all_points_log where member_id='.$this->member_id[0].' ');
        $total = $rs['total_num'];
        $sql = "select * from sdb_taocrm_all_points_log where member_id=".$this->member_id[0]." order by log_id desc limit ".($page-1)*$page_size.",$page_size";
        $point_logs = $db->select($sql);
        $schema = $this->app->model('all_points_log')->get_schema();
        
        $pager = $this->ui()->pager(array(
            'current'=>$page,
            'total'=>ceil($total/$page_size),
            'link'=>'?page=%d',
        ));
        //echo('<pre>');var_dump($point_logs);
        
        foreach($point_logs as $k=>$v){
           // $point_logs[$k]['point_type'] = $schema['columns']['point_type']['type'][$v['point_type']];
            $point_logs[$k]['shop_name'] = $shops[$v['shop_id']];         
            $point_logs[$k]['create_time'] = date('Y-m-d H:i:s',$v['op_time']);
        }

        $this->pagedata['point_logs'] = $point_logs;
        $this->pagedata['schema'] = $schema;
        $this->pagedata['pager'] = $pager;
        
        $this->display('site/header.html');
        $this->display('site/points.html');
        $this->display('site/footer.html');
    }
    
    public function orders(){
        
        if(!$this->nick) $this->show_error($this->login_info);
        
        $db = kernel::database();
        $oOrder = &app::get('market')->model('exchange_order');
        $oItems = &app::get('market')->model('exchange_items');
        
        $rs = $oItems->getList('*');
        foreach($rs as $v){
            $items[$v['item_id']] = $v['title'];
        }
        
        $rs = app::get('ecorder')->model('shop')->getList('*');
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $page = (isset($_GET['page']))?intval($_GET['page']):1;
        $page_size = $this->page_size;
        $total = $oOrder->count(array('member_id'=>$this->member_id[0]));//var_dump($total);
        $orders = $oOrder->getList('*',array('member_id'=>$this->member_id[0]),($page-1)*$page_size,$page_size,'order_id desc');
        $schema = $oOrder->get_schema();
        
        $pager = $this->ui()->pager(array(
            'current'=>$page,
            'total'=>ceil($total/$page_size),
            'link'=>'?page=%d',
            'nobutton'=>false,
        ));
        
        foreach($orders as $k=>$v){
            $orders[$k]['point_type'] = $schema['columns']['point_type']['type'][$v['point_type']];         
            $orders[$k]['shop_name'] = $shops[$v['shop_id']];         
            $orders[$k]['title'] = $items[$v['item_id']];         
            $orders[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);         
        }

        $this->pagedata['orders'] = $orders;
        $this->pagedata['schema'] = $schema;
        $this->pagedata['pager'] = $pager;
        
        $this->display('site/header.html');
        $this->display('site/orders.html');
        $this->display('site/footer.html');
    }
    
    private function show_error(&$msg){
        
        $res = array('type'=>'error_info','msg'=>$msg);
        $this->pagedata['res'] = $res;
        $this->display('site/header.html');
        $this->display('site/succ.html');
        $this->display('site/footer.html');
        die();
    }
}

