<?php
class openapi_mdl_setting extends dbeav_model{

    public function modifier_status($row){

        $ret = ($row == 1) ? '开启' : '关闭';
        return $ret;
    }

}

?>