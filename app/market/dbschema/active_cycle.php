<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['active_cycle']=array (
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
         'goods_id' =>
            array (
                'type' => 'text',
                'label' => '购买过的商品',
                'width' => 180,
                'editable' => false,
                'filtertype' => 'normal',
                'filterdefault' => false,
                'default' => '',
                'order'=>20,
        ),
        'exclude_filter' =>
            array (
              'type' => 'text',
              'label' => '排除条件',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
              'order'=>20,
                'default' => '',
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
              'order'=>20,
            ),
        'auto_cycle_type' =>
            array (
              'type' => array(
                'order_finish'=>'订单完成时间',
                'order_paid'=>'订单付款时间',
                'order_create'=>'订单创建时间',
              ),
              'label' => '自动周期开始时间',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
              'order'=>20,
        ),
        'auto_cycle_days' =>
            array (
              'type' => 'int(10)',
              'label' => '自动周期天数',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
              'order'=>20,
                'default' => 0,
        ),
        'fixed_cycle_days' =>
            array (
              'type' => 'int(10)',
              'label' => '固定周期天数',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
              'order'=>20,
        ),
        'auto_run_hour' =>
            array (
              'type' => 'int(10)',
              'label' => '执行时间小时',
              'width' => 180,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
              'order'=>20,
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
        'shop_id' =>
            array (
              'type' => 'varchar(500)',
              'required' => false,
              'label' => '适用店铺',
              'width' => 110,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
              'order'=>30,
                'default' => '',
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
              'order'=>40,
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
              'width' => 75,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
              'order'=>60,
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
        'group_id' =>
            array(
              'type' => 'number',
              'label' => '分组id',
              'editable' => false,
              'required' => false,
                'default' => 0,
            ),
        'tag_id' =>
            array(
              'type' => 'number',
              'label' => '标签id',
              'editable' => false,
              'required' => false,
                'default' => 0,
            ),
        'source' =>
            array(
              'type' => 'varchar(15)',
              'label' => '数据来源',
              'editable' => false,
              'required' => true,
                'default' => '',
            ),
        'templete_title' =>
            array(
              'type' => 'varchar(500)',
              'label' => '模板标题',
              'editable' => false,
                'default' => '',
            ),
        'templete' =>
            array(
              'type' => 'text',
              'label' => '模板内容',
              'editable' => false,
                'default' => '',
            ),
        'template_id' =>
            array(
              'type' => 'number',
              'label' => '短信模板',
              'width' => 110,
              'default' => 0,
              'editable' => false,
            ),
        'template_edm_id' =>
            array(
              'type' => 'table:edm_templates@market',
              'label' => '邮件模板',
              'width' => 110,
              'default' => 0,
              'editable' => false,
            ),
        'coupon_id' =>
            array(
              'type' => 'table:coupons@market',
              'label' => '优惠券id',
              'width' => 110,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => false,
                'default' => 0,
            ),
        'sent_time' =>
            array(
              'type' => 'time',
              'label' => '计划发送时间',
              'width' => 110,
              'default'=>'0',
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
        'exec_time' =>
            array (
              'type' => 'time',
              'label' => '执行时间',
              'width' => 130,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
              'filtertype' => 'normal',
              'filterdefault' =>true,
              'order'=>80,
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
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
            'order' => 100,
        ),
        'total_send_num' =>
        array (
            'type' => 'int',
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
        'plan_send_time' =>
        array (
            'type' => 'time',
            'label' => '定时发送时间',
            'editable' => false,
                'default' => 0,
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
                'default' => '',
        ),
        'ip' =>
        array (
            'type' => 'varchar(64)',
            'label' => 'IP地址',
            'editable' => false,
        ),
        'wait_days' =>
        array (
              'type' => 'varchar(50)',
              'default' => '',
              'label' => '等待天数',
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
        ),
    ),
    'index' =>array(
        'ind_create_time' =>
            array(
                'columns' =>
                    array(
                        0 => 'create_time',
                    ),
            ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
