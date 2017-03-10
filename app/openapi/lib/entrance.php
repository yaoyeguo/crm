<?php

class openapi_entrance extends openapi_response{

    /**
     * 静态私有变量系统级参数
     * @var array
     */
    static private $_sysParams = array();

    /**
     * 静态私有变量应用级参数
     * @var array
     */
    static private $_appParams = array();

    /**
     * 开放数据接口入口函数
     * @param array $params
     */
    public function service($params){
        //接收所有参数
        $this->setParams($params);
        $oApi_log = &app::get('openapi')->model('api_log');
        $log_sdf = array(
            'api_name' => $params['method'],
            'api_flag' => $params['flag'],
            'params' => serialize($params),
            'createtime' => time(),
        );
        //检查系统级参数
        if(!$this->checkSysParams($params)){
            $log_sdf['status'] = 'fail';
            $log_sdf['msg'] = serialize(array("error"=>'e000001','errormsg'=>'检查系统级参数有错误'));
            $oApi_log->save($log_sdf);
            $this->send_error('e000001',self::$_sysParams['charset'],self::$_sysParams['type']);
        }

        //签名验证
        if(!$this->validate($params)){
            $log_sdf['status'] = 'fail';
            $log_sdf['msg'] = serialize(array("error"=>'e000002','errormsg'=>'签名错误'));
            $oApi_log->save($log_sdf);
            $this->send_error('e000002',self::$_sysParams['charset'],self::$_sysParams['type']);
        }

        //检查接口是否存在
        $allow_methods = openapi_conf::getMethods();
        if(!isset($allow_methods[self::$_sysParams['class']]) || !isset($allow_methods[self::$_sysParams['class']]['methods'][self::$_sysParams['method']])){
            $log_sdf['status'] = 'fail';
            $log_sdf['msg'] = serialize(array("error"=>'e000003','errormsg'=>'接口/方法不存在'));
            $oApi_log->save($log_sdf);
            $this->send_error('e000003',self::$_sysParams['charset'],self::$_sysParams['type']);
        }

        //检查权限
        if(!openapi_privilege::checkAccess(self::$_sysParams['flag'],self::$_sysParams['class'],self::$_sysParams['method'])){
            $log_sdf['status'] = 'fail';
            $log_sdf['msg'] = serialize(array("error"=>'e000004','errormsg'=>'接口访问权限有误'));            $oApi_log->save($log_sdf);
            $this->send_error('e000004',self::$_sysParams['charset'],self::$_sysParams['type']);
        }

        //监控统计
        $statisticsLib = kernel::single('openapi_statistics');
        $statisticsLib->set(self::$_sysParams['flag'],self::$_sysParams['class'],self::$_sysParams['method']);

        //实例化接口对象
        $dataObjectLib = kernel::single('openapi_object');
        if(!$dataObjectLib->instance(self::$_sysParams,self::$_appParams,$code,$sub_msg)){
            $log_sdf['status'] = 'fail';
            $log_sdf['msg'] = serialize(array('code'=>$code,'msg'=>'实例化接口对象失败','sub_msg'=>$sub_msg));
            $oApi_log->save($log_sdf);
            $this->send_error($code,self::$_sysParams['charset'],self::$_sysParams['type'],$sub_msg);
        }
        //执行接口调用处理

        if($dataObjectLib->process($result,$code,$sub_msg)){
            $log_sdf['status'] = $result['rsp'];
            $log_sdf['msg'] = serialize($result);
            $oApi_log->save($log_sdf);
            $this->send_result($result,self::$_sysParams['charset'],self::$_sysParams['type'],$sub_msg);
        }else{
            $data= array('code'=>$code, 'sub_msg'=>$sub_msg );
            $log_sdf['status'] = 'fail';
            $log_sdf['msg'] = serialize($data);
            $oApi_log->save($log_sdf);
            $this->send_error($code,self::$_sysParams['charset'],self::$_sysParams['type'],$sub_msg);
        }
    }

    /**
     *
     * 接收传入参数兼容post数据
     * @param unknown_type $params
     */
    private function setParams(&$params){
        if(empty($params)){
            $params = array();
        }

        foreach($params as &$v){
            $v = urldecode($v);
        }
        //兼容ESB数据
        if (empty($_POST) && ($json = file_get_contents("php://input")) ) {
            $jsonArr = json_decode($json,1);
            $_POST  = current($jsonArr);
        }
        return $params = array_merge($params , $_POST);
    }

    /**
     *
     * 检查系统级参数函数
     * @param array $params
     */
    private function checkSysParams($params){
        self::$_sysParams = array(
            'ver' => $params['ver'] ? $params['ver'] : 1,
            'charset' => $this->getFormatCharset($params['charset']) ? $params['charset'] : 'utf-8',
            'type' => $this->getFormatType($params['type']) ? $params['type'] : 'json',
        );

        if(empty($params['method']) || empty($params['flag']) || empty($params['sign'])){
            return false;
        }
        $args = explode('.',$params['method']);
        $method = array_pop($args);
        $class = array_pop($args);
        if(empty($class) || empty($method)){
            return false;
        }
        $path ='';
        if(count($args)>0){
            $path = implode('_', $args);
        }
        self::$_sysParams['path'] = $path;
        self::$_sysParams['flag'] = $params['flag'];
        self::$_sysParams['class'] = $class;
        self::$_sysParams['method'] = $method;
        return true;
    }

    /**
     *
     * 验证签名函数
     * @param array $params
     */
    private function validate($params){
        $sign = $params['sign'];
        unset($params['sign']);
        $local_sign = $this->gen_sign($params);

        if($sign != $local_sign || !$local_sign){
            return false;
        }else{
            unset($params['method']);
            unset($params['flag']);
            unset($params['ver']);
            unset($params['charset']);
            unset($params['type']);

            self::$_appParams = $params;
            return true;
        }
    }

    /**
     *
     * 生成签名算法函数
     * @param array $params
     */
    private function gen_sign($params){
        $token = openapi_setting::getConf(self::$_sysParams['flag'],'interfacekey');
        if(!$token){
            return false;
        }
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

    /**
     *
     * 签名参数组合函数
     * @param array $params
     */
    private function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }
}