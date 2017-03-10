<?php
class market_mdl_wx_survey extends dbeav_model {

    function delete($id){
        $ids = array();
        if(!is_array($id)){
            $ids[] = $id;
        }else{
            $ids = $id;
        }

        kernel::database()->exec('delete from sdb_market_wx_survey where survey_id in ('.implode(',', $ids).')');
        return true;
    }
}