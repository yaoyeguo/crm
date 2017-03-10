<?php

$db['rebate_cycle']=array(
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
        'rebate_start_time' =>
        array (
            'type' => 'time',
            'required' => true,
            'label' => '周期开始时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'rebate_end_time' =>
        array (
            'type' => 'time',
            'required' => true,
            'label' => '周期结束时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 10,
        ),
        'rebate_number' =>
        array (
            'type' => 'number',
            'default' => 0,
            'label' => '本周期返利次数',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'order' =>20,
        ),
        'rebate_price' =>
        array(
            'type' => 'money',
            'required' => true,
            'label' => '本周期返利值',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 10,
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
            'default_in_list' => true,
            'width' => 140,
            'order' => 180,
        ),

    ),
    'index' =>array(),
    'comment' => '返利周期表',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);

