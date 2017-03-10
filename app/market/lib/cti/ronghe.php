<?php

//对接融合通信
class market_cti_ronghe {

    //讯鸟介绍
    public function get_info()
    {
        $info = array(
            'title' => '融合云通讯',
            'logo' => 'ronghe.jpg',
            'desc' => '融合云通讯系统提供来电弹屏对接。当座席来电响铃时，
                       系统以http get方式调用第三方B/S系统的url。
                       并将相关来电数据送给第三方系统。',
            'params' => '',
        );
        
        return $info;
    }

    //呼入
    public function inbound(&$mobile, $params=array())
    {
        if( ! $params) $params = $_GET;
        
        $originCallNo = $params['originCallNo'];//主叫号码
        $originCalledNo = $params['originCalledNo'];//被叫号码
        $offeringTime = $params['offeringTime'];//来电时间
        $Agent = $params['Agent'];//坐席
        
        $mobile = $originCallNo;
    }

    //呼出
    public function outbound()
    {
        base_kvstore::instance('market')->fetch('cti_conf:'.kernel::single('desktop_user')->get_id(), $cti_conf);
        if($cti_conf){
            $cti_conf = json_decode($cti_conf, true);
        }else{
            echo('cti config error');
            exit;
        }
        
        if( ! strstr($cti_conf['ob_url'], 'http://')) 
            $cti_conf['ob_url'] = 'http://'.$cti_conf['ob_url'];
        //$ob_url = 'http://119.254.56.1/app?
        //Action=Dialout&ActionID=1234567890&Account=N0000000012
        //&Exten=01067011234&FromExten=8001&PBX=ds.pbx.1.0';
        $ob_url = $cti_conf['ob_url'].'?Action=Dialout&ActionID='.time().rand(11,99).'&Account='.$cti_conf['account'].'&FromExten='.$cti_conf['exten'].'&PBX='.$cti_conf['pbx'].'';
        $call_script = "window.open('{$ob_url}&Exten='+phone);";
        return $call_script;
    }
    
}