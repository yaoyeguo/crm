<?php
class market_cti {

    var $support_cti = array('bird','ronghe');

    public function get_mobile($params)
    {
        $mobile = '';
        base_kvstore::instance('market')->fetch('cti_type', $cti_type);
        if(in_array($cti_type, $this->support_cti)){
            if( ! $mobile) kernel::single('market_cti_'.$cti_type)->inbound($mobile, $params);
        }
        return $mobile;
    }
}