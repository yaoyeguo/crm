<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$db['activity_m_queue_finished']=array (
'columns' => 
  array (
    'queue_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'active_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'editable' => false,
    ),
    'template_id' =>
    array(
      'type' => 'number',
      'label' => '短信模板id',
      'width' => 110,
      'editable' => false,
    ),
    'member_id' =>
    array(
      'type' => 'int unsigned',
      'required' => true,
      'editable' => false,
    ),
    'uname' =>
    array (
      'type' => 'char(32)',
      'required' => true,
      'label' => '用户名',
      'comment' => '用户名(旺旺帐号)',
      'width' => 75,
      'editable' => false,
      'order'=>40,
    ),
    'truename' =>
    array (
      'type' => 'char(32)',
      'required' => true,
      'label' => '真实姓名',
      'comment' => '真实姓名',
      'width' => 75,
      'editable' => false,
      'order'=>30,
    ),
    'mobile' =>
    array (
      'type' => 'char(15)',
      'required' => true,
      'label' => '手机号码',
      'comment' => '手机号码',
      'width' => 75,
      'editable' => false,
      'order'=>30,
    ),
     'is_send' => 
	     array (
		      'type' => 'tinyint unsigned',
		      'default' => '1',
		      'label' => '是否发送短信(0:不发送 1:发送 2 不发送,对照的)',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
    ),
    'is_send_coupon' => 
	     array (
		      'type' => 'tinyint unsigned',
		      'default' => '1',
		      'label' => '是否发送优惠券',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
    ),
    'is_send_finish' => 
	     array (
		      'type' => 'tinyint unsigned',
		      'default' => '0',
		      'label' => '是否发送短信完成',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
    ),
     'is_send_coupon_finish' => 
	     array (
		      'type' => 'tinyint unsigned',
		      'default' => '0',
		      'label' => '是否发送优惠券完成',
		      'width' => 75,
		      'editable' => true,
		      'in_list' => true,
    ),
   'sent_time' =>
    array(
      'type' => 'time',
      'label' => '发送短信时间',
      'width' => 110,
      'default'=>'0',
      'editable' => false,
    ),
    'sent_coupon_time' =>
    array(
      'type' => 'time',
      'label' => '发送优惠券时间',
      'width' => 110,
      'default'=>'0',
      'editable' => false,
    ),
  
  ),
  'index' =>
  array (
    'ind_active_id' =>
    array (
        'columns' =>
        array (
          0 => 'active_id',
        ),
    ),
    'ind_mobile' =>
    array (
        'columns' =>
        array (
          0 => 'mobile',
        ),
    ),
    'ind_sent_time' =>
    array (
        'columns' =>
        array (
          0 => 'sent_time',
        ),
    ),
     'ind_member_id' =>
    array (
        'columns' =>
        array (
          0 => 'member_id',
        ),
    ),
    'ind_is_send' =>
    array (
        'columns' =>
        array (
          0 => 'is_send',
        ),
    ),
     'ind_is_send_finish' =>
    array (
        'columns' =>
        array (
          0 => 'is_send_finish',
        ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);