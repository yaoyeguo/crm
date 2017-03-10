<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
  
$db['coupon_used']=array (
  'columns' => 
  array (
    'id' => 
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
    'state' => 
    array (
      'type' =>
      array(
        'Unused' => '未使用',
        'using' => '使用中',
        'used' => '已使用',
      ),
      'required' => true,
      'default' => 'Unused',
      'label' => '优惠券状态',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'channel' => 
    array (
      'type' => 
      array (
        'rewardforgifts' => '满就送',
        'marketingMessage' => '营销消息',
        'activityofget' => '活动领取',
        'createActivity' => '创建活动',
        'ISV' => 'ISV',
        'other' => '其他',
      ),
      'default' => 'other',
      'label' => '发放渠道',
    ),
    'buyer_nick' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '买家昵称',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
  ),
  'engine' => 'innodb',
);