<?php

class market_ctl_admin_weixin_points extends desktop_controller {

    var $workground = 'market.weixin';

    public function savePoint()
    {
        $this->begin('index.php?app=market&ctl=admin_weixin&act=openId');
        $point_desc = trim($_POST['point_desc']);
        $wx_member_id = $_POST['wx_member_id'];
        $point = intval($_POST['point']);
        $op_type = intval($_POST['op_type']);
        
        if($point===0 or !$point_desc){
            $this->end(false, '请输入积分和备注');
        }
        
        $objWxMember = app::get('market')->model('wx_member');
        if($op_type == 1){
            $id = $objWxMember->updatePoint($wx_member_id,2,$point,'手工增加:'.$point_desc,$msg);
        }else{
            $id = $objWxMember->updatePoint($wx_member_id,2,-$point,'手工扣减:'.$point_desc,$msg);
        }

        if($id){
            $this->end(true,'操作成功');
        }else{
            $this->end(false,$msg);
        }
    }
    
}

