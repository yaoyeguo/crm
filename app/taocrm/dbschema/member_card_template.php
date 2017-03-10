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
$db['member_card_template'] = array(
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
        'card_name' => array(
			'type' => 'varchar(20)',
            'default' => '',
			'required' => false,
            'label' => '会员卡名称',
            'width' => 100,
            'in_list' => true,
            'default_in_list' => true,
            'order'=>10,
		),
		'is_type_code' => array(
			 'type' =>
            array(
                0 => '否',
                1 => '是',
            ),
	        'default' => '0',
			'required' => false,
            'label' => '是否启用会员卡类型编码',
            'width' => 140,
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
	        'in_list' => true,
            'default_in_list' => true,
	        'width' => 80,
	        'order'=>30,
	    ),
	    'card_pwd_len' =>
	    array (
	        'type' => 'tinyint unsigned',
	        'default' => 4,
	        'label' => '会员卡卡密长度',
	        'editable' => false,
	        'in_list' => true,
            'default_in_list' => true,
	        'width' => 100,
	    	'order'=>40,
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
	        'width' => 100,
            'order'=>50,
	    ),
        'card_type' =>
        array (
            'type' =>
            array(
                0 => '手动生成',
                1 => '微信自动生成'
            ),
            'default' => '0',
            'label' => '会员卡生成类型',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'order'=>60,
        ),
        'create_time' => array(
			'type' => 'time',
			'required' => false,
            'label' => '创建时间',
            'in_list' => true,
            'default_in_list' => true,
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
        'card_img' =>
        array (
            'type' => 'varchar(255)',
            'label' => '会员卡图片',
        ),
    ),
	'engine' => 'innodb',
    'version' => '$Rev:  $',
    'comment' => '会员卡模板',
);