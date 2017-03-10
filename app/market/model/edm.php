<?php 
class market_mdl_edm extends dbeav_model {
var $detail_basic = '基本信息';
    public function detail_basic($sms_id){
        $app = &app::get('market');
        $render = $app->render();
        $smslog=&app::get('market')->model('sms_log');
        $sql="select reason,status from sdb_market_sms_log where id=".$sms_id;
        $result_data=$smslog->db->select($sql);
        if (empty($result_data)){
            $render->pagedata['tag'] = true;
            }else {
            $render->pagedata['activedata'] = $result_data;
        }
        return $render->fetch('admin/active/edmsenddetail.html');
    }

}