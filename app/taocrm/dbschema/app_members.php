<?php

/**
 * 应用客户
 */
$db['app_members'] = array(
    'columns' => 
    array(
        'id' => array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'order' => 10
        ),
        'app_type' => array(
            'type' => array(
                'wwgenius' => '旺旺精灵',
            ),
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '应用类型',
            'order' => 20
        ),
        'shop_id' => array(
            'type' => 'table:shop@ecorder',
            'required' => false,
            'editable' => false,
            'label' => '来源店铺',
            'in_list' => false,
            'default_in_list' => false,
            'order' => 30
        ),
        'uname' => array(
            'type' => 'varchar(50)',
            'required' => true,
            'label' => '客户名',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 40
        ),
        'member_id' => array(
            'type' =>'int',
            'required' => false,
            'label' => '客户ID',
            'order' => 50,
            'default' => 0,
        ),
    ),
    'index' =>
    array(
        'ind_uname_shop_apptype' =>
        array(
            'columns' =>
            array(
                0 => 'uname',
                1 => 'shop_id',
                2 => 'app_type',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
