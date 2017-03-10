<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['coupon_sent']=array (
  'columns' => 
  array (
    'sent_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'coupon_id' => 
    array (
      'type' => 'table:coupons@market',
      'required' => true,
      'label' => '优惠券名称',
      'width' => 180,
      'editable' => false,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'coupon_number' =>
    array (
      'type' => 'varchar(32)',
      'label' => '发送的优惠券编号',
      'width' => 125,
      'editable' => false,
    ),
    'buyer_nick' =>
    array(
//      'type' => 'varchar(50)',
      'type' => 'text',
      'required' => true,
      'label' => '用户名',
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ecorder',
      'required' => false,
      'label' => '所属店铺',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'order'=>2,
    ),
    'sent_status' => 
    array (
      'type' =>
      array(
        'succ' => '成功',
        'fail' => '失败',
      ),
      'required' => true,
      'default' => 'succ',
      'label' => '发送状态',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'reason' => 
    array (
//      'type' => 'varchar(255)',
      'type' => 'text',
      'default' => '',
      'label' => '失败信息',
    ),
    'send_time' => 
    array (
      'type' => 'time',
      'label' => '发送时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'comment' => '优惠券发送记录',
  'engine' => 'innodb',
);