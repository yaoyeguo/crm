<?php

class openapi_errorcode {

    static public function get($code){
        $errorInfos = array(
            //系统级错误码
			'e000001' => array('code' => 'e000001','msg' => 'system params lost or error'),
            'e000002' => array('code' => 'e000002','msg' => 'sign error'),
            'e000003' => array('code' => 'e000003','msg' => 'class or method not exist'),
            'e000004' => array('code' => 'e000004','msg' => 'no permissions to access'),
            'e000005' => array('code' => 'e000005','msg' => 'init interface fail'),
    		'e000006' => array('code' => 'e000006','msg' => 'application params error'),
    		'e000007' => array('code' => 'e000007','msg' => 'init template fail'),
            'e000008' => array('code' => 'e000008','msg' => 'submit data error or business logic error'),
        );

        return $errorInfos[$code] ? $errorInfos[$code] : array('code'=>'','msg'=>'');
    }
}