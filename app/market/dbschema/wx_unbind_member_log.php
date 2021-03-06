<?php

$db['wx_unbind_member_log']=array (
    'columns' => 
    array (
        'id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'FromUserName' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '客户微信账号',
            'editable' => false,
            'width' => 120,
            'order' => 10,
        ),
        'mobile' =>
        array (
            'type' => 'varchar(30)',
            'label' => '手机号',
            'editable' => false,
            'width' => 105,
            'order' => 20,
        ),
        'passcode' =>
        array (
            'type' => 'varchar(10)',
            'label' => '验证码',
            'editable' => false,
            'width' => 105,
            'order' => 30,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '创建时间',
            'width' => 140,
            'order' => 40,
        ),
    ),
    'comment' => '手机号解绑会员日志',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
