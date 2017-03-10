<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['active_member']=array (
  'columns' => 
  array (
    'member_id' =>
    array(
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'active_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'status' =>
    array (
      'type' => 'tinyint unsigned',
      'required' => false,
      'default' => '0',
      'label' => '对照组状态',
      'comment' => '0=>未加入,1=>活动组,2=>对照组,3=>短信对照组',
      'width' => 75,
      'editable' => false,
      'order'=>3,
    ),
    'issend' =>
    array (
      'type' => 'tinyint unsigned',
      'required' => false,
      'default' => '0',
      'label' => '是否发送',
      'comment' => '0=>未发送,1=>发送',
      'width' => 75,
      'editable' => false,
      'order'=>3,
    ),
    'sendtime' =>
    array (
      'type' => 'time',
      'label' => '发送时间',
      'width' => 130,
      'editable' => false,
      'in_list' => false,
      'order'=>80,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ecorder',
      'required' => false,
      'label' => '所属店铺',
      'editable' => false,
      'filterdefault' => true,
	  'filtertype' => 'normal',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
       'order'=>10,
    ),
    'send_type' =>
    array (
      'type' => 'tinyint unsigned',
      'required' => false,
      'default' => '1',
      'label' => '发送类型',
      'comment' => '1=>短信,2=>邮件',
      'width' => 75,
      'editable' => false,
      'order'=>100,
    ),
    'active_type' =>
    array (
      'type' => array(
        '1'=>'常规活动',
        '2'=>'周期活动',
        '3'=>'活动计划',
      ),
      'default' => '1',
      'pkey' => true,
      'required' => false,
      'label' => '活动类型',
      'width' => 75,
      'editable' => false,
      'order'=>100,
    ),
    'mobile' =>
    array (
      'type' => 'bigint(11)',
      'label' => '手机',
      'default' => 0,
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
    'ind_member_id' =>
    array (
        'columns' =>
        array (
          0 => 'member_id',
        ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);