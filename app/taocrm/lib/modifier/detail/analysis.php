<?php

class taocrm_modifier_detail_analysis
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
    
    protected function detail_wangwangjingling_display()
    {
        $obj = kernel::single('taocrm_wangwangjingling_service');
        return $obj->isBind();
    }
}
