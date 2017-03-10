<?php
$db['order_pmt']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' =>
    array (
      'type' => 'table:orders@ecorder',
      'required' => true,
      'editable' => false,
    ),
    'pmt_amount' =>
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'pmt_memo' =>
    array (
      'type' => 'longtext',
      'edtiable' => false,
    ),
    'pmt_describe' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'label' => '优惠名称',
    ),
    'promotion_id' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'pmt_desc' =>
    array (
      'type' => 'varchar(500)',
      'editable' => false,
      'label' => '优惠详细描述',
    ),
    'oid' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'label' => '订单ID',
    ),
    'coupon_id' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'label' => '优惠券ID',
    ),
  ), 
  'index' =>
  array (
    'ind_coupon_id' =>
    array (
        'columns' =>
        array (
          0 => 'coupon_id',
        ),
    ),
    'ind_promotion_id' =>
    array (
        'columns' =>
        array (
          0 => 'promotion_id',
        ),
    ),
    'ind_oid' =>
    array (
        'columns' =>
        array (
          0 => 'oid',
        ),
    ),
  ), 
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);