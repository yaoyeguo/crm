<?php

$db['member_contacts'] = array(
    'columns' =>
    array(
        'contact_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
            'label' => '联系人ID',
        ),
        'member_id' =>
        array(
            'type' => 'int unsigned',
            'label' => '用户ID',
            'sdfpath' => 'account/uname',
            'is_title' => true,
            'required' => false,
            'searchtype' => 'head',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 75,
            'order' => 10,
        ),
        'name' =>
        array(
            'type' => 'varchar(50)',
            'label' => '姓名',
            'sdfpath' => 'contact/name',
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 75,
            'order' => 20,
        ),
         'area' =>
        array(
            'label' => '冗余地区',
            'type' => 'varchar(32)',
            'sdfpath' => 'contact/area',
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => 'true',
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
            'default_in_list' => true,
            'width' => 100,
            'order' => 30,
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
            'default_in_list' => true,
            'width' => 100,
            'order' => 40,
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
            'default_in_list' => true,
            'width' => 100,
            'order' => 50,
        ),
        'addr' =>
        array(
            'type' => 'varchar(255)',
            'label' => '地址',
            'sdfpath' => 'contact/addr',
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 160,
            'order' => 60,
        ),
        'mobile' =>
        array(
            'type' => 'varchar(30)',
            'label' => '手机',
            'sdfpath' => 'contact/phone/mobile',
            'searchtype' => 'head',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 70,
        ),
        'tel' =>
        array(
            'type' => 'varchar(30)',
            'label' => '固定电话',
            'sdfpath' => 'contact/phone/telephone',
            'searchtype' => 'head',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 80,
        ),
        'email' =>
        array(
            'type' => 'varchar(200)',
            'label' => '电子邮件',
            'sdfpath' => 'contact/email',
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 150,
            'order' => 90,
        ),
        'wangwang' =>
        array(
            'type' => 'varchar(200)',
            'label' => '旺旺',
            'sdfpath' => 'contact/wangwang',
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 80,
            'order' => 100,
        ),
        'qq' =>
        array(
            'type' => 'varchar(200)',
            'label' => 'QQ',
            'sdfpath' => 'contact/qq',
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 80,
            'order' => 110,
        ),
        'msn' =>
        array(
            'type' => 'varchar(200)',
            'label' => 'MSN',
            'sdfpath' => 'contact/msn',
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 120,
        ),
        'weibo' =>
        array(
            'type' => 'varchar(200)',
            'label' => '微博',
            'sdfpath' => 'contact/weibo',
            'searchtype' => 'has',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 100,
            'order' => 130,
        ),
        'zip' =>
        array(
            'type' => 'varchar(20)',
            'label' => '邮编',
            'sdfpath' => 'contact/zipcode',
            'editable' => true,
            'filtertype' => 'normal',
        ),
        'sex' =>
        array(
            'type' =>
            array(
            	'unkown' => '-',
                'female' => '女',
                'male' => '男',
            ),
            'sdfpath' => 'profile/gender',
            'default' => 'unkown',
            'required' => true,
            'label' => '性别',
            'editable' => true,
            'filtertype' => 'yes',
            'in_list' => true,
            'default_in_list' => false,
            'width' => 50,
            'order' => 140,
        ),
        'remark' =>
        array(
            'label' => '备注',
            'type' => 'varchar(500)',
            'width' => 100,
            'order' => 150,
            'in_list' => true,
        ),
        'channel_id' =>
        array(
            'label' => '来源渠道',
            'type' => 'table:shop_channel@ecorder',
            'width' => 100,
            'required' => false,
            'in_list' => true,
        ),
        'shop_id' =>
        array(
            'label' => '来源店铺',
            'type' => 'table:shop@ecorder',
            'width' => 120,
            'required' => false,
            'in_list' => true,
        ),
        'md5' =>
	    array (
	      'type' => 'varchar(32)',
	      'editable' => false,
          'label' => '唯一识别码',
          'required' => false,
	    ),
        'disabled' =>
        array(
            'label' => '状态',
            'type' => 'bool',
            'width' => 75,
            'in_list' => true,
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
    ),
    'index' =>
    array(
        'ind_name' =>
        array(
            'columns' =>
            array(
                0 => 'name',
            ),
        ),
        'ind_md5' =>
        array(
            'columns' =>
            array(
                0 => 'md5',
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


