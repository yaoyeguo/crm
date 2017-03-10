<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['active']=array (
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
              'comment' => '活动名称',
              'is_title' => true,
              'editable' => false,
              'searchtype' => 'has',
              'filtertype' => 'normal',
              'filterdefault' => 'true',
              'in_list' => true,
              'default_in_list' => true,
              'order'=>10,
              'width'=>240,
            ),
         'filter_mem' =>
            array (
              'type' => 'text',
              'label' => '客户条件',
              'comment' => '客户条件',
              'is_title' => true,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
            ),
        'filter_sql' =>
            array (
              'type' => 'text',
              'label' => '筛选语句',
              'comment' => '筛选语句',
              'editable' => false,
            ),
        'report_filter' =>
            array (
              'type' => 'text',
              'label' => '报表条件',
              'comment' => '报表条件',
              'is_title' => true,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
            ),
        'member_list' =>
            array (
              'type' => 'text',
              'label' => '客户id',
              'comment' => '客户id',
              'is_title' => true,
              'editable' => false,
              'filtertype' => 'normal',
              'filterdefault' => false,
            ),
        'shop_id' =>
            array (
              'type' => 'table:shop@ecorder',
              'required' => false,
              'label' => '所属店铺',
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
              'order'=>30,
              'width' => 140,
            ),
       'type' =>
            array(
              'type' => 'text',
              'default' => 'sms',
              'required' => false,
              'label' => '活动类型',
              'width' => 75,
              'in_list' => true,
              'default_in_list' => true,
              'editable' => false,
              'order'=>40,
            ),
       'total_num' =>
            array (
              'type' => 'number',
              'required' => false,
              'default' => '0',
              'label' => '总客户数',
              'width' => 75,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
              'order'=>50,
            ),
       'valid_num' =>
            array (
              'type' => 'number',
              'required' => false,
              'default' => '0',
              'label' => '有效人数',
              'width' => 75,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
              'order'=>60,
            ),
        'active_num' =>
            array (
              'type' => 'number',
              'required' => false,
              'default' => '0',
              'label' => '响应人数',
              'width' => 75,
              'editable' => false,
            ),
        'create_source' =>
            array (
              'type' => array(
                  'normal'=>'常规',
                  'tags' => '标签',
                  'members' => '店铺客户',
            ),
              'default'=>'normal',
              'label' => '创建来源',
              'comment' => '创建来源',
              'width' => 25,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
              'orderby' => false
        ),
        'is_active' =>
            array (
              'type' => array(
                  'sel_coupon'=>'选择优惠劵',
                  'sel_member'=>'等待选择客户',
                  'sel_template'=>'等待选择模板',
                  'wait_exec'=>'等待执行',
                  'finish'=>'执行完成',
                  'dead'=>'已作废',
                  'execute' => '正在执行',
              ),
              'default'=>'sel_member',
              'required' => false,
              'label' => '活动状态',
              'comment' => '活动是否执行',
              'width' => 75,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
              'orderby' => false
        ),
        'templete_title' =>
            array(
              'type' => 'varchar(500)',
              'label' => '模板标题',
              'editable' => false,
            ),
        'templete' =>
            array(
              'type' => 'text',
              'label' => '模板内容',
              'editable' => false,
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
              'in_list' => true,
              'default_in_list' => true,
              'filtertype' => 'normal',
              'filterdefault' =>true,
              'order'=>80,
            ),
        'remark' =>
            array (
                'type' => 'varchar(1000)',
                'editable' => false,
                'label' => '备注',
            ),
        'cost' =>
            array (
                'type' => 'money',
                'editable' => false,
                'in_list' => true,
                'default_in_list' => false,
                'label' => '成本',
            ),
        'tags' =>
            array (
                'type' => 'varchar(500)',
                'editable' => false,
                'in_list' => true,
                'default_in_list' => false,
                'label' => '活动描述',
            ),
        'control_group' =>
            array (
                'type' => array(
                    'yes' => '是',
                    'no' => '否'
                ),
                'default' => 'no',
                'required' => true,
                'label' => '是否开启对照组',
                'editable' => false,
                'in_list' => false,
                'default_in_list' => false,
                'width' => 40,
            ),
        'unsubscribe' =>
            array (
                'type' => 'tinyint',
                'default' => 0,
                'required' => false,
                'label' => '是否开启退订消息',
            ),
        'is_send_salemember' =>
            array (
                'type' => 'tinyint',
                'default' => 1,
                'required' => false,
                'label' => '是否发送已营销客户',
            ),
        'pay_type' =>
            array (
                'type' => array ('free'=>'自主创建','pay' => '按效果付费','market' => '营销超市'),
                'default' => 'free',
                'required' => true,
                'label' => '活动来源',
                'editable' => true,
                'width' => 75,
                'in_list' => true,
            ),
        'templete_title_b' =>
            array(
              'type' => 'varchar(500)',
              'label' => '模板标题B',
              'editable' => false,
            ),
        'templete_b' =>
            array(
              'type' => 'text',
              'label' => '模板内容B',
              'editable' => false,
            ),
        'template_id_b' =>
            array(
              'type' => 'number',
              'label' => '短信模板B',
              'width' => 110,
              'editable' => false,
              'default' => 0,
            ),
        'active_remark' =>
            array(
              'type' => 'text',
              'label' => '活动备注',
              'width' => 110,
              'editable' => false,
              'comment' => 'shopName,entId,entPwd,license,taskId="activity"+activityId',
            ),
        'cache_id' =>
            array(
              'type' => 'int unsigned',
              'label' => '缓存ID',
              'width' => 110,
              'editable' => false,
              'default' => 0,
            ),
        'cache_id_create_time' =>
            array (
              'type' => 'time',
              'label' => 'cache_id创建时间',
              'width' => 130,
              'editable' => false,
              'in_list' => false,
              'default_in_list' => false,
            ),
        'is_timing' => 
        array (
            'type' => 'tinyint',
            'label' => '是否定时发送',
            'editable' => false,
        ),
        'plan_send_time' => 
        array (
            'type' => 'time',
            'label' => '定时发送时间',
            'editable' => false,
        ),
        'op_user' => 
        array (
            'type' => 'varchar(30)',
            'label' => '操作人',
            'editable' => false,
        ),
        'ip' => 
        array (
            'type' => 'varchar(64)',
            'label' => 'IP地址',
            'editable' => false,
        ),
    ),
    'index' =>
    array(
        'ind_shop_id' =>
        array(
            'columns' =>
                array(
                    0 => 'shop_id',
                ),
        ),
        'ind_exec_time' =>
        array(
            'columns' =>
                array(
                    0 => 'exec_time',
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
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
