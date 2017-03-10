<?php
class market_ctl_site_weixin_point extends base_controller{
    
    function __construct($app){
        parent::__construct($app);
    }

    /*
    *显示积分日志
    * get参数 fromusername
    */
    public function index(){
        $page_size = 8;
        $page = max(1, intval($_GET['page']));
        $fromusername = $_GET['fromusername'];
        $db = kernel::database();
        $row = $db->selectrow('select tb_nick,mobile,points,member_id from sdb_market_wx_member where FromUserName="'.$fromusername.'"');
        if($row){
            $memberId = $row['member_id'];

            if($memberId){
                $pointObj=kernel::single("taocrm_member_point");

                //总积分
                $msg = '';
                $sum_points = $pointObj->get($memberId,$msg,'',time());
                $this->pagedata['sum_points'] = $sum_points['total_point'];

                //全部积分日志
                $msg = '';
                $memberPointLogList = $pointObj->getPointLogList('',$memberId,$page_size,$page,$msg);
                //获取店铺列表
                $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
                if($rs) {
                    foreach($rs as $v){
                        $shops[$v['shop_id']] = $v;
                    }
                }
                foreach($memberPointLogList['logs'] as $k=>$v){
                    $member_data = app::get('taocrm')->model('members')->dump(array('member_id'=>$memberId),'uname');
                    $memberPointLogList['logs'][$k]['user_name'] = $member_data['account']['uname'];
                    $memberPointLogList['logs'][$k]['shop_name'] = empty($shops[$v['shop_id']]['name']) ? '-' : $shops[$v['shop_id']]['name'] ;
                    if($v['points'] > 0){
                        $memberPointLogList['logs'][$k]['points'] = '+'.$v['points'];
                    }
                    $memberPointLogList['logs'][$k]['op_time'] = date('Y-m-d',strtotime($v['op_time']));
                }

                //获取的积分日志
                $msg_add = '';
                $memberPointLogList_add = $pointObj->getPointLogList('',$memberId,$page_size,$page,$msg_add,'+');
                foreach($memberPointLogList_add['logs'] as $ka=>$va){
                    $memberPointLogList_add['logs'][$ka]['points'] = '+'.$va['points'];
                    $memberPointLogList_add['logs'][$ka]['op_time'] = date('Y-m-d',strtotime($va['op_time']));
                }

                //扣除的积分日志
                $msg_minus = '';
                $memberPointLogList_minus = $pointObj->getPointLogList('',$memberId,$page_size,$page,$msg_minus,'-');
                foreach($memberPointLogList_minus['logs'] as $km=>$vm){
                    $memberPointLogList_minus['logs'][$km]['op_time'] = date('Y-m-d',strtotime($vm['op_time']));
                }
            }
        }
        //$total_page = ceil($memberPointLogList['totalResult'] / $page_size);
       // $pager = $this->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=market&ctl=site_weixin_point&act=index&page=%d&fromusername='.$_GET['fromusername']));
        //$this->pagedata['pager'] = $pager;
        $this->pagedata['memberPointLogList'] = $memberPointLogList;
        $this->pagedata['memberPointLogList_add'] = $memberPointLogList_add;
        $this->pagedata['memberPointLogList_minus'] = $memberPointLogList_minus;
        $this->pagedata['member_id'] = $memberId;
        $this->display('site/weixin/point_log.html');
    }

    /*
     * ajax加载积分日志
     */
    public function get_poit_logs(){
        $page_size = 8;
        $page = max(1, intval($_POST['page']));
        $member_id = $_POST['member_id'];
        $point_type = $_POST['point_type'];
        $pointObj=kernel::single("taocrm_member_point");

        if($point_type == '-'){
            $msg_minus = '';
            $minus_list = $pointObj->getPointLogList('',$member_id,$page_size,$page,$msg_minus,'-');
            foreach($minus_list['logs'] as $km=>$vm){
                $minus_list['logs'][$km]['op_time'] = date('Y-m-d',strtotime($vm['op_time']));
            }
            $res = $minus_list['logs'];
        }elseif($point_type == '+'){
            $msg_add = '';
            $plus_list = $pointObj->getPointLogList('',$member_id,$page_size,$page,$msg_add,'+');
            foreach($plus_list['logs'] as $ka=>$va){
                $plus_list['logs'][$ka]['points'] = '+'.$va['points'];
                $plus_list['logs'][$ka]['op_time'] = date('Y-m-d',strtotime($va['op_time']));
            }
            $res = $plus_list['logs'];
        }else{
            $msg = '';
            $memberPointLogList = $pointObj->getPointLogList('',$member_id,$page_size,$page,$msg);
            foreach($memberPointLogList['logs'] as $k=>$v){
                if($v['points'] > 0){
                    $memberPointLogList['logs'][$k]['points'] = '+'.$v['points'];
                }
                $memberPointLogList['logs'][$k]['op_time'] = date('Y-m-d',strtotime($v['op_time']));
            }
            $res = $memberPointLogList['logs'];
        }
        if(empty($res)){
            echo '111111';
        }else{
            echo json_encode($res);
        }
        exit;
    }

    /*
     * 交易记录
     * get参数 fromusername
     */
    public function business_record(){
        $page_size = 10;
        $page = max(1, intval($_GET['page']));
        $fromusername = $_GET['fromusername'];
        $db = kernel::database();
        $order_data = array();
        $row = $db->selectrow('select tb_nick,mobile,points,member_id from sdb_market_wx_member where FromUserName="'.$fromusername.'"');
        if($row){
            $memberId = $row['member_id'];
            //获取店铺列表
            $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
            if($rs) {
                foreach($rs as $v){
                    $shops[$v['shop_id']] = $v;
                }
            }
            if($memberId){
                $order_obj = app::get('ecorder')->model('orders');
                $order_data = $order_obj->getlist('*',array('member_id'=>$memberId));
                $order_item_obj = app::get('ecorder')->model('order_items');
                $paying_list = array();
                $sending_list = array();
                $receiving_list = array();
                $i = 0;
                $j = 0;
                $n = 0;
                foreach($order_data as $key=>$value){
                    $order_data[$key]['item'] = $order_item_obj->getlist('name',array('order_id'=>$value['order_id']),0,3);
                    $order_data[$key]['shop_name'] = $shops[$value['shop_id']]['name'];
                    $order_data[$key]['createtime'] = date('Y-m-d H:i:s',$value['createtime']);
                    //订单进度状态，先判断是否支付，再判断是否发货，最后判断是否收货
                    if($value['pay_status'] == '0'){
                        $order_data[$key]['flow_status'] = '未支付';//进度状态
                        $paying_list[$i] = $order_data[$key];
                        $i++;
                    }else{
                        if($value['ship_status'] == '0'){
                            $order_data[$key]['flow_status'] = '待发货';//进度状态
                            $sending_list[$j] = $order_data[$key];
                            $j++;
                        }else{
                            if($value['status'] != 'finish'){
                                $order_data[$key]['flow_status'] = '待收货';//进度状态
                                $receiving_list[$n] = $order_data[$key];
                                $n++;
                            }else{
                                $order_data[$key]['flow_status'] = '已完成';//进度状态
                            }
                        }
                    }

                }
                //var_dump($order_data);
            }
        }
        $this->pagedata['order_data'] = $order_data;
        $this->pagedata['paying_list'] = $paying_list;
        $this->pagedata['sending_list'] = $sending_list;
        $this->pagedata['receiving_list'] = $receiving_list;
        $this->display('site/weixin/business_record.html');
    }

    //订单详情
    public function order_detail(){
        //获取店铺列表
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }

        $order_obj = app::get('ecorder')->model('orders');
        $order_data = $order_obj->dump(array('order_id'=>trim($_GET['order_id'])));
        $order_item_obj = app::get('ecorder')->model('order_items');
        $order_data['item'] = $order_item_obj->getlist('name,price,nums',array('order_id'=>$order_data['order_id']));
        $order_data['shop_name'] = $shops[$order_data['shop_id']]['name'];
        //订单进度状态，先判断是否支付，再判断是否发货，最后判断是否收货
        if($order_data['pay_status'] == '0'){
            $order_data['flow_status'] = '未支付';//进度状态
        }else{
            if($order_data['ship_status'] == '0'){
                $order_data['flow_status'] = '待发货';//进度状态
            }else{
                if($order_data['status'] != 'finish'){
                    $order_data['flow_status'] = '待收货';//进度状态
                }else{
                    $order_data['flow_status'] = '已完成';//进度状态
                }
            }
        }
        $order_data['createtime'] = date('Y-m-d H:i:s',$order_data['createtime']);
        $order_data['pay_time'] = date('Y-m-d H:i:s',$order_data['pay_time']);

        //物流信息
        $logi_obj = app::get('ecorder')->model('logi_info');
        $logi_data = $logi_obj->dump(array('order_id'=>$order_data['order_id']));
        $order_data['delivery_time'] = date('Y-m-d H:i:s',$logi_data['delivery_time']);
        $order_data['logi_company'] = $logi_data['logi_company'];
        //var_dump($order_data);
        $this->pagedata['order_data'] = $order_data;
        $this->display('site/weixin/order_detail.html');
    }

    /*
     * 物流信息
     */
    public function logistics_info(){
        if(!empty($_GET['order_id'])){
            $logi_obj = app::get('ecorder')->model('logi_info');
            $logi_data = $logi_obj->dump(array('order_id'=>trim($_GET['order_id'])));
            if(empty($logi_data)){
                echo '该订单不存在物流信息！';
                return false;
            }
            $logi_data['delivery_time'] = date('Y-m-d H:i:s',$logi_data['delivery_time']);

            $rpc_data['order_bn'] = $logi_data['order_bn'];
            $rpc_data['company_name'] = $logi_data['logi_company'];
            $rpc_data['logi_no'] = $logi_data['logi_no'];
            $orders = kernel::single('ecorder_service_hqepay');
            $rs = $orders->detail_delivery($rpc_data);
            //echo $rs;
            $data = array();
            if($rs['rsp'] == 'succ'){
                $data = $rs['data'];
            }
            //var_dump($data);
            $this->pagedata['data'] = $data;
            $this->pagedata['length_max'] = count($data)-1;
            $this->pagedata['logi_data'] = $logi_data;
            $this->display('site/weixin/logi_info.html');
        }
    }

    /*
    * 积分兑换订单列表
    * get参数 fromusername
    */
    public function point_business_record(){
        $fromusername = $_GET['fromusername'];

        //获取微信会员的信息
        $objWxMember = $this->app->model('wx_member');
        $wxMembeData = $objWxMember->dump(array('FromUserName'=>$fromusername));

        //查询兑换订单列表
        $objExchangeOrders = app::get('ecorder')->model('exchange_orders');
        $order_data = $objExchangeOrders->db->select('SELECT * FROM `sdb_ecorder_exchange_orders` where member_id='.$wxMembeData['member_id'].' ORDER BY order_id DESC;');
        foreach($order_data as $key => $value){
            $order_data[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        }

        $this->pagedata['order_data'] = $order_data;
        $this->display('site/weixin/point_business_record.html');
    }

    //积分兑换订单详情
    public function point_order_detail(){
        $order_obj = app::get('ecorder')->model('exchange_orders');
        $order_data = $order_obj->dump(array('order_id'=>trim($_GET['order_id'])));
        $order_data['create_time'] = date('Y-m-d H:i:s',$order_data['create_time']);
        $order_data['addr'] = str_replace(',','',$order_data['addr']);
        //var_dump($order_data);
        $this->pagedata['order_data'] = $order_data;
        $this->display('site/weixin/point_order_detail.html');
    }
}
