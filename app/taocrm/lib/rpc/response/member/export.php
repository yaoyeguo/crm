<?php

class taocrm_rpc_response_member_export extends taocrm_rpc_response
{

    public function finish($sdf, &$responseObj){
        $apiParams = array(
            'export_id'=>array('label'=>'导出ID','required'=>true),
            'download_url'=>array('label'=>'下载路径','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $obj = &app::get('taocrm')->model('member_export');
        $export = $obj->get($sdf['export_id']);
        if(!$export){
            $responseObj->send_user_error('没有此导出任务');
        }

        $data  = array('export_id'=>$sdf['export_id'],'export_status'=>'succ','download_url'=>$sdf['download_url'],'finish_time'=>time());
        $obj->save($data);

        return array('export_id'=>$sdf['export_id']);
    }
     

}