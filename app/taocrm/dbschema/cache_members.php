<?php

//客户报表缓存
$db['cache_members']=array (
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
        'itype' =>
        array (
            'type' => 'char(100)',
            'label' => '报表类型',
            'required' => false,
            'editable' => false,
        ),
        'report_data' =>
        array(
            'type' => 'text',
            'default' => 0,
            'label' => '报表结果',
            'required' => false,
            'editable' => false,
        ),
        'date_from' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '日期',
            'required' => false,
            'editable' => false,
        ),
        'date_to' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '日期',
            'required' => false,
            'editable' => false,
        ),
        'cdate' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '日期',
            'required' => false,
            'editable' => false,
        ),
        'shop_id' =>
        array(
            'type' => 'char(32)',
            'default' => 0,
            'label' => '店铺ID',
            'required' => false,
            'editable' => false,
        ),
        'create_date' =>
        array(
            'type' => 'datetime',
            'default' => 0,
            'label' => '创建时间',
            'required' => false,
            'editable' => false,
        ),
    ),
    'index' =>
    array(
        'ind_cdate' =>
        array(
            'columns' =>
            array(
                0 => 'cdate',
            ),
        ),
        'ind_shop_id' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
            ),
        ),
    ),
    'comment' => '报表缓存(客户相关报表)',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);


