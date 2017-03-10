<?php
class market_view_helper extends desktop_controller{

    function function_desktop_header($params, &$smarty)
    {
        return app::get("market")->render()->fetch("header.html");
    }
    
}