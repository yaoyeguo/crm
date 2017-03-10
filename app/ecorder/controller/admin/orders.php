<?php 

class ecorder_ctl_admin_orders extends desktop_controller{

    //var $workground = 'taocrm.sales';
    
    public function __construct($app){
        parent::__construct($app);
    }

    function index()
    {   
        $this->finder('ecorder_mdl_orders',array(
            'title'=>'销售订单列表',
            'actions'=>$actions,
            //'base_filter'=>$base_filter,
            'orderBy' => 'createtime DESC',
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        ));
    }
     
    function _views()
    {
        $oGoods = $this->app->model('orders');
        $base_filter = array();
        
        $sub_menu[] = array(
            'label'=>'全部',
            'filter'=>array(),
            'optional'=>false,
            'display'=>true,
        );

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->get_shops('no_fx');
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false,
                'display'=>true,
            );
            $shop_id_arr[] = $shop['shop_id'];
        }
        
        //$sub_menu[0]['filter'] = array('shop_id'=>$shop_id_arr);

        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = 0;//$oGoods->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }
        return $sub_menu;
    }

    public function getMemberAddress($mem_id=0)
    {
        if (!$mem_id){
            $mem_id = floatval($_POST['member_id']);
        }
        $app = app::get('taocrm');
        $addrObj = $app->model('member_receivers');
        $addrs = $addrObj->getList('*',array('member_id' => $mem_id));
        foreach($addrs as $k => $v){
            !$v['tel'] && $addrs[$k]['tel'] = '';
        }
        echo json_encode($addrs);
        exit;
    }

    public function get_goods()
    {
        $this->display("admin/order/set_goods.html");
    }

    public function set_goods()
    {
        $goods_ids = $_POST['goods_id'];
        $sql = "select goods_id,bn,name,spec,price from sdb_ecgoods_shop_goods where 1=1 ";
        $sql .= " and goods_id in (".implode(',',$goods_ids).") ";

        $rs = &app::get('ecgoods')->model('shop_goods')->db->select($sql);
        $res = '';
        foreach($rs as $k => $v){
            $res .= '<tr class="tr_product">';
            $res .= '    <input type="hidden" name="goods_id[]" value="'.$v['goods_id'].'" />';
            $res .= '    <td>'.$v['bn'].'</td>';
            $res .= '    <td>'.$v['name'].'</td>';
            $res .= '    <td>'.mb_substr($v['spec'],0,10,'utf-8').'</td>';
            $res .= '    <td><input type="text" style="width:50px" name="num[]" onblur="count_price()" /></td>';
            $res .= '    <td><input type="text" style="width:50px" name="price[]" onblur="count_price()" /></td>';
            $res .= '    <td><a href="javascript:;" onclick="delete_goods($(this));">删除</a></td>';
            $res .= '</tr>';
        }
        echo($res);
    }

    function get_members()
    {
        $mbObj = &app::get('taocrm')->model('members');
        if($_POST['mobile']){
            $data = $mbObj->get_member($_POST['mobile'],'mobile');
        }elseif ($_POST['uname']){
            $data = $mbObj->get_member($_POST['uname'],'uname');
        }elseif ($_POST['name']){
            $data = $mbObj->get_member($_POST['name'],'name');
        }

        if ($data){
            foreach ($data as $k => $v){
                switch($v['sex'])
                {
                    case 'male':
                        $data[$k]['sex'] = '男';
                        break;
                    case 'female':
                        $data[$k]['sex'] = '女';
                        break;
                    case 'unkown':
                        $data[$k]['sex'] = '未知';
                        break;
                }
            }
        }

        if ($data){
            echo "window.autocompleter_json=".json_encode($data);
            exit;
        }
        echo "000000";
        exit;
    }

    public function addNewAddress()
    {
        if (isset($_GET['area'])){
            $this->pagedata['region'] = $_GET['area'];
        }
        $this->display("admin/order/add_new_address.html");
    }

    public function getConsingee()
    {
        $string = $_POST['consignee'];
        if ($string['area']){
            $region = explode(':', $string['area']);
            if (!$region[2]){
                return false;
            }
        }else {
            return false;
        }
        $string['id'] = $region[2];
        echo json_encode($string);
    }

    function create()
    {
		$shopObj = app::get('ecorder')->model('shop');
		$shopList=$shopObj->getList("*");
        foreach($shopList as $k=>$v){
            if(!$v['node_id'] or !$v['shop_type']) unset($shopList[$k]);
        }
		$this->pagedata['shop_list']=$shopList;//店铺信息
        $this->pagedata['datetime'] = date('Y-m-d H:i:s',time());
		$this->page('admin/order/create.html');
    }
    
    function save_create()
    {
        $this->begin('index.php?app=ecorder&ctl=admin_orders&act=system');
        //店铺
        $shopObj = app::get('ecorder')->model('shop');
        $shopList=$shopObj->getList("*",array('shop_id'=>$_POST['shop_id']));
        $nodeId = $shopList[0]['node_id'];
        $is_cod = $_POST['order_type'] == 'on' ? 'true' : 'false';//是否货到付款
        //ip地址
        $user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
        $user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];
        //会员信息
        $memberObj = app::get('taocrm')->model('members');
        $memberInfo = $memberObj->getList('*',array('member_id'=>$_POST['id']));
        $memberInfo_json = json_encode(array(
            'uname'=>$memberInfo[0]['uname'],
            'name'=>$memberInfo[0]['name'],
            'area'=>$memberInfo[0]['area'],
            'state'=>$memberInfo[0]['state'],
            'city'=>$memberInfo[0]['city'],
            'district'=>$memberInfo[0]['district'],
            'alipay_no'=>$memberInfo[0]['alipay_no'],
            'addr'=>$memberInfo[0]['addr'],
            'mobile'=>$memberInfo[0]['mobile'],
            'tel'=>$memberInfo[0]['tel'],
            'email'=>$memberInfo[0]['email'],
            'zip'=>$memberInfo[0]['zip']
        ));
        
        //收货信息，mainland:北京/北京市/西城区:4
        $consignee_area = trim($_POST['consignee_area']);
        preg_match("/mainland:(.+?)\/(.+?)\/(.+?):/", $consignee_area, $consignee_area_match);
        $consignee = array(
            'name'=>$_POST['consignee_name'],
            'area_state'=>$consignee_area_match[1],
            'area_city'=>$consignee_area_match[2],
            'area_district'=>$consignee_area_match[3],
            'addr'=>$_POST['consignee_addr'],
            'zip'=>$_POST['consignee_zip'],
            'telephone'=>$_POST['consignee_phone'],
            'email'=>$_POST['consignee_email'],
            'r_time'=>$_POST['consignee_r_time'],
            'mobile'=>$_POST['consignee_mobile']
        );
        $consignee_json = json_encode($consignee);
        
        //商品明细
        $goods_ids = $_POST['goods_id'];
        foreach($goods_ids as $k=>$v){
            $buy_goods[$v] = array(
                'price' => floatval($_POST['price'][$k]),
                'quantity' => floatval($_POST['num'][$k]),
            );
        }
        
        $sql = "select goods.goods_id,goods.bn,goods.name,goods.weight,goods.score,goods.cost from sdb_ecgoods_shop_goods as goods where 1=1 ";
        $sql .= " and goods.goods_id in (".implode(',',$goods_ids).") ";
        $rs = app::get('ecgoods')->model('shop_goods')->db->select($sql);
        $goods_arr = array();
        foreach($rs as $key=>$value){
        
            $goods_id = $value['goods_id'];
            $price = $buy_goods[$goods_id]['price'];
            $quantity = $buy_goods[$goods_id]['quantity'];
            $amount = round($price*$quantity, 2);
        
            $goods_arr[$key] = array(
                "oid"=>null,
                "logistics_company"=>null,
                "logistics_code"=>null,
                "obj_type"=>"goods",
                "obj_alias"=>null,
                "shop_goods_id"=>$goods_id,
                "bn"=>$value['bn'],
                "name"=>$value['name'],
                "price"=>$price,
                "quantity"=>$quantity,
                "amount"=>$amount,
                "weight"=>$value['weight'],
                "score"=>$value['score'],
                "order_items"=>array(
                    0=>array(
                        "shop_product_id"=>0,
                        "shop_goods_id"=>$goods_id,
                        "item_type"=>$value['item_type'],
                        "bn"=>$value['bn'],
                        "name"=>$value['name'],
                        "product_attr"=>null,
                        "cost"=>$value['cost'],
                        "quantity"=>$quantity,
                        "sendnum"=>$quantity,
                        "amount"=>$amount,
                        "price"=>$price,
                        "weight"=>$value['weight'],
                        "status"=>"active",
                        "score"=>$value['score'],
                        "create_time"=>$value['create_time']
                    )
                )
            );
        }
        
        //付款信息
        $post_price = floatval($_POST['post_price']);
        $pay_amount = floatval($_POST['pay_amount']);
        $order_amount = floatval($_POST['order_amount']);
        $payment_detail = array(
            'pay_time' => time(),
            'money' => $pay_amount,
        );
        
        //print_r($goods_arr);
        $goods_json = json_encode($goods_arr);
        $sdf = array(
            'source' => 'manual',
            'order_source' => 'manual',
            'order_bn' => $this->random_order_bn(),
            'memeber_id' => trim($_POST['id']),
            'status' => 'finish',
            'pay_status' => '1',
            'ship_status' => '1',
            'is_delivery' => 'Y',
            'member_info' => $memberInfo_json,
            'title' => $shopList[0]['name'],
            'itemnum' => $_POST['cnt'],//商品总数
            'modified' => time(),
            'createtime' => time(),
            'ip' => $user_IP,
            'consignee' => $consignee_json,
            'payment_detail' =>json_encode($payment_detail),
            'cost_item' => floatval($_POST['goods_amount']),//商品总金额
            'is_tax' => 'false',
            'cost_tax' => '0.00',
            'tax_title' => $_POST['invoice_title'],
            'currency' => 'CNY',
            'cur_rate' => '1',
            'discount' => $order_amount - $pay_amount,//折扣
            'pmt_goods' => '0',
            'pmt_order' => '0',
            'total_amount' => $order_amount,//订单总金额
            'payed' => $pay_amount,//实付金额
            'custom_mark' => '',
            'order_objects' => $goods_json,
            'trade_type' => 'fixed',
            'delivery_time' => time(),
            'pay_time' => time(),
            'ship_time' => time(),
            'finish_time' => time(),
            'shipping' => json_encode(array('is_cod'=>$is_cod, 'cost_shipping'=>$post_price)),//是否货到付款
            'shop_id'=> $_POST['shop_id'],
            'op_name'=>'agent',
        );
        $res = $this->insertIntoOrder($sdf,$nodeId);
        if(is_array($res)){
            $orders_manual = array(
                'order_id' => $res['order_id'],
                'order_bn' => $sdf['order_bn'],
                'uname' => $memberInfo[0]['uname'],
                'receiver' => $consignee['name'],
                'mobile' => $consignee['mobile'],
                'shop_id' => $sdf['shop_id'],
                'op_name'=>kernel::single('desktop_user')->get_name(),
                'create_time' => time(),
            );
            //自建订单表，saas暂时不启用
            $this->app->model('orders_manual')->insert($orders_manual);
            $this->end(true,'创建成功');
        }else{
            $this->end(false,$res);
        }
    }

    private function insertIntoOrder($sdf,$nodeId)
    {
        static $orderObj = null;
        static $response = null;
        if (!$orderObj) {
            $orderObj = new ecorder_rpc_response_order_add();
            $response = kernel::single('base_rpc_service');
        }
        base_rpc_service::$node_id = $nodeId;
        return $orderObj->add($sdf, $response);
    }
    
    function system()
    {
        //$base_filter = array('op_name'=>'agent');
        
        $actions = array(
            array(
            'label'=>'导入历史数据',
            'href'=>'index.php?app=ecorder&ctl=admin_orders&act=import_old_system_order',
            )
        );
        
        $this->finder('ecorder_mdl_orders_manual',array(
            'title'=>'自建订单列表',
            'base_filter'=>$base_filter,
            'actions'=>$actions,
            'orderBy' => 'id DESC',
            'use_buildin_set_tag'=>false,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true,
            'use_view_tab'=>true,
        ));
    }

    //数据时间范围：2015-06-01 ~ 2015-09-01
    public function import_old_system_order()
    {
        $this->begin();
        
        $mdl_orders_manual = $this->app->model('orders_manual');
        $start_time = strtotime('2015-06-01 00:00:00');
        $end_time = strtotime('2015-09-01  00:00:00');
        $sql = "select a.order_id,a.order_bn,a.member_id,a.createtime as create_time,a.shop_id,
                a.ship_name as receiver,a.ship_mobile as mobile,b.uname 
            from sdb_ecorder_orders as a left join sdb_taocrm_members as b 
                on a.member_id=b.member_id 
            where a.createtime between $start_time and $end_time and a.op_name='agent' ";
        $rs = $mdl_orders_manual->db->select($sql);
        if( ! $rs){
            $this->end(false, '没有数据需要导入！');
        }
        
        $op_name = kernel::single('desktop_user')->get_name();
        $succ_num = 0;
        foreach($rs as $v){
            $filter = array('order_bn'=>$v['order_bn']);
            if( ! $mdl_orders_manual->dump($filter)){
                $orders_manual = array(
                    'order_id' => $v['order_id'],
                    'order_bn' => $v['order_bn'],
                    'uname' => $v['uname'],
                    'receiver' => $v['receiver'],
                    'mobile' => $v['mobile'],
                    'shop_id' => $v['shop_id'],
                    'op_name'=>$op_name,
                    'create_time' => $v['create_time'],
                );
                $mdl_orders_manual->insert($orders_manual);
                $succ_num ++;
            }
        }
        $this->end(true, '导入完成('.$succ_num.'笔订单)！');
    }
    
    /**
     * 生成随机的数字串订单号
     * @return string
     */
    function random_order_bn()
    {
        $str = '';
        for($i = 0; $i < 5; $i++) {
            $str .= mt_rand(0, 5);
        }
        $current_time = time() ;
        return $current_time . $str;
    }
    
}
