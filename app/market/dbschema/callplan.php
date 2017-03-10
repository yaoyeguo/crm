<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
//呼叫计划
$db['callplan']=array (
    'columns' => 
        array (
        'callplan_id' => 
        array (
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
        ),
        'callplan_name' =>
        array (
          'type' => 'varchar(100)',
          'label' => '呼叫计划名称',
          'is_title' => true,
          'editable' => false,
          'searchtype' => 'has',
          'filtertype' => 'normal',
          'filterdefault' => 'true',
          'in_list' => true,
          'default_in_list' => true,
          'width'=>150,
          'order'=>10,
        ),
        'assign_users' =>
        array(
          'type' => 'varchar(500)',
          'label' => '分配客服',
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'width' => 100,
          'order'=>15,
        ),
         'start_time' =>
        array (
          'type' => 'time',
          'label' => '开始时间',
          'is_title' => true,
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>20,
        ),
        'end_time' =>
        array (
          'type' => 'time',
          'label' => '结束时间',
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>30,
        ),
        'total_num' =>
        array (
          'type' => 'number',
          'label' => '全部客户数',
          'default' => 0,
          'width' => 80,
          'is_title' => true,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>40,
        ),
        'assign_num' =>
        array (
          'type' => 'number',
          'label' => '已分配数',
          'default' => 0,
          'width' => 80,
          'is_title' => true,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>45,
        ),
        'called_num' =>
        array (
          'type' => 'number',
          'label' => '已拨打数',
          'default' => 0,
          'width' => 80,
          'is_title' => true,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>50,
        ),
        'finish_num' =>
        array (
          'type' => 'number',
          'label' => '完成数',
          'default' => 0,
          'width' => 80,
          'is_title' => true,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>55,
        ),
        'desc' =>
        array (
          'type' => 'text',
          'required' => false,
          'label' => '备注说明',
          'width' => 110,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'order'=>58,
        ),
       'create_time' =>
        array(
          'type' => 'time',
          'required' => false,
          'label' => '创建时间',
          'width' => 75,
          'in_list' => true,
          'default_in_list' => false,
          'editable' => false,
          'order'=>64,
        ),
       'update_time' =>
        array (
          'type' => 'time',
          'required' => false,
          'label' => '更新时间',
          'width' => 140,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>70,
        ),
       'create_user' =>
        array (
          'type' => 'varchar(50)',
          'required' => false,
          'label' => '创建人',
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'order'=>80,
        ),
        'status' =>
        array (
          'type' => array(
              '0'=>'关闭',
              '1'=>'开启',
          ),
          'required' => false,
          'default' => '1',
          'label' => '状态',
          'width' => 75,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'order'=>90,
        ),
        'assign_user_id' =>
        array(
          'type' => 'varchar(500)',
          'label' => '分配客服ID',
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
        ),
        'survey_id' =>
        array(
          'type' => 'int(11)',
          'label' => '问卷调查',
          'default' => 0,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'order'=>110,
        ),
        'source' =>
        array(
          'type' => 'varchar(50)',
          'label' => '创建来源',
          'width' => 110,
          'default' => 0,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'order'=>120,
        ),
        'source_id' =>
        array(
          'type' => 'int(11)',
          'label' => '来源ID',
          'default' => 0,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'width' => 110,
          'order'=>130,
        ),
        'err_msg' =>
        array(
          'type' => 'varchar(500)',
          'label' => '错误描述',
          'width' => 110,
          'default' => '',
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'order'=>150,
        ),
    ),
    'index' =>
    array(
        'ind_callplan_name' =>
        array(
            'columns' =>
            array(
                0 => 'callplan_name',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
); 