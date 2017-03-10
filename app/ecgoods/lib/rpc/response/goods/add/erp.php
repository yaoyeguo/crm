<?php

/**
 * 淘宝订单更新
 *
 * @author shiyao744@sohu.com
 * @version 0.1b
 */
class ecgoods_rpc_response_goods_add_erp {
    
    function __construct()
    {
        $this->app = app::get('ecgoods');
        $this->goods_mdl = $this->app->model('shop_goods');
    }
    
    public function add($sdf, &$response)
    {
        //处理brand
        if($sdf['brand']) $this->brand_process($sdf);
        
        //处理group
        if($sdf['group']) $this->group_process($sdf);
        
        //处理商品数据
        $sql = sprintf("select goods_id from sdb_ecgoods_shop_goods where bn='%s' or name='%s' ", $sdf['bn'], $sdf['name']);
        $rs = $this->goods_mdl->db->selectRow($sql);
        if($rs){            
            $this->updateProcess($sdf, $rs['goods_id']);
        }else{
            $this->createProcess($sdf);
        }
        
        if($sdf['brand_id']>0){
            $brand_arr = array();
            if($sdf['brand_goods_ids']){
                if(!in_array($sdf['goods_id'], $sdf['brand_goods_ids'])){
                    $sdf['brand_goods_ids'][] = $sdf['goods_id'];
                    $brand_arr['goods_id'] = implode(',', $sdf['brand_goods_ids']);
                }
                $brand_arr['goods_count'] = count($sdf['brand_goods_ids']);
            }else{
                $brand_arr['goods_id'] = $sdf['goods_id'];
            }
            if($brand_arr){
                $this->app->model('brand')->update($brand_arr, array('brand_id'=>$sdf['brand_id']));
            }
        }
        
        if($sdf['group_id']>0){
            $group_arr = array();
            if($sdf['group_goods_ids']){
                if(!in_array($sdf['goods_id'], $sdf['group_goods_ids'])){
                    $sdf['group_goods_ids'][] = $sdf['goods_id'];
                    $group_arr['goods_id'] = implode(',', $sdf['group_goods_ids']);
                }
                $group_arr['goods_count'] = count($sdf['group_goods_ids']);
            }else{
                $group_arr['goods_id'] = $sdf['goods_id'];
            }
            if($group_arr){
                $this->app->model('group')->update($group_arr, array('group_id'=>$sdf['group_id']));
            }
        }
        
        return array('goods_id' => $sdf['goods_id']);
    }
    
    //新增商品
    public function createProcess(&$sdf)
    {
        $this->goods_mdl->insert($sdf);
    }
    
    //更新商品
    public function updateProcess(&$sdf, $goods_id)
    {
        $this->goods_mdl->update($sdf, array('goods_id'=>$goods_id));
        $sdf['goods_id'] = $goods_id;
    }

    //处理商品品牌
    public function brand_process(&$sdf)
    {
        $sdf['brand'] = trim($sdf['brand']);
            
        $brand_arr = array(
            'brand_name' => $sdf['brand'],
            'goods_count' => 1,
            'goods_id' => '',
            'create_time' => time(),
            'update_time' => time(),
        );
        
        $brand_mdl = $this->app->model('brand');
        $rs = $brand_mdl->dump(array('brand_name'=>$sdf['brand']));
        if($rs){
            $sdf['brand_id'] = $rs['brand_id'];
            $sdf['brand_goods_ids'] = array_unique(explode(',',$rs['goods_id']));
        }else{
            $brand_mdl->insert($brand_arr);
            $sdf['brand_id'] = $brand_arr['brand_id'];
        }
    }
    
    //处理商品分组&分类
    public function group_process(&$sdf)
    {
        $sdf['group'] = trim($sdf['group']);
            
        $group_arr = array(
            'group_name' => $sdf['group'],
            'goods_count' => 1,
            'goods_id' => '',
            'create_time' => time(),
            'update_time' => time(),
        );
        
        $group_mdl = $this->app->model('group');
        $rs = $group_mdl->dump(array('group_name'=>$sdf['group']));
        if($rs){
            $sdf['group_id'] = $rs['group_id'];
            $sdf['group_goods_ids'] = array_unique(explode(',',$rs['goods_id']));
        }else{
            $group_mdl->insert($group_arr);
            $sdf['group_id'] = $group_arr['group_id'];
        }
    }
}