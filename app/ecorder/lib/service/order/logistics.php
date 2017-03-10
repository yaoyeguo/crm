<?php
class ecorder_service_order_logistics {

    function __construct(){
        
    }

    /**
     *  保存订单的物流信息
     */
    function process( & $order_sdf)
    {   
        $mdl_logi_info = app::get('ecorder')->model('logi_info');
        $save_arr = array(
            'order_id' => $order_sdf['order_id'],
            'order_bn' => $order_sdf['order_bn'],
            'ship_name' => $order_sdf['consignee']['name'],
            'ship_mobile' => $order_sdf['consignee']['mobile'],
            'logi_company' => '',
            'logi_no' => '',
            'member_id' => $order_sdf['member_id'],
            'delivery_time' => $order_sdf['delivery_time'],
            'create_time' => time(),
        );
        
        foreach($order_sdf['order_objects'] as $v){
            if( ! $v['logistics_company'] or ! $v['logistics_code']){
                continue;
            }
            
            $filter = array(
                'order_id'=>$order_sdf['order_id'],
                'logi_no'=>$v['logistics_code'],
            );
            
            kernel::single('ecorder_func')->clear_value($filter);
            
            if( ! $mdl_logi_info->dump($filter,'id')){
                $save_arr['logi_company'] = $v['logistics_company'];
                $save_arr['logi_no'] = $v['logistics_code'];
                $mdl_logi_info->insert($save_arr);
                unset($save_arr['id']);
            }
        }

        //兼容矩阵传值的问题
        if(!empty($order_sdf['shipping']['logistics_no']) && !empty($order_sdf['shipping']['company_name'])){

            $logi_no_arr = explode(',',$order_sdf['shipping']['logistics_no']);
            $company_name_arr = explode(',',$order_sdf['shipping']['company_name']);
            foreach($logi_no_arr as $k=>$v){
                $filter = array(
                    'order_id'=>$order_sdf['order_id'],
                    'logi_no'=>$v,
                );

                kernel::single('ecorder_func')->clear_value($filter);

                if( ! $mdl_logi_info->dump($filter,'id')){
                    $save_arr['logi_company'] = $company_name_arr[$k];
                    $save_arr['logi_no'] = $v;
                    $mdl_logi_info->insert($save_arr);
                    unset($save_arr['id']);
                }
            }
        }
        return true;
    }

}
