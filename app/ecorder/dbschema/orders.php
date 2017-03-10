<?php

$db['orders'] = array(
    'columns' =>
    array(
        'order_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'order_bn' =>
        array(
            'type' => 'varchar(32)',
            'required' => true,
            'default' => 0,
            'label' => '订单号',
            'is_title' => true,
            'width' => 140,
            'searchtype' => 'head',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 10,
        ),
        'member_id' =>
        array(
            'type' => 'table:members@taocrm',
            //'type' => 'int',
            'label' => '客户帐号',
            'width' => 100,
            'editable' => false,
            'searchtype' => 'has',
            //'filtertype' => 'normal',
            //'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 20,
        ),
        'is_refund' =>array (
      		'type' =>   array (
	        0 => '否',
	        1 => '是',
	         ),
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'label' => '是否冲红销售',
            'width' => 140,
            'order' => 100,
      ),
        'refund_order_bn' =>
        array(
            'type' => 'varchar(32)',
            'required' => false,
            'default' => 0,
            'label' => '冲红订单号',
            'width' => 140,
            'editable' => false,
            'in_list' => true,
            'order' => 100,
        ),
         'consumer_terminal' =>
        array(
            'type' => 'varchar(32)',
            'default' => '',
            'required' => false,
            'label' => '消费终端',
            'width' => 140,
            'editable' => false,
            'in_list' => true,
            'order' => 120,
        ),
        'op_name' =>
        array(
            'type' => 'varchar(32)',
            'default' => '',
            'required' => false,
            'label' => '操作人',
            'width' => 140,
            'editable' => false,
            'in_list' => true,
            'order' => 140,
        ),
        'confirm' =>
        array(
            'type' => 'tinybool',
            'default' => 'N',
            'required' => false,
            'label' => '确认状态',
            'width' => 75,
            'hidden' => true,
            'editable' => false,
        ),
        'process_status' =>
        array(
            'type' =>
            array(
                'unconfirmed' => '未确认',
                'confirmed' => '已确认',
                'splitting' => '部分拆分',
                'splited' => '已拆分完',
                'cancel' => '取消',
            ),
            'default' => 'unconfirmed',
            'required' => false,
            'label' => '确认状态',
            'width' => 70,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'status' =>
        array(
            'type' =>
            array(
                'active' => '活动订单',
                'dead' => '已作废',
                'finish' => '已完成',
            ),
            'default' => 'active',
            'required' => false,
            'label' => '订单状态',
            'width' => 75,
            'hidden' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'order' => 35,
        ),
        'pay_status' =>
        array(
            'type' =>
            array(
                0 => '未支付',
                1 => '已支付',
                2 => '处理中',
                3 => '部分付款',
                4 => '部分退款',
                5 => '全额退款',
            ),
            'default' => '0',
            'required' => true,
            'label' => '付款状态',
            'width' => 75,
            'editable' => false,
            //'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'ship_status' =>
        array(
            'type' =>
            array(
                0 => '未发货',
                1 => '已发货',
                2 => '部分发货',
                3 => '部分退货',
                4 => '已退货',
            ),
            'default' => '0',
            'required' => true,
            'label' => '发货状态',
            'width' => 75,
            'editable' => false,
            //'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'is_delivery' =>
        array(
            'type' => 'tinybool',
            'default' => 'Y',
            'required' => true,
            'editable' => false,
        ),
        'shipping' =>
        array(
            'type' => 'varchar(100)',
            'label' => '配送方式',
            'width' => 75,
            'editable' => false,
            'sdfpath' => 'shipping/shipping_name',
            'in_list' => true,
        ),
        'payment' =>
        array(
            'type' => 'varchar(100)',
            'label' => '支付方式',
            'width' => 65,
            'editable' => false,
            //'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
            'sdfpath' => 'payinfo/pay_name',
        ),
        'weight' =>
        array(
            'type' => 'money',
            'editable' => false,
        ),
        'tostr' =>
        array(
            'type' => 'longtext',
            'editable' => false,
            'sdfpath' => 'title',
        ),
        'item_num' =>
        array(
            'type' => 'number',
            'editable' => false,
            'label' => '商品数量',
        ),
        'skus' =>
        array(
            'type' => 'number',
            'editable' => false,
            'label' => '商品SKU数量',
        ),
        'createtime' =>
        array(
            'type' => 'time',
            'label' => '下单时间',
            'width' => 130,
            'editable' => false,
            'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 40,
        ),
        'pay_time' =>
        array(
            'type' => 'time',
            'label' => '支付时间',
            'width' => 130,
            'editable' => false,
            //'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 50,
        ),
        'delivery_time' =>
        array(
            'type' => 'time',
            'label' => '发货时间',
            'width' => 130,
            'editable' => false,
            //'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
        ),
        'finish_time' =>
        array(
            'type' => 'time',
            'label' => '完成时间',
            'width' => 130,
            'editable' => false,
            //'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
        ),
        'last_modified' =>
        array(
            'label' => '最后更新时间',
            'type' => 'last_modify',
            'width' => 130,
            'editable' => false,
            'in_list' => true,
        ),
        'shop_id' =>
        array(
            'type' => 'table:shop@ecorder',
            'label' => '来源店铺',
            'width' => 120,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
        ),
        'shop_type' =>
        array(
            'type' => 'varchar(50)',
            'label' => '店铺类型',
            'width' => 75,
            'editable' => false,
            'in_list' => true,
            //'filtertype' => 'normal'
        ),
        'ip' =>
        array(
            'type' => 'varchar(15)',
            'editable' => false,
        ),
        'ship_name' =>
        array(
            'type' => 'varchar(50)',
            'label' => '收货人',
            'sdfpath' => 'consignee/name',
            'width' => 60,
            'searchtype' => 'head',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 25,
        ),
        'ship_area' =>
        array(
            'type' => 'region',
            'label' => '收货地区',
            'width' => 170,
            'editable' => false,
            'filtertype' => 'yes',
            'sdfpath' => 'consignee/area',
        ),
        'ship_addr' =>
        array(
            'type' => 'varchar(100)',
            'label' => '收货地址',
            'width' => 180,
            'editable' => false,
            //'filtertype' => 'normal',
            'sdfpath' => 'consignee/addr',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 27,
        ),
        'ship_zip' =>
        array(
            'type' => 'varchar(20)',
            'editable' => false,
            'sdfpath' => 'consignee/zip',
        ),
        'ship_tel' =>
        array(
            'type' => 'varchar(30)',
            'label' => '收货人电话',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'sdfpath' => 'consignee/telephone',
            'in_list' => true,
            
        ),
        'ship_email' =>
        array(
            'type' => 'varchar(150)',
            'editable' => false,
            'sdfpath' => 'consignee/email',
        ),
        'ship_time' =>
        array(
            'type' => 'varchar(50)',
            'editable' => false,
            'sdfpath' => 'consignee/r_time',
        ),
        'ship_mobile' =>
        array(
            'label' => '收货人手机',
            'hidden' => true,
            'type' => 'varchar(50)',
            'editable' => false,
            'width' => 100,
            'sdfpath' => 'consignee/mobile',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 29,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'head',
        ),
        'cost_item' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'label' => '商品金额',
            'width' => 75,
        ),
        'is_tax' =>
        array(
            'type' => 'bool',
            'default' => 'false',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'label' => '是否开发票',
            'width' => 80,
        ),
        'cost_tax' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'width' => 65,
            'label' => '税金',
        ),
        'tax_company' =>
        array(
            'type' => 'varchar(255)',
            'editable' => false,
            'sdfpath' => 'tax_title',
        ),
        'cost_freight' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => false,
            'label' => '配送费用',
            'width' => 70,
            'editable' => false,
            //'filtertype' => 'number',
            'sdfpath' => 'shipping/cost_shipping',
            'in_list' => true,
        ),
        'is_protect' =>
        array(
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'editable' => false,
            'sdfpath' => 'shipping/is_protect',
        ),
        'cost_protect' =>
        array(
            'type' => 'money',
            'default' => '0',
            'label' => '保价费用',
            'required' => false,
            'editable' => false,
            'sdfpath' => 'shipping/cost_protect',
        ),
        'is_cod' =>
        array(
            'type' => 'bool',
            'required' => false,
            'default' => 'false',
            'editable' => false,
            'label' => '货到付款',
            'sdfpath' => 'shipping/is_cod',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 60,
        ),
        'cost_payment' =>
        array(
            'type' => 'money',
            'editable' => false,
            'sdfpath' => 'payinfo/cost_payment',
        ),
        'currency' =>
        array(
            'type' => 'varchar(8)',
            'editable' => false,
        ),
        'cur_rate' =>
        array(
            'type' => 'decimal(10,4)',
            'default' => '1.0000',
            'editable' => false,
        ),
        'score_u' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
        ),
        'score_g' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
        ),
        'discount' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
        ),
        'pmt_goods' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
        ),
        'pmt_order' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
        ),
        'total_amount' =>
        array(
            'type' => 'money',
            'default' => '0',
            'label' => '订单总额',
            'width' => 70,
            'editable' => false,
            'filtertype' => 'number',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'final_amount' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
            'sdfpath' => 'cur_amount',
        ),
        'payed' =>
        array(
            'type' => 'money',
            'default' => '0',
            'editable' => false,
            'label' => '已付金额',
            'width' => 75,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'custom_mark' =>
        array(
            'type' => 'longtext',
            'label' => '买家留言',
            'editable' => false,
        ),
        'mark_text' =>
        array(
            'type' => 'longtext',
            'label' => '订单备注',
            'editable' => false,
        ),
        'disabled' =>
        array(
            'type' => 'bool',
            'required' => true,
            'default' => 'false',
            'editable' => false,
        ),
        'mark_type' =>
        array(
            'type' => 'varchar(2)',
            'default' => 'b1',
            'required' => true,
            'label' => '订单备注图标',
            'hidden' => true,
            'width' => 85,
            'editable' => false,
            'in_list' => false,
        ),
        'tax_no' =>
        array(
            'type' => 'varchar(50)',
            'label' => '发票号',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'dt_begin' =>
        array(
            'type' => 'time',
            'label' => '分派开始时间',
            'editable' => false,
            'width' => 110,
            //'filtertype' => 'time',
            'filterdefault' => true,
        ),
        'is_anti' =>
        array(
            'type' => 'bool',
            'required' => false,
            'default' => 'false',
            'editable' => false,
        ),
        'group_id' =>
        array(
            //'type' => 'table:groups@ome',
            'type' => 'int unsigned',
            'label' => '确认组',
            'editable' => false,
            'width' => 90,
            //'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'op_id' =>
        array(
            //'type' => 'table:account@pam',
            'type' => 'int unsigned',
            'label' => '确认人',
            'editable' => false,
            'width' => 60,
            //'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'dispatch_time' =>
        array(
            'type' => 'time',
            'label' => '分派时间',
            'editable' => false,
            'width' => 130,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'order_limit_time' =>
        array(
            'type' => 'time',
            'editable' => false,
        ),
        'coupons_name' =>
        array(
            'type' => 'varchar(255)',
            'editable' => false,
        ),
        'refer_id' =>
        array(
            'type' => 'varchar(50)',
            'label' => '首次来源ID',
            'width' => 75,
            'editable' => false,
            //'filtertype' => 'normal',
        ),
        'refer_url' =>
        array(
            'type' => 'varchar(200)',
            'label' => '首次来源URL',
            'width' => 150,
            'editable' => false,
            //'filtertype' => 'normal',
        ),
        'refer_time' =>
        array(
            'type' => 'time',
            'label' => '首次来源时间',
            'width' => 110,
            'editable' => false,
            //'filtertype' => 'time',
        ),
        'c_refer_id' =>
        array(
            'type' => 'varchar(50)',
            'label' => '本次来源ID',
            'width' => 75,
            'editable' => false,
            //'filtertype' => 'normal',
        ),
        'c_refer_url' =>
        array(
            'type' => 'varchar(200)',
            'label' => '本次来源URL',
            'width' => 150,
            'editable' => false,
            //'filtertype' => 'normal',
        ),
        'c_refer_time' =>
        array(
            'type' => 'time',
            'label' => '本次来源时间',
            'width' => 110,
            'editable' => false,
            //'filtertype' => 'time',
        ),
        'abnormal' =>
        array(
            'type' => 'bool',
            'label' => '异常处理状态',
            'editable' => false,
            'filterdefault' => true,
            'in_list' => false,
            'default' => 'false',
        ),
        'print_finish' =>
        array(
            'type' => 'bool',
            'editable' => false,
            'default' => 'false',
            'required' => true,
        ),
        'source' =>
        array(
            'type' => 'varchar(50)',
            'default' => 'matrix',
            'editable' => false,
        ),
        'pause' =>
        array(
            'type' => 'bool',
            'default' => 'false',
            'editable' => false,
            'in_list' => false,
            'label' => '暂停',
        ),
        'is_modify' =>
        array(
            'type' => 'bool',
            'default' => 'false',
            'editable' => false,
            'in_list' => false,
            'label' => '已编辑',
            'width' => 60,
            'filterdefault' => true,
        ),
        'old_amount' =>
        array(
            'type' => 'money',
            'default' => '0',
            'editable' => false,
        ),
        'order_type' =>
        array(
            'type' => array(
                'normal' => '订单',
                'sale' => '销售单'
            ),
            'default' => 'normal',
            'editable' => false,
        ),
        'product_hash' =>
	    array (
	      'type' => 'char(32)',
	      'label' => '订单明细数据hash',
	      'editable' => false,
	    ),
	    'f_modified' =>
	    array (
	      'type' => 'time',
	      'label' => '前端最后修改时间',
	      'width' => 130,
	      'editable' => false,
	      //'filtertype' => 'time',
	      //'filterdefault' => true,
	    ),
	    'f_ship_time' =>
	    array (
	      'type' => 'time',
	      'label' => '前端发货时间',
	      'width' => 130,
	      'editable' => false,
	      //'filtertype' => 'time',
	      //'filterdefault' => true,
	    ),
        'state_id' =>
	    array (
	      'type' => 'number',
	      'label' => '省份ID',
	      'editable' => false,
	    ),
        'city_id' =>
	    array (
	      'type' => 'number',
	      'label' => '城市ID',
	      'editable' => false,
	    ),
        'district_id' =>
	    array (
	      'type' => 'number',
	      'label' => '区域ID',
	      'editable' => false,
	    ),
        'hour' =>
	    array (
	      'type' => 'number',
	      'label' => '下单时间(小时)',
	      'editable' => false,
	    ),
        'trade_type' =>
	    array (
            'type' => 'char(32)',
            'label' => '订单类型',
            'required' => false,
	    ),
        'step_trade_status' =>
	    array (
            'type' => 'char(32)',
            'label' => 'step_trade_status',
            'required' => false,
	    ),
        'step_paid_fee' =>
	    array (
            'type' => 'money',
            'label' => 'step_paid_fee',
            'required' => false,
	    ),
        'errortrade_desc' =>
	    array (
            'type' => 'varchar(500)',
            'label' => 'errortrade_desc',
            'required' => false,
	    ),
        'is_errortrade' =>
	    array (
            'type' => 'char(10)',
            'label' => 'is_errortrade',
            'required' => false,
	    ),
        'card_no' =>
	    array (
            'type' => 'varchar(30)',
            'label' => '会员卡号',
            'required' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 80,
	    ),
    ),
    'index' =>
    array(
        'ind_card_no' =>
        array(
            'columns' =>
            array(
                0 => 'card_no',
            ),
        ),
        'ind_trade_type' =>
        array(
            'columns' =>
            array(
                0 => 'trade_type',
            ),
        ),
        'ind_order_bn_shop' =>
        array(
            'columns' =>
            array(
                0 => 'order_bn',
                1 => 'shop_id',
            ),
            'prefix' => 'unique',
        ),
        'ind_order_bn' =>
        array(
            'columns' =>
            array(
                0 => 'order_bn',
            ),
        ),
        'ind_ship_status' =>
        array(
            'columns' =>
            array(
                0 => 'ship_status',
            ),
        ),
        'ind_pay_status' =>
        array(
            'columns' =>
            array(
                0 => 'pay_status',
            ),
        ),
        'ind_status' =>
        array(
            'columns' =>
            array(
                0 => 'status',
            ),
        ),
        'ind_process_status' =>
        array(
            'columns' =>
            array(
                0 => 'process_status',
            ),
        ),
        'ind_shop_type' =>
        array(
            'columns' =>
            array(
                0 => 'shop_type',
            ),
        ),
        'ind_is_cod' =>
        array(
            'columns' =>
            array(
                0 => 'is_cod',
            ),
        ),
        'ind_createtime' =>
        array(
            'columns' =>
            array(
                0 => 'createtime',
            ),
        ),
        'ind_ship_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'ship_mobile',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);