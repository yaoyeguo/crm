<?php

class openapi_setting{

    static function getConf($flag,$key){
        if(!$flag){ return false; }

        $settingObj = &app::get('openapi')->model('setting');
        $settingInfo = $settingObj->dump(array('code'=>$flag),'*');
        if($settingInfo){
            if($settingInfo[$key]){
                return $settingInfo[$key];
            }else{
                return $settingInfo;
            }
        }else{
            return false;
        }
    }
}