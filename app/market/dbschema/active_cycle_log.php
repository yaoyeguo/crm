<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['active_cycle_log']=array (
    'columns' =>
        array (
        'log_id' =>
            array (
                'type' => 'int unsigned',
                'required' => true,
                'pkey' => true,
                'extra' => 'auto_increment',
                'editable' => false,
            ),
        'active_id' =>
            array (
                'type' => 'table:active_cycle@market',
                'label' => '活动名称',
                'editable' => false,
                'searchtype' => 'has',
                'filtertype' => 'normal',
                'filterdefault' => 'true',
                'in_list' => true,
                'default_in_list' => true,
                'order'=>10,
                'width'=>250,
        ),
        'send_num' => 
        array (
            'type' => 'int(10)',
            'default' => 0,
            'label' => '客户总数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 50,
        ),
        'succ_num' => 
        array (
            'type' => 'int(10)',
            'default' => 0,
            'label' => '成功数量',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 50,
        ),
        'status' => 
        array (
            'type' => array(
                'fail'=>'失败',
                'succ'=>'成功',
                'retry'=>'重试',
                'close'=>'中断',
            ),
            'label' => '结果',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 150,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'label' => '发送时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 200,
        ),
        'remark' => 
        array (
            'type' => 'varchar(500)',
            'label' => '备注说明',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 150,
            'order' => 300,
        ),
    ),
    'index' =>array(
        'ind_active_id' =>
            array(
                'columns' =>
                    array(
                        0 => 'active_id',
                    ),
            ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);