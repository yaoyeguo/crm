<?php 
$db['wx_member']=array (
    'columns' => 
    array (
    'wx_member_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
    'member_id' =>
    array(
        'type' => 'table:members@taocrm',
        'required' => false,
        'label' => '关联帐号',
        'editable' => false,
        'comment' => '',
    ),
    'ToUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '公众号',
        'editable' => false,
        'width' => 120,
        'order' => 10,
    ),
     'FromUserName' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '客户微信账号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 10,
    ),

    'user_id' =>
    array (
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '用户唯一标示',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'width' => 120,
        'order' => 10,
    ),
    'weixin_token' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '微信账号标示',
        'editable' => false,
        'width' => 120,
        'order' => 10,
    ),
    'wx_nick' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '微信昵称',
        'editable' => false,
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 20,
    ),
     'sex' => 
    array (
        'type' => array ('female' => '女','male' => '男','unkown' => '-',),
        'default' => 'unkown',
        'required' => false,
        'label' => '性别',
        'editable' => true,
        'in_list' => true,
        'width' => 40,
    ),
     'city' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '地区',
        'editable' => false,
        'width' => 120,
        'in_list' => true,
        'order' => 10,
    ),
      'province' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '省市',
        'editable' => false,
        'width' => 120,
        'in_list' => true,
        'order' => 10,
    ),
      'country' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '国家',
        'editable' => false,
        'width' => 120,
        'in_list' => true,
        'order' => 10,
    ),
     'tb_nick' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '淘宝昵称',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        //'in_list' => true,
        //'default_in_list' => true,
        'width' => 85,
        'order' => 20,
    ),
     'mobile' => 
    array (
        'type' => 'varchar(30)',
        'label' => '手机',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 105,
        'order' => 30,
    ),
    'points' => 
    array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'label' => '积分',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'width' => 60,
        'order' => 40,
    ),
    'continue_regist_count' => 
    array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'label' => '连续签到次数',
        'editable' => false,
        'width' => 80,
        'order' => 50,
        'in_list' => true,
        'default_in_list' => true,
    ),
      'create_time' => 
        array (
          'type' => 'time',
          'label' => '创建时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 60,
        ),
        'update_time' => 
        array (
          'type' => 'time',
          'label' => '更新时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 70,
        ),
        'status' =>
        array (
            'type' => 'bool',
            'label' => '绑定状态',
            'default'=>'false',
            'in_list' => false,
        ),
  ),
  'index' =>
    array(
    	
        'ind_FromUserName' =>
        array(
            'columns' =>
            array(
                0 => 'FromUserName',
            ),
        ),
    ),
  'comment' => '微信客户关联表',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
