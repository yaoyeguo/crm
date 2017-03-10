<?php

class openapi_params  extends openapi_api_params_abstract implements openapi_api_params_interface {
    public function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv'){
        if(parent::checkParams($method,$params,$sub_msg,$defined_params,$dataType)){
            return true;
        }else{
            return false;
        }
    }
	function getAppParams($method){
        $params = array(
            'set'=>array(
                'content'=>array('required'=>'true','name'=>'params对象','type'=>'json',
                    'cols'=>array(
                        'wms_url'  => array('type'=>'string','name'=>'WMS地址','required'=>'true'),
                        'jocs_url' => array('type'=>'string','name'=>'JOCS地址','required'=>'true'),
                        'wms_token'  => array('type'=>'string','name'=>'WMS密钥','required'=>'true'),
                        'jocs_token' => array('type'=>'string','name'=>'JOCS密钥','required'=>'true'),
                    ),
                ),
            ),
		);
		return $params[$method];
	}

    function description($method)
    {
        // TODO: Implement description() method.
    }
    /**
     * 
     *
     * @param  
     *
     * @return void
     * 
     * @author 张学会 <phlv@163.com>
     **/
    public function setParams($data)
    {
        return app::get('openapi')->setConf('params_setting',$data);
    }
    /**
     * undocumented function
     *
     * @param  
     *
     * @return void
     * 
     * @author 张学会 <phlv@163.com>
     **/
    public function getParams($key=null)
    {
        $data = app::get('openapi')->getConf('params_setting');
        if (!$key) {
            return $data;
        }
        if (isset($data[$key])) {
            return $data[$key];
        }else{
            return '';
        }
    }
}