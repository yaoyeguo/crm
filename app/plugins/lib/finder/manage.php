<?php
class plugins_finder_manage {

    var $addon_cols = 'worker,plugin_id';

	var $detail_basic = '运行日志';
	public function detail_basic($plugin_id){
        $app = &app::get('plugins');
		$oLog = $app->model('log');
        //$logs = $oLog->getList('*',array('plugin_id'=>$plugin_id),0,20,'log_id desc');

 		$render = $app->render();
        if(!$plugin_id) $plugin_id = $_GET['plugin_id'];
        //分页
        $pagelimit = 10;
        $page = max(1, intval($_GET['page']));
        $offset = ($page - 1) * $pagelimit;
        $filter = array('plugin_id'=>$plugin_id);
        $logs = $oLog->getList('*', $filter, $offset, $pagelimit, 'log_id desc');
        $count = $oLog->count($filter);
        $view = $_GET['view'];
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=plugins&ctl=admin_manage&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&plugin_id='.$plugin_id.'&finderview=detail_basic&page=%d&view='.$view));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['logs'] = $logs;
		return $render->fetch('admin/log.html');
	}

    var $detail_sms_log = '短信日志';
	public function detail_sms_log($plugin_id){
        $app = &app::get('plugins');
		$oLog = $app->model('sms_logs');
        //$logs = $oLog->getList('*',array('plugin_id'=>$plugin_id),0,20,'log_id desc');

 		$render = $app->render();
        if(!$plugin_id) $plugin_id = $_GET['plugin_id'];
        //分页
        $pagelimit = 10;
        $page = max(1, intval($_GET['page2']));
        $offset = ($page - 1) * $pagelimit;
        $filter = array('worker'=>$plugin_id);

        if(isset($_GET['mobile']) && $_GET['mobile']){
            $filter['mobile'] = trim($_GET['mobile']);
        }

        $logs = $oLog->getList('*', $filter, $offset, $pagelimit,'id desc');
        $count = $oLog->count($filter);
        $view = $_GET['view'];
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=plugins&ctl=admin_manage&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&plugin_id='.$plugin_id.'&finderview=detail_sms_log&page2=%d&view='.$view));
        $render->pagedata['mobile'] = $_GET['mobile'];
        $render->pagedata['pager'] = $pager;
        $render->pagedata['logs'] = $logs;
		return $render->fetch('admin/sms_logs.html');
	}

    /*
    var $detail_log = '购买记录';
	public function detail_log($plugin_id){
        $app = &app::get('plugins');
		$oOrders = $app->model('orders');
		$orders = $oOrders->getList('*',array('plugin_id'=>$plugin_id),0,20,'order_id desc');

        //获取插件的截止时间
        $plugin_desc = array();
        if(isset($orders[0]['worker'])){
            $plugin_desc = kernel::single($orders[0]['worker'])->get_desc();
        }

 		$render = $app->render();
		$render->pagedata['orders'] = $orders;
		$render->pagedata['plugin_desc'] = $plugin_desc;
		return $render->fetch('admin/orders.html');
	}
    */

	var $column_edit = "操作";
    var $column_edit_width = 80;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        $finder_id = $_GET['_finder']['finder_id'];
        $worker = $row[$this->col_prefix.'worker'];
        $plugin_id = $row[$this->col_prefix.'plugin_id'];

        if($row['end_time']>=time()){
            $button1 = '<a href="index.php?app=plugins&ctl=admin_manage&act=set&p[0]='.$plugin_id.'&finder_id='.$finder_id.'" target="dialog::{width:680,height:350,title:\'插件设置\'}">设置</a>　';
            $button1 .= '<a href="index.php?app=plugins&ctl=admin_buy&act=close_plugin&plugin_id='.$worker.'&finder_id='.$finder_id.'">禁用</a>';
        }else{
            $button1 .= '<a href="index.php?app=plugins&ctl=admin_buy&act=buy&worker='.$worker.'&finder_id='.$finder_id.'" target="dialog::{width:600,height:230,title:\'启用插件\'}">启用</a>';
        }

        return $button1;
    }

}