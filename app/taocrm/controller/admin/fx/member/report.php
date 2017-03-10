<?php

// 客户列表，报表页面弹窗专用
class taocrm_ctl_admin_fx_member_report extends desktop_controller
{
    var $workground = 'taocrm.member';
    private $interfaceTableName = '';
    private static $taocrm_middleware_connect = '';
    private static $CacheId = '';
    public function __construct($app)
    {
        parent::__construct($app);
        $this->interfaceTableName = 'taocrm_mdl_middleware_fx_member_report';
        $this->interfaceTableName = '';
        if (self::$taocrm_middleware_connect == '') {
            self::$taocrm_middleware_connect = new taocrm_middleware_connect;
        }
    }

    public function index()
    {
       
        //$repoartArr = array('group','analysis','contribution','frequency');
        //$memberList = array('goods');
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

       
            $func = 'getMemberFilterBy' . ucfirst(strtolower($_GET['filter_type']));
            if (isset($_GET['count_by']) && $_GET['count_by']) {
                $func .= ucfirst(strtolower($_GET['count_by']));
            }
            $baseFilter = $this->$func($params);
            //$href = 'index.php?app=market&ctl=admin_active&act=create_active';
            //$href .= "&shop_id=$shop_id&total=".$baseFilter['total']."&CacheId=".$baseFilter['CacheId'];
            $smsHref = 'index.php?app=market&ctl=admin_fx_activity&act=create_activity';
            $tmpHref = "&p[group_id]=$group_id&shop_id=$shop_id&total=".$baseFilter['total']."&CacheId=".$baseFilter['CacheId']."&CacheIdCreateTime=".$baseFilter['CacheIdCreateTime'];
            $smsHref .= $tmpHref;
           

            if ($baseFilter['total'] > 0) {
            	
            	$actions = array(
					 array(
                        'label' => '创建短信活动',
                        'href' => $smsHref."&send_method=sms",
                        'target' => 'dialog::{width:700,height:350,title:\'创建短信活动\'}'
                        ),
				);
				
                $this->finder('taocrm_mdl_middleware_fx_member_report',array(
                    'title' => $page_title,
                    'actions' => $actions,
                    'base_filter'=> $baseFilter['filter'],
                    'use_buildin_set_tag'=>false,
                    'use_buildin_import'=>false,
                    'use_buildin_export'=>false,
                    'use_buildin_recycle'=>false,
                    'use_buildin_filter'=>false,
                    'use_buildin_tagedit'=>false,
                    'use_buildin_selectrow'=>false,
                    'use_view_tab'=>false,
                        ));
            }else {
                $this->finder('taocrm_mdl_middleware_fx_member_report',array(
                    'title' => $page_title,
                    'actions' => array(),
                    'base_filter'=> $baseFilter['filter'],
                    'use_buildin_set_tag'=>false,
                    'use_buildin_import'=>false,
                    'use_buildin_export'=>false,
                    'use_buildin_recycle'=>false,
                    'use_buildin_filter'=>false,
                    'use_buildin_tagedit'=>false,
                    'use_buildin_selectrow'=>false,
                    'use_view_tab'=>false,
                ));
            }
           
        
    }

    //自定义分组
    public function getMemberFilterByGroup($params)
    {
        $groupId = $params['group_id'];
        $model = app::get('taocrm')->model('fx_member_group');
        $groupInfo = $model->dump($groupId);
        $shop_id = $groupInfo['shop_id'];
        $filter = unserialize($groupInfo['filter']);
        $data['filter'] = $filter;
        $data['shop_id'] = $shop_id;
        $data['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $data['pageSize'] = max(intval($params['plimit']), 10);
        $data['packetName'] = 'ShopMemberAnalysis';
        $data['methodName'] = 'FxSearchMemberAnalysisList';
        return $this->getReportUrlData($data);
    }

    public function getMemberFilterByAnalysisContribution($params){
    	$filter = array();
        $filter['shopId'] = $params['shop_id'];
        $filter['beginTime'] = strtotime($params['date_from']);
        $filter['endTime'] = strtotime($params['date_to']) + 86400;
        $filter['status'] = strtoupper($params['order_status']);
        //$filter['agent_name'] = $params['agent_name'];
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        //中文编码转换
        $charset=mb_detect_encoding($params['agent_name'],array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        if($charset != 'UFT-8'){
        	$filter['agent_name'] = iconv($charset,"UTF-8",$params['agent_name'] ); 
        }
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['methodName'] = 'FxMemberAnalysisByTimeAndOrderStatus';
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
        $filter['status'] = strtoupper($params['order_status']);
        $filter['stateId'] = $params['area'];
        $filter['pageIndex'] = max(0, intval($params['page']) + 1, 1);
        $filter['pageSize'] = max(intval($params['plimit']), 10);
        $filter['packetName'] = 'ShopMemberAnalysis';
        $filter['is_city'] = $params['is_city'];
        $filter['methodName'] = 'FxMemberAnalysisByTimeStateAndOrderStatus';
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
        $filter['methodName'] = 'FxAnalysisByFinishOrderCount';

        $filter['finishOrderCount'] = intval($params['buy_freq']);
        $filter['agentCount'] = intval($params['agent_count']);
        $filter['orderCountType'] = '=';
        $filter['agentCountType'] = '=';

        if($filter['finishOrderCount'] == 0){
            $filter['finishOrderCount'] = 6;
            $filter['orderCountType'] = '>';
        }
        if($filter['agentCount'] == 3){
        	$filter['agentCountType'] = '>=';
        }
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

}
