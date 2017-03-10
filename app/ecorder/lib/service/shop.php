<?php

class ecorder_service_shop{

    public function getTaobaoShopList()
    {
        $db = kernel::database();
        $rows = $db->select('select shop_id,name from sdb_ecorder_shop where node_type="taobao"');
        $list = array();
        foreach($rows as $row){
            $list[$row['shop_id']] = $row['name'];
        }
        return $list;
    }
    
    //店铺统计数据
    public function run_analysis()
    {
        $db = kernel::database();
        $sql = 'select shop_id from sdb_ecorder_shop where node_id<>"" ';
        $rs = $db->select($sql);
        foreach($rs as $v){
            kernel::single('taocrm_service_shop')->countShopBuys($v['shop_id']);
        }
    }
    
    public function del_api_logs($days)
    {
        app::get('ecorder')->model('api_log')->clear_old_logs($days);
    }

}