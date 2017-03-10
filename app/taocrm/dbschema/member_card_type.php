<?php
/**
 * ShopEx
 *
 * @author shiyao
 * @email shiyao@shopex.cn
 * @copyright 2003-2014 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
$db['member_card_type'] = array(
	'columns' => array(
		'id' => array(
            'type' => 'bigint unsigned',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment',
		),
        'type_name' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '类型名称',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>10,
		),
		'type_code' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '会员卡类型编码',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>20,
		),
        'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>30,
		),
        'update_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '修改时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>40,
		),
		'current_card_no' => array(
            'type' => 'int unsigned',
			'required' => true,
	        'default' => 0,
		),
    ),
    'index' =>
    array(
       'ind_type_code' =>
        array(
            'columns' =>
            array(
                0 => 'type_code',
            ),
            'prefix' => 'unique',
        ),
    ),
	'engine' => 'innodb',
    'version' => '$Rev:  $',
);