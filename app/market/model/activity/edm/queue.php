<?php

class market_mdl_activity_edm_queue extends dbeav_model
{
    public function __construct($app)
    {
        parent::__construct($app);
    }
    
    public function getTotal($active_id)
    {
        if ($active_id == '') {
            $active_id = 0;
        }
        $active_id = intval($active_id);
        
        if ($active_id == 0) {
            return 0;
        }
        $filter = array('active_id' => $active_id);
        return $this->count($filter);
    }
    
    public function getIsSendNum($active_id, $is_send = 1)
    {
        $filter = array(
            'active_id' => $active_id,
            'is_send' => $is_send
        );
        return $this->count($filter);
    } 
}
