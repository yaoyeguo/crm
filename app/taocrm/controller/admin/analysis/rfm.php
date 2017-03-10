<?php
class taocrm_ctl_admin_analysis_rfm extends desktop_controller {
    var $workground = 'taocrm.analysts';
    protected $rules = array(
           0 => array(
               array(0, 2),
               array(1, 2),
               array(2, 2)
           ),
           1 => array(
               array(0, 1),
               array(1, 1),
               array(2, 1),
           ),
           2 => array(
               array(0, 0),
               array(1, 0),
               array(2, 0)
           )
        );
    protected $RFSingle = array('33' => array('<=', '>='),
                                '23' => array('-', '>='),
                                '13' => array('>=', '>='),
                                '32' => array('<=', '-'),
                                '22' => array('-', '-'),
                                '12' => array('>=', '-'),
                                '31' => array('<=', '<='),
                                '21' => array('-', '<='),
                                '11' => array('>=', '<=')
    );
    protected $formatSign = '|';
    public function __construct($app){
        parent::__construct($app);
        $timeBtn = array(
            'today' => date("Y-m-d"),
            'yesterday' => date("Y-m-d", time()-86400),
            'this_month_from' => date("Y-m-" . 01),
            'this_month_to' => date("Y-m-d"),
            'this_week_from' => date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400),
            'this_week_to' => date("Y-m-d"),
            'sevenday_from' => date("Y-m-d", time()-6*86400),
            'sevenday_to' => date("Y-m-d"),
        );
        $this->pagedata['timeBtn'] = $timeBtn;
        
        //初始化统计时间段
        if($_POST['date_from'] && $_POST['date_to']){
            base_kvstore::instance('analysis')->
                store('analysis_date_from',$_POST['date_from']);
            base_kvstore::instance('analysis')->
                store('analysis_date_to',$_POST['date_to']);
        }
        if($_POST['shop_id']) 
            base_kvstore::instance('analysis')->store('analysis_shop_id',$_POST['shop_id']);
            base_kvstore::instance('analysis')->fetch('analysis_shop_id',$this->shop_id);
        base_kvstore::instance('analysis')->
            fetch('analysis_date_from',$this->date_from);
        base_kvstore::instance('analysis')->
            fetch('analysis_date_to',$this->date_to);
        if(!$this->date_from) 
            $this->date_from = date('Y-m-d',(time()-86400*7));
        if(!$this->date_to)
            $this->date_to = date('Y-m-d',(time()-86400*1));
    }

    //RF分析
    public function index()
    {
        $filter['shop_id'] = $this->shop_id;
        $shop_id = $this->shop_id;
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$filter['shop_id'])
                $filter['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        
        $r = $_POST['r'];
        $f = $_POST['f'];
        //初始化参数
        if(!$r) $r = array(array(0,30),array(31,90),array(91,0));
        if(!$f) $f = array(array(0,3),array(4,7),array(8,0));
        
        $filter['r'] = $r;
        $filter['f'] = $f;
        
        //标签
        foreach($r as $k=>$v){
            if($v[0]==0) $r_label[] = '≤'.$v[1];
            elseif($v[1]==0) $r_label[] = '≥'.$v[0];
            else{
                if(!$v[0]||!$v[1]){unset($r[$k]);continue;}
                $r_label[] = $v[0].' - '.$v[1];
            }
        }
        
        foreach($f as $k=>$v){
            if($v[0]==0) $f_label[] = '≤'.$v[1];
            elseif($v[1]==0) $f_label[] = '≥'.$v[0];
            else{
                if(!$v[0]||!$v[1]){unset($f[$k]);continue;}
                $f_label[] = $v[0].' - '.$v[1];
            }
        }
//        $rs = kernel::single('taocrm_analysis_day')->get_rfm_data($filter);
        $rs = $this->getRFData($filter);
        //echo('<pre>');var_dump(json_decode($rs['total_r_data'],1));
        $this->pagedata['analysis_data'] = ($rs['analysis_data']);
        $this->pagedata['total_data'] = ($rs['total_data']);
        $this->pagedata['total_r_data'] = ($rs['total_r_data']);
        $this->pagedata['total_f_data'] = ($rs['total_f_data']);
        $this->pagedata['rParamsSort'] = ($rs['rParamsSort']);
        $this->pagedata['fParamsSort'] = ($rs['fParamsSort']);

        krsort($f_label);
        $this->pagedata['r']= $r;
        $this->pagedata['f']= $f;
        $this->pagedata['r_label']= $r_label;
        $this->pagedata['f_label']= $f_label;
        $this->pagedata['shops']= $shops;
        $this->pagedata['shop_id']= $filter['shop_id'];
        $this->pagedata['path']= '客户升迁路径';
        $this->pagedata['service'] = 'taocrm_analysis_rfm';
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_rfm&act=index';
        $this->pagedata['member_url'] = 'index.php?app=taocrm&ctl=admin_member&act=index&filter_type=rfm';
        $this->page('admin/analysis/rfm.html');
    }
    
    protected function getRFData($filter)
    {
        $connect = kernel::single('taocrm_middleware_connect');
        $time = strtotime(date("Y-m-d 00:00:00"));
        $r = $filter['r'];
        $r1 = $time - $r[0][1] * 86400;
        $r3 = $time - $r[2][0] * 86400;
        $r2 = $r3.','.$r1;
        $f = $filter['f'];
        $f1 = $f[0][1];
        $f3 = $f[2][0];
        $f2 = $f[1][0].','.$f[1][1];
        $shopId = $filter['shop_id'];
        $params = array('shopId' => $shopId, 'r1' => $r1, 'r2' => $r2 , 'r3' => $r3, 'f1' => $f1, 'f2' => $f2, 'f3' => $f3);
        //$result = json_decode($connect->RF($params), true);
        $result = $connect->RF($params);
        
        $data = array();
        if ($result) {
            $RFData = array();
            foreach ($result as $k => $v) {
                switch ($k) {
                    case '33':
                        $i = 0;
                        $j = 0;
                        $Rmain = $r[0][1];
                        $Fmain = $f[2][0];
//                        $pageparam = array('r1' => 0, 'r2' => $r1, 'f1' => 0, 'f2' => $f3);
                        $pageparam = array('r1' => $r1, 'r2' => 0, 'f1' => $f3, 'f2' => 0);
                        break;
                    case '23':
                        $i = 0;
                        $j = 1;
                        $Rmain = $r[1];
                        $Fmain = $f[2][0];
//                        $pageparam = array('r1' => $r3, 'r2' => $r1, 'f1' => $f3, 'f2' => 0);
                        $pageparam = array('r1' => $r3, 'r2' => $r1, 'f1' => $f3, 'f2' => 0);
                        break;
                    case '13':
                        $i = 0;
                        $j = 2;
                        $Rmain = $r[2][0];
                        $Fmain = $f[2][0];
//                        $pageparam = array('r1' => $r3, 'r2' => 0, 'f1' => 0, 'f2' => $f3);
                        $pageparam = array('r1' => 0, 'r2' => $r3, 'f1' => $f3, 'f2' => 0);
                        break;
                    case '32':
                        $i = 1;
                        $j = 0;
                        $Rmain = $r[0][1];
                        $Fmain = $f[1];
//                        $pageparam = array('r1' => 0, 'r2' => $r1, 'f1' => $f1, 'f2' => $f3);
                        $pageparam = array('r1' => $r1, 'r2' => 0, 'f1' => ($f1 + 1), 'f2' => ($f3 - 1));
                        break;
                    case '22':
                        $i = 1;
                        $j = 1;
                        $Rmain = $r[1];
                        $Fmain = $f[1];
                        $pageparam = array('r1' => $r3, 'r2' => $r1, 'f1' => $f1 + 1, 'f2' => $f3 - 1);
                        break;
                    case '12':
                        $i = 1;
                        $j = 2;
                        $Rmain = $r[2][0];
                        $Fmain = $f[1];
//                        $pageparam = array('r1' => $r3, 'r2' => 0, 'f1' => $f1, 'f2' => $f3);
                        $pageparam = array('r1' => 0, 'r2' => $r3, 'f1' => $f1 + 1, 'f2' => $f3 - 1);
                        break;
                    case  '31':
                        $i = 2;
                        $j = 0;
                        $Rmain = $r[0][1];
                        $Fmain = $f[0][1];
                        $pageparam = array('r1' => $r1, 'r2' => 0, 'f1' => 0, 'f2' => $f1);
                        break;
                    case '21':
                        $i = 2;
                        $j = 1;
                        $Rmain = $r[1];
                        $Fmain = $f[0][1];
                        $pageparam = array('r1' => $r3, 'r2' => $r1, 'f1' => 0, 'f2' => $f1);
                        break;
                    case '11':
                        $i = 2;
                        $j = 2;
                        $Rmain = $r[2][0];
                        $Fmain = $f[0][1];
                        $pageparam = array('r1' => 0, 'r2' => $r3, 'f1' => 0, 'f2' => $f1);
                        break; 
                }
                $RFData[$i][$j] = $this->formatRFData($v, $k, $Rmain, $Fmain, $pageparam);
            }

            $RFData = $this->sortRFData($RFData);
            $total_r_data = array();
            $total_f_data = array();
            $total_data = array();
            for ($i = 0; $i < 3; $i++) {
                $total_r_data[$i]['members'] = $RFData[0][$i]['members'] + $RFData[1][$i]['members'] + $RFData[2][$i]['members'];
                $total_r_data[$i]['amount'] = $RFData[0][$i]['amount'] + $RFData[1][$i]['amount'] + $RFData[2][$i]['amount'];
                $total_f_data[$i]['members'] = $RFData[$i][0]['members'] + $RFData[$i][1]['members'] + $RFData[$i][2]['members'];
                $total_f_data[$i]['amount'] = $RFData[$i][0]['amount'] + $RFData[$i][1]['amount'] + $RFData[$i][2]['amount'];
                $total_data['members'] += $total_f_data[$i]['members'];
                $total_data['amount'] += $total_f_data[$i]['amount'];
            }
            
            $data['analysis_data'] = $RFData;
            $data['total_r_data'] = $total_r_data;
            $data['total_f_data'] = $total_f_data;
            $data['total_data'] = $total_data;
            
            $rParamsSort[0] = array('r1' => $r1, 'r2' => 0, 'f1' => 0, 'f2' => 0);
            $rParamsSort[1] = array('r1' => $r3, 'r2' => $r1, 'f1' => 0, 'f2' => 0);
            $rParamsSort[2] = array('r1' => 0, 'r2' => $r3, 'f1' => 0, 'f2' => 0);
            
            $fParamsSort[0] = array('r1' => 0, 'r2' => 0, 'f1' => $f3, 'f2' => 0);
            $fParamsSort[1] = array('r1' => 0, 'r2' => 0, 'f1' => $f1 + 1, 'f2' => $f3 - 1);
            $fParamsSort[2] = array('r1' => 0, 'r2' => 0, 'f1' => 0, 'f2' => $f1);
            
            
            $data['rParamsSort'] = $rParamsSort;
            $data['fParamsSort'] = $fParamsSort;
        }
        return $data;
    }
    
    protected function sortRFData($data)
    {
        ksort($data);
        foreach ($data as $k => &$v) {
            ksort($v);
        }
        return $data;
    }
    
    protected function formatRFData($value, $k, $R, $F, $pageparam)
    {
        $rString = '';
        $fString = '';
        if (isset($this->RFSingle[$k])) {
            if (is_array($R)) {
                $rString = $R[0] . $this->RFSingle[$k][0] . $R[1];
            }
            else {
                $rString = $this->RFSingle[$k][0] . $R;
            }
            if (is_array($F)) {
                $fString = $F[0] . $this->RFSingle[$k][1] . $F[1];
            }
            else {
                $fString = $this->RFSingle[$k][1] . $F;
            }
        }
        return array('members' => $value['members'], 'amount' => $value['amount'], 'R' => $rString, 'F' => $fString, 'PR' => array($pageparam['r1'], $pageparam['r2']), 'PF' => array($pageparam['f1'], $pageparam['f2']));
    }
    /**
     * 格式化参数
     */
    protected function getRulesItem($R, $F)
    {
        $R0 = $R[0];
        $R1 = $R[1];
        $F0 = $F[0];
        $F1 = $F[1];
        //F落点位置
        if ($F0 > 0 && $F1 == 0) {
            $k1 = 0;
        }
        elseif ($F0 >0 && $F1 >0) {
            $k1 = 1;
        }
        else {
            $k1 = 2;
        }
        //R落点位置
        if ($R0 == 0 && $R1 > 0) {
            $k2 = 0;
        }
        elseif ($R0 > 0 && $R1 == 0) {
            $k2 = 2;
        }
        else {
            $k2 = 1;
        }
        return $this->rules[$k1][$k2];

    }
    
    protected function formatParams($value, $type = 'f')
    {
        $array = array();
        if ($value[0] == 0) {
            $array = array($value[1], '', '');
        }
        elseif ($value[1] == 0) {
            $array = array('', '', $value[0]);
        }
        else {
            $array = array('', $value, '');
        }
        if ($type == 'r') {
            $dayTime = 86400;
            $time = strtotime(date("Y-m-d 00:00:00"));
            //设置UNIX时间戳
            foreach ($array as &$v) {
                if ($v == '') {
                    continue;
                }
                if (is_array($v)) {
                    foreach ($v as &$v1) {
                         $v1 = $time - $v1 * $dayTime;
                    }
                }
                else {
                    $v = $time - $v * $dayTime;
                }
            }
        }
        return $array;
    }
    
    protected function trimArray($array)
    {
        if ($array) {
            foreach ($array as $v) {
                $v = trim($v);
            }
        }
        return $array;
    }
    
    protected function formatRF($array)
    {
        $value = $this->trimArray($array);
        $data = array();
        foreach ($value as $k => $v) {
             $tmpv = explode('_', $v);
             $data[$k][0] = $tmpv[0];
             $data[$k][1] = $tmpv[1];
        }
        return $data;
    }
    
    protected function makeSqlR($params, $field)
    {
        $sql = '';
        $count= count($params);
        for ($i = 0; $i < $count; $i++) {
            $tmpSql = '';
            if ($params[$i][0] == 0 && $params[$i][1] > 0) {
                $status = 0;
            }
            elseif ($params[$i][0] > 0 && $params[$i][1] > 0) {
                $status = 1;
            }
            elseif ($params[$i][0] > 0 && $params[$i][1] == 0) {
                $status = 2;
            }
            switch ($status) {
                case 0:
                    $tmpSql = "( {$field} >=  {$params[$i][1]} )"; 
                    break;
                case 1:
                    $tmpSql = "( {$field} >= {$params[$i][1]} AND {$field} <= {$params[$i][0]} )";
                    break;
                case 2:
                    $tmpSql = " ({$field} <= {$params[$i][0]} )";
                    break;
            }
            $sql .= $tmpSql;
            $sql .= ($i + 1) != $count ? " OR " : ' '; 
        }
        return $sql;
    }
    
    protected function makeSqlF($params, $field)
    {
        $sql = '';
        $count= count($params);
        for ($i = 0; $i < $count; $i++) {
            $tmpSql = '';
            if ($params[$i][0] == 0 && $params[$i][1] > 0) {
                $status = 0;
            }
            elseif ($params[$i][0] > 0 && $params[$i][1] > 0) {
                $status = 1;
            }
            elseif ($params[$i][0] > 0 && $params[$i][1] == 0) {
                $status = 2;
            }
            switch ($status) {
                case 2:
                    $tmpSql = "( {$field} >=  {$params[$i][0]} )"; 
                    break;
                case 1:
                    $tmpSql = "( {$field} >= {$params[$i][0]} AND {$field} <= {$params[$i][1]} )";
                    break;
                case 0:
                    $tmpSql = " ({$field} <= {$params[$i][1]} )";
                    break;
            }
            $sql .= $tmpSql;
            $sql .= ($i + 1) != $count ? " OR " : ' '; 
        }
        return $sql;
    }
    
    
    protected function makeSql($r , $f, $shop_id, $choise)
    {
        $sql .= "SELECT COUNT(DISTINCT member_id) as _count FROM sdb_taocrm_member_analysis WHERE 1 = 1";
        $filterSql = " SELECT DISTINCT member_id FROM sdb_taocrm_member_analysis WHERE 1 = 1 ";
        $data = array();
        $time = strtotime(date("Y-m-d 00:00:00"));
        $dayTime = 86400;
        if ($choise == 0) {
            if ($f[0] > 0 && $f[1] == 0) {
                $fStatus = 2;
            }
            elseif ($f[0] == 0 && $f[1] > 0) {
                $fStatus = 0;
            }
            elseif ($f[0] > 0 && $f[1] > 0) {
                $fStatus = 1;
            }
            switch ($fStatus) {
                case 0:
                    $sql .= " AND `finish_orders` <= {$f[1]} ";
                    $filterSql .= " AND `finish_orders` <= {$f[1]} ";
                    break;
                case 1:
                    $sql .= " AND `finish_orders` >= {$f[0]} AND  `finish_orders` <= {$f[1]}";
                    $filterSql .= " AND `finish_orders` >= {$f[0]} AND  `finish_orders` <= {$f[1]}";
                    break;
                case 2:
                    $sql .= " AND `finish_orders` >= {$f[0]} ";
                    $filterSql .= " AND `finish_orders` >= {$f[0]} ";
                    break;
            }
            $formatR = array();
            foreach ($r as $k => $v) {
                if ($v[0] != 0) {
                    $v[0] = $time - $v[0] * $dayTime;
                }
                if ($v[1] != 0) {
                    $v[1] = $time - $v[1] * $dayTime;
                }
                $formatR[$k] = $v;
            }
            $sql .= " AND shop_id = '{$shop_id}'";
            $filterSql .= " AND shop_id = '{$shop_id}'";
            $rSql = " AND " . '( ' . $this->makeSqlR($formatR, 'last_buy_time') . ' ) ';
            $sql .= $rSql;
            $filterSql .= $rSql;
        }
        else {
            if ($r[0] > 0 && $r[1] == 0) {
                $rStatus = 2;
            }
            elseif ($r[0] == 0 && $r[1] > 0) {
                $rStatus = 0;
            }
            elseif ($r[0] > 0 && $r[1] > 0) {
                $rStatus = 1;
            }
            $r0 = '';
            if ($r[0] != 0) {
                $r0 = $time - $r[0] * $dayTime;
            }
            $r1 = '';
            if ($r[1] != 0) {
                $r1 =  $time - $r[1] * $dayTime;
            }
            switch ($rStatus) {
                case 0:
                    $sql .= " AND `last_buy_time` >= {$r1} ";
                    $filterSql .= " AND `last_buy_time` >= {$r1} ";
                    break;
                case 1:
                    $sql .= " AND `last_buy_time` >= {$r1} AND  `last_buy_time` <= {$r0}";
                    $filterSql .= " AND `last_buy_time` >= {$r1} AND  `last_buy_time` <= {$r0}";
                    break;
                case 2:
                    $sql .= " AND `last_buy_time` <= {$r0} ";
                    $filterSql .= " AND `finish_orders` <= {$r0} ";
                    break;
            }
            $sql .= " AND shop_id = '{$shop_id}'";
            $filterSql .= " AND shop_id = '{$shop_id}'";
            $fSql = " AND " . '( ' . $this->makeSqlF($f, 'finish_orders') . ' ) ';
            $sql .= $fSql;
            $filterSql .= $fSql;
        }
        
        $data['sql'] = $sql;
        $data['filter_sql'] = $filterSql;
        return $data;
    }
    
    public function get_filter_member($filter)
    {
        $limit = $filter['plimit'];
        $offset = $filter['page'] * $limit;
        $members = array();
        $db = kernel::database();
        $shop_id = $filter['shop_id'];
        if (strpos($filter['r'], $this->formatSign)) {
            $tmpR = explode($this->formatSign, $filter['r']);
            $r = $this->formatRF($tmpR);
            $f = explode('_', $filter['f']);
            $data = $this->makeSql($r, $f, trim($shop_id), 0);
            $filter_sql = $data['filter_sql'];
            $tmpFilterSQl =  $filter_sql . " limit {$offset} , {$limit}";
            $result = $db->select($data['sql']);
            $total = $result[0]['_count'];
            $result[0]['filter_sql'] = $filter_sql;
        }
        elseif (strpos($filter['f'], $this->formatSign)) {
            $tmpF = explode($this->formatSign, $filter['f']);
            $r = explode('_', $filter['r']);
            $f = $this->formatRF($tmpF);
            $data = $this->makeSql($r, $f, trim($shop_id), 1);
            $filter_sql = $data['filter_sql'];
            $tmpFilterSQl =  $filter_sql . " limit {$offset} , {$limit}";
            $result = $db->select($data['sql']);
            $total = $result[0]['_count'];
            $result[0]['filter_sql'] = $filter_sql;
        }
        else {
            $r = explode('_', $filter['r']);
            $f = explode('_', $filter['f']);
            $rules = array(array($this->getRulesItem($r, $f)));
            $formatR = $this->formatParams($r, 'r');
            $formatF = $this->formatParams($f);
            $params = array(
                'shop_id' => $shop_id,
                'R' => $formatR,
                'F' => $formatF
                
            );
            $result = kernel::single('taocrm_analysis_cache')->getRfmCacheData($rules, $params);
            $filter_sql = $result[0]['filter_sql']; 
            $total = $result[0]['members'];
            $tmpFilterSQl =  $filter_sql. " limit {$offset} , {$limit}";
        }
        //过滤参数
        $params = array(
            'r' => $filter['r'],
            'f' => $filter['f']
        );
        
        $members = $db->select($tmpFilterSQl);
        $formatMebmers = array();
        foreach ($members as $member) {
            $formatMebmers[] = $member['member_id'];
        }
        return array(
            'member_id' => $formatMebmers,
            'total' => $total,
            'params' => $params,
            'filter_sql' => $result[0]['filter_sql']
        );
    }
    
    public function get_filter_member_back($filter){
        $page = $filter['page'];
        $page_size = $filter['plimit'];
        
        $members = array();
        $shop_id = $filter['shop_id'];
        $r = explode('_',$filter['r']);
        $f = explode('_',$filter['f']);
        
        if($r[1]==0 && $r[0]>0) {
            $where .= ' AND datediff(now(),FROM_UNIXTIME(last_buy_time))>='.$r[0].' ';
        }elseif($r[1]>0){
            $where .= ' AND datediff(now(),FROM_UNIXTIME(last_buy_time))>='.$r[0].' 
                        AND datediff(now(),FROM_UNIXTIME(last_buy_time))<='.$r[1].' ';
        }
            
        if($f[1]==0 && (int)$f[0]>0) {
            $where .= ' AND finish_orders>='.$f[0].' ';
        }elseif($f[1]>0){
            $where .= ' AND finish_orders>='.$f[0].' 
                        AND finish_orders<='.$f[1].' ';
        }
    
        $sql = "SELECT member_id FROM sdb_taocrm_member_analysis WHERE shop_id='$shop_id' $where ";
        $filter_sql = $sql;
        $sql .= "LIMIT ".($page*$page_size).",$page_size";
        $rs = kernel::database()->select($sql);
        if($rs){
            foreach($rs as $v) {
                $members[] = $v['member_id'];
            }
        }unset($rs);
        
        //总数
        $sql = "SELECT count(member_id) as total FROM sdb_taocrm_member_analysis 
        WHERE shop_id='$shop_id' $where";
        $rs = kernel::database()->selectRow($sql);
        $total = $rs['total'];
        
        //过滤参数
        $params = array(
            'r' => $filter['r'],
            'f' => $filter['f']
        );
        
        return array('member_id'=>$members,'total'=>$total,'params'=>$params,'filter_sql'=>$filter_sql);
    }
    
}

