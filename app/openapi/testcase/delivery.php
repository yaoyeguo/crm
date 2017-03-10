<?php
class delivery extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->_rpc = include 'rpc.php';
    }
    
    /**
    * 发货单
    */
    public function testdelivery(){

        #发货单添加
        $items = array(
            array('bn'=>'pb001','name'=>'pb001-name','num'=>'1','price'=>'2.0','sale_price'=>'1'),
            array('bn'=>'pb002','name'=>'pb002-name','num'=>'2','price'=>'3.0','sale_price'=>'1'),
        );
        #收货人地址
        $consignee = array(
            'name' => '收货人姓名',    
            'zip' => '330039',
            'tel' => '收货人电话',
            'mobile' => '收货人手机',
            'province' => '收货人所在省',
            'city' => '收货人所在市',
            'district' => '收货人所在县（区）',
            'addr' => '收货人地址',
            'email' => '收货人邮箱',
            'time' => '要求到货时间',
        );
        $sdf = array(
            'outer_delivery_bn' => 'pu001',
            'order_bn' => 'L01|201',
            'total_amount' => '105',
            'discount_fee' => '10',
            'total_goods_amount' => '120',
            'goods_discount_fee' => '10',
            'create_time' => date('Y-m-d H:i:s'),
            'memo' => '备注啊',
            'shop_type' => 'taobao',
            'shop_code' => 'taobao',
            'logistics_costs' => '5',
            'logi_code' => 'STO',
            'is_order_invoice' => '',
            'is_wms_invoice' => '',
            'invoice' => '',
            'is_protect' => 'true',
            'cost_protect' => '2',
            'is_cod' => 'true',
            'cod_fee' => '3',
            'consignee' => $consignee,
            'storage_code' => 'storage_code',
            'print_remark' => '',
            'dispatch_time' => '',
            'items' => $items
        );
        $c = 'middlewaretest_delivery';
        $m = 'create_callback';
        $p = array('wo le ge qu');
        //$rs = $this->_rpc->setUserCallback($c,$m,$p)->request('delivery_create',$sdf,$sync=false);

        #发货单取消
        $sdf = array(
            'outer_delivery_bn' => 'pu001', 
            'branch_bn' => 'branch'
        );
        //$rs = $this->_rpc->request('delivery_cancel',$sdf,$sync=true);
        //print_r($rs);exit;
        $repeat = array('task00001','task00002','task00003','task00004','task00005','task00006','task00007','task00008','task00009','task00010','task00011','task00012','task00013','task00014','task00015');
        
        #发货单状态回传
        $item = array(
            array('39036-730-013'=>'pbn1','num'=>'1'),
            
        );
        $sdf = array(
           'logistics'=>'',
'volume'=>'',
'remark'=>'',
//'task'=>'1033933038-57229562',
'logi_no'=>'',
'weight'=>'',
'node_version'=>'',
'app_id'=>'ecos.ome',
'delivery_bn'=>'1404291100045',
'item'=>'[]',
'status'=>'ACCEPT',
'warehouse'=>'SHXJ',
'operate_time'=>'2014-04-29 14:10:47',
        );
        $rpcObj = $this->_rpc;
       //$rs = $rpcObj->response('wms.delivery.status_update',$sdf);
        //print_r(is_array($rs) ? $rs : json_decode($rs,1));
        $url = 'http://api.log.taoex.com/index.php';
        $data = array (
  'data' => 
  array (
    'status' => 'fail',
    'msg' => '未知的发货单操作通知行为',
  ),
  'filter' => 
  array (
    'log_id' => '8e3340d383b01cf0c3b438e88088ccde',
  ),
);
        $response = kernel::single('ome_apilog_request_apilog')->request('tg.api.update',$url,$data);
        var_dump($response);
    }

}
