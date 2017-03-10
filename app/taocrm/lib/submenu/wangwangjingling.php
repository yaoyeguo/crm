<?php

class taocrm_submenu_wangwangjingling
{
    private static $wangwangService = '';
    public function index()
    {
        return true;
        $bind = false;
        $shopList = $this->getAllShopId();
        foreach ($shopList as $v) {
            $bind = $this->isBind($v['shop_id']);
            if ($bind == true) {
                break;
            }
        }
        return $bind;
    }
    
    //判断的taocrm与旺旺精灵绑定
    private function isBind($shop_id = '')
    {
        $service = $this->getWangwangService();
        return $service->isBind($shop_id);
    }
    
    private function getWangwangService()
    {
        if (self::$wangwangService == '') {
            self::$wangwangService = kernel::single('taocrm_wangwangjingling_service');
        }
        return self::$wangwangService;
    }
    /**
     * 获得所有店铺ID
     */
    private function getAllShopId()
    {
        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        return $shopList;
    }
}
