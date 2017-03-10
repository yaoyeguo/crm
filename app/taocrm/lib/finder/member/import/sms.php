<?php
class taocrm_finder_member_import_sms{

    var $pagelimit = 10;
     
    public function __construct($app){
        $this->app = $app;
    }

    var $column_group_id = '分组名称[批次号]';
    var $column_group_id_width = 180;
    var $column_group_id_order = 10;
    function column_group_id($row){
        $smsObj = $this->app->model('member_import_sms');
        $sms = $smsObj->getSms($row['sms_id']);
        $oGroup = $this->app->model('member_import_group');
        $data = $oGroup->dump($sms['group_id'],'group_name');

        return $data['group_name'].'['.$sms['group_id'].'-'.$sms['batch_id'].']';
    }


    var $detail_sms_log= '发送明细';
    function detail_sms_log($id){
        $app = app::get('taocrm');
        $render = $app->render();
         
        if(!$id) return null;
        $nPage = $_GET['page2'] ? $_GET['page2'] : 1;

        $smsObj = $app->model('member_import_sms');
        $smsLogObj = $app->model('member_import_sms_log');
        $batchObj = $app->model('member_import_batch');

        $sms = $smsObj->dump($id);

        //营销效果
        $batch_id = $sms['batch_id'];
        $start_time = $sms['last_send_time'];
        $end_time = strtotime('+15 day', $start_time);
        $analysis = $batchObj->get_analysis($batch_id, $start_time, $end_time);
        
        if($analysis['member_num']>0 && $sms['succ_num']>0){
            $analysis['buy_ratio'] = '　('.round($analysis['member_num']*100/$sms['succ_num'],2) . '%)';
        }

        $smsLogList = $smsObj->getSmsLogList('*',array('sms_id' => $id),$this->pagelimit*($nPage-1),$this->pagelimit,'sms_log_id DESC');
        $hash =  array (
	        '0' => '未发送',
	        '1' => '发送成功',
	        '2' => '发送失败',
        );
        foreach($smsLogList as $k=>$row){
            $smsLogList[$k]['is_send'] = $hash[$row['is_send']];
        }

        $render->pagedata['smsLogList'] = $smsLogList;
        if($_GET['page2']) unset($_GET['page2']);
        $count = $smsLogObj->count(array('sms_id' => $id));

        $total_page = ceil($count / $this->pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $nPage, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member_import&act=sms&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_sms_log&page2=%d' ));
        
        $render->pagedata['analysis'] = $analysis;
        $render->pagedata['pager'] = $pager;
        $render->pagedata['sms'] = $sms;
        return $render->fetch('admin/member/import/sms_log.html');
    }

}