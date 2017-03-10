<?php
class taocrm_member_point_type {
     
     
    function add($name,$code,& $msg){
        $db = kernel::database();

        $row = $db->selectRow('select id from sdb_taocrm_member_point_type where code="'.$code.'"');
        if($row){
            $msg = '类型已存在';
            return false;
        }

        $data = array('name'=>$name,'code'=>$code,'create_time'=>time());
        $db->insert('sdb_taocrm_member_point_type',$data);
        $id = $db->lastinsertid();

        if(!$id){
            $msg = '创建类型失败';
            return false;
        }

        return $id;
    }

    function getlist(){
        $db = kernel::database();
        $rows = $db->select('select * from sdb_taocrm_member_point_type');

        return $rows;
    }
}