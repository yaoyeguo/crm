<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['wx_store_subbranch']=array (
    'columns' =>
        array (
        'store_id' =>
            array (
              'type' => 'int unsigned',
              'required' => true,
              'pkey' => true,
              'extra' => 'auto_increment',
              'editable' => false,
            ),
        'store_name' =>
            array (
              'type' => 'varchar(100)',
              'label' => '店铺名称',
              'comment' => '活动名称',
              'is_title' => true,
              'searchtype' => 'has',
              'filtertype' => 'normal',
              'filterdefault' => 'true',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>10,
              'width'=>150,
            ),
        'phone' =>
            array (
              'type' => 'char(13)',
              'label' => '联系电话',
              'comment' => '联系电话',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>30,
              'width'=>150,
            ),
        'store_area' =>
            array (
              'type' => 'region',
              'label' => '店铺地区',
              'comment' => '店铺地区',
              'in_list' => true,
              'default_in_list' => true,
              'order' => 50,
              'width' => 150,
            ),
        'province_id' =>
            array (
              'type' => 'int(10)',
              'comment' => '省',
            ),
        'city_id' =>
            array (
              'type' => 'int(10)',
              'comment' => '市',
            ),
        'area_id' =>
            array (
              'type' => 'int(10)',
              'comment' => '区域',
            ),
        'picurl' =>
            array (
              'type' => 'varchar(255)',
              'label' => '店铺logo',
              'comment' => '店铺logo',
            ),
        'address' =>
            array (
              'type' => 'varchar(255)',
              'label' => '地址',
              'comment' => '地址',
              'in_list' => true,
              'default_in_list' => true,
              'width'=>150,
              'order'=>60,
            ),
        'map_x' =>
            array (
              'type' => 'varchar(30)',
              'label' => '坐标X',
              'comment' => '坐标X',
              'in_list' => true,
              'default_in_list' => false,
              'order'=>70,
              'width'=>150,
            ),
        'map_y' =>
            array (
              'type' => 'varchar(30)',
              'label' => '坐标Y',
              'comment' => '坐标Y',
              'in_list' => true,
              'default_in_list' => false,
              'order'=>80,
              'width'=>150,
            ),
        'open_time' =>
            array (
              'type' => 'varchar(250)',
              'label' => '营业时间',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>100,
              'width'=>120,
            ),
        'business' =>
            array (
              'type' => 'varchar(250)',
              'label' => '营业范围',
            ),
        'create_time' =>
            array (
              'type' => 'time',
              'label' => '创建时间',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>200,
            ),
    ),
    
    'index' =>
    array(
        'ind_store_area' =>
        array(
            'columns' =>
            array(
                0 => 'store_area',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
