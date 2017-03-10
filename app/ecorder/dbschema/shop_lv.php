<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['shop_lv']=array (
    'columns' => 
    array (
        'lv_id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'name' => 
        array (
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'label' => '等级名称',
            'in_list' => true,
            'default_in_list' => true,
            'is_title' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 10,
        ),
       'amount_symbol' => 
        array (
            'type' =>array(
                'unlimited' => '无限制',
                'gthan' => '大于',
                'sthan' => '小于',
                'equal' => '等于',
                'gethan' => '大于等于',
        		'sethan' => '小于等于',
        		'between' => '介于'
            ),
            'default' =>'unlimited',
            'required' => false,
            'editable' => false,
            'label' => '消费金额条件',
            'in_list' => false,
            'default_in_list' => false,
            'is_title' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 10,
        ),
        'min_amount' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '最低消费额',
            'in_list' => false,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 20,
        ),
        'max_amount' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '最高消费额',
            'searchtype' => 'has',
            'width' => 100,
            'order' => 30,
        ),
        'buy_times_symbol' => 
        array (
            'type' =>array(
                'unlimited' => '无限制',
                'gthan' => '大于',
                'sthan' => '小于',
                'equal' => '等于',
                'gethan' => '大于等于',
        		'sethan' => '小于等于',
        		'between' => '介于'
            ),
            'default' =>'unlimited',
            'required' => false,
            'editable' => false,
            'label' => '消费次数条件',
            'is_title' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 10,
        ),
        'min_buy_times' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '最低消费次数',
            'in_list' => false,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 120,
            'order' => 40,
        ),
        'max_buy_times' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '最高消费次数',
            'in_list' => false,
            'default_in_list' => false,
            'searchtype' => 'has',
            'width' => 120,
            'order' => 50,
        ),
        'shop_id' => 
        array (
            'type' => 'table:shop@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '适用店铺',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 150,
            'order' => 60,
        ),
        'is_active' =>
        array (
            'type' => 'intbool',
            'required' => false,
            'default' => '1',
            'label' => '状态',
            'editable' => false,
        ),
        'is_default' =>
        array (
            'type' => 'intbool',
            'required' => false,
            'default' => '0',
            'label' => '是否默认等级',
            'editable' => false,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 70,
        ),
    ),
    'index' =>
    array (
        'ind_shop_id' =>
        array (
            'columns' =>
            array (
            0 => 'shop_id',
            ),
        ),
    ),
    'comment' => '店铺客户等级规则',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);