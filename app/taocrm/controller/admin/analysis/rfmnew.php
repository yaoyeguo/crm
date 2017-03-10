<?php

class taocrm_ctl_admin_analysis_rfmnew extends desktop_controller
{
    public $workground = 'taocrm.analysts';
    protected $shop_id = null;
    protected $defaultMain = array('Rmain' => 90, 'Fmain' => 3, 'Mmain' => 300);
    protected $ruels = array(
            array(1, 1, 1, 'label' => '重要保持', 'color' => '#4CA575'),
            array(1, 0, 0, 'label' => '重要发展', 'color' => '#76A03A'),
            array(1, 0, 1, 'label' => '重要价值', 'color' => '#9C572E'),
            array(0, 1, 1, 'label' => '重要挽留', 'color' => '#52739C'),
            array(1, 1, 0, 'label' => '一般重要', 'color' => '#95467E'),
            array(0, 0, 1, 'label' => '一般客户', 'color' => '#95467E'),
            array(0, 1, 0, 'label' => '一般挽留', 'color' => '#95467E'),
            array(0, 0, 0, 'label' => '无价值', 'color' => '#000000')
    );
    
    public function __construct($app)
    {
        parent::__construct($app);
    }
    
    public function index()
    {
        $data = $this->getDefalutParams();
        if ($data) {
            $this->defaultMain = $data;
        }
        $ecorderShop = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($ecorderShop as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        
        //从java接口获取自动配置参数
        if($_POST['task'] == 'autoConf'){
            $this->begin();
            $connect = kernel::single('taocrm_middleware_connect');
            $params = array(
                'shop_id'=> $_POST['shop_id'],
            );
            $data = $connect->getRFMConf($params);
            if(is_array($data)){
                $params = array_merge($params, $data);
                $this->setDefalutParams($params);
            }else{
                $this->end(false, $data);
            }
        }elseif($_POST){
            $this->setDefalutParams($_POST);
        }
        
        if ($this->shop_id == null) {
            $this->shop_id = key($shops);
            $this->setDefalutParams(array('shop_id' => $this->shop_id));
        }
//        $reportList = kernel::single('taocrm_analysis_day')->getNewRfmData($this->defaultMain, $this->shop_id);
        $reportList = $this->getRFMData($this->defaultMain, $this->shop_id);
        $this->pagedata['path']= 'RFM分析';
        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $this->shop_id;
        $this->pagedata['defaultMain']= $this->defaultMain;
        $this->pagedata['reportList'] = $reportList;
        $this->pagedata['rules'] = $this->ruels;
        $this->page('admin/analysis/rfmnew.html');
    }
    
    protected function getRFMData($params, $shopId)
    {
        $time = strtotime(date("Y-m-d 00:00:00"));
        $params['Rmain'] = $time - $params['Rmain'] * 86400;
        $connect = kernel::single('taocrm_middleware_connect');
        $params['shopId'] = $shopId;
        //$result = json_decode($connect->RFM($params), true);
        $result = $connect->RFM($params);
        $data = array();
        if ($result) {
            foreach ($result as $k => $v) {
                switch ($k) {
                    case '222':
                        $i = 0;
                        break;
                    case '211':
                        $i = 1;
                        break;
                    case '212':
                        $i = 2;
                        break;
                    case '122':
                        $i = 3;
                        break;
                    case '221':
                        $i = 4;
                        break;
                    case '112':
                        $i = 5;
                        break;
                    case '121':
                        $i = 6;
                        break;
                    case '111':
                        $i = 7;
                        break;
                }
                $data[$i] = $this->formatRFMData($v);
            }
            ksort($data);
        }
        return $data;
    }
    
    protected function formatRFMData($value)
    {
        return array('count' => $value['members'], 'sum_total_amount' => number_format($value['amount'], 2));
    }
    
    protected function setDefalutParams($params = array())
    {
        if ($params) {
            $kvstore = base_kvstore::instance('analysis');
            if ($params['shop_id']) {
                $kvstore->store('analysis_shop_id', $params['shop_id']);
                $this->shop_id = $params['shop_id'];
            }
            
            if ($params['Rmain']) {
                $kvstore->store('Rmain', $params['Rmain']);
                $this->defaultMain['Rmain'] = $params['Rmain'];
            }
            
            if ($params['Fmain']) {
                $kvstore->store('Fmain', $params['Fmain']);
                $this->defaultMain['Fmain'] = $params['Fmain'];
            }
            
            if ($params['Mmain']) {
                $kvstore->store('Mmain', $params['Mmain']);
                $this->defaultMain['Mmain'] = $params['Mmain'];
            }
        }
        return true;
    }
    
    protected function getDefalutParams()
    {
        $data = array();
        $kvstore = base_kvstore::instance('analysis');
        $kvstore->fetch('analysis_shop_id', $this->shop_id);
        $kvstore->fetch('Rmain', $data['Rmain']);
        $kvstore->fetch('Fmain', $data['Fmain']);
        $kvstore->fetch('Mmain', $data['Mmain']);
        foreach ($data as $v) {
            if (empty($v)) {
                return '';
            }
        }
        return $data;
    }
    
    public function get_filter_member($params)
    {
        $limit = $params['plimit'];
        $offest = $limit * $params['page'];
        return $this->getAnalysisList($offest, $limit, $params);
    }
    
    protected function getAnalysisList($offset, $limit, $filterParams)
    {
        $time = strtotime(date("Y-m-d 00:00:00"));
        $R = $time - $filterParams['Rmain'] * 86400;
        $params = array('R' => $R, 'F' => $filterParams['Fmain'], 'M' => $filterParams['Mmain'], 'shop_id' => $filterParams['shop_id']);
        $filterRule = $this->ruels[$filterParams['rules']];
        $rules = array($filterRule);
        $result = kernel::single('taocrm_analysis_cache')->getRewRfmCacheData($rules, $params);
        $data = array();
        $tmp_filter_sql = $result[0]['filter_sql'] . " limit {$offset} , {$limit}";
        $data['filter_sql'] = $result[0]['filter_sql'];
        $data['total'] = $result[0]['count'];
        $data['params'] = $params;
        $members = kernel::database()->select($tmp_filter_sql);
        $formatMebmers = array();
        foreach ($members as $member) {
            $formatMebmers[] = $member['member_id'];
        }
        $data['member_id'] = $formatMebmers;
        return $data;
    }
}