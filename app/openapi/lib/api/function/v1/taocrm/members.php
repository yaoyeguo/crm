<?php

class openapi_api_function_v1_taocrm_members {

    public function add($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_members');
        $res = $member_points_obj->add($params,$sub_msg);
        if(!$res) $code = 'e000008';
        return $res;
    }
    public function get($params,&$code,&$sub_msg){
        $params = $params['content']['member_id'];
        $member_points_obj = kernel::single('taocrm_members');
        $res = $member_points_obj->get($params,$sub_msg);
        if(!$res) $code = 'e000008';
        return $res;
    }
    public function getlist($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_members');
        $res = $member_points_obj->getMembers($params,$sub_msg);
        if(!$res) $code = 'e000008';
        return $res;
    }
    public function update($params,&$code,&$sub_msg){
        $params = $params['content'];
        $member_points_obj = kernel::single('taocrm_members');
        $res = $member_points_obj->update($params,$sub_msg);
        if(!$res) $code = 'e000008';
        return $res;
    }
    public function update_recommend($params,&$code,&$sub_msg){
        $params = $params['content'];
        if(!$params['recommended_member_ids']){
            $code = 'e000008';
            $sub_msg = '被推荐会员ID不能为空';
            return false;
        }
        if(!$params['referee_member_id']){
            $code = 'e000008';
            $sub_msg = '推荐会员ID不能为空';
            return false;
        }
        $params['recommended_member_ids'] = explode(',',$params['recommended_member_ids']);
        $recommended_member_obj = app::get('taocrm')->model('members_recommend');
        if(!empty($params['recommended_member_ids'])){
            foreach($params['recommended_member_ids'] as $key => $recommended_member_id){
                $re = $recommended_member_obj->dump(array('member_id'=>$recommended_member_id),'member_id');
                if(!($re['member_id'])){
                    $not_by_member_id_arr[] = $recommended_member_id;
                    unset($params['recommended_member_ids'][$key]);
                }
            }
        }
        $params['recommended_member_ids'] = json_encode($params['recommended_member_ids']);

        if(count($not_by_member_id_arr)){
            $code = 'e000008';
            $sub_msg = '被推荐会员ID'.implode(',',$not_by_member_id_arr)."在推荐关系表没有被找到!";
            return false;
        }
        $member_points_obj = kernel::single('taocrm_members');
        $res = $member_points_obj->update_recommend($params);
        if(!$res) {
            $code = 'e000008';
            $sub_msg = "更新失败！";
            return false;
        }
        return array('member_id'=>$res,'sub_msg'=>'更新成功');
    }
}