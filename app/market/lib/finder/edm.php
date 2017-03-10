<?php 
class market_finder_edm {
    protected static $apps = array();
    var $detail_basic = '基本信息';
    public function detail_basic($sms_id){
        $smsInfo = $this->getSendEdmInfo($sms_id);
        $activeId = $smsInfo['active_id'];
        $sendList = $this->getSendList($activeId);
        $app = $this->getAppObj('market');
        $render = $app->render();
        if ($sendList['count'] > 0) {
            $render->pagedata['list'] = $sendList['list'];
            return $render->fetch('admin/active/smssenddetailhardware.html');
        }
        else {
            $smslog = $app->model('sms_log');
            $sql="select reason,status from sdb_market_edm_log where edm_id=".$sms_id;
            $result_data = $smslog->db->select($sql);
            if (empty($result_data)){
                $render->pagedata['tag'] = true;
                }else {
                $render->pagedata['activedata'] = $result_data;
            }
            return $render->fetch('admin/active/smssenddetail.html');
        }
    }
    
    /**
     * 获得发送列表
     */
    protected function getSendList($activeId)
    {
        $app = $this->getAppObj('market');
        $activeMemberModel = $app->model('active_member');
        $filter = array('active_id' => $activeId, 'send_type' => 2);
        //每页记录条数
//        $limit = 50;
        //页码
//        $page = max(1, intval($_GET['page']));
        //记录当前位置
//        $offset = ($page - 1) * $limit;
        $count = $activeMemberModel->count($filter);
        $list = array();
        if ($count > 0) {
            //成功发送人数
            $sendFilter = array('issend' => 1);
            $sendFilter = array_merge($sendFilter, $filter);
            $sendCount = $activeMemberModel->count($sendFilter);
            //失败发送人数
            $faildFilter = array('issend' => 2);
            $faildFilter = array_merge($faildFilter, $filter);
            $faildCount = $activeMemberModel->count($faildFilter);

            $list['count'] = $count;
            $list['sendCount'] = $sendCount;
            $list['faildCount'] = $faildCount;
            //$send = 
//            $data = $activeMemberModel->getList('*', $filter, $offset, $limit);
//            $memberIds = '';
//            $member
//            foreach ($data as $v) {
//                $memberIds .= $v['member_id'] . ',';
//            }
//            $memberIds = trim($memberIds, ",");
//            $memberSql = "SELECT member_id, uname, name, mobile, email FROM sdb_taocrm_members WHERE member_id IN ({$memberIds})";
//            $memberInfo = $activeMemberModel->db->select($memberSql);
//            echo $memberSql;
        }
        return array('count' => $count, 'list' => $list);
    }
    
    /**
     * 获得发送邮件信息
     * @param int $sms_id 短信发送ID
     */
    protected function getSendEdmInfo($id)
    {
        $app = $this->getAppObj('market');
        $model = $app->model('edm');
        $smsInfo = $model->dump(array('id' => $id));
        return $smsInfo;
    }
    
    /**
     * 获得应用APP
     */
    protected function getAppObj($appName)
    {
        if (self::$apps[$appName] == '') {
            self::$apps[$appName] = app::get($appName);
        }
        return self::$apps[$appName];
    }
}