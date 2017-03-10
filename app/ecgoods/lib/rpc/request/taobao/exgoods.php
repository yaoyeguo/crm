<?php

/**
 * 商品同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecgoods_rpc_request_taobao_exgoods extends ecgoods_rpc_request {
	

    protected $topClient;

    protected $_shopInfo = array();

    protected $_cids = array();

    protected $_rertyCount = 0;

    protected $_rertyMaxCount = 3;
    

    public function __construct(){
        $c = new ectools_top_TopClient;
        $c->format = "json";
        $c->appkey = TOP_APP_KEY; 
        $c->secretKey = TOP_SECRET_KEY;
        $this->topClient = $c;
    }

    public function download($shop_id="",$data_from,$data_end){
    		
        kernel::ilog(__CLASS__ . ' download start......');
        if (!empty($shop_id)){
        	 $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao" and shop_id='.'"'.$shop_id.'"');
        }else {
        	 $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao"');
        }
        foreach($shopList as $shop){
        	
            $addon = unserialize($shop['addon']);
            if($addon && !empty($addon['session'])){
                $this->_shopInfo = array('shop_id'=>$shop['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname']);
                $rs = $this->getAll($pageNo=1,$pageSize=100,$data_from,$data_end);
            }
        }
        return $rs;
    }

    protected function getAll($pageNo=1,$pageSize=100,$data_from,$data_end){
    	$result=$this->ItemsOnsaleGetRequest($pageNo,$pageSize,$data_from,$data_end);
		if($result == 'success'){
            $pageNo++;
            $this->ItemsOnsaleGetRequest($pageNo,$pageSize=100,$data_from,$data_end);
        }elseif ($result=="finish"){
        	
        	$result="finish";
        }
        return $result;
    }


    protected function ItemsOnsaleGetRequest($pageNo,$pageSize,$data_from,$data_end){
        $msg = '';
        $req = new ectools_top_request_ItemsOnsaleGetRequest();
        $req->setFields('num_iid,outer_id,title,cid,price,list_time,delist_time,modified,pic_url');
        $req->setPageSize($pageSize);
        $req->setPageNo($pageNo);
        //$req->setStartModified($data_from);
        //$req->setEndModified($data_end);
        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            $total_results = $resp->total_results;
            error_log(var_export($total_results,true),3,'d:/abc.txt');
            // 循环插入商品数据
            if($resp->items->item) {
                foreach($resp->items->item as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $v['marketable'] = 'true';
                    $goods_id = $this->processGoods($v);
                    if(!$goods_id){
                        kernel::ilog($v['num_iid'] . ' create failed.');
                        continue;
                    }
                }
            }
           $msg = 'success';
        }
        
        if($pageSize*$pageNo >= $total_results){
            $msg = 'finish';
        }
        return $msg;
    }
    protected function ItemcatsGetRequest($pageNo,$pageSize){
        $pageNo -= 1;
        if(!isset($this->_cids[$pageNo])){
            return 'finish';
        }

        $cid = $this->_cids[$pageNo];
        $req = new ectools_top_request_ItemcatsGetRequest();
        $req->setFields('cid,parent_cid,name,is_parent,status,sort_order');
        $req->setCids($cid);
        $resp = $this->topClient->execute($req);
        //var_dump($resp);exit;
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            // 循环插入分类数据
            if($resp->item_cats->item_cat) {
                foreach($resp->item_cats->item_cat as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $catId = $this->processGoodsCat($v);
                    if(!$catId){
                        kernel::ilog($cid . ' create failed.');
                        continue;
                    }
                }
            }
            $msg = 'success';
        }

        return $msg;
    }

    protected function processGoods($item){
    	
        //转换商品标准格式sdf
        $sdf = $this->convertGoodsSdfParams($item);
   
        $this->acceptCreateGoods($sdf);

        if($this->acceptCreateGoods($sdf)){
            $sdf['create_time'] = time();
          
        
        }else{
            $sdf = array_merge($sdf, array('goods_id' => $sdf['goods_id']));
        }
       
        

        if(!in_array($sdf['c_id'], $this->_cids)){
            $this->_cids[] = $sdf['c_id'];
        }

        return kernel::single("ecgoods_service_goods")->saveGoods($sdf);
    }
    
    

    protected function processProducts($sku){
        //转换商品标准格式sdf
        $sdf = $this->convertProductSdfParams($sku);
        if($this->acceptCreateProduct($sdf)){
            $sdf['create_time'] = time();
        }else{
            $sdf = array_merge($sdf, array('product_id' => $sdf['product_id']));
        }
        return kernel::single("ecgoods_service_products")->saveProducts($sdf);
    }

    protected function processGoodsCat($catInfo){
        //转换商品标准格式sdf
        $sdf = $this->convertGoodsCatSdfParams($catInfo);
        
        if($this->acceptCreateGoodsCat($sdf)){
            $sdf['create_time'] = time();
        }else{
            $sdf = array_merge($sdf, array('cat_id' => $sdf['cat_id']));
        }

        $cat_id = kernel::single("ecgoods_service_goodscat")->saveCat($sdf);
        //echo $sdf['outer_id']."\n";
        kernel::database()->exec('update sdb_ecgoods_shop_goods set cat_id=' . $cat_id . ' where c_id='.$catInfo['cid']);

        return $cat_id;
    }



    protected function convertGoodsSdfParams($item){
        $sdf = array('shop_id' => $this->_shopInfo['shop_id'],
            'outer_id' => $item['num_iid'],
            'bn' => $item['outer_id'],
            'name' => $item['title'],
            'c_id' => $item['cid'],
            'goods_type' => 'normal',
            'price' => $item['price'],
            'marketable' => isset($item['marketable']) ? $item['marketable'] : 'false',
            'pic_url' => $item['pic_url'],
        	'status' => $item['status'],
            'uptime' => strtotime($item['list_time']),
            'downtime' => strtotime($item['delist_time']),
            'last_modify' => strtotime($item['modified']),
        );

        return $sdf;
    }

    protected function convertProductSdfParams($sku){
        $sdf = array('goods_id' => $sku['goods_id'],
            'shop_id' => $this->_shopInfo['shop_id'],
            'outer_id' => $sku['num_iid'],
            'outer_sku_id' => $sku['sku_id'],
            'bn' => $sku['outer_id'],
        	'name' => $sku['name'],
            'price' => $sku['price'],
            'update_time' => strtotime($sku['modified']),
            'store' => $sku['quantity'],
        	'status' => $sku['status'],
        );

        return $sdf;
    }

    protected function convertGoodsCatSdfParams($cat){
        $goods_count = app::get(GOODS_APP)->model('shop_goods')->count(array('c_id'=>$cat['cid']));
        $sdf = array('outer_parent_id'=> $cat['parent_cid'],
            'outer_id' => $cat['cid'],
            'parent_id' => 0,
        //'cat_path' => '',
        //'is_leaf' => 'false',
        //'type_id' => '',
            'cat_name' => $cat['name'],
            'p_order' => $cat['sort_order'],
            'goods_count' => $goods_count,
        //'child_count' => '',
        );

        return $sdf;
    }

    protected function acceptCreateGoods(& $goodsInfo) {
    	     
        $goods = app::get('ecgoods')->model('shop_goods')->dump(array('outer_id'=>$goodsInfo['outer_id']),'goods_id');
        if($goods){
            $goodsInfo['goods_id'] = $goods['goods_id'];
            return false;
        }else{
        	
            return true;
        }
    }

    protected function acceptCreateProduct(& $productInfo) {
         
        $product = app::get("ecgoods")->model('shop_products')->dump(array('outer_id'=>$productInfo['outer_id'],'outer_sku_id'=>$productInfo['outer_sku_id']),'product_id,goods_id');

        if($product){
            $productInfo['product_id'] = $product['product_id'];
            return false;
        }else{
            return true;
        }
    }

    protected function acceptCreateGoodsCat(& $catInfo) {
    	
         
        $cat = app::get(GOODS_APP)->model('shop_goods_cat')->dump(array('outer_id'=>$catInfo['outer_id']),'cat_id');

        if($cat){
            $catInfo['cat_id'] = $cat['cat_id'];
            return false;
        }else{
            return true;
        }
    }

    protected function countGoodsStore($goodsId) {
        $all_store = kernel::database()->selectrow('select sum(store) as all_store from sdb_ecgoods_shop_products where goods_id='.$goodsId);
        $all_store = !empty($all_store['all_store']) ? intval($all_store['all_store']) : 0;
        kernel::database()->exec('update sdb_ecgoods_shop_goods set store=' . $all_store . ' where goods_id='.$goodsId);
        return true;
    }
  
    

    protected function updateOrderInfo() {
        $offset = 0;
        $len = 100;
        $execTime = time();
        while(true){
            $curTime = time();
            if($curTime >= $execTime + 30 ){
                kernel::database()->dbclose();
                $execTime = $curTime;
            }

            $rows = kernel::database()->select('select obj.obj_id from sdb_ecorder_order_objects as obj left join sdb_ecorder_orders as o on obj.order_id = o.order_id 
            where obj.goods_id=0 and o.shop_id="'.$this->_shopInfo['shop_id'].'" limit '. ($offset * $len) . ' , ' . $len);
            if(!$rows)break;

            $ids = array();
            foreach($rows as $row){
                $ids[] = $row['obj_id'];
            }

            $sql = 'update sdb_ecorder_order_objects as obj,sdb_ecgoods_shop_goods as g set obj.goods_id=g.goods_id 
            where obj.obj_id in('.implode(',', $ids).') and g.outer_id = obj.shop_goods_id';
            kernel::database()->exec($sql);
            $offset++;
        }

        $offset = 0;
        $len = 100;
        while(true){
            $curTime = time();
            if($curTime >= $execTime + 30 ){
                kernel::database()->dbclose();
                $execTime = $curTime;
            }

            $rows = kernel::database()->select('select oi.item_id from sdb_ecorder_order_items as oi left join sdb_ecorder_orders as o on oi.order_id = o.order_id 
            where oi.product_id=0 and o.shop_id="'.$this->_shopInfo['shop_id'].'" limit '. ($offset * $len) . ' , ' . $len);
            if(!$rows)break;

            $ids = array();
            foreach($rows as $row){
                $ids[] = $row['item_id'];
            }

            $sql = 'update sdb_ecorder_order_items as oi, sdb_ecgoods_shop_products as p set oi.product_id=p.product_id 
            where oi.product_id=0 and oi.item_id in('.implode(',', $ids).') and p.outer_sku_id = oi.shop_product_id';
            kernel::database()->exec($sql);
            $offset++;
        }

        return true;
    }
     
}