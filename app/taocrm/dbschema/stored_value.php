<?php
/**
 * ShopEx
 *
 * @author lb
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
$db['stored_value'] = array(
	'columns' => array(
		'id' => array(
            'type' => 'bigint unsigned',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment',
		),
		'member_id' => array(
            'type' => 'int',
            'label' => '客户ID',
            'default' => 0,
            'required' => false,
            'editable' => false,
        ),
        'shop_id' =>
        array (
            'type' => 'varchar(32)',
            'label' => '店铺ID',
            'required' => false,
            'in_list' => false,
        ),
        'stored_value' =>
        array (
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'label' => '储值',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 20,
        ),
        'stored_value_type' =>
        array (
            'type' => array(
                '0' => '手动调整',
                '1' => '推广返利',
            ),
            'default' => '0',
            'required' => false,
            'label' => '储值类型',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 15,
        ),
        'create_time' => array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order'=>60,
        ),
        'last_modification_time' => array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'label' => '最后修改时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order'=>60,
        ),
	),
	'index' =>
    array(
        'ind_member' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
    ),
	'engine' => 'innodb',
    'version' => '$Rev:  $',
);