<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */

$db['edm_template_type'] = array(
	'columns' => array(
		'type_id' =>  array (
			'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
		'title' => array (
            'type' => 'varchar(100)',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => true,
            'label' => '分类名称',
            'filtertype' => true,
            'searchtype' => true,
            'required' => true,
            'searchtype' => 'has',
        	'order' => 10,
        ),
        'remark' => array(
            'type' => 'varchar(100)',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => true,
            'required' => true,
            'label' => '分类备注',
        	'order' => 20
        ),
        'create_time' => array(
            'in_list' => true,
            'default_in_list' => true,
            'type' => 'time',
            'label' => '添加时间',
        	'order' => 30
        ),
        'create_time' => 
		    array (
		      'type' => 'time',
		      'label' => '创建时间',
		      'width' => 130,
		      'editable' => false,
		      'in_list' => true,
		      'default_in_list' => true,
		      'order'=>4,
		    ),
	),
    'engine' => 'innodb',
);

