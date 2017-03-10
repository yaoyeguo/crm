<?php
class ecorder_service_traderates {

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();

    function __construct(){
        $this->app = app::get('ecorder');
    }


    /**
     * 订单评价保存
     *
     * @param $shopId
     * @return bool
     */
    public function saveTradeRates($sdf) {

        $this->app->model('trade_rates')->save($sdf);
        
        return $sdf['rate_id'];
    }

    

}
