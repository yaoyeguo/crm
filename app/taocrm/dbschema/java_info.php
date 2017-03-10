<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// java信息表
 
$db['java_info']=array (
    'columns' => 
    array (
        'java_id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        	'order' => 10
        ),
        'java_class' =>
        array (
            'type' => 'varchar(10)',
            'required' => true,
            'editable' => false,
            'label' => 'java类名',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
        	'order' => 20
        ),
        'java_version' => array(
        	'type' => 'varchar(10)',
            'required' => false,
            'editable' => false,
            'label' => 'java版本',
            'in_list' => false,
            'default_in_list' => false,
        	'order' => 30
        ),
        'last_modify_time' =>
        array (
            'default' => 0,
            'type' => 'time',
            'label' => '更新时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 140,
            'order' => 40,
        ),
    ), 
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);