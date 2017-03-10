<?php
$db['shop_goods']=array (
  'columns' =>
  array (
    'goods_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'shop_id' =>
    array(
       'type' => 'varchar(32)',
       'required' => false,
       'editable' => false,
       'label' => '来源店铺',
    ),
    'outer_id' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '外部ID',
      'hidden' => true,
      'editable' => false,
      'default' => 0,
    ),
    'code' =>
    array (
      'type' => 'varchar(200)',
      'default' => '',
      'label' => '商品唯一码',
      'width' => 120,
      'searchtype' => 'nequal',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 10,
    ),
    'bn' =>
    array (
      'type' => 'varchar(200)',
      'default' => '',
      'label' => '商品编号',
      'width' => 120,
      'searchtype' => 'nequal',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => 10,
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'required' => false,
      'default' => '',
      'label' => '商品名称',
      'is_title' => true,
      'in_list' => true,
      'default_in_list' => true,
      'width' => 260,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype' => 'custom',
      'filterdefalut' => true,
      'filtercustom' =>
      array (
        'has' => '包含',
        'tequal' => '等于',
        'head' => '开头等于',
        'foot' => '结尾等于',
      ),
      'order' => 20,
    ),
    'brand_id' =>
    array(
        'type' => 'table:brand@ecgoods',
        'label' => '品牌',
        'width' => 100,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'filtertype' => 'normal',
        'filterdefault' => true,
        'order' => 25,
    ),
    'group_ids' =>
    array(
        'type' => 'varchar(100)',
        'label' => '商品分组',
        'width' => 100,
        'editable' => false,
        'in_list' => false,
        'default_in_list' => false,
    ),
    'store' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => '库存（各仓库的库存总和）',
      'default' => 0,
      'width' => 100,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => false,
      'label' => '库存',
      'order' => 30,
    ),
    'c_id' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => 0,
      'label' => '淘宝分类',
      'width' => 75,
      'editable' => false,
      'filtertype' => false,
      'filterdefalut' => false,
      'searchtype' => false,
      'in_list' => false,
    ),
    'cat_id' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'sdfpath' => 'category/cat_id',
      'default' => 0,
      'label' => '分类',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefalut' => true,
      'searchtype' => false,
    ),
    /*'type_id' =>
    array (
      'type' => 'table:goods_type@ome',
      'sdfpath' => 'type/type_id',
      'label' => '类型',
      'width' => 100,
      'editable' => false,
      'filterdefalut' => true,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),*/
    'goods_type' =>
    array (
      'type' =>
      array (
        'normal' => '普通商品',
        'bind' => '捆绑商品',
      ),
      'sdfpath' => 'goods_type',
      'default' => 'normal',
      'required' => true,
      'label' => '销售类型',
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'filterdefault' => true,
      'searchtype' => false,
      'filtertype' => 'yes',
      'in_list' => true,
    ),
    /*'brand_id' =>
    array (
      'type' => 'table:brand@ome',
      'sdfpath' => 'brand/brand_id',
      'label' => '品牌',
      'width' => 75,
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
      'default_in_list' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
    ),
    'brief' =>
    array (
      'type' => 'varchar(255)',
      'label' => '商品简介',
      'width' => 110,
      'hidden' => false,
      'editable' => false,
      'in_list' => false,
      'filtertype' => 'false',
    ),
    'intro' =>
    array (
      'type' => 'longtext',
      'sdfpath' => 'description',
      'label' => '详细介绍',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'barcode' =>
    array (
      'type' => 'varchar(32)',
      'label' => '条形码',
      'width' => 110,
      'hidden' => false,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
      'filterdefault' => true,
      'searchtype' => 'has',
    ),*/
    'mktprice' =>
    array (
      'type' => 'money',
      //'sdfpath' => 'product[default]/price/mktprice/price',
      'label' => '市场价',
      'width' => 80,
      'editable' => false,
    ),
    'cost' =>
    array (
      'type' => 'money',
      //'sdfpath' => 'product[default]/price/cost/price',
      'default' => '0',
      'label' => '成本价',
      'width' => 80,
      'editable' => false,
      'filtertype' => 'false',
      'filterdefault' => false,
      'in_list' => false,
    ),
    'price' =>
    array (
      'type' => 'money',
      //'sdfpath' => 'product[default]/price/price/price',
      'default' => '0',
      'label' => '销售价',
      'width' => 80,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
    ),
  'total_num' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '销售量',
      'default' => 0,
      'width' => 80,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'searchtype' => 'bthan',
      'order' => 40,
    ),
  'sale_money' =>
    array (
      'type' => 'money',
      'default' => '0',
      'label' => '销售金额',
      'width' => 100,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'searchtype' => 'bthan',
      'order' => 50,
    ),
  'buyperson' =>
    array (
      'type' => 'number',
      'editable' => false,
      'label' => '购买人数',
      'default' => 0,
      'width' => 100,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => false,
      'default_in_list' => false,
      'searchtype' => 'bthan',
      'order' => 60,
    ),
    'marketable' =>
    array (
      'type' => 'bool',
      'default' => 'true',
      'sdfpath' => 'status',
      'required' => true,
      'label' => '上架',
      'width' => 30,
      'editable' => false,
      'filtertype' => 'false',
      'in_list' => false,
    ),
    'weight' =>
    array (
      'type' => 'decimal(20,2)',
      'sdfpath' => 'product[default]/weight',
      'label' => '重量',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'filtertype' => 'yes',
    ),
    'unit' =>
    array (
      'type' => 'varchar(20)',
      'sdfpath' => 'unit',
      'label' => '单位',
      'width' => 30,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'score_setting' =>
    array (
      'type' =>
      array (
        'percent' => '百分比',
        'number' => '实际值',
      ),
      'default' => 'number',
      'editable' => false,
    ),
    'score' =>
    array (
      'type' => 'number',
      'sdfpath' => 'gain_score',
      'label' => '积分',
      'width' => 30,
      'editable' => false,
      'in_list' => false,
    ),
    'spec_desc' =>
    array (
      'type' => 'serialize',
      'label' => '物品',
      'width' => 110,

      'editable' => false,

    ),
    'params' =>
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'uptime' =>
    array (
      'type' => 'time',
      'depend_col' => 'marketable:true:now',
      'label' => '上架时间',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'filtertype' => 'false',
      'filterdefault' => false,
    ),
    'downtime' =>
    array (
      'type' => 'time',
      'depend_col' => 'marketable:false:now',
      'label' => '下架时间',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'filtertype' => 'false',
      'filterdefault' => false,
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => '更新时间',
      'width' => 150,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filtertype' => 'false',
      'filterdefault' => false,
      'order' => 100,
    ),
    'create_time' =>
    array (
        'type' => 'time',
        'label' => '创建时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'width' => 140,
        'order' => 180,
    ),
    'is_full_price' =>
      array (
          'type' =>
          array (
              '1' => '正价商品',
              '0' => '非正价商品',
          ),
          'default' => '1',
          'required' => true,
          'label' => '是否正价商品',
          'width' => 110,
          'editable' => false,
          'in_list' => false,
    ),
    'point_rule' =>
      array (
          'type' => array (
              '1' => '按金额给积分',
              '2' => '给固定积分',
              '3' => '不参与送积分',
          ),
          'default' => '1',
          'required' => true,
          'label' => '积分规则',
          'width' => 110,
          'editable' => false,
          'in_list' => false,
    ),
    'fixed_point_num' =>
      array (
          'type' => 'number',
          'editable' => false,
          'label' => '固定积分数',
          'default' => 0,
          'width' => 100,
          'in_list' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'p_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => '排序',
      'width' => 110,
      'editable' => false,
      'hidden' => true,
    ),
    'd_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => '排序',
      'width' => 30,
      'editable' => false,
    ),
    'pic_url' =>
        array(
            'type' => 'varchar(255)',
            'required' => false,
            'label' => '商品主图',
            'width' => 75,
            'editable' => false,
        ),
    'info_url' =>
        array(
            'type' => 'varchar(255)',
            'required' => false,
            'label' => '商品描述链接',
            'width' => 75,
            'editable' => false,
        ),
    'spec' =>
        array(
            'type' => 'varchar(255)',
            'required' => false,
            'label' => '规格型号说明',
            'width' => 75,
            'editable' => false,
        ),
    'desc' =>
        array(
            'type' => 'varchar(255)',
            'required' => false,
            'label' => '商品其他描述',
            'width' => 75,
            'editable' => false,
        ),
    'p_1' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_1/value',
      'editable' => false,
    ),
    'p_2' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_2/value',
      'editable' => false,
    ),
    'p_3' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_3/value',
      'editable' => false,
    ),
    'p_4' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_4/value',
      'editable' => false,
    ),
    'p_5' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_5/value',
      'editable' => false,
    ),
    'p_6' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_6/value',
      'editable' => false,
    ),
    'p_7' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_7/value',
      'editable' => false,
    ),
    'p_8' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_8/value',
      'editable' => false,
    ),
    'p_9' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_9/value',
      'editable' => false,
    ),
    'p_10' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_10/value',
      'editable' => false,
    ),
    'p_11' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_11/value',
      'editable' => false,
    ),
    'p_12' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_12/value',
      'editable' => false,
    ),
    'p_13' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_13/value',
      'editable' => false,
    ),
    'p_14' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_14/value',
      'editable' => false,
    ),
    'p_15' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_15/value',
      'editable' => false,
    ),
    'p_16' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_16/value',
      'editable' => false,
    ),
    'p_17' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_17/value',
      'editable' => false,
    ),
    'p_18' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_18/value',
      'editable' => false,
    ),
    'p_19' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_19/value',
      'editable' => false,
    ),
    'p_20' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_20/value',
      'editable' => false,
    ),
    'p_21' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_21/value',
      'editable' => false,
    ),
    'p_22' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_22/value',
      'editable' => false,
    ),
    'p_23' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_23/value',
      'editable' => false,
    ),
    'p_24' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_24/value',
      'editable' => false,
    ),
    'p_25' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_25/value',
      'editable' => false,
    ),
    'p_26' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_26/value',
      'editable' => false,
    ),
    'p_27' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_27/value',
      'editable' => false,
    ),
    'p_28' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_28/value',
      'editable' => false,
    ),
    'month3_paid_amount' =>
    array(
        'type' => 'money',
        'default' => 0,
        'label' => '近3个月付款金额',
        'editable' => false,
        'in_list' => false,
        'in_list' => false,
        'default_in_list' => false,
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'width' => 100,
        'order' => 90,
    ),
    'month3_paid_num' =>
    array(
        'type' => 'number',
        'default' => 0,
        'label' => '近3个月付款数量',
        'editable' => false,
        'in_list' => false,
        'in_list' => false,
        'default_in_list' => false,
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'width' => 100,
        'order' => 90,
    ),
    'no_use' =>
    array(
        'type' => 'tinyint',
        'default' => 0,
        'label' => '不参与统计',
    ),
  ),
  'comment' => '商品表',
  'index' =>
  array (
    'ind_bn' =>
    array (
      'columns' =>
      array (
        0 => 'bn',
      ),
      //'prefix' => 'UNIQUE',
    ),
     'ind_outer_id' =>
    array (
      'columns' =>
      array (
        0 => 'outer_id',
      ),
    ),
    'ind_month3_paid_num' =>
    array (
      'columns' =>
      array (
        0 => 'month3_paid_num',
      ),
    ),
    'ind_name' =>
    array (
      'columns' =>
      array (
        0 => 'name',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 44513 $',
);
