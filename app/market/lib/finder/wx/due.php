<?php
class market_finder_wx_due {
     
    var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $due_id = $row[$this->col_prefix.'due_id'];

        $button1  = '<a href="index.php?app=market&ctl=admin_weixin_due&act=due_edit&due_id='.$due_id.'&finder_id='.$finder_id.'" target="dialog::{width:800,height:400,title:\'编辑\'}">编辑</a>';

        return $button1;
    }
    
    //活动参与人数
    var $detail_basic = '详细信息';
    public function detail_basic($due_id)
    {
        $app = &app::get('market');
		$oLog = $app->model('wx_due_orders');
		
 		$render = $app->render();
        if(!$due_id) $due_id = $_GET['due_id'];
        //分页
        $pagelimit = 10;
        $page = max(1, intval($_GET['page']));
        $offset = ($page - 1) * $pagelimit;
        $filter = array('due_id'=>$due_id);
        $logs = $oLog->getList('*', $filter, $offset, $pagelimit, 'order_id desc');
        
        //解码附加内容
        foreach($logs as $k=>$v){
            $logs[$k]['log'] = json_decode($v['log'], true);
        }
        
        $count = $oLog->count($filter);
        $view = $_GET['view'];
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=market&ctl=admin_weixin_due&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&due_id='.$due_id.'&finderview=detail_basic&page=%d&view='.$view));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['logs'] = $logs;        
		return $render->fetch('admin/weixin/due_orders.html');
    }
}