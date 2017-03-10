<?php

interface openapi_api_params_interface{

    function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv');
	
    function description($method);
}