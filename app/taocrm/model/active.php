<?php
class taocrm_mdl_active extends dbeav_model{
    var $defaultOrder = array('createtime','DESC');

    public function getMemberList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        if(isset($filter['active_id']) && $filter['active_id'] > 0){
            $where = ' A.active_id='.$filter['active_id'];
        }
        $sql = 'SELECT M.uname,M.member_id FROM '.
            'sdb_taocrm_active_member as A LEFT JOIN '.
            'sdb_taocrm_members as M ON A.member_id=M.member_id WHERE '.$where;

        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);
        $rows = $this->db->selectLimit($sql,$limit,$offset);
        return $rows;
    }
}