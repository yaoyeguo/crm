<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['active_review']=array (
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
        'active_id' => 
        array (
            'type' =>'int unsigned',
            'editable' => false,
            'label' => '营销活动ID',
        ),
        'title' => 
        array(
            'type' =>'varchar(100)',
            'label' => '营销名称',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filterdefault' => true,
            'filtertype' => 'normal',
            'order'=>10,
            'width' => 180,
        ),
        'active_type' => 
        array(
            'type' => array(
                'active' => '营销活动',
                'active_ontime' => '定时营销',
                'active_cycle' => '周期营销',
                'plugins' => '营销插件',
            ),
            'label' => '任务类型',
            'default' => 'active',
            'filterdefault' => true,
            'filtertype' => 'normal',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>20,
            'width' => 100,
        ),
        'begin_time' => 
        array(
            'type' =>'time',
            'label' => '活动开始时间',
            'filterdefault' => true,
            'filtertype' => 'date',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>30,
            'width' => 130,
        ),
        'end_time' => 
        array(
            'type' =>'time',
            'label' => '活动结束时间',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>40,
            'width' => 130,
        ),
        'last_run_time' => 
        array(
            'type' =>'time',
            'label' => '最后执行时间',
            'filterdefault' => true,
            'filtertype' => 'date',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>50,
            'width' => 130,
        ),
        'status' => 
        array(
            'type' => array(
                'wait' => '等待执行',
                'running' => '执行中',
                'finish' => '完成',
                'dead' => '关闭',
                'pause' => '暂停',
            ),
            'default' => 'wait',
            'label' => '执行状态',
            'filterdefault' => true,
            'filtertype' => 'normal',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>60,
            'width' => 100,
        ),
        'exec_times' => 
        array(
            'type' =>'int unsigned',
            'label' => '执行次数',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>70,
            'width' => 80,
        ),
        'send_members' => 
        array (
            'type' => 'int unsigned',
            'label' => '累计发送人数',
            'default' => 0,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>80,
            'width' => 100,
        ),
        'update_time' => 
        array (
            'type' => 'time',
            'label' => '更新时间',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>90,
            'width' => 130,
        ),
    ),
    'index' =>
    array(
        'ind_active_key' =>
        array(
            'columns' =>
            array(
                0 => 'active_id',
                1 => 'active_type',
            ),
            'prefix' => 'unique'
        ),
        'ind_last_run_time' =>
        array(
            'columns' =>
            array(
                0 => 'last_run_time',
            ),
        ),
        'ind_title' =>
        array(
            'columns' =>
            array(
                0 => 'title',
            ),
        ),
    ),
    'engine' => 'innodb',
    'comment' => '营销活动监控',
    'version' => '$Rev:  $',
);
