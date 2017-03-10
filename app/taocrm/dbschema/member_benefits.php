<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
//客户权益表
$db['member_benefits'] = array(
    'columns' => array(
        'id' => array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
         'member_id' => 
        array(
        	'type' =>'int',
            'required' => false,
            'label' => '客户ID',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            //'searchtype' => 'has',
            'width' => 110,
        ),
         'benefits_type' =>
        array(
           'type' =>   array (
	        0 => '金额',
	        1 => '计次',
	        2 => '折扣',
	        3 => '其它'
	         ),
            'label' => '权益类型',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 10,
        ),
         'benefits_code' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '权益项代码',
            'required' => false,
            'order'=> 50,
        ),
       'benefits_name' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '权益项名称',
            'required' => false,
            'order'=> 60,
        ),
         'nums' =>
        array(
            'type' => 'varchar(100)',
            'required' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list'=>true,
            'label' => '值',
            'width' => 70,
        ),
          'is_enable' =>array (
      		'type' =>   array (
	        0 => '不可用',
	        1 => '可用',
	         ),
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '状态',
            'width' => 140,
            'order' => 100,
      ),
       'effectie_time' => 
        array (
        'type' => 'time',
        'label' => '生效时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
        'failure_time' => 
        array (
        'type' => 'time',
        'label' => '失效时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
        'update_time' => 
        array (
        'type' => 'time',
        'label' => '更新时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
       'create_time' => 
        array (
        'type' => 'time',
        'label' => '创建时间',
        'editable' => false,
        'in_list' => true,
        'width' => 140,
        'order' => 150,
       ),
    ),
    'index' =>
    array(
        
    ),
    'comment' => '客户标签表',
    'engine' => 'innodb',
);
