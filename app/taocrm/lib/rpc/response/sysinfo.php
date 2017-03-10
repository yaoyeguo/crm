<?php

class taocrm_rpc_response_sysinfo extends taocrm_rpc_response
{

    public function version($sdf, &$responseObj)
    {
        $info = array(
            'crm_version' => CRM_VERSION,
            'system_version' => SYSTEM_VERSION,
        );
        return $info;
    }

}