<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 店铺积分规则
 
$db['shop_credit']=array (
    'columns' => 
    array (
        'rule_id' =>
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
            'label' => '规则名称',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 120,
            'order' => 10,
        ),
        'is_active' =>
        array (
            'type' => 'intbool',
            'required' => false,
            'default' => 1,
            'label' => '状态',
            'editable' => false,
        ),
        'order_type' => 
        array (
            'type' =>array(
                'all' => '累计付款',
                'single' => '单笔付款',
            ),
            'default' =>'single',
            'required' => false,
            'editable' => false,
            'label' => '消费类型',
            'in_list' => false,
            'default_in_list' => false,
            'is_title' => true,
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
            'label' => '消费金额符号',
            'in_list' => false,
            'default_in_list' => false,
            'is_title' => true,
            //'searchtype' => 'has',
            'width' => 100,
            'order' => 10,
        ),
        'min_amount' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '最低消费',
            'in_list' => false,
            'default_in_list' => false,
            //'searchtype' => 'has',
            'width' => 100,
            'order' => 30,
        ),
        'max_amount' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '最高消费',
            'in_list' => false,
            'default_in_list' => false,
           // 'searchtype' => 'has',
            'width' => 100,
            'order' => 40,
        ),
        'cost_amount' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '每积分需要消费',
            'in_list' => true,
            'default_in_list' => true,
            //'searchtype' => 'has',
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
            //'searchtype' => 'has',
            'width' => 150,
            'order' => 60,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 70,
        ),
        'start_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '开始时间',
            'width' => 150,
            'order' => 80,
        ),
        'end_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '结束时间',
            'width' => 150,
            'order' => 90,
        ),
        'count_type' => 
        array (
            'type' =>array(
                'total_amount' => '订单金额',
                'payed' => '实付金额',
            ),
            'default' =>'payed',
            'required' => false,
            'editable' => false,
            'label' => '消费金额类型',
            'in_list' => true,
            'default_in_list' => true,
            'is_title' => true,
            'width' => 100,
            'order' => 100,
        ),
        'time_from' =>
        array (
            'type' => 'date',
            'editable' => false,
            'label' => '活动开始时间',
            'in_list' => true,
            'width' => 150,
            'order' => 110,
        ),
        'time_to' =>
        array (
            'type' => 'date',
            'editable' => false,
            'label' => '活动结束时间',
            'in_list' => true,
            'width' => 150,
            'order' => 120,
        ),
        'birthday_type' =>
        array (
            'type' =>array(
                '1' => '当天',
                '2' => '当月',
            ),
            'default' =>'1',
            'required' => false,
            'editable' => false,
            'label' => '生日送积分时间类型',
            'in_list' => true,
            'width' => 100,
            'order' => 130,
        ),
        'special_point_rule' =>
        array (
            'type' => 'varchar(15)',
            'default' =>'',//1=>活动送积分，2=>生日送积分
            'required' => false,
            'editable' => false,
            'label' => '特殊积分规则',
            'in_list' => true,
            'width' => 100,
            'order' => 140,
        ),
        'activity_point_times' =>
        array (
            'type' =>array(
                '1' => '1.5',
                '2' => '2',
                '3' => '3',
                '4' => '5',
            ),
            'required' => false,
            'editable' => false,
            'label' => '活动送积分的倍数',
            'in_list' => true,
            'width' => 100,
            'order' => 150,
        ),
      'birthday_point_times' =>
        array (
            'type' =>array(
                '1' => '1.5',
                '2' => '2',
                '3' => '3',
                '4' => '5',
            ),
            'required' => false,
            'editable' => false,
            'label' => '生日送积分的倍数',
            'in_list' => true,
            'width' => 100,
            'order' => 160,
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
    'engine' => 'innodb',
    'version' => '$Rev:  2015$',
);