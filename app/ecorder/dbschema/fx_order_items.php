<?php

$db['fx_order_items'] = array(
    'columns' =>
    array(
        'item_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'order_id' =>
        array(
            'type' => 'table:fx_orders@ecorder',
            'required' => true,
            'default' => 0,
            'editable' => false,
        ),
        'member_id' =>
        array(
            'type' => 'int unsigned',
            'default' => 0,
            'label' => '客户ID',
            'editable' => false,
        ),
        'obj_id' =>
        array(
            'type' => 'table:fx_order_objects@ecorder',
            'required' => true,
            'default' => 0,
            'editable' => false,
        ),
        'shop_goods_id' =>
        array(
            'type' => 'varchar(50)',
            'required' => true,
            'default' => 0,
            'editable' => false,
        ),
        'product_id' =>
        array(
            'type' => 'int unsigned',
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
        'oid' => 
        array (
          'type' => 'varchar(50)',
          'required' => true,
          'default' => 0,
          'editable' => false,
        ),
        'shop_product_id' =>
        array(
            'type' => 'varchar(50)',
            'editable' => false,
            'required' => true,
            'default' => 0,
        ),
        'bn' =>
        array(
            'type' => 'varchar(40)',
            'editable' => false,
            'is_title' => true,
        ),
        'name' =>
        array(
            'type' => 'varchar(200)',
            'editable' => false,
        ),
        'cost' =>
        array(
            'type' => 'money',
            'editable' => false,
        ),
        'price' =>
        array(
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'editable' => false,
        ),
        'amount' =>
        array(
            'type' => 'money',
            'editable' => false,
        ),
        'weight' =>
        array(
            'type' => 'money',
            'editable' => false,
        ),
        'nums' =>
        array(
            'type' => 'number',
            'default' => 1,
            'required' => true,
            'editable' => false,
            'sdfpath' => 'quantity',
        ),
        'sendnum' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => true,
            'editable' => false,
        ),
        'addon' =>
        array(
            'type' => 'longtext',
            'editable' => false,
        ),
        'item_type' =>
        array(
            'type' =>
            array(
                'product' => '商品',
                'pkg' => '捆绑商品',
                'gift' => '赠品',
                'adjunct' => '配件',
            ),
            'default' => 'product',
            'required' => true,
            'editable' => false,
        ),
        'score' =>
        array(
            'type' => 'number',
            'editable' => false,
        ),
        'evaluation' => 
        array(
            'type' => array('good'=>'好评','bad'=>'差评','neutral'=>'中评','unkown'=>'-'),
            'required' => false,
            'editable' => false,
        	'default' => 'unkown',
            'label' => '评价',
            'in_list' => true,
        ),
        'shop_id' =>
        array(
            'type' => 'table:shop@ecorder',
            'editable' => false,
            'required' => false,
            'label' => '店铺编号',
        ), 
        'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'label' => '创建时间',
        ), 
        'delete' =>
        array(
            'type' => 'bool',
            'default' => 'false',
            'editable' => false,
        ),
    ),
     'index' =>
  array (
    'ind_product_id' =>
    array (
        'columns' =>
        array (
          0 => 'product_id',
        ),
    ),
    'ind_shop_product_id' =>
    array (
        'columns' =>
        array (
          0 => 'shop_product_id',
        ),
    ),
    'ind_create_time' =>
    array (
        'columns' =>
        array (
          0 => 'create_time',
        ),
    ),
    'ind_goods_id' =>
    array (
        'columns' =>
        array (
          0 => 'goods_id',
        ),
    ),
    'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
  ), 
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
