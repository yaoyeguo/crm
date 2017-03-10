<?php

class taocrm_service_shop{

	/**
	 * 对应店铺信息
	 * @var Array
	 */
	protected $_shopInfo = array();

	function __construct(){
		$this->app = app::get('taocrm');
	}

	//初始化店铺的客户分组和客户等级
	public function insert(&$data){

		return true;

		$aData['relate_table'] = 'ecorder_shop';
		$aData['relate_key'] = $data['shop_id'];
		$relateObj = &app::get('taocrm')->model("analysis_relate");
		$relate = $relateObj->dump(array('relate_table'=>$aData['relate_table'],'relate_key'=>$aData['relate_key']));
		if(!isset($relate['relate_id']) && $aData['relate_key']){
			$relateObj->insert($aData);
		}

		$shopLvObj = &app::get('ecorder')->model("shop_lv");
		$lvData = array(
            'name'=>'普通客户',
            'shop_id'=>$data['shop_id'],
            'dis_count'=>'1.00',
            'default_lv'=>'1',
            'lv_type'=>'retail',
            'point'=>'0',
            'experience'=>'0',
		);
		$shopLvObj->insert($lvData);

		$memGroupObj = &app::get('taocrm')->model("member_group");
		$groupData = array(
            'group_title'=>'订购顾客',
            'group_content'=>'订购顾客',
            'shop_id'=>$data['shop_id'],
            'group_posttime'=>time(),
            'query_condition'=>'a:4:{s:16:"order_count_than";s:4:"than";s:11:"order_count";s:1:"1";s:8:"group_id";s:1:"4";s:7:"shop_id";s:32:"05c03a15cd54b60dd26a15f63ab41355";}',
		);
		$memGroupObj->insert($groupData);
		return true;
	}

	public function delete($filter){
        return true;
        
		$shopLvObj = &app::get('ecorder')->model("shop_lv");
		$shopLvObj->delete(array('shop_id'=>$filter['shop_id']));

		$memGroupObj = &app::get('taocrm')->model("member_group");
		$memGroupObj->delete(array('shop_id'=>$filter['shop_id']));

		return true;
	}

	public function update($data){

		return true;

		$aData['relate_table'] = 'ecorder_shop';
		$aData['relate_key'] = $data['shop_id'];
		$relateObj = &app::get('taocrm')->model("analysis_relate");
		$relate = $relateObj->dump(array('relate_table'=>$aData['relate_table'],'relate_key'=>$aData['relate_key']));
		if(!isset($relate['relate_id']) && $aData['relate_key']){
			$relateObj->insert($aData);
		}
		return true;
	}

	/**
	 * 店铺统计
	 *
	 * @param $shopId
	 * @return bool
	 */
	public function countShopBuys($shopId) {
	    if(empty($shopId))return false;
        
		$base_filter = array('shop_id'=>$shopId);
		$members = $this->app->model('member_analysis')->count($base_filter);
		
		//单次购买客户数
        $base_filter['finish_orders'] = 1;
		$single_members = $this->app->model('member_analysis')->count($base_filter);
        
        //成功的订单数
        $sql = "select count(*) as nums from sdb_ecorder_orders where shop_id='$shopId' AND status='finish'  ";
        $finish_members = $this->app->model('member_analysis')->db->selectRow($sql);
        
        //未付款的客户数
        //$sql = "select count(*) as nums from sdb_ecorder_orders where shop_id='$shopId' AND pay_status='0' ";
        //去掉重复的客户
        $sql = "select count(distinct member_id) as nums from sdb_ecorder_orders where shop_id='$shopId' AND pay_status='0' ";
        $unpay_members = $this->app->model('member_analysis')->db->selectRow($sql);
        
        //退款的客户数
        //$sql = "select count(*) as nums from sdb_ecorder_orders where shop_id='$shopId' AND pay_status in ('4','5')";
        //去掉重复的客户
        $sql = "select count(distinct member_id) as nums from sdb_ecorder_orders where shop_id='$shopId' AND pay_status in ('4','5')";
        $refund_members = $this->app->model('member_analysis')->db->selectRow($sql);
        
        //付款订单总金额(新增)
        $sql = "select sum(total_amount) as pay_amount from sdb_ecorder_orders where shop_id='$shopId' AND pay_status = '1' ";
		$pay_amount = $this->app->model('member_analysis')->db->selectRow($sql);
		
		//付款订单总数(新增)
		$sql = "select count(*) as pay_orders from sdb_ecorder_orders where shop_id='$shopId' AND pay_status = '1' ";
		$pay_orders = $this->app->model('member_analysis')->db->selectRow($sql);
		
		$analysisData = $this->getShopAnalysis($shopId);
		$analysisData['shop_id'] = $shopId;
		$analysisData['members'] = $members;
		$analysisData['finish_members'] = $finish_members['nums'];
		$analysisData['unpay_members'] = $unpay_members['nums'];
		$analysisData['refund_members'] = $refund_members['nums'];
		
		$this->save_redis();

        if($finish_members['nums']) $analysisData['finish_per_amount'] = $analysisData['finish_amount']/$finish_members['nums'];
        if($unpay_members['nums']) $analysisData['unpay_per_amount'] = $analysisData['unpay_amount']/$unpay_members['nums'];
        if($refund_members['nums']) $analysisData['refund_per_amount'] = $analysisData['refund_amount']/$refund_members['nums'];
        if($analysisData['amount'])$analysisData['per_amount'] = $analysisData['amount']/$members;
		$analysisData['single_members'] = $single_members;
		//products	字段需要接口拉
		//付款订单数据
		$data['pay_amount'] = $pay_amount['pay_amount'];
		$data['pay_orders'] = $pay_orders['pay_orders'];
		app::get('ecorder')->model('shop_analysis')->save($analysisData);
		return $data;
        //return app::get('ecorder')->model('shop_analysis')->save($analysisData);
	}
	
    //下载完商品统计店铺宝贝数
	public function countShopProducts($shopId) {
	    $all_store = kernel::database()->selectrow('select count(*) as all_store from sdb_ecgoods_shop_goods where shop_id="'.$shopId.'"');
        $all_store = !empty($all_store['all_store']) ? intval($all_store['all_store']) : 0;
        $analysisData = array(
            'shop_id'=>$shopId,
            'products'=>$all_store,
        );
        app::get('ecorder')->model('shop_analysis')->save($analysisData);
	}

	/**
	 * 获取店铺统计数据
	 *
	 * @param void
	 * @return array
	 */
	protected function getShopAnalysis($shopId) {
		$sql = '
            select 
                sum(refund_amount) as refund_amount,
                sum(refund_orders) as refund_orders,
                sum(finish_total_amount) as finish_amount, 
                sum(finish_orders) as finish_orders,
                sum(finish_per_amount) as finish_per_amount, 
                sum(unpay_orders) as unpay_orders, 
                sum(unpay_amount) as unpay_amount, 
                sum(unpay_per_amount) as unpay_per_amount, 
                sum(total_orders) as orders, 
                sum(total_amount) as amount
			from sdb_taocrm_member_analysis 
			where shop_id="'.$shopId.'" ';
        $rs = &app::get('taocrm')->model('member_analysis')->db->selectRow($sql);
		return $rs;
	}
	
	//将店铺统计数据保存到redis
	public function save_redis(){
	
		$shops = kernel::database()->selectrow('select count(*) as total from sdb_ecorder_shop where node_id<>"" ');
		
		$data = kernel::database()->selectrow('select 
		sum(orders) as orders,sum(amount) as total_amount,sum(members) as members
		from sdb_ecorder_shop_analysis');
	
		$analysis = array();
		$analysis['shops'] = $shops['total'];
		$analysis['members'] = $data['members'];
		$analysis['orders'] = $data['orders'];
		$analysis['total_amount'] = $data['total_amount'];
		
		$analysis = json_encode($analysis);
		kernel::single('taocrm_service_redis')->redis->set($_SERVER['SERVER_NAME'].':ANALYSIS',$analysis);
	}

}