<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_member_export extends dbeav_model{
     
    function get($exportId,$cols='*'){
        $export =  $this->db->selectrow('select '.$cols.' from sdb_taocrm_member_export where export_id='.$exportId);

        return $export;
    }

    function saveExportLog($filter){
        $data = array(
        'total_num'=>$filter['total'],
        'export_param'=>json_encode($filter),
        'export_status'=>'exporting',
        'create_time'=>time(),
        'finish_time'=>0,
        'download_url'=>''
        );
        $this->save($data);

        $filter_data = $filter['filter'];
        $filter_data['export_id'] = $data['export_id'];

        $oMember = app::get('taocrm')->model('middleware_member_analysis');
        $result = $oMember->export($filter_data);


        return $result;
    }
    
    function deleteExpireExport(){
        $time = time() - (86400 * 7);
        $this->db->exec('delete from sdb_taocrm_member_export where create_time <='.$time);
    }
}