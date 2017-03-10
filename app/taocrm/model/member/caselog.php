<?php
class taocrm_mdl_member_caselog extends dbeav_model{
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null, $forceIndex='')
    {
        $rs = parent::getList($cols, $filter, $offset, $limit, $orderType, $forceIndex);
        if($rs){
            foreach($rs as $v){
                $member_ids[] = $v['_0_member_id'];
            }
            
            //获取每个会员的帐号
            if($member_ids){
                $mdl_members = app::get('taocrm')->model('members');
                $rs_members = $mdl_members->getList('uname,member_id', array('member_id'=>$member_ids));
                if($rs_members){
                    foreach($rs_members as $v){
                        $unames[$v['member_id']] = $v['uname'];
                    }
                
                    foreach($rs as $k=>$v){
                        if(isset($unames[$v['_0_member_id']]))
                            $rs[$k]['uname'] = $unames[$v['_0_member_id']];
                    }
                }
            }
        }
        return $rs;
    }    

}