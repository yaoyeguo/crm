<?php
    /**
     * ShopEx licence
     *
     * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
     * @license  http://ecos.shopex.cn/ ShopEx License
     * @version tg---yangminsheng
     * @date 2012-06-19
     */

class ectools_api_prism_syncorder
{

  var $status = array('TRADE_ACTIVE'=>'active','TRADE_CLOSED'=>'dead','TRADE_FINISHED'=>'finish');
  var $pay_status = array('PAY_NO'=>0,'PAY_FINISH'=>1,'PAY_TO_MEDIUM'=>2,'PAY_PART'=>3,'REFUND_PART'=>4,'REFUND_ALL'=>5,'REFUNDING'=>6);
  var $ship_status = array('SHIP_NO'=>0,'SHIP_FINISH'=>1,'SHIP_PREPARE'=>1,'SHIP_PART'=>2,'RESHIP_PART'=>3,'RESHIP_ALL'=>4);
    //矩阵订单数据转化后保存到TG订单系统
  function info2sdf($aData, $shop_id)
  {
          $shop = app::get("ecorder")->model("shop");
          $shop_row = $shop->dump(array('shop_id'=>$shop_id));
          $order_sdf['shop_type'] = $shop_row['shop_type'];
          $order_sdf['node_id'] = $shop_row['node_id'];
          $order_sdf['order_bn'] = $aData['tid'];
          $order_sdf['status'] = $this->status[$aData['status']];
          $order_sdf['pay_status'] = $this->pay_status[$aData['pay_status']];
          $order_sdf['ship_status'] = $this->ship_status[$aData['ship_status']];
          $order_sdf['is_delivery'] = $aData['is_delivery'];
          $order_sdf['t_type'] = empty($aData['tradetype'])?'fixed':$aData['tradetype'];
          $order_sdf['fx_order_id'] = $aData['fx_order_id'];
          $order_sdf['tc_order_id'] = $aData['tc_order_id'];

          //配送信息 begin
          $shipping['shipping_name'] = $aData['shipping_type'];
          $shipping['cost_shipping'] = $aData['shipping_fee'];
          $shipping['is_protect'] = $aData['is_protect'];
          $shipping['cost_protect'] = $aData['protect_fee'];
          $shipping['is_cod'] = $aData['is_cod'];
          $shipping['delivery_time'] = $aData['consign_time'];
          $shipping['logistics_code'] = $aData['logistics_no'];
          $shipping['logistics_company'] = $aData['company_name'];
          
          //配送信息 end
          $order_sdf['shipping'] = json_encode($shipping);
          
          //支付方式信息 begin
          $payinfo['pay_name'] = $aData['payment_type'];
          $payinfo['cost_payment'] = $aData['cost_payment'];  //支付费用
          
          //支付方式信息 end
          $order_sdf['payinfo'] = json_encode($payinfo);
          $order_sdf['pay_bn'] = $aData['payment_tid'];
          $order_sdf['weight'] = $aData['total_weight'];
          $order_sdf['title'] = $aData['title'];
          $order_sdf['createtime'] = $aData['created'];
          
          // 收货人信息 begin
          $consignee['name'] = $aData['receiver_name'];
          $consignee['area_state'] = $aData['receiver_state'];
          $consignee['area_city'] = $aData['receiver_city'];
          $consignee['area_district'] = $aData['receiver_district'];
          $consignee['addr'] = $aData['receiver_address'];
          $consignee['zip'] = $aData['receiver_zip'];
          $consignee['telephone'] = $aData['receiver_phone'];
          $consignee['mobile'] = $aData['receiver_mobile'];
          $consignee['email'] = $aData['receiver_email'];
          $consignee['r_time'] = $aData['receiver_time'];
          
          //收货人信息 end
          $order_sdf['consignee'] = json_encode($consignee);
          
          //发货人信息 begin    暂时没有找到 用发货人信息代替
          $consigner['name'] = $aData['receiver_name'];
          $consigner['area_state'] = $aData['receiver_state'];
          $consigner['area_city'] = $aData['receiver_city'];
          $consigner['area_district'] = $aData['receiver_district'];
          $consigner['addr'] = $aData['receiver_address'];
          $consigner['zip'] = $aData['receiver_zip'];
          $consigner['telephone'] = $aData['receiver_phone'];
          $consigner['mobile'] = $aData['receiver_mobile'];
          $consigner['email'] = $aData['receiver_email'];
          
          //发货人信息 end
          $order_sdf['consigner'] = $consigner;
          
          //代销人信息 begin
          $selling_agent['member_info']['uname'] = $aData['agent_uname'];
          $selling_agent['member_info']['name'] = $aData['agent_name'];
          $selling_agent['member_info']['level'] = $aData['agent_level'];
          $selling_agent['member_info']['birthday'] = $aData['agent_birthdate'];
          $selling_agent['member_info']['sex'] = $aData['agent_sex'];
          $selling_agent['member_info']['area_state'] = $aData['agent_state'];
          $selling_agent['member_info']['area_city'] = $aData['agent_city'];
          $selling_agent['member_info']['area_district'] = $aData['agent_district'];
          $selling_agent['member_info']['addr'] = $aData['agent_address'];
          $selling_agent['member_info']['zip'] = $aData['agent_zip'];
          $selling_agent['member_info']['telephone'] = $aData['agent_phone'];
          $selling_agent['member_info']['mobile'] = $aData['agent_mobile'];
          $selling_agent['member_info']['email'] = $aData['agent_email'];
          $selling_agent['website']['name'] = $aData['agent_shop_name'];
          $selling_agent['website']['domain'] = $aData['agent_shop_url'];
          
          #单拉订单时,增加分销王代销人信息
          if($shop_row['shop_type'] == 'shopex_b2b'){
              $order_sdf['seller_name'] = $aData['seller_name'];#卖家姓名
              $order_sdf['seller_mobile'] = $aData['seller_mobile'];#卖家电话号码
              $order_sdf['seller_phone'] = $aData['seller_phone'];#卖家电话号码
              $order_sdf['seller_state'] = $aData['seller_state'];#卖家的所在省份
              $order_sdf['seller_city'] = $aData['seller_city'];#卖家的所在城市
              $order_sdf['seller_district'] = $aData['seller_district'];#卖家的所在地区
              $order_sdf['seller_zip'] = $aData['seller_zip'];#卖家的邮编
              $order_sdf['seller_address'] = $aData['seller_address'];#发货人的详细地址
          } 
          
          $selling_agent['website']['logo'] = '';//代销人网站LOGO
          
          //代销人信息 end
          $order_sdf['selling_agent'] = $selling_agent;
          
          //买家会员信息 begin
          $member_info['uname'] = $aData['buyer_uname'];
          $member_info['name'] = $aData['buyer_name'];
          $member_info['alipay_no'] = $aData['buyer_alipay_no'];
          $member_info['area_state'] = $aData['buyer_state'];
          $member_info['area_city'] = $aData['buyer_city'];
          $member_info['area_district'] = $aData['buyer_district'];
          $member_info['addr'] = $aData['buyer_address'];
          $member_info['mobile'] = $aData['buyer_mobile'];
          $member_info['tel'] = $aData['buyer_phone'];
          $member_info['email'] = $aData['buyer_email'];
          $member_info['zip'] = $aData['buyer_zip'];
          if($aData['buyer_id']){
            $member_info['member_id'] = $aData['buyer_id'];
            $order_sdf['buyer_id'] = $aData['buyer_id'];
          }

          //买家会员信息 end
          $order_sdf['member_info'] =  json_encode($member_info);
          //订单来源
          $order_sdf['order_source'] =  $aData['order_source'];
          //订单优惠方案信息  begin
          $tmp_pmt_detail = $aData['promotion_details']['promotiondetail'];

          $order_sdf['pmt_detail'] = array();
          $order_sdf['other_list'] = array();
          $k_count = 0;
          if($tmp_pmt_detail){
              foreach((array)$tmp_pmt_detail as $k=>$v){
                  $order_sdf['pmt_detail'][$k]['pmt_amount'] = $v['promotion_fee'];
                  $order_sdf['pmt_detail'][$k]['pmt_describe'] = $v['promotion_name'];

                  if(isset($v['gift_item_id']) && $v['gift_item_id']){
                    $order_sdf['other_list'][$k_count]['type'] = 'gift';
                    $order_sdf['other_list'][$k_count]['id'] = $v['gift_item_id'];
                    $order_sdf['other_list'][$k_count]['name'] = $v['gift_item_name'];
                    $order_sdf['other_list'][$k_count]['num'] = $v['gift_item_num'];
                    $k_count++;
                  }
              }
          }

          // 应收款记录
          if ($aData['is_cod'] == 'true' && isset($aData['unpaidprice'])) {
            $order_sdf['other_list'][] = array(
              'type' => 'unpaid',
              'unpaidprice' => $aData['unpaidprice'],
            );
          }

          $order_sdf['other_list'] = json_encode($order_sdf['other_list']);
          
          //兼容ecstore的付款信息
          if($aData['payment_lists']){
            $order_sdf['payment_lists'] = json_encode($aData['payment_lists']);
          }          

          //订单优惠方案信息  end
          //支付单信息  新版本
          foreach((array) $aData['payment_lists']['payment_list'] as $p_k=>$p_v)
          {
            $payments[$p_k]['trade_no'] = $p_v['payment_id'];
            $payments[$p_k]['money'] = isset($p_v['pay_fee'])?$p_v['pay_fee']:$p_v['payed_fee'];
            $payments[$p_k]['pay_time'] = $p_v['pay_time'];
            $payments[$p_k]['account'] = $p_v['seller_account'];
            $payments[$p_k]['bank'] = $p_v['seller_bank'];
            $payments[$p_k]['pay_bn'] = $p_v['payment_code'];
            $payments[$p_k]['paycost'] = $p_v['paycost'];
            $payments[$p_k]['pay_account'] = $p_v['buyer_account'];
            $payments[$p_k]['paymethod'] = $p_v['payment_name'];
            $payments[$p_k]['memo'] = $p_v['memo'];
          }
          $order_sdf['payments'] = $payments;
          
          //支付单信息  新版本
          $order_sdf['cost_item'] = $aData['total_goods_fee'];
          $order_sdf['is_tax'] = $aData['invoice_title'] ? true:false;
          $order_sdf['cost_tax'] = $aData['invoice_fee'];
          $order_sdf['tax_title'] = $aData['invoice_title'];
          $order_sdf['currency'] = $aData['currency'];
          $order_sdf['cur_rate'] = $aData['currency_rate'];
          $order_sdf['score_u'] = $aData['point_fee'];
          $order_sdf['score_g'] = $aData['buyer_obtain_point_fee'];
          if(in_array($shop_row['shop_type'],$this->shopex_shop_type())){
              $order_sdf['discount'] = $aData['discount_fee'];
          }else{
              $order_sdf['discount'] = 0.00;
          }
          $order_sdf['pmt_goods'] = $aData['goods_discount_fee'];
          $order_sdf['pmt_order'] = $aData['orders_discount_fee'];
          $order_sdf['total_amount'] = $aData['total_trade_fee']; //订单总格  = 交易应付总额
          $order_sdf['payed'] = $aData['payed_fee'];
          $order_sdf['custom_mark'] = $aData['buyer_message'];
          $order_sdf['mark_text'] = $aData['trade_memo'];
          $order_sdf['buyer_flag'] = $aData['buyer_flag'];
          $order_sdf['tax_no'] = $aData['tax_no'];  //发票号
          $order_sdf['order_limit_time'] = $aData['pay_time'];  //订单失效时间
          $order_sdf['coupons_name'] = $aData['coupons_name']; //优惠卷名称

          //寄售字段
          $order_sdf['order_type'] = $aData['is_brand_sale'];
          $order_sdf['is_force_wlb'] = $aData['is_force_wlb'];
          $order_sdf['is_lgtype'] = $aData['is_lgtype'];

          //订单商品结构数组信息
          $order_objects = array();
          //$aData['orders'] = json_decode($aData['orders'],true);

          foreach($aData['orders']['order'] as $o_k=>$o_v)
          {
              $order_objects[$o_k]['obj_type'] = $o_v['type'];
              $order_objects[$o_k]['shop_goods_id'] = $o_v['iid'];
              $order_objects[$o_k]['oid'] = $o_v['oid'];
              $order_objects[$o_k]['obj_alias'] = $o_v['type_alias'];
              $order_objects[$o_k]['bn'] = $o_v['orders_bn'];
              $order_objects[$o_k]['name'] = $o_v['title']; //子订单名称
              $order_objects[$o_k]['price'] = $o_v['total_order_fee']/$o_v['items_num']; //原始单价
              $order_objects[$o_k]['amount'] = $o_v['total_order_fee']; //原始价小计
              $order_objects[$o_k]['sale_price'] = $o_v['sale_price'];
              $order_objects[$o_k]['quantity'] = $o_v['items_num'];
              $order_objects[$o_k]['weight'] = $o_v['weight'];
              $order_objects[$o_k]['score'] = 0;//积分
              $order_objects[$o_k]['is_oversold'] = $o_v['is_oversold'];//淘宝超卖标记
              $order_objects[$o_k]['fx_oid'] = $o_v['fx_oid'];
              $order_objects[$o_k]['tc_order_id'] = $o_v['tc_order_id'];
              $order_objects[$o_k]['cost_tax'] = $o_v['cost_tax'];
              $order_objects[$o_k]['buyer_payment'] = $o_v['buyer_payment'];

              $order_items = array();

              $total_pmt_price = 0;

              foreach($o_v['order_items']['orderitem'] as $i_k=>$i_v)
              {
                  $order_items[$i_k]['item_type'] = $i_v['item_type'];
                  $order_items[$i_k]['shop_goods_id'] = $i_v['iid'];
                  $order_items[$i_k]['shop_product_id'] = $i_v['sku_id'];
                  $order_items[$i_k]['bn'] = $i_v['bn'];
                  $order_items[$i_k]['name'] = $i_v['name'];
                  $product_attr = array();
                  $sku_properties = explode(';',$i_v['sku_properties']);
                  foreach($sku_properties as $si=>$sp){
                    $_sp = explode(':',$sp);
                    $product_attr[$si]['label'] = $_sp[0];
                    $product_attr[$si]['value'] = $_sp[1];
                  }
                  $order_items[$i_k]['product_attr'] = $product_attr;
                  $order_items[$i_k]['quantity'] = $i_v['num'];
                  $order_items[$i_k]['price'] = $i_v['price'];
                  $order_items[$i_k]['amount'] = $i_v['total_item_fee'];
                  $order_items[$i_k]['pmt_price'] = $i_v['discount_fee'];
                  $order_items[$i_k]['sale_price'] = $i_v['sale_price'];
                  $order_items[$i_k]['weight'] = $i_v['weight'];
                  $order_items[$i_k]['score'] = $i_v['score'];
                  $order_items[$i_k]['status'] = $i_v['status'];

                  $order_items[$i_k]['fx_oid'] = $i_v['fx_oid'];
                  $order_items[$i_k]['cost_tax'] = $i_v['cost_tax'];
                  $order_items[$i_k]['buyer_payment'] = $i_v['buyer_payment']; 
                  
                  //兼容ecstore的sale_price 2015-04-29 By YW
                    if($i_v['sale_price']){
                        $i_v['sale_price'] = floatval($i_v['sale_price']);
                        $order_items[$i_k]['amount'] = $item['sale_price'];
                        $order_items[$i_k]['price'] = round($order_items[$i_k]['amount']/$order_items[$i_k]['quantity'], 2);
                    }
                                   
                  $total_pmt_price +=$i_v['discount_fee'];
              }
              $order_objects[$o_k]['order_items'] = $order_items;
              $order_objects[$o_k]['pmt_price'] = $o_v['discount_fee'] - $total_pmt_price;
          }
          $order_sdf['order_objects'] = json_encode($order_objects);
          //订单商品结构数组信息
          $order_sdf['lastmodify'] = $aData['lastmodify']?$aData['lastmodify']:$aData['modified'];

          return $order_sdf;
      }

    /**
     * shopex前端店铺列表
     * @author yangminsheng
     * @return array
     **/
    function shopex_shop_type(){
        $shop = array('shopex_b2b','shopex_b2c','ecos.b2c','ecshop_b2c','ecos.dzg');
        return $shop;
    }
}
