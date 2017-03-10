<?php

// 交易评价信息
$db['trade_rates'] = array(
    'columns' =>
    array(
        'rate_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'order_id' =>
        array(
            'type' => 'int unsigned',
            'required' => false,
            'label' => '订单ID',
        ),
        'order_bn' =>
        array(
            'type' => 'varchar(32)',
            'required' => true,
            'default' => 0,
            'label' => '订单号',
            'is_title' => true,
            'width' => 125,
            'searchtype' => 'has',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'oid' => 
        array (
          'type' => 'varchar(50)',
          'required' => true,
          'default' => 0,
          'editable' => false,
        ),
        'role' =>
        array(
            'type' => array('seller'=>'卖家','buyer'=>'买家'),
            'default' => 'buyer',
            'required' => false,
            'label' => '评价者角色',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 60,
            'order' => 20,
        ),
        'nick' =>
        array(
            'type' => 'varchar(32)',
            'required' => false,
            'label' => '昵称',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'member_id' =>
        array(
            'type' => 'table:members@taocrm',
            'required' => false,
            'label' => '客户编号',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 40,
        ),
        'result' =>
        array(
            'type' => array('good'=>'好评','neutral'=>'中评','bad'=>'差评'),
            'required' => false,
            'label' => '评价',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 60,
            'order' => 50,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'label' => '创建时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 60,
        ),
        'created' =>
        array(
            'type' => 'time',
            'required' => false,
            'label' => '评价时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 60,
        ),
        'content' =>
        array(
            'type' => 'varchar(1000)',
            'required' => false,
            'label' => '评价内容',
            'editable' => false,
            'width' => 150,
            'order' => 70,
        ),
        'reply' =>
        array(
            'type' => 'varchar(10000)',
            'required' => false,
            'label' => '评价解释',
            'editable' => false,
        ),
        'shop_id' =>
        array(
            'type' => 'table:shop@ecorder',
            'required' => false,
            'label' => '来源店铺',
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 80,
        ),
        'channel_id' =>
        array(
            'type' => 'table:shop_channel@ecorder',
            'required' => false,
            'label' => '来源渠道',
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 150,
            'order' => 90,
        ),
    ),
    'index' =>
    array(
        'ind_shop_id' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
            ),
        ),
        'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
        'ind_order_id' =>
        array(
            'columns' =>
            array(
                0 => 'order_id',
            ),
        ),
        'ind_nick' =>
        array(
            'columns' =>
            array(
                0 => 'nick',
            ),
        ),
        'ind_oid' =>
        array(
            'columns' =>
            array(
                0 => 'oid',
            ),
            'prefix' => 'UNIQUE',
        ),
    ),
    'comment' => '交易评价信息',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);