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
$db['sms_blacklist'] = array(
    'columns' => array(
        'id' =>  array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        'member_id' => array (
            'type' => 'table:members@taocrm',
            'label'=> '客户编号',
        ),
        'contact_id' => array (
            'type' => 'table:member_contacts@taocrm',
            'label'=> '联系人',
        ),
        'create_time' => array(
            'type' => 'time',
            'label'=>'添加时间',
            'in_list'=>true,
            'default_in_list' => true,
        ),
    ),
    'index' =>
    array(
        'ind_member_id' =>
        array(
            'columns' =>
            array(
                0 => 'member_id',
            ),
        ),
    ),
    'engine' => 'innodb',
);