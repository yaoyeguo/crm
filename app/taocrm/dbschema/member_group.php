<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_group'] = array(
    'columns' => array(
        'group_id' => array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        'group_name' => array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list'=>true,
            'label'=> '分组名称',
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
            'order'=>15,
        ),
        'group_content' => array(
            'type' => 'varchar(500)',
            'required' => false,
            'label'=> '分组描述',
            'order'=>20,
        ),
        'shop_id' =>  array (
            'type' => 'table:shop@ecorder',
            'required' => false,
            'label'=> '所属店铺',
            'order' => 30,
        ),
        'create_time' => array(
            'in_list'=>true,
            'default_in_list' => false,
            'type' => 'time',
            'label'=> '添加时间',
            'order'=> 40,
        ),
        'update_time' => array (
            'type' => 'time',
            'editable' => false,
            'in_list'=>true,
            'default_in_list' => true,
            'label' => '更新时间',
            'order'=> 50,
        ),
        'filter' => array(
            'type' => 'text',
            'required' => false,
            'label'=> '查询条件',
        ),
        'parent_id' => array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label'=> '上级分组',
        ),
        'childs' => array(
            'type' => 'number',
            'default' => 0,
            'required' => false,
            'label'=> '子分组数量',
        ),
        'op_user' => array(
            'type' => 'varchar(32)',
            'required' => false,
            'label'=> '操作人',
        ),
        'create_type' => array(
            'type' => array(
                'system' => '系统',
                'user' => '用户',
            ),
            'required' => false,
            'default' => 'user',
            'label'=> '创建类别',
        ),
    ),
    'index' =>
    array(
        'ind_shop_id' =>
        array(
            'columns' =>
            array(
                0 => 'shop_id',
            ),
        ),
    ),
    'comment' => '客户分组表',
    'engine' => 'innodb',
);

