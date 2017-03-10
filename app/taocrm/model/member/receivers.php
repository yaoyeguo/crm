<?php
class taocrm_mdl_member_receivers extends dbeav_model{

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null, $forceIndex='')
    {
        $rs = parent::getList($cols, $filter, $offset, $limit, $orderType, $forceIndex);
        if($rs){
            foreach($rs as $v){
                $member_ids[] = $v['member_id'];
            }

            if($member_ids){
                $mdl_members = app::get('taocrm')->model('members');
                $areasInfo = $mdl_members->getAllAreasInfo($member_ids);
                if($areasInfo){
                    foreach($rs as $k=>$v){
                        if(isset($areasInfo[$v['member_id']])){
                            $rs[$k]['area'] = explode('/', $areasInfo[$v['member_id']]['area']);
                            $rs[$k]['state'] = isset($rs[$k]['area'][0]) ? $rs[$k]['area'][0] : '';
                            $rs[$k]['city'] = isset($rs[$k]['area'][1]) ? $rs[$k]['area'][1] : '';
                            $rs[$k]['district'] = isset($rs[$k]['area'][2]) ? $rs[$k]['area'][2] : '';
                        }
                    }
                }
            }
        }
        return $rs;
    }
    
    public function _filter($filter,$tableAlias=null,$baseWhere=null)
    {
        if (is_array($filter)) {
            if (isset($filter['member_id']) && !is_numeric($filter['member_id'])){
                $memberObj = $this->app->model("members");
                $_filter = array();
                if($filter['member_id']) $_filter['uname|head'] = $filter['member_id'];
                $rows = $memberObj->getList('member_id', $_filter);
                $memberId[] = 0;
                foreach($rows as $row){
                    $memberId[] = $row['member_id'];
                }
                $where .= '  AND member_id IN ('.implode(',', $memberId).')';
                unset($filter['member_id']);
            }
            return parent::_filter($filter,$tableAlias,$baseWhere).$where;
        }
        else {
            return $filter;
        }
    }
    
}
