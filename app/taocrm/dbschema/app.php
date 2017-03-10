<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 店铺积分规则
 
$db['app']=array (
    'columns' => 
    array (
        'id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        	'order' => 10
        ),
        'app_type' =>
        array(
        	'type' =>
            array(
                'wwgenius' => '旺旺精灵',
            ),
            'required' => true,
            'editable' => false,
        	'in_list' => true,
            'default_in_list' => true,
         	'label' => '应用类型',
        	'order' => 20
        ),
        'app_name' => 
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'editable' => false,
            'label' => '应用名称',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
        	'order' => 40
        ),
        'app_desc' => 
        array (
            'type' => 'text',
            'required' => false,
            'label' => '应用描述',
            'in_list' => false,
            'default_in_list' => false,
        	'order' => 50
        ),
        'status' => array(
        	'type' => 'tinyint',
            'default' => 0,
            'label'=>'绑定状态',
        	'default_in_list' => true,
        	'in_list' => true
        ),
        'seller_nick' => array(
        	'type' => 'varchar(100)',
            'required' => false,
            'editable' => false,
            'label' => '卖家昵称',
            'in_list' => false,
            'default_in_list' => false,
        	'order' => 60
        ),
        'shop_id' => 
        array (
            'type' => 'table:shop@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '绑定店铺',
            'in_list' => false,
            'default_in_list' => false,
        ),
        
    ), 
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);