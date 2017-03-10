<?php
class market_finder_wx_survey {
     
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $survey_id = $row[$this->col_prefix.'survey_id'];

        $button1  = '<a href="index.php?app=market&ctl=admin_weixin&act=survey_edit&survey_id='.$survey_id.'&finder_id='.$finder_id.'" target="dialog::{width:650,height:355,title:\'编辑活动\'}">编辑</a>';

        return $button1;
    }
    
    //活动参与人数
    var $detail_basic = '详细信息';
    public function detail_basic($survey_id)
    {
        $app = app::get('market');
		$oLog = $app->model('wx_survey_log');
		
 		$render = $app->render();
        if(!$survey_id) $survey_id = $_GET['survey_id'];
        //分页
        $pagelimit = 10;
        $page = max(1, intval($_GET['page']));
        $offset = ($page - 1) * $pagelimit;
        $filter = array('survey_id'=>$survey_id);
        $logs = $oLog->getList('*', $filter, $offset, $pagelimit, 'survey_log_id desc');
        $count = $oLog->count($filter);
        $view = $_GET['view'];
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=market&ctl=admin_weixin&act=survey&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&survey_id='.$survey_id.'&finderview=detail_basic&page=%d&view='.$view));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['logs'] = $logs;        
		return $render->fetch('admin/weixin/survey_logs.html');
    }
}
