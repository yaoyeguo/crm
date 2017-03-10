<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['shop_agents']=array (
    'columns' => 
    array (
        'agent_id' =>
        array(
            'type' => 'int(11)',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'extra' => 'auto_increment',
        ),
        'agent_name' => 
        array (
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'label' => '客服帐号',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'width' => 100,
            'order' => 20,
        ),
        'nickname' =>
        array (
            'type' => 'varchar(32)',
            'required' => false,
            'label' => '昵称',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 75,
            'order'=>30,
        ),
        'chat_num' => 
        array (
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'label' => '聊天次数',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order'=> 40,
        ),
        'shop_id' => 
        array (
            'type' => 'table:shop@ecorder',
            'required' => false,
            'label' => '所属店铺',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order'=> 50,
        ),
        'create_time' => 
        array (
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order'=> 60,
        ),
    ),
    'index' =>
    array (
        'ind_nickname' =>
        array (
            'columns' =>
            array (
                0 => 'nickname',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);