<?php

class ecorder_mdl_payments extends dbeav_model{

    /* create_payments 添加付款单
     * @param sdf $sdf
     * @return sdf
     */
    function create_payments(&$sdf){
        $objOrder = &app::get('ecorder')->model('orders');
        $this->save($sdf);
        $order_id = $sdf['order_id'];
        $shop_id = $sdf['shop_id'];
        $payment_money = $sdf['money'];
        if ($sdf['is_orderupdate'] != 'false'){
            $this->_updateOrder($order_id,$shop_id,$payment_money);
        }

        //如果有OME自动确认订单插件的话，会按照自动确认规则来自动确认订单
        if($oAuto = kernel::service('do_autodispatch')){
            $order_sdf = $objOrder->dump($order_id,"*",array("order_objects"=>array("*",array("order_items"=>array("*")))));
            if($order_sdf['shipping']['is_cod'] == 'false' && $order_sdf['pay_status'] == 1){
                if(method_exists($oAuto,autodispatch)){
                    $oAuto->autodispatch($order_sdf);
                }
            }
        }
        //如果有KPI考核插件，会增加客服的考核
        if($oKpi = kernel::service('omekpi_servicer_incremental')){
            $kpi_sdf = $objOrder->dump($order_id);
            if(method_exists($oKpi,getOrderIncremental)){
                $oKpi->getOrderIncremental($kpi_sdf);
            }
        }

    }

    /*
     * 更新订单状态及金额
     * @param string order_id
     * @param string shop_id
     * @param money payment_money
     */
    private function _updateOrder($order_id, $shop_id, $payment_money){

        $orderObj = &app::get('ecorder')->model('orders');
        $order_detail = $orderObj->dump(array('order_id'=>$order_id), 'payed,cost_payment,total_amount');

        $filter = array('order_id'=>$order_id);
        $orderdata['payed'] = $order_detail['payed'] + $payment_money;
        if ($orderdata['payed'] < $order_detail['total_amount'])
        {
           //如果已经付款金额小于总金额，则为部分付款
           $orderdata['pay_status'] = 3;
        }else{
           //如果已经付款金额等于总金额，则为全部付款
           $orderdata['pay_status'] = 1;
        }
        $orderObj->update($orderdata, $filter);
    }

    function getMethods($type=''){

        if($type=="online"){
            $sql = ' AND pay_type NOT IN(\'OFFLINE\',\'DEPOSIT\')';
        }
        return $this->db->select('SELECT * FROM sdb_ecorder_payment_cfg WHERE disabled = \'false\''.$sql,PAGELIMIT);
    }
    function getAccount(){

        $query = 'SELECT DISTINCT bank, account FROM sdb_ecorder_payments WHERE status="succ"';
        return $this->db->select($query);
    }

    /*
     * 生成付款单号
     *
     *
     * @return 付款单号
     */
    function gen_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $payment_bn = date("YmdH").'12'.str_pad($i,6,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('select payment_id from sdb_ecorder_payments where payment_bn =\''.$payment_bn.'\'');
        }while($row);
        return $payment_bn;
    }

    function searchOptions(){
        return array(

            );
    }
}
?>