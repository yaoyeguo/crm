<?php

//对接讯鸟
class market_cti_bird {

    //讯鸟介绍
    public function get_info()
    {
        $info = array(
            'title' => '讯鸟通讯',
            'logo' => 'bird.jpg',
            'desc' => '讯鸟提供封装的GUI客户端给二次开发商，
                       开发者可以通过启通宝客户端和客户的业务系统整合;',
            'params' => '',
        );
        
        return $info;
    }

    //呼入
    public function inbound(&$mobile, $params=array())
    {
        /*
        http://www.test.com?actiontype=CallPhone
            &callid=4A71104B0000002F&callerid=2303&calleeid=0106694847
            &calltype=0&uuid=123sadfas
            &OriginalData=01082628946
            &agentname= liuyj@A.com &corpname=物流A公司&deptname=客服组
        */
        if( ! $params) $params = $_GET;
        $actiontype = $params['actiontype'];
        $calleeid = $params['calleeid'];
        $callerid = $params['callerid'];
        $agentname = $params['agentname'];
        
        $mobile = $calleeid;
    }

    //呼出
    public function outbound()
    {
        /*
            http://192.168.51.18/git/crm/index.php#app=market
            &ctl=admin_callcenter_callin&act=index
            ?actiontype=OutDial&callid=N54CA5A3300F40197001984B2
            &callerid=47315118@qq.com&calleeid=17091263345&calltype=-1
            &uuid=&agentname=47315118@qq.com&corpname=广州城视电子商务有限公司&deptname=品牌策划部
        */
        $call_script = "window.external.WebMakeCall(phone);";
        return $call_script;
    }
    
}