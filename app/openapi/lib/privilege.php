<?php
class openapi_privilege {

    static public function checkAccess($flag,$obj,$method){
        if(!$flag){
            return false;
        }
        $settingObj = &app::get('openapi')->model('setting');
        $settingInfo = $settingObj->dump(array('code'=>$flag,'status'=>1),'*');
        if($settingInfo){
            if(isset($settingInfo['config'][$obj]) && in_array($method,$settingInfo['config'][$obj])){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

}