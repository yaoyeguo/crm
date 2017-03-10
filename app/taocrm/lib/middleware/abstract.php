<?php
abstract class taocrm_middleware_abstract
{
    //数据来源是否是数据库
    const DATA_SOURCE = 'DATABASE';

    public static $singLeton = array();
    
    //缓存ID号
    protected  $CacheId = 0;
    
    //缓存ID创建时间
    protected $CacheIdCreateTime = 0;

    //服务接口URL
    protected  $memoServiceUrl = MEMO_SERVICE_URL;
    
    public $interfaceUrl = array();

    /*
     public function __construct($uri = '')
     {
     $this->setMemoServiceUrl($uri);
     }
     */
    public function setMemoServiceUrl($uri = '')
    {
        if ($uri) {
            $this->memoServiceUrl = $uri;
        }elseif(defined('MEMO_SERVICE_URL')) {
            $this->memoServiceUrl = MEMO_SERVICE_URL;
        }

        $urlList = array(MEMO_SERVICE_URL,MEMO_SERVICE_URL_back);

        $this->memoServiceUrl = $urlList[((substr(DB_NAME, -1))%2)];

        $this->setInterfaceUrl();
    }

    protected function setInterfaceUrl()
    {
        //获取一段时间内 每月 订单数及客户数
        $this->interfaceUrl['SdbEcorderOrders']['OrderMemberCountByMonth'] = $this->memoServiceUrl . '/SdbEcorderOrders/OrderMemberCountByMonth';
        //获取一段时间内每天的订单总数 订单客户数 销售金额
        $this->interfaceUrl['SdbEcorderOrders']['OrderMemberAmountCountByDay'] = $this->memoServiceUrl . '/SdbEcorderOrders/OrderMemberAmountCountByDay';
        //获取一段时间内的客户购买金额排名
        $this->interfaceUrl['SdbEcorderOrders']['TopAmountMemberIdByTime'] = $this->memoServiceUrl . '/SdbEcorderOrders/TopAmountMemberIdByTime';
        //获取一段时间内客户购买频次
        $this->interfaceUrl['SdbEcorderOrders']['BuyFreqByTime'] = $this->memoServiceUrl . '/SdbEcorderOrders/BuyFreqByTime';
        //获取一段时间内热销商品排名
        $this->interfaceUrl['SdbEcorderOrderItem']['TopSale'] = $this->memoServiceUrl . '/SdbEcorderOrderItem/TopSale';
        //获取购物篮XY商品推荐
        $this->interfaceUrl['SdbEcorderOrderItem']['TopOrderItemXY'] = $this->memoServiceUrl . '/SdbEcorderOrderItem/TopOrderItemXY';
        //获取RFM模型报表
        $this->interfaceUrl['Report']['RFM'] = $this->memoServiceUrl . '/Report/RFM';
        //获取RF模型报表
        $this->interfaceUrl['Report']['RF'] = $this->memoServiceUrl . '/Report/RF';
        //根据时间获取新老客户统计信息
        $this->interfaceUrl['ShopMemberAnalysis']['NewOldMemberAnalysis'] = $this->memoServiceUrl . '/ShopMemberAnalysis/NewOldMemberAnalysis';
        //根据时间类型及分组类型获取一组新老客户统计信息
        $this->interfaceUrl['ShopMemberAnalysis']['MemberAnalysisByNewOldAndTime'] = $this->memoServiceUrl . '/ShopMemberAnalysis/MemberAnalysisByNewOldAndTime';
        //自定义客户分组
        $this->interfaceUrl['ShopMemberAnalysis']['SearchMemberAnalysisList'] = $this->memoServiceUrl . '/ShopMemberAnalysis/SearchMemberAnalysisList';
        //根据地域获取订单统计数据                    销售报表页 地域分析
        $this->interfaceUrl['Report']['OrderReportModelByState'] = $this->memoServiceUrl . '/Report/OrderReportModelByState';
        ////根据时间、地域及付款状态获取客户分析信息           销售报表 地域分析 放大镜
        $this->interfaceUrl['ShopMemberAnalysis']['MemberAnalysisByTimeStateAndOrderStatus'] = $this->memoServiceUrl . '/ShopMemberAnalysis/MemberAnalysisByTimeStateAndOrderStatus';
        //根据时间类型及分组类型获取一组新老客户统计信息                    销售报表页 新老客户
        $this->interfaceUrl['ShopMemberAnalysis']['NewOldMemberAnalysisByTimeType'] = $this->memoServiceUrl . '/ShopMemberAnalysis/NewOldMemberAnalysisByTimeType';
        //根据RFM值获取客户分析信息                   销售报表页 RFM分析 放大镜
        $this->interfaceUrl['ShopMemberAnalysis']['AnalysisByRFM'] = $this->memoServiceUrl . '/ShopMemberAnalysis/AnalysisByRFM';
        //根据RF值获取客户分析信息                      销售报表页 RF分析 放大镜
        $this->interfaceUrl['ShopMemberAnalysis']['AnalysisByRF'] = $this->memoServiceUrl . '/ShopMemberAnalysis/AnalysisByRF';
        //销售报表页 销售漏斗
        $this->interfaceUrl['SdbEcorderOrders']['ListByOrderStatus'] = $this->memoServiceUrl . '/SdbEcorderOrders/ListByOrderStatus';
        //获取店铺客户数
        $this->interfaceUrl['ShopMemberAnalysis']['MemberCountByShopId'] = $this->memoServiceUrl . '/ShopMemberAnalysis/MemberCountByShopId';
        //获取客户等级统计信息
        $this->interfaceUrl['ShopMemberAnalysis']['MemberInfoBySyslv'] = $this->memoServiceUrl . '/ShopMemberAnalysis/MemberInfoBySyslv';
        //根据客户ID获取分析信息
        $this->interfaceUrl['ShopMemberAnalysis']['AnalysisByMemberId'] = $this->memoServiceUrl . '/ShopMemberAnalysis/AnalysisByMemberId';
        //根据购买频次获取客户分析信息
        //销售报表页 购买频次 放大镜
        //销售报表页 下单次数 放大镜
        $this->interfaceUrl['ShopMemberAnalysis']['AnalysisByFinishOrderCount'] = $this->memoServiceUrl . '/ShopMemberAnalysis/AnalysisByFinishOrderCount';

        //根据小时时间段及付款状态获取客户分析信息
        //销售报表 购买时段 放大镜
        $this->interfaceUrl['ShopMemberAnalysis']['MemberAnalysisByTimeHourAndOrderStatus'] = $this->memoServiceUrl . '/ShopMemberAnalysis/MemberAnalysisByTimeHourAndOrderStatus';

        //根据商品XY获取客户统计信息  ---- 商品分析 关联商品 放大镜  --- 商品分析 购物篮分析 放大镜
        $this->interfaceUrl['ShopMemberAnalysis']['AnalysisByXY'] = $this->memoServiceUrl . '/ShopMemberAnalysis/AnalysisByXY';

        //根据时间段及时间获取订单统计数据
        //销售报表页 销售统计
        //销售报表页 订单状态
        $this->interfaceUrl['Report']['OrderReportModelByTimeAndType'] = $this->memoServiceUrl . '/Report/OrderReportModelByTimeAndType';

        //销售报表页 销售统计 放大镜                 销售报表页 订单状态 放大镜                销售报表页 销售漏斗 放大镜
        $this->interfaceUrl['ShopMemberAnalysis']['MemberAnalysisByTimeAndOrderStatus'] = $this->memoServiceUrl . '/ShopMemberAnalysis/MemberAnalysisByTimeAndOrderStatus';

        //获取每个小时时间段订单统计数据
        //销售报表页 购买时段
        $this->interfaceUrl['Report']['OrderReportModelByHour'] = $this->memoServiceUrl . '/Report/OrderReportModelByHour';

        //购买频次
        $this->interfaceUrl['SdbEcorderOrders']['BuyFreqByTime'] = $this->memoServiceUrl . '/SdbEcorderOrders/BuyFreqByTime';

        //加载数据库到内存
        $this->interfaceUrl['DBIndexManager']['addDBIndex'] = $this->memoServiceUrl . '/DBIndexManager/addDBIndex';
        //获取店铺是否存在 "true" "false"
        $this->interfaceUrl['DBIndexManager']['isExistsShop'] = $this->memoServiceUrl . '/DBIndexManager/isExistsShop';
        //删除数据库索引 "true" "false"
        $this->interfaceUrl['DBIndexManager']['removeDBIndex'] = $this->memoServiceUrl . '/DBIndexManager/removeDBIndex';
        //添加订单
        $this->interfaceUrl['DBIndexManager']['addOrder'] = $this->memoServiceUrl . '/DBIndexManager/addOrder';
        //获取单个数据库索引状态 "NULL" "READY" "LOADING"
        $this->interfaceUrl['DBIndexManager']['DbIndexState'] = $this->memoServiceUrl . '/DBIndexManager/DbIndexState';
        //添加订单明细
        $this->interfaceUrl['DBIndexManager']['addOrderItem'] = $this->memoServiceUrl . '/DBIndexManager/addOrderItem';
        //获取数据所有店铺的订单及客户数
        $this->interfaceUrl['DBIndexManager']['DBAllShopInfo'] = $this->memoServiceUrl . '/DBIndexManager/DBAllShopInfo';
        //创建短信活动 人数预览
        $this->interfaceUrl['ShopMemberAnalysis']['SMSTaskInfo'] = $this->memoServiceUrl . '/ShopMemberAnalysis/SMSTaskInfo';
        //创建短信活动
        $this->interfaceUrl['ShopMemberAnalysis']['createSMSTaskInfo'] = $this->memoServiceUrl . '/ShopMemberAnalysis/createSMSTaskInfo';
        //更新订单
        $this->interfaceUrl['DBIndexManager']['updateOrder'] = $this->memoServiceUrl . '/DBIndexManager/updateOrder';
        //更新订单明细
        $this->interfaceUrl['DBIndexManager']['updateOrderItem'] = $this->memoServiceUrl . '/DBIndexManager/updateOrderItem';
        //获取活动预览信息（邮件、短信）
        $this->interfaceUrl['ShopMemberAnalysis']['TaskInfo'] = $this->memoServiceUrl . '/ShopMemberAnalysis/TaskInfo';
        //创建营销活动（邮件、短信）
        $this->interfaceUrl['ShopMemberAnalysis']['createTask'] = $this->memoServiceUrl . '/ShopMemberAnalysis/createTask';
        //根据用户ID创建营销活动
        $this->interfaceUrl['ShopMemberAnalysis']['createTaskByMemberIdList'] = $this->memoServiceUrl . '/ShopMemberAnalysis/createTaskByMemberIdList';
        //根据放大镜缓存创建营销活动
        $this->interfaceUrl['ShopMemberAnalysis']['createTaskByCahceId'] = $this->memoServiceUrl . '/ShopMemberAnalysis/createTaskByCahceId';
        //根据放大镜查询缓查询发送客户数量
        $this->interfaceUrl['ShopMemberAnalysis']['SearchMemberAnalysisCountByCacheId'] = $this->memoServiceUrl . '/ShopMemberAnalysis/SearchMemberAnalysisCountByCacheId';
        //根据放大镜缓存ID查询活动预览信息
        $this->interfaceUrl['ShopMemberAnalysis']['TaskInfoByCacheId'] = $this->memoServiceUrl . '/ShopMemberAnalysis/TaskInfoByCacheId';
        //根据用户ID创建营销活动人数预览
        $this->interfaceUrl['ShopMemberAnalysis']['TaskInfoByMemberId'] = $this->memoServiceUrl . '/ShopMemberAnalysis/TaskInfoByMemberId';
        //根据客户ID（字符串）获得客户数据
        $this->interfaceUrl['ShopMemberAnalysis']['MemberAnalysisByMemberId'] = $this->memoServiceUrl . '/ShopMemberAnalysis/MemberAnalysisByMemberId';
        //添加店铺ID
        $this->interfaceUrl['DBIndexManager']['addShop'] = $this->memoServiceUrl . '/DBIndexManager/addShop';
        //营销评估活动详情
        $this->interfaceUrl['ShopMemberAnalysis']['ActiveTotalInfo'] = $this->memoServiceUrl . '/ShopMemberAnalysis/ActiveTotalInfo';
        //营销评估活动客户详情
        $this->interfaceUrl['ShopMemberAnalysis']['ActiveMemberInfo'] = $this->memoServiceUrl . '/ShopMemberAnalysis/ActiveMemberInfo';
        //更新客户
        $this->interfaceUrl['DBIndexManager']['updateMember'] = $this->memoServiceUrl . '/DBIndexManager/updateMember';
    }

    public function getMemoServiceUrl()
    {
        return $this->memoServiceUrl;
    }

    /**
     * 获得通信URI
     * @param string $packetName 包名称
     * @param string $methodName 方法名称
     */
    public function getInterfaceUrl($packetName, $methodName)
    {
        if (isset($this->interfaceUrl[$packetName][$methodName])) {
            return $this->interfaceUrl[$packetName][$methodName];
        }
        return null;
    }

    /**
     * 获得数据库名称
     */
    protected function getDbName()
    {
        $dbName = DB_NAME;;
        return $dbName;
    }

    /**
     * 获得数据库密码
     */
    protected function getDbPasswd()
    {
        $password = DB_PASSWORD;
        return $password;
    }

    /**
     * 获得用户名
     */
    protected function getUserName()
    {
        $userName = DB_USER;
        return $userName;
    }

    protected function getHostName()
    {
        $dbHost = DB_HOST;
        return $dbHost;
    }

    /**
     *
     * 获得客户过滤参数
     * @param unknown_type $params
     */
    public function getMemberPackFilter($data)
    {
        $finalFilter = $this->formatFilter($data['filter']);
        //数字类型的筛选条件
        $num_field_arr = array(
            'totalOrders','finishOrder','totalAmount','totalPerAmount','buyFreq',
            'buyMonth','avgBuyInterval','buyProducts','finishTotalAmount',
            'finishPerAmount','unpayOrders','refundOrders','refundAmount',
            'createTime','maxCreateTime','minCreateTime',
            'points','birthday','unpayAmount','buyGoodsCount',
            'activeTimes','activeBuyTimes',
        );
        foreach ($num_field_arr as $v) {
            if(isset($finalFilter[$v])){
                $params[$v] = array(
                $finalFilter[$v]['p1'],
                $finalFilter[$v]['p2'],
                $finalFilter[$v]['sign']
                );
            }else{
                $params[$v] = array(-1, -1, 'false');
            }
        }
        
        $params['sex'] = (string)$finalFilter['sex'];
        $params['tagCalculation'] = (string)$finalFilter['tag_calculation'];
        $params['tagIdPlus'] = (string)$finalFilter['tags'];
        //字符串类型的筛选条件
        $params['shopEvaluation'] = (string)$finalFilter['shopEvaluation'];
        //客户等级
        $params['sysLv'] = max(0, intval($finalFilter['sysLv'])) != 0 ? intval($finalFilter['sysLv']) : -1;
        //淘宝等级
        $params['taobaoLv'] = (string)$finalFilter['taobaoLv'];
        //购买商品ID
        $params['buyGoods'] = (string)$finalFilter['buyGoods'];//11,22,33逗号分割
        //省份
        $params['state'] = (string)$finalFilter['state'];//11,22,,33
        $params = $this->unsettMemberPackFilterParams($params);
        $post = array();
        foreach($params as $name=>$arr){
            if(is_array($arr)){
                foreach($arr as $k=>$val){
                    if($k == 2){
                        $new_name = $name.'Equals';
                    }else{
                        $new_name = $name.($k+1);
                    }
                    $post[$new_name] = $val;
                }
            }else{
                $post[$name] = $arr;
            }
        }
        $post['shopId'] = $data['shop_id'];
        $post['dbName'] = $this->getDbName();
        $pageIndex = 1;
        if (isset($data['pageIndex'])) {
            $pageIndex = max(intval($data['pageIndex']), $pageIndex);
        }
        
        /*
        $pageSize = 20;
        if (isset($data['pageSize'])) {
            $pageSize = max(intval($data['pageSize']), $pageSize);
        }
        */
        $pageSize = $data['pageSize'];
        
        $post['pageIndex'] = $pageIndex;
        $post['pageSize'] = $pageSize;

        //排除条件
        if(isset($data['exclude_filter']) && is_array($data['exclude_filter'])){
            foreach($data['exclude_filter'] as $k=>$v){
                if($v == 2){
                    $post['recentSentHours'] = $data['exclude_hours'];
                }else if($v == 3){
                    $post['tagId'] = $data['exclude_tag_id'];
                }else if($v == 4){
                    $post['previousTaskId'] = $data['exclude_active_id'];
                }
            }
        }

        //单独处理会员自定义属性
        if(isset($data['filter']['prop_val']) && $data['filter']['prop_val']){
            foreach($data['filter']['prop_val'] as $k=>$v){
                if($v['sign']=='between'){
                    $post['memberAttr'.($k+1).'1'] = (string)$v['min_val'];
                    $post['memberAttr'.($k+1).'2'] = (string)$v['max_val'];
                    $post['memberAttr'.($k+1).'Equals'] = 'false';
                }elseif($v['sign']=='sthan' or $v['sign']=='lthan'){
                    $post['memberAttr'.($k+1).'1'] = '';
                    $post['memberAttr'.($k+1).'2'] = (string)$v['min_val'];
                    $post['memberAttr'.($k+1).'Equals'] = 'false';
                }elseif($v['sign']=='bthan' or $v['sign']=='than'){
                    $post['memberAttr'.($k+1).'1'] = (string)$v['min_val'];
                    $post['memberAttr'.($k+1).'2'] = '';
                    $post['memberAttr'.($k+1).'Equals'] = 'false';
                }elseif($v['min_val']){
                    $post['memberAttr'.($k+1).'1'] = (string)$v['min_val'];
                    $post['memberAttr'.($k+1).'2'] = (string)$v['min_val'];
                    $post['memberAttr'.($k+1).'Equals'] = 'true';
                }
            }
        }
        
        return $post;
    }

    public function getFxMemberPackFilter($data)
    {
        $finalFilter = $this->formatFilter($data);
        //数字类型的筛选条件
        $num_field_arr = array(
            'totalOrders','totalAmount','createTime'
            );
            foreach ($num_field_arr as $v) {
                if(isset($finalFilter[$v])){
                    $params[$v] = array(
                    $finalFilter[$v]['p1'],
                    $finalFilter[$v]['p2'],
                    $finalFilter[$v]['sign']
                    );
                }else{
                    $params[$v] = array(-1, -1, 'false');
                }
            }
            //购买商品ID
            $params['buyGoods'] = (string)$finalFilter['buyGoods'];//11,22,33逗号分割
            //省份
            $params['state'] = (string)$finalFilter['state'];//11,22,,33
            $params['payStatus'] = $finalFilter['payStatus'];
            $params['agentName'] = $finalFilter['agentName'];
            $params = $this->unsettMemberPackFilterParams($params);
            $post = array();
            foreach($params as $name=>$arr){
                if(is_array($arr)){
                    foreach($arr as $k=>$val){
                        if($k == 2){
                            $new_name = $name.'Equals';
                        }else{
                            $new_name = $name.($k+1);
                        }
                        $post[$new_name] = $val;
                    }
                }else{
                    $post[$name] = $arr;
                }
            }
            $post['shopId'] = $data['shop_id'];
            $post['dbName'] = $this->getDbName();
            $pageIndex = 1;
            if (isset($data['pageIndex'])) {
                $pageIndex = max(intval($data['pageIndex']), $pageIndex);
            }
            $pageSize = 20;
            if (isset($data['pageSize'])) {
                $pageSize = max(intval($data['pageSize']), $pageSize);
            }
            $post['pageIndex'] = $pageIndex;
            $post['pageSize'] = $pageSize;
            return $post;
    }

    public function getReportMemberPackFilter($data)
    {
         
        //        echo "<pre>";
        //        print_r($data);
        $finalFilter = $this->formatFilter($data['filter']);
        //        echo "<pre>";
        //        print_r($finalFilter);
         
        //数字类型的筛选条件
        $num_field_arr = array(
            'finishOrder','totalAmount','totalPerAmount','buyFreq',
            'buyProducts',
            'finishPerAmount','createTime','points','birthday'
            );
            foreach ($num_field_arr as $v) {
                if(isset($finalFilter[$v])){
                    $params[$v] = array(
                    $finalFilter[$v]['p1'],
                    $finalFilter[$v]['p2'],
                    $finalFilter[$v]['sign']
                    );
                }else{
                    $params[$v] = array(-1, -1, 'false');
                }
            }
            //字符串类型的筛选条件
            $params['shopEvaluation'] = (string)$finalFilter['shopEvaluation'];
            //客户等级
            $params['sysLv'] = max(0, intval($finalFilter['sysLv'])) != 0 ? intval($finalFilter['sysLv']) : -1;
            //淘宝等级
            $params['taobaoLv'] = (string)$finalFilter['taobaoLv'];
            //购买商品ID
            $params['buyGoods'] = (string)$finalFilter['buyGoods'];//11,22,33逗号分割
            //省份
            $params['state'] = (string)$finalFilter['state'];//11,22,,33
            $params = $this->unsettMemberPackFilterParams($params);
            $post = array();
            foreach($params as $name=>$arr){
                if(is_array($arr)){
                    foreach($arr as $k=>$val){
                        if($k == 2){
                            $new_name = $name.'Equals';
                        }else{
                            $new_name = $name.($k+1);
                        }
                        $post[$new_name] = $val;
                    }
                }else{
                    $post[$name] = $arr;
                }
            }
            $post['shopId'] = $data['shop_id'];
            $post['dbName'] = $this->getDbName();
            return $post;
    }

    protected function unsettMemberPackFilterParams($params)
    {
        //        if (isset($params['maxCreateTime'][2])) {
        //            unset($params['maxCreateTime'][2]);
        //        }
        //        if (isset($params['minCreateTime'][2])) {
        //            unset($params['minCreateTime'][2]);
        //        }
        if (isset($params['birthday'][2])) {
            unset($params['birthday'][2]);
        }
        return $params;
    }

    public function packFilter($shopId,$filter)
    {
        //转换筛选条件的格式
        $final_filter = $this->formatFilter($filter);
        //echo('<pre>');var_dump($final_filter);

        //数字类型的筛选条件
        $num_field_arr = array(
            'totalOrders','finishOrder','totalAmount','totalPerAmount','buyFreq',
            'buyMonth','avgBuyInterval','buySkus','buyProducts','finishTotalAmount',
            'finishPerAmount','unpayOrders','refundOrders','refundAmount',
            'maxCreateTime','minCreateTime','points','birthday','buySkus','unpayAmount','buyGoodsCount'
            );
            foreach($num_field_arr as $v){
                if(isset($final_filter[$v])){
                    $params[$v] = array(
                    $final_filter[$v]['p1'],
                    $final_filter[$v]['p2'],
                    $final_filter[$v]['sign']
                    );
                }else{
                    $params[$v] = array(-1, -1, 'false');
                    //var_dump($v);
                }
            }
            //echo('<pre>');var_dump($final_filter);

            //字符串类型的筛选条件
            $params['shopEvaluation'] = (string)$final_filter['shopEvaluation'];
            //客户等级
            $params['sysLv'] = max(0, intval($final_filter['sysLv'])) != 0 ? intval($final_filter['sysLv']) : -1;
            $params['taobaoLv'] = (string)$final_filter['taobaoLv'];
            $params['buyGoods'] = (string)$final_filter['buyGoods'];//11,22,33逗号分割
            $params['state'] = (string)$final_filter['state'];//11,22,,33
            //客户名称
            $params['memberUname'] = (string)$final_filter['memberUname'];

            /**
             //订单状态
             $params['orderStatus'] = (string)$final_filter['orderStatus'];
             //订单统计单位
             $params['orderCountBy'] = (string)$final_filter['orderCountBy'];
             //订单起始时间
             $params['orderStartTime'] = intval($params['orderStartTime']);
             //订单结束时间
             $params['orderEndTime'] = intval($final_filter['orderEndTime']);
             **/

            unset(
            $params['maxCreateTime'][2],
            $params['minCreateTime'][2],
            $params['birthday'][2]
            );
            $post = array();
            foreach($params as $name=>$arr){
                if(is_array($arr)){
                    foreach($arr as $k=>$val){
                        if($k == 2){
                            $new_name = $name.'Equals';
                        }else{
                            $new_name = $name.($k+1);
                        }

                        $post[$new_name] = $val;
                    }
                }else{
                    $post[$name] = $arr;
                }
            }
            $post['shopId'] = md5(DB_NAME.$shopId);
            //echo('<hr/><pre>');var_export($post);
            return $post;
    }

    protected function formatFilter($filter)
    {
        $params = array();
        if(!$filter) $filter = array();
        foreach ($filter as $k => $v) {
            switch ($k) {
                //订单总数
                case 'total_orders':
                    $params['totalOrders'] = $this->formatFilterInteger($v);
                    break;
                    //购买次数（成功的订单数）
                case 'finish_orders':
                    $params['finishOrder'] = $this->formatFilterInteger($v);
                    break;
                    //订单总金额
                case 'total_amount':
                    $params['totalAmount'] = $this->formatFilterInteger($v);
                    break;
                    //购买月数
                case 'buy_month':
                    $params['buyMonth'] = $this->formatFilterInteger($v);
                    break;
                    //成功的订单金额
                case 'finish_total_amount':
                    $params['finishTotalAmount'] = $this->formatFilterInteger($v);
                    break;
                    //未付款的订单数
                case 'unpay_orders':
                    $params['unpayOrders'] = $this->formatFilterInteger($v);
                    break;
                    //退款总金额
                case 'refund_amount':
                    $params['refundAmount'] = $this->formatFilterInteger($v);
                    break;
                    //平均购买周期(天)
                case 'buy_freq':
                    $params['buyFreq'] = $this->formatFilterInteger($v);
                    break;
                    //平均订单价
                case 'total_per_amount':
                    $params['totalPerAmount'] = $this->formatFilterInteger($v);
                    break;
                    //平均购买间隔
                case 'avg_buy_interval' :
                    $params['avgBuyInterval'] = $this->formatFilterInteger($v);
                    break;
                    //购买商品总数(下单次数)
                case 'buy_products':
                    $params['buyProducts'] = $this->formatFilterInteger($v);
                    break;
                    //成功的平均订单价
                case 'finish_per_amount':
                    $params['finishPerAmount'] = $this->formatFilterInteger($v);
                    break;
                    //退款订单数(refund_orders)
                case 'refund_orders':
                    $params['refundOrders'] = $this->formatFilterInteger($v);
                    break;
                    //第一次下单时间
                     
                case 'first_buy_time':
                    $params['minCreateTime'] = $this->formatFilterInteger($this->covertUnixTime($v));
                    break;
                    //最后下单日期
                case 'last_buy_time':
                    $params['maxCreateTime'] = $this->formatFilterInteger($this->covertUnixTime($v));
                    break;
                    //购买时间（最近多少时间，即最后下单时间
                case 'create_time':
                    $params['createTime'] = $this->formatFilterInteger($this->covertUnixTime($v));
                    break;
                    /*
                     case 'good_buy_date':
                     if ($v) {
                     $params['maxCreateTime'] = $this->formatFilterInteger($this->getMaxOrderTimeByInt($v));
                     }
                     break;
                     */
                    //支付状态
                case 'pay_status':
                    $params['payStatus'] = $v;
                    break;
                    //分销商
                case 'agent_name':
                    $params['agentName'] = (string)$v;
                    break;
                    //积分范围
                case 'points':
                    $params['points'] = $this->formatFilterInteger($v);
                    break;
                    //客户评价
                case 'evaluation' :
                case 'shop_evaluation':
                    $params['shopEvaluation'] = (string)$v;
                    break;
                    //生日范围
                case 'birthday':
                    $params['birthday'] = $this->formatFilterInteger($this->covertUnixTime($v));
                    break;
                    //客户等级
                case 'lv_id' :
                    $params['sysLv'] = max(0, intval($v)) != 0 ? intval($v) : -1;
                    break;
                    //淘宝等级
                case 'taobaolv_id' : $params['taobaoLv'] = $v;break;
                //商品数量种数：（未生成）
                case 'buy_skus' :
                    //                    $params['buySkus'] = max(0, intval($v)) != 0 ? intval($v) : -1;
                    $params['buyGoodsCount'] = $this->formatFilterInteger($v);
                    break;
                    //未付款订单金额（未生成）
                case 'unpay_amount':
                    $params['unpayAmount'] = $this->formatFilterInteger($v);
                    break;
                    //客户名称
                case 'member_uname':
                    $params['memberUname'] = (string)($v);
                    break;
                    //订单状态
                case 'order_status':
                    $checkArray = array('unship', 'ship', 'paid', 'finish', 'unpaid', 'dead', 'all');
                    if (in_array($v, $$checkArray)) {
                        $params['orderStatus'] = $v;
                    }
                    else {
                        $params['orderStatus'] = '';
                    }
                    break;
                    //订单统计单位
                case 'order_count_by':
                    $checkArray = array('date', 'week', 'month', 'year');
                    if (in_array($v, $checkArray)) {
                        $params['orderCountBy'] = $v;
                    }
                    else {
                        $params['orderCountBy'] = '';
                    }
                    break;
                    //订单起始时间
                case 'order_start_time':
                    $params['orderStartTime'] = max(0, intval($v));
                    break;
                    //订单结束时间
                case 'order_end_time':
                    $params['orderEndTime'] = max(0, intval($v));
                    break;
                case 'regions_id' : $params['state'] = $this->formatFilterExplode($v); break;;
                case 'goods_id' : $params['buyGoods'] = $this->formatFilterExplode($v); break;
                
                case 'active_times':
                    $params['activeTimes'] = $this->formatFilterInteger($v);
                    break;
                    
                case 'active_buy_times':
                    $params['activeBuyTimes'] = $this->formatFilterInteger($v);
                    break;
                
                default:
                    $params[$k] = $v;
            }
        }
        return $params;
    }

    protected function formatFilterInteger($data)
    {
        $params = array(
            'p1' => -1,         //参数1
            'p2' => -1,         //参数2
            'sign' => 'false',    //运算符，为true时表示=
        );
        if ($data['sign'] != '') {
            switch ($data['sign']) {
                //等于
                case 'nequal':
                    $params['p1'] = $data['min_val'];
                    $params['p2'] = $data['min_val'];
                    //$params['p2'] = -1;
                    $params['sign'] = 'true';
                    break;
                    //小于等于
                case 'sthan':
                    $params['p1'] = -1;
                    $params['p2'] = $data['min_val'];
                    //                    $params['sign'] = 'false';
                    $params['sign'] = 'true';
                    break;
                    //大于等于
                case 'bthan':
                    $params['p1'] = $data['min_val'];
                    $params['p2'] = -1;
                    //$params['sign'] = 'false';
                    $params['sign'] = 'true';
                    break;
                    //介于
                case 'between':
                    $params['p1'] = $data['min_val'];
                    $params['p2'] = $data['max_val'];
                    $params['sign'] = 'false';
                    break;
            }
        }
        return $params;
    }

    protected function formatFilterExplode($value)
    {
        $string = '';
        if (is_array($value) && $value) {
            $string = implode(',', $value);
        }
        return $string;
    }

    protected function formatFilterSelect($value)
    {
        $params = array(
            'p1' => -1,         //参数1
            'p2' => -1,         //参数2
            'sign' => 'false',    //运算符，为true时表示=
        );
        if ($value !== '') {
            $params['p1'] = $value;
            $params['p2'] = -1;
            $params['sign'] = 'true';
        }
        return $params;
    }

    protected function covertUnixTime($data)
    {
        $params = array();
        $params['sign'] = '';
        if ($data['sign'] != '') {
            switch ($data['sign']) {
                //等于
                case 'nequal':
                    $params['sign'] = 'nequal';
                    $params['min_val'] = $this->formatTime($data['min_val']);
                    $params['max_val'] = '';
                    break;
                    //晚于(等于)
                case 'than':
                    $params['sign'] = 'bthan';
                    $params['min_val'] = $this->formatTime($data['min_val']);
                    $params['max_val'] = '';
                    break;
                    //早于(小于)
                case 'lthan':
                    $params['sign'] = 'sthan';
                    $params['min_val'] = $this->formatTime($data['min_val']);
                    $params['max_val'] = '';
                    break;
                    //介于
                case 'between':
                    $params['sign'] = 'between';
                    $params['min_val'] = $this->formatTime($data['min_val']);
                    $params['max_val'] = $this->formatTime($data['max_val']);
                    break;
            }
        }
        return $params;
    }

    protected function formatTime($date)
    {
        if(strstr($date, '-')){
            return strtotime($date);
        }else{
            return floatval($date);
        }
    }

    protected function getMaxOrderTimeByInt($int)
    {
        $time = '';
        if ($int) {
            $time = time() - intval($int) * 86400;
        }
        $data = array('sign' => 'bthan', 'min_val' => $time);
        return $data;
    }

    /**
     * 设置缓存ID
     */
    public function setCacheId($cacheId)
    {
        $this->CacheId = $cacheId;
    }

    public function setCacheIdCreateTime()
    {
        $this->CacheIdCreateTime = time();
    }

    /**
     * 获得缓存ID
     */
    public function getCacheId()
    {
        $cacheId = 0;
        if ($this->CacheId > 0) {
            $cacheId = $this->CacheId;
        }
        return $cacheId;
    }

    /**
     * 获得缓存创建时间
     */
    public function getCacheIdCreateTime()
    {
        $time = 0;
        if ($this->CacheIdCreateTime > 0) {
            $time = $this->CacheIdCreateTime;
        }
        return $time;
    }

    public function getCacheInfo()
    {
        $cacheId = $this->getCacheId();
        $time = $this->getCacheIdCreateTime();
        return array('CacheId' => $cacheId, 'CacheIdCreateTime' => $time);
    }

    protected function getMembersIdsStr($filter, $shopId, $offset, $limit)
    {
        /*
         $sql = "SELECT
         DISTINCT(A.member_id)
         FROM
         `sdb_taocrm_member_analysis` AS A
         LEFT JOIN sdb_taocrm_members AS B ON A.member_id = B.member_id
         WHERE
         shop_id = '{$shopId}'
         AND B.uname LIKE '%{$username}%'
         LIMIT {$offset}, {$limit}
         ";
         */
        $filter_sql = '';
        if(isset($filter['member_uname'])) $filter_sql .= " A.uname LIKE '".$filter['member_uname']."%' ";
        if(isset($filter['member_name'])) $filter_sql .= " A.name LIKE '".$filter['member_name']."%' ";
        if(isset($filter['member_mobile'])) $filter_sql .= " A.mobile LIKE '".$filter['member_mobile']."%' ";
        
        $sql = "SELECT
                  DISTINCT(A.member_id)
                FROM
                  sdb_taocrm_members AS A
                LEFT JOIN sdb_taocrm_member_analysis AS B ON A.member_id = B.member_id
                WHERE
                    ".$filter_sql." 
                    AND  B.shop_id = '{$shopId}'
                LIMIT {$offset}, {$limit}
               ";
         
        //$filter = array('uname|has' => $username, 'shop_id' => $shopId);
        $model = $this->getDataModel('taocrm', 'member_analysis');
        $result = $model->db->select($sql);
        $memberIdsStr = '';
        foreach($result as $memberId){
            $memberIdsStr .= $memberId['member_id'] .',';
        }
        if($memberIdsStr){
            $memberIdsStr = trim($memberIdsStr, ',');
        }
        return $memberIdsStr;
    }

    protected function getMembersIdsCount($username, $shopId)
    {
        /*
         $sql = "SELECT
         count(DISTINCT(A.member_id)) as _count
         FROM
         `sdb_taocrm_member_analysis` AS A
         LEFT JOIN sdb_taocrm_members AS B ON A.member_id = B.member_id
         WHERE
         shop_id = '{$shopId}'
         AND B.uname LIKE '%{$username}%'";
         */
        $sql = "SELECT
                  count(DISTINCT(A.member_id)) as _count
                FROM
                  `sdb_taocrm_members` AS A
                LEFT JOIN sdb_taocrm_member_analysis AS B ON A.member_id = B.member_id
                WHERE
                  A.uname LIKE '%{$username}%'
                AND B.shop_id = '{$shopId}' ";
        $model = $this->getDataModel('taocrm', 'member_analysis');
        $result = $model->db->select($sql);
        return $result[0]['_count'];
    }

    /**
     * 实例化数据模式
     */
    public function getDataModel($appName, $objectName)
    {
        if (self::$singLeton[$appName][$objectName] == '') {
            self::$singLeton[$appName][$objectName] = &app::get($appName)->model($objectName);
        }
        return self::$singLeton[$appName][$objectName];
    }

    /**
     * 获得活动客户Id
     */
    protected function getResourceActive($data)
    {
        $filter = $data['filter'];
        $model = $this->getDataModel('market', 'active_member');
        $limit = $data['pageSize'];
        $offset = ($data['pageIndex'] - 1) * $limit;
        $result = $model->getList('member_id', $filter, $offset, $limit);
        $memberIdsStr = '';
        foreach ($result as $v) {
            $memberIdsStr .= $v['member_id'] . ',';
        }
        if ($memberIdsStr) {
            $memberIdsStr = trim($memberIdsStr, ',');
        }
        return $memberIdsStr;
    }

    /**
     * 商品统计数据
     */
    public function getShopGoodsCount($filter)
    {
        //商品ID（数组）
        $goods_id = $filter['goods_id'];
        //店铺ID
        $shop_id = $filter['shopId'];
        //付款方式（all = 全部; 1 = 已付款； 0 = 未付款）
        $pay_status = $filter['pay_status'];
        //是否购买商品（1 = 购买；0 = 未购买）
        $has_buy = intval($filter['has_buy']);
        //购买范围（1 = 购买全部； 0 = 购买任意商品)
        $all_buy = intval($filter['all_buy']);
        //当前页面
        $pageIndex = intval($filter['pageIndex']);
        //没有数据容量
        $pageSize = intval($filter['pageSize']);
        //购买起始时间（时间戳）
        $date_from = $filter['date_from'];
        //购买结束时间（时间戳）
        $date_to = $filter['date_to'];

        $quantity = $filter['quantity'];
        $goodsIds = implode(',', $filter['goods_id']);

        $sql = "SELECT COUNT(DISTINCT B.`member_id`) as _count
                FROM `sdb_ecorder_order_items` AS A 
                LEFT JOIN `sdb_ecorder_orders` AS B ON A.`order_id` = B.`order_id` 
                WHERE B.`createtime` >= {$date_from} AND B.`createtime` <= {$date_to} AND B.`shop_id` = '{$shop_id}'";
        //是否购买商品
        if ($has_buy == 1) {
            //购买商品范围Sql
            $hasBuySql = '';
            if ($all_buy == 1) {
                foreach ($goods_id as $v) {
                    $hasBuySql .= " AND goods_id = {$v}";
                }
            }
            elseif ($all_buy == 0) {
                $hasBuySql .= " AND goods_id IN ({$goodsIds})";
            }
            $payStatusSql = '';
            //付款状态
            if ($pay_status == 'all') {
                ;
            }
            elseif ($pay_status == '1') {
                $payStatusSql = " AND B.`pay_status` = '1' ";
            }
            elseif ($pay_status == '0') {
                $payStatusSql = " AND B.`pay_status` = '0' ";
            }
            $hasBuySql .= $payStatusSql;
        }
        elseif ($has_buy == 0) {
            $hasBuySql .= " AND goods_id NOT IN ({$goodsIds})";
        }
        $sql .= $hasBuySql;
        $db = kernel::database();
        $result = $db->select($sql);
        $count = $result[0]['_count'] > 10000 ? 10000 : $result[0]['_count'];
        $returnData = array('Count' => $count);
        return json_encode($returnData);
    }

    //获取商品数据
    public function getResourceGetshopgoodsmemberlist($filter)
    {
        //商品ID（数组）
        $goods_id = $filter['goods_id'];
        //店铺ID
        $shop_id = $filter['shopId'];
        //付款方式（all = 全部; 1 = 已付款； 0 = 未付款）
        $pay_status = $filter['pay_status'];
        //是否购买商品（1 = 购买；0 = 未购买）
        $has_buy = intval($filter['has_buy']);
        //购买范围（1 = 购买全部； 0 = 购买任意商品)
        $all_buy = intval($filter['all_buy']);
        //当前页面
        $pageIndex = intval($filter['pageIndex']);
        //没有数据容量
        $pageSize = intval($filter['pageSize']);
        //购买起始时间（时间戳）
        $date_from = $filter['date_from'];
        //购买结束时间（时间戳）
        $date_to = $filter['date_to'];

        $quantity = $filter['quantity'];
        $goodsIds = implode(',', $filter['goods_id']);

        $sql = "SELECT DISTINCT B.`member_id`
                FROM `sdb_ecorder_order_items` AS A 
                LEFT JOIN `sdb_ecorder_orders` AS B ON A.`order_id` = B.`order_id` 
                WHERE B.`createtime` >= {$date_from} AND B.`createtime` <= {$date_to} AND B.`shop_id` = '{$shop_id}'";
        //是否购买商品
        if ($has_buy == 1) {
            //购买商品范围Sql
            $hasBuySql = '';
            if ($all_buy == 1) {
                foreach ($goods_id as $v) {
                    $hasBuySql .= " AND goods_id = {$v}";
                }
            }
            elseif ($all_buy == 0) {
                $hasBuySql .= " AND goods_id IN ({$goodsIds})";
            }
            $payStatusSql = '';
            //付款状态
            if ($pay_status == 'all') {
                ;
            }
            elseif ($pay_status == '1') {
                $payStatusSql = " AND B.`pay_status` = '1' ";
            }
            elseif ($pay_status == '0') {
                $payStatusSql = " AND B.`pay_status` = '0' ";
            }
            $hasBuySql .= $payStatusSql;
        }
        elseif ($has_buy == 0) {
            $hasBuySql .= " AND goods_id NOT IN ({$goodsIds})";
        }
        $sql .= $hasBuySql . " LIMIT 10000";
        $db = kernel::database();
        $result = $db->select($sql);
        $memberList = array();
        foreach ($result as $v) {
            $memberList[] = $v['member_id'];
        }
        //把全部客户ID存在KV中
        $user_id = kernel::single('desktop_user')->get_id();
        base_kvstore::instance('analysis')->store('filter_member_'.$user_id,implode(',',$memberList));
        //返回页面显示的member_id列表
        $start = ($pageIndex - 1) * $pageSize;
        $returnMemberList = array();
        for ($i = 0; $i < $pageSize; $i++) {
            if (isset($memberList[$start + $i]) && $memberList[$start + $i]) {
                $returnMemberList[] = $memberList[$start + $i];
            }
            else {
                break;
            }
        }
        if ($returnMemberList) {
            return implode(',', $returnMemberList);
        }
        else {
            return '';
        }
    }
}
