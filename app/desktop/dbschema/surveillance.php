<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['surveillance']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'task_name' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'label' => '任务名',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 180,
            'order' => 10,
        ),
        'cycle' =>
        array(
            'type' => 'varchar(20)',
            'label' => '周期',
            'default' => '',
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 20,
        ),
        'begin_time' =>
        array (
            'default' => 0,
            'type' => 'time',
            'label' => '开始时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 30,
        ),
        'end_time' =>
        array (
            'default' => 0,
            'type' => 'time',
            'label' => '结束时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 40,
        ),
        'time_consuming' =>
        array(
            'type' => 'number',
            'label' => '耗时(秒为单位)',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 50,
        ),
        'task_desc' =>
        array (
            'type' => 'varchar(200)',
            'required' => true,
            'default' => '',
            'label' => '任务描述',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 200,
            'order' => 60,
        ),
    ),

    'engine' => 'innodb',
    'version' => '$Rev:  $',
);