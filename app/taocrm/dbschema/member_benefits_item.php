<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
//客户权益项表
$db['member_benefits_item'] = array(
    'columns' => array(
        'id' => array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        'benefits_code' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '权益项代码',
            'required' => false,
            'order'=>10,
        ),
       'benefits_name' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '权益项名称',
            'required' => false,
            'order'=>20,
        ),
         'source' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '来源业务',
            'required' => false,
            'order'=>30,
        ),
          'is_enable' =>array (
      		'type' =>   array (
	        0 => '未启用',
	        1 => '启用',
	         ),
	        'default' => 1,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '是否启用',
            'width' => 140,
            'order' => 40,
      ),
       'create_op_name' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '创建人',
            'required' => false,
            'order'=>50,
        ),
        'create_op_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '创建人时间',
            'width' => 140,
            'order' => 60,
        ),
        'update_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'label' => '更新时间',
            'width' => 140,
            'order' => 60,
        ),
         'create_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 60,
        ),
    ),
    'index' =>
    array(
        
    ),
    'comment' => '客户标签表',
    'engine' => 'innodb',
);

