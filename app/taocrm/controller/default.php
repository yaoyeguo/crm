<?php
class taocrm_ctl_default extends base_controller{
    
    function __construct($app){

        parent::__construct($app);
        $this->OAUTH = &kernel::single('taocrm_service_oauth');
        $this->account_info = $this->OAUTH->get_login_info();
        $tb_url = $this->OAUTH->get_tb_url();
        $this->pagedata['tb_url'] = $tb_url;
    }
    
    public function index(){
        
        if($_GET['state'] == 'logout'){
            $this->OAUTH->logout();
        }
        $this->OAUTH->login_from_tb();
        
        $db = kernel::database();
        
        $nick = $this->account_info['nick'];
        $user_id = $this->account_info['user_id'];
        
        //查询用户积分
        $member_id = array();
        $points = 0;
        $sql = "select member_id from sdb_taocrm_members where uname='$nick' ";
        $rs = $db->select($sql);
        if($rs) {
            foreach($rs as $v){
                $member_id[] = $v['member_id'];
            }
        }
        
        if($member_id){
            $sql = "select points,shop_id from sdb_taocrm_member_analysis where member_id in (".implode(',',$member_id).") ";
            $rs = $db->select($sql);
            foreach($rs as $v){
                $points += $v['points'];
                $shop_ids[] = $v['shop_id'];
            }
        }
    
        //优惠券列表
        /*
        if($member_id>0){
            $sql = 'select * from sdb_market_exchange_items where is_active=1 and shop_id in ("'.implode('","',$shop_ids).'") limit 16';
        }else{
            $sql = 'select * from sdb_market_exchange_items where is_active=1 limit 16';
        }*/
        
        $sql = "select a.* from sdb_market_exchange_items as a
            inner join sdb_market_coupons as b on a.relate_id=b.coupon_id
        where a.is_active=1 and b.end_time>".time()." and b.source='local' ";
        if($shop_ids){
            //$sql .= " and a.shop_id in ('".implode("','",$this->shop_ids)."') ";
        }
        $sql .= " group by a.relate_id ";
        
        $items = $db->select($sql);
        if(!$items){
            //header('location:index.php/admin/');die();            
        }
        //var_dump($rs);
        
        $sql = 'select shop_id,name from sdb_ecorder_shop';
        $rs = $db->select($sql);
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];            
        }

        $this->pagedata['items'] = $items;
        $this->pagedata['shops'] = $shops;
        $this->pagedata['nick'] = $nick;
        $this->pagedata['user_id'] = $user_id;
        $this->pagedata['points'] = $points;
        
        $this->display('site/header.html');
        $this->display('site/index.html');
        $this->display('site/footer.html');
    }
    
}
