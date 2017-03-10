
<?php
 
$db['wx_survey_items']=array (
    'columns' => 
    array (
    'item_id' =>
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
        'label' => '问题描述',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 240,
        'order' => 10,
    ),
    'is_active' =>
    array(
        'type' => array(
            '1'=>'启用',
            '0'=>'不启用',
        ),
        'required' => false,
        'label' => '状态',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 85,
        'order' => 10,
    ),
     'item_type' => 
    array (
        'type' => array(
            '1'=>'选择题',
            '2'=>'文字题',
        ),
        'label' => '问题类型',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 105,
        'order' => 90,
    ),
      'options' => 
        array (
          'type' => 'text',
          'label' => '问题选项',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>false,
        ),
        'option_tags' => 
        array (
          'type' => 'text',
          'label' => '问题标志',
          'width' => 130,
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>false,
        ),
        'remark' => 
        array (
          'type' => 'text',
          'label' => '备注',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>true,
        ),
        'created' => 
        array (
          'type' => 'datetime',
          'label' => '创建时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => false,
          'filtertype' => 'normal',
          'filterdefault' =>true,
        ),
        'modified' => 
        array (
          'type' => 'datetime',
          'label' => '更新时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'normal',
          'filterdefault' =>true,
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
  'comment' => '问卷调查题目',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
