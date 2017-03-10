<?php

//会员自定义属性

$db['member_attr']=array (
    'columns' => 
    array (
        'attr_id' => array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'member_id' => array(
            'type' => 'int unsigned',
            'label' => '会员ID',
            'required' => true,
        ),
        'shop_id' => 
        array (
            'type' => 'char(32)',
            'label' => '店铺ID',
            'default' => 'all',
            'editable' => false,
        ),
        'attr1' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性1',
            'in_list' => false,
        ),
        'attr2' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性2',
            'in_list' => false,
        ),
        'attr3' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性3',
            'in_list' => false,
        ),
        'attr4' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性4',
            'in_list' => false,
        ),
        'attr5' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性5',
            'in_list' => false,
        ),
        'attr6' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性6',
            'in_list' => false,
        ),
        'attr7' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性7',
            'in_list' => false,
        ),
        'attr8' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性8',
            'in_list' => false,
        ),
        'attr9' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性9',
            'in_list' => false,
        ),
        'attr10' => array (
            'type' => 'varchar(50)',
            'label' => '自定义属性10',
            'in_list' => false,
        ),
        'create_time' => array (
            'type' => 'time',
            'label' => '创建时间',
            'in_list' => false,
        ),
        'update_time' => array (
            'type' => 'time',
            'label' => '修改时间',
            'in_list' => false,
        ),
    ),
    'index' => 
    array(
        'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
        'ind_shop_member' =>
        array (
            'columns' =>
            array(
                0 => 'member_id',
                1 => 'shop_id',
            ),
            'prefix' => 'unique',
        ),
    ),
    'comment' => '会员自定义属性',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
