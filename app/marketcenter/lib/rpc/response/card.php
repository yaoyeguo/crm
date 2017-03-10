<?php
class marketcenter_rpc_response_card{
    public function consume($data){
        $result = kernel::single('marketcenter_service_card')->consume($data);
        return $result;
    }

}