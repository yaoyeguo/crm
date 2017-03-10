<?php
$db['shop']=array (
  'columns' => 
  array (
    'shop_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'shop_bn' => 
    array (
      'type' => 'varchar(20)',
      'required' => false,
      'label' => '店铺编号',
      'in_list' => false,
      'default_in_list' => false,
      'width' => 80,
      'order' => 10,
    ),
    'name' =>
    array (
      'type' => 'varchar(255)',
      'required' => false,
      'label' => '店铺名称',
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
      'width' => 200,
      'order' => 20,
    ),
    'shop_type' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '店铺类型',
      'in_list' => true,
      'default_in_list' => false,
      'width' => 80,
      'order' => 30,
    ),
     'subbiztype' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '子业务类型',
      'in_list' => true,
      'default_in_list' => false,
      'width' => 80,
      'order' => 30,
    ),
    'config' =>
    array (
      'type' => 'text',
      'editable' => false,
    ),
    'last_download_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '上次下载订单时间(终端)',
      'in_list' => true,
      'default_in_list' => true,
      'order' => 40,
    ),
    'last_upload_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '上次上传订单时间(ome)',
      'in_list' => false,
      'default_in_list' => false,
      'order' => 50,
    ),
    'last_market_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '最后一次营销时间',
      'in_list' => true,
      'default_in_list' => true,
      'order' => 60,
    ),
    'active' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'in_list' => false,
      'default_in_list' => true,
      'editable' => false,
      'label' => '激活',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
    ),
    'last_store_sync_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '上次库存同步时间',
      'in_list' => false,
      'default_in_list' => false,
    ),
    'area' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'addr' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'default_sender' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'mobile' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'tel' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'filter_bn' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
    ),
    'bn_regular' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'express_remark' =>
    array (
      'type' => 'text',
      'editable' => false,
    ),
    'delivery_template' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'order_bland_template' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'node_id' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
    ),
    'node_type' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
    ),
    'shop_prop' =>array (
      		'type' =>   array (
    	        'online' => '电商平台',
    	        'offline' => '实体门店',
    	        'wechat' => '微信',
	         ),
	        'default' => 'online',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '店铺性质',
            'width' => 140,
            'order' => 100,
     ),
    'channel_id' =>
    array (
      'type' => 'table:shop_channel@ecorder',
      'editable' => false,
      'label' => '店铺分类',
      'in_list' => false,
      'default_in_list' => false,
      'width' => 100,
      'order' => 60,
    ),
    'addon' =>
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'create_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '创建时间',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'modified_time' =>
    array (
      'type' => 'time',
      'editable' => false,
      'label' => '更新时间',
      'in_list' => true,
      'default_in_list' => true,
        ),
        'orders' =>
        array (
            'type' => 'int',
            'default' => 0,
            'width' => 60,
            'editable' => false,
            'label' => '排序',
            'in_list' => true,
            'default_in_list' => true,
    )
  ),
  'index' =>
  array (
    'ind_shop_bn' =>
    array (
        'columns' =>
        array (
          0 => 'shop_bn',
        ),
        'prefix' => 'unique',
    ),
    'ind_node_id' =>
    array (
        'columns' =>
        array (
          0 => 'node_id',
        ),
    ),
    'ind_channel_id' =>
    array (
        'columns' =>
        array (
          0 => 'channel_id',
        ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
