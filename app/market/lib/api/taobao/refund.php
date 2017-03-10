<?php

/*
 * 订单API调用
 *
 * @author hzjsq
 * @version 0.1
 */

class market_api_taobao_refund extends market_api_taobao_request implements market_api_interface {
    /**
     * 通过API接口要获取的订单内容
     *
     * @var String
     */
    const FIELDS = 'refund_id, tid,oid,buyer_nick,created,modified,status,total_fee,refund_fee';

    /**
     * 定义API每页获取的订单数
     *
     * @var Integer
     */
    const PAGESIZE = '100';

    /**
     * 开始日期
     *
     * @var String
     */
    private $aliStartDay = '';
    /**
     * 结束日期
     *
     * @var String
     */
    private $aliEndDay = '';

    public function & fetch($startTime, $endTime,$flag = false) {

       
    }


    public function getIncrementOrdersByPage($startTime,$endTime,$page=1,$page_size=1) {
        return false;//独立部署不需要
        $result = array();
        $this->aliStartDay = date('Y-m-d H:i:s', $startTime);
        $this->aliEndDay = date('Y-m-d H:i:s', $endTime);

        //获取 sessionKey , 如取不到，则直接返加空数组
        $this->sessionKey = $this->getSessionKey();
        if (empty($this->sessionKey)) {

            return $maxPageNum;
        }

        $method = 'taobao.refunds.receive.get';
        $params = array(
            'fields' => self::FIELDS, //self::FIELDS,
            'page_size' => $page_size,
            'start_modified' => $this->aliStartDay,
            'end_modified' => $this->aliEndDay,
            'status'=>'SUCCESS',
        );


        $curParams = $params;
        $curParams['page_no'] = $page;

        $apiResult = $this->apiRequest($method, $curParams);
        //var_dump($apiResult);
        if (is_array($apiResult) && !isset($apiResult['error_response']) && !empty($apiResult['refunds_receive_get_response']['refunds']['refund'])) {
            $totalNum = $apiResult['refunds_receive_get_response']['total_results'];
            $orders = array();
            if(isset($apiResult['refunds_receive_get_response']['refunds']['refund']['tid'])){
                $orders[] = $apiResult['refunds_receive_get_response']['refunds']['refund'];
            }else{
                foreach ($apiResult['refunds_receive_get_response']['refunds']['refund'] as $value) {
                    $orders[] = $value;
                }
            }

            $result = array('totalNum'=>$totalNum,'orders'=>$orders,'status'=>'succ');
        }elseif(isset($apiResult['error_response']) && isset($apiResult['error_response']['sub_code']) && $apiResult['error_response']['sub_code'] =='isp.remote-service-timeout'){
            $result = array('status'=>'timeout');
        }elseif(empty($apiResult)){
            $result = array('status'=>'timeout');
        }

        return $result;
    }


}
