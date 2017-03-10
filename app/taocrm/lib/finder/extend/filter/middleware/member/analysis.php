<?php

class taocrm_finder_extend_filter_middleware_member_analysis
{
    protected static $shopObj = '';
    function get_extend_colums()
    {
        $data = '';
        $shopId = $this->getShopId();
        if ($shopId) {
            $model =  app::get(ORDER_APP)->model('shop_lv');
            $lvInfo = $model->getList('lv_id,name', array('shop_id' => $shopId));
            if ($lvInfo) {
                $type = array();
                foreach ($lvInfo as $v) {
                    $type[$v['lv_id']] = $v['name'];
                }
                $data['middleware_member_analysis'] = array();
                $data['middleware_member_analysis']['columns'] = array(
                    'ext_lv_id' => array(
                        'type' => $type,
                        'label' => '客户等级',
                        'width' => 130,
                        'filtertype' => 'yes',
                        'filterdefault' => true,
                    ),
                );
            }
        }
        return $data;
    }
    
    /**
     * 获得店铺ID
     */
    protected function getShopId()
    {
        if ($this->shop_id == '') {
            $shopList = $this->getAllShopId();
            $currentShopInfo = $shopList[intval($_GET['view'])];
            if ($currentShopInfo) {
                $this->shop_id = $currentShopInfo['shop_id'];
            }
            else {
                if (isset($_GET['shop_id'])) {
                    $this->shop_id = $_GET['shop_id'];
                }
                else {
                    $this->shop_id = $shopList[0]['shop_id'];
                }
            }
        }
        return $this->shop_id;
    }
    
    /**
     * 获得所有店铺ID
     */
    protected function getAllShopId()
    {
        $shopObj = $this->getShopObj();
        $shopList = $shopObj->getList('shop_id,name');
        return $shopList;
    }
    
    /**
     * 获得店铺对象
     */
    protected function getShopObj()
    {
        if (self::$shopObj == '') {
            self::$shopObj = &app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
    }
}