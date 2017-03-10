<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['active_abc']=array (
    'columns' => 
    array (
        'id' => 
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'active_id' => 
        array (
            'type' =>'table:active@market',
            'editable' => false,
            'label' => '营销活动',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>5,
        ),
        'total_members' => 
        array(
            'type' =>'int unsigned',
            'label' => '总客户数',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order'=>5,
            'width' => 110,
        ),
        'order_members' => 
        array(
            'type' =>'int unsigned',
            'label' => '下单客户数',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order'=>5,
            'width' => 110,
        ),
        'paid_members' => 
        array(
            'type' =>'int unsigned',
            'label' => '付款客户数',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order'=>5,
            'width' => 110,
        ),
        'finish_members' => 
        array(
            'type' =>'int unsigned',
            'label' => '成功订单客户数',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order'=>5,
            'width' => 110,
        ),
        'paid_amount' => 
        array(
            'type' =>'money',
            'label' => '付款订单金额',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order'=>5,
            'width' => 110,
        ),
        'total_amount' => 
        array(
            'type' =>'money',
            'label' => '订单总金额',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order'=>5,
            'width' => 110,
        ),
        'identify' => 
        array(
            'type' =>'varchar(3)',
            'label' => '标识符',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'order'=>5,
            'width' => 110,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'width' => 130,
            'label' => '创建时间',
            'editable' => false,
            'in_list' => true,
            'order'=>20,
        ),
        'modified_time' => 
        array (
            'type' => 'time',
            'label' => '更新时间',
            'width' => 130,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault' => true,
            'filtertype' => 'normal',
            'order'=>30,
        ),
        'end_time' => 
        array (
            'type' => 'time',
            'label' => '结束时间',
            'width' => 130,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault' => true,
            'filtertype' => 'normal',
            'order'=>30,
        ),
    ),
    'index' =>
    array(
        'ind_active_id' =>
        array(
            'columns' =>
            array(
                0 => 'active_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'comment' => '营销活动效果评估abc拆分数据',
    'version' => '$Rev:  $',
);