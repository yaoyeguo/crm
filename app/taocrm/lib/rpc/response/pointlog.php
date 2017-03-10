<?php

class taocrm_rpc_response_pointlog extends taocrm_rpc_response
{

    /**
     *
     *
     *
     * @param unknown_type $sdf
     * @param unknown_type $responseObj
     */
    public function getlist($sdf, &$responseObj){
        $apiParams = array(
            'member_id'=>array('label'=>'客户ID','required'=>true),
            'shop_id'=>array('label'=>'店铺ID','required'=>false),
            'page_size'=>array('label'=>'page_size','required'=>true),
            'page'=>array('label'=>'page','required'=>true),
            'node_id'=>array('label'=>'节点ID','required'=>false),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);
        /*if(base_rpc_service::$node_id){
            $sdf['shop_id'] = $this->get_shop_id($responseObj);
        }else{
            $sdf['shop_id'] = 0;
        }*/
        $pointObj=kernel::single("taocrm_member_point");
        $msg = '';
        $memberPointLogList = $pointObj->getPointLogList($sdf['shop_id'],$sdf['member_id'],$sdf['page_size'],$sdf['page'],$msg);

        return array('point_log_list'=>$memberPointLogList['logs'],'total_result'=>$memberPointLogList['totalResult']);
    }




}