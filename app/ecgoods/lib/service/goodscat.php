<?php

class ecgoods_service_goodscat{

    /**
     * 对应店铺信息
     * @var Array
     */
    //protected $_shopInfo = array();

    function __construct(){
        $this->app = app::get('ecgoods');
    }

    /**
     * 商品保存
     *
     * @param $shopId
     * @return bool
     */
    public function saveCat($sdf) {

        app::get('ecgoods')->model('shop_goods_cat')->save($sdf);
        
        return $sdf['cat_id'];
    }




}