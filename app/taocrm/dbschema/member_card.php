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
$db['member_card'] = array(
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
		'member_card_make_log_id' => array(
            'type' => 'bigint unsigned',
			'required' => true,
		),
		'member_id' =>
	    array(
	        'type' => 'int unsigned',
	        'required' => false,
	        'editable' => false,
	    	'default' => 0,
	    ),
        'card_no' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '会员卡号',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>10,
		),
		'card_pwd' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '会员卡卡密',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>20,
		),
        'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>50,
		),
	    'send_card_channel' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '发卡渠道',
            'width' => 100,
            'in_list' => true,
            'order'=>60,
		),
	    'bind_card_channel' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '激活渠道',
            'width' => 100,
            'in_list' => true,
            'order'=>70,
		),
        'update_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '修改时间',
            'in_list' => true,
            'default_in_list' => true,
            'order'=>80,
		),
		'bind_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '激活时间',
            'in_list' => true,
            'default_in_list' => true,
			'default' => 0,
            'order'=>90,
		),
		'card_status' =>
	    array (
	        'type' => array ('unactive' => '未激活','active' => '激活','loss' => '挂失','logout'=>'注销'),
	        'default' => 'unactive',
	        'required' => true,
	        'label' => '会员卡状态',
	        'in_list' => true,
	    	'default_in_list' => true,
	        'width' => 100,
	    ),
    ),
    'index' =>
    array(
       'ind_card_no' =>
        array(
            'columns' =>
            array(
                0 => 'card_no',
            ),
            'prefix' => 'unique',
        ),
    ),
	'engine' => 'innodb',
    'version' => '$Rev:  $',
    'comment' => '会员卡',
);