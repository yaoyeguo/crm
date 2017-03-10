<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * 积分兑换日志
 */
 
$db['exchange_order']=array (
    'columns' => 
    array (
        'order_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'item_id' =>
        array (
            'type' => 'table:exchange_items@market',
            'label' => '兑换名称',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'width' => 260,
            'order' => 10,
        ),
        'member_id' =>
        array (
            'type' => 'table:members@taocrm',
            'label' => '客户名',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'width' => 120,
            'order' => 20,
        ),
        'payment' => 
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '消费积分',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'num' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '兑换数量',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 40,
        ),
        'item_relate_id' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '优惠券原始ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 80,
            'order' => 50,
        ),
        'item_type' => 
        array (
            'type' => array('coupon'=>'优惠券','gift'=>'礼品'),
            'default' => 'coupon',
            'required' => false,
            'label' => '兑换类型',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 100,
            'order' => 60,
        ),
        'shop_id' =>
        array (
            'type' => 'table:shop@ecorder',
            'label' => '来源店铺',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 180,
            'order' => 70,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 80,
        ),
        'is_active' =>
        array(
            'type' => 'intbool',
            'default' => '1',
            'required' => false,
            'editable' => false,
            'label' => '状态',
        ),
        'op_user' =>
        array(
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'label' => '操作人',
            'width' => 90,
            'order' => 90,
        ),
        'status' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '兑换状态',
            'width' => 90,
            'order' => 100,
        ),
        'remark' =>
        array(
            'type' => 'varchar(200)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'label' => '备注',
            'width' => 150,
            'order' => 110,
        ),
        'user_ip' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => 'IP地址',
        ),
    ),
    'index' =>
    array(
        'ind_order_id' =>
        array(
            'columns' =>
            array(
                0 => 'order_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'comment' => '优惠券兑换日志',
);