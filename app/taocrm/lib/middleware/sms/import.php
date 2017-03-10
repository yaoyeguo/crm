<?php

/**
 * 外部客户短信发送
 */

class taocrm_middleware_sms_import extends taocrm_middleware_connect implements taocrm_interface_middleware
{

    //外部客户短信发送
    public function send($data, &$err_msg)
    {
        //应用参数
        $params['smsTemplateA'] = $data['sms_content'];
        $params['batchId'] = $data['batch_id'];
        $params['taskId'] = $data['sms_id'];
        $params['opUser'] = $data['op_user'];
        $params['ip'] = $data['ip'];
        $params['smsOrMail'] = 'sms';
        
        //短信平台参数
        $params['sendType'] = 'fan-out';
        $params['entId'] = $data['entId'];
        $params['entPwd'] = $data['entPwd'];
        $params['license'] = $data['license'];

        //系统参数
        $params['method'] = 'sdop.marketing.statistics.get';
        $params['targets'] = '840005';
        $params['dbName'] = $this->getDbName();

        $data = $this->getReturnData($this->memoServiceUrl, $params);
        $err_msg = $data;
        $data = json_decode($data,true);
        //err_log($data);die();
        if(is_array($data)){
            $res = $data[$params['targets']]['data']['status'];
        }else{
            $res = false;
        }
        return $res;
    }

}
