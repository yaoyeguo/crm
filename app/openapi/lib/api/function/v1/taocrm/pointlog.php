<?php

class openapi_api_function_v1_taocrm_pointlog {

    public function getlist($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_member_point');
        $res = $member_points_obj->getPointLogList($params['shop_id'],$params['member_id'],$params['page_size'],$params['page'],$sub_msg,null);
        if(!$res) $code = 'e000008';
        return $res;
    }
}