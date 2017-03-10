<?php 

// 客户统计数据
$db['member_analysis_day'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'shop_id' =>
        array(
            'type' => 'varchar(64)',
            'label' => '来源店铺',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
            'order' => 20,
        ),
        'channel_id' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '来源渠道',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'total_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '订单总数',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'order' => 70,
        ),
        'total_members' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '客户总数',
            'editable' => false,
        ),
        'total_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '订单总金额',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 80,
        ),
        'total_per_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '订单客单价',
            'editable' => false,
            'in_list' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 90,
        ),
        'buy_skus' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '购买商品种数',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'buy_products' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '购买商品总数',
            'editable' => false,
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'avg_buy_skus' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均购买商品种数',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'avg_buy_products' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均购买商品件数',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'finish_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '完成的单数',
            'editable' => false,
            'filtertype' => 'normal',
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'finish_members' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '完成的客户总数',
            'editable' => false,
        ),
        'finish_total_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '成功的金额',
            'editable' => false,
            'hidden' => true,
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'finish_per_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '成功的客单价',
            'editable' => false,
            'in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'unpay_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '未付款单数',
            'width' => 80,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 100,
            'order' => 100,
        ),
        'unpay_amount' =>
        array(
            'label' => '未付款金额',
            'type' => 'money',
            'default' => 0,
            'editable' => false,
            'hidden' => true,
            'in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'unpay_per_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '未支付客单价',
            'editable' => false,
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'refund_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '退款订单数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'refund_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'editable' => false,
            'label' => '退款总金额',
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'failed_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '失败订单数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'failed_members' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '失败客户数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'failed_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'editable' => false,
            'label' => '失败总金额',
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'paid_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '付款订单数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'paid_members' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '付款客户数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'paid_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'editable' => false,
            'label' => '付款总金额',
            'filtertype' => 'normal',
            'filterdefault' => 'false',
        ),
        'update_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '更新时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 130,
        ),
		'c_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '统计时间',
        ),
        'c_date' =>
        array(
            'type' => 'varchar(10)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '统计日期',
        ),
        'c_week' =>
        array(
            'type' => 'varchar(10)',
            'required' => false,
            'editable' => false,
            'label' => '统计周',
        ),
        'c_month' =>
        array(
            'type' => 'varchar(10)',
            'required' => false,
            'editable' => false,
            'label' => '统计月',
        ),
        'c_year' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '统计年',
        ),
    ),
    'index' =>
    array(
     	'ind_shop_date' =>
        array(
            'columns' =>
            array(
                1 => 'shop_id',
                2 => 'c_date',
            ),
            'prefix' => 'UNIQUE',
        ),
        'ind_shop_id' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
            ),
        ),
        'ind_c_time' =>
        array(
            'columns' =>
            array(
                0 => 'c_time',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

