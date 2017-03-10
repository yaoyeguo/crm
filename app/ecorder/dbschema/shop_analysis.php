<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['shop_analysis']=array (
    'columns' => 
    array (
        'shop_id' =>
        array(
            'type' => 'table:shop@ecorder',
            'required' => true,
            'editable' => false,
        	'pkey' => true,
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
            'width' => 100,
            'order' => 20,
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
            'order' => 20,
        ),
        'refund_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '退款金额',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 10,
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
        'finish_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '交易成功金额',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
            'order' => 30,
        ),
        'finish_orders' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '交易成功订单数',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 40,
        ),
        'finish_members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '交易成功客户数',
            'in_list' => true,
            'width' => 140,
            'order' => 40,
        ),
        'finish_per_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '成功客单价',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 50,
        ),
        'unpay_orders' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '未付款订单数',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
            'order' => 60,
        ),
        'unpay_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '未付款金额',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 70,
        ),
        'unpay_members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '未付款客户数',
            'in_list' => true,
            'width' => 120,
            'order' => 60,
        ),
        'unpay_per_amount' => 
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '未付款客单价',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
            'order' => 80,
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
            'width' => 80,
            'order' => 90,
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
            'width' => 80,
            'order' => 100,
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
            'width' => 80,
            'order' => 110,
        ),
        'members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '客户数',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 120,
        ),
        'single_members' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '单次购买客户数',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 140,
            'order' => 130,
        ),
        'products' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '宝贝数',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 140,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '创建时间',
            'in_list' => true,
            'width' => 150,
            'order' => 150,
        ),
        'update_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '更新时间',
            'in_list' => true,
            'width' => 150,
            'order' => 160,
        ),
    ),
    'index' =>
    array (
        'ind_products' =>
        array (
            'columns' =>
            array (
            0 => 'products',
            ),
        ),
    ),
    'comment' => '店铺分析表',
    'engine' => 'innodb',
    'version' => '$Rev:  $', 
);

