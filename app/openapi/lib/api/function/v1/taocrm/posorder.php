<?php

class openapi_api_function_v1_taocrm_posorder{

    public function add($params,&$code,&$sub_msg){
        $params = $params['content'];
        $params['order_items'] = json_encode($params['order_items']);
        $member_points_obj = kernel::single('taocrm_rpc_response_posorder');
        $res = $member_points_obj->add($params);
        if(!$res) $code = 'e000008';
        return $res;
    }
}