<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 店铺赠品 
$db['shop_gift']=array (
    'columns' => 
    array (
        'id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        	'order' => 10,
        ),
        'gift_bn' =>
        array (
            'type' => 'varchar(100)',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'label' => '商家编码',
            'width' => 140,
        	'order' => 20,
        ),
        'gift_name' =>
        array(
        	'type' => 'varchar(100)',
            'default' => '',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => true,
            'required' => false,
            'label' => '商品名称',
        	'filtertype'=>false,
            'searchtype' => 'has',
            'width' => 300,
        	'order' => 30,
        ),
        'gift_num' =>
        array(
        	'type' => 'number',
            'in_list' => true,
            'default' => 0,
            'default_in_list' => true,
            'required' => false,
            'label' => '赠品库存',
        	'filtertype'=>false,
            'searchtype'=>false,
            'width' => 80,
        	'order' => 40,
        ),

        'gift_price' =>
        array(
        	'type' => 'money',
            'in_list' => true,
            'default' => 0,
            'default_in_list' => true,
            'required' => false,
            'label' => '成本价',
            'width' => 80,
        	'order' => 45,
        ),
        'send_num' =>
        array(
        	'type' => 'number',
            'in_list' => true,
            'default' => 0,
            'default_in_list' => true,
            'required' => false,
            'label' => '已赠送数量',
        	'filtertype'=>false,
            'searchtype'=>false,
            'width' => 80,
        	'order' => 45,
        ),
        'is_del' =>
        array(
        	'type' => array(
                '0' => '启用',
                '1' => '禁用',
            ),
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'required' => false,
            'label' => '是否启用',
        	'filtertype'=>false,
            'searchtype'=>false,
            'width' => 80,
        	'order' => 50,
        ),
        'create_time' =>
        array(
        	'type' => 'time',
            'in_list' => true,
            'default_in_list' => false,
            'required' => false,
            'label' => '创建时间',
        	'filtertype'=>false,
            'searchtype'=>false,
            'width' => 150,
        	'order' => 60,
        ),
        'update_time' =>
        array(
        	'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'required' => false,
            'label' => '更新日期',
        	'filtertype'=>false,
            'searchtype' => false,
            'width' => 150,
        	'order' => 70,
        ),
        'shop_id' => 
        array (
            'type' => 'table:shop@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '来源店铺',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
        	'order' => 80
        ),
    ),
    
    'index' =>
    array (
        'ind_gift_bn' =>
        array (
            'columns' =>
            array (
                1 => 'gift_bn'
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);