<?php
class taocrm_service_order{
    
    public function save_member($sdf)
    {
        $memberInfo = array();
        $memberInfo = json_decode($sdf['member_info'], true);
        $oOrders = &app::get(ORDER_APP)->model('orders');
        $oServiceMember = kernel::single('taocrm_service_member');
        $rs_order = $oOrders->dump(array('order_bn'=>$sdf['order_bn'], 'shop_id'=>$sdf['shop_id']), 'order_id,order_bn');
        
        //保存客户信息
        $member_id = $oServiceMember->saveMember($sdf['shop_id'],$memberInfo, $sdf['consignee']);
        
        //更新订单的member_id
        $order_arr = array('order_id'=>$rs_order['order_id'],'member_id'=>$member_id);
        $oOrders->save($order_arr);
        
        //更新客户的统计数据
        $oServiceMember->countMemberBuys($member_id,$sdf['shop_id']);
        
        return $member_id;
        
        
        
        
        
        
        
        
        
        if(!$order_detail['order_bn'] && $sdf['shop_id'] && $member['uname']){
            $memberObj = &app::get('taocrm')->model('members');
            $shopLvObj = &app::get('ecorder')->model('shop_lv');
          //  $addrObj = &app::get('taocrm')->model('member_addrs');
            $memberInfo = $memberObj->dump(array('shop_id'=>$sdf['shop_id'],'uname'=>$member['uname']),'*');
            if($memberInfo['member_id'] && $memberInfo['member_id']>0) {
                $mData['order_total_num'] = $memberInfo['order_total_num']+1;
                $mData['order_total_amount'] = $memberInfo['order_total_amount']+$sdf['total_amount'];
                if($sdf['pay_status']==1 || $sdf['status'] == 'finish') {
                    $mData['order_succ_num'] = $memberInfo['order_succ_num']+1;
                    $mData['order_succ_amount'] = $memberInfo['order_succ_amount']+$sdf['total_amount'];
                }
                
                if ($sdf['pay_status']==1 && $sdf['status'] == 'finish') {
                    //计算积分和等级
	                $pointSet['method'] = &app::get('taocrm')->getConf('taocrm.level_point.method');
	                $pointSet['config'] = &app::get('taocrm')->getConf('taocrm.level_point.config');
	
	                if($pointSet['method'] == "0"){
	                    $setNum = $pointSet['config']['advanced']['num']?$pointSet['config']['advanced']['num']:1;
	                    $setAmount = $pointSet['config']['advanced']['amount']?$pointSet['config']['advanced']['amount']:1;
	                    if($mData['order_succ_num']>=4){
	                        $i = $mData['order_succ_num']-1;
	                        $F = $pointSet['config']['advanced']['F'][$i]?$pointSet['config']['advanced']['F'][$i]:$mData['order_succ_num'];
	                    }else{
	                        $i = $mData['order_succ_num']-1;
	                        $F = $pointSet['config']['advanced']['F'][$i]?$pointSet['config']['advanced']['F'][$i]:$mData['order_succ_num'];
	                    }
	                    $mData['experience'] = ($F*$setNum)+($mData['order_succ_amount']*$setAmount);
	                }else{
	                    $setAmount = $pointSet['config']['normal']['amount']?$pointSet['config']['normal']['amount']:1;
	                    $mData['experience'] = $memberInfo['experience']+(floor($sdf['total_amount'])*$setAmount);
	                }
	
	                $mData['point'] = $memberInfo['point']+floor($sdf['total_amount']);
	                $mData['experience'] = $memberInfo['experience']+floor($sdf['total_amount']);
	
	                //$mData['member_lv_id'] = $lvObj->getLvExperience($mData['experience']);
	                $mData['shop_lv_id'] = $shopLvObj->getLvExperience($mData['experience'],$sdf['shop_id']);                    	
                }
                
                $mData['order_first_time'] = ($sdf['createtime']<$memberInfo['order_first_time'])?$sdf['createtime']:$memberInfo['order_first_time'];
                $mData['order_last_time'] = ($sdf['createtime']>$memberInfo['order_last_time'])?$sdf['createtime']:$memberInfo['order_last_time'];

                $mData['regtime'] = ($sdf['createtime']<$memberInfo['regtime'])?$sdf['createtime']:$memberInfo['regtime'];

                $memberObj->update($mData,array('member_id'=>$memberInfo['member_id']));

               // $addCount = $addrObj->count(array('member_id'=>$memberInfo['member_id']));
//                $addrInfo = $addrObj->dump(array('member_id'=>$memberInfo['member_id'],'name'=>$sdf['consignee']['name']),'addr_id');
//                if(!$addrInfo['addr_id'] && $addCount<5){
//                    $area = $sdf['consignee']['area'];
//                    kernel::single("ecorder_func")->region_validate($area);
//
//                    $addrData = array();
//                    $addrData['member_id'] = $memberInfo['member_id'];
//                    $addrData['name'] = $sdf['consignee']['name'];
//                    $addrData['area'] = $area;
//                    $addrData['addr'] = $sdf['consignee']['addr'];
//                    $addrData['zip'] = $sdf['consignee']['zip'];
//                    $addrData['tel'] = $sdf['consignee']['telephone'];
//                    $addrData['mobile'] = $sdf['consignee']['mobile'];
//
//                    $addr_id = $addrObj->insert($addrData);
//                }
//                return $memberInfo['member_id'];
            }else{
                $mData = array();
                $mData['shop_id'] = $sdf['shop_id'];
                $mData['uname'] = $member['uname'];
                $mData['name'] = $sdf['consignee']['name'] ? $sdf['consignee']['name'] : $member['name'];
                $mData['addr'] = $member['addr'] ? $member['addr'] : $sdf['consignee']['addr'];
                $mData['zip'] = $member['zip'] ? $member['zip'] : $sdf['consignee']['zip'];
                $mData['email'] = (strpos($member['alipay_no'],"@")!==false)?$member['alipay_no']:$member['email'];
                $mData['alipay_no'] = $member['alipay_no'];
                $mData['mobile'] = $member['mobile'] ? $member['mobile'] : $sdf['consignee']['mobile'];
                $mData['tel'] = $member['tel'] ? $member['telephone'] : $sdf['consignee']['telephone'];
                $mData['regtime'] = $sdf['createtime'];

                $area = $sdf['consignee']['area'];
                kernel::single("ecorder_func")->region_validate($area);
                $mData['area'] = $area;

                $mData['order_total_num'] = 1;
                $mData['order_total_amount'] = $sdf['total_amount'];
                if($sdf['pay_status']==1 || $sdf['status'] == 'finish'){
                    $mData['order_succ_num'] = 1;
                    $mData['order_succ_amount'] = $sdf['total_amount'];
                }
                
                if ($sdf['pay_status']==1 && $sdf['status'] == 'finish') {
	                $mData['point'] = floor($sdf['total_amount']);
	                $mData['experience'] = floor($sdf['total_amount']);
	                
	                $mData['shop_lv_id'] = $shopLvObj->getLvExperience($mData['experience'],$sdf['shop_id']);
                }
                else {
                	$mData['shop_lv_id'] = $shopLvObj->getLvExperience(0, $sdf['shop_id']);
                }
                $mData['order_first_time'] = $sdf['createtime'];
                $mData['order_last_time'] = $sdf['createtime'];
                
//                if($member_id = $memberObj->insert($mData)){
//                    $addrData = array();
//                    $addrData['member_id'] = $member_id;
//                    $addrData['name'] = $sdf['consignee']['name'];
//                    $addrData['area'] = $area;
//                    $addrData['addr'] = $sdf['consignee']['addr'];
//                    $addrData['zip'] = $sdf['consignee']['zip'];
//                    $addrData['tel'] = $sdf['consignee']['telephone'];
//                    $addrData['mobile'] = $sdf['consignee']['mobile'];
//
//                    $addr_id = $addrObj->insert($addrData);
//
//                    return $member_id;
//                }
            }
        }
        return '';
    }
}
