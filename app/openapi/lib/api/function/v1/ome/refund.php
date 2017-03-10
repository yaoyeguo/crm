<?php

class openapi_api_function_v1_ome_refund{

    public function add($params,&$code,&$sub_msg){
        $params = $params['content'];

        $member_points_obj = kernel::single('ecorder_rpc_response_refund');
        $res = $member_points_obj->add($params);
        return $res;
    }
}