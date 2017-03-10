<?php

$db['members']=array (
    'columns' =>
    array (
    'member_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
   'ext_uid' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '外部用户ID',
        'in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
    'stand_node_id' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
      'label' => '独立店节点ID',
      'in_list' => true,
      'width' => 85,
      'order' => 10,
    ),
    'uname' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '客户名',
        'sdfpath' => 'account/uname',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
            'is_title' => true,
        'width' => 100,
        'order' => 10,
    ),
    'unique_code' =>
    array(
        'default' => 0,
        'type' => 'int unsigned',
        'required' => true,
        'label' => '客户唯一码',
        'editable' => false,
    ),
    'name' =>
    array (
        'type' => 'varchar(50)',
        'label' => '姓名',
        'sdfpath' => 'contact/name',
        'editable' => false,
            'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 30,
    ),
    'source_terminal' =>
    array(
        'type' => 'varchar(50)',
        'required' => false,
        'label' => '来源终端',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
    'channel_type' =>
        array (
            'type' => 'varchar(32)',
            'required' => false,
            'editable' => false,
            'label' => '渠道类型',
            'in_list' => true,
            //'default_in_list' => true,
            'default' => 'taobao',
            'width' => 100,
            'order' => 30,
        ),
    'parent_member_id' =>
    array(
        'type' => 'int unsigned',
        'default' => 0,
        'required' => true,
        'editable' => false,
    ),
     'is_merger' =>
        array (
            'type' => 'tinyint unsigned',
            'required' => false,
            'default' => 0,
            'label' => '是否合并',
            'editable' => false,
        ),
        'member_card' =>
        array (
            'type' => 'varchar(64)',
            'required' => false,
            'editable' => false,
            'label' => '会员卡号',
            'in_list' => true,
            //'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'qq' =>
        array (
            'type' => 'varchar(64)',
            'required' => false,
            'editable' => false,
            'label' => 'QQ',
            'in_list' => true,
            //'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'wangwang' =>
        array (
            'type' => 'varchar(64)',
            'required' => false,
            'editable' => false,
            'label' => '旺旺账号',
            'in_list' => true,
            //'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'weixin' =>
        array (
            'type' => 'varchar(64)',
            'required' => false,
            'editable' => false,
            'label' => '微信',
            'in_list' => true,
            //'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'weibo' =>
        array (
            'type' => 'varchar(64)',
            'required' => false,
            'editable' => false,
            'label' => '微博',
            'in_list' => true,
            //'default_in_list' => true,
            'width' => 100,
            'order' => 30,
        ),
        'other_contact' => array(
            'type' => 'text',
            'required' => false,
            'label'=> '其他联系方式',
        ),
    'points' =>
    array (
            'type' => 'bigint',
        'default' => 0,
        'required' => false,
        'label' => '积分',
        'editable' => false,
        'in_list' => true,
            'default_in_list' => true,
        'width' => 60,
        'order' => 40,
    ),
    'area' =>
    array (
        'type' => 'region',
            'label' => '区域',
        'sdfpath' => 'contact/area',
        'editable' => false,
        'filtertype' => 'yes',
        'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 120,
    ),
     'state' =>
    array(
        'label' => '省份',
        'type' => 'varchar(32)',
        'sdfpath' => 'contact/state',
        'editable' => false,
        'filtertype' => 'yes',
        'filterdefault' => 'true',
        'in_list' => true,
        'default_in_list' => false,
            'width' => 50,
        'order' => 50,
    ),
    'city' =>
    array(
        'label' => '城市',
        'type' => 'varchar(32)',
        'sdfpath' => 'contact/city',
        'editable' => false,
        'filtertype' => 'yes',
        'filterdefault' => 'true',
        'in_list' => true,
        'default_in_list' => false,
            'width' => 60,
        'order' => 60,
    ),
    'district' =>
    array(
        'label' => '地区',
        'type' => 'varchar(32)',
        'sdfpath' => 'contact/district',
        'editable' => false,
        'filtertype' => 'yes',
        'filterdefault' => 'true',
        'in_list' => true,
        'default_in_list' => false,
            'width' => 80,
        'order' => 70,
    ),
    'addr' =>
    array (
        'type' => 'varchar(255)',
        'label' => '地址',
        'sdfpath' => 'contact/addr',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'width' => 200,
        'order' => 80,
    ),
    'mobile' =>
    array (
        'type' => 'varchar(30)',
        'label' => '手机',
        'sdfpath' => 'contact/phone/mobile',
        'default'=>'',
        'editable' => false,
        'searchtype' => 'nequal',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 105,
        'order' => 90,
    ),
    'tel' =>
    array (
        'type' => 'varchar(30)',
        'label' => '固定电话',
        'sdfpath' => 'contact/phone/telephone',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => false,
        'width' => 110,
        'order' => 100,
    ),
    'email' =>
    array (
        'type' => 'varchar(200)',
        'label' => '电子邮件',
        'sdfpath' => 'contact/email',
        'editable' => false,
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 160,
        'order' => 110,
    ),
    'zip' =>
    array (
        'type' => 'varchar(20)',
        'label' => '邮编',
        'sdfpath' => 'contact/zipcode',
        'editable' => false,
        'in_list' => true,
            'width' => 60,
    ),
    'alipay_account' =>
    array (
        'type' => 'varchar(255)',
        'label' => '支付宝账号',
        'required' => false,
        'editable' => false,
        'width' => 110,
    ),
    'alipay_no' =>
    array (
        'type' => 'varchar(200)',
        'label' => '支付宝ID',
        'required' => false,
        'editable' => false,
        'width' => 110,
    ),
    'order_total_num' =>
    array (
        'type' => 'number',
        'default' => 0,
            'label' => '订单数',
        'editable' => false,
        'filtertype' => 'normal',
        'filterdefault' => 'true',
            'in_list' => true,
        'default_in_list' => false,
            'width' => 60,
        'order' => 120,
    ),
    'order_total_amount' =>
    array(
        'type' => 'money',
        'default' => 0,
        'label' => '订单总额',
        'editable' => false,
        'in_list' => false,
        'default_in_list' => false,
        'width' => 80,
        'order' => 130,
    ),
    'order_succ_num' =>
    array (
        'type' => 'number',
        'default' => 0,
        'label' => '成功订单数',
        'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        'width' => 110,
        'order' => 140,
    ),
    'order_succ_amount' =>
    array(
        'type' => 'money',
        'default' => 0,
        'label' => '成功订单金额',
        'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        'width' => 70,
        'order' => 150,
    ),
    'order_first_time' =>
    array (
        'type' => 'time',
        'label' => '首次购买时间',
        'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        'width' => 140,
        'order' => 160,
    ),
    'order_last_time' =>
    array (
        'type' => 'time',
        'label' => '最后购买时间',
        'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        'width' => 140,
        'order' => 170,
    ),
    'birthday' =>
    array (
        'type' => 'time',
    	'sdfpath' => 'profile/birthday',
        'default' => 0,
        'label' => '生日',
        'editable' => false,
        'hidden' => true,
        'in_list' => true,
        'width' => 40,
    ),
    'sex' =>
    array (
        'type' => array (
            'female' => '女',
            'male' => '男',
            'unkown' => '-',
        ),
        'sdfpath' => 'profile/gender',
        'default' => 'unkown',
        'required' => false,
        'label' => '性别',
        'editable' => true,
        'in_list' => true,
        'width' => 40,
    ),
    'create_time' =>
    array (
        'default' => 0,
        'type' => 'time',
        'label' => '创建时间',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'width' => 140,
        'order' => 180,
    ),
     'update_time' =>
    array (
        'default' => 0,
        'type' => 'time',
        'label' => '更新时间',
        'editable' => false,
        'in_list' => true,
            'default_in_list' => true,
        'width' => 140,
        'order' => 180,
    ),
    'point_update_time' =>
        array (
            'default' => 0,
            'type' => 'time',
            'label' => '积分更新时间',
            'editable' => false,
        ),
    'remark' =>
    array (
        'type' => 'varchar(500)',
        'label' => '备注',
        'width' => 75,
        'in_list' => false,
    ),
    'disabled' =>
    array (
        'type' => 'bool',
        'default' => 'false',
        'editable' => false,
    ),
    'is_vip' =>
    array(
        'type' => 'bool',
        'required' => false,
    	'default' => 'false',
        'editable' => false,
        'in_list' => true,
        'label' => '贵宾客户',
    ),
    'sms_blacklist' =>
    array(
        'type' => 'bool',
        'required' => false,
    	'default' => 'false',
        'editable' => false,
        'in_list' => true,
        'label' => '短信黑名单',
    ),
    'edm_blacklist' =>
    array(
        'type' => 'bool',
        'required' => false,
    	'default' => 'false',
        'editable' => false,
        'in_list' => true,
        'label' => '邮件黑名单',
    ),
    'last_caselog' =>
    array(
        'type' => 'varchar(500)',
        'required' => false,
        'editable' => false,
        'in_list' => false,
        'label' => '最后一次服务(不含服务内容)',
    ),
    'last_contact_time' =>
    array(
        'type' => 'time',
        'required' => false,
        'editable' => false,
        'in_list' => true,
            'default_in_list' => false,
        'label' => '最后服务时间',
    ),
    'contact_times' =>
    array(
        'type' => 'int(10)',
        'required' => false,
    	'default' => 0,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'label' => '服务次数',
            'width' => 60,
    ),
    'props' =>
    array(
        'type' => 'text',
        'required' => false,
    	'default' => '',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'label' => '自定义属性',
    ),
    'level_id' => array(
        'type' => 'table:member_level@taocrm',
        'label' => '全局会员等级',
        'default' => 0,
        'in_list' => true,
    ),
        'shop_id' =>
        array (
            'type' => 'varchar(32)',
            'label' => '店铺ID',
            'in_list' => false,
        ),
        'stored_value' =>
        array(
            'type' => 'money',
            'default' => 0,
            'label' => '储值金额',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 80,
        ),
  ),
  'index' =>
    array(
    	'ind_unique_code' =>
        array(
            'columns' =>
            array(
                0 => 'unique_code',
            ),
        ),
        'ind_name' =>
        array(
            'columns' =>
            array(
                0 => 'name',
            ),
        ),
        'ind_uname' =>
        array(
            'columns' =>
            array(
                0 => 'uname',
            ),
        ),
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
        'ind_order_first_time' =>
        array(
            'columns' =>
            array(
                0 => 'order_first_time',
            ),
        ),
        'ind_order_last_time' =>
        array(
            'columns' =>
            array(
                0 => 'order_last_time',
            ),
        ),
        'ind_tel' =>
        array(
            'columns' =>
            array(
                0 => 'tel',
            ),
        ),
        'ind_is_merger' =>
        array(
            'columns' =>
            array(
                0 => 'is_merger',
            ),
        ),
        'ind_parent_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'parent_member_id',
            ),
        ),
        'ind_create_time' =>
        array(
            'columns' =>
            array(
                0 => 'create_time',
            ),
        ),
    ),
  'comment' => '客户表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
