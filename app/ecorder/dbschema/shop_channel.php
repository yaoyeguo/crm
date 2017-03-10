<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['shop_channel']=array (
    'columns' => 
    array (
        'channel_id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'channel_bn' => 
        array (
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'label' => '分类编号',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 20,
        ),
        'channel_name' => 
        array (
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'label' => '分类名称',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 30,
            'is_title' => true,
        ),
        'orders' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '订单总数',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 40,
        ),
        'amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '总金额',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 50,
        ),
        'members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '客户数',
            'in_list' => true,
            'width' => 100,
            'order' => 50,
        ),
        'per_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '客单价',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 60,
        ),
        'finish_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '成功金额',
            'in_list' => true,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 70,
        ),
        'finish_orders' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '成功交易数',
            'in_list' => true,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 80,
        ),
        'finish_members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '成功客户数',
            'in_list' => true,
            'width' => 100,
            'order' => 80,
        ),
        'finish_per_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '成功客单价',
            'in_list' => true,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 90,
        ),
        'unpay_orders' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '未付款订单数',
            'in_list' => true,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 100,
        ),
        'unpay_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '未付款金额',
            'in_list' => true,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 110,
        ),
        'unpay_members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '未支付客户数',
            'in_list' => true,
            'width' => 100,
            'order' => 130,
        ),
        'unpay_per_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'label' => '未付款客单价',
            'in_list' => true,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 120,
            'order' => 120,
        ),
        'refund_orders' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '退款订单数',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 130,
        ),
        'refund_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '退款总金额',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 140,
        ),
        'refund_per_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '退款客单价',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 80,
            'order' => 10,
        ),
        'refund_members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '退款客户数',
            'in_list' => true,
            'width' => 100,
            'order' => 130,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 150,
            'order' => 150,
        ),
        'update_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '更新时间',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 150,
            'order' => 160,
        ),
        'is_fixed' => 
        array (
            'type' => 'intbool',
            'default' => '0',
            'required' => false,
            'editable' => false,
            'label' => '固定分类',
        ),
        'is_active' =>
        array (
            'type' => 'intbool',
            'required' => false,
            'default' => 1,
            'label' => '状态',
            'editable' => false,
        ),
    ),
    'index' =>
    array (
        'ind_channel_bn' =>
        array (
            'columns' =>
            array (
            0 => 'channel_bn',
            ),
        ),
    ),
    'comment' => '渠道分析',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);