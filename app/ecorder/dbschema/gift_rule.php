<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 赠品发放规则
$db['gift_rule']=array (
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
        'title' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'editable' => false,
        	'in_list' => true,
            'default_in_list' => true,
         	'label' => '规则名称',
         	'searchtype' => 'has',
            'filtertype' => 'normal',
        	'order' => 20
        ),
        'lv_id' => 
        array (
        	'type' => 'table:shop_lv@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '客户等级',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 100,
        	'order' => 40
        ),
        'gift_bn' =>
        array (
            'type' => 'varchar(100)',
            'required' => false,
            'editable' => false,
        	'in_list' => false,
            'default_in_list' => false,
         	'label' => '商家编码',
        	'order' => 30
        ),
        'gift_ids' =>
        array (
            'type' => 'varchar(500)',
            'required' => false,
            'editable' => false,
        	'in_list' => false,
            'default_in_list' => false,
         	'label' => '赠品ID',
        	'order' => 35
        ),
        'gift_num' =>
        array (
            'type' => 'varchar(500)',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'label' => '赠品数量',
            'order' => 35
        ),
        'shop_id' => 
        array (
            'type' => 'table:shop@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '适用店铺',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
        	'order' => 50
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'required' => true,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
        	'order' => 60
        ),
        'modified_time' => 
        array (
            'type' => 'time',
            'required' => true,
            'label' => '修改时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
        	'order' => 65
        ),
        'time_type' => 
        array (
            'type' => array(
                'sendtime' => '订单处理时间',
                'createtime' => '订单创建时间',
                'pay_time' => '订单付款时间',
                'other' => '其他',
            ),
            'required' => false,
            'editable' => true,
            'label' => '时间类型',
        	'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 67,
        ),
        'start_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => true,
            'label' => '开始时间',
        	'in_list' => false,
            'default_in_list' => false,
            'width' => 150,
            'order' => 70,
        ),
        'end_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => true,
            'label' => '结束时间',
        	'in_list' => false,
            'default_in_list' => false,
            'width' => 150,
            'order' => 80,
        ),
        'status' => 
        array(
        	'type' => array(
                '0' => '关闭',
                '1' => '开启',
            ),
            'default' => 1,
            'label'=>'规则状态',
            'width' => 70,
            'order' => 90,
        ),
        'filter_arr' => 
        array(
        	'type' => 'text',
            'default' => 1,
            'label'=>'促销条件',
        	'default_in_list' => false,
        	'in_list' => false,
            'order' => 150,
        ),
        'priority' => 
        array(
        	'type' => 'int(10)',
            'default' => 0,
            'label'=>'优先级',
        	'default_in_list' => true,
        	'in_list' => true,
            'width' => 60,
            'order' => 250,
        ),
    ), 
    'index' =>
    array (
        'ind_shop_gift_rule' =>
        array (
            'columns' =>
            array (
            0 => 'lv_id',
            1 => 'shop_id'
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);