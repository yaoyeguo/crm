<?php
/**
 * 前端店铺售后业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_response_aftersale extends ome_rpc_response
{

    /**
     * 添加售后申请
     * @access public
     * @param array $return_sdf 售后申请单数据
     * @param object $responseObj 框架API接口实例化对象
     * @return array('aftersale_id'=>'售后申请主键ID')
     */
    function add($return_sdf, &$responseObj)
    {

        $order_bn = $return_sdf['order_bn'];
        $return_bn = $return_sdf['return_bn'];
        //返回值
        $return_value = array('tid'=>$order_bn,'aftersale_id'=>$return_bn);
        $status = $return_sdf['status'];
        $shop_id = $this->get_shop_id($responseObj);
        
        $returnObj = &app::get('ome')->model('return_product');
        $return_itemsObj = &app::get('ome')->model('return_product_items');
        $productObj = &app::get('ome')->model('products');
        $orderObj = &app::get('ome')->model('orders');
        $oApi_log = &app::get('ome')->model('api_log');
        $shopObj = &app::get('ome')->model('shop');
        $return_product_detail = $returnObj->dump(array('return_bn'=>$return_bn,'shop_id'=>$shop_id),'return_bn');
        $order_detail = $orderObj->dump(array('shop_id'=>$shop_id,'order_bn'=>$order_bn), 'order_id');
        $shop_detail = $shopObj->dump($shop_id, 'shop_name');
        $shop_name = $shop_detail['shop_name'];
        
        //判断订单是否存在
        if(!$order_detail['order_id']){
            $msg = 'order_bn '.$order_bn.' not exists!';
            //日志记录
            $api_filter = array('marking_value'=>$order_bn,'marking_type'=>'aftersale');
            $api_detail = $oApi_log->dump($api_filter, 'log_id');
            if (empty($api_detail['log_id'])){
                $log_title = '店铺('.$shop_name.')售后申请,订单'.$order_bn.'不存在';
                $addon = $api_filter;
                $log_id = $oApi_log->gen_id();
                $oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
            }
            $responseObj->send_user_error(app::get('base')->_($msg), $return_value);
        }
        //判断售后申请单是否已经存在
        if($return_product_detail['return_bn']){
            return $return_value;
        }
        //判断状态
        if (!$status) {
            $msg = 'status: '.$status.' is not correct!';
            //日志记录
            $api_filter = array('marking_value'=>$status,'marking_type'=>'aftersale');
            $api_detail = $oApi_log->dump($api_filter, 'log_id');
            if (empty($api_detail['log_id'])){
                $log_title = '店铺('.$shop_name.')售后申请状态'.$status.'不正确';
                $addon = $api_filter;
                $log_id = $oApi_log->gen_id();
                $oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
            }
            $responseObj->send_user_error(app::get('base')->_($msg), $return_value);
        }
        //操作工号op_id
        $user_info = kernel::database()->selectrow("SELECT user_id FROM sdb_desktop_users WHERE super='1' ORDER BY user_id asc ");
        $op_id = $user_info['user_id'];
        //售后货品
        $return_product_items = json_decode($return_sdf['return_product_items'],true);
        if (!$return_product_items){
            $msg = 'Sale of goods does not exist!';
            //日志记录
            $api_filter = array('marking_value'=>'items_is_empty','marking_type'=>'aftersale');
            $api_detail = $oApi_log->dump($api_filter, 'log_id');
            if (empty($api_detail['log_id'])){
                $log_title = '店铺('.$shop_name.')售后申请货品不能为空';
                $addon = $api_filter;
                $log_id = $oApi_log->gen_id();
                $oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
            }
            $responseObj->send_user_error(app::get('base')->_($msg), $return_value);
        }
        foreach ($return_product_items as $k=>$items)
        {
             $product_info = $productObj->dump(array('bn'=>$items['bn']),"product_id");
            if(!$product_info){
                $msg = 'bn: '.$items['bn'].' not exists!';
                //日志记录
                $api_filter = array('marking_value'=>$items['bn'],'marking_type'=>'aftersale');
                $api_detail = $oApi_log->dump($api_filter, 'log_id');
                if (empty($api_detail['log_id'])){
                    $log_title = '店铺('.$shop_name.')售后申请货品'.$items['bn'].'不存在';
                    $addon = $api_filter;
                    $log_id = $oApi_log->gen_id();
                    $oApi_log->write_log($log_id,$log_title,__CLASS__,__FUNCTION__,'','','response','fail',$msg,$addon);
                }
                $responseObj->send_user_error(app::get('base')->_($msg), $return_value);
            }
        }
        $order_id = $order_detail['order_id'];
        $sdf = array(
            'return_bn' => $return_bn,
            'shop_id' => $shop_id,
            'order_id' => $order_id,
            'title' => $return_sdf['title'],
            'content' => $return_sdf['content'],
            'comment' => $return_sdf['comment'],
            'memo' => $return_sdf['memo'],
            'add_time' => $return_sdf['add_time']?$return_sdf['add_time']:0,
            'status' => $status,
            'op_id' => $op_id,
            //'member_id' => '',#TODO:暂时为空，待后完善
        );
        $returnObj->create_return_product($sdf);
        
        $product_items  = array();
        foreach ($return_product_items as $k=>$items)
        {
            $product_info = $productObj->dump(array('bn'=>$items['bn']),"product_id");
            $product_items['return_id'] = $sdf['return_id'];
            $product_items['product_id'] = $product_info['product_id'];
            $product_items['bn'] = $items['bn'];
            $product_items['name'] = $items['name'];
            $product_items['num'] = $items['num'];
            $return_itemsObj->save($product_items);
        }
        
        return $return_value;

    }
    
    /**
     * 更新售后申请单状态
     * @access public
     * @param array $status_sdf 售后状态数据
     * @param object $responseObj 框架API接口实例化对象
     */
    function status_update($status_sdf, &$responseObj){

        $status = $status_sdf['status'];
        $return_bn = $status_sdf['return_bn'];
        $order_bn = $status_sdf['order_bn'];
        //返回值
        $return_value = array('tid'=>$order_bn,'aftersale_id'=>$return_bn);
        //状态值判断
        if ($status==''){
            $responseObj->send_user_error(app::get('base')->_('Status field value is not correct'), array());
        }
        if ($return_bn!='' and $order_bn!=''){
            
            $shop_id = $this->get_shop_id($responseObj);
            $orderObj = &app::get('ome')->model('orders');
            $returnObj = &app::get('ome')->model('return_product');
            $return_itemsObj = &app::get('ome')->model('return_product_items');
            $order_detail = $orderObj->dump(array('shop_id'=>$shop_id,'order_bn'=>$order_bn), 'order_id');
            $return_product_detail = $returnObj->dump(array('return_bn'=>$return_bn,'shop_id'=>$shop_id));
            
            $order_id = $order_detail['order_id'];
            $return_id = $return_product_detail['return_id'];
            $return_items_detail = $return_itemsObj->getList('*', $return_id, 0,-1);
            
            $return_data['status'] = $status;
            $return_data['return_id'] = $return_id;

            if(in_array($status, array('2','3'))){//审核中、接受申请
                if ($return_items_detail)
                foreach ($return_items_detail as $key=>$val){
                    $return_data['item_id'][$key] = $val['item_id'];
                    $return_data['effective'][$val['bn']] = $val['num'];
                    $return_data['bn'][$val['bn']] = $val['num'];
                }
                $returnObj->tosave($return_data,'','ecos.b2c');
                
            }elseif($status==4){//完成
                $oProduct_items = &app::get('ome')->model('return_process_items');
                $check_data = $oProduct_items->getList('need_money,other',array('return_id'=>$return_id,'is_check'=>'true'));
                $totalmoney = 0;
                if ($oProduct_items)
                foreach ($oProduct_items as $k=>$v){
                    $return_data['need'][$k] = $v['need_money'];
                    $return_data['other'][$k] = $v['other'];
                    $totalmoney += $v['need_money'];
                }
                
                if ($return_items_detail)
                foreach ($return_items_detail as $key=>$val){
                    $return_data['branch_id'][$key] = $val['branch_id'];
                    $return_data['product_id'][$key] = $val['product_id'];
                    $return_data['item_id'][$key] = $val['item_id'];
                    $return_data['effective'][$key] = $val['num'];
                    $return_data['name'][$key] = $val['name'];
                    $return_data['bn'][$key] = $val['bn'];
                    $return_data['deal'.$key] = 1;#TODO:默认为退货，后期根据B2C修改
                }
                $return_data['totalmoney'] = $totalmoney;
                $return_data['tmoney'] = $totalmoney;
                $return_data['bmoney'] = '0.000';
                $return_data['memo'] = '';
                
                 /*统计此次请求对应货号退货数量累加*/
                $ret=array();
                $can_refund=array();
                foreach($return_data['bn'] as $k=>$v){
                   if(isset($ret[$v])){
                     $can_refund[$v][num]++;
                   }else{
                     $ret[$v] = $v;
                     $can_refund[$v]['num']=1;
                     $can_refund[$v]['effective']=$return_data['effective'][$k];
                   }
                   if($can_refund[$v]['effective']==0){
                          $responseObj->send_user_error(__('货号为['.$v.']没有可申请量，请选择拒绝操作'), '');
                   }else if($can_refund[$v]['num']>$can_refund[$v]['effective']){
                          $responseObj->send_user_error(__('货号为['.$v.']大于可申请量，请选择拒绝操作'), '');
                   }
                }
                $returnObj->saveinfo($return_data,'ecos.b2c');
            }else{
                $filter = array('return_id'=>$return_id);
                $data = array('status'=>$status);
                $returnObj->update($data, $filter);
            }
            
            return $return_value;
            
        }else{
            $responseObj->send_user_error(app::get('base')->_('Return_bn and Order_bn can not be empty'), $return_value);
        }
    }
    

}
?>