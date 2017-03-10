<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 *  消息模板表
 */
$db['sms_templates'] = array(
    'columns' => array(
        'template_id' =>  array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        'title' =>
            array (
            'type' => 'varchar(100)',
            'is_title'=>true,
            'in_list'=>false,
            'default_in_list' => false,
            'label'=> '模板名称',
            'filtertype'=>true,
            'searchtype'=>true,
            'searchtype' => 'has',
            'width' => 180,
            'order' => 10,
        ),
        'content' => array(
            'type' => 'text',
        	'in_list'=>true,
            'default_in_list' => true,
            'required' => true,
            'label'=>'内容',
            'width' => 200,
            'order' => 20,
        ),
        'type_id' => array(
        	'type' => 'table:sms_template_type@market',
            'in_list'=>true,
            'default_in_list' => true,
        	'label' => '所属分类',       
            'width' => 100,
            'order' => 30,
        ),
        'create_time' => array(
            'in_list'=>true,
            'default_in_list' => true,
            'type' => 'time',
            'label'=>'添加时间',
            'order' => 40,
        ),
        'is_fixed' => array(
            'type' => 'tinyint',
            'default' => 0,
            'label'=>'是否固定',
        ),
        'status' => array(
        	'type' => 'tinyint',
            'default' => 1,
            'label'=>'模板状态',
        	'default_in_list' => true,
        	'in_list' => true
        ),
        'cloud_id' => array(
            'type' => 'int',
            'default' => 0,
            'label'=>'云模板ID',
            'default_in_list' => false,
            'in_list' => false
        ),
    ),
    'index' =>
    array(
        'ind_title' =>
        array(
            'columns' =>
            array(
                0 => 'title',
            ),
        ),
    ),
    'engine' => 'innodb',
);