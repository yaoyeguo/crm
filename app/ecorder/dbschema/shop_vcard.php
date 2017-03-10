<?php
$db['shop_vcard']=array (
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
      'in_list' => false,
      'default_in_list' => false,
      'width' => 80,
      'order' => 30,
    ),
     'subbiztype' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => '子业务类型',
      'in_list' => false,
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
      'in_list' => false,
      'default_in_list' => false,
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
    'active' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'in_list' => false,
      'default_in_list' => false,
      'editable' => false,
      'label' => '激活',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'required' => false,
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
    'nick' =>
    array (
      'type' => 'varchar(30)',
      'required' => false,
      'editable' => false,
    ),
    'title' =>
    array (
      'type' => 'varchar(30)',
      'required' => false,
      'editable' => false,
    ),
    'fax' =>
    array (
      'type' => 'varchar(30)',
      'required' => false,
      'editable' => false,
    ),
    'email' =>
    array (
      'type' => 'varchar(50)',
      'required' => false,
      'editable' => false,
    ),
    'company' =>
    array (
      'type' => 'varchar(100)',
      'required' => false,
      'editable' => false,
    ),
    'shop_url' =>
    array (
      'type' => 'varchar(100)',
      'required' => false,
      'editable' => false,
    ),
    'address' =>
    array (
      'type' => 'varchar(30)',
      'required' => false,
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
      'required' => false,
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
    'addon' =>
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'vcard_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => false,
      'editable' => false,
      'label' => '淘名片ID',
      'in_list' => false,
      'default_in_list' => false,
    ),
    'vcard_url' =>
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'label' => '名片短地址',
      'required' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'passcode' =>
    array (
      'type' => 'number',
      'label' => '验证码',
      'required' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
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
  ),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);