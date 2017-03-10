<?php
class taocrm_finder_member_report {

    function __construct(){
        $this->oMembers = &app::get('taocrm')->model('members');
    }

    private function getMemberInfo($id)
    {
        $this->member_id = $id;
    }

    var $detail_basic = '统计信息';
    public function detail_basic($id)
    {
        $this->getMemberInfo($id);
        $app = app::get('taocrm');

        $analysis = array(
            'total_orders' => 0,
            'total_amount' => 0,
            'total_per_amount' => 0,
            'points' => 0,
            'lv_id' => '',//普通会员
            'refund_orders' => 0,
            'refund_amount' => 0,
            'finish_orders' => 0,
            'finish_total_amount' => 0,
            'finish_per_amount' => 0,
            'unpay_orders' => 0,
            'unpay_amount' => 0,
            'unpay_per_amount' => 0,
            'buy_freq' => 0,
            'buy_month' => 0,
            'buy_skus' => 0,
            'buy_products' => 0,
            'avg_buy_skus' => 0,
            'avg_buy_products' => 0,
            'first_buy_time' => 0,
            'last_buy_time' => 0,
            'shop_evaluation' => '',
            'is_vip' => '否',
        );
        
        $db = kernel::database();
        $order_ids = array();
        $buy_month = array();
        $sql = "select order_id,total_amount,pay_status,status,createtime,payed,item_num,skus from sdb_ecorder_orders where member_id=".$this->member_id." ";
        $rs_orders = $db->select($sql);
        if($rs_orders){
            foreach($rs_orders as $v){
                if($analysis['first_buy_time']==0) $analysis['first_buy_time']=$v['createtime'];
                if($analysis['last_buy_time']==0) $analysis['last_buy_time']=$v['createtime'];
            
                $order_ids[] = $v['order_id'];
                $buy_month[date('Ym', $v['createtime'])] = 1;
                $analysis['total_orders'] ++;
                $analysis['buy_products'] += $v['item_num'];
                $analysis['buy_skus'] += $v['skus'];
                $analysis['total_amount'] += $v['total_amount'];
                $analysis['first_buy_time'] = min($analysis['first_buy_time'],$v['createtime']);
                $analysis['last_buy_time'] = max($analysis['last_buy_time'],$v['createtime']);
                
                if($v['status']=='finish'){
                    $analysis['finish_orders'] ++;
                    $analysis['finish_total_amount'] += $v['total_amount'];
                }
                
                if($v['pay_status']==5){
                    $analysis['refund_orders'] ++;
                    $analysis['refund_amount'] += $v['total_amount'];
                }elseif($v['pay_status']==0){
                    $analysis['unpay_orders'] ++;
                    $analysis['unpay_amount'] += $v['total_amount'];
                }
            }
            
            $sql = "select count(*) as buy_skus from sdb_ecorder_order_items where order_id in (".implode(',', $order_ids).") group by goods_id ";
            $rs_order_items = $db->selectRow($sql);
            $analysis['buy_skus'] = $rs_order_items['buy_skus'];
            //echo($sql);
            
            $analysis['buy_freq'] = round(($analysis['last_buy_time'] - $analysis['first_buy_time'])/($analysis['total_orders']*86400), 2);
            $analysis['total_per_amount'] = round($analysis['total_amount']/$analysis['total_orders'], 2);
            $analysis['finish_per_amount'] = round($analysis['finish_total_amount']/$analysis['finish_orders'], 2);
            $analysis['unpay_per_amount'] = round($analysis['unpay_amount']/$analysis['unpay_orders'], 2);
            $analysis['avg_buy_skus'] = round($analysis['buy_skus']/$analysis['total_orders'], 2);
            $analysis['avg_buy_products'] = round($analysis['buy_products']/$analysis['total_orders'], 2);
            $analysis['buy_month'] = count($buy_month);
        $analysis['first_buy_time'] = date('Y-m-d H:i:s',$analysis['first_buy_time']);
        $analysis['last_buy_time'] = date('Y-m-d H:i:s',$analysis['last_buy_time']);
        }

        if($analysis['lv_id']){
            $sql = 'select name from sdb_ecorder_shop_lv where lv_id='.$analysis['lv_id'].' ';
            $rs = $db->selectRow($sql);
        $analysis['lv_id'] = $rs['name'];
        }
        
        //echo('<pre>');var_dump($analysis);
        
        $render = $app->render();
        $render->pagedata['analysis'] = $analysis;
        return $render->fetch('admin/member/analysis.html');
    }

    var $detail_edit = '客户信息';
    function detail_edit($id){
    	$app = app::get('taocrm');
        $render = $app->render();
        $this->getMemberInfo($id);
        $memberObj = $app->model('members');
        $member_analysisObj = $app->model('member_analysis');
        $taocrm_service_member = kernel::single('taocrm_service_member');
        if($_POST){
        	$data=array();
        	$data['email']=$_POST['email'];
        	$data['name']=$_POST['name'];
        	$data['sex']=$_POST['gender'];
        	$data['birthday']=strtotime($_POST['birthday']);
        	$data['area']=$_POST['area'];
        	$data['addr']=$_POST['addr'];
        	$data['zip']=$_POST['zipcode'];
        	$data['mobile']=$_POST['mobile'];
        	$data['telephone']=$_POST['telephone'];
        	$data['alipay_no']=$_POST['alipay_no'];
        	$data['is_vip']=$_POST['is_vip'];
        	$data['sms_blacklist']=$_POST['sms_blacklist'];
        	$data['edm_blacklist']=$_POST['edm_blacklist'];
        	$data['remark']=$_POST['remark'];
        	$member_analysisObj->update(array('is_vip'=>$_POST['is_vip']),array('member_id'=>$_POST['member_id']));
         	$rs =  $memberObj->update($data,array('member_id'=>$_POST['member_id']));

            $memberObj->chkMemberArea($_POST['member_id']);
            
            //保存ext扩展属性
            if($_POST['birthday']){
                $ext_info = array();
                $ext_info['member_id'] = $this->member_id;
                list($ext_info['b_year'],$ext_info['b_month'],$ext_info['b_day']) = 
                    explode('-', $_POST['birthday']);
                $taocrm_service_member->save_member_ext($ext_info);
            }
        }
	  $mem = $memberObj->dump(array('member_id'=>$this->member_id),'*');
      
      //处理扩展属性的生日
        $ext_info = $taocrm_service_member->get_member_ext($this->member_id);
        if($ext_info){
            if($ext_info['b_year']){
                $mem['profile']['birthday'] = 
                $ext_info['b_year'].'-'.$ext_info['b_month'].'-'.$ext_info['b_day'];
            }
        }
        if(!$mem['profile']['birthday']) $mem['profile']['birthday'] = '';
      
	  $render->pagedata['mem'] = $mem;
	  $render->pagedata['shops'] = $shops;
   	  return $render->fetch('admin/member/edit.html');
    }

    var $detail_goods = '买过的商品';
    function detail_goods($id){
        if(!$id) return null;
        $this->getMemberInfo($id);

        $app = app::get('taocrm');
        $render = $app->render();
        $goods = array();
        $orders = array();
        $shopIdFilter = '';
        if (isset($_GET['shop_id']) && $_GET['shop_id']) {
            $shopIdFilter = " AND shop_id =  '{$_GET['shop_id']}'";
        }
        $sql = "select order_id from sdb_ecorder_orders where member_id=".$this->member_id . $shopIdFilter;
        $rs = kernel::database()->select($sql);
        if($rs){
            foreach($rs as $v){
                $orders[] = $v['order_id'];
            }
        }
        if($orders) {
            $sql = "select bn,name,sum(nums) as nums,sum(amount) as amount from sdb_ecorder_order_items where order_id in (".implode(',',$orders).") {$shopIdFilter}
            group by goods_id";
            $goods = kernel::database()->select($sql);
        }

        $shop_id = '';
        if (isset($_GET['shop_id']) && $_GET['shop_id']) {
            $shop_id = $_GET['shop_id'];
        }

        $render->pagedata['goods'] = $goods;
        return $render->fetch('admin/member/goods.html');
    }

    var $detail_order = '历史订单';
    function detail_order($id=null){
        if(!$id) return null;
        $this->getMemberInfo($id);
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }
        
        //订单报表
        $order_report = array();
        $order_max_amount = 0;
        
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orderObj = &app::get(ORDER_APP)->model('orders');
        $order_cols = 'order_id,order_bn,status,pay_status,ship_status,total_amount,createtime,shop_id,payed';
        $orders = $orderObj->getList($order_cols, array('member_id' => $this->member_id), 0, -1, 'createtime DESC');
        //$row = $orderObj->getList('order_id',array('member_id' => $this->member_id));
        //$count = count($row);
        foreach($orders as $key=>$order){
        
            $rk = date('Y-m', $order['createtime']);
            if($order['pay_status']=='1'){
                if(date('Y', $order['createtime']) != date('Y')){
                    $order_report[$rk]['bgcolor'] = '#F4F4F4';
                }
                $order_report[$rk]['amount'] = intval($order_report[$rk]['amount']) + $order['total_amount'];
                $order_report[$rk]['orders'] = intval($order_report[$rk]['orders']) + 1;
                $order_report[$rk]['avg_amount'] = round($order_report[$rk]['amount']/$order_report[$rk]['orders'], 2);
                $order_max_amount = max($order_max_amount, $order['total_amount']);
            }
        
            $orders[$key]['shop_name'] = $shops[$orders[$key]['shop_id']]['name'];
            $orders[$key]['status'] = $orderObj->trasform_status('status',$orders[$key]['status']);
            $orders[$key]['pay_status'] = $orderObj->trasform_status('pay_status',$orders[$key]['pay_status'] );
            $orders[$key]['ship_status'] = $orderObj->trasform_status('ship_status', $orders[$key]['ship_status']);
        }
        $shop_id = '';
        if (isset($_GET['shop_id']) && $_GET['shop_id']) {
            $shop_id = $_GET['shop_id'];
        }
        $cloneOrders = array();
        if ($shop_id != '') {
            foreach ($orders as $k => $v) {
                if ($v['shop_id'] == $shop_id) {
                    $cloneOrders[] = $v;
                }
                continue;
            }
        }
        $app = app::get('taocrm');
        $render = $app->render();
        if ($cloneOrders) {
            $render->pagedata['orders'] = $cloneOrders;
        }
        else {
            $render->pagedata['orders'] = $orders;
        }
        $render->pagedata['order_max_amount'] = $order_max_amount;
        $render->pagedata['order_report'] = ($order_report);
        return $render->fetch('admin/member/order.html');
    }

    var $detail_contact = '联 系 人';
    function detail_contact($id=null){
        if(!$id) return null;
        $this->getMemberInfo($id);
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_contacts');
        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));
        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;
        return $render->fetch('admin/member/contact.html');
    }

    var $detail_addr = '收货地址';
    function detail_addr($id=null){
        if(!$id) return null;
        $this->getMemberInfo($id);

        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_receivers');
        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));

        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;

        return $render->fetch('admin/member/addr.html');
    }

    var $detail_active = '营销活动';
    function detail_active($id){
    	if(!$id) $id = $_GET['id'];
    	$app = app::get('taocrm');
    	$render = $app->render();
    	$member_analysisObj = $app->model('member_analysis');
    	$sms_log_obj = app::get('market')->model('sms_log');
    	$shop_obj = app::get('ecorder')->model('shop');
    	$active_obj = app::get('market')->model('active');
    	//分页
    	$pagelimit =3;
    	$page = intval($_GET['page']);
        $page = $page ? $page : 1;
    	$member_id=$member_analysisObj->dump(array('id'=>$id),"member_id");
    	$m_id=$member_id['member_id'];
    	$activelist=$sms_log_obj->getList("*");
    	$activeid=array();
    	foreach ($activelist as $k=>$v){
    		$aclist=json_decode($v['member_id']);
    		if (in_array($m_id, $aclist)){
    			$activeid[]=$v['active_id'];
    		}
    	}
		$filter=array('active_id|in'=>$activeid);
    	$activenamelist=$active_obj->getList("*",$filter);
    	$orderItems = $active_obj->getPager($filter,'*',$pagelimit * ($page - 1), $pagelimit);
    	foreach ($orderItems['data'] as $k=>$v){
    		$shop_name=$shop_obj->dump(array("shop_id"=>$v["shop_id"]),"name");
    		$active_id=$v['active_id'];
    		$count=$sms_log_obj->dump(array('active_id'=>$active_id));
    		$cou=count(json_decode($count['member_id']));
			$orderItems['data'][$k]['total_num']=$cou;
    		$orderItems['data'][$k]['shop_name']=$shop_name['name'];
    		$orderItems['data'][$k]['create_time']=date('Y-m-d',$v['create_time']);//end_time
    		$orderItems['data'][$k]['end_time']=date('Y-m-d',$v['end_time']);
    	}
    	$count = $orderItems ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&finderview=detail_active&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&page=%d&view='.$view));
        $render->pagedata['pager'] = $pager;
    	$render->pagedata['activenamelist'] = $orderItems['data'];
    	return $render->fetch('admin/member/active.html');
    }

   var $detail_points = '积分日志';
    function detail_points($id){
        $this->getMemberInfo($id);
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }
        $logs = app::get('taocrm')->model('all_points_log')->db->select('select * from sdb_taocrm_all_points_log where member_id='.$this->member_id.' order by id desc limit 10');
        foreach($logs as $k=>$v){
            $logs[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            $logs[$k]['order_bn'] = &app::get('ecorder')->model('orders')->dump($v['order_id'],'order_bn');
            $logs[$k]['order_bn'] = $logs[$k]['order_bn']['order_bn'];
            $logs[$k]['refund_bn'] = &app::get('ecorder')->model('refunds')->dump($v['refund_id'],'refund_bn');
            $logs[$k]['refund_bn'] = $logs[$k]['refund_bn']['refund_bn'];
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['logs'] = $logs;
        return $render->fetch('admin/member/points_log.html');
    }

}
