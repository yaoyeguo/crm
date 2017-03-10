<?php
class taocrm_finder_member_import_group{

    var $pagelimit = 10;
     
    public function __construct($app){
        $this->app = $app;
        $this->controller = app::get('taocrm')->controller('admin_member_import');
    }

    var $column_edit = '操作';
    var $column_edit_width = 80;
    var $column_edit_order = 2;
    function column_edit($row) {
        $href = '<a href="index.php?app=taocrm&ctl=admin_member_import&act=editGroup&p[0]='.$row['group_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '" target="dialog::{title:\''.app::get('taocrm')->_('编辑客户分组').'\', width:700, height:500}">编辑</a>'.$row[$this->col_prefix.'is_fixed'];
      
        return $href;
    }

    var $detail_export_batch = '分组批次';
    function detail_export_batch($id){
        $app = app::get('taocrm');
        $render = $app->render();
         
        if(!$id) return null;
        $nPage = $_GET['page_batch'] ? $_GET['page_batch'] : 1;

        $tips = '';
        $batchObj = $app->model('member_import_batch');
        $batch = $app->model('member_import');
        $orders = app::get('ecorder')->model('orders');

        $batchList = $batchObj->getBatchList('*',array('group_id' => $id),$this->pagelimit*($nPage-1),$this->pagelimit,'batch_id');
        $send_status = $batchObj->schema['columns']['last_send_status']['type'];

        //统计所有批次的营销效果
        foreach($batchList as $k=>$row){
            $batchList[$k]['last_send_status'] = $send_status[$row['last_send_status']];
            
            if($row['last_send_status'] != 'succ') continue;
            
            $tips = '<div style="color:#666;padding:0 0 5px 0;">* 统计数据基于<b>最后发送时间</b>后15天内的付款订单</div>';
            
            $batch_id = $row['batch_id'];
            $start_time = $row['last_send_time'];
            $end_time = strtotime('+15 day', $start_time);
            $data = $batchObj->get_analysis($batch_id, $start_time, $end_time);
            $batchList[$k] = array_merge($batchList[$k], $data);
            
            if($batchList[$k]['member_num']>0 && $row['total_nums']>0){
                $batchList[$k]['buy_ratio'] = '　('.round($batchList[$k]['member_num']*100/$row['total_nums'],2) . '%)';
            }
        }

        $render->pagedata['batchList'] = $batchList;
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_export_batch';
        $count = $batchObj->count(array('group_id' => $id));

        $total_page = ceil($count / $this->pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $nPage, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member_import&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_export_batch&page_batch=%d' ));
        $render->pagedata['tips'] = $tips;
        $render->pagedata['pager'] = $pager;
        $render->pagedata['refresh_url'] = 'index.php?app=taocrm&ctl=admin_member_import&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_export_batch&page_batch='.$nPage;
        return $render->fetch('admin/member/import/batch.html');
    }
    
    var $detail_list = '数据明细';
    function detail_list($id){
        $app = app::get('taocrm');
        $render = $app->render();
         
        if(!$id) return null;
        $nPage = $_GET['page2'] ? $_GET['page2'] : 1;
        
        $q = $_GET['q'] ? $_GET['q'] : 1;
        unset($_GET['q']);

        $batchObj = $app->model('member_import');

        //搜索条件
        $filter = array('group_id' => $id);
        if($q['mobile']) $filter['mobile|has'] = $q['mobile'];
        if($q['uname']) $filter['uname|has'] = $q['uname'];
        
        $batchList = $batchObj->getlist(
            '*',
            $filter,
            $this->pagelimit*($nPage-1),
            $this->pagelimit,
            'member_id DESC'
        );

        $render->pagedata['batchList'] = $batchList;
        if($_GET['page2']) unset($_GET['page2']);
        $_GET['page2'] = 'detail_export_batch';
        $count = $batchObj->count($filter);

        $total_page = ceil($count / $this->pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $nPage, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member_import&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&q[mobile]='.$q['mobile'].'&q[uname]='.$q['uname'].'&id='.$id.'&finderview=detail_list&page2=%d' ));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['q'] = $q;

        return $render->fetch('admin/member/import/list.html');
    }
}
