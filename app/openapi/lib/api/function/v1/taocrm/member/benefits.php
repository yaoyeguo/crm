<?php

class openapi_api_function_v1_taocrm_member_benefits{

    public function add($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_rpc_response_member_benefits');
        $responseObj = $this;

        $res = $member_points_obj->get($params,$responseObj);
        print_r($res);exit;
        return $res;
    }

    public function additem($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_rpc_response_member_benefits');
        $res = $member_points_obj->additem($params);
        return $res;
    }

    public function getlogs($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_rpc_response_member_benefits');
        $res = $member_points_obj->getlogs($params);
        return $res;
    }
}