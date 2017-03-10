<?php

class openapi_api_function_v1_taocrm_point{

    public function get ($params,&$code,&$sub_msg){
        $params = $params['content'];
        $msg = '';
        $member_points_obj = kernel::single('taocrm_member_point');
        $res = $member_points_obj->get($params['member_id'],$msg,null,$params['node_id'],null);
        if($res){
            return array("member_id"=>$res);
        }else{
            $code = 'e000006';
            $sub_msg = "获取积分失败";
            return false;
        }
    }

    public function update($params,&$code,&$sub_msg){
        $params = $params['content'];
        $msg = '';
        $member_points_obj = kernel::single('taocrm_member_point');
        $res = $member_points_obj->update($params['shop_id'],$params['member_id'],$params['type'],$params['point'],$params['point_desc'],$msg,null,$params['point_type']);
        if($res){
            return array("member_id"=>$res);
        }else{
            $code = 'e000006';
            $sub_msg = "更新积分失败";
            return false;
        }
    }

    public function update_by_parent_code($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_member_point');
        $res = $member_points_obj->update_by_parent_code($params['register_crm_member_id'],$params['parent_code'],$params['point']);
        if($res){
            return array("member_id"=>$res);
        }else{
            $code = 'e000008';
            $sub_msg = "通过推荐码更新积分失败";
            return false;
        }
    }

}