<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['relate_products']=array (
    'columns' => 
    array (
        'relate_id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'label' => 'ID',
            'extra' => 'auto_increment',
        ),
        'goods_a' => 
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'editable' => false,
            'label' => '商品A',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 20,
        	'default' => 0,
        ),
        'goods_b' =>
        array (
            'type' => 'varchar(32)',
            'required' => true,
            'default' => '0',
            'label' => '商品B',
            'width' => 75,
            'editable' => false,
            'order'=>3,
        ),
        'times' => 
        array (
            'type' => 'number',
            'required' => true,
            'editable' => false,
            'label' => '关联次数',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 30,
        	'default' => 0,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'required' => true,
            'editable' => false,
            'label' => '关联时间',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 30,
            'default' => 0,
        ),
        'update_time' => 
        array (
            'type' => 'time',
            'required' => true,
            'editable' => false,
            'label' => '更新时间',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 30,
        	'default' => 0,
        ),
    ),
    'index' =>
    array (
        'ind_relate_id' =>
        array (
            'columns' =>
            array (
            0 => 'relate_id',
            ),
        ),
        'ind_goods_a_b' =>
        array (
            'columns' =>
            array (
             0 => 'goods_a',
             1 => 'goods_b',
            ),
            'prefix' => 'unique',
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);