<?php
interface taocrm_interface_middleware_model
{
    public function getList($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderby = null);
    
    public function count($filter = null);
    
    public function get_schema();
}
