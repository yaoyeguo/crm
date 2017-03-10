<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 *  邮件模板表
 */
$db['edm_templates'] = array(
    'columns' => array(
        'theme_id' =>  array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        'theme_title' =>
            array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'default_in_list'=>true,
            'searchtype'=>true,
            'label'=> '模板名称',
            
        ),
        'theme_content' => array(
            'type' => 'text',
        	'in_list'=>false,
        	'default_in_list'=>false,
            'searchtype'=>true,
            'required' => true,
            'label'=>'内容',
        ),
        'type_id' => array(
        	'type' => 'table:edm_tclass@market',
        	//'type'=>'varchar(100)',
            'in_list'=>true,
            'default_in_list' => true,
        	'label' => '所属分类',
        	'in_list' => true,
               
        ),
        'create_time' => array(
            'in_list'=>true,
            'default_in_list' => true,
            'type' => 'time',
            'label'=>'创建时间',
        	'in_list' => true,
            'default_in_list' => true,
        ),
        
        'status' => array(
        	'type' => 'tinyint',
            'default' => 1,
            'label'=>'模板状态',
        	'default_in_list' => true,
        	'in_list' => true
        ),
        'mbt_theme_id' => array(
        	'type' => 'number',
        	'default' => 0,
            'label'=>'模板堂模板ID',
        )
    ),
    'engine' => 'innodb',
);