<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 积分兑换比例
 
$db['credit_exchange']=array (
    'columns' => 
    array (
        'id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'consume_points_cti' =>
        array (
            'type' => 'number',
            'required' => false,
            'editable' => false,
            'label' => '消费转互动的消费积分',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 120,
            'order' => 10,
        ),
        'interaction_points_cti' =>
        array (
            'type' => 'number',
            'required' => false,
            'editable' => false,
            'label' => '消费转互动的互动积分',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 120,
            'order' => 20,
        ),
        'interaction_points_itc' =>
        array (
            'type' => 'number',
            'required' => false,
            'editable' => false,
            'label' => '互动转消费的互动积分',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 130,
            'order' => 30,
        ),
        'consume_points_itc' =>
        array (
            'type' => 'number',
            'required' => false,
            'editable' => false,
            'label' => '互动转消费的消费积分',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 130,
            'order' => 40,
        ),
        'op_user_id' =>array (
            'type' => 'number',
            'required' => true,
            'label' => '操作人ID',
            'width' => 110,
            'editable' => false,
            'default' => 0,
        ),
        'op_name' =>array (
            'type' => 'varchar(30)',
            'label' => '操作人',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 50,
            'width' => 110,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 60,
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);