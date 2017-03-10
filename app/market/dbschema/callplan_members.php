<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 呼叫计划明细
$db['callplan_members']=array (
    'columns' => 
        array (
        'id' => 
        array (
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
        ),
        'callplan_id' =>
        array (
          'type' => 'table:callplan@market',
          'label' => '呼叫计划',
          'is_title' => true,
          'editable' => false,
          'default' => 0,
          //'searchtype' => 'has',
          //'filtertype' => 'normal',
          //'filterdefault' => 'true',
          'in_list' => true,
          'default_in_list' => true,  
          'order'=>10,
        ),
        'customer' =>
        array (
          'type' => 'varchar(50)',
          'label' => '客户昵称',
          'width' => 100,
          'editable' => false,
          'searchtype' => 'has',
          'filtertype' => 'normal',
          'filterdefault' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>20,
        ),
        'member_id' =>
        array (
          'type' => 'table:members@taocrm',
          'label' => '客户ID',
          'default' => 0,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
        ),
        'truename' =>
        array (
          'type' => 'varchar(50)',
          'label' => '姓名',
          'width' => 80,
          'editable' => false,
          'searchtype' => 'has',
          'filtertype' => 'normal',
          'filterdefault' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>30,
        ),
        'mobile' =>
        array (
          'type' => 'varchar(50)',
          'label' => '手机号码',
          'is_title' => true,
          'width' => 120,
          'editable' => false,
          'searchtype' => 'has',
          'filtertype' => 'normal',
          'filterdefault' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>30,
        ),
        'create_time' =>
        array (
          'type' => 'time',
          'label' => '创建时间',
          'is_title' => true,
          'editable' => false,
          'filtertype' => 'normal',
          'filterdefault' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'update_time' =>
        array (
          'type' => 'time',
          'required' => false,
          'label' => '跟进时间',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>40,
        ),
       'alarm_time' =>
        array(
          'type' => 'time',
          'required' => false,
          'label' => '提醒时间',
          'in_list' => true,
          'default_in_list' => true,
          'editable' => false,
          'order'=>50,
        ),
       'call_result' =>
        array (
          'type' => 'table:member_caselog_category@taocrm',
          'required' => false,
          'label' => '呼叫结果',
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>60,
        ),
       'remark' =>
        array (
          'type' => 'text',
          'required' => false,
          'label' => '呼叫备注',
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>70,
        ),
        'call_times' =>
        array (
          'type' => 'number',
          'required' => false,
          'default' => 0,
          'label' => '呼叫次数',
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>80,
        ),
        'caselog_id' =>
        array(
          'type' => 'table:member_caselog@taocrm',
          'label' => '接待详情ID',
          'editable' => false,
          'order'=>90,
        ),
        'assign_user_id' =>
        array(
          'type' => 'int(11)',
          'default' => 0,
          'label' => '坐席ID',
          'editable' => false,
          'order'=>100,
        ),
        'assign_user' =>
        array(
          'type' => 'varchar(50)',
          'label' => '坐席',
          'width' => 110,
          'in_list' => true,
          'default_in_list' => true,
          'searchtype' => 'has',
          'editable' => false,
          'order'=>110,
        ),
        'esc_user' =>
        array(
          'type' => 'varchar(50)',
          'label' => '升级对象',
          'width' => 110,
          'editable' => false,
          'order'=>120,
        ),
        'is_finish' =>
        array(
          'type' => 'tinyint',
          'label' => '是否完成',
          'default' => 0,
          'width' => 110,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'order'=>150,
        ),
    ),
    'index' =>
    array(
        'ind_callplan_id' =>
        array(
            'columns' =>
            array(
                0 => 'callplan_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 