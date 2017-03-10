<?php
class taocrm_mdl_member_overall_property extends dbeav_model {

    function getTypeSearch($fields){
        $data = array();
        if($fields){
            foreach ($fields as $v) {
                $v = trim($v);
                if(!empty($v)){
                    $sql = "SELECT DISTINCT `value`
                    FROM `sdb_taocrm_member_overall_property`
                    WHERE `property` = '{$v}'";
                    $result = $this->db->select($sql);
                    if ($result) {
                        foreach ($result as $v1) {
                            $data[$v][] = $v1['value'];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 获取的客户数量
     */
    public function getTypeTagAllInfo( $page = 0, $pageSize = 0, $fieds = array(), $search = array()){
         
        $sql = "SELECT member_id,`uname`, GROUP_CONCAT(property) AS card_key,
                GROUP_CONCAT(`value`) AS card_value
                FROM `sdb_taocrm_member_overall_property`";
        //$sql .=$typeSql;
        $sql .= " GROUP BY member_id";
        $result = $this->db->select($sql);

        $data = array();
        $count = 0;
        if ($result) {
            $i = 0;
            foreach ($result as $v) {
                $data[$i]['member_id'] = $v['member_id'];
                $card_key = explode(',', $v['card_key']);
                $card_value = explode(',', $v['card_value']);
                $cardKV = array();
                foreach ($card_key as $k1 => $v1) {
                    $cardKV[$v1] = $card_value[$k1];
                }
                //ksort($cardKV);
                $stack = array();
                foreach ($fieds as $v2) {
                    $stack[$v2] = isset($cardKV[$v2]) ? $cardKV[$v2] : "";
                }
                $data[$i]['uname'] = $v['uname'];
                $data[$i]['data'] = $stack;
                $i++;
            }
            $count = count($data);
        }
        if ($search && $data) {
            $newSearch = array();
            foreach ($search as $k => $v) {
                if ($v) {
                    $newSearch[$k] = $v;
                }
            }
            if ($newSearch && $data) {
                $newData = array();
                foreach ($data as $k => $v) {
                    $result = array_diff_assoc($newSearch, $v['data']);
                    if (empty($result)) {
                        $newData[] = $v;
                    }
                }
                $data = $newData;
            }
            $count = count($data);
        }

        if ($data && $page > 0) {
            $start = ($page - 1) * $pageSize;
            $end = $start + $pageSize;
            $pageData = array();
            for ($i = $start; $i < $end; $i++) {
                if (isset($data[$i])) {
                    $pageData[] = $data[$i];
                }
                else {
                    break;
                }

            }
            $data = $pageData;
        }

        return array('count' => $count, 'data' => $data);
    }

    /**
     * 获得自定义类型标签字段
     */
    public function getTypeTagField(){
        $sql = "SELECT  DISTINCT `property`
                FROM  
                    `sdb_taocrm_member_overall_property`";
        
        $result = $this->db->select($sql);
        $fields = array();
        if ($result) {
            $stack = array();
            foreach ($result as $v) {
                $stack[] = $v['property'];
            }
            asort($stack);
            $fields = $stack;
            
        }
        return $fields;
    }

}