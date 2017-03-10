<?php 
class market_mdl_sms extends dbeav_model {
function get_batch_no(){
		$svRow=$this->db->select('select sms_id from sdb_market_sms order by sms_id desc LIMIT 1');
		return $svRow[0];
	}
	
 public function modifier_create_time($row){
        $date = date("Y-m-d H:i:s",$row);
        return $date ;
    }
 public function _filter($filter,$tableAlias=null,$baseWhere=null) {
     $where = '';
     if (isset($_GET['view'])) {
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $count = $shopObj->count();
        if ($_GET['view'] > $count) {
            $shopList = $shopObj->getList('shop_id,name');
            foreach((array)$shopList as $v){
                $shops[] = $v['shop_id'];
            }
            $shopids = implode("','", $shops);
            $where = " AND shop_id IN ('.".$shopids.".')";
        }
     }
     return parent::_filter($filter,$tableAlias=null,$baseWhere=null) . $where;
 }

}