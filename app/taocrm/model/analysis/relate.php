<?php
class taocrm_mdl_analysis_relate extends dbeav_model {
    public function get_shop(){//店铺
        $sql = 'SELECT shop_id as type_id,name,relate_id FROM '.
            'sdb_ecorder_shop as S LEFT JOIN '.
            'sdb_taocrm_analysis_relate as R ON R.relate_key=S.shop_id';
        $row = $this->db->select($sql);
        return $row;
    }
}