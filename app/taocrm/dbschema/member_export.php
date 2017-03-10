<?php 

$db['member_export'] = array(
	'columns' => array(
			'export_id' => array(
			'type' => 'number',
			'required' => true,
			'pkey' => true,
			'extra' => 'auto_increment'
			),
		 'total_num' => 
			array (
              'type' => 'int',
              'default' => 0,
              'label' => '总数',
              'width' => 130,
              'editable' => false,
              'in_list' => true,
              'default_in_list' => true,
			  'order' => 10,
			),
        'export_param' => array(
			'type' => 'text',
			'required' => false,
            'label' => '导出条件',
			'width' => 130,
            'editable' => false,
			),
        'export_status' => array (
          'type' =>   array (
    	        'succ' => '导出成功',
    	        'fail' => '导出失败',
    	        'unexport' => '准备导出',
        		'exporting' => '导出中',
	       ),
          'default' => 'unexport',
          'required' => FALSE,
          'label' => '导出状态',
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
    	   'order' => 20,
          ),
  	  'create_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '创建时间',
            'width' => 140,
            'order' => 30,
        ),
     'finish_time' =>
        array(
            'type' => 'time',
            'required' => false,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => '完成时间',
            'width' => 140,
            'order' => 40,
        ),
         /* 'download_url' =>
        array(
            'type' => 'varchar(255)',
            'label' => '下载地址',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 50,
        ),*/
           'export_content' =>
        array(
            'type' => 'LongBlob',
            'label' => '导出内容',
            'required' => false,
            'editable' => false,
            'in_list' => false,
        	//'searchtype' => 'has',
            'default_in_list' => false,
            'width' => 120,
            'order' => 50,
        ),
    ),
    'ignore_cache' => true,
    'engine' => 'innodb',
);
