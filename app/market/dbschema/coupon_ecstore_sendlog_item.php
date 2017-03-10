<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['coupon_ecstore_sendlog_item']=array (
  'columns' => 
  array (
    'log_item_id' => 
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
     'is_sms' =>
    array (
      'type' => 'tinyint unsigned',
      'required' => false,
      'default' => 0,
      'label' => '是否发送短信',
      'comment' => '0=>否,1=>是',
      'width' => 75,
      'editable' => false,
      'order'=>100,
    ),
     'is_coupon' =>
    array (
      'type' => 'tinyint unsigned',
      'required' => false,
      'default' => 0,
      'label' => '是否发送优惠劵',
      'comment' => '0=>否,1=>是',
      'width' => 75,
      'editable' => false,
      'order'=>100,
    ),
    'reason' => 
    array (
//      'type' => 'varchar(255)',
      'type' => 'text',
      'default' => '',
      'label' => '失败信息',
    ),
    'sendtime' => 
    array (
      'type' => 'time',
      'label' => '发送时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'comment' => '优惠券发送记录明细',
  'engine' => 'innodb',
);