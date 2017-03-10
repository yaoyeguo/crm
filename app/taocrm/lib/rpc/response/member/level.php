<?php

class taocrm_rpc_response_member_level extends taocrm_rpc_response
{

    public function get($sdf, &$responseObj)
    {
        $level = array();
        $mdl = app::get('taocrm')->model('member_level');
        $rs = $mdl->getList('level_id,level_name');
        if($rs){
            foreach($rs as $v){
                $level[$v['level_id']] = $v['level_name'];
            }
        }
        return $level;
    }

}