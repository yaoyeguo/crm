<?php

class openapi_api_function_v1_taocrm_orders{

    public function search($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_rpc_response_orders');
        $res = $member_points_obj->search($sub_msg);
        if(!$res) $code = 'e000008';
        return $res;
    }
}