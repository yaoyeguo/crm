<?php

class openapi_response{

    function __construct(){
        $this->format = kernel::single('openapi_format_abstract');
    }

    public function send_result($data,$charset,$type,$root='response')
    {
        $res = array(
            $root => $data,
        );
        $this->format->process($res,$charset,$type);
    }

    public function send_error($code,$charset,$type,$sub_msg ='')
    {
        $error_arr = openapi_errorcode::get($code);
        $res = array(
            'error_response'  =>  array(
                'code' => $error_arr['code'],
                'msg' => $error_arr['msg'],
                'sub_msg' => $sub_msg,
            ),
        );
        $this->format->process($res,$charset,$type);
    }



    public function send_result_log($data,$charset,$type,$root='response')
    {
        $res = array(
            $root => $data,
        );
        return $res;
    }
    public function send_error_log($code,$charset,$type,$sub_msg ='')
    {
        $error_arr = openapi_errorcode::get($code);
        $res = array(
            'error_response'  =>  array(
                'code' => $error_arr['code'],
                'msg' => $error_arr['msg'],
                'sub_msg' => $sub_msg,
            ),
        );
        return $res;
    }


    public function getFormatType($type){
        if(in_array($type, $this->format->type_lists)){
            return $type;
        }else{
            return '';
        }
    }

    public function getFormatCharset($charset){
        if(in_array($charset, $this->format->charset_lists)){
            return $charset;
        }else{
            return '';
        }
    }
}