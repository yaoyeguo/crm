<?php

// 客户列表，报表页面弹窗专用
class taocrm_ctl_admin_member_report extends desktop_controller
{
    var $workground = 'taocrm.member';
    private $interfaceTableName = '';
    private static $taocrm_middleware_connect = '';
    private static $CacheId = '';
    public function __construct($app)
    {
        parent::__construct($app);
        $this->interfaceTableName = 'taocrm_mdl_middleware_member_report';
        $this->interfaceTableName = '';
        if (self::$taocrm_middleware_connect == '') {
            self::$taocrm_middleware_connect = new taocrm_middleware_connect;
        }
    }

    public function index()
    {
        $repoartArr = array(
            'group'  ,'analysis'  ,'rfmnew',
            'rfm'    ,'frequency' ,'active',
            'goods'  ,'lca'       ,'price' ,
        );
        $params = $_GET;
        $group_id = intval($params['group_id']);
        //分页页码
        is_numeric($params['page'])?$params['page']--:$params['page']=0;

        //分页大小
        $user_id = kernel::single('desktop_user')->get_id();
        if($_POST['plimit']) {
            $plimit = $_POST['plimit'];
            $params['page'] = 0;
        }else{
            $plimit = $this->app->getConf('lister.pagelimit.'.$user_id);
        }
        $plimit = $plimit?$plimit:20;
        $params['plimit'] = $plimit;

        $shopObj = app::get(ORDER_APP)->model('shop');
        $shopList = $shopObj->getList('shop_id,name');
        foreach((array)$shopList as $v){
            $shops[$v['shop_id']] = $v;
        }
        //将shop_id转换成view
        $page_title = '客户列表';
        if($params['shop_id'] && $view==0) {
            $shop_id = $params['shop_id'];
            $page_title = $shops[$shop_id]['name'];
        }

        if(in_array($_GET['filter_type'], $repoartArr)){
            $func = 'getMemberFilterBy' . ucfirst(strtolower($_GET['filter_type']));
            if (isset($_GET['count_by']) && $_GET['count_by']) {
                $func .= ucfirst(strtolower($_GET['count_by']));
            }
            $baseFilter = $this->$func($params);
            $smsHref = 'index.php?app=market&ctl=admin_active_sms&act=create_active';
            $edmHref = 'index.php?app=market&ctl=admin_active_edm&act=create_active';
            $exportHref = 'index.php?app=taocrm&ctl=admin_member_report&act=toExport';
            $tmpHref = "&p[group_id]=$group_id&shop_id=$shop_id&total=".$baseFilter['total']."&CacheId=".$baseFilter['CacheId']."&CacheIdCreateTime=".$baseFilter['CacheIdCreateTime'];
            $smsHref .= $tmpHref;
            $edmHref .= $tmpHref;
            $exportHref .= $tmpHref;
            if ($_GET['filter_type'] == 'goods') {
                $reportFilter = array('filter_type' => 'goods');
                $smsHref .= '&filter_from=report&filter_type=goods';
                $edmHref .= '&filter_from=report&filter_type=goods';
                $exportHref .= '&filter_from=report&filter_type=goods';
            }

            if($baseFilter['total'] > 0){
            	$actions = array(
                    array(
                        'label'=>'客户标签',
                        'submit'=>'index.php?app=taocrm&ctl=admin_member_report&act=addTag',
                        'target'=>'dialog::{width:650,height:355,title:\'客户标签\'}'
                    ),
                    array(
                        'label' => '全部客户营销',
                        'href' => $smsHref."&send_method=sms",
                        'target' => 'dialog::{width:700,height:350,title:\'全部客户营销\'}'
                    ),
                    array(
                        'label'=>'指定客户营销',
                        'submit'=>'index.php?app=market&ctl=admin_active_sms&act=create_active&create_source=members&send_method=sms&memlist=1&shop_id='.$shop_id,
                        'target'=>'dialog::{width:700,height:350,title:\'指定客户营销\'}'
                    ),
                    /*array(
                        'label' => '创建邮件活动',
                        'href' => $edmHref."&send_method=edm",
                        'target' => 'dialog::{width:700,height:350,title:\'创建邮件活动\'}'
                    ),*/
				);
                
				//只支持自定义分组放大镜导出
				if($_GET['filter_type'] == 'group'){
					$action = array(
                        'label' => '导出所有客户',
                        'href' => $exportHref,
                        'target' => 'dialog::{width:500,height:150,title:\'导出所有客户\'}'
                    );
					array_push($actions,$action);
				}
				
                $this->finder('taocrm_mdl_middleware_member_report',array(
                    'title' => $page_title,
                    'actions' => $actions,
                    'base_filter'=> $baseFilter['filter'],
                    'use_buildin_set_tag'=>false,
                    'use_buildin_import'=>false,
                    'use_buildin_export'=>false,
                    'use_buildin_recycle'=>false,
                    'use_buildin_filter'=>false,
                    'use_buildin_tagedit'=>false,
                    'use_buildin_selectrow'=>true,
                    'use_view_tab'=>false,
                        ));
            }else{
                $this->finder('taocrm_mdl_middleware_member_report',array(
                    'title' => $page_title,
                    'actions' => array(),
                    'base_filter'=> $baseFilter['filter'],
                    'use_buildin_set_tag'=>false,
                    'use_buildin_import'=>false,
                    'use_buildin_export'=>false,
                    'use_buildin_recycle'=>false,
                    'use_buildin_filter'=>false,
                    'use_buildin_tagedit'=>false,
                    'use_buildin_selectrow'=>true,
                    'use_view_tab'=>false,
                ));
            }
        }else{
            //▲▲▲ 内存改造已经全部完成，下面的代码不再执行
            //筛选RFM数据
            if($params['filter_type'] == 'rfm') {
                $rfm_filter = kernel::single('taocrm_ctl_admin_analysis_rfm')->get_filter_member($params);
                if($rfm_filter) {
                    $base_filter = $rfm_filter;
                }
            }

            if ($params['filter_type'] == 'rfmnew') {
                $rfmnew_filter = kernel::single('taocrm_ctl_admin_analysis_rfmnew')->get_filter_member($params);
                if ($rfmnew_filter) {
                    $base_filter = $rfmnew_filter;
                }
            }

            //从报表筛选客户数据（内存改造中）
            if($params['filter_type'] == 'analysis') {
                $analysis_filter = $this->app->model('member_analysis')->get_filter_member($params);
                if($analysis_filter) {
                    $base_filter = $analysis_filter;
                }
            }
            
            //从商品筛选客户数据
            if($params['filter_type'] == 'goods') {
                $analysis_filter = app::get('ecgoods')->model('shop_goods')->get_filter_member($params);
                if($analysis_filter) {
                    $base_filter = $analysis_filter;
                }
            }

            //自定义客户分组（内存改造已完成）
            if($params['filter_type'] == 'group') {
                $group_filter = $this->app->model('member_group')->getMemberList($params);
                if($group_filter) {
                    $base_filter = $group_filter;
                }
            }

            //营销活动效果评估
            if($params['filter_type'] == 'active') {
                $group_filter = app::get('market')->model('active')->get_filter_member($params);
                if($group_filter) {
                    $base_filter = $group_filter;
                }
            }

            if($params['filter_type'] == 'frequency'){
                $analysis_filter = $this->app->model('member_analysis')->get_freq_member($params);
                if($analysis_filter) {
                    $base_filter = $analysis_filter;
                }
            }

            //客户标签
            if($params['filter_type'] == 'member_tag') {
                if(isset($_GET['page'])){
                    $page = intval($_GET['page']) - 1;
                }else{
                    $page = 0;
                }
            
                $tag_filter = $this->app->model('member_tag')->getMemberList($params, $plimit*$page, $plimit);
                if($tag_filter) {
                    $base_filter = $tag_filter;
                }
            }

            if($shop_id) {
                $base_filter['shop_id'] = $shop_id;
            }

            $href = 'index.php?app=market&ctl=admin_active&act=create_active';
            $href .= "&shop_id=$shop_id&total=".$base_filter['total'];
            if($params['filter_type']=='group') {
                $href .= '&p[group_id]='.$params['group_id'];
            }elseif($params['filter_type']=='member_tag') {
                $href = 'index.php?app=market&ctl=admin_active_sms&act=create_active&create_source=tags&tag_id='.$params['tag_id'];
            }else{
                $href .= '&filter_from=report&filter_type='.$params['filter_type'];
                $href .= '&'.http_build_query($base_filter['params']);
                base_kvstore::instance('analysis')->store('filter_sql_'.$user_id,$base_filter['filter_sql']);
            }
             
            $this->finder(
                'taocrm_mdl_member_report',
                array(
                'title' => $page_title,
                'actions' => array(
            array(
                    'label' => '创建短信活动',
                    'href' => $href."&send_method=sms",
                    'target' => 'dialog::{width:700,height:350,title:\'创建短信活动\'}'
                    ),
                    ),
                'base_filter'=>$base_filter,
                'use_buildin_set_tag'=>false,
                'use_buildin_import'=>false,
                'use_buildin_export'=>false,
                'use_buildin_recycle'=>false,
                'use_buildin_filter'=>false,
                'use_buildin_tagedit'=>false,
                'use_buildin_selectrow'=>false,
                'use_view_tab'=>false,
                )
            );
        }
    }

    //从商品列表筛选客户
    public function filter_by_goods()
    {
        $goods_id = $_POST['goods_id'];
        if(sizeof($goods_id)>20){
            die('<p align=center><br/><br/><br/><br/>最多只能选择20款商品！</p>');
        }elseif(sizeof($goods_id)==0){
            die('<p align=center><br/><br/><br/><br/>不支持全部选择，请选择您要筛选的商品！</p>');
        }

        $date_from = date('Y-m-d',strtotime('-7 days'));
        $date_to = date('Y-m-d',strtotime('-1 days'));

        $shopObj = app::get('ecorder')->model('shop');
        $rs = $shopObj->getList('shop_id,name');
        foreach($rs as $v){
            $shop_name_arr[$v['shop_id']] = $v['name'];
        }

        $oGoods = app::get('ecgoods')->model('shop_goods');
        $goods = $oGoods->getList('goods_id,name,shop_id',array('goods_id'=>$goods_id));
        foreach($goods as $v){
            $shop_arr[$v['shop_id']] = $shop_name_arr[$v['shop_id']];
            $shop_id = $v['shop_id'];
            $shop_name = $shop_name_arr[$v['shop_id']];
        }
        if(sizeof($shop_arr)>1) {
            die('<p align=center><br/><br/><br/><br/>不能同时选择两个店铺的商品！</p>');
        }

        $this->pagedata['shop_name'] = $shop_name;
        $this->pagedata['shop_id'] = $shop_id;
        $this->pagedata['date_from'] = $date_from;
        $this->pagedata['date_to'] = $date_to;
        $this->pagedata['goods'] = $goods;
        $this->display('admin/member/filter_by_goods.html');
    }

    public function getMemberFilterByGroup($params)
    {
        $groupId = $params['group_id'];
        $model = app::get('taocrm')->model('member_group');
        $groupInfo = $model->dump($groupId);
        $shop_id = $groupInfo['shop_id'];
        $filter = unserialize($groupInfo['filter']);
        $data['filter'] = $filter;
        $data['shop_id'] = $shop_id;
        $data['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $data['pageSize'] = max(intval($params['plimit']), 10);
        $data['packetName'] = 'ShopMemberAnalysis';
        $data['methodName'] = 'SearchMemberAnalysisList';
        return $this->getReportUrlData($data);
    }

    /**
     * 营销活动评估
     */
    public function getMemberFilterByActive($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = $params['date_from'];
        $filter['endTime'] = $params['date_to'];
        $filter['execTime'] = $params['exec_time'];
        $filter['taskId'] = $params['active_id'];
        $filter['orderStatus'] = strtolower($params['order_status']);
        $filter['group'] = strtoupper($params['group_type']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'ActiveMemberInfo';
        return $this->getReportUrlData($filter);
    }
    /*
     public function getMemberFilterByAnalysis($params)
     {
     //echo('<pre>');var_dump($_GET);
     $connect = new taocrm_middleware_connect;
     $filter = &$_GET;
     $dateFormate = $this->getDateFormatByType($params);
     $filter['date_from'] = $dateFormate['date_from'];
     $filter['date_to'] = $dateFormate['date_to'];
     $filter['order_status'] = strtoupper($filter['order_status']);
     $total = $connect->get_member_list($filter);
     $total = json_decode($total, 1);
     //echo('<pre>');var_dump($total);
     $data = array('total' => $total, 'filter' => $filter);
     return $data;
     }
     */
    public function getMemberFilterByAnalysis($params)
    {
        $filter = array();
        $dateFormate = $this->getDateFormatByType($params);
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = $dateFormate['date_from'];
        $filter['endTime'] = $dateFormate['date_to'];
        $filter['status'] = strtoupper($params['order_status']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByTimeAndOrderStatus';
        return $this->getReportUrlData($filter);
    }
    
	public function getMemberFilterByAnalysisLose($params)
    {
        $filter = array();
        $dateFormate = $this->getDateFormatByType($params);
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = $dateFormate['date_from'];
        $filter['endTime'] = $dateFormate['date_to'];
        $filter['status'] = strtoupper($params['member_status']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByLose';
        return $this->getReportUrlData($filter);
    }

    /**
     * 首页订单数据分析
     */
    public function getMemberFilterByAnalysisHomepage($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = strtotime($params['date_from']);
        $filter['endTime'] = strtotime($params['date_to']);
        $filter['newOrOld'] = strtoupper($params['member_status']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByNewOldAndTime';
        return $this->getReportUrlData($filter);
    }

    /**
     * 报表管理 --- 地域分析
     */
    public function getMemberFilterByAnalysisArea($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = strtotime($params['date_from']);
        $filter['endTime'] = strtotime($params['date_to']) + 86400;
        if ($params['order_status'] == 'paid')
        $tmpStatus = 'PAY';
        else
        $tmpStatus = $params['order_status'];
        $filter['status'] = strtoupper($tmpStatus);
        $filter['stateId'] = $params['area'];
        $filter['is_city'] = $params['is_city'];
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByTimeStateAndOrderStatus';
        return $this->getReportUrlData($filter);
    }

    /**
     * 客户销售订单单价分布图报表明细放大镜
     */
    public function getMemberFilterByPrice($params)
    {
        $params = array(
            'shopId' => !empty($params['shop_id']) ? trim($params['shop_id']) : 0,
            'beginTime' => !empty($params['date_from']) ? strtotime($params['date_from']) : 0,
            'endTime' => !empty($params['date_to']) ? strtotime($params['date_to']) : 0,
            'totalAmount1' => !empty($params['totalAmount1']) ? intval($params['totalAmount1']) : 2,
            'totalAmount2' => !empty($params['totalAmount2']) ? intval($params['totalAmount2']) : 2,
            'orderStatus' => !empty($params['order_status']) ? trim($params['order_status']) : 'all',
            'pageIndex' => max(0, intval($params['page']) + 1, 1),
            'pageSize' => $params['plimit'] ? max(intval($params['plimit']), 10) : 200,
            'methodName'=> 'MemberAnalysisByPrice'
        );

        if(!$params['endTime'])
        {
            exit("参数有误");
        }
        return $this->getReportUrlData($params);
    }
    /**
     * 生命周期放大镜,复购周期明细放大镜
     */
    public function getMemberFilterByLca($params)
    {
        $params = array(
            'combineRange' => !empty($params['combineRange']) ? trim($params['combineRange']) : 0,
            'beginTime' => !empty($params['beginTime']) ? trim($params['beginTime']) : 0,
            'endTime' => !empty($params['endTime']) ? trim($params['endTime']) : 0,
            'daysType' => !empty($params['buyDays']) ? trim($params['buyDays']) : 0,
            'preType' => !empty($params['preType']) ? trim($params['preType']) : 0,
            
            'shopId' => !empty($params['shop_id']) ? trim($params['shop_id']) : 0,
            //'beginDays' => !empty($params['beginDays']) ? intval($params['beginDays']) : 0,
            //'endDays' => !empty($params['endDays']) ? intval($params['endDays']) : 0,
            'targets' => !empty($params['targets']) ? intval($params['targets']) : 2,
            'pageIndex' => max(0, intval($params['page']) + 1, 1),
            'pageSize' => max(intval($params['plimit']), 10),
            'methodName'=> 'MemberAnalysisByLca'
        );

        return $this->getReportUrlData($params);
    }
    /**
     * 新老客户---根据时间类型及新老获取客户统计信息
     */
    public function getMemberFilterByAnalysisNewold($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $dateFormate = $this->getDateFormatByType($params);
        $filter['beginTime'] = $dateFormate['date_from'];
        $filter['endTime'] = $dateFormate['date_to'];
        $filter['newOrOld'] = strtoupper($params['type']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByNewOldAndTime';
        return $this->getReportUrlData($filter);
    }

    public function getMemberFilterByAnalysisTree($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $dateFormate = $this->getDateFormatByType($params);
        $filter['beginTime'] = $dateFormate['date_from'];
        $filter['endTime'] = $dateFormate['date_to'];
        $filter['newOrOld'] = strtoupper($params['member_status']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByNewOldAndTime';
        return $this->getReportUrlData($filter);
    }

    /**
     * 根据RFM值获取客户分析信息                   销售报表页 RFM分析 放大镜
     */
    public function getMemberFilterByRfmnew($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['r'] = strtotime(date("Y-m-d 00:00:00")) - $params['Rmain'] * 86400;
        $filter['f'] = $params['Fmain'];
        $filter['m'] = $params['Mmain'];
        /*
         $rules = array(
         0 => array('>=', '>', '>'),
         1 => array('>=', '<=', '<='),
         2 => array('>=', '<=', '>'),
         3 => array('<', '>', '>'),
         4 => array('>=', '>', '<='),
         5 => array('<', '<=', '>'),
         6 => array('<', '>', '<='),
         7 => array('<', '<=', '<=')
         );
         */
        //ge  大于等于		gt	大于		le	小于等于
        $rules = array(
        0 => array('ge', 'gt', 'gt'),
        1 => array('ge', 'le', 'le'),
        2 => array('ge', 'le', 'gt'),
        3 => array('lt', 'gt', 'gt'),
        4 => array('ge', 'gt', 'le'),
        5 => array('lt', 'le', 'gt'),
        6 => array('lt', 'gt', 'le'),
        7 => array('lt', 'le', 'le')
        );

        $filter['rc'] = $rules[$params['rules']][0];
        $filter['fc'] = $rules[$params['rules']][1];
        $filter['mc'] = $rules[$params['rules']][2];
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'AnalysisByRFM';
        return $this->getReportUrlData($filter);
    }

    /**
     * 根据RF值获取客户分析信息                      销售报表页 RF分析 放大镜
     */
    public function getMemberFilterByRfm($params)
    {
        $r = explode('_', $params['r']);
        $f = explode('_', $params['f']);
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['r1'] = $r[0];
        $filter['r2'] = $r[1];
        $filter['f1'] = $f[0];
        $filter['f2'] = $f[1];
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'AnalysisByRF';
        return $this->getReportUrlData($filter);

    }

    /**
     * 根据商品XY获取客户统计信息              商品分析 关联商品 放大镜                     商品分析 购物篮分析 放大镜
     */
    public function getMemberFilterByAnalysisBasket($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = strtotime($params['date_from']);
        $filter['endTime'] = strtotime($params['date_to']);
        $filter['xGoodsId'] = $params['goods_a'];
        $filter['yGoodsId'] = $params['goods_b'];
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'AnalysisByXY';
        $filter['inOrOut'] = isset($params['inOrOut']) ? $params['inOrOut'] : 'IN';
        return $this->getReportUrlData($filter);
    }

    /**
     * 销售报表页 销售统计 放大镜              销售报表页 订单状态 放大镜                销售报表页 销售漏斗 放大镜
     */
    public function getMemberFilterByAnalysisFunnel($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = strtotime($params['date_from']);
        $filter['endTime'] = strtotime($params['date_to']) + 86400;
        $status = '';
        switch ($params['order_status']) {
            case 'paid':
                $status = 'PAY';
                break;
            case 'finish':
                $status = 'FINISH';
                break;
            case 'ship':
                $status = 'SHIP';
                break;
            case 'unship':
                $status = 'UNSHIP';
                break;
            case 'unpaid':
                $status = 'UNPAY';
                break;
            case 'dead':
                $status = 'SHIPUNFINISH';
                break;
            default:
                $status = 'ALL';
                break;
        }
        $filter['status'] = $status;
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByTimeAndOrderStatus';
        return $this->getReportUrlData($filter);
    }

    /**
     * 订单状态客户列表
     */
    public function getMemberFilterByAnalysisOstatus($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $dateFormate = $this->getDateFormatByType($params);
        $filter['beginTime'] = $dateFormate['date_from'];
        $filter['endTime'] = $dateFormate['date_to'];
        $filter['status'] = strtoupper($params['order_status']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByTimeAndOrderStatus';
        return $this->getReportUrlData($filter);
    }

    /**
     * buy_freq
     */
    public function getMemberFilterByFrequency($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = strtotime($params['date_from']);
        $filter['endTime'] = strtotime($params['date_to']) + 86400;
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'AnalysisByFinishOrderCount';

        $filter['finishOrderCount'] = intval($params['buy_freq']);
        $filter['greaterOrLess'] = '=';

        if($filter['finishOrderCount'] == 0){
            $filter['finishOrderCount'] = 6;
            $filter['greaterOrLess'] = '>';
        }
        return $this->getReportUrlData($filter);
    }

    /**
     * 商品分析 关联商品 放大镜
     */
    public function getMemberFilterByAnalysisRelation($params)
    {
        if ($params['relation'] == 'only_a') {
            $params['inOrOut'] = 'OUT';
            //            $params['xGoodsId'] = $params['goods_b'];
            //            $params['yGoodsId'] = $params['goods_a'];
        }
        return $this->getMemberFilterByAnalysisBasket($params);
        //        $filter = array();
        //        $filter['shopId'] = $params['shop_id'];
        //        $filter['beginTime'] = strtotime($params['date_from']);
        //        $filter['endTime'] = strtotime($params['date_to']);
        //        $filter['xGoodsId'] = $params['goods_a'];
        //        $filter['yGoodsId'] = $params['goods_b'];
        //        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        //        $filter['pageSize'] = max(intval($params['plimit']), 10);
        //        $filter['packetName'] = 'ShopMemberAnalysis';
        //        $filter['methodName'] = 'AnalysisByXY';
        //        return $this->getReportUrlData($filter);
    }

    public function getMemberFilterByAnalysisHour($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $dateFormate = $this->getDateFormatByType($params);
        $filter['beginTime'] = $dateFormate['date_from'];
        $filter['endTime'] = $dateFormate['date_to'];
        $filter['hour'] = intval($params['hours']);
        $filter['day'] = $params['hours'];
        $filter['timeType'] = 'hour';
        $filter['status'] = strtoupper($params['order_status']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByTimeHourAndOrderStatus';
        return $this->getReportUrlData($filter);
    }

    public function getMemberFilterByAnalysisWeek($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $dateFormate = $this->getDateFormatByType($params);
        $filter['beginTime'] = $dateFormate['date_from'];
        $filter['endTime'] = $dateFormate['date_to'];
        $filter['hour'] = intval($params['hours']);
        $filter['day'] = $params['hours'];
        $filter['timeType'] = 'week';
        $filter['status'] = strtoupper($params['order_status']);
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'MemberAnalysisByTimeHourAndOrderStatus';
        return $this->getReportUrlData($filter);
    }

    /**
     * 返回报表数据
     */
    protected function getReportUrlData($filter, $tableName = '')
    {
        if ($tableName == '') {
            $tableName = $this->interfaceTableName;
        }
        $count = self::$taocrm_middleware_connect->count($tableName, $filter);
        $cacheInfo = self::$taocrm_middleware_connect->getCacheInfo();
        
        $filter['Count'] = $count;
        return array('total' => $count, 'filter' => $filter, 'CacheId' => $cacheInfo['CacheId'], 'CacheIdCreateTime' => $cacheInfo['CacheIdCreateTime']);
    }

    /**
     * 获得日期数据格式
     */
    protected function getDateFormatByType($params)
    {
        $filter = array();
        switch (strtoupper($params['datetype'])) {
            case 'DAY':
                $filter['date_from'] = strtotime($params['date']);
                $filter['date_to']   = strtotime($params['date']) + 86400;
                break;
            case 'WEEK':
                $date = $this->getDayByDateAndWeek($params['date']);
                $filter['date_from'] = $date[0];
                $filter['date_to'] = $date[1];
                //一年的最后一周和第一周进行处理，周不能跨年
                $year_from = date('Y',$filter['date_from']);
                $year_to = date('Y',$filter['date_to']);
                
                $data = explode('-',$params['date']);
                if(in_array($data[1],array('52','53'))){
                	if($year_from != $year_to){
                		$filter['date_to'] = strtotime($year_to.'-01-01 00:00:00');
                	}
                }else if($data[1] == '01'){
                	if($year_from != $year_to){
                		$filter['date_from'] = strtotime($year_to.'-01-01 00:00:00');
                	}
                }
   
                break;
            case 'MONTH':
                $max_days = date('t', strtotime($params['date'].'-01'));
                $filter['date_from'] = strtotime($params['date'].'-01');
                $filter['date_to']   = strtotime($params['date']."-$max_days") + 86400;
                break;
            case 'YEAR':
                $filter['date_from'] = strtotime($params['date'].'-01-01');
                $filter['date_to']   = strtotime($params['date']."-12-31") + 86400;
                break;
            default:
                $filter['date_from'] = strtotime($params['date_from']);
                $filter['date_to']   = strtotime($params['date_to']) + 86400;
                break;
        }
        //        exit;
        return $filter;
    }

    /**
     *
     * @param string $dateData  日期数据格式（2012年第5周只需要年在第1位序列，周在最后1列序列即可：
     * 例如：2012-05，2012-0A-0B-05都是合法的输入
     */
    private function getDayByDateAndWeek($dateData)
    {
        $tmp = explode('-', $dateData);
        $year = $tmp[0];
        $week = $tmp[count($tmp)-1];
        $yearAddNum = 0;
        $weekAddNum = 0;
        $weight = 86399;
        //本年的第1天是周几
        $yearFirstDayNode = date('N', strtotime($year.'-01-01'));
        if ($yearFirstDayNode == 7) {
            $yearAddNum = 1;
        }
        else {
            $weekAddNum = (7 - $yearFirstDayNode) * 86400;
        }
        $weekEndTime = strtotime($year.'-01-01') + 86400 * ($week - 1 + $yearAddNum) * 7 + $weekAddNum + $weight;
        $weekStartTime = $weekEndTime - 86400 * 6 - $weight;
        return array(0 => $weekStartTime, 1 => $weekEndTime);
    }

    /**
     * 商品列表筛选数据
     */
    public function getMemberFilterByGoods($params)
    {
        $filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['quantity'] = $params['quantity'];
        $dateFormate = $this->getDateFormatByType($params);
        $filter['date_from'] = $dateFormate['date_from'];
        $filter['date_to'] = $dateFormate['date_to'];
        $filter['goods_id'] = $params['goods_id'];
        $filter['has_buy'] = $params['has_buy'];
        $filter['all_buy'] = $params['all_buy'];
        $filter['pay_status'] = $params['pay_status'];
        $filter['dialog'] = $params['dialog'];
        $filter['packetName'] = 'getShopGoods';
        $filter['methodName'] = 'getShopGoodsCount';
        return $this->getReportUrlData($filter);
    }
     
    public function getMemberFilterByLcamember() { 
        $params = array( 
            'shopId' => !empty($_GET['shop_id']) ? trim($_GET['shop_id']) : 0,
            'beginDays' => !empty($_GET['beginDays']) ? intval($_GET['beginDays']) : 0,
            'endDays' => !empty($_GET['endDays']) ?  intval($_GET['endDays']) : 0,
            'targets' => !empty($_GET['targets']) ? intval($_GET['targets']) : 2,
        ); 


        if(!$params['shopId'] || !$params['endDays']) {
            exit("参数有误"); 
        } 
   //     $all_res = kernel::single('taocrm_middleware_connect')->createCallplanMemberList($params);
    
        return $this->getReportUrlData($params,'createCallplanMemberList');
    }

    public function addTagByGroup(){
        $page = $_GET['page'];
        $group_id = $_GET['group_id'];
        $tag_ids = $_POST['tag_ids'];
        $response = array();
        if($page && $group_id){
            $plimit = 100;
            $page = $page - 1;
            $oMember= &$this->app->model('middleware_member_report');
            $oTag= &$this->app->model('member_tag');
            $params = array('group_id'=>$group_id,'page'=>$page,'plimit'=>$plimit);
            $filter = $this->getMemberFilterByGroup($params);
            $list = $oMember->getList('*',$filter['filter'],$plimit * $page,$plimit,'');
            if($list){
                //var_dump($list);exit;
                $member_ids = array();
                foreach($list as $row){
                    $member_ids[] = $row['member_id'];
                }
                $tag_ids = explode(',', $tag_ids);
                $oTag->saveMemberTag($member_ids,$tag_ids);
                $response = array('rsp'=>'succ','res'=>array('page'=>$page+2));
            }else{
                $response = array('rsp'=>'succ','res'=>'');
            }

        }else{
            $response = array('rsp'=>'fail','info'=>'数据异常');
        }
        echo json_encode($response);
        exit;

    }


    function toExport(){
        $params = $_GET;
        //echo '<pre>';var_export($params);exit;
        $params['group_id'] = $params['p']['group_id'];
        $baseFilter = $this->getMemberFilterByGroup($params);
        $result = array('res'=>'succ');
        if($baseFilter['total'] > 0){
            $oMember = app::get('taocrm')->model('member_export');
            if($oMember->saveExportLog($baseFilter)){
                $result = array('res'=>'succ','msg'=>'下载客户资料队列已生成，稍后在“系统设置-》客户资料导出” 查看、导出。<br />
<a href="index.php?app=taocrm&ctl=admin_member&act=exportIndex" target="_blank">立即查看</a>
                ');
            }else{
                $result = array('res'=>'fail','msg'=>'导出失败');
            }
        }else{
            $result = array('res'=>'fail','msg'=>'客户数为0，无法导出');
        }
        
        $this->pagedata['result'] = $result;

        $this->display('admin/member/report/toExport.html');
    }
    //客户打标签
    public function addTag()
    {
        if(isset($_POST['id']) && $_POST['id']){
            $member_ids = $_POST['id'];
        }elseif(isset($_POST['member_id']) && $_POST['member_id']){
            $member_ids = $_POST['member_id'];
        }else{
            $member_ids = $_GET['id'];
        }

        $oTag= $this->app->model('member_tag');
        $this->pagedata['taglist'] = $oTag->getTagList();
        $this->pagedata['member_ids'] = implode(',', $member_ids);

        //获取当前客户的标签
        $oTag= $this->app->model('member_tag');
        $tags = $oTag->getTagsByMember($member_ids);
        $this->pagedata['tags'] = '0,'.implode(',',$tags).',0';

        $this->display('admin/member/report/add_tag.html');
    }
}
