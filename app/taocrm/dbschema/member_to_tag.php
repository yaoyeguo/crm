<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_to_tag'] = array(
    'columns' => array(
        'tag_id' => array (
            'type' => 'number',
            'required' => true,
            //'extra' => 'auto_increment',
            //'pkey' => true
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
            'order' => 12,
        ),
        'mobile' =>
            array (
            'type' => 'varchar(30)',
            'label' => '手机',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 105,
            'order' => 90,
            'default' => 0,
        ),
        'tag_type'       => array(
            'type' => array(
                    'system_on' => '系统打标',
                    'system_off' => '系统删除',
                    'hand_on' => '手动打标',
                    'hand_off' => '手动删除',
                ),
            'label' => '标签类型',
            'default' => 'hand_on',
        ),
    ),
    'index' =>
    array(
    	'ind_member_shop' =>
        array(
            'columns' =>
            array(
                0 => 'tag_id',
                1 => 'member_id',
            ),
            'prefix' => 'UNIQUE',
        ),
        'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
    ),
    'comment' => '客户标签关联表',
    'engine' => 'innodb',
);

