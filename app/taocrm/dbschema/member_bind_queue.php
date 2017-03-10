<?php

$db['member_bind_queue']=array (
    'columns' =>
    array (
        'queue_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'bind_dimensions' =>
        array(
            'type' => 'varchar(50)',
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 10,
            'label' => '合并维度',
        ),
        'finish_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '处理时间',
            'width' => 140,
            'order' => 20,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 30,
        ),
        'is_send' =>
        array (
            'type' => array (
                'succ' => '处理成功',
                'fail' => '处理失败',
                'unsend' => '未处理',
                'sending' => '处理中',
            ),
            'default' => 'unsend',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '处理状态',
            'width' => 140,
            'order' => 10,
        ),
    ),
    'index' =>
    array(

    ),
    'comment' => '客户去重任务',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
