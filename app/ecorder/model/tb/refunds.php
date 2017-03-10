<?php

class ecorder_mdl_tb_refunds extends dbeav_model{

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        $data = parent::getList($cols, $filter, $offset, $limit, $orderby);
        $mdl_member_analysis = app::get('taocrm')->model('member_analysis');
        $mdl_shop_lv = app::get('ecorder')->model('shop_lv');
        
        $levels = $mdl_shop_lv->get_lv_name();
               
        foreach($data as $k=>$v){
            if($v['_0_member_id']) $mnember_ids[] = $v['_0_member_id'];
        }
        
        if($mnember_ids){
            //获取会员等级
            $rs_member = $mdl_member_analysis->getList('lv_id,member_id,shop_id', array('member_id'=>$mnember_ids));
            if($rs_member){
                foreach($rs_member as $v){
                    $level_name[$v['shop_id'].'_'.$v['member_id']] = $levels[$v['lv_id']];
                }
            }
            
            //获取会员标签
            $oTag = app::get('taocrm')->model('member_tag');
            $tagInfo = $oTag->getMemberTagInfo($mnember_ids);

            foreach($data as $k=>$v){
                $data[$k]['level_name'] = $level_name[$v['_0_shop_id'].'_'.$v['_0_member_id']];
                if(isset($tagInfo[$v['_0_member_id']]))
                    $data[$k]['tagInfo'] = implode('；', $tagInfo[$v['_0_member_id']]);
            }
        }

        return $data;
    }

}
