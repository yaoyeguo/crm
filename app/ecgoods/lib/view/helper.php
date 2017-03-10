<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class taoapi_view_helper extends desktop_controller{ 
    function function_desktop_header($params, &$smarty){ 
        return app::get("taoapi")->render()->fetch("header.html"); 
    } 
	
	function function_desktop_footer(){
		
	}
}