<?php
$db['shop_products'] = array(
    'columns' =>
    array(
        'product_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'label' => '货品ID',
            'extra' => 'auto_increment',
        ),
        'goods_id' => 
        array (
          'type' => 'int unsigned',
          'required' => true,
          'label' => '商品ID',
          'width' => 110,
          'hidden' => true,
          'editable' => false,
        ),
        'shop_id' =>
        array(
            'type' => 'varchar(32)',
            'required' => true,
            'editable' => false,
            'label' => '来源店铺',
        ),
        'outer_id' =>
        array(
            'type' => 'varchar(32)',
            'required' => true,
            'editable' => false,
            'label' => '外部ID',
        ),
        'outer_sku_id' =>
        array(
            'type' => 'varchar(32)',
            'required' => true,
            'editable' => false,
            'label' => '外部SKU-ID',
        ),
        'bn' =>
        array(
            'type' => 'varchar(32)',
            'required' => false,
            'label' => '商品货号',
            'is_title' => true,
            'width' => 125,
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
        ),
        'name' =>
        array(
            'type' => 'varchar(64)',
            'label' => '商品名称',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
        ),
        'store' =>
        array (
          'type' => 'number',
          'editable' => false,
          'comment' => '库存',
          'label' => '库存量',
          'default' => 0,
          'width' => 65,
          'in_list' => true,
          'filtertype' => 'number',
          'filterdefault' => true,
          'default_in_list' => true,
          'label' => '库存',
        ),
        'price' =>
        array(
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'label' => '单价',
            'width' => 75,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        /*'pic_url' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '图片',
            'width' => 75,
            'editable' => false,
        ),*/
        'good_ranks' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => true,
            'label' => '好评数',
            'width' => 75,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'bad_ranks' =>
        array(
            'type' => 'number',
            'default' => '0',
            'required' => false,
            'label' => '差评数',
            'width' => 70,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'neutral_ranks' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '中评数',
            'width' => 75,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'total_amount' =>
        array(
            'type' => 'money',
            'default' => 0,
            'required' => false,
            'label' => '销售总金额',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'total_num' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '销售总数',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'refund_num' =>
        array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label' => '退货总数',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'create_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'label' => '创建时间',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'update_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => true,
            'label' => '更新时间',
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'status' =>
        array(
            'type' => 'varchar(16)',
            'required' => false,
            'label' => '货品状态',
            'width' => 125,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' =>
    array(
         'ind_bn' => 
            array (
              'columns' => 
              array (
                0 => 'bn',
              ),
            ),
         'ind_outer_sku_id' => 
            array (
              'columns' => 
              array (
                0 => 'outer_sku_id',
              ),
            ),
    
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);