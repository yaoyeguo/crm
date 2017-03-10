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

$db['edm_tclass'] = array(
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
        'is_fixed' => array(
            'type' => 'tinyint',
            'default' => 0,
            'label'=>'是否固定',
        ),
        'mbt_type_id' => array (
			'type' => 'number',
        	'default' => 0,
            'label'=>'模板堂分类ID',
        ),
	),
    'engine' => 'innodb',
);
