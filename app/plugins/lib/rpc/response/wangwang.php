<?php
/**
 * 前端店铺订单数据业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */

class plugins_rpc_response_wangwang extends plugins_rpc_response {
	
	var $unuse_words = array('补运费','补差价','不拍不送','邮费专拍','拍下无效');
	function __construct($app){
        parent::check_nick_authority();
    }
    
	//存储客户属性信息
	public function setMemProperty() {
		$seller_nick = $_POST['seller_nick'];
		$uname = $_POST['buyer_nick'];
    	if(!$seller_nick || !$uname){
    		echo  json_encode(array('res'=>'fail','data'=>'缺少参数'));
    		exit();
    	}
    	$shop_id = $this->check_nick($seller_nick);
    	
		$args = json_decode($_POST['prop_val'],true);
		if(empty($args)){
			echo json_encode(array('res'=>'fail','data'=>'No data need store.'));
			exit();
		}
		
		//$shop_id = $this->getShopId($seller_nick);
		
		/*
		$sql = "select count(*) cou from  sdb_taocrm_members where uname ='$uname'";
		$rs = kernel::database()->selectRow($sql);
		if($rs['cou'] < 1){
			echo json_encode(array('res'=>'fail','data'=>'buyer nick is not exist'));
			exit();
		}
		*/
		$memberObj = app::get('taocrm')->model('member_property');
		$memberObj -> delete(array('shop'=>$shop_id,'uname'=>$uname));
		
		foreach($args as $k=>$v){
			$data = array('shop_id'=>$shop_id,'uname'=>$uname,'property'=>$k,'value'=>$v);
			$memberObj->save($data);
			unset($data);
		}
		$this->setAppMembers($shop_id, $uname);
		echo json_encode(array('res'=>'succ','data'=>'data store success'));
	}
	
    //存储到应用客户表中
    public function setAppMembers($shop_id, $uname, $app_type = 'wwgenius')
    {
        $appMembersModel = app::get('taocrm')->model('app_members');
        $filter = array(
            'uname' => $uname,
            'shop' => $shop_id,
            'app_type' => $app_type
        );
        //查询客户名称是否存在
        $info = $appMembersModel->dump($filter);
        if ($info) {
            //客户已经存在
            if (!($info['member_id'] > 0)) {
                $member_id  = $this->getMembnerIdByUnameAndShopId($uname, $shop_id);
                if ($member_id > 0) {
                    $data = array('member_id' => $member_id);
                    $appMembersModel->update($data, $filter);
                }
            }
        }
        else {
            $member_id  = $this->getMembnerIdByUnameAndShopId($uname, $shop_id);
            $data = array(
                'shop_id' => $shop_id,
                'uname' => $uname,
                'member_id' => $member_id,
                'app_type' => $app_type,
            );
            $appMembersModel->insert($data);
        }
    }
    
    /**
     * 获得客户ID
     */
    public function getMembnerIdByUnameAndShopId($uname, $shop_id)
    {
        $sql = "SELECT `sdb_taocrm_members`.`member_id` FROM `sdb_taocrm_members` INNER JOIN `sdb_taocrm_member_analysis` ON `sdb_taocrm_members`.`member_id` = `sdb_taocrm_member_analysis`.`member_id` 
                WHERE `sdb_taocrm_members`.`uname` = '{$uname}' AND `sdb_taocrm_member_analysis`.`shop_id` = '{$shop_id}' 
                GROUP BY `sdb_taocrm_members`.`member_id` ASC";
        $rs = kernel::database()->select($sql);
        $member_id = 0;
        if ($rs) {
            $member_id = $rs[0]['member_id'];
        }
        return $member_id;
    }
	
	
	//获取客户属性 
	public function getMemProperty() {
		$seller_nick = $_POST['seller_nick'];
		if(!$seller_nick || !$_POST['buyer_nick']){
    		echo  json_encode(array('res'=>'fail','data'=>'缺少参数'));
    		exit();
    	}
    	$shop_id = $this->check_nick($seller_nick);
		
		//$shop_id = $this->getShopId($seller_nick);
		$memberObj = app::get('taocrm')->model('member_property');
		$list = $memberObj -> getList('property as name,value',array('shop_id'=>$shop_id,'uname'=>$_POST['buyer_nick']));
		if($list){
			echo json_encode(array('res'=>'succ','data'=>$list));
		}else{
			echo json_encode(array('res'=>'fail','data'=>''));
		}
	}
	
	//关联商品推荐
	public function getAssoProduct() {
	 	if(empty($_POST['date_from']) || empty($_POST['date_to'])){
        	echo json_encode(array('res'=>'fail','data'=>'start time or end time is empty.'));
        	exit();
        }
        $date_from = strtotime($_POST['date_from']);
        $date_to = strtotime($_POST['date_to']);
        
        if($date_from > $date_to){
        	echo json_encode(array('res'=>'fail','data'=>'start time greater than end time.'));
        	exit();
        }
		
		if(empty($_POST['goods_id'])){
			echo json_encode(array('res'=>'fail','data'=>'goods id is empty.'));
			exit();
		}
		$goods_id = $_POST['goods_id'];
		$seller_nick = $_POST['seller_nick'];
		
		$shop_id = $this->check_nick($seller_nick);
		
		//$shop_id = $this->getShopId($seller_nick);
		
		$sql = "select distinct goods_id from  sdb_ecgoods_shop_goods where outer_id = '$goods_id' limit 0,1";
        $rs = kernel::database()->select($sql);

		if(empty($rs)){
			echo json_encode(array('res'=>'fail','data'=>''));
			exit();
		}
		
        $filter['date_from'] = $date_from;
        $filter['date_to'] = $date_to;
        $filter['shop_id'] = $shop_id;
        $filter['goods_id'] = $rs[0]['goods_id'];
  
        $analysis_data = $this->get_goods_relation($filter);
		$pics = $this->get_pic($analysis_data);
		foreach($analysis_data as $k=>$v) {
            $analysis_data[$k]['pic_url'] = $pics[$v['goods_id']]['pic_url'];
        }
		if($analysis_data){
			echo json_encode(array('res'=>'succ','data'=>$analysis_data));
		}else{
			echo json_encode(array('res'=>'fail','data'=>''));
		}
	}
	
	//TOP20商品
	public function topTwenty(){
		
        if(empty($_POST['date_from']) || empty($_POST['date_to'])){
        	echo json_encode(array('res'=>'fail','data'=>'start time or end time is empty.'));
        	exit();
        }
        $date_from = strtotime($_POST['date_from']);
        $date_to = strtotime($_POST['date_to']);
        
        if($date_from > $date_to){
        	echo json_encode(array('res'=>'fail','data'=>'start time greater than end time.'));
        	exit();
        }
        
		$seller_nick = $_POST['seller_nick'];
		
		$shop_id = $this->check_nick($seller_nick);
		
		//$shop_id = $this->getShopId($seller_nick);
		$sql = "select * from sdb_ecgoods_shop_goods";
        $rs = kernel::database()->select($sql);
        foreach($rs as $v) {
            $goods[$v['goods_id']] = $v;
        }
		$sql = "select shop_goods_id,goods_id,price,bn,name,sum(nums) as nums from sdb_ecorder_order_items
        where shop_id='$shop_id' and (create_time between $date_from and $date_to) and goods_id>0 
        group by goods_id order by nums desc limit 0,20";
		$rs = kernel::database()->select($sql);
		$pics = $this->get_pic($rs);
		foreach($rs as $k=>$v) {
            $rs[$k]['store'] = $goods[$v['goods_id']]['store'];
            $rs[$k]['pic_url'] = $pics[$v['goods_id']]['pic_url'];
        }
        if($rs){
        	echo json_encode(array('res'=>'succ','data'=>$rs));
        }else{
        	echo json_encode(array('res'=>'fail','data'=>''));
        }
	}
	
	//获取客户等级
	function getMemberLv(){
		$seller_nick = $_POST['seller_nick'];
		$uname = $_POST['buyer_nick'];
    	if(!$seller_nick || !$uname){
    		echo  json_encode(array('res'=>'fail','data'=>'缺少参数'));
    		exit();
    	}
    	$shop_id = $this->check_nick($seller_nick);
    	$memberObj = app::get('taocrm')->model('members');
    	$memberArr = $memberObj -> dump(array('uname'=>$uname),'member_id');
    	if(!$memberArr){
    		echo  json_encode(array('res' => 'fail','data' => '客户不存在'));
			exit();
    	}
    	$analysisObj = app::get('taocrm')->model('member_analysis');
    	$memberLv = $analysisObj -> dump(array('member_id'=>$memberArr['member_id'],'shop_id'=>$shop_id,'lv_id'));
		if(!$memberArr){
    		echo  json_encode(array('res' => 'fail','data' => '客户不存在'));
			exit();
    	}
    	$lvObj = app::get('ecorder')->model('shop_lv');
    	$lvName = $lvObj -> dump(array('lv_id'=>$memberLv['lv_id']),'name');
    	if($lvName){
    		echo json_encode(array('res'=>'succ','data'=>$lvName));
			exit();
    	}else{
    		echo  json_encode(array('res' => 'fail','data' => '客户等级不存在'));
			exit();
    	}
	}
	
	//获取历史订单
	function getHistoryOrders(){
		
		$seller_nick = $_POST['seller_nick'];
		$uname = $_POST['buyer_nick'];
    	if(!$seller_nick || !$uname){
    		echo  json_encode(array('res'=>'fail','data'=>'缺少参数'));
    		exit();
    	}
    	$shop_id = $this->check_nick($seller_nick);
    	$memberObj = app::get('taocrm')->model('members');
    	//$memberArr = $memberObj -> dump(array('uname'=>$uname),'member_id');
    	$memberArr = $memberObj->getList('member_id',array('uname'=>$uname));
    	if(!$memberArr){
    		echo  json_encode(array('res' => 'fail','data' => '客户不存在'));
			exit();
    	}
		$members = array();
		//客户ID
		foreach($memberArr as $v){
			$members[] = $v['member_id'];
		}
    	$orderObj = app::get('ecorder')->model('orders');
    	//$orders = $orderObj->getList('*',array('member_id'=>$memberArr['member_id'],'shop_id'=>$shop_id));
    	$time = time() - 90 * 86400;
    	//订单信息
    	$orders = $orderObj->getList('order_id,order_bn,createtime,pay_time,discount,status,ship_name,ship_addr,ship_mobile,ship_tel,ship_zip,
    	ship_area,cost_freight,total_amount',array('member_id|in'=>$members,'shop_id'=>$shop_id,'createtime|sthan'=>$time));
    	
    	if(!$orders){
    		echo  json_encode(array('res' => 'fail','data' => '订单不存在'));
			exit();
    	}
    	$db = kernel::database();
    	//商品信息
    	foreach($orders as $k=>$v){
    		$sql = 'select tab1.name,tab1.shop_goods_id,tab1.price,sum(tab1.nums) as nums,tab1.addon,tab2.pic_url from sdb_ecorder_order_items as tab1,
    		 sdb_ecgoods_shop_goods as tab2 where tab1.goods_id=tab2.goods_id and tab1.order_id = '.$v['order_id'] .' group by tab1.goods_id';
    		$items = $db->select($sql);
    		$orders[$k]['items'] = $items;
    	}

    	echo json_encode(array('res'=>'succ','data'=>$orders));
	}
	
	
	private function getShopId($nick){
		//根据旺旺号获得shop_id
       	$sql = "select shop_id,addon from sdb_ecorder_shop where node_id is not null and node_id != ''";
		$shops = kernel::database()->select($sql);
		$shop_id = '';
    	foreach($shops as $v){
			$session = unserialize($v['addon']);
			if($nick == $session['nickname']){
				$shop_id = $v['shop_id'];
			}
		}
		if(empty($shop_id)){
			echo  json_encode(array('res' => 'fail','data' => 'seller nick is not exist'));
			exit();
		}else{
			return $shop_id;
		}
	}
	
	public function get_goods_relation($filter){
    
        $max_num = 10;//最多返回前10个分析结果
        $db = kernel::database();
        $goods_id = $filter['goods_id'];
  
        $goods_a_arr = kernel::single('market_backstage_report')->get_goods_relate($goods_id,$filter);
        $order_a = $goods_a_arr['orders'];//购买A商品的订单编号
		if(empty($order_a)){
			echo json_encode(array('res'=>'fail','data'=>''));
			exit();
		}

        $sql = "select shop_goods_id,goods_id,price,name,order_id ,price from sdb_ecorder_order_items 
            where order_id in (".implode(',',$order_a).")
            order by order_id";
        $rs = $db->select($sql);
		if(empty($rs)){
			echo json_encode(array('res'=>'fail','data'=>''));
			exit();
		}
		
        foreach($rs as $v){
            $unuse = 0;
            foreach($this->unuse_words as $vv){
                if(strstr($v['name'],$vv)){$unuse = 1;break;}
            }
            if($unuse == 1) continue;
        
            if($goods_id != $v['goods_id']) {
                $analysis_data[$v['goods_id']]['times'] += 1;
                $analysis_data[$v['goods_id']]['name'] = $v['name'];
                $analysis_data[$v['goods_id']]['shop_goods_id'] = $v['shop_goods_id'];
                $analysis_data[$v['goods_id']]['price'] = $v['price'];
                $analysis_data[$v['goods_id']]['goods_id'] = $v['goods_id'];
            }
        }
        //按购买次数排序
        kernel::single('taocrm_analysis_day')->array_sort($analysis_data,'times','desc');
        
		$i = 0;
        foreach($analysis_data as $k=>$v){
            if($i >= $max_num) {
                unset($analysis_data[$k]);
                continue;
            }
            $i++;
        }
       
        return $analysis_data;
    }
    
    //获取图片地址
    public function get_pic($rs){
    	$goods_ids = array();
		foreach($rs as $v){
			$goods_ids[] = $v['goods_id'];
		}
		$ids = implode($goods_ids, ',');
		$sql = "select goods_id,pic_url from  sdb_ecgoods_shop_goods where goods_id in". '('.$ids.')';
		$rs = kernel::database()->select($sql);
   		foreach($rs as $v) {
            $pic[$v['goods_id']] = $v;
        }
		return $pic;
    }
    
	private function check_nick($nick){
		//根据旺旺号获得shop_id
		$db = kernel::database();
       	$sql = "select shop_id from sdb_taocrm_app where seller_nick='".trim($nick)."'";
		$shops = $db->selectrow($sql);
		if(!$shops){
			echo  json_encode(array('res'=>'fail','data'=>'绑定关系不存在'));
    		exit();
		}
		
		$sql = "select node_id,shop_id from sdb_ecorder_shop where shop_id='".$shops['shop_id']."'";
		$shop_info = $db->selectrow($sql);
		
		if(!$shop_info){
			echo  json_encode(array('res'=>'fail','data'=>'此旺旺对应的店铺已删除，请重新添加'));
    		exit();
		}else if(!$shop_info['node_id']){
			echo  json_encode(array('res'=>'fail','data'=>'此旺旺对应的店铺未绑定，请重新绑定'));
    		exit();
		}
		return $shops['shop_id'];
		
	}
}


