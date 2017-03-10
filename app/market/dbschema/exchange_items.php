<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * 可兑换物品
 */
 
$db['exchange_items']=array (

  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'title' => 
    array (
      'type' => 'varchar(100)',
      'required' => false,
      'label' => '兑换名称',
      'width' => 180,
      'editable' => false,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'num' =>
    array (
      'type' => 'int unsigned',
      'label' => '可兑换数量',
      'width' => 125,
      'editable' => false,
    ),
    'price' =>
    array(
      'type' => 'int unsigned',
      'required' => false,
      'label' => '兑换价格',
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'max_buy_num' =>
    array (
      'type' => 'number',
      'required' => false,
      'default' => 0,
      'label' => '限兑数量',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'order'=>2,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ecorder',
      'required' => false,
      'label' => '适用店铺',
    ),
    'item_type' => 
    array (
      'type' =>
      array(
        'coupon' => '优惠券',
        'gift' => '礼品',
      ),
      'required' => false,
      'default' => 'coupon',
      'label' => '兑换类型',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'relate_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'label' => '关联ID',
    ),
    'item_desc' => 
    array (
      'type' => 'text',
      'label' => '兑换说明',
    ),
    'is_active' => 
    array (
      'type' => 'tinyint',
      'default' => 0,
      'label' => '是否可用',
    ),
    'op_user' => 
    array (
      'type' => 'varchar(32)',
      'label' => '操作人',
    ),
    'user_ip' => 
    array (
      'type' => 'varchar(64)',
      'label' => '操作IP',
    ),
    'end_time' => 
    array (
      'type' => 'time',
      'label' => '兑换截止时间',
    ),
    'create_time' => 
    array (
      'type' => 'time',
      'label' => '创建时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'comment' => '可兑换物品',
  'engine' => 'innodb',
);