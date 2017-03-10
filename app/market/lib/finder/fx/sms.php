<?php
class market_finder_fx_sms{

    var $pagelimit = 10;
     
    public function __construct($app){
        $this->app = $app;
    }


    var $detail_sms_log= '发送明细';
    function detail_sms_log($id){
        $app = app::get('market');
        $render = $app->render();
         
        if(!$id) return null;
        $nPage = $_GET['page'] ? $_GET['page'] : 1;

        $smsObj = $app->model('fx_sms');

        $smsLogList = $smsObj->getSmsLogList('*',array('sms_id' => $id),$this->pagelimit*($nPage-1),$this->pagelimit,'page');
        $hash =  array (
	        '0' => '未发送',
	        '1' => '发送成功',
	        '2' => '发送失败',
        );
        foreach($smsLogList as $k=>$row){
            $smsLogList[$k]['is_send'] = $hash[$row['is_send']];
        }

        $render->pagedata['smsLogList'] = $smsLogList;
        if($_GET['page']) unset($_GET['page']);
        $count = $smsObj->count(array('sms_id' => $id));

        $total_page = ceil($count / $this->pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $nPage, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member_import&act=sms&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_sms_log&page=%d' ));
        $render->pagedata['pager'] = $pager;

        return $render->fetch('admin/fx/sms/sms_log.html');
    }

}