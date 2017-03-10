<?php

class taocrm_rpc_response_pointtype extends taocrm_rpc_response
{

    public function add($sdf, &$responseObj){
        if(!isset($sdf['name'])){
            $responseObj->send_user_error(app::get('base')->_('类型名称不存在'));
        }

        if(!isset($sdf['code'])){
            $responseObj->send_user_error(app::get('base')->_('类型代码不存在'));
        }

        $pointTypeObj=kernel::single("taocrm_member_point_type");
        $msg = '';
        $id = $pointTypeObj->add($sdf['name'],$sdf['code'],$msg);
        if(!$id){
            $responseObj->send_user_error(app::get('base')->_($msg));
        }
        
        return array('pointtype_id'=>$id);
    }

    public function getlist($sdf, &$responseObj){
        $pointTypeObj=kernel::single("taocrm_member_point_type");

        return $pointTypeObj->getlist();
    }



}