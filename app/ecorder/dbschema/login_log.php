<?php
$db['login_log'] = array(
    'columns' => array(
        'id' =>  array (
            'type' => 'number',
            'required' => true,
            'extra' => 'auto_increment',
            'pkey' => true
        ),
        'login_time' => array(
        	'type' => 'datetime',
            'in_list'=>true,
        	'required' => true,
            'default_in_list' => true,
        	'label' => '操作时间',
        	'order' => 10    
        ),
        'operate_type' => array(
            'type' => array(
                'login' => '登陆',
                'delete' => '删除',
            ),
            'default'=>'login',
            'in_list'=>true,
            'required' => false,
            'default_in_list' => true,
            'label' => '操作类型',
            'order' => 11
        ),
        'ip' => 
        array (
            'type' => 'varchar(20)',
            'editable' => false,
            'label' => 'IP地址',
        	'required' => false,
            'in_list' => true,
            'default_in_list' => true,
        	'order' => 20
        ),
        'user_name' =>
            array (
            'type' => 'varchar(100)',
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list' => true,
            'label'=> '用户名',
            'filtertype'=>false,
            'searchtype'=>true,
            'searchtype' => 'has',
            'order' => 30,
           
        ),
        'status' => array(
        	'type' =>
            array(
                'succ' => '成功',
                'fail' => '失败',
            ),
            'in_list'=>true,
            'is_title'=>true,
            'default_in_list' => true,
            'label'=> '状态',
            'order' => 40,
        ),
        'addon' => array(
            'type' => 'text',
            'default' => '',
            'label'=>'其他',
        	'order' => 60
        ),
    ),  
    'index' =>
    array(
        'ind_title' =>
        array(
            'columns' =>
            array(
                0 => 'user_name',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);