<?php
class taocrm_finder_member_analysis {
    var $pagelimit = 20;
    var $addon_cols = 'member_id,shop_id';
    var $shop_evaluation =array('good'=>'好评','bad'=>'差评','neutral'=>'中评','unkown'=>'-');
    protected static $membersObj = null;

    function __construct(){
        $this->oMembers = app::get('taocrm')->model('members');
        $oShop = app::get('ecorder')->model('shop');
        $rs = $oShop->getList('shop_id,name');
        if(!$rs) return false;
        foreach($rs as $v){
            $this->shops[$v['shop_id']] = $v['name'];
        }
    }

    var $column_uname = '客户名';
    var $column_uname_width = 110;
    var $column_uname_order = 10;
    function column_uname($row){
        $member_id = $row[$this->col_prefix.'member_id'];
        if(!$member_id)return '';
        $rs = $this->oMembers->dump($member_id,'uname');
        return $rs['account']['uname'];
    }

    var $column_shop = '来源店铺';
    var $column_shop_order = 20;
    function column_shop($row){
        $shop_id = $row[$this->col_prefix.'shop_id'];
        if(!$shop_id)return '';
        return $this->shops[$shop_id];
    }

    public $column_area = '地区';
    public $column_area_width = 110;
    public $column_area_order = 71;
    public $column_area_order_field = 'district';
    public function column_area($row)
    {
        $member_id = $row[$this->col_prefix.'member_id'];
        if (self::$membersObj == null) {
            $app = app::get('taocrm');
            self::$membersObj = $app->model('members');
        }
        return self::$membersObj->getAreasInfo($member_id);
    }

	var $column_tag = '标签';
    var $column_tag_order = 30;
    function column_tag($row)
    {
        $tagInfo = $row['tagInfo'];
        if($tagInfo){
            $tagInfo = '<img border=0 title="'.$tagInfo.'" align="absmiddle" src="'.app::get('taocrm')->res_url.'/teg_ico.png" >';
    	}
        return $tagInfo;
    }
    private function getMemberInfo($id){
        $rs = app::get('taocrm')->model('member_analysis')->getUniqueKey($id);
        $this->member_id = $rs['member_id'];
        $this->shop_id = $rs['shop_id'];
    }

    var $detail_edit = '客户信息';
    function detail_edit($id){
    	$app = app::get('taocrm');
        $render = $app->render();
        $this->getMemberInfo($id);
        $memberObj = $app->model('members');
        $member_analysisObj = $app->model('member_analysis');

        //保存客户资料
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
        }

        $mem = $memberObj->dump(array('member_id'=>$this->member_id),'*');
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
        $sql = "select order_id from sdb_ecorder_orders where member_id=".$this->member_id;
        //$sql = "select order_id from sdb_ecorder_orders where member_id=".$this->member_id." and pay_status='1' ";
        if (isset($this->shop_id) && $this->shop_id) {
            $sql .= " AND shop_id = '{$this->shop_id}' ";
        }
        $rs = kernel::database()->select($sql);
        if($rs){
            foreach($rs as $v){
                $orders[] = $v['order_id'];
            }
        }
        if($orders) {
        	/*
            $sql = "select bn,name,sum(nums) as nums,sum(amount) as amount from sdb_ecorder_order_items where order_id in (".implode(',',$orders).")
            group by shop_goods_id
            ";
            */
            $sql = "select bn,name,sum(nums) as nums,sum(amount) as amount from sdb_ecorder_order_items where order_id in (".implode(',',$orders).")
            group by name
            ";
            $goods = kernel::database()->select($sql);
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
        $filter = array();
        $filter['member_id'] = $this->member_id;
        if (isset($this->shop_id) && $this->shop_id) {
            $filter['shop_id'] = $this->shop_id;
        }
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orderObj = app::get(ORDER_APP)->model('orders');
        $order_cols = 'order_id,order_bn,status,pay_status,ship_status,total_amount,createtime,shop_id,payed';
        $orders = $orderObj->getList($order_cols, $filter);
        $row = $orderObj->getList('order_id', $filter);
        $count = count($row);
        foreach($orders as $key=>$order){
            $orders[$key]['shop_name'] = $shops[$orders[$key]['shop_id']]['name'];
            $orders[$key]['status'] = $orderObj->trasform_status('status',$orders[$key]['status']);
            $orders[$key]['pay_status'] = $orderObj->trasform_status('pay_status',$orders[$key]['pay_status'] );
            $orders[$key]['ship_status'] = $orderObj->trasform_status('ship_status', $orders[$key]['ship_status']);
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['orders'] = $orders;
        return $render->fetch('admin/member/order.html');
    }

    var $detail_refunds = '退款单';
    function detail_refunds($id=null){
        if(!$id) return null;
        $this->getMemberInfo($id);
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }
        $filter = array();
        $filter['member_id'] = $this->member_id;
        if (isset($this->shop_id) && $this->shop_id) {
            $filter['shop_id'] = $this->shop_id;
        }
        //$nPage = $_GET['detail_refunds'] ? $_GET['detail_refunds'] : 1;
        $orderObj = app::get(ORDER_APP)->model('tb_refunds');
        $order_cols = '*';
        $orders = $orderObj->getList($order_cols, $filter);
        //$row = $orderObj->getList('refund_id_id', $filter);
        //$count = count($row);

        $order_status = array(
            'TRADE_NO_CREATE_PAY' => '非支付宝交易',
            'WAIT_BUYER_PAY' => '等待付款',
            'WAIT_SELLER_SEND_GOODS' => '等待卖家发货',
            'WAIT_BUYER_CONFIRM_GOODS' => '等待确认收货',
            'TRADE_BUYER_SIGNED' => '买家已签收',
            'TRADE_FINISHED' => '交易成功',
            'TRADE_CLOSED' => '交易关闭',
            'TRADE_CLOSED_BY_TAOBAO' => '被淘宝关闭',
        );

        $good_status = array(
            'BUYER_NOT_RECEIVED' => '买家未收到货',
            'BUYER_RECEIVED' => '买家已收到货',
            'BUYER_RETURNED_GOODS' => '买家已退货',
        );

        foreach($orders as $key=>$order){
            $orders[$key]['shop_name'] = $shops[$orders[$key]['shop_id']]['name'];
            $orders[$key]['order_status'] = $order_status[$orders[$key]['order_status']];
            $orders[$key]['good_status'] = $good_status[$orders[$key]['good_status']];
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['refunds'] = $orders;
        return $render->fetch('admin/member/refunds.html');
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
    /*
    var $detail_remark = '客户备注';
    function detail_remark($id){
        $this->getMemberInfo($id);
        $app = app::get('taocrm');
        $memberObj = $app->model('members');
        if($_POST){
            $sdf['remark'] = $_POST['remark'];
            $sdf['remark_type'] = $_POST['remark_type'];
            if(!$memberObj->update($sdf,array('member_id' => $this->member_id))){
                $msg = app::get('b2c')->_('保存失败!');
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
            }
            if($_GET['singlepage']=='true'){
                $msg = app::get('b2c')->_('保存成功!');
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.$msg.'",_:null}';
                exit;
            }
        }
        $remark = $memberObj->getRemarkByMemId($this->member_id);
        $render = $app->render();
        $render->pagedata['remark_type'] = $remark['remark_type'];
        $render->pagedata['remark'] =  $remark['remark'];
        $render->pagedata['res_url'] = $app->res_url;
        return $render->fetch('admin/member/remark.html');
    }
    */

    var $detail_active = '营销活动';
    function detail_active($id){
        //此处的id,是客户分析表中的序号
        if(!$id) $id = $_GET['id'];
        $this->getMemberInfo($id);
        $app = app::get('taocrm');
        $marketApp = app::get('market');

        $orderObj = app::get('ecorder')->model('orders');
        //分页
        $pagelimit = 3;
        $page = max(1, intval($_GET['page']));
        $offset = ($page - 1) * $pagelimit;
        $activeMemberModel = $marketApp->model('active_member');
        $shopId = $this->shop_id;
        $filter = array('issend' => 1, 'member_id' => $this->member_id, 'shop_id' => $shopId);
        $memberList = $activeMemberModel->getList('*', $filter, $offset, $pagelimit);
        $count = $activeMemberModel->count($filter);
        $render = $app->render();
        if ($memberList) {
            $activeModel = $marketApp->model('active');
            $ecorderApp = app::get('ecorder');
            $shopModel = $ecorderApp->model('shop');
            $shopInfo = $shopModel->getList('shop_id,name');
            $shopList = array();
            foreach ($shopInfo as $v) {
                $shopList[$v['shop_id']] = $v['name'];
            }
            $activenamelist = array();
            $i = 0;
            foreach ($memberList as $v) {
                $activenamelist[$i]['shop_name'] = isset($shopList[$v['shop_id']]) ? $shopList[$v['shop_id']] : '未知店铺';
                $activeInfo = $activeModel->dump(array('active_id' => $v['active_id']));
                if ($activeInfo) {
                	$activenamelist[$i]['shop_id'] = $v['shop_id'];
                    $activenamelist[$i]['active_name'] = $activeInfo['active_name'];
                    $activenamelist[$i]['total_num'] = $activeInfo['total_num'];
                    $activenamelist[$i]['create_time'] = date("Y-m-d H:i:s", $activeInfo['create_time']);
                    $activenamelist[$i]['end_time'] = date("Y-m-d H:i:s", $activeInfo['end_time']);

                    //获取营销活动期内的订单号
		    		$filter_order = array('shop_id'=>$v['shop_id'],'member_id'=>$this->member_id,'createtime|between'=>array($activeInfo['create_time'],$activeInfo['end_time']));
		    		$orders = $orderObj->getList("order_bn,order_id",$filter_order);
		    		$activenamelist[$i]['orders'] = $orders;
                }
                else {
                    $activenamelist[$i]['active_name'] = '活动已经删除';
                    $activenamelist[$i]['total_num'] = '未知';
                    $activenamelist[$i]['create_time'] = '未知';
                    $activenamelist[$i]['end_time'] = '未知';
                }
                $i++;
            }

            $view = $_GET['view'];
            $total_page = ceil($count / $pagelimit);
            $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member_vip&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_active&page=%d&view='.$view.'&shop_id='.$shopId ));
            $render->pagedata['pager'] = $pager;
            $render->pagedata['activenamelist'] = $activenamelist;
        }
        return $render->fetch('admin/member/active.html');
    }

    function back_detail_active($id){
    	if(!$id) $id = $_GET['id'];
    	$app = app::get('taocrm');
    	$render = $app->render();
    	$member_analysisObj = $app->model('member_analysis');
    	$sms_log_obj = app::get('market')->model('sms_log');
    	$edm_log_obj = app::get('market')->model('edm_log');
    	$shop_obj = app::get('ecorder')->model('shop');
    	$active_obj = app::get('market')->model('active');
    	//分页
    	$pagelimit =3;
    	$page = intval($_GET['page']);
        $page = $page ? $page : 1;
    	$memberInfo = $member_analysisObj->dump(array('id'=>$id),"member_id,shop_id");
    	$m_id=$memberInfo['member_id'];
    	/*
    	$activelist=$sms_log_obj->getList("*");
    	$activeid=array();
    	foreach ($activelist as $k=>$v){
    		$aclist=json_decode($v['member_id']);
    		if (in_array($m_id, $aclist)){
    			$activeid[]=$v['active_id'];
    		}
    	}
    	*/

    	$active_member = app::get('market')->model('active_member');
    	$actives = $active_member->getList('distinct active_id',array('member_id'=>$m_id));
    	foreach($actives as $v){
    		$activeid[]=$v['active_id'];
    	}
    	$filter = array();
		$filter=array('active_id|in'=>$activeid);
    	if ($memberInfo['shop_id']) {
    	    $filter['shop_id'] = $memberInfo['shop_id'];
    	}
    	//$orderItems['data'] = $active_obj->getList("*",array(),$pagelimit * ($page - 1), $pagelimit);
        //$orderItems['count'] = 100;
    	$orderItems = $active_obj->getPager($filter,'*',$pagelimit * ($page - 1), $pagelimit);
    	foreach ($orderItems['data'] as $k=>$v){
    		$shop_name=$shop_obj->dump(array("shop_id"=>$v["shop_id"]),"name");
    		$active_id=$v['active_id'];
    		$type=$active_obj->dump(array('active_id'=>$active_id),"type");
    		if(in_array('sms',unserialize($type['type']))){
    			$count=$sms_log_obj->dump(array('active_id'=>$active_id));
    		}elseif(in_array('edm',unserialize($type['type']))){
    			$count=$edm_log_obj->dump(array('active_id'=>$active_id));
    		}
    		$cou=count(json_decode($count['member_id']));
			$orderItems['data'][$k]['total_num']=$cou;
    		$orderItems['data'][$k]['shop_name']=$shop_name['name'];
    		$orderItems['data'][$k]['create_time']=date('Y-m-d',$v['create_time']);//end_time
    		$orderItems['data'][$k]['end_time']=date('Y-m-d',$v['end_time']);
    	}
    	$count = $orderItems ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $render->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&finderview=detail_active&id='.$id.'&page=%d' ));
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
            $logs[$k]['order_bn'] = app::get('ecorder')->model('orders')->dump($v['order_id'],'order_bn');
            $logs[$k]['order_bn'] = $logs[$k]['order_bn']['order_bn'];
            $logs[$k]['refund_bn'] = app::get('ecorder')->model('refunds')->dump($v['refund_id'],'refund_bn');
            $logs[$k]['refund_bn'] = $logs[$k]['refund_bn']['refund_bn'];
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['logs'] = $logs;
        return $render->fetch('admin/member/points_log.html');
    }

}
