<?php 
class ecorder_ctl_admin_download extends desktop_controller{

    function __construct($app)
    {
        parent::__construct($app);
        
        base_kvstore::instance('analysis')->fetch('manual_time',$manual_time);
        if($manual_time && (time() - $manual_time)<60*3){
            $this->forbidden = 'no';//禁止用户操作，yes
        }else{
            $this->forbidden = 'no';
            base_kvstore::instance('analysis')->store('manual_time','');
        }
    }

	public function goods()
    {
		$shop_data=$this->get_shops();
        
		$this->pagedata['shoplist']=$shop_data;
		$this->pagedata['forbidden']=$this->forbidden;
		$this->page("admin/download/goods.html");
	}
    
    //隐藏菜单，重新统计商品相关数据
    public function goods2()
    {
		$shop_data=$this->get_shops('all');
        
		$this->pagedata['shoplist']=$shop_data;
		$this->pagedata['forbidden']=$this->forbidden;
		$this->page("admin/download/goods2.html");
	}
    
    public function order()
    {
		$shop_data=$this->get_shops();
        
		$this->pagedata['date_from'] = date('Y-m-d',strtotime('-1 days'));
		$this->pagedata['date_to'] = date('Y-m-d');
		$this->pagedata['shoplist']=$shop_data;
        $this->pagedata['forbidden']=$this->forbidden;
        $this->pagedata['today'] = time();
		$this->page("admin/download/order.html");
	} 

	public function get_items()
    {
        set_time_limit(60);
        base_kvstore::instance('analysis')->store('manual_time',time());
        
		$shop_id = $_GET['shop_id'];
		$task = $_GET['task'];
		$page_no = intval($_GET['page_no']);
        
		$goods_obj=kernel::single('ecgoods_rpc_request_taobao_goods');
        
        //初始化参数
        $goods_obj->init_param($shop_id);
        
        //开始执行任务
		$res= $goods_obj->switchTask($task,$page_no,100);
        
        /*
        if($page_no==1 && $task == 'ItemsOnsaleGetRequest'){
            $sql = "delete from sdb_ecgoods_shop_goods where shop_id='$shop_id' ";
            kernel::database()->exec($sql);
        }
        */
        
        if($res == 'success'){
            $page_no++;
        }elseif($res == 'finish'){
            $page_no = 1;
            if($task == 'ItemsOnsaleGetRequest')
                $task = ('ItemsInventoryGetRequest');
            elseif($task == 'ItemsInventoryGetRequest')
                $task = ('ItemSkusGetRequest');
            elseif($task == 'ItemSkusGetRequest')
                $task = ('ItemcatsGetRequest');
            elseif($task == 'ItemcatsGetRequest')
                //$task = ('updateOrderInfo');
                $task = ('updateShopInfo');
            elseif($task == 'updateOrderInfo')
                $task = ('updateShopInfo');
            elseif($task == 'updateShopInfo')
                $task = 'finish';
        }else{
            $task = 'error:'.$res;
        }
        $arr = array('shop_id'=>$shop_id,'task'=>$task,'page_no'=>$page_no);
        echo(json_encode($arr));
        
        /*
        $sql = 'update sdb_ecorder_order_items as oi,sdb_ecgoods_shop_products as p
            set oi.goods_id=p.goods_id,oi.product_id=p.product_id 
            where p.outer_sku_id = oi.shop_product_id';
        if($task == 'ItemcatsGetRequest'){
            kernel::database()->exec($sql);
        }
        */
        
        //统计商品的销量(total_num)和销售金额(sale_money)
        $sql = 'update sdb_ecgoods_shop_goods as a,(
        select sum(nums) as num,sum(amount) as amount,goods_id 
        from sdb_ecorder_order_items where shop_id="'.$shop_id.'"
        group by goods_id
        ) as b set a.total_num=b.num,a.sale_money=b.amount
        where a.shop_id="'.$shop_id.'" and a.goods_id=b.goods_id
        ';
        if($task == 'finish'){
            kernel::database()->exec($sql);
            //$this->_add_lost_goods();//从订单补全丢失的商品信息
            
            kernel::single('ecorder_rpc_request_taobao_refunds')->download($shop_id);
        }
        
        base_kvstore::instance('analysis')->store('manual_time','');
	}
    
    public function get_order_tids()
    {
        base_kvstore::instance('analysis')->store('manual_time',time());
        
        $shop_id = $_POST['shop_id'];
		$date_from = strtotime($_POST['date_from']);
		$date_to = strtotime($_POST['date_to']);
        if(!$_POST['date_to']) $date_to = strtotime('+1 days',$date_from);
    
        $orderObj = new ectools_api_taobao_order();
        $shop = $this->get_shop_info($shop_id);
        if ($shop['addon']['session']) {
            $orderObj->setSessionKey($shop['addon']['session']);
            $orders = $orderObj->fetch($date_from, $date_to,false,$shop_id);
            base_kvstore::instance('cache')->store('order_down_'.$shop_id,json_encode($orders));
            echo(count($orders));
        }else{
            echo('error:请重新登录淘宝');
        }
        base_kvstore::instance('analysis')->store('manual_time','');
    }

    public function get_order_detail()
    {
        $page_size = 10;
        $page_no = intval($_POST['page_no']);//从0开始
        $shop_id = $_POST['shop_id'];
        
        $shop = $this->get_shop_info($shop_id);
        if (!$shop['addon']['session']) {
            echo('error:请重新登录淘宝');
            return false;
        }
        
        base_kvstore::instance('analysis')->store('manual_time',time());//防止重复运行
        base_kvstore::instance('cache')->fetch('order_down_'.$shop_id,$tid_str);//kv存储的订单号列表
        $tid_arr = json_decode($tid_str); 
        
        //开始循环
        for($i=$page_no;$i<($page_no+$page_size);$i++){
            if($i >= (sizeof($tid_arr))) break;
            $tid = $tid_arr[$i];
            $orderObj = new ectools_api_taobao_order();
            $orderObj->setSessionKey($shop['addon']['session']);
            $orderOme = $orderObj->getFullTrade($tid,$shop_id);
            if (!empty($orderOme)) {
                $result = $this->insertIntoOrder($orderOme, $shop['node_id']);
            }
        }
        
        if($i >= (sizeof($tid_arr) - 1)){
            base_kvstore::instance('analysis')->store('manual_time','');
        }
        echo($i);
    }
    
    private function insertIntoOrder($sdf, $nodeId)
    {
        static $orderObj = null;
        static $response = null;
        if (!$orderObj) {
            $orderObj = new ecorder_rpc_response_order();
            $response = kernel::single('base_rpc_service');
        }
        base_rpc_service::$node_id = $nodeId;
        return $orderObj->add($sdf, $response);
    }
    
    private function get_shops($type='taobao')
    {
        $sql = 'select shop_id,name from sdb_ecorder_shop';
        if('taobao' == $type) $sql .= ' where node_id is not null and node_type="taobao"';
        $rs = kernel::database()->select($sql);
        return $rs;
    }
    
    private function get_shop_info($shop_id)
    {
        $db = kernel::database();
        $rs = $db->select("SELECT * FROM sdb_ecorder_shop WHERE shop_id='$shop_id' AND  disabled='false' LIMIT 1");
        if(!$rs) return false;
        $rs[0]['addon'] = unserialize($rs[0]['addon']);
        $rs[0]['config'] = unserialize($rs[0]['config']);
        return $rs[0];
    }
    
    //重新关联订单明细的商品id
    public function update_item_goods()
    {
        set_time_limit(60*10);
        $page = intval($_GET['page']);
        $shop_id = trim($_GET['shop_id']);
        
        $page -- ;
        $page_size = 1;
        $offset = $page*$page_size;
        $db = kernel::database();
        
        //清空goods_id
        if($page == 0){
            $sql = "update `sdb_ecorder_order_items` set goods_id=0";
            //if($shop_id != '') $sql .= " where shop_id='$shop_id' ";
            $db->exec($sql);
        }
        
        $sql = "SELECT goods_id,outer_id,bn,name,shop_id FROM sdb_ecgoods_shop_goods ";
        //if($shop_id != '') $sql .= " where shop_id='$shop_id' ";
        $sql .= " LIMIT $offset,$page_size ";
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){
                //按淘宝商品编码关联
                /*
                if($v['outer_id'] && $v['outer_id']!='0'){
                    $sql = "update `sdb_ecorder_order_items` set goods_id = ".$v['goods_id']." WHERE goods_id=0 and shop_goods_id='".$v['outer_id']."' and shop_id='".$v['shop_id']."' ";
                    $db->exec($sql);
                }
                
                //按货号关联
                if($v['bn'] && $v['bn']!=''){
                    $sql = "update `sdb_ecorder_order_items` set goods_id = ".$v['goods_id']." WHERE goods_id=0 and bn='".$v['bn']."' and shop_id='".$v['shop_id']."' ";
                    $db->exec($sql);
                }
                */
                
                //按商品名称关联
                if($v['name'] && $v['name']!=''){
                    $v['name'] = str_replace("'","''",$v['name']);
                    $sql = "update `sdb_ecorder_order_items` set goods_id = ".$v['goods_id']." WHERE goods_id=0 and name='".$v['name']."' ";
                    //$sql .= " and shop_id='".$v['shop_id']."' ";
                    $db->exec($sql);
                }
            }
            die('succ');
        }else{
            die('finish');
        }
    }
    
    //重新计算客户积分，每付款一元积一分
    public function update_points()
    {
        set_time_limit(60*10);
        $page = intval($_GET['page']);
        $shop_id = trim($_GET['shop_id']);
        if($page == 0) $page = 1;
        $page -- ;
        $page_size = 1;
        $offset = $page*$page_size;
        $db = kernel::database();
        
        $sql = "SELECT id,member_id FROM sdb_taocrm_member_analysis ";
        if($shop_id != '') $sql .= " where shop_id='$shop_id' ";
        $sql .= " LIMIT $offset,$page_size ";
        //echo($sql);
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $v){                
                $member_id = $v['member_id'];
                $sql = "select sum(total_amount) as total_amount from sdb_ecorder_orders where member_id=$member_id and pay_status='1' and status in ('active','finish') ";
                if($shop_id != '') $sql .= " and shop_id='$shop_id' ";
                $rs_order = $db->selectRow($sql);
                //echo($sql);
                if($rs_order){
                    $points = intval($rs_order['total_amount']);
                }else{
                    $points = 0;
                }
                
                $sql = "update sdb_taocrm_member_analysis set points=$points where id=".$v['id'];
                //echo($sql);
                $db->exec($sql);
            }
            die('succ');
        }else{
            die('finish');
        }
    }
    
    //根据订单明细表创建新的商品
    public function create_item_goods()
    {
        set_time_limit(60*10);
        $page = intval($_GET['page']);
        $shop_id = trim($_GET['shop_id']);
        if($page == 0) $page = 1;
        $page -- ;
        $page_size = 1;
        $offset = $page*$page_size;
    
        $db = kernel::database();
        $sql = "SELECT name,shop_goods_id,bn,price,shop_id FROM sdb_ecorder_order_items WHERE goods_id=0 ";
        if($shop_id != '') $sql .= " and shop_id='$shop_id' ";
        //$sql .= " GROUP BY name ";
        $sql .= " LIMIT $page_size ";
        $rs = $db->select($sql);
        if($rs){
            $oShopGoods = app::get('ecgoods')->model('shop_goods');
            foreach($rs as $v){
                if($v['name']=='') continue;
                
                $name = $v['name'];
                $arr = array();
                $arr['outer_id'] = $v['shop_goods_id'];
                $arr['bn'] = $v['bn'];
                $arr['name'] = $v['name'];
                $arr['price'] = $v['price'];
                $arr['shop_id'] = $v['shop_id'];
                
                $arr['create_time'] = time();
                $arr['disabled'] = 'false';
                if($oShopGoods->insert($arr)){//创建商品
                    $goods_id = $arr['goods_id'];
                    $name = str_replace("'","''",$name);
                    $sql = "update sdb_ecorder_order_items set goods_id=$goods_id where goods_id=0 AND name='$name' ";
                    //if($shop_id != '') $sql .= " and shop_id='$shop_id' ";
                    //if($v['bn']!='') $sql .= "or bn='".$v['bn']."' ";
                    $db->exec($sql);
                }else{
                    die('system error....');
                }
                unset($arr);
            }
        }else{
            die('finish');
        }
    }
    
	//重新统计客户购买过的商品
    public function update_member_products()
    {	
        set_time_limit(60*30);
        $db = kernel::database();
        $sql = "truncate sdb_ecorder_member_products";
        $db->exec($sql); 
        
        $sql = "insert into sdb_ecorder_member_products (member_id,product_id,goods_id,name,last_time,buy_times,buy_num)
(select b.member_id,max(a.product_id),a.goods_id,(a.name),max(a.create_time)
,count(a.item_id),sum(a.nums) from sdb_ecorder_order_items as a
inner join sdb_ecorder_orders as b on a.order_id= b.order_id
group by a.goods_id,b.member_id
)";
        //$db->exec($sql); 
        
        /*
        $sql = 'update sdb_ecgoods_shop_goods as a,(
        select sum(nums) as num,sum(amount) as amount,goods_id 
        from sdb_ecorder_order_items group by goods_id
        ) as b set a.total_num=b.num,a.sale_money=b.amount
        where a.goods_id=b.goods_id
        ';
        $db->exec($sql);
        */
        
        $sql = 'update sdb_ecgoods_shop_goods set total_num=0,sale_money=0,last_modify='.time().' ';
        $db->exec($sql);
        
        //统计商品销售额
        $sql = 'update sdb_ecgoods_shop_goods as a,
        (
            select sum(aa.nums) as num,sum(aa.amount) as amount,aa.goods_id,bb.shop_id 
            from sdb_ecorder_order_items as aa 
            left join sdb_ecorder_orders as bb on aa.order_id=bb.order_id 
            where bb.pay_status="1" and aa.goods_id>0 
            group by aa.goods_id,bb.shop_id
        ) as b 
        set a.total_num=b.num,a.sale_money=b.amount,a.last_modify='.time().'
        where a.goods_id=b.goods_id and a.shop_id=b.shop_id
        ';
        $db->exec($sql);
        
        echo('finish');
        return true;
    }
    
    public function restore_active_assess(){
        $active_id = intval($_POST['active_id']);
        $db = kernel::database();
        
        $activity = $db->selectrow('select * from sdb_market_active where active_id='.$active_id);
        if(!$activity) die('error active id');
        
        $db->exec('delete from sdb_market_activity_m_queue where active_id = ' . $active_id);
        
        $shop = $db->selectrow('select name from sdb_ecorder_shop where shop_id="'.$activity['shop_id'].'"');
        $activity['shop_name'] = $shop['name'];
        if($activity){
            $objMemberGroup = app::get('taocrm')->model('member_group');
            if($activity['member_list']!='') {
                $member_list = unserialize($activity['member_list']);
                if(strstr($member_list[0],'group_id')){
                    // 1.自定义分组
                    $group_id = str_replace('group_id:','',$member_list[0]);
                    $sql = "SELECT filter FROM sdb_taocrm_member_group WHERE group_id=$group_id";
                    $rs = $db->selectrow($sql);
                    if($rs){
                        $sql = $objMemberGroup->gmBuildFilterSQL(unserialize($rs['filter']),$activity['shop_id'],$active_id);
                    }else{
                        $sql = false;
                    }
                }else{
                    // 2.直接勾选客户
                    if ($member_list == null) {
                        $market_user_id = kernel::single('desktop_user')->get_id();
                        base_kvstore::instance('analysis')->fetch('filter_member_' . $market_user_id, $membersList);
                        if ($membersList) {
                            $member_list = explode(',', $membersList);
                        }
                    }
                    $sql = "SELECT $active_id as active_id,member_id,uname,name as truename,mobile FROM sdb_taocrm_members WHERE member_id in (".implode(',',$member_list).")";
                }
            }elseif($activity['filter_sql']!=''){
                // 4.报表sql语句
                $sql = $activity['filter_sql'];
                $sql = "SELECT $active_id as active_id,a.member_id,a.uname,a.name as truename,a.mobile
                FROM sdb_taocrm_members as a
                inner join ($sql) as b on a.member_id=b.member_id";
            }else{
                // 3.自定义筛选条件
                $filter_mem = unserialize($activity['filter_mem']);
                $sql = $objMemberGroup->gmBuildFilterSQL($filter_mem['filter'],$activity['shop_id'],$active_id);
            }

            if($sql){
                //先清空活动之前的短信记录
                $db->exec('delete from sdb_market_activity_m_queue where active_id='.$active_id);
                $insertSql = 'INSERT INTO sdb_market_activity_m_queue(active_id,member_id,uname,truename,mobile) '.$sql;

                if(!$db->exec($insertSql)){
                    die($insertSql);
                }
                
                //die($insertSql);
            }
        }

        //无效1：删除空号码队列
        $db->exec('update sdb_market_activity_m_queue set is_send=0 where active_id='.$active_id.' and mobile =""');

        //无效2：黑名单客户不发送
        $db->exec('update sdb_market_activity_m_queue as a
        inner join  sdb_taocrm_members  as b on a.member_id=b.member_id 
         set a.is_send=0
        where b.sms_blacklist="true"');

        //无效3：过滤重复数据
        $mobile_re_rows = $db->select('select count(*) as total,mobile from sdb_market_activity_m_queue where active_id = '.$active_id.' group by mobile having total>1');
        if($mobile_re_rows){
            $ids = array();
            foreach($mobile_re_rows as $row){
                $mobile_info_rows = $db->select('select queue_id from sdb_market_activity_m_queue where active_id = '.$active_id .' and mobile="'.$row['mobile'].'"');
                foreach($mobile_info_rows as $k=>$moble_row){
                    if($k == 0)continue;
                    $ids[] = $moble_row['queue_id'];
                }
            }
            $db->exec('update sdb_market_activity_m_queue set is_send = 0 where queue_id in('.implode(',', $ids).')');
        }
        
        //清楚重复数据
        $sql = 'delete from sdb_market_active_member where active_id='.$active_id;
        $db->exec($sql);
        
        if ($activity['control_group']=='no'){
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,0,1,"","'.$activity['shop_id'].'",1 from sdb_market_activity_m_queue where active_id='.$active_id.' and is_send=1 ';
            $db->exec($sql);
            
            //die($sql);
            
            $is_control = 0;
        }elseif($activity['control_group']=='yes') {//开启对照组
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,1,1,"","'.$activity['shop_id'].'",1 from sdb_market_activity_m_queue where active_id='.$active_id.' and is_send=1 ';
            $db->exec($sql);
            
            $sql = 'INSERT INTO sdb_market_active_member select member_id,active_id,2,1,"","'.$activity['shop_id'].'",1 from sdb_market_activity_m_queue where active_id='.$active_id.' and is_send=1 ';
            $db->exec($sql);
            $is_control = 1;
        }
        
        die('finish');
    }
    
    //从淘宝订单接口获取退款数据
    public function get_refunds(){
        $shop_id = $_POST['shop_id'];
		$date_from = strtotime($_POST['date_from']);
		$date_to = strtotime($_POST['date_to']);
        $date_from = date('Y-m-d H:i:s', $date_from);
        $date_to = date('Y-m-d H:i:s', $date_to);
        
        kernel::single('ecorder_rpc_request_taobao_refunds')->download($shop_id, $date_from, $date_to);
        die('finish');
    }
    
    public function review_goods_bn()
    {
        $db = kernel::database();
        
        $sql = "select goods_id,name from sdb_ecgoods_shop_goods where isnull(bn) or bn='' ";
        $rs = $db->select($sql);
        if(! $rs) die('finish');
        foreach($rs as $v){
            $goods_id = $v['goods_id'];
            $name = $v['name'];
            $sql = "select bn from sdb_ecorder_order_items where goods_id=$goods_id and name='$name' and bn<>'' order by item_id desc ";
            $rs_order_items = $db->selectRow($sql);
            if($rs_order_items){
                $bn = $rs_order_items['bn'];
                $sql = "update sdb_ecgoods_shop_goods set bn='$bn' where goods_id=$goods_id ";
                $db->exec($sql);
            }
        }
        
        die('finish');
    }
}
