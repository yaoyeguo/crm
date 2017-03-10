<?php
class ecorder_mdl_sms_sign extends dbeav_model{

    public  function  modifier_is_code_sign($row)
    {
        if($row == 'true')
            $img = '<img src="'.kernel::base_url(0).'/app/taocrm/statics/tick_ok.gif" />';
        return $img;
    }

}

