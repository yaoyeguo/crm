
<?php
 
$db['wx_survey_result']=array (
    'columns' => 
    array (
    'result_id' =>
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
    'item_id' =>
    array(
        'type' => 'table:wx_survey_items@market',
        'required' => false,
        'label' => '题目编号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 150,
        'order' => 20,
    ),
      'survey_log_id' =>
    array(
        'type' => 'table:wx_survey_log@market',
        'required' => false,
        'label' => '调查快照ID',
        'editable' => false,
        'width' => 150,
        'order' => 20,
    ),
    'result_no' =>
    array(
        'type' => 'varchar(32)',
        'required' => false,
        'label' => '答案编号',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 80,
        'order' => 30,
    ),
    'result' =>
    array(
        'type' => 'varchar(500)',
        'required' => false,
        'label' => '回答内容',
        'editable' => false,
        'searchtype' => 'head',
        'filtertype' => 'normal',
        'in_list' => true,
        'default_in_list' => true,
        'width' => 200,
        'order' => 40,
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
  'comment' => '问卷调查结果',
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
