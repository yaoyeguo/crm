<?php
$db['members_rule_log']=array (
    'columns' => 
    array (
        'log_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'type' => 
        array (
            'type' => array(
                'pay' => '根据消费金额设定',
                'point' => '根据会员积分设定',
            ),
            'default' => 'pay',
            'label' => '积分类型',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 15,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 60,
        ),
    ),
    'index' =>
    array(
        'create_time' =>
        array(
            'columns' =>
            array(
                0 => 'create_time',
            ),
        ),
    ),
    'comment' => '会员等级规则设置日志',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
