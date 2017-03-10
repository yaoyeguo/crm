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
			'type' => 'int(11)',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
		),
		'member_id' =>
		    array(
		        'type' => 'text',
                'required' => false,
		        'editable' => false,
		    ),
        'sms_id' => array(
			'type' => 'table:sms@market',
			'required' => false,
		),
		'active_id' => array (
	      'type' =>'table:active@market',
	      'editable' => false,
	      'label' => '营销活动',
	       'in_list' => true,
	      'default_in_list' => true,
            'order'=>10,
	    ),
		'type' => array(
			'type' => array('1' => 'queue', '2' => 'cron'),
			'required' => false,
			'label' => '发送方式'
		),
		'shop_id' => array(
			'type' => 'varchar(32)',
			'required' => false,
		),
		'batch_no' => array(
			'type' => 'varchar(150)',
			'required' => false,
            'label' => '批次编号',
		),
		'sms_batch_no' => array(
			'type' => 'varchar(50)',
			'required' => false,
            'label' => '短信发送批次',
		),
		'mobile' => array(
			'type' => 'text',
			'required' => false,
            'label' => '手机号码',
		),
		'content' => array(
			'type' => 'varchar(280)',
			'required' => false,
            'label' => '短信内容',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>20,
		),
		'status' => array(
			'default' => 'failed',			
			'type' => array(
				'wait' => '等待',
				'failed' => '发送失败',
				'success' => '发送成功',
			),
            'in_list' => false,
            'default_in_list' => false,
			'required' => false,
            'order'=>30,
		),
		'reason' => array(
			'type' => 'text',
			'label' => '失败原因'
		),
		'plan_send_time' => array(
			'type' => 'time',
			 'default' => '0',
			'required' => false,
            'label' => '计划发送时间',
            'order'=>40,
		),
        'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>50,
		),
	),
	'index' =>
    array(
        'ind_sms_id' =>
        array(
            'columns' =>
            array(
                0 => 'sms_id',
            ),
        ),
    ),
	'engine' => 'innodb',
);
