<?php

$db['cache_dashboard'] = array(
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
        'day' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '天, 格式YYYYMMDD',
            'required' => false,
            'editable' => false,
        ),
        'bind_shop_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '绑定店铺数',
            'required' => false,
            'editable' => false,
        ),
        'unbind_shop_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '未绑定店铺数',
            'required' => false,
            'editable' => false,
        ),
        'weixin_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '微信关注数',
            'required' => false,
            'editable' => false,
        ),
        'once_buy_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '一次购买用户数',
            'required' => false,
            'editable' => false,
        ),
        'more_buy_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '多次购买用户数',
            'required' => false,
            'editable' => false,
        ),
        'total_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '总客户数',
            'required' => false,
            'editable' => false,
        ),
        'total_amount' =>
        array (
            'type' => 'decimal(10,2)',
            'default' => 0,
            'label' => '总成交金额',
            'required' => false,
            'editable' => false,
        ),
        'avg_member_amount' =>
        array (
            'type' => 'decimal(10,2)',
            'default' => 0,
            'label' => '平均客单价',
            'required' => false,
            'editable' => false,
        ),
        'avg_order_amount' =>
        array (
            'type' => 'decimal(10,2)',
            'default' => 0,
            'label' => '平均订单价',
            'required' => false,
            'editable' => false,
        ),
        'total_order_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '总成交订单数',
            'required' => false,
            'editable' => false,
        ),
        'total_marketing_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '营销客户数',
            'required' => false,
            'editable' => false,
        ),
        'total_marketing_buy_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '营销参与客户数',
            'required' => false,
            'editable' => false,
        ),
        'total_marketing_buy_order_amount' =>
        array (
            'type' => 'decimal(10,2)',
            'default' => 0,
            'label' => '营销下单金额',
            'required' => false,
            'editable' => false,
        ),
        'weixin_buy_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '微信参与客户数',
            'required' => false,
            'editable' => false,
        ),
        'active_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '活跃客户数',
            'required' => false,
            'editable' => false,
        ),
        'inactive_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '沉睡客户数',
            'required' => false,
            'editable' => false,
        ),
        'total_tag_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '客户标签数',
            'required' => false,
            'editable' => false,
        ),
        'unorder_member_cnt' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '未下单客户',
            'required' => false,
            'editable' => false,
        ),
        'avg_member_goods_cnt' =>
        array (
            'type' => 'decimal(10,2)',
            'default' => 0,
            'label' => '人均成交件数',
            'required' => false,
            'editable' => false,
        ),
        'avg_member_order_cnt' =>
        array (
            'type' => 'decimal(10,2)',
            'default' => 0,
            'label' => '人均成交笔数',
            'required' => false,
            'editable' => false,
        ),
        'avg_buy_times_cnt' =>
        array (
            'type' => 'decimal(10,2)',
            'default' => 0,
            'label' => '平均购买次数',
            'required' => false,
            'editable' => false,
        ),
        'avg_buy_days' =>
        array (
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '平均回购周期',
            'required' => false,
            'editable' => false,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'label' => '插入时间',
            'required' => false,
            'editable' => false,
        ),
    ),
    'index' =>
    array(
        'ind_day' =>
        array(
            'columns' =>
            array(
                0 => 'day',
            ),
        ),
    ),
    'comment' => '首页缓存',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
