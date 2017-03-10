<?php

class openapi_request_jocs_ship extends openapi_rpc_node_jocs{

    /**
     * @暂停正常发货接口
     * undocumented function
     * @param
     * @return void
     **/
     function delivery_status($param)
    {
        #$res = $this->call($param,'return_goods');
        $method_name = $param['is_shipments'] == 2 ? 'suspendedShipments' :'replyshipments';
        $res = $this->call($param,$method_name);
        return $res;
    }
    /**
     * @留言回复回调接口
     * undocumented function
     * @param
     * @return void
     **/
     function return_msg_jocs($param)
    {
        $res = $this->call($param,'saveAmMessageForCrm');
        return $res;
    }

    /**
     * @获取订单属性,PV值  M020150605000009
     * undocumented function
     * @param
     * @return void
     **/
    function get_order_msg($param)
    {
//        $res = $this->call($param,'savePdLogisticsBaseByInter');
        $res = $this->call_jocs_order($param,'getOrderInfos');
    //    $res = $this->call($param,'getOrderInfos');
        return $res;
    }

    /**
     * @工单审批后,调用Java接口,提醒下一节点工单审批人审批
     * undocumented function
     * @param
     * @return void
     **/
    function call_mobile_worker($param)
    {
        //$rs = kernel::single('openapi_request_jocs_ship')->delivery_status($data);

        $res = $this->call_mobile_worker($param);

        return $res;
    }


}