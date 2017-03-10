<?php
class taocrm_service_channel {

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();

    function __construct(){
        $this->app = app::get('ecorder');
    }


    /**
     * 渠道统计
     *
     * @param $shopId
     * @return bool
     */
    public function countChannelBuys($channel_id) {
        if(empty($channel_id))return false;
        
        $analysisData = $this->getAnalysis($channel_id);

        $analysisData['channel_id'] = $channel_id;
        if($analysisData['members'])
        $analysisData['per_amount'] = $analysisData['amount']/$analysisData['members'];
        if($analysisData['finish_members'])
        $analysisData['finish_per_amount'] = $analysisData['finish_amount']/$analysisData['finish_members'];
        if($analysisData['unpay_members'])
        $analysisData['unpay_per_amount'] = $analysisData['unpay_amount']/$analysisData['unpay_members'];
        if($analysisData['refund_members'])
        $analysisData['refund_per_amount'] = $analysisData['refund_amount']/$analysisData['refund_members'];

        return app::get('ecorder')->model('shop_channel')->save($analysisData);
    }


    /**
     * 获取渠道统计数据
     *
     * @param void
     * @return array
     */
    protected function getAnalysis($channel_id) {

        $sql = "select shop_id from sdb_ecorder_shop where channel_id=$channel_id ";
        $rs = $this->app->model('shop')->db->select($sql);
        if(!$rs) return false;
        foreach($rs as $v){
            $shop_ids[] = '"'.$v['shop_id'].'"';
        }

        $sql = 'select
                sum(refund_amount) as refund_amount,
                sum(refund_orders) as refund_orders,
                sum(finish_amount) as finish_amount, 
                sum(finish_orders) as finish_orders, 
                sum(unpay_orders) as unpay_orders, 
                sum(unpay_amount) as unpay_amount, 
                sum(orders) as orders, 
                sum(amount) as amount,
                sum(members) as members,
                sum(unpay_members) as unpay_members,
                sum(refund_members) as refund_members,
                sum(finish_members) as finish_members
			from sdb_ecorder_shop_analysis 
			where shop_id in ('.implode(',',$shop_ids).') ';

        return $this->app->model('shop')->db->selectRow($sql);
    }

}
