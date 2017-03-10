<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class taocrm_mdl_member_export_log extends dbeav_model{
     
     
    function addLog($export_id){
        $kvstore = base_kvstore::instance('taocrm');
        $kvstore->fetch('member_export_passcode', $kv_passcode);
        $mobile = '';
        if($kv_passcode) {
            $kv_passcode = json_decode($kv_passcode, 1);
            $mobile = $kv_passcode['mobile'];
        }

        $data = array('export_id'=>$export_id,'mobile'=>$mobile,'create_time'=>time());
        $this->save($data);
    }
}