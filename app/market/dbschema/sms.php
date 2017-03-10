<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['sms']=array (
  'columns' => 
  array (
    'sms_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'template_id' =>
    array(
      'type' => 'table:sms_templates@market',
      'label' => '短信模板A',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default' => 0,
      'default_in_list' => true,
    ),
    'template_id_b' =>
    array(
      'type' => 'table:sms_templates@market',
      'label' => '短信模板B',
      'width' => 110,
      'default' => 0,
      'editable' => false,
       'in_list' => true,
      'default_in_list' => true,
    ),
    'active_id' => array (
      'type' =>'table:active@market',
      'editable' => false,
      'label' => '活动编号',
     // 'in_list' => true,
      //'default_in_list' => true,
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
    'active_name' =>
    array (
      'type' => 'varchar(255)',
      'label' => '活动名称',
      'comment' => '活动名称',
      'is_title' => true,
      'width' => 180,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
      'order'=>1,
    ),
    'sms_type' => 
    array (
      'type' => 
      array (
        'active' => '创建活动',
        'coupon' => '发优惠券',
        'order' => '订单',
      ),
      'label' => '短信类型',
      'width' => 75,
      'default' => 'active',
      'editable' => false,
      'in_list' => true,
    ),
    'create_time' => 
    array (
      'type' => 'time',
      'label' => '发送时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'end_time' => 
    array (
      'type' => 'time',
      'label' => '结束时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'plan_send_time' => 
    array (
      'type' => 'time',
      'label' => '计划发送时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'total_num' => 
    array (
      'type' => 'number',
      'label' => '总数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'success_num' => 
    array (
      'type' => 'number',
      'label' => '成功数',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'is_send' =>
    array (
      'type' =>   array (
	        'succ' => '发送成功',
	        'fail' => '发送失败',
	        'unsend' => '未发送',
    		'sending' => '发送中',
	      ),
      'default' => 'unsend',
      'required' => FALSE,
      'label' => '发送状态',
      'comment' => '短信是否发送',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
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
);
