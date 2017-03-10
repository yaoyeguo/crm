<?php

class taocrm_modifier_detail_wangwang_shop_chat_log
{
    //修改finder引流方法
    public function detail_columns_modifier(&$detail)
    {
        $tmpDetail = array();
        foreach ($detail as $key => $value) {
            $method = $key . '_display';
            if (method_exists($this, $method)) {
                if ($this->$method()) {
                    $tmpDetail[$key]=$value;
                }
            }
            else {
                $tmpDetail[$key] = $value;
            }
        }
        $detail = $tmpDetail;
    }
    
    protected function detail_basic_display()
    {
        if (isset($_GET['type']) && $_GET['type'] == 1) {
            return true;
        }
        else {
            return false;
        }
    }
}
