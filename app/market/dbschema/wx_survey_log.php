<?php
 
$db['wx_survey_log']=array (
    'columns' => 
    array (
    'survey_log_id' =>
    array(
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
    ),
    'survey_id' =>
    array(
        'type' => 'table:wx_survey@market',
        'required' => false,
        'label' => '活动编号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 100,
        'order' => 10,
    ),
       'start_date' => 
    array (
        'type' => 'time',
        'label' => '开始时间',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 105,
        'order' => 90,
    ),
      'end_date' => 
        array (
          'type' => 'time',
          'label' => '结束时间',
          'width' => 130,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filtertype' => 'normal',
          'filterdefault' =>true,
          'order' => 100,
        ),
    'result' =>
    array(
        'type' => 'text',
        'required' => false,
        'label' => '全部回答内容',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 200,
        'order' => 40,
    ),
    'survey_items' =>
    array(
        'type' => 'text',
        'required' => false,
        'label' => '题目快照',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => false,
        'default_in_list' => false,
        'width' => 85,
        'order' => 50,
    ),
    'wx_id' =>
    array(
        'type' => 'varchar(100)',
        'required' => false,
        'label' => '微信用户识别码',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 120,
        'order' => 60,
    ),
    'status' =>
        array(
         'type' => array(
                'survey'=>'问答中',
                'sysclose'=>'系统关闭',
                'userclose'=>'用户关闭',
        		'finish'=>'完成',
          ),
        'default' => 'survey',
        'required' => false,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'label' => '问答状态',
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
    'created' => 
    array (
      'type' => 'time',
      'label' => '创建时间',
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' =>true,
      'order' => 100,
    ),
  ),
  'index' =>
    array(
    	 'ind_wx_id' =>
        array(
            'columns' =>
            array(
                0 => 'wx_id',
            ),
        ),
    ),
  'comment' => '问卷调查快照',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
