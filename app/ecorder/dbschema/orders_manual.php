<?php

$db['orders_manual'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'order_id' =>
        array(
            'type' => 'int unsigned',
            'required' => false,
            'editable' => false,
            'label' => '订单ID',
        ),
        'order_bn' =>
        array(
            'type' => 'varchar(50)',
            'searchtype' => 'head',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '订单编号',
            'order' => 10,
            'width' => 180,
        ),
        'uname' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'searchtype' => 'head',
            'in_list' => true,
            'default_in_list' => true,
            'label' => '客户帐号',
            'order' => 20,
            'width' => 120,
        ),
        'receiver' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'head',
            'label' => '收货人',
            'order' => 30,
            'width' => 80,
        ),
        'mobile' =>
        array(
            'type' => 'varchar(11)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'head',
            'label' => '收货人手机',
            'order' => 50,
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
            'order' => 60,
        ),
        'op_name' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '操作人',
            'order' => 80,
            'width' => 80,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'time',
            'filterdefault' => true,
            'label' => '下单时间',
            'order' => 110,
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
    'comment' => '手工订单表',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);