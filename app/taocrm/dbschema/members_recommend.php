<?php
$db['members_recommend']=array (
    'columns' =>
    array (
        'member_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'editable' => false,
        ),
        'uname' =>
        array(
            'type' => 'varchar(50)',
            'required' => false,
            'label' => '推荐人名称(客户名)',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order' => 10,
            'default'=>'',
        ),
        'name' =>
        array (
            'type' => 'varchar(50)',
            'label' => '真实姓名',
            'editable' => false,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 85,
            'order' => 30,
            'default'=>'',
        ),
        'mobile' =>
        array (
            'type' => 'varchar(30)',
            'label' => '手机号',
            'editable' => false,
            'searchtype' => 'nequal',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 105,
            'order' => 90,
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
        'is_parent' =>
        array (
            'type' => 'bool',
            'label' => '是否有被推荐人',
            'default'=>'false',
            'in_list' => false,
        ),
        'self_code' =>
        array (
            'type' => 'int',
            'label' => '推荐唯一码',
            'in_list' => true,
            'default_in_list' => true,
            'default'=>'0',
        ),
        'parent_code' =>
        array (
            'type' => 'int',
            'label' => '推荐人CODE',
            'in_list' => false,
            'default'=>'0',
        ),
        'update_time' =>
        array (
            'default' => 0,
            'type' => 'time',
            'label' => '更新时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'width' => 140,
            'order' => 180,
        ),
        'mRTwoStatus' =>
        array (
            'type' => 'int',
            'label' => '二级推荐人数返利是否已经统计过了。0未统计1统计过了',
            'in_list' => false,
            'default'=>'0',
        ),
        'mRThreeStatus' =>
        array (
            'type' => 'int',
            'label' => '三级推荐人是否返利0未统计，1统计过了',
            'in_list' => false,
            'default'=>'0',
        ),
    ),
    'index' =>
    array(
        'ind_comm' =>
        array(
            'columns' =>
            array(
                0 => 'parent_code',
            ),
        ),
        'ind_code' =>
        array(
            'columns' =>
            array(
                0 => 'self_code',
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
    ),
    'comment' => '推荐表',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
