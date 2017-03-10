<?php
$db['wangwang_shop_chat_log']=array (
  'columns' => 
  array (
    'id' => array(
       'type' => 'int unsigned',
       'required' => true,
       'pkey' => true,
       'extra' => 'auto_increment',
       'editable' => false,
       'label' => '序号',
    ),
    'shop_id' => 
    array (
      'type' => 'table:shop@ecorder',
      'required' => true,
      'editable' => false,
      'label' => '来源店铺',
      'in_list' => true,
    ),
    'member_id' => array(
      'type' =>'int',
      'required' => false,
      'label' => '客户ID',
      'order' => 50,
      'default' => 0,
    ),
    'uname' => array(
      'type' => 'varchar(50)',
      'required' => true,
      'label' => '客户名',
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
      'width' => 120,
      'order' => 60
    ),
    'seller_nick' => array(
      'type' => 'varchar(65)',
      'label' => '客服旺旺号',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'chat_date' =>
    array(
        'type' => 'time',
        'required' => false,
        'editable' => false,
        'label' => '咨询日期',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 150,
        'order' => 130,
    ),
  ),
  'index' =>
  array (
    'ind_shop_id' =>
    array (
        'columns' =>
        array (
          0 => 'shop_id',
        ),
    ),
	'ind_uname' =>
    array (
        'columns' =>
        array (
          0 => 'uname',
        ),
    ),
    'ind_chat_date' =>
    array (
        'columns' =>
        array (
          0 => 'chat_date',
        ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
