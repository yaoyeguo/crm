<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['active_plan']=array (
    'columns' =>
        array (
        'active_id' =>
            array (
                'type' => 'int unsigned',
                'required' => true,
                'pkey' => true,
                'extra' => 'auto_increment',
                'editable' => false,
        ),
        'parent_id' =>
            array (
                'type' => 'int unsigned',
                'default' => 0,
                'required' => false,
                'editable' => false,
                'label' => '父节点ID',
        ),
        'active_name' =>
            array (
                'type' => 'varchar(255)',
                'label' => '活动名称',
                'is_title' => true,
                'editable' => false,
                'searchtype' => 'has',
                'filtertype' => 'normal',
                'filterdefault' => 'true',
                'in_list' => true,
                'default_in_list' => true,
                'order'=>10,
                'width'=>150,
        ),
        'filter_mem' =>
            array (
              'type' => 'text',
              'label' => '筛选条件',
              'comment' => '筛选条件',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
        ),
        'exclude_filter' =>
            array (
              'type' => 'text',
              'label' => '排除条件',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
            ),
        'cycle_type' =>
            array (
              'type' => array(
                'auto'=>'自动周期',
                'fixed'=>'固定周期',
              ),
              'label' => '周期设置',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
              'in_list' => false,
              'default_in_list' => false,
            ),
        'auto_cycle_days' =>
            array (
              'type' => 'int(10)',
              'label' => '自动周期天数',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
        ),
        'fixed_cycle_days' =>
            array (
              'type' => 'int(10)',
              'label' => '固定周期天数',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
        ),
        'wait_days' =>
            array (
              'type' => 'varchar(50)',
              'label' => '等待天数',
              'editable' => false,
        ),
        'plan_send_time' =>
        array (
            'type' => 'time',
            'label' => '计划发送',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 130,
            'order'=>80,
        ),
        'exec_time' =>
            array (
              'type' => 'time',
              'label' => '执行时间',
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
              'filtertype' => 'normal',
              'filterdefault' =>true,
        ),
        'auto_run_hour' =>
            array (
              'type' => 'int(10)',
              'label' => '执行时间小时',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
        ),
        'auto_run_min' =>
            array (
              'type' => 'int(10)',
              'label' => '执行时间分钟',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
              'order'=>20,
        ),
        'shop_ids' =>
            array (
              'type' => 'text',
              'required' => false,
              'label' => '店铺',
              'width' => 110,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
            ),
       'type' =>
            array(
              'type' => 'varchar(20)',
              'default' => 'sms',
              'required' => false,
              'label' => '活动类型',
              'width' => 75,
              'in_list' => false,
              'default_in_list' => false,
              'editable' => false,
            ),
       'total_num' =>
            array (
              'type' => 'number',
              'required' => false,
              'default' => 0,
              'label' => '总客户数',
              'width' => 75,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
              'order'=>50,
        ),
       'valid_num' =>
            array (
              'type' => 'number',
              'required' => false,
              'default' => 0,
              'label' => '有效人数',
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
              'width' => 75,
              'order'=>40,
        ),
        'active_num' =>
            array (
              'type' => 'number',
              'required' => false,
              'default' => 0,
              'label' => '响应人数',
              'width' => 75,
              'editable' => false,
            ),
        'templete' =>
            array(
              'type' => 'text',
              'label' => '短信内容',
              'editable' => false,
            ),
        'templete_b' =>
            array(
              'type' => 'text',
              'label' => '短信内容B',
              'editable' => false,
            ),
        'template_id' =>
            array(
              'type' => 'number',
              'default' => 0,
              'label' => '短信模板',
              'width' => 110,
              'editable' => false,
            ),
        'template_id_b' =>
            array(
              'type' => 'number',
              'default' => 0,
              'label' => '短信模板B',
              'width' => 110,
              'editable' => false,
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
              'order' => 200,
            ),
        'start_time' =>
            array (
              'type' => 'time',
              'label' => '开始时间',
              'width' => 130,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => false,
              'filtertype' => 'normal',
              'filterdefault' =>true,
            ),
        'end_time' =>
            array (
              'type' => 'time',
              'label' => '结束时间',
              'width' => 130,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => false,
              'filtertype' => 'normal',
              'filterdefault' =>true,
              'order'=>70,
            ),

        'remark' =>
            array (
                'type' => 'varchar(100)',
                'editable' => false,
                'label' => '备注',
            ),
        'is_timing' =>
        array (
            'type' => 'tinyint',
            'label' => '是否定时发送',
            'editable' => false,
        ),
        'run_times' =>
        array (
            'type' => 'int',
            'default' => 0,
            'label' => '已执行次数',
            'editable' => false,
            'width' => 100,
        ),
        'total_send_num' =>
        array (
            'type' => 'int(10)',
            'default' => 0,
            'label' => '累计发送人数',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 90,
            'order' => 100,
        ),
        'status' =>
        array (
            'type' => array(
                '0'=>'关闭',
                '1'=>'开启',
            ),
            'default' => '1',
            'label' => '是否开启',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 60,
            'order' => 100,
        ),
        'run_status' =>
        array (
            'type' => array(
                'wait'=>'等待执行',
                'running'=>'执行中',
                'finish'=>'完成',
                'closed'=>'已关闭',
            ),
            'default' => 'wait',
            'label' => '执行状态',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 100,
        ),
        'op_user' =>
        array (
            'type' => 'varchar(30)',
            'label' => '操作人',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 200,
        ),
        'sms_sign' =>
        array (
            'type' => 'varchar(30)',
            'label' => '短信签名',
            'editable' => false,
        ),
        'extend_no' =>
        array (
            'type' => 'char(7)',
            'label' => '短信签名no',
            'editable' => false,
        ),
        'sms_sign_b' =>
        array (
            'type' => 'varchar(30)',
            'label' => '短信签名B',
            'editable' => false,
        ),
        'extend_no_b' =>
        array (
            'type' => 'char(7)',
            'label' => '短信签名Bno',
            'editable' => false,
        ),
        'ip' =>
        array (
            'type' => 'varchar(64)',
            'label' => 'IP地址',
            'editable' => false,
        ),
        'half_compare' =>
        array (
            'type' => 'int(10)',
            'default' => 0,
            'label' => '是否活动对照',
            'editable' => false,
        ),
        'ab_compare' =>
        array (
            'type' => 'int(10)',
            'default' => 0,
            'label' => '是否AB短信对照',
            'editable' => false,
        ),
    ),
    'index' =>array(
        'ind_ab_compare' =>
            array(
                'columns' =>
                    array(
                        0 => 'ab_compare',
                    ),
            ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);