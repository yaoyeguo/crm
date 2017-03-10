<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_member_benefits_item extends dbeav_model{

    function checkBenefitsCode($code){
        $row = $this->db->selectRow('select id from sdb_taocrm_member_benefits_item where benefits_code="'.$code.'"');

        if($row){
            return true;
        }else{
            return false;
        }
    }

}