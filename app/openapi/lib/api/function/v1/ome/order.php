<?php

class openapi_api_function_v1_ome_order{

    public function add($params,&$code,&$sub_msg){
        $params = $params['content'];
        $params['member_info'] = json_encode($params['member_info']);
        $params['consignee'] = json_encode($params['consignee']);
        $params['order_objects'] = json_encode($params['order_objects']);
        $params['payments'] = json_encode($params['payments']);
        $member_points_obj = kernel::single('ecorder_rpc_response_order');
        $res = $member_points_obj->add($params);
        return $res;
    }
}