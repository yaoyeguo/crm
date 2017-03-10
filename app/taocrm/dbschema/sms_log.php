<?php
/**
 * ShopEx
 *
 * @author Tian Xingang
 * @email ttian20@gmail.com
 * @copyright 2003-2011 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
$db['sms_log'] = array(
	'columns' => array(
		'log_id' => array(
            'type' => 'bigint unsigned',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment',
		),
		'member_id' => array(
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'editable' => false,
        ),
		'source_id' => array (
            'type' =>'int',
            'default' => 0,
            'editable' => false,
            'label' => '来源ID',
            'in_list' => false,
            'default_in_list' => false,
            'order'=>10,
	    ),
		'source' => array(
			'type' => array(
                'active_plan' => '营销计划',
                'active_cycle' => '周期营销',
                'market_active' => '营销活动',
                'plugins_plugins' => '自动插件',
                'taocrm_member_import_batch' => '导入客户',
                'taocrm_member_caselog' => '服务记录',
                'market_callplan' => '呼叫计划',
                'taocrm_member_group' => '自定义分组',
                'market_fx_activity' => '分销活动',
                'sale_model' => '营销模型',
                'weixin' => '微信服务',
                'report' => '运营报表',
                'other' => '其他',
            ),
			'default' => 'other',
			'required' => false,
			'label' => '短信类型',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>80,
            'order'=>20,
		),
		'shop_id' => array(
			'type' => 'varchar(32)',
            'default' => '',
			'required' => false,
            'label' => '店铺ID',
		),
        'shop_name' => array(
			'type' => 'varchar(32)',
            'default' => '',
			'required' => false,
            'label' => '店铺',
		),
		'batch_no' => array(
			'type' => 'varchar(64)',
            'default' => '',
			'required' => false,
            'label' => '批次编号',
		),
		'mobile' => array(
			'type' => 'varchar(11)',
			'required' => false,
            'searchtype' => 'has',
            'label' => '手机号码',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>100,
            'order'=>50,
		),
		'content' => array(
			'type' => 'varchar(350)',
			'required' => false,
            'label' => '短信内容',
            'in_list' => true,
            'default_in_list' => true,
            'width'=>400,
            'order'=>60,
		),
		'status' => array(	
			'type' => array(
				'wait' => '待发送',
				'fail' => '失败',
				'succ' => '成功',
			),
            'default' => 'wait',	
            'label' => '状态',
            'in_list' => true,
            'default_in_list' => true,
			'required' => false,
            'width'=>80,
            'order'=>70,
		),
		'remark' => array(
			'type' => 'text',
			'label' => '备注',
            'required' => false,
		),
		'plan_send_time' => array(
			'type' => 'time',
			'default' => 0,
			'required' => false,
            'label' => '计划发送时间',
		),
        'send_time' => array(
			'type' => 'time',
			'default' => 0,
			'required' => false,
            'label' => '发送时间',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'width' => 150,
		),
        'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
            'order'=>80,
		),
        'sms_size' => array(
			'type' => 'int',
			'required' => false,
            'label' => '短信长度',
            'in_list' => true,
            'default_in_list' => false,
            'order'=>90,
		),
        'cyear' => array(
			'type' => 'int',
			'required' => false,
            'label' => '年',
            'in_list' => false,
            'default_in_list' => false,
		),
        'cmonth' => array(
			'type' => 'int',
			'required' => false,
            'label' => '月',
            'in_list' => false,
            'default_in_list' => false,
		),
        'cday' => array(
			'type' => 'int',
			'required' => false,
            'label' => '天',
            'in_list' => false,
            'default_in_list' => false,
		),
        'op_user' => array(
			'type' => 'varchar(50)',
			'required' => false,
            'label' => '操作人',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 80,
		),
        'ip' => array(
			'type' => 'varchar(64)',
			'required' => false,
            'label' => '操作IP',
            'in_list' => true,
            'default_in_list' => false,
		),
	),
	'index' =>
    array(
        'ind_mobile' =>
        array(
            'columns' =>
            array(
                0 => 'mobile',
            ),
        ),
        'ind_op_user' =>
        array(
            'columns' =>
            array(
                0 => 'op_user',
            ),
        ),
        'ind_send_time' =>
        array(
            'columns' =>
            array(
                0 => 'send_time',
            ),
        ),
    ),
	'engine' => 'innodb',
    'version' => '$Rev:  $',
);