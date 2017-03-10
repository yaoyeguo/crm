<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['exchange_orders']=array (
    'columns' =>
        array(
        'order_id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'member_id' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'required' => false,
            'label' => 'CRM客户ID',
        ),
        'order_bn' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '订单编号',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
            'order' => 10,
        ),
        'uname' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '买家帐号',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 20,
        ),
        'receiver' =>
        array (
            'type' => 'varchar(50)',
            'label' => '收货人',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 60,
            'order' => 30,
        ),
        'tel' =>
        array (
            'type' => 'varchar(20)',
            'label' => '电话号码',
        ),
        'mobile' =>
        array (
            'type' => 'bigint(11)',
            'label' => '手机号',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 90,
            'order' => 40,
        ),
        'state' =>
        array (
            'type' => 'varchar(20)',
            'label' => '省份',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 40,
            'order' => 50,
        ),
        'city' =>
        array (
            'type' => 'varchar(20)',
            'label' => '城市',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 40,
            'order' => 60,
        ),
        'area' =>
        array (
            'type' => 'varchar(50)',
            'label' => '区域',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 60,
            'order' => 70,
        ),
        'addr' =>
        array (
            'type' => 'varchar(255)',
            'label' => '收货地址',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 80,
        ),
        'goods_bn' =>
        array (
            'type' => 'varchar(50)',
            'label' => '商品编码',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 90,
        ),
        'goods_name' =>
        array (
            'type' => 'varchar(255)',
            'label' => '商品名称',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
            'order' => 100,
        ),
        'num' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'label' => '数量',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 60,
            'order' => 110,
        ),
        'pay_status' =>
        array (
            'type' => 'tinyint',
            'label' => '付款状态',
        ),
        'ship_status' =>
        array (
            'type' => 'tinyint',
            'label' => '发货状态',
        ),
        'status' =>
        array (
            'type' => array(
                'active' => '活动订单',
                'finish' => '已完成',
                'dead' => '已关闭',
            ),
            'label' => '订单状态',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 60,
            'order' => 120,
        ),
        'source' =>
        array (
            'type' => array(
                'ecshop' => '积分商城',
                'weixin' => '微信兑换',
            ),
            'label' => '订单来源',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 130,
        ),
        'shop_id' =>
        array (
            'type' => 'table:shop@ecorder',
            'label' => '店铺',
            'in_list' => true,
            'default_in_list' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 140,
        ),
        'create_time' =>
        array (
            'type' => 'time',
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 150,
        ),
        'modified_time' =>
        array (
            'type' => 'time',
            'label' => '修改时间',
        ),
        'buy_id' =>
        array (
            'type' => 'int unsigned',
            'label' => '积分换购活动id',
        ),
    ),
    'index' =>
    array(
        'ind_order_bn' =>
        array(
            'columns' =>
            array(
                0 => 'order_bn',
            ),
        ),
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
    ),
    'comment' => '积分兑换订单',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);