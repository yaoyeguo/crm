<?php

/**
 * 旺旺咨询日志
 *
 */
class taocrm_mdl_wangwang_shop_chat_log extends dbeav_model
{
    public function modifier_chat_date($time)
    {
        return date("Y-m-d", $time);
    }
}
