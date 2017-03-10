<?php

$db['rebate_rule']=array(
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
        'conf' =>
        array (
            'type' => 'varchar(500)',
            'label' => '配置信息',
            'required' => false,
            'editable' => false,
        ),
        'op_user' =>
        array (
            'type' => 'varchar(32)',
            'label' => '操作人',
            'required' => false,
            'editable' => false,
        ),
        'create_time' =>
        array(
            'type' => 'datetime',
            'default' => 0,
            'label' => '创建时间',
            'required' => false,
            'editable' => false,
        ),
        'is_del' =>
        array(
            'type' => 'tinyint',
            'default' => 0,
            'label' => '是否删除',
            'required' => false,
            'editable' => false,
        ),
    ),
    'index' =>
    array(
        
    ),
    'comment' => '返点规则设置',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);

