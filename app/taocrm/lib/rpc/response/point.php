<?php

class taocrm_rpc_response_point extends taocrm_rpc_response{

    /**
     * 积分更新接口
     *
     * @param unknown_type $sdf
     * @param unknown_type $responseObj
     * @type 0表示覆盖更新，1表示增量更新
     */
    public function update($sdf, &$responseObj){

        $apiParams = array(
        	'shop_id'=>array('label'=>'店铺ID','required'=>false),
            'node_id'=>array('label'=>'节点ID','required'=>false),
            'member_id'=>array('label'=>'客户ID','required'=>true),
            'point'=>array('label'=>'积分数值','required'=>true),
            'type'=>array('label'=>'操作类型','required'=>true),
            'points_type'=>array('label'=>'新积分类型','required'=>false),
           // 'point_type'=>array('label'=>'积分类型(No-use)','required'=>false),
            'invalid_time'=>array('label'=>'有效期至','required'=>false),
            'point_desc'=>array('label'=>'积分描述','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        if(base_rpc_service::$node_id){
            $sdf['shop_id'] = $this->get_shop_id($responseObj);
        }
        $shop_id = $sdf['shop_id'];

        $pointObj=kernel::single("taocrm_member_point");
        $msg = '';
        $id = $pointObj->update(
            $shop_id,
            $sdf['member_id'],
            $sdf['type'],
            $sdf['point'],
           // $sdf['point_type'],
            $sdf['point_desc'],
            $msg,
            $sdf['invalid_time'],
            $sdf['points_type']
        );
        if(!$id){
            $responseObj->send_user_error(app::get('base')->_($msg));
        }

        return array('member_id'=>$id);
    }

    //查询客户积分和等级
    public function get($sdf, &$responseObj)
    {
        $apiParams = array(
            'member_id'=>array('label'=>'客户ID','required'=>true),
            'node_id'=>array('label'=>'节点ID','required'=>false),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);
        $node_id = $sdf['node_id'];
        if(!empty($node_id)){
            $shop_obj = app::get('ecorder')->model('shop');
            $shop_data = $shop_obj->dump(array('node_id'=>$node_id));
            $shop_id = $shop_data['shop_id'];
        }
        $pointObj=kernel::single("taocrm_member_point");
        $msg = '';
        $memberPointList = $pointObj->get($sdf['member_id'],$msg,$shop_id,$node_id);
        if(!$memberPointList){
            $responseObj->send_user_error(app::get('base')->_($msg));
        }

        return array('shop_point_list'=>$memberPointList);
    }

    public function weibaolai_point($sdf, &$responseObj){

        $apiParams = array(
            'ToUserName'=>array('label'=>'公众账号ID','required'=>true),
            'FromUserName'=>array('label'=>'微信ID','required'=>true),
            'mobile'=>array('label'=>'用户手机号','required'=>true),
            'user_id'=>array('label'=>'用户微信唯一标示','required'=>true),
            'wx_nick'=>array('label'=>'微信昵称','required'=>false),
            'create_time'=>array('label'=>'创建时间','required'=>false),
            'update_time'=>array('label'=>'修改时间','required'=>false),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);
        $filter = array(
            'mobile' => $sdf['mobile'],
            'user_id' => $sdf['user_id']
        );
        $mdl_wx_member = app::get('market')->model('wx_member');
        $wx_info = $mdl_wx_member->dump($filter);
        if(empty($wx_info)){
            $arr = array(
                'ToUserName'     => $sdf['ToUserName'],
                'FromUserName'   => $sdf['FromUserName'],
                'mobile'         => $sdf['mobile'],
                'user_id'        => $sdf['user_id'],
                'wx_nick'        => $sdf['wx_nick'],
                'create_time'    => time(),
                'update_time'    => time()
            );
            $mdl_wx_member = app::get('market')->model('wx_member');
            $flag = $mdl_wx_member->insert($arr);
            if(!$flag){
                return "数据添加失败,请重试!";exit;
            }
            $wx_points = 0;
            }
        else{
            $wx_points = $wx_info['points'];
        }
        $filt = array(
            'mobile' => $sdf['mobile']
        );
        $mdl_member = app::get('taocrm')->model('members');
        $member_info = $mdl_member->dump($filt);
        if(!empty($member_info)){
            $pointObj=kernel::single("taocrm_mdl_member_points");
            $params = array(
                'member_id' => $member_info['member_id']
            );
            $points = $pointObj->get_member_points($params);
            if(empty($points))$points = 0;;
        }else{
            $points = 0;
        }
        $return_data = array(
            'wx_points' =>$wx_points,
            'shop_points' => $points,
        );
        return $return_data;
    }
     
   public function sync_user($sdf, &$responseObj){
       $apiParams = array(
           'ToUserName'=>array('label'=>'公众账号ID','required'=>true),
           'FromUserName'=>array('label'=>'微信ID','required'=>true),
           'user_id'=>array('label'=>'用户微信唯一标示','required'=>true),
           'mobile'=>array('label'=>'用户手机号','required'=>true),
           'wx_nick'=>array('label'=>'微信昵称','required'=>true),
           'create_time'=>array('label'=>'创建时间','required'=>false),
           'update_time'=>array('label'=>'修改时间','required'=>false),
       );
       $ToUserName = $sdf['ToUserName'];
       $FromUserName = $sdf['FromUserName'];
       $this->checkApiParams($apiParams,$sdf, $responseObj);
       $arr = array(
           'ToUserName'     => $ToUserName,
           'FromUserName'   => $FromUserName,
           'mobile'         => $sdf['mobile'],
           'user_id'        => $sdf['user_id'],
           'wx_nick'        => $sdf['wx_nick'],
           'create_time'    => $sdf['create_time'],
           'update_time'    => $sdf['update_time']
       );
       $filter = array(
           'mobile' => $sdf['mobile'],
           'user_id' => $sdf['user_id']
       );
       $mdl_wx_member = app::get('market')->model('wx_member');
       $wx_info = $mdl_wx_member->dump($filter);
       if(!empty($wx_info)){
           $flag = $mdl_wx_member->update($arr, $filter);
           if($flag) $flag=$wx_info['wx_member_id'];
       }
       else
       {
           $flag = $mdl_wx_member->insert($arr);
       }
       if(!$flag) {
           $arr = array(
               'error' => '1',
               'msg'   => '保存错误，请重试！',
               'wx_member_id' => ''
           );
           return $arr;
       }
       $arr = array(
           'error' => '0',
           'msg'   => '保存成功！',
           'wx_member_id' => $flag
       );
       return $arr;
   }
    /**
     * 通过推荐码更新积分接口
     *
     * @param unknown_type $sdf
     * @param unknown_type $responseObj
     */
    public function update_by_parent_code($sdf, &$responseObj){

        $apiParams = array(
            'register_crm_member_id'=>array('label'=>'注册人CRM会员ID','required'=>true),
            'parent_code'=>array('label'=>'推荐人的推荐码','required'=>true),
            'point'=>array('label'=>'积分修改值','required'=>true)
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $pointObj=kernel::single("taocrm_member_point");
        $msg = '';
        $id = $pointObj->update_by_parent_code(
            $sdf['register_crm_member_id'],
            $sdf['parent_code'],
            $sdf['point'],
            $msg
        );
        if(!$id){
            $responseObj->send_user_error(app::get('base')->_($msg));
        }

        return array('register_crm_member_id'=>$id);
    }
}