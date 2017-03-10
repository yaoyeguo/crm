<?php

$db['member_receivers'] = array(
    'columns' =>
    array(
        'receiver_id' =>
        array(
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
            'in_list' => true,
            'label' => '收货人ID',
        ),
        'member_id' =>
        array(
            'type' => 'table:members@taocrm',
            'label' => '客户帐号',
            'sdfpath' => 'account/uname',
            'is_title' => true,
            'required' => false,
            'searchtype' => 'nequal',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'order' => 10,
        ),
        'name' =>
        array(
            'type' => 'varchar(50)',
            'label' => '姓名',
            'sdfpath' => 'contact/name',
            'searchtype' => 'head',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 20,
        ),
        'area' =>
        array(
            'label' => '冗余地区',
            'type' => 'region',
            'sdfpath' => 'contact/area',
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
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
            'width' => 80,
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
            'width' => 80,
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
            'default_in_list' => false,
            'width' => 150,
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
            'width' => 100,
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
            'searchtype' => 'head',
            'editable' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 90,
        ),
        'zip' =>
        array(
            'type' => 'varchar(20)',
            'label' => '邮编',
            'sdfpath' => 'contact/zipcode',
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => true,
            'width' => 60,
            'order' => 100,
        ),
        'order_num' =>
        array(
            'type' => 'number',
            'default' => 0,
            'label' => '订单数',
            'width' => 110,
            'editable' => false,
            'in_list' => true,
            'width' => 80,
            'order' => 110,
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
            'required' => false,
            'label' => '性别',
            'editable' => true,
            'filtertype' => 'yes',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 40,
            'order' => 120,
        ),
        'remark' =>
        array(
            'label' => '备注',
            'type' => 'varchar(500)',
            'in_list' => true,
        ),
        'create_time' =>
        array(
            'label' => '创建时间',
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 140,
            'order' => 130,
        ),
        'default_addr' =>
        array(
            'type' => 'bool',
            'required' => false,
            'default' => 'false',
            'editable' => false,
            'in_list' => true,
            'label' => '是否是默认地址',
        ),
        'selected' =>
        array(
            'type' => 'bool',
            'required' => false,
            'default' => 'false',
            'editable' => false,
            'in_list' => true,
            'label' => '是否选中为收货地址',
        ),
        'md5' =>
        array (
            'type' => 'varchar(32)',
            'editable' => false,
            'label' => '联系人唯一识别码',
        ),
    ),
    'index' =>
    array(
        'ind_md5' =>
        array(
            'columns' =>
            array(
                0 => 'md5',
            )
        ),
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
        'ind_name' =>
        array(
            'columns' =>
            array(
                0 => 'name',
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
