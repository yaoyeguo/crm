<?php

class taocrm_mdl_middleware_member_analysis extends taocrm_mdl_middleware_model
{
    public $filterValue = array();
    public function searchOptions(){
        $parentOptions = parent::searchOptions();
        $childOptions = array(
            'member_uname'=>app::get('base')->_('用户名'),
            'member_name'=>app::get('base')->_('姓名'),
            'member_mobile'=>app::get('base')->_('手机号码'),
        );
        return $Options = array_merge($parentOptions,$childOptions);
    }

    /* (non-PHPdoc)
     * @see taocrm_middleware_model::_filter()
     */
    public function _filter($filter, $tableAlias = null, $baseWhere = null)
    {
         
        $this->filterValue = $filter;
        $_filter = array();

        //活动的标示ID
        if (isset($filter['VoidId']) && $filter['VoidId']) {
            $_filter['VoidId'] = $filter['VoidId'];
            unset($filter['VoidId']);
        }
         
        //店铺
        if (isset($filter['shop_id']) && $filter['shop_id']) {
            $_filter['shop_id'] = $filter['shop_id'];
            unset($filter['shop_id']);
        }
         

        //客户名称（未生成）
        if (isset($filter['member_uname']) && $filter['member_uname'] !== '') {
            $_filter['filter']['member_uname'] = $filter['member_uname'];
            //            $model = $this->db->getDataModel('taocrm', 'members');
            //            $memberIds = $model->getList
            //              $_filter['memberIdsList'] = $this->getMembersIdsList($filter['member_uname'], $_filter['shop_id']);
            unset($filter['member_uname']);
        }
        
        //姓名
        if (isset($filter['member_name']) && $filter['member_name'] !== '') {
            $_filter['filter']['member_name'] = $filter['member_name'];
            unset($filter['member_name']);
        }
        
        //手机号
        if (isset($filter['member_mobile']) && $filter['member_mobile'] !== '') {
            $_filter['filter']['member_mobile'] = $filter['member_mobile'];
            unset($filter['member_mobile']);
        }

        //订单总数
        if (isset($filter['total_orders']) && $filter['total_orders'] !== '') {
            $_filter['filter']['total_orders'] = $this->formatFilterInt($filter['total_orders'], 'total_orders');
            unset($filter['total_orders']);
        }

        //订单总金额
        if (isset($filter['total_amount']) && $filter['total_amount'] !== '') {
            $_filter['filter']['total_amount'] = $this->formatFilterInt($filter['total_amount'], 'total_amount');
            unset($filter['total_amount']);
        }

        //平均订单价
        if (isset($filter['total_per_amount']) && $filter['total_per_amount'] !== '') {
            $_filter['filter']['total_per_amount'] = $this->formatFilterInt($filter['total_per_amount'], 'total_per_amount');
            unset($filter['total_per_amount']);
        }

        //购买次数
        if (isset($filter['buy_freq']) && $filter['buy_freq'] !== '') {
            $_filter['filter']['buy_freq'] = $this->formatFilterInt($filter['buy_freq'], 'buy_freq');
            unset($filter['buy_freq']);
        }

        //购买月数
        if (isset($filter['buy_month']) && $filter['buy_month'] !== '') {
            $_filter['filter']['buy_month'] = $this->formatFilterInt($filter['buy_month'], 'buy_month');
            unset($filter['buy_month']);
        }

        //下载商品数量种数（未生成）
        if (isset($filter['buy_skus']) && $filter['buy_skus'] !== '') {
            $_filter['filter']['buy_skus'] = $this->formatFilterInt($filter['buy_skus'], 'buy_skus');
            unset($filter['buy_skus']);
        }

        //下单商品总数
        if (isset($filter['buy_products']) && $filter['buy_products'] !== '') {
            $_filter['filter']['buy_products'] = $this->formatFilterInt($filter['buy_products'], 'buy_products');
            unset($filter['buy_products']);
        }

        //成功的订单数
        if (isset($filter['finish_orders']) && $filter['finish_orders'] !== '') {
            $_filter['filter']['finish_orders'] = $this->formatFilterInt($filter['finish_orders'], 'finish_orders');
            unset($filter['finish_orders']);
        }

        //成功的订单金额
        if (isset($filter['finish_total_amount']) && $filter['finish_total_amount'] !== '') {
            $_filter['filter']['finish_total_amount'] = $this->formatFilterInt($filter['finish_total_amount'], 'finish_total_amount');
            unset($filter['finish_total_amount']);
        }

        //成功的平均订单价
        if (isset($filter['finish_per_amount']) && $filter['finish_per_amount'] !== '') {
            $_filter['filter']['finish_per_amount'] = $this->formatFilterInt($filter['finish_per_amount'], 'finish_per_amount');
            unset($filter['finish_per_amount']);
        }

        //未付款订单数
        if (isset($filter['unpay_orders']) && $filter['unpay_orders'] !== '') {
            $_filter['filter']['unpay_orders'] = $this->formatFilterInt($filter['unpay_orders'], 'unpay_orders');
            unset($filter['unpay_orders']);
        }

        //未付款的订单金额（未生成）
        if (isset($filter['unpay_amount']) && $filter['unpay_amount'] !== '') {
            $_filter['filter']['unpay_amount'] = $this->formatFilterInt($filter['unpay_amount'], 'unpay_amount');
            unset($filter['unpay_amount']);
        }

        //退款订单数
        if (isset($filter['refund_orders']) && $filter['refund_orders'] !== '') {
            $_filter['filter']['refund_orders'] = $this->formatFilterInt($filter['refund_orders'], 'refund_orders');
            unset($filter['refund_orders']);
        }

        //退款总金额
        if (isset($filter['refund_amount']) && $filter['refund_amount'] !== '') {
            $_filter['filter']['refund_amount'] = $this->formatFilterInt($filter['refund_amount'], 'refund_amount');
            unset($filter['refund_amount']);
        }

        //客户等级
        if (isset($filter['lv_id']) && $filter['lv_id'] !== '') {
            $_filter['filter']['lv_id'] = intval($filter['lv_id']);
            unset($filter['lv_id']);
        }

        //第一次下单时间
        if (isset($filter['first_buy_time']) && $filter['first_buy_time'] !== '') {
            $_filter['filter']['first_buy_time'] = $this->formatFilterDate($filter['first_buy_time'], 'first_buy_time');
            unset($filter['first_buy_time']);
        }

        //平均购买间隔（天）
        if (isset($filter['avg_buy_interval']) && $filter['avg_buy_interval'] !== '') {
            $_filter['filter']['avg_buy_interval'] = $this->formatFilterInt($filter['avg_buy_interval'], 'avg_buy_interval');
            unset($filter['avg_buy_interval']);
        }

        //最后下单时间
        if (isset($filter['last_buy_time']) && $filter['last_buy_time'] !== '') {
            $_filter['filter']['last_buy_time'] = $this->formatFilterDate($filter['last_buy_time'], 'last_buy_time');
            unset($filter['last_buy_time']);
        }

        //客户等级
        if (isset($filter['ext_lv_id']) && $filter['ext_lv_id'] !== '') {
            $_filter['filter']['lv_id'] = intval($filter['ext_lv_id']);
            unset($filter['ext_lv_id']);
        }

        //包名
        if (isset($filter['packetName']) && $filter['packetName']) {
            $_filter['packetName'] = $filter['packetName'];
            unset($filter['packetName']);
        }

        //方法名
        if (isset($filter['methodName']) && $filter['methodName']) {
            $_filter['methodName'] = $filter['methodName'];
            unset($filter['methodName']);
        }

        if (isset($filter['invalidType']) && $filter['invalidType']) {
            $_filter['invalidType'] = $filter['invalidType'];
            unset($filter['invalidType']);
        }
        return $_filter;
    }

    //    protected function getMembersIdsList($username, $shopId)
    //    {
    //        $page = isset($_GET['page']) ? trim($_GET['page']) : 0;
    //        $offset =
    //        $filter = array('uname|has' => $username, 'shop_id' => $shopId);
    //        $model = $this->db->getDataModel('taocrm', 'member_analysis');
    //        $result = $model->getList('member_id', $filter);
    //        $memberIds = array();
    //        foreach ($result as $memberId) {
    //            $memberIds[] = $memberId['member_id'];
    //        }
    //        if (empty($memberIds)) {
    //            $memberIds = -1;
    //        }
    //        return $memberIds;
    ////        $memberIdsStr = '';
    ////        if ($memberIds) {
    ////            $memberIdsStr = implode(',', $memberIds);
    ////        }
    ////        return $memberIdsStr;
    //    }

    public function formatFilterInt($value, $key)
    {
        $search = '_' . $key . '_search';
        $params = array();
        if (isset($this->filterValue[$search]) && $this->filterValue[$search]) {
            switch ($this->filterValue[$search]) {
                //等于
                case 'nequal':
                    //小于等于
                case 'sthan':
                    //大于等于
                case 'bthan':
                    $params['min_val'] = $value;
                    $params['sign'] = $this->filterValue[$search];
                    break;
                    //介于
                case 'between':
                    $from = $key .'_from';
                    $to = $key . '_to';
                    $params['min_val'] = isset($this->filterValue[$from]) ? max(0, $this->filterValue[$from]) : -1;
                    $params['max_val'] = isset($this->filterValue[$to]) ? max(0, $this->filterValue[$to]) : -1;
                    $params['sign'] = $this->filterValue[$search];
                    break;
            }
        }
        return $params;
    }

    public function formatFilterDate($value, $key)
    {
        $search = '_' . $key . '_search';
        $params = array();
        if (isset($this->filterValue[$search]) && $this->filterValue[$search]) {
            switch ($this->filterValue[$search]) {
                //早于
                case 'lthan':
                    //晚于
                case 'than':
                    $dtime = $this->filterValue['_DTIME_'];
                    $fromDate = isset($this->filterValue[$key]) ? $this->filterValue[$key] : '';
                    if ($fromDate) {
                        $fromH = isset($dtime['H'][$key]) ? $dtime['H'][$key] : '00';
                        $fromM = isset($dtime['M'][$key]) ? $dtime['M'][$key] : '00';
                        $params['min_val'] = $fromDate . ' ' . $fromH . ':' . $fromM . ':00';
                    }
                    $params['sign'] = $this->filterValue[$search];
                    break;
                case 'between':
                    $dtime = $this->filterValue['_DTIME_'];
                    $from = $key .'_from';
                    $to = $key . '_to';
                    $fromDate = isset($this->filterValue[$from]) ? $this->filterValue[$from] : '';
                    $toDate = isset($this->filterValue[$to]) ? $this->filterValue[$to] : '';
                    if ($fromDate) {
                        $fromH = isset($dtime['H'][$from]) ? $dtime['H'][$from] : '00';
                        $fromM = isset($dtime['M'][$from]) ? $dtime['M'][$from] : '00';
                        $params['min_val'] = $fromDate . ' ' . $fromH . ':' . $fromM . ':00';
                    }

                    if ($toDate) {
                        $toH = isset($dtime['H'][$to]) ? $dtime['H'][$to] : '00';
                        $toM = isset($dtime['M'][$to]) ? $dtime['M'][$to] : '00';
                        $params['max_val'] = $toDate . ' ' . $toH . ':' . $toM . ':00';
                    }
                    $params['sign'] = $this->filterValue[$search];
                    break;
            }
        }
        return $params;
    }

    function export($filter){
        return $this->db->exportMemberList($filter);
    }

}