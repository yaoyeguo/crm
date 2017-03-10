<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
  
$db['coupon_ecstore_used']=array (
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
    'log_id' => array(
			'type' => 'int(11)',
			'required' => true,
	),
	'coupon_id' => 
     array (
          'type' => 'int unsigned',
          'required' => true,
          'editable' => false,
    ),
    'member_id' =>
        array (
            'type' => 'table:members@taocrm',
            'label' => '客户名',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'width' => 100,
            'order' => 10,
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
    'coupon_number' =>
    array (
      'type' => 'varchar(32)',
      'label' => '优惠券编号',
      'width' => 125,
      'editable' => false,
    ),
    'tid' =>
    array (
      'type' => 'varchar(32)',
      'label' => '订单号',
      'width' => 125,
      'editable' => false,
    ),
    'amount' =>
    array (
      'type' => 'varchar(32)',
      'label' => '订单金额',
      'width' => 125,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
);