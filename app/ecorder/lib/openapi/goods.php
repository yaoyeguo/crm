<?php
class ecorder_openapi_goods
{
    public $app;
    public function __construct($app) {
        $this->app = $app;
    }
    
    public function updateid()
    {
        set_time_limit(0);
        $submit = false;
        $shopObj = &app::get(ORDER_APP)->model('shop');
        if ($_POST) {
//            $sqlCount =   "SELECT 
//                      count(1) as _count
//                    FROM
//                      `sdb_ecorder_order_items`
//                    INNER JOIN 
//                      `sdb_ecgoods_shop_goods` ON `sdb_ecorder_order_items`.`shop_id` = `sdb_ecgoods_shop_goods`.`shop_id`
//                    WHERE
//                      `sdb_ecorder_order_items`.shop_id = '{$shop_id}'
//                    AND `sdb_ecorder_order_items`.goods_id != `sdb_ecgoods_shop_goods`.`goods_id`
//                    AND `sdb_ecorder_order_items`.`name` = `sdb_ecgoods_shop_goods`.`name`";
//            echo $sqlCount;
//            exit;
            $submit = true;
            $shop_id = $_POST['shop_id'];
            $sql = "UPDATE `sdb_ecorder_order_items` 
                          INNER JOIN `sdb_ecgoods_shop_goods` ON `sdb_ecorder_order_items`.`shop_id` = `sdb_ecgoods_shop_goods`.`shop_id`
                             AND `sdb_ecorder_order_items`.`name` = `sdb_ecgoods_shop_goods`.`name`
                        SET  `sdb_ecorder_order_items`.goods_id = `sdb_ecgoods_shop_goods`.goods_id
                        WHERE
                           `sdb_ecorder_order_items`.shop_id = '{$shop_id}'
                        AND `sdb_ecorder_order_items`.goods_id != `sdb_ecgoods_shop_goods`.`goods_id`";
            $shopObj->db->exec($sql);
        }
        
        $rs = $shopObj->getList('shop_id, name');
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $render = $this->app->render();
        $render->pagedata['shops'] = $shops;
        $render->pagedata['submit'] = $submit;
        $render->display('admin/openapi/ecorder/goods/updateid.html');
    }
    
    
    public function test()
    {
        echo "sss";
    }
}