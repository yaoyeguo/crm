<?php

/**
 * 商品同步请求
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecgoods_rpc_request_taobao_goods extends ecgoods_rpc_request {

    protected $_shopInfo = array();

    protected $_cids = array();

    protected $_rertyCount = 0;

    protected $_rertyMaxCount = 3;

    //初始化店铺参数，仅外部调用
    public function init_param($shop_id){
        if(!$shop_id) return false;

        $rs = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao" and shop_id='.'"'.$shop_id.'"');
        $addon = unserialize($rs[0]['addon']);
        if($addon && !empty($addon['session'])){
            $this->_shopInfo = array('shop_id'=>$rs[0]['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname']);
        }
    }

    //只执行商品销量统计，不下载商品
    public function download($shop_id="")
    {
        $sql = 'update sdb_ecgoods_shop_goods as a,(
        select sum(nums) as num,sum(amount) as amount,goods_id 
        from sdb_ecorder_order_items group by goods_id
        ) as b set a.total_num=b.num,a.sale_money=b.amount
        where a.goods_id=b.goods_id ';
        kernel::database()->exec($sql);
        return true;
    
        if (!empty($shop_id)){
            $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao" and shop_id='.'"'.$shop_id.'"');
        }else {
            $shopList = kernel::database()->select('select * from sdb_ecorder_shop where node_id is not null and node_type="taobao"');
        }

        if($shopList){
            foreach($shopList as $shop){
                $addon = unserialize($shop['addon']);
                if($addon && !empty($addon['session'])){
                    //kernel::ilog($shop['name'] . ' start......');
                    $this->_shopInfo = array('shop_id'=>$shop['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname']);
                    $this->getAll('ItemsOnsaleGetRequest');
                    //kernel::ilog($shop['name'] . ' end......');
                    $this->updateOrderInfo();
                    $this->updateShopInfo();
                }
                
                $this->updateGoodsInfo($shop['shop_id']);
            }
        }
    }
    
    //更新商品ID
    public function updateGoodsInfo($shop_id)
    {
        if(!$shop_id) return false;
        $db = kernel::database();
        
        //step1:重新关联商品id为空的订单信息
        $sql = "SELECT goods_id,name FROM sdb_ecgoods_shop_goods where shop_id='$shop_id' ";
        $rs = $db->select($sql);
        if($rs){
            foreach($rs as $k=>$v){
                //按商品名称关联
                if($v['name'] && $v['name']!=''){
                    $sql = "UPDATE `sdb_ecorder_order_items` SET goods_id = ".$v['goods_id']." WHERE goods_id=0 AND name='".$v['name']."'";
                    $db->exec($sql);
                    echo("renionitems=$k\r\n");
                }
            }
        }
        
        //step2:根据订单信息补全商品
        $sql = "SELECT `name`,shop_goods_id,bn,price,shop_id FROM sdb_ecorder_order_items WHERE goods_id=0 and shop_id='$shop_id' GROUP BY `name` ";
        $rs = $db->select($sql);
        if($rs){
            $oShopGoods = app::get('ecgoods')->model('shop_goods');
            foreach($rs as $k=>$v){
                if($v['name']=='') continue;
                
                $name = $v['name'];
                $arr = array();
                $arr['outer_id'] = $v['shop_goods_id'];
                $arr['bn'] = $v['bn'];
                $arr['name'] = $v['name'];
                $arr['price'] = $v['price'];
                $arr['shop_id'] = $v['shop_id'];
                $arr['create_time'] = time();
                $arr['disabled'] = 'false';
                
                //创建商品
                if($oShopGoods->insert($arr)){
                    $goods_id = $arr['goods_id'];
                    $sql = "UPDATE sdb_ecorder_order_items SET goods_id=$goods_id WHERE goods_id=0 and name='$name' ";
                    $db->exec($sql);
                }
                echo("addgoods=$k\r\n");
            }
        }
        
        //step3:更新数据sdb_ecorder_member_products表
        //kernel::single('ecorder_ctl_admin_download')->update_member_products($shop_id);
    }

    public function downloadByNodeId($node_id){
        $shop = kernel::database()->selectrow('select * from sdb_ecorder_shop where node_id ="'.$node_id.'"');
        if($shop){
            $addon = unserialize($shop['addon']);
            if($addon && !empty($addon['session'])){
                //kernel::ilog($shop['name'] . ' start......');
                $this->_shopInfo = array('shop_id'=>$shop['shop_id'],'session'=>$addon['session'],'nickname'=>$addon['nickname']);
                $this->getAll('ItemsOnsaleGetRequest');
                //kernel::ilog($shop['name'] . ' end......');
                $this->updateOrderInfo();
                $this->updateShopInfo();
            }

        }

        //kernel::ilog(__CLASS__ . ' download end......');
    }

    protected function getAll($task,$pageNo=1){
        kernel::database()->dbclose();
        $result = $this->switchTask($task,$pageNo);
        if($result == 'timeout'){
            //kernel::ilog($task . '-'. $pageNo . ' is ' . $result);
            //kernel::ilog('sleep 3 sec...');
            sleep(3);

            if( $this->_rertyCount < $this->_rertyMaxCount ){
                $this->_rertyCount++;
                $this->getAll($task,$pageNo);
            }else{
                //kernel::ilog('rerty finish...');
                $this->_rertyCount = 0;
                $pageNo++;
                $this->getAll($task,$pageNo);
            }
        }else if($result == 'success'){
            $pageNo++;
            $this->getAll($task,$pageNo);
        }else if($result == 'finish'){
            if($task == 'ItemsOnsaleGetRequest'){
                $this->getAll('ItemsInventoryGetRequest');
            }

            if($task == 'ItemsInventoryGetRequest'){
                $this->getAll('ItemSkusGetRequest');
            }

            if($task == 'ItemSkusGetRequest'){
                $this->getAll('ItemcatsGetRequest');
            }

        }else{
            if(defined('SHOW_ILOG') && SHOW_ILOG){
                echo($result);die();
            }else{
                kernel::ilog($result);
            }
        }
    }

    public function switchTask($task,$pageNo=1,$pageSize=100){
        return $this->{$task}($pageNo,$pageSize);
    }

    protected function ItemsOnsaleGetRequest($pageNo,$pageSize){
        $msg = '';
        $req = new ectools_top_request_ItemsOnsaleGetRequest();
        $req->setFields('num_iid,outer_id,title,cid,price,list_time,delist_time,modified,pic_url,num');
        $req->setPageSize($pageSize);
        $req->setPageNo($pageNo);
        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
        //var_dump($resp);exit;
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
            if($resp->code == 'Remote service error' && $resp->msg == 'isp.top-remote-connection-timeout'){
                //kernel::ilog($msg);
                $msg = ('timeout');
            }
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            $total_results = $resp->total_results;
            // 循环插入商品数据
            if($resp->items->item) {
                foreach($resp->items->item as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $v['marketable'] = 'true';
                    //echo $v['num_iid']."\n";
                    $goods_id = $this->processGoods($v);
                    if(!$goods_id){
                        //kernel::ilog($v['num_iid'] . ' create failed.');
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

    protected function ItemsInventoryGetRequest($pageNo=1,$pageSize=100){
        $msg = '';
        $req = new ectools_top_request_ItemsInventoryGetRequest();
        $req->setFields('num_iid,outer_id,title,cid,price,list_time,delist_time,modified,pic_url,num');
        $req->setPageSize($pageSize);
        $req->setPageNo($pageNo);
        $resp = $this->topClient->execute($req,$this->_shopInfo['session']);
        //var_dump($resp);exit;
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
            if($resp->code == 'Remote service error' && $resp->msg == 'isp.top-remote-connection-timeout'){
                //kernel::ilog($msg);
                $msg = ('timeout');
            }
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            $total_results = $resp->total_results;
            // 循环插入商品数据
            if($resp->items->item) {
                foreach($resp->items->item as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $v['marketable'] = 'false';
                    $goods_id = $this->processGoods($v);
                     

                    if(!$goods_id){
                        //kernel::ilog($v['num_iid'] . ' create failed.');
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


    protected function ItemSkusGetRequest($pageNo,$pageSize){
        $pageNo -= 1;
        $goods = app::get('ecgoods')->model('shop_goods')->getList('goods_id,outer_id,name',array('shop_id'=>$this->_shopInfo['shop_id']),$pageNo,1,'goods_id');
        if(!$goods){
            return 'finish';
        }
        $goods = $goods[0];
        //$item['num_iid'] = 12753339231;
        $req = new ectools_top_request_ItemSkusGetRequest();
        $req->setFields('num_iid,sku_id,outer_id,price,modified,status,quantity');
        $req->setNumIids($goods['outer_id']);
        $resp = $this->topClient->execute($req);
        //var_dump($goods['outer_id']);exit;
        if($resp->code || $resp->msg){
            $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
            if($resp->sub_code){
                $msg .= '<br/>【sub_code】' . $resp->sub_code;
            }
            if($resp->sub_msg){
                $msg .= '<br/>【sub_msg】' . $resp->sub_msg;
            }
            if($resp->code == 'Remote service error' && $resp->msg == 'isp.top-remote-connection-timeout'){
                //kernel::ilog($msg);
                $msg = ('timeout');
            }
        }else if(!$resp){ // 超时错误
            $msg = ('timeout');
        }else{
            // 循环插入货品数据
            if($resp->skus->sku) {
                foreach($resp->skus->sku as $v) {
                    if(is_object($v)) $v = get_object_vars($v);
                    $v['goods_id'] = $goods['goods_id'];
                    $v['name'] = $goods['name'];
                    $product_id = $this->processProducts($v);
                    if(!$product_id){
                        //kernel::ilog($v['num_iid'] . '_' . $v['sku_id'] . ' create failed.');
                        continue;
                    }
                }
            }
            $msg = 'success';
        }

        //统计商品总库存
        //$this->countGoodsStore($goods['goods_id']);

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
            if($resp->code == 'Remote service error' && $resp->msg == 'isp.top-remote-connection-timeout'){
                //kernel::ilog($msg);
                $msg = ('timeout');
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
                        //kernel::ilog($cid . ' create failed.');
                        continue;
                    }
                }
            }
            $msg = 'success';
        }

        return $msg;
    }

    /*protected function findSelf($cid){
     $req = new ectools_top_request_ItemcatsGetRequest();
     $req->setFields('cid,parent_cid,name,is_parent,status,sort_order');
     $req->setCids($cid);
     $resp = $this->topClient->execute($req);
     //var_dump($resp);exit;
     if($resp->code || $resp->msg){
     $msg = ('【code】'.$resp->code.'<br/>【msg】'.$resp->msg);
     }else if(!$resp){ // 超时错误
     $msg = ('timeout');
     }else{
     // 循环插入货品数据
     if($resp->skus->sku) {
     foreach($resp->skus->sku as $v) {
     if(is_object($v)) $v = get_object_vars($v);
     $v['goods_id'] = $goods['goods_id'];
     $product_id = $this->processProducts($v);
     if(!$product_id){
     kernel::ilog($v['num_iid'] . '_' . $v['sku_id'] . ' create failed.');
     continue;
     }
     }
     }
     $msg = 'success';
     }

     return $childs;
     }

     protected function buildTree($cid=0){
     //   $this->count++;
     $catInfo= $this->findSelf($cid);var_dump($childs);exit;
     if(empty($catInfo) || $catInfo['parent_cid'] == 0){
     return null;
     }else{
     $rescurTree = $this->buildTree($catInfo['parent_cid']);
     if( null !=   $rescurTree){
     $this->goodsCats[$k]['childs'] = $rescurTree;
     }
     }

     foreach ($childs as $k => $v){
     $rescurTree = $this->buildTree($v['parent_cid']);
     if( null !=   $rescurTree){
     $this->goodsCats[$k]['childs'] = $rescurTree;
     }
     }
     }*/

    protected function processGoods($item){ //**********************************************************************************************
         
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
            'store' => $item['num'],
            'marketable' => isset($item['marketable']) ? $item['marketable'] : 'false',
            'pic_url' => $item['pic_url'],
        	'status' => $item['status'],
            'uptime' => strtotime($item['list_time']),
            'downtime' => strtotime($item['delist_time']),
            'last_modify' =>time(),
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
        $goods_count = app::get('ecgoods')->model('shop_goods')->count(array('c_id'=>$cat['cid']));
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

        //$goods = app::get('ecgoods')->model('shop_goods')->dump(array('outer_id'=>$goodsInfo['outer_id']),'goods_id');
        $oGoods = &app::get('ecgoods')->model('shop_goods');
        $sql = "SELECT goods_id FROM sdb_ecgoods_shop_goods
                WHERE outer_id='".$goodsInfo['outer_id']."' AND shop_id='".$goodsInfo['shop_id']."' ";
        $goods = $oGoods->db->selectrow($sql);
        if($goods){
            $goodsInfo['goods_id'] = $goods['goods_id'];
            return false;
        }else{
             
            return true;
        }
    }

    protected function acceptCreateProduct(& $productInfo) {
         
        $product = app::get('ecgoods')->model('shop_products')->dump(array('outer_id'=>$productInfo['outer_id'],'outer_sku_id'=>$productInfo['outer_sku_id']),'product_id,goods_id');

        if($product){
            $productInfo['product_id'] = $product['product_id'];
            return false;
        }else{
            return true;
        }
    }

    protected function acceptCreateGoodsCat(& $catInfo) {

        $cat = app::get('ecgoods')->model('shop_goods_cat')->dump(array('outer_id'=>$catInfo['outer_id']),'cat_id');

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
        
        return false;//gyct:屏蔽更新订单商品id，防止锁表
        
        $offset = 0;
        $len = 100;
        $execTime = time();
        while(true){
            $curTime = time();
            if($curTime >= $execTime + 30 ){
                kernel::database()->dbclose();
                $execTime = $curTime;
            }

            $rows = kernel::database()->select('select obj.obj_id
            	from sdb_ecorder_order_objects as obj 
            	left join sdb_ecorder_orders as o on obj.order_id = o.order_id 
            	where obj.goods_id=0 
            	and o.shop_id="'.$this->_shopInfo['shop_id'].'" 
            	order by obj.obj_id 
            	limit '. ($offset * $len) . ' , ' . $len);
            if(!$rows)break;

            $ids = array();
            foreach($rows as $row){
                $ids[] = $row['obj_id'];
            }

            $sql = 'update
            sdb_ecorder_order_objects as obj,sdb_ecgoods_shop_goods as g 
            set obj.goods_id=g.goods_id where g.outer_id = obj.shop_goods_id and obj.obj_id in('.implode(',', $ids).')';
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

            $rows = kernel::database()->select('select oi.item_id
             from sdb_ecorder_order_items as oi 
             left join sdb_ecorder_orders as o on oi.order_id = o.order_id 
             where oi.product_id=0 and o.shop_id="'.$this->_shopInfo['shop_id'].'"  
             order by oi.item_id 
             limit '. ($offset * $len) . ' , ' . $len);
            if(!$rows)break;

            $ids = array();
            foreach($rows as $row){
                $ids[] = $row['item_id'];
            }

            $sql = 'update sdb_ecorder_order_items as oi, sdb_ecgoods_shop_products as p
            set oi.goods_id=p.goods_id,oi.product_id=p.product_id 
            where p.outer_sku_id = oi.shop_product_id and oi.item_id 
            in('.implode(',', $ids).')';
            kernel::database()->exec($sql);
            $offset++;
        }

        return 'finish';
    }

    protected function updateShopInfo() {
        kernel::single("taocrm_service_shop")->countShopProducts($this->_shopInfo['shop_id']);
        return 'finish';
    }

}