<?php

class upgrade{

    public function start(){
        //根据更新项目的需要，添加代码。比如：初始化数据等。
        $db = kernel::database();
        $sql1 = "show table status from ".DB_NAME." where name='sdb_taocrm_all_points_log'";
        $data1 = $db->select($sql1);
        if($data1[0]['Engine'] != 'innodb'){
            $sql2 = 'alter table sdb_taocrm_all_points_log ENGINE=InnoDB';
            $db->exec($sql2);
        }

        $sql3 = "show table status from ".DB_NAME." where name='sdb_taocrm_member_points'";
        $data1 = $db->select($sql3);
        if($data1[0]['Engine'] != 'innodb'){
            $sql4 = 'alter table sdb_taocrm_member_points ENGINE=InnoDB';
            $db->exec($sql4);
        }

    }

}