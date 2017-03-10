<?php
class market_backstage_analysis{

    function count($data){
        $day_time = strtotime($data['day']);

        //统计客户店铺每日分析数据
        kernel::single('ecorder_service_orders')->countBuys(null,$day_time);

        //统计商品的销售情况
        kernel::single('ecgoods_service_products')->countProductBuys();

        //执行统计每天店铺交易数据
        kernel::single('taocrm_service_member')->runAnalysisDay($day_time);

        //生成决策树缓存数据
        kernel::single('taocrm_analysis_cache')->create_tree_task();


        return array('status'=>'succ');
    }


}

