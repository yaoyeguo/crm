<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_tag'] = array(
    'columns' => array(
        'tag_id' => array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        'tag_name' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '标签名称',
            'filtertype'=>true,
            'searchtype'=>true,
            'required' => false,
            'searchtype' => 'has',
            'order'=>10,
        ),
        'members' => array(
            'type' => 'number',
            'default' => 0,
            'in_list'=>true,
            'default_in_list'=>true,
            'required' => false,
            'label'=> '客户数',
            'width'=>80,
            'order'=>15,
        ),
         'mobile_valid_nums' =>
        array (
            'type' => 'int',
            'default' => 0,
            'required' => false,
            'label' => '手机有效人数',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'width' => 90,
            'order' => 40,
        ),
        'last_send_time' =>
        array(
            'type' => 'time',
            'default' => 0,
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '最后发送时间',
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
        'get_tag'       => array(
            'type' => array(
                    'true' => '规则开启',
                    'false' => '规则关闭',
                ),
            'default' => 'false',
            'label' => '规则开关',
        ),
        'tag_select'    => array(
            'type' => 'tinyint',
            'label' => '规则类型',
            'default' => 0,
        ),
        'activity_num'  => array(
            'type' => 'int',
            'label' => '活动次数',
            'default' => 0,
        ),
        'one_min'       => array(
            'type' => 'int',
            'label' => '单笔最小金额',
            'default' => 0,
        ),
        'one_max'       => array(
            'type' => 'int',
            'label' => '单笔最大金额',
            'default' => 0,
        ),
        'all_min'       => array(
            'type' => 'int',
            'label' => '总计最小金额',
            'default' => 0,
        ),
        'all_max'       => array(
            'type' => 'int',
            'label' => '总计最大金额',
            'default' => 0,
        ),
        'tag_type'       => array(
            'type' => array(
                    'system_a' => '系统标签',
                    'system_b' => '系统标签',
                    'system_c' => '系统标签',
                    'system_d' => '系统标签',
                    'store' => '商家自动打标',
                    'hand' => '手动打标',
                ),
            'default' => 'hand',
            'label' => '标签类型',
            'in_list' => true,
            'default_in_list' => true,
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

