<?php 

class market_mdl_active_abc extends dbeav_model{

    //营销效果评估缓存
    function getABC($active_id)
    {
        $identify_conf = array(
            '0,2' => 'A',
            '1' => 'C',
            '3' => 'B',
        );
        $accessActiveInfo = array();
        $filter = array(
            'active_id'=>$active_id,
        );
        $rs = $this->getList('*', $filter);
        foreach($rs as $v){
            $accessActiveInfo[$identify_conf[$v['identify']]] = array(
                'MemberCount'=>$v['total_members'],
                'BuyMember'=>$v['order_members'],
                'PayMember'=>$v['paid_members'],
                'FinishMember'=>$v['finish_members'],
                'AmountCount'=>$v['paid_amount'] ? $v['paid_amount'] : $v['total_amount'],
            );
        }
        ksort($accessActiveInfo);
        return $accessActiveInfo;
    }
}