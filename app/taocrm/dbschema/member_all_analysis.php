<?php
// 全局客户统计数据
$db['member_all_analysis'] = array(
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
        'member_id' => 
        array(
            'type' =>'table:members@taocrm',
            'required' => false,
            'label' => '客户',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 110,
            'order' => 12,
        ),
        'total_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '订单总数',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 70,
            'order' => 70,
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
            'label' => '平均订单价',
            'in_list' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'buy_freq' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '购买频次(天)',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 30,
        ),
        'avg_buy_interval' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均购买间隔(天)',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 40,
        ),
        'buy_month' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '购买月数',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'buy_skus' =>
        array(
            'type' => 'number',
            'default' => 0,
            'editable' => false,
            'label' => '下单商品种数',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'buy_products' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '下单商品总数',
            'editable' => false,
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'avg_buy_skus' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均下单商品种数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'avg_buy_products' =>
        array(
            'type' => 'avg',
            'default' => 0,
            'label' => '平均下单商品件数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'finish_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '成功的订单数',
            'editable' => false,
            'filtertype' => 'normal',
            'in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'finish_total_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '成功的订单金额',
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
            'label' => '成功的平均订单价',
            'editable' => false,
            'in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
        'unpay_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '未付款订单数',
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
            'label' => '未付款订单金额',
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
            'label' => '未支付平均订单价',
            'editable' => false,
            'in_list' => true,
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
        'lv_id' =>
        array(
            'type' => 'table:shop_lv@ecorder',
            'editable' => false,
            'label' => '客户等级',
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'width' => 80,
            'order' => 120,
        ),
        'first_buy_time' =>
        array(
            'type' => 'time',
            'label' => '第一次下单时间',
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => 'false',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 124,
        ),
        'last_buy_time' =>
        array(
            'type' => 'time',
            'label' => '最后下单时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 125,
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
        'month3_finish_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '近3个月成功金额',
            'editable' => false,
            'in_list' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 100,
            'order' => 90,
        ),
        'month3_finish_orders' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '近3个月成功订单数',
            'editable' => false,
            'in_list' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 100,
            'order' => 100,
        ),
        'active_times' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '参加营销次数',
        ),
        'active_buy_times' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '营销成功购买次数',
        ),
    ),
    'index' =>
    array(
        'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
            'prefix' => 'UNIQUE',
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

