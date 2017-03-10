<?php
// 客户统计数据
$db['hits'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'market_id' =>
        array(
            'type' => 'int',
            'required' => false,
            'editable' => false,
            'label' => '营销ID',            
        ),
        'market_name' => 
        array(
        	'type' =>'char(100)',
            'required' => false,
            'label' => '营销名称',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'type' => 
        array(
        	'type' =>'char(10)',
            'required' => false,
            'label' => '类型',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'shop_id' => 
        array(
        	'type' =>'char(100)',
            'required' => false,
            'label' => '店铺ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'shop_name' => 
        array(
        	'type' =>'char(100)',
            'required' => false,
            'label' => '店铺名称',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'created' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '创建时间',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' =>
    array(
        'ind_created' =>
        array(
            'columns' =>
            array(
                0 => 'created',
            ),
        ),'ind_market_id' =>
        array(
            'columns' =>
            array(
                0 => 'market_id',
            ),
        ),
        'ind_shop_id' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
            ),
        ),
        'ind_type' =>
        array(
            'columns' =>
            array(
                0 => 'type',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 
