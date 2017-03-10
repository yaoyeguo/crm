<?php
/**
 * taocrm_finder_rebate_payment
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author liuqi 
 * @license PHP Version 3.0 liuqi1@shopex.cn
 */
class taocrm_finder_rebate_cycle {

	var $detail_cycle = "周期返利详情";

    function detail_cycle($id)
    {
        if(!$id){
            $id = $_GET['id'];
        }
        $app = app::get('taocrm');
        $rebate = $app->model('rebate');
        $rebates = $rebate->getList('*',array('rebate_cycle_id'=>$id));
        $pagelimit = 10;//分页
        $page_log = max(1, intval($_GET['page_log']));
        $offset = ($page_log - 1) * $pagelimit;
        $logs = $rebate->db->select('select * from sdb_taocrm_rebate where rebate_cycle_id = '.$id.' order by id, create_time desc limit '.$offset.','.$pagelimit);
        $count = $rebate->count(array('rebate_cycle_id'=>$id));
        $render = $app->render();
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page_log, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_rebate_payment&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_cycle&page_log=%d&id='.$id));

        $render->pagedata['rebates'] = $rebates;
        $render->pagedata['pager'] = $pager;
        $ecorder_orders = app::get('ecorder')->model('orders');

        if(count($logs)){
            foreach($logs as &$v){
                if(!empty($v['order_id'])){
                    $order_bn = $ecorder_orders->dump($v['order_id'],'order_bn');
                    $v['order_id'] = $order_bn['order_bn'];
                }
            }
        }
        $render->pagedata['logs'] = $logs;
        $render->pagedata['finder_id'] = $_GET['finder_id'];
        return $render->fetch('admin/rebate/rebate.html');
    }

}
