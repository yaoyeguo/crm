<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
$db['member_level'] = array(
    'columns' => array(
        'level_id' => array(
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'level_name' => array(
            'type' => 'varchar(20)',
            'default' => '',
            'required' => false,
            'label' => '等级名称',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'is_title' => true,
            'order'=>10,
        ),
        'create_time' => array(
            'type' => 'time',
            'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>20,
        ),
        'update_time' => array(
            'type' => 'time',
            'required' => false,
            'label' => '修改时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>30,
        ),
        'rule_type' => array (
            'type' => array(
                'pay' => '根据消费金额设定',
                'point' => '根据会员积分设定',
            ),
            'default' => 'pay',
            'label' => '规则类型',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 15,
        ),
        'count_member' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '当前累计客户',
            'width' => 70,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>40,
        ),
        'total_amount' => array (
            'type' => 'money',
            'label' => '累计成交金额',
            'default' => 0,
            'editable' => false,
            'width' => 70,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>50
        ),
        'average_amount' => array (
            'type' => 'money',
            'label' => '平均客单价',
            'default' => 0,
            'width' => 70,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>60
        ),
        'statistics_time' => array(
            'type' => 'time',
            'required' => false,
            'label' => '统计日期',
            'order'=>30,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>70
        ),
        'rule_point_month' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '积分统计时效月份',
        ),
        'rule_point_condition' => array (
            'type' => array(
                'between' => '介于',
                'nolimit' => '不限制',
            ),
            'label' => '积分条件',
        ),
        'rule_point_min' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '最小限制积分',
        ),
        'rule_point_max' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '最大限制积分',
        ),
        'rule_amount_month' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '成功交易金额月',
        ),
        'rule_amount_condition' => array (
            'type' => array(
                'between' => '介于',
                'nolimit' => '不限制',
            ),
            'label' => '成功交易金额条件',
        ),
        'rule_amount_min' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '最小限制金额',
        ),
        'rule_amount_max' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '最大限制金额',
        ),
        'rule_count_month' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '成功交易次数月',
        ),
        'rule_count_condition' => array (
            'type' => array(
                'between' => '介于',
                'nolimit' => '不限制',
            ),
            'label' => '成功交易次数条件',
        ),
        'rule_count_min' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '最小限制次数',
        ),
        'rule_count_max' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '最大限制次数',
        ),
        'rule_select' => array (
            'type' => array(
                'and' => '同时满足所有条件',
                'or' => '满足以上任一条件'
            ),
            'default' => 'or',
            'editable' => false,
            'label' => '规则条件选项',
        ),
        'rule_msg' => array (
            'type' =>'varchar(255)',
            'default' => '',
            'editable' => false,
            'label' => '规则描述',
        ),
    ),
    'engine' => 'innodb',
    'comment' => '全局客户等级',
    'version' => '$Rev:  $',
);
