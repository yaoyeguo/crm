<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['active_assess']=array (
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
    'active_id' => 
    array (
     'type' =>'table:active@market',
     'editable' => false,
     'label' => '活动名称',
     'in_list' => true,
      'default_in_list' => true,
      'order'=>5,
    ),
    'shop_id' =>
    array (
      'type' => 'table:shop@ecorder',
      //'required' => false,
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
   'msgid' => 
        array(
        	'type' =>'varchar(100)',
            'required' => false,
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 110,
        ),
    'create_time' => 
    array (
      'type' => 'time',
      'width' => 130,
      'label' => '创建时间',
      'editable' => false,
      'in_list' => true,
      'order'=>20,
    ),
    'start_time' =>
    array (
        'type' => 'time',
        'label' => '开始时间',
        'width' => 130,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'filtertype' => 'normal',
        'filterdefault' =>true,
    ),
    'end_time' => 
    array (
        'type' => 'time',
        'label' => '结束时间',
        'width' => 130,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'filterdefault' => true,
        'filtertype' => 'normal',
        'order'=>30,
    ),
   'exec_time' => 
    array (
      'type' => 'time',
      'label' => '执行时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
       'filterdefault' => true,
	  'filtertype' => 'normal',
      'order'=>40,
    ),
   'active_members_res' =>
    array (
      'type' => 'text',
      'label' => '活动客户A评估结果',
      'comment' => '活动客户B评估结果',
      'is_title' => true,
      'width' => 180,
      'editable' => false,
    ),
   'active_members_res_b' =>
    array (
      'type' => 'text',
      'label' => '活动客户B评估结果',
      'comment' => '活动客户B评估结果',
      'is_title' => true,
      'width' => 180,
      'editable' => false,
    ),
    
   'control_members_res' =>
    array (
      'type' => 'text',
      'label' => '对照客户评估结果',
      'comment' => '对照客户评估结果',
      'is_title' => true,
      'width' => 180,
      'editable' => false,
    ),
   'state' => 
     array (
          'type' => 
          array (
            'finish' => '已完成',
            'unfinish' =>'未完成',
          ),
          'default' => 'unfinish',
          'label' => '状态',
          'width' => 75,
          'editable' => true,
          'in_list' => false,
    ),
    'is_control' => 
	     array (
          'type' => 'tinyint unsigned',
          'default' => '0',
          'label' => '是否对照',
          'width' => 75,
          'editable' => true,
    ),
    'total_members' =>
    array (
      'type' => 'int',
      'label' => '参加活动人数',
      'width' => 100,
      'editable' => false,
      'in_list'=> true,
      'default_in_list'=> true,
    ),
    'order_members' =>
    array (
      'type' => 'int',
      'label' => '下单人数',
      'width' => 80,
      'editable' => false,
      'in_list'=> true,
      'default_in_list'=> true,
    ),
    'paid_members' =>
    array (
      'type' => 'int',
      'label' => '付款人数',
      'width' => 80,
      'editable' => false,
      'in_list'=> true,
      'default_in_list'=> true,
    ),
    'paid_amount' =>
    array (
      'type' => 'money',
      'label' => '付款金额',
      'width' => 100,
      'editable' => false,
      'in_list'=> true,
      'default_in_list'=> true,
    ),
    'total_amount' =>
    array (
      'type' => 'money',
      'label' => '下单总金额',
      'width' => 80,
      'editable' => false,
      'in_list'=> false,
      'default_in_list'=> false,
    ),
    'data_update_time' =>
    array (
        'type' => 'time',
        'label' => '数据更新时间',
        'default' => 0,
        'editable' => false,
        'in_list'=> false,
    ),
  ),
    'index' =>
    array(
        'ind_active_id' =>
        array(
            'columns' =>
            array(
                0 => 'active_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);