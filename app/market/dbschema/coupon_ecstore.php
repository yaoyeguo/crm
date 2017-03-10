<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['coupon_ecstore']=array (
  'columns' => 
  array (
    'coupon_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
     'ecstore_coupon_id' =>
    array (
      'type' => 'varchar(32)',
      'label' => '优惠券ID',
      'comment' => '优惠券ID',
      'width' => 100,
      'editable' => false,
      'default_in_list' => true,
      'order' => 20,
    ),
    'coupon_name' =>
    array (
      'type' => 'varchar(255)',
      'label' => '优惠券名称',
      'comment' => '优惠券名称',
      'is_title' => true,
      'width' => 200,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
      'order' => 10,
    ),
     'coupon_bn' =>
    array (
      'type' => 'varchar(32)',
      'label' => '优惠券编码',
      'comment' => '优惠券编码',
      'width' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 20,
    ),
     'coupon_type' =>
    array (
      'type' => 'varchar(8)',
      'label' => '优惠券类型',
      'comment' => '优惠券类型',
      'width' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 30,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ecorder',
      'required' => true,
      'label' => '所属店铺',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 40,
    ),
    'created' => 
    array (
      'type' => 'time',
      'label' => '创建时间',
      'width' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>100,
    ),
    'sync_last_time' => 
    array (
      'type' => 'time',
      'label' => '最后同步时间',
      'width' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'order'=>60,
    ),
    'start_time' => 
    array (
      'type' => 'time',
      'label' => '开始时间',
      'width' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>70,
    ),
    'end_time' => 
    array (
      'type' => 'time',
      'label' => '结束时间',
      'width' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 80,
    ),
     'description' =>
    array (
      'type' => 'text',
      'editable' => false,
      'width' => 100,
      'label' => '规则描述',
      'comment' => '规则描述',
      'in_list' => true,
      'order' => 180,
    ),
     'user_lv_id' =>
    array (
      'type' => 'text',
      'label' => '会员级别ID',
      'comment' => '会员级别ID',
      'width' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 90,
    ),
     'coupon_status' =>
    array (
       'type' =>
            array(
                'y' => '是',
                'n' => '否',
         ),
      'label' => '有效状态',
      'comment' => '有效状态',
      'width' => 50,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 100,
    ),
     'is_del' =>
    array (
       'type' =>
            array(
                'y' => '是',
                'n' => '否',
         ),
      'label' => '是否删除',
      'comment' => '是否删除',
      'width' => 50,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 100,
    ),
  ), 
    'index' =>
    array(
      
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);