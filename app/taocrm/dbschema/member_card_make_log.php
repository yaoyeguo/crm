<?php
/**
 * ShopEx
 *
 * @author shiyao
 * @email shiyao@shopex.cn
 * @copyright 2003-2014 Shanghai ShopEx Network Tech. Co., Ltd.
 * @website http://www.shopex.cn/
 *
 */
$db['member_card_make_log'] = array(
	'columns' => array(
		'id' => array(
            'type' => 'bigint unsigned',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment',
		),
		'member_card_type_id' => array(
            'type' => 'bigint unsigned',
			'required' => true,
		),
		'member_card_template_id' => array(
            'type' => 'bigint unsigned',
			'required' => true,
		),
       'is_type_code' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '是否启用会员卡类型编码',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>20,
		),
	   'card_len' =>
	    array (
	        'type' => 'tinyint unsigned',
	        'default' => 6,
	        'label' => '会员卡长度',
	        'editable' => false,
	        'hidden' => true,
	        'in_list' => false,
	        'width' => 30,
	    ),
	    'card_pwd_len' =>
	    array (
	        'type' => 'tinyint unsigned',
	        'default' => 4,
	        'label' => '会员卡卡密长度',
	        'editable' => false,
	        'in_list' => false,
	        'width' => 40,
	    ),
	    'card_pwd_rule' =>
	    array (
	        'type' =>
            array(
                0 => '纯字母',
                1 => '纯数字',
                2 => '字母数字混合',
            ),
	        'default' => '0',
	        'label' => '会员卡卡密规则',
	        'editable' => false,
	        'in_list' => true,
            'default_in_list' => true,
	        'width' => 50,
	    ),
	    'make_count' =>
	    array (
	        'type' => 'int(5) unsigned',
	        'default' => 0,
	        'label' => '发卡数',
	        'editable' => false,
	        'in_list' => false,
	        'width' => 60,
	    ),
	    'bind_count' =>
	    array (
	        'type' => 'int(5) unsigned',
	        'default' => 0,
	        'label' => '激活数量',
	        'editable' => false,
	        'in_list' => false,
	        'width' => 60,
	    ),
	     'op_name' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '操作者',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>70,
		),
		'type_code' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '会员卡类型编码',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>80,
		),
		'batch_no' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '批次号',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>80,
		),
        'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>90,
		),
		'send_card_channel' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '发卡渠道',
            'width' => 100,
            'in_list' => true,
            'order'=>100,
		),
    ),
     'index' =>
    array(
       'ind_batch_no' =>
        array(
            'columns' =>
            array(
                0 => 'batch_no',
            ),
            'prefix' => 'unique',
        ),
    ),
	'engine' => 'innodb',
    'version' => '$Rev:  $',
    'comment' => '会员卡生成记录',
);