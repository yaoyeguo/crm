<?php

$db['rebate']=array(
    'columns' =>
    array (
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'rebate_cycle_id' => array(
            'type' => 'int',
            'label' => '返利周期ID',
            'default' => 0,
            'required' => false,
            'editable' => false,
        ),
        'order_id' =>
        array (
            'type' => 'int',
            'required' => false,
            'label' => '订单编号',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'order' => 10,
        ),
        'member_id' =>
        array (
            'type' => 'int',
            'default' => 0,
            'label' => '消费者id',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 110,
            'order' =>20,
        ),
        'uname' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '客户名称',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 10,
            'default'=>'',
        ),
        'mobile' =>
        array (
            'type' => 'varchar(20)',
            'label' => '手机号',
            'editable' => false,
            'searchtype' => 'nequal',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 105,
            'order' => 90,
        ),
        'rebate_amount' =>
        array(
            'type' => 'money',
            'default' => '0',
            'label' => '返利值',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'order' => 12,
        ),
        'rebate_type' =>
        array(
            'type' => array(
                '0'=>'-',
                '1'=>'金额',
                '2'=>'积分'
            ),
            'default'=>'0',
            'label' => '返利类型',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'order' => 12,
        ),
        'is_send' =>
            array(
            'type' => array(
                'false'=>'否',
                'true'=>'是'
            ),
            'label' => '是否发放',
            'default'=>'false',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'order' => 12,
        ),
//        'rebate_number' =>
//        array(
//            'type' => 'number',
//            'label' => '返利数量',
//            'required' => false,
//            'editable' => false,
//            'in_list' => true,
//            'default_in_list' => true,
//            'width' => 110,
//            'order' => 12,
//        ),
        'send_time' =>
        array (
            'type' => 'time',
            'label' => '发送时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 140,
            'order' => 160,
        ),
        'create_time' =>
        array (
            'type' => 'time',
            'label' => '创建时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 140,
            'order' => 180,
        ),
        'origin_modified' =>
        array(
            'label' => '推荐人关系生成时间',
            'type' => 'time',
            'width' => 130,
            'editable' => false,
            'in_list' => true,
        ),
    ),
    'comment' => '返利发放记录表',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);

