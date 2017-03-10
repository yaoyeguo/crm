
<?php
 
$db['wx_due']=array (
    'columns' => 
    array (
        'due_id' =>
        array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
        ),
        'title' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '活动名称',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 240,
            'order' => 10,
        ),
        'keywords' =>
        array(
            'type' => 'varchar(100)',
            'required' => false,
            'label' => '关键词',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 185,
            'order' => 20,
        ),
        'desc' =>
        array(
            'type' => 'varchar(255)',
            'required' => false,
            'label' => '简介',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 185,
            'order' => 20,
        ),
        'start_date' => 
        array (
            'type' => 'time',
            'label' => '开始时间',
            'editable' => false,
            'searchtype' => 'head',
            'filtertype' => 'normal',
            'in_list' => false,
            'default_in_list' => false,
            'width' => 105,
            'order' => 90,
        ),
        'end_date' => 
        array (
          'type' => 'time',
          'label' => '结束时间',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 100,
        ),
        'is_active' => 
        array (
          'type' => array(
            '0'=>'不启用',
            '1'=>'启用',
          ),
          'label' => '活动状态',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 120,
        ),
        'end_words' => 
        array (
          'type' => 'varchar(500)',
          'label' => '结束语',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>true,
        ),
        'due_items' => 
        array (
          'type' => 'text',
          'label' => '预约内容(json格式)',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filterdefault' =>false,
        ),
        'num' => 
        array (
          'type' => 'int',
          'label' => '库存数量',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filterdefault' =>false,
        ),
        'created' => 
        array (
          'type' => 'datetime',
          'label' => '创建时间',
          'editable' => false,
          'searchtype' => 'head',
          'filtertype' => 'normal',
          'in_list' => true,
          'default_in_list' => false,
          'width' => 130,
          'filterdefault' =>true,
        ),
        'modified' => 
        array (
          'type' => 'datetime',
          'label' => '更新时间',
          'searchtype' => 'head',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 110,
        ),
        'picurl' => 
        array (
          'type' => 'varchar(100)',
          'label' => '图片地址',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 110,
        ),
        'req_fields' => 
        array (
          'type' => 'text',
          'label' => '必填资料',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filterdefault' =>false,
        ),
        'content' => 
        array (
          'type' => 'text',
          'label' => '活动详细说明',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filterdefault' =>false,
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
    
    'comment' => '微信预约',
    'engine' => 'innodb',
    'version' => '$Rev:  $',
);
