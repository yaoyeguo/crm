<?php
// 客户统计数据
$db['middleware_fx_member_report'] = array(
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
        'member_id' => array (
            'type' =>'int',
            'required' => true,
            'label' => '客户ID',
            'editable' => false,
            'width' =>60,
            'is_title' => true,
            'orderby' => false,
            'order'=>10,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'mobile' => array (
	        'type' => 'varchar(30)',
	        'label' => '手机',
	        'sdfpath' => 'contact/phone/mobile',
	        'editable' => false,
	        'in_list' => true,
	        'default_in_list' => true,
	        'width' => 105,
	        'order' => 20,
	    ),
	    'ship_name' =>
        array(
            'type' => 'varchar(50)',
            'label' => '收货人',
            'sdfpath' => 'consignee/name',
            'width' => 60,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 30,
        ),
        'total_orders' => array (
            'type' => 'smallint',
            'label' => '订单总数',
            'editable' => false,
            'orderby' => false,
            'width' =>100,
            'order'=>60,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'total_amount' => array (
            'type' => 'money',
            'label' => '订单总金额',
            'editable' => false,
            'orderby' => false,
            'width' =>100,
            'order'=>70,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'first_buy_time' => array (
            'type' => 'time',
            'label' => '第一次下单时间',
            'editable' => false,
            'orderby' => false,
            'width' =>130,
            'order'=>80,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'last_buy_time' => array (
            'type' => 'time',
            'label' => '最后下单时间',
            'editable' => false,
            'orderby' => false,
            'width' =>130,
            'order'=>90,
            'in_list' => true,
            'default_in_list' => true,
        ),
        
    ),
); 

