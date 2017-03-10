<?php
$db['api_log']=array (
    'columns' =>
    array (
        'log_id' => array(
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'in_list' => true,
            'default_in_list' => true,
            'label' => '主键ID',
            'order' => 10,
        ),
        'api_name' =>
        array (
            'type' => 'varchar(255)',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'has',
            'label' => 'api方法名',
            'width' => 250,
            'order' => 20,
        ),
        'api_flag' =>
        array (
            'type' => 'varchar(60)',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'has',
            'label' => '访问来源标示',
            'width' => 100,
            'order' => 30,
        ),
        'status' =>
        array (
            'type' =>
            array (
                'succ' => '成功',
                'fail' => '失败',
                'timeout' => '请求超时',
            ),
            'required' => true,
            'default' => 'succ',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'label' => '状态',
            'width' => 60,
            'order' => 40,
        ),
        'params' =>
        array (
            'type' => 'text',
            'editable' => false,
        ),
        'msg' =>
        array (
            'type' => 'text',
            'editable' => false,
            'label' => '返回信息',
            'width' => 160,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 60,
        ),
        'api_type' =>
        array (
            'type' =>
            array (
                'response' => '响应',
                'request' => '请求',
            ),
            'editable' => false,
            'default' => 'response',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'label' => '同步类型',
            'width' => 70,
            'order' => 50,
        ),
        'createtime' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'searchtype' => 'nequal',
            'filtertype' => 'normal',
            'in_list' => true,
            'label' => '发起同步时间',
            'width' => 150,
            'order' => 70,
        )
    ),
    'index' =>
    array (
        'ind_status_type' =>
        array (
            'columns' =>
            array (
                0 => 'status',
            ),
        ),
        'ind_api_type' =>
        array (
            'columns' =>
            array (
                0 => 'api_type',
            ),
        )
    ),
    'comment' => 'api日志',
    'engine' => 'MyISAM ',
    'version' => '$Rev: 44513 $',
);
