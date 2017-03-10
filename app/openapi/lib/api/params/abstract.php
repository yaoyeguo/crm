<?php

abstract class openapi_api_params_abstract{

    protected function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv'){
        if (!$defined_params) {
            $defined_params = $this->getAppParams($method);
        }
        if(empty($defined_params)){
            return false;
        }
        if ($dataType == 'kv') {
            $paramsAll[] = $params;
        }else{
            $paramsAll  = $params;
        }

        foreach ($paramsAll as  $paramsRow) {

            foreach($defined_params as $defined_param => $attribute){
                if(isset($attribute['required']) && $attribute['required'] == 'true'){
                    if(!isset($paramsRow[$defined_param]) || $paramsRow[$defined_param]===null || $paramsRow[$defined_param]===false   ){
                        $sub_msg = $defined_param.'['.$attribute['name'].'] not null';
                        return false;
                    }else{
                        $curValue = $paramsRow[$defined_param];

                    }
                }else {
                	if (empty($paramsRow[$defined_param])){
                		continue;
                	}else{
                        $curValue = $paramsRow[$defined_param];
                    }
                }
                if (!$this->checkType($defined_param,$attribute,$curValue,$sub_msg)) {
                     return false;
                 } 
            }
        }
        return true;
    }
    /**
     * 验证数据类型
     *
     * @param  
     *
     * @return void
     * 
     * @author 张学会 <phlv@163.com>
     **/
    public function checkType($cols,$attribute,$curValue,&$sub_msg)
    {
        switch($attribute['type']){
            case 'double':
            case 'money':
                $curValue = (float)$curValue;
                if(!is_float($curValue)){
                    var_dump($curValue);
                    $sub_msg = $curValue.$cols.$attribute['name'].'格式错误';
                    return false;
                }
                break;
            case 'date':
                if(!preg_match("(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})",$curValue)){
                    $sub_msg = $cols.$attribute['name'].'格式错误';
                    return false;
                }
                break;
            case 'int':
            case 'number':
                if(!is_numeric($curValue) || $curValue < 0){
                    $sub_msg = $cols.$attribute['name'].'格式错误';
                    return false;
                }
                break;
            case 'string':
                if(!is_string($curValue)){
                    $sub_msg = $cols.$attribute['name'].'格式错误';
                    return false;
                }
                break;
            case 'json':
                $params = json_decode($curValue,1);
                if (!(is_array($params) && $params) ) {
                    $sub_msg = $cols.$attribute['name'].'格式错误';
                    return false;
                }
                if(!$this->checkArray($cols,$attribute,$params,$sub_msg)){
                    return false;
                }

                break;
            case 'array':
                $params = $curValue;
                if (!(is_array($params) && $params) ) {
                    $sub_msg = $cols.$attribute['name'].'格式错误';
                    return false;
                }
//                if (!$this->checkArray($cols,$attribute,$params,$sub_msg)) {
//                    return false;
//                }
//                break;
        }
        return true;
    }
    /**
     * 验证array
     *
     * @param  
     *
     * @return void
     * 
     * @author 张学会 <phlv@163.com>
     **/
    public function checkArray($cols,$attribute,$params,&$sub_msg)
    {
        if (isset($attribute['items']) && $attribute['items']) {
            $defined_params = $attribute['items'];
            $dataType = 'obj';
        }elseif(isset($attribute['cols']) && $attribute['cols']){
            $defined_params = $attribute['cols'];
            $dataType = 'kv';
        }else{
            $sub_msg = $cols.$attribute['name'].'格式错误';
            return false;
        }
        $res = $this->checkParams('s',$params,$sub_msg,$defined_params,$dataType);
        if (!$res) {
            $sub_msg = $sub_msg;
            return false;
        }
        return true;
    }

}