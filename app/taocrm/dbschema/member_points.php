<?php
// 客户积分表
$db['member_points'] = array(
    'columns' =>
    array(
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'member_id' =>
        array(
            'type' => 'table:members@taocrm',
            'label' => '客户',
            'width' => 100,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order' => 20,
        ),
        'shop_id' =>
        array(
            'type' => 'table:shop@ecorder',
            'label' => '店铺',
            'width' => 120,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 30,
        ),
        'points' =>
        array(
            'type' => 'int(16)',
            'required' => false,
            'label' => '积分',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 85,
            'order' => 10,
        ),
        'points_type' =>
        array(
            'type' => array(
                'trade' => '交易积分',
                'wechat' => '微信积分',
                'manual' => '手动积分',
                'active' => '活动积分',
                'other' => '其它积分',
            ),
            'default' => 'other',
            'label' => '积分类型',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 120,
            'order' => 22,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'label' => '创建时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' =>false,
            'width' => 140,
            'order' => 160,
        ),
        'modified_time' => 
        array (
            'type' => 'time',
            'label' => '更新时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' =>false,
            'width' => 140,
            'order' => 160,
        ),
        'invalid_time' => 
        array (
            'type' => 'time',
            'label' => '过期时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' =>false,
            'width' => 140,
            'order' => 160,
        ),
    ),
    'index' =>
    array(
        'ind_modified_time' =>
        array(
            'columns' =>
            array(
                0 => 'modified_time',
            ),
        ),
        'ind_invalid_time' =>
        array(
            'columns' =>
            array(
                0 => 'invalid_time',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 

