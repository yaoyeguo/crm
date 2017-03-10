<?php
class ecorder_finder_orders {

	//taocrm_finder_middleware_member_analysis
	function __construct(){
		//$this->middleware = kernel::single('taocrm_finder_middleware_member_analysis');
	}
    
    var $column_level = "客户等级";
    var $column_level_width = 80;
    var $column_level_order = 22;
    function column_level($row)
    {
        $level_name = $row['level_name'] ? $row['level_name'] : '-';
        return $level_name;
    }
    
    var $column_tag = '标签';
    var $column_tag_width = 60;
    var $column_tag_order = 23;
    function column_tag($row)
    {
        $tagInfo = $row['tagInfo'];
        if($tagInfo){
            $tagInfo = '<img border=0 title="'.$tagInfo.'" align="absmiddle" src="'.app::get('taocrm')->res_url.'/teg_ico.png" >';
        }
        return $tagInfo;
	}
    var $column_logis = '物流';
    var $column_logis_width = 60;
    var $column_logis_order = 23;
    function column_logis($row)
    {
        $logi_obj = app::get('ecorder')->model('logi_info');
        $logi_data = $logi_obj->dump(array('order_id'=>trim($_GET['order_id'])));
        $act = " <a target='dialog::{width:700,height:200,title:\"物流信息\"}' href='index.php?app=taocrm&ctl=admin_member&act=getLogisticsInfo&order_id={$row['order_id']}'>物流信息</a>";
        return $act;
    }

	var $detail_order_items = '订单商品';
	public function detail_edit($id)
	{
		$pagelimit = 20;
		$page = $page ? $page : 1;
		$render = app::get('taocrm')->render();
		$orderItems = app::get('ecorder')->model('order_items')->getPager(array('order_id'=>$id),'name,price,nums,amount,evaluation,bn,`delete`',$pagelimit * ($page - 1), $pagelimit);
        //print_r($orderItems);
		$trade_rates = array('good'=>'好评','bad'=>'差评','neutral'=>'中评','unkown'=>'-');
		foreach($orderItems['data'] as $k=>$v){
			$orderItems['data'][$k]['evaluation'] = $trade_rates[$v['evaluation']];
			$orderItems['data'][$k]['pmt_amount'] = ($v['price'] * $v['nums']) -  $v['amount'];
		}

		$count = $orderItems ['count'];
		$total_page = ceil ( $count / $pagelimit );
		$pager = $render->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=taocrm&ctl=admin_member&act=getOrderInfo&p[0]='.$shop_id.'&p[1]='.$order_id.'&p[2]=%d' ) );
		$render->pagedata['pager'] = $pager;

		$render->pagedata['orderItems'] = $orderItems['data'];
		$render->display('admin/member/order_info.html');
	}

	/*var $detail_basic = '统计信息';
	 public function detail_basic($id)
	 {
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_basic($rs['member_id']);
	 }

	 var $detail_edit = '客户信息';
	 public function detail_edit($id)
	 {
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_edit($rs['member_id']);
	 }

	 var $detail_goods = '买过的商品';
	 function detail_goods($id){
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_goods($rs['member_id']);
	 }

	 var $detail_order = '历史订单';
	 function detail_order($id=null){
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_order($rs['member_id']);
	 }

	 var $detail_contact = '联 系 人';
	 function detail_contact($id=null){
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_contact($rs['member_id']);
	 }

	 var $detail_addr = '收货地址';
	 function detail_addr($id=null){
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_addr($rs['member_id']);
	 }

	 var $detail_active = '营销活动';
	 function detail_active($id){
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_active($rs['member_id']);
	 }

	 var $detail_points = '积分日志';
	 function detail_points($id){
	 $rs = $this->get_id($id);
	 $this->middleware->shop_id = $rs['shop_id'];
	 return $this->middleware->detail_points($rs['member_id']);
	 }

	 private function get_id($id)
	 {
	 $oOrders = &app::get('ecorder')->model('orders');
	 $oAnalysis = &app::get('taocrm')->model('member_analysis');
	 $rs = $oOrders->dump($id,'member_id, shop_id');

	 return $rs;
	 }*/
}