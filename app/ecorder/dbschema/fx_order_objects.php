<?php
$db['fx_order_objects']=array (
  'columns' => 
  array (
    'obj_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' => 
    array (
      'type' => 'table:fx_orders@ecorder',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'obj_type' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'obj_alias' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'shop_goods_id' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'oid' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'goods_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'bn' => 
    array (
      'type' => 'varchar(40)',
      'editable' => false,
      'is_title' => true,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'quantity' => 
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
    ),
    'weight' => 
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'score' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
  ), 
   'index' =>
  array (
    'ind_goods_id' =>
    array (
        'columns' =>
        array (
          0 => 'goods_id',
        ),
    ),
    'ind_shop_goods_id' =>
    array (
        'columns' =>
        array (
          0 => 'shop_goods_id',
        ),
    ),
   
    
  ), 
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);