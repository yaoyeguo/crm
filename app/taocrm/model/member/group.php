<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_member_group extends dbeav_model{
    //提示信息描述
    protected $selectFilterSignType = array(
        'int' => array(
            'nequal' => '等于',
            'lthan' => '小于',
            'sthan' => '小于等于',
            'than' => '大于',
            'bthan' => '大于等于',
            'between' => '介于'),
        'time' => array(
            'than' => '晚于',
            'lthan' => '早于',
            'nequal' => '等于',
            'between' => '介于'),
        'date' => array(
            '7' => '最近一周',
            '30' => '最近一个月',
            '60' => '最近二个月',
            '90' => '最近三个月',
            '180' => '最近半年',
            '360' => '最近一年')
    );
    
    //过滤容器
    protected $selectFilter = array(
        'total_orders' => array('label' => '订单总数', 'func' => 'intFilter','type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'finish_orders' => array('label' => '成功的单数', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'total_amount' => array('label' => '订单总金额', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
//        'total_per_amount' => array('label' => '订单客单价', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'total_per_amount' => array('label' => '平均订单价', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'buy_freq' => array('label' => '购买频次(天)', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'avg_buy_interval' => array('label' => '平均购买间隔(天)', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'buy_month' => array('label' => '购买月数', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
//        'buy_products' => array('label' => '购买商品总数', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'buy_products' => array('label' => '下单商品总数', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'finish_total_amount' => array('label' => '成功的金额', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        //'finish_per_amount' => array('label' => '成功的客单价', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'finish_per_amount' => array('label' => '成功的平均订单价', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'unpay_orders' => array('label' => '未付款单数', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'unpay_amount' => array('label' => '未付款金额', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'refund_orders' => array('label' => '退款订单数', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'first_buy_time' => array('label' => '第一次购买时间', 'func' => 'timeFilter', 'type' => 'time', 'table' => 'sdb_taocrm_member_analysis'),
        'last_buy_time' => array('label' => '最后购买时间', 'func' => 'timeFilter', 'type' => 'time', 'table' => 'sdb_taocrm_member_analysis'),
        'refund_amount' => array('label' => '退款总金额', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_member_analysis'),
        'lv_id' => array('label' => '客户等级', 'func' => 'selectFilter', 'type' => 'select', 'table' => 'sdb_taocrm_member_analysis', 'relationField' => 'name', 'relationMethond' => 'getShopInfoRelating'),
        'shop_evaluation' => array('label' => '店铺评价', 'func' => 'selectFilter', 'type' => 'select', 'table' => 'sdb_taocrm_member_analysis', 'relationField' => 'db_schema','relationMethond' => 'getMembersAnalysisDbschema'),
        'good_buy_date' => array('label' => '购买时间', 'func' => 'selectFilter', 'type' => 'timeselect', 'table' => 'sdb_taocrm_member_analysis', 'alias' => 'last_buy_time', 'relationField' => 'selectFilterSignType-date','relationMethond' => 'classFields', 'sign' => 'bthan'),
        //'last_buy_time' => array('label' => '购买时间', 'func' => 'selectFilter', 'type' => 'time', 'table' => 'sdb_taocrm_member_analysis', 'alias' => 'last_buy_time', 'relationField' => 'selectFilterSignType-date','relationMethond' => 'classFields', 'sign' => 'bthan'),
        //'min_good_num' => array('label' => '购买数量', 'func' => 'selectFilter', 'type' => 'anaInt', 'table' => 'sdb_taocrm_member_analysis', 'alias' => 'last_buy_time', 'relationField' => 'selectFilterSignType-date','relationMethond' => 'classFields', 'sign' => 'bthan'),
    
        'points' => array('label' => '积分', 'func' => 'intFilter', 'type' => 'int', 'table' => 'sdb_taocrm_members', 'primary' => 'member_id', 'app' => 'taocrm', 'model' => 'members'),
        'birthday' => array('label' => '生日', 'func' => 'timeFilter', 'type' => 'time', 'table' => 'sdb_taocrm_members', 'primary' => 'member_id', 'app' => 'taocrm', 'model' => 'members'),
        'regions_id' => array('label' => '地区', 'func' => 'arrayFilter', 'type' => 'array', 'table' => 'sdb_taocrm_members', 'primary' => 'member_id', 'app' => 'taocrm', 'model' => 'members', 'alias' => 'state'),
    
//        'goods_id' => array('label' => '商品编号', 'func' => 'arrayFilter', 'type' => 'array', 'table' => 'sdb_taocrm_member_analysis', 'secondTable' => 'sdb_ecorder_order_items-sdb_ecorder_orders,member_id','primary' => 'member_id', 'secondePrimary' => 'order_id'),
        'goods_id' => array('label' => '商品编号', 'func' => '', 'type' => 'array', 'table' => 'sdb_ecorder_orders', 'secondTable' => 'sdb_ecorder_order_items','primary' => 'member_id', 'secondePrimary' => 'order_id'),
    );
    
    //过滤容器错误信息
    protected $filterErrorMsg = array(
        'sign' => '-->条件必须与父结点相同,父结点是：',
        'lthan' => '-->条件不能大于父结点值,父结点值：',
        'sthan' => '-->条件不能大于父结点值,父结点值：',
        'nequal' => '-->条件必须与父节点值相同,父节点值：',
        'bthan' => '-->条件不能小于父节点值,父节点值：',
        'between' => '-->条件不能超出父节点区间,父节点区间值',
        'node_between' => '-->条件的区间不正确,第1个文本框的值不能大于第2个文本框的值',
        'node_value' => '-->没有选择条件时,输入框的值必须为空',
        'must_int' => '-->输入框里的数值必须是正整数',
        'must_money' => '-->输入框里的数值必须是正数',
        'must_time' => '-->输入框里的数值必须是合法日期',
        'error_params' => '参数错误',
        'error_conditions_exist' => '条件尚未创建',
        'select' => '-->条件必须与父结点相同：父结点值：',
        'local_empty' => '-->条件至少选中一个或多个,父节点值：',
        'error_choise' => '-->条件选择不能包括：'
    );
    //区域
    protected $regions_id = array();
    //当前结点信息
    protected $nodeInfo = '';
    //父结点信息
    protected $parentInfo = '';
    //主表
    public $primaryTable = 'sdb_taocrm_member_analysis';
    //连接字段
    public $onFiled = 'member_id';
    //过滤字段
    protected $filter = '';
    //店铺id
    protected $shop_id;
    
    public function __construct($app) {
        parent::__construct($app);
        $rs = app::get('ectools')->model('regions')->getList('region_id,local_name',array('region_grade' => 1));
        $regions = array();
        foreach ($rs as $value) {
            $regions[$value['region_id']] = $value['local_name'];
        }
        $this->regions_id = $regions;
    }
    
    // 获取分组内的客户数量
    public function countMembers($data){
        $this->nodeInfo = $data;
        $filter = $data['filter'];
        $shop_id = $data['shop_id'];
        $final_filter = $this->buildFilter($filter,$shop_id);
        if (is_string($final_filter)) {
            $rs = $this->db->select($final_filter);
        }
        elseif (is_array($final_filter)) {
            $oMemberAnalysis = $this->app->model('member_analysis');
            $rs = $oMemberAnalysis->count($final_filter);
        }
        
        if (is_array($rs)) {
            $rs = $rs[0]['_count'];
        }
        return $rs;
    }
    
    //获得父结点信息
    protected function getParentGroupInfo($pid, $field = '*')
    {
        $filter = array('group_id' => $pid);
        $fieldString = '';
        if (is_array($field)) {
            foreach ($field as $v) {
                if (empty($v)) {
                    continue;
                }
                $fieldString .= $v . ',';
            }
        }
        if ($fieldString) {
            $fieldString = rtrim($fieldString, ',');
        }
        else {
            $fieldString = $field;
        }
        return ($this->parentInfo = $this->dump($filter, $fieldString));
    }
    
    //格式化为时间戳
    protected function formatUnixTime($date)
    {
        if ($date == null || $date == '') {
            return '';
        }
        return strtotime($date);
    }
    
    //获得过滤容器错误信息
    protected function getFilterErrorMsg($key = '')
    {
        if ($key == '') {
            return $this->filterErrorMsg;
        }
        else {
            return $this->filterErrorMsg[$key];
        }
    }
    
    protected function checkIntAndTimeFilter($field, $nodeInfo)
    {
        $msg = '';
        $fieldType = $this->selectFilter[$field]['type'];
        $patten = '';
        switch ($fieldType) {
            case 'int':
                $patten = '/^[0-9]+$/';
                break;
            case 'time';
                $patten = '/^[0-9]{0,10}$/';
                break;
        }
        
        if ($patten == '') {
            return array('msg' => $this->getFilterErrorMsg('error_params'));
        }
        
        if ($nodeInfo['sign'] !== '') {
            if (preg_match($patten, $nodeInfo['min_val'])) {
                if ($nodeInfo['sign'] == 'between') {
                    if (preg_match($patten, $nodeInfo['max_val'])) {
                        $min_val = intval($nodeInfo['min_val']);
                        $max_val = intval($nodeInfo['max_val']);
                        if ($max_val < $min_val) {
                            $msg =  $this->selectFilter[$field]['label'] . $this->getFilterErrorMsg('node_between');
                        }
                    }
                    else {
                        $msg =  $this->selectFilter[$field]['label'] . $this->getFilterErrorMsg("must_{$fieldType}");
                    }
                }
            }
            else {
                $msg =  $this->selectFilter[$field]['label'] . $this->getFilterErrorMsg("must_{$fieldType}");
            }
        }
        elseif ($nodeInfo['min_val'] !== '' || $nodeInfo['max_val'] !== '') {
            $msg = $this->selectFilter[$field]['label'] . $this->getFilterErrorMsg('node_value');
        }
        
        return array('msg' => $msg);
    }
    
    protected function intAndTimeFilter($field, $nodeInfo, $parentInfo)
    {
        $msg = '';
        $nodeError = false;
        $checkIntFilter = $this->checkIntAndTimeFilter($field, $nodeInfo);
        if ($checkIntFilter['msg']) {
            return $checkIntFilter;
        }
        if ($parentInfo && '' != $parentInfo['sign']) {
            if ($nodeInfo['sign'] != $parentInfo['sign']) {
                return (array('msg' => $this->selectFilter[$field]['label'] . 
                    $this->getFilterErrorMsg('sign') . $this->selectFilterSignType['int'][$parentInfo['sign']]));
            }
            $error = false;
            switch ($nodeInfo['sign']) {
                case 'nequal':
                    if ($nodeInfo['min_val'] != $parentInfo['min_val']) {
                        $error = true;
                    }
                    break;
                case 'lthan':
                    if ($nodeInfo['min_val'] > $parentInfo['min_val']) {
                        $error = true;
                    }
                case 'sthan':
                    if ($nodeInfo['min_val'] > $parentInfo['min_val']) {
                        $error = true;
                    }
                    break;
                case 'than':
                    if ($nodeInfo['min_val'] <= $parentInfo['min_val']) {
                        $error = true;
                    }
                case 'bthan':
                    if ($nodeInfo['min_val'] < $parentInfo['min_val']) {
                        $error = true;
                    }
                    break;
                case 'between':
                    if ($nodeInfo['min_val'] > $nodeInfo['max_val']) {
                        $error = true;
                        $nodeError = true;
                    }
                    elseif ($nodeInfo['min_val'] < $parentInfo['min_val'] || $nodeInfo['max_val'] > $parentInfo['max_val']) {
                        $error = true;
                    }
                    break;
            }
            if ($error) {
                if ($this->selectFilter[$field]['type'] == 'time') {
                    $parentInfo['min_val'] = date("Y-m-d", $parentInfo['min_val']);
                    if ($nodeInfo['sign'] == 'between') {
                        $parentInfo['max_val'] = date("Y-m-d", $parentInfo['max_val']);
                    }
                }
                if ($nodeInfo['sign'] == 'between') {
                    $sign = $nodeInfo['sign'];
                    if ($nodeError) {
                        $sign = 'node_' . $sign;
                    }
                    $msg = $this->selectFilter[$field]['label'] . $this->getFilterErrorMsg($sign) .
                     ($nodeError ? '' : $parentInfo['min_val'] . '---' . $parentInfo['max_val']);
                }
                else {
                    $msg = $this->selectFilter[$field]['label'] . $this->getFilterErrorMsg($nodeInfo['sign']) . $parentInfo['min_val'];
                }
            }
        }
        return array('msg' => $msg);
    }
    
    protected function timeFilter($field, $nodeInfo, $parentInfo)
    {
        return $this->intAndTimeFilter($field, $nodeInfo, $parentInfo);
    }
    
    protected function intFilter($field, $nodeInfo, $parentInfo)
    {
        return $this->intAndTimeFilter($field, $nodeInfo, $parentInfo);
    }
    
    protected function checkSelectFilter($field, $value)
    {
        $msg = '';
        if ($value !== '') {
            $result = '';
            switch ($field) {
                case 'shop_evaluation':
                    $result = $this->getMembersAnalysisDbschema($field);
                    break;
                case 'lv_id':
                    $result = $this->getShopInfoRelating($field);
                    break;
                case 'good_buy_date':
                    $result = $this->selectFilterSignType['date'];
                break;
            }
            if (!array_key_exists($value, $result)) {
                $msg = $this->filterErrorMsg['error_params'];
            }
            elseif(empty($result)) {
                $msg = $this->filterErrorMsg['error_conditions_exist'];
            }
        }
        return array('msg' => $msg);
    }
    
    protected function getShopInfoRelating($field, $relatingValue = '')
    {
        $filter = array($field);
        if ($relatingValue) {
            array_push($filter, $relatingValue);
        }
        else {
            $relatingValue = $field;
        }
        $leveIds = $this->getShopleveInfo($this->nodeInfo['shop_id'], $filter);
        $array = array();
        foreach ($leveIds as $v) {
            $array[$v[$field]] = $v[$relatingValue];
        }
        return $array;
    }
    
    
    protected function getMembersAnalysisDbschema($fields = '', $nullRelationField = '')
    {
        $memberAnalysis = $this->app->model('member_analysis');
        $column = $memberAnalysis->get_schema();
        if ($fields && array_key_exists($fields, $column['columns'])) {
            return $column['columns'][$fields]['type'];
        }
        return '';
    }
    
    protected function getShopleveInfo($shop_id,  $fields= '*')
    {
        $filter = array();
        if ($shop_id != '') {
            $filter['shop_id']  = $shop_id;
        }
        
        $string = '';
        if (is_array($fields)) {
            foreach ($fields as $k => $v) {
                $string .= $v . ',';
            }
            $string = rtrim($string, ',');
        }
        elseif($fields != '') {
            $string = $fields;
        }
        else {
            $string = '*';
        }
        $ecorderApp = &app::get('ecorder');
        $shopLv = $ecorderApp->model('shop_lv');
        return $shopLv->getList($string, $filter);
    }
    
    protected function classFields($field, $relationField = '')
    {
        $vars = explode('-', $relationField);
        $tmpVar = '';
        if ($this->$vars[0]) {
            foreach ($vars as $var) {
                if ($var != '') {
                    if ($tmpVar == '') {
                        $tmpVar = $this->$var;
                    }
                    else {
                        $tmpVar = $tmpVar[$var];
                    }
                }
            }
        }
        return $tmpVar;
    }
    
    protected function getAbstractSelectTitle($field, $value, $relationField = '', $relationMethond = '')
    {
        if ($relationField == '') {
            $relationField = $this->selectFilter[$field]['relationField'];
        }
        if ($relationMethond == '') {
            $relationMethond = $this->selectFilter[$field]['relationMethond'];
        }
        $result = $this->$relationMethond($field, $relationField);
//        if ($relationField != 'db_schema') {
//            $result = $this->$relationMethond($field, $relationField);
//        }
//        else {
//            $result = $this->$relationMethond($field);
//        }
        
        if ($relationField == '' || $relationMethond == '') {
            return '';
        }
        else {
            return $result[$value];
            
        }
    }
    
    protected function selectFilter($field, $nodeValue, $parenttValue)
    {
        $checkSelectFilter = $this->checkSelectFilter($field, $nodeValue);
        if ($checkSelectFilter['msg']) {
            return $checkSelectFilter;
        }
        $msg = '';
        if ($parenttValue) {
            if ($parenttValue != $nodeValue) {
                if ($this->selectFilter[$field]['relationField'] && $this->selectFilter[$field]['relationMethond']) {
                    $parenttValue = $this->getAbstractSelectTitle($field, $parenttValue);
                }
                $msg = $this->selectFilter[$field]['label'] . $this->filterErrorMsg['select'] . $parenttValue;
            }
        }
        return array('msg' => $msg);
    }
    
    //检查数组
    protected function arrayFilter($field, $nodeValue, $parentValue)
    {
        if (is_array($parentValue) && $parentValue[0] !== '') {
            $gather = $this->selectFilter[$field];
            $noExistLocal = array();
            if (empty($nodeValue)) {
                $msg .= $this->selectFilter[$field]['label'] . $this->filterErrorMsg['local_empty'];
                $noExistLocal = $parentValue;
            }
            if ($msg == '') {
                foreach ($nodeValue as $nk => $nv) {
                    $find = false;
                    foreach ($parentValue as $pk => $pv) {
                        if ($pv == $nv) {
                            $find = true;
                            unset($parentValue[$pk]);
                            break;
                        }
                    }
                    if (!$find) {
                        array_push($noExistLocal, $nv);
                        $find = false;
                    }
                }
                if (!empty($noExistLocal)) {
                    $msg .= $this->selectFilter[$field]['label'] . $this->filterErrorMsg['error_choise'];
                }
            }
            
            if (!empty($noExistLocal)) {
                    foreach ($noExistLocal as $k) {
                        $strLink .= $gather[$k] .'、';
                    }
                    $strLink = rtrim($strLink, '、');
                    $msg .= $strLink; 
             }
            
            
        }
        return array('msg' => $msg);
    }
    
    //重构   检测过滤条件是否和父分组冲突
    public function validateFilter(&$data, &$error_msg = '')
    {
        $parentId = isset($data['parent_id']) ? $data['parent_id'] : 0;
        $parentFilter = '';
        if ($parentId) {
            $parentFilter = $this->getParentGroupInfo($parentId, 'filter');
            $parentFilter = unserialize($parentFilter['filter']);
        }
        //日期转化
        foreach ($data['filter'] as $k => &$v) {
            if (isset($this->selectFilter[$k]) && $this->selectFilter[$k]['type'] == 'time') {
                $v['min_val'] = $this->formatUnixTime($v['min_val']);
                $v['max_val'] = $this->formatUnixTime($v['max_val']);
            }
            if (is_array($v) && isset($v['sign']) && $v['sign']!= 'between') {
                $v['max_val'] = '';
            }
            
            if (isset($this->selectFilter[$k]) && $this->selectFilter[$k]['type'] == 'array') {
                $v = array_unique($v);
            }
        }
        
        if (!isset($data['filter']['regions_id'])) {
            $data['filter']['regions_id'] = '';
        }
        
        if (!isset($data['filter']['goods_id'])) {
            $data['filter']['goods_id'] = '';
        }
        $this->nodeInfo = $data;
        //检查验证
        foreach ($data['filter'] as $k => $m) {
            if (array_key_exists($k, $this->selectFilter) && $this->selectFilter[$k]['func'] != '') {
                $func = $this->selectFilter[$k]['func'];
                $result = $this->$func($k, $m, $parentFilter[$k]);
                if ($result['msg']) {
                    $error_msg = $result['msg'];
                    break;
                }
            }
        }
        if ($error_msg) {
            return false;
        }
        return true;
    } 
    
    

    // 检测过滤条件是否和父分组冲突
//    function validateFilter(&$data,&$err_msg=''){
//        //将日期转换为时间戳 
//        if($data['filter']['last_buy_time']['min_val'])
//            $data['filter']['last_buy_time']['min_val'] = strtotime($data['filter']['last_buy_time']['min_val']);
//        if($data['filter']['last_buy_time']['max_val'])
//            $data['filter']['last_buy_time']['max_val'] = strtotime($data['filter']['last_buy_time']['max_val']);
//        if($data['filter']['birthday']['min_val'])
//            $data['filter']['birthday']['min_val'] = strtotime($data['filter']['birthday']['min_val']);
//        if($data['filter']['birthday']['max_val'])
//            $data['filter']['birthday']['max_val'] = strtotime($data['filter']['birthday']['max_val']);
//
//        $select_filter = array(
//            'finish_orders' => '购买次数',
//            'total_amount' => '订单总金额',          
//            'buy_freq' => '购买频次',          
//            'buy_products' => '购买商品总数',          
//            'last_buy_time' => '最后下单日期',          
//            'points' => '积分范围', 
//            'birthday' => '生日范围',
//        );
//        //父分组的过滤条件
//        if(!$data['parent_id']) return true;//不存在父分组
//        $rs = $this->dump(array('group_id'=>$data['parent_id']));
//        $parent_filter = unserialize($rs['filter']);
//
//        foreach($data['filter'] as $k=>$v) {
//            $vv = $parent_filter[$k];
//            if($vv['sign'] && $v['sign']){
//
//                if($vv['sign']=='nequal') {
//                    if($v['sign']!='nequal' || $vv['min_val']!=$v['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突1！";
//                        return false;
//                    }
//                }
//
//                elseif($vv['sign']=='than') {
//                    if(($v['sign']=='lthan'||$v['sign']=='nequal'||$v['sign']=='sthan') && $v['min_val']<=$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突2！";
//                        return false;
//                    }
//                    // 组合过滤条件
//                    if(($v['sign']=='lthan'||$v['sign']=='sthan') && $v['min_val']>$vv['min_val']) {
//                        $data['filter'][$k]['sign'] = 'between';
//                        $data['filter'][$k]['min_val'] = $vv['min_val'];
//                        $data['filter'][$k]['max_val'] = $v['min_val'];
//                    }
//                    if(($v['sign']=='than'||$v['sign']=='bthan') && $v['min_val']<$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突3！";
//                        return false;
//                    }
//                    if(($v['sign']=='between') && $v['max_val']<=$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突4！";
//                        return false;
//                    }
//                }
//
//                elseif($vv['sign']=='bthan') {
//                    if(($v['sign']=='lthan'||$v['sign']=='nequal'||$v['sign']=='sthan') && $v['min_val']<$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突5！";
//                        return false;
//                    }
//                    // 组合过滤条件
//                    if(($v['sign']=='lthan'||$v['sign']=='sthan') && $v['min_val']>$vv['min_val']) {
//                        $data['filter'][$k]['sign'] = 'between';
//                        $data['filter'][$k]['min_val'] = $vv['min_val'];
//                        $data['filter'][$k]['max_val'] = $v['min_val'];
//                    }
//                    if(($v['sign']=='between') && $v['max_val']<$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突6！";
//                        return false;
//                    }
//                }
//
//                elseif($vv['sign']=='lthan') {
//                    if(($v['sign']=='than'||$v['sign']=='nequal'||$v['sign']=='bthan') && $v['min_val']>=$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突7！";
//                        return false;
//                    }
//
//                    // 组合过滤条件
//                    if(($v['sign']=='than'||$v['sign']=='bthan') && $v['min_val']<$vv['min_val']) {
//                        $data['filter'][$k]['sign'] = 'between';
//                        $data['filter'][$k]['min_val'] = $v['min_val'];
//                        $data['filter'][$k]['max_val'] = $vv['min_val'];
//                    }
//
//                    if(($v['sign']=='between') && $v['min_val']>=$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突8！";
//                        return false;
//                    }
//                }
//
//                elseif($vv['sign']=='sthan') {
//                    if(($v['sign']=='than'||$v['sign']=='nequal'||$v['sign']=='bthan') && $v['min_val']>$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突9！";
//                        return false;
//                    }
//
//                    // 组合过滤条件
//                    if(($v['sign']=='than'||$v['sign']=='bthan') && $v['min_val']<$vv['min_val']) {
//                        $data['filter'][$k]['sign'] = 'between';
//                        $data['filter'][$k]['min_val'] = $v['min_val'];
//                        $data['filter'][$k]['max_val'] = $vv['min_val'];
//                    }
//
//                    if(($v['sign']=='between') && $v['min_val']>$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突10！";
//                        return false;
//                    }
//                }
//
//                elseif($vv['sign']=='between') {
//                    if(($v['sign']=='lthan'||$v['sign']=='nequal'||$v['sign']=='sthan') && $v['min_val']<=$vv['min_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突11！";
//                        return false;
//                    }
//
//                    // 组合过滤条件
//                    if(($v['sign']=='lthan'||$v['sign']=='sthan') && $v['min_val']>=$vv['min_val']) {
//                        $data['filter'][$k]['sign'] = 'between';
//                        $data['filter'][$k]['min_val'] = $vv['min_val'];
//                        $data['filter'][$k]['max_val'] = $v['min_val'];
//                    }
//
//                    if(($v['sign']=='than'||$v['sign']=='nequal'||$v['sign']=='bthan') && $v['min_val']>=$vv['max_val']) {
//                        $err_msg = $select_filter[$k]." 条件冲突12！";
//                        return false;
//                    }
//
//                    // 组合过滤条件
//                    if(($v['sign']=='than'||$v['sign']=='bthan') && $v['min_val']<=$vv['max_val']) {
//                        $data['filter'][$k]['sign'] = 'between';
//                        $data['filter'][$k]['min_val'] = $v['min_val'];
//                        $data['filter'][$k]['max_val'] = $vv['max_val'];
//                    }
//
//                    if(($v['sign']=='between') && ($v['max_val']<$vv['min_val'] || $v['min_val']>$vv['max_val'])) {
//                        $err_msg = $select_filter[$k]." 条件冲突13！";
//                        return false;
//                    }
//                }
//            }
//        }
//        return true;
//    }

    // 将过滤条件转换为SQL语句
    public function buildFilterSQL($filter,$shop_id){

        $sign = array('than' => '>','lthan' => '<','nequal' => '=','sthan' => '<=','bthan' => '>=');
        $sql = 'SELECT DISTINCT(a.member_id) FROM sdb_taocrm_member_analysis AS a';

        if($filter['birthday'] || $filter['regions_id'])
        $sql .= ' LEFT JOIN sdb_taocrm_members AS b on a.member_id=b.member_id';

//        if($filter['goods_id'])
//        $sql .= ' LEFT JOIN sdb_ecorder_member_products AS c on a.member_id=c.member_id';
        if (isset($filter['goods_id'])) {
            $sql .= ' LEFT JOIN sdb_ecorder_orders AS d on a.member_id = d.member_id';
            $sql .= ' LEFT JOIN sdb_ecorder_order_items AS c on d.order_id = c.order_id';
        }

        $sql .= " WHERE a.shop_id='$shop_id' ";

        foreach($filter as $k=>$v) {
            switch($k){
                case 'birthday'://出生年月
                    if($v['sign']=='between') {
                        if(!$v['min_val'] || !$v['max_val']) break;
                        if(!is_numeric($v['min_val'])) $v['min_val'] = strtotime($v['min_val']);
                        if(!is_numeric($v['max_val'])) $v['max_val'] = strtotime($v['max_val']);
                        $sql .= ' AND (b.'.$k.' BETWEEN '.$v['min_val'].' AND '.$v['max_val'].' )';
                    }elseif($v['sign']){
                        if(!$v['min_val']) break;
                        if(!is_numeric($v['min_val'])) $v['min_val'] = strtotime($v['min_val']);
                        $sql .= ' AND (b.'.$k.' '.$sign[$v['sign']].' '.$v['min_val'].' )';
                    }
                    break;

                case 'lv_id'://客户等级
                case 'evaluation'://客户评价
                case 'f_level':
                    if($k=='evaluation') $k='shop_evaluation';
                    if($v != '') $sql .= " AND a.$k = '$v' ";
                    break;

                case 'goods_id'://购买过商品的客户
                    if($v){
//                        $where = '';
//                        $good_buy_date = intval($filter['good_buy_date']);
//                        $min_good_num = intval($filter['min_good_num']);
//                        if($good_buy_date>0) {
//                            $min_createtime = strtotime("-$good_buy_date days");
//                            $sql .= " AND (c.last_time>=$min_createtime) ";
//                        }
//                        if($min_good_num>0) {
//                            $sql .= " AND (c.buy_num>$min_good_num) ";
//                        }

                        $sql .= " AND (c.goods_id in (".implode(',',$v).")) ";
                    }
                    break;
                    	
                case 'regions_id'://所属地区
                    if(!$v) break;
                    $sql .= " AND (b.state in (".implode(',',$v).")) ";
                    break;

                case 'shop_id':
                case 'good_buy_date':
                    $good_buy_date = intval($filter['good_buy_date']);
                    if (0 < $good_buy_date) {
                        $dateTime = 86400;
                        $todayTime= strtotime(date('Y-m-d'));
                        $finalTime = $todayTime - $good_buy_date * $dateTime;
                        $sql .= " AND (a.last_buy_time >= $finalTime) ";
                    }
                    break;
                case 'min_good_num':
                case 'in_blacklist':
                case 'is_vip':
                    break;

                default://购买行为
                    if($v['sign']=='between') {
                        if(!$v['min_val'] || !$v['max_val']) break;
                        if($k=='last_buy_time'){
                            if(!is_numeric($v['min_val'])) $v['min_val'] = strtotime($v['min_val']);
                            if(!is_numeric($v['max_val'])) $v['max_val'] = strtotime($v['max_val']);
                        }
                        $sql .= ' AND (a.'.$k.' BETWEEN '.$v['min_val'].' AND '.$v['max_val'].' )';
                    }elseif($v['sign']){
                        if(!$v['min_val']) break;
                        if($k=='last_buy_time'){
                            if(!is_numeric($v['min_val'])) $v['min_val'] = strtotime($v['min_val']);
                        }
                        $sql .= ' AND (a.'.$k.' '.$sign[$v['sign']].' '.$v['min_val'].' )';
                    }else{
                        break;
                    }
                    break;
            }
        }
        //echo('<pre>');print_r($sql);die();
        return $sql;
    }
    
    public function dateCovertTime(&$filter)
    {
        //日期转化
        foreach ($filter as $k => &$v) {
            if (isset($this->selectFilter[$k]) && $this->selectFilter[$k]['type'] == 'time') {
                if (strpos($v['min_val'], '-')) {
                    $v['min_val'] = $this->formatUnixTime($v['min_val']);
                }
                if (strpos($v['max_val'], '-')) {
                    $v['max_val'] = $this->formatUnixTime($v['max_val']);
                }
            }
            if (is_array($v) && isset($v['sign']) && $v['sign']!= 'between') {
                $v['max_val'] = '';
            }
        }
    }
    
    //重构过滤条件转换
    public function buildFilter($filter, $shop_id, $dbParams = 'array')
    {
        $this->dateCovertTime($filter);
        $this->filter = $filter;
        $this->shop_id = $shop_id;
        $class = array();
        foreach ($filter as $key => $value) {
            if (isset($this->selectFilter[$key])) {
                if ( ( is_array($value) && $value['min_val'] ) || ( is_string($value) && $value ) || (is_array($value) && $value[0] != '')) {
                    $class[$this->selectFilter[$key]['table']][$key] = $value;
                }
            }
        }
        $classCount = count($class);
        if ($classCount == 0) {
            return array('shop_id' => $shop_id);
        }
        $result = array();
        if ($classCount == 1 && $this->primaryTable == key($class)) {
            foreach ($class as $tableName => $fields) {
                $result[] = $this->getFinalFilter($fields, $dbParams);
            }
            $finalFilter = array();
            foreach ($result as $tableFileds) {
                foreach ($tableFileds as $k => $v) {
                    $finalFilter[$k] = $v;
                }
            }
            $finalFilter['shop_id'] = $shop_id;
            return $finalFilter;
        }
        else {
            if ($dbParams == 'array') {
                $sql = 'select count(DISTINCT `'.$this->primaryTable.'`.'. $this->onFiled .') as _count from `' . $this->primaryTable . '` ';
            }
            else {
                $sql = 'select distinct '. $dbParams . ' from `' . $this->primaryTable . '` ';
            }
            
            $leftJoin = '';
            $leftJoinState = false;
            foreach ($class as $tableName => $fields) {
                if ($tableName != $this->primaryTable) {
                    $leftJoin .= ' LEFT JOIN `' . $tableName . '`'.
                                ' ON `' . $this->primaryTable .'`.' . $this->onFiled . ' = `' . $tableName . '`.' . $this->onFiled;
                    $keyFields = key($fields);
                    if (isset($this->selectFilter[$keyFields]['secondTable']) && $this->selectFilter[$keyFields]['secondTable']) {
                        $secondTableName = $this->selectFilter[$keyFields]['secondTable'];
                        $secondPrimary = $this->selectFilter[$keyFields]['secondePrimary'];
                        $leftJoin .= ' LEFT JOIN `' . $secondTableName . '`' . ' ON `'
                                     . $secondTableName .'`.' . $secondPrimary . ' = `' . $tableName . '`.'.$secondPrimary;
                    }
                }
                $result[] = $this->getFinalFilter($fields);
            }
            
            $where = ' where 1 = 1 ';
            foreach ($result as $whereSql) {
                $where .= $whereSql;
            }
            $where .= " AND `{$this->primaryTable}`.shop_id = '{$shop_id}' ";
            return $sql . $leftJoin . $where;
        }
    }
    
    protected function getFinalFilter($fields, $condition = 'sql')
    {
        $formatFilter = array();
        foreach ($fields as $field => $value) {
            $formatFilter[] = $this->formatFinalFilter($field, $value);
        }
        if ($condition == 'sql') {
            $sql = '';
            foreach ($formatFilter as $filter) {
                $sql .= $filter['sql'];
            }
            return $sql;
        }
        elseif($condition == 'array') {
            $finalFilter = array();
            foreach ($formatFilter as $filter) {
                foreach ($filter['filter'] as $filed => $v) {
                    $finalFilter[$filed] = $v;
                }
            }
            return $finalFilter;
        }
        return null;
    }
    
    protected function formatFinalFilter($field, $value)
    {
        $table = $this->selectFilter[$field]['table'];
        return $this->setFilterData($field, $value, $table);
    }
    
    protected function setFilterData($field, $value, $tableName)
    {
        $type = $this->selectFilter[$field]['type'];
        switch ($type) {
            case 'time':
            case 'int':
                $result = $this->setIntAndTimeFilter($field, $value, $tableName);
                break;
            case 'select':
                $result = $this->setSelectFilter($field, $value, $tableName);
                break;
            case 'array':
                $result = $this->setArrayFilter($field, $value, $tableName);
                break;
            case 'timeselect':
                $result = $this->setTimeSelectFilter($field, $value, $tableName);
                break;
        }
        return $result;
    }
    
    protected function setTimeSelectFilter($field, $value, $tableName)
    {
        $fieldConf = $this->selectFilter[$field];
        $todayTime= strtotime(date('Y-m-d'));
        $data['sign'] = $fieldConf['sign'];
        $finalTime = '';
        $dateTime = 86400;
        switch ($data['sign']) {
            case 'bthan':
                $finalTime = $todayTime - $value * $dateTime;
                break;
        }
        if (isset($this->selectFilter[$field]['alias']) && $this->selectFilter[$field]['alias']) {
            $field = $this->selectFilter[$field]['alias'];
        }
        $data['min_val'] = $finalTime;
        $set = $this->setIntAndTimeFilter($field, $data, $tableName);
        return $set;
    }
    
    protected function setArrayFilter($field, $value, $tableName)
    {
        $oldField = $field;
        $cloneValue = $value;
        if (isset($this->selectFilter[$field]['alias']) && $this->selectFilter[$field]['alias']) {
            $field = $this->selectFilter[$field]['alias'];
        }
        if (is_array($value)) {
            $value = implode(',', $value);
            $value = rtrim($value);
        }
        if (isset($this->selectFilter[$field]['secondTable']) && $this->selectFilter[$field]['secondTable']) {
            $tableName = $this->selectFilter[$field]['secondTable'];
        }
//        $secondTable = '';
//        if (isset($this->selectFilter[$oldField]['secondTable'])) {
//            $secondTable = $this->selectFilter[$oldField]['secondTable'];
//            $tableArray = array();
//            if (strpos($secondTable, '-')) {
//                $tempTableArray = explode('-', $secondTable);
//                $tableDetail = array();
//                foreach ($tempTableArray as $k => $v) {
//                    if (strpos($v, ',')) {
//                        $tableDetail = explode(',', $v);
//                        $tableArray[$k]['table'] = $tableDetail[0];
//                        unset($tableDetail[0]);
//                        foreach ($tableDetail as $k1 => $v1) {
//                            $tableArray[$k]['field'][] = $v1;
//                        }
//                    }
//                    else {
//                        $tableArray[$k]['table'] = $v;
//                    }
//                }
//            }
//            else {
//                if (strpos(',', $secondTable)) {
//                    $tableDetail = explode(',', $secondTable);
//                    $tableArray[0]['table'] = $tableDetail[0];
//                    unset($tableDetail[0]);
//                    foreach ($tableDetail as $v) {
//                        $tableArray[0]['field'][] = $v;
//                    }
//                }
//                else {
//                    $tableArray[0]['table'] = $secondTable;
//                }
//            }
//            $select = 'SELECT ';
//            foreach ($tableArray as $k => $v) {
//                $table = $v['table'];
//                if (isset($v['field'])) {
//                    foreach ($v['field'] as $v1) {
//                        $select .= '`'.$table.'`.' . $v1 . ',';
//                    }
//                 }
//            }
//            $select = rtrim($select, ',');
//            $primaryTable = $tableArray[0]['table'];
//            $select .= ' FROM `' . $primaryTable . '`'; 
//            if (count($tableArray) > 1) {
//                $leftJoin = '';
//                unset($tableArray[0]);
//                foreach ($tableArray as $k => $v) {
//                    $leftJoin .= ' LEFT JOIN `' . $v['table'] . '` ON `' . $primaryTable . '`.' . $this->selectFilter[$oldField]['secondePrimary'] . ' = `' .
//                                 $v['table'] . '`.' .$this->selectFilter[$oldField]['secondePrimary'];
//                }
//            } 
//            $where .= ' where 1 = 1 ';
//            $where .=  ' AND `' . $primaryTable . '`.'. $oldField . ' IN (' . $value . ')';
//            if ($this->shop_id) {
//                $where .= ' AND `' . $primaryTable . '`.shop_id = ' . "'{$this->shop_id}'". ' ';
//            }
//            
//            $sql = $select .$leftJoin . $where;
//            $result = $this->db->select($sql);
//            $field = $this->selectFilter[$oldField]['primary'];
//            if ($result) {
//                $fieldsCount = count($result[0]);
//                if ($fieldsCount == 1) {
//                    $value = array();
//                    foreach ($result as $v) {
//                        $data[] = $v[$this->selectFilter[$oldField]['primary']];
//                    }
//                    $value = array_unique($data);
//                }
//                else {
//                    return $result;
//                }
//            }
//        }
        
        $set = array(
            'field' => $field,
            'sql' => ' AND ' . '`'.$tableName .'`.' .$field . ' IN (' .$value .')',
            'filter' => array($field => $cloneValue)
        );
        return $set;
    }
    
    protected function setSelectFilter($field, $value, $tableName)
    {
        $oldValue = $value;
        if (!is_numeric($value)) {
            $value = "'{$value}'";
        }
        $set = array(
            'field' => $field,
            'sql' => ' AND ' . '`'.$tableName .'`.' .$field . ' = ' . $value,
            'filter' => array($field => $oldValue)
        );
        return $set;
    }
    
    protected function setIntAndTimeFilter($field, $value, $tableName )
    {
        $set = array();
        $set['field'] = $field;
        switch ($value['sign']) {
            case 'nequal':
                $set['sql'] .=  ' AND '. '`'.$tableName .'`.' . $field . ' = ' . $value['min_val'];
                $set['filter'] = array($field => $value['min_val']);
                break;
            case 'sthan':
                $set['sql'] .= ' AND ' . '`'.$tableName .'`.' . $field . ' <= ' . $value['min_val'];
                $set['filter'] = array($field .'|' . $value['sign'] => $value['min_val']);
                break;
            case 'bthan':
                $set['sql'] .= ' AND ' . '`'.$tableName .'`.' . $field . ' >= ' . $value['min_val'];
                $set['filter'] = array($field .'|' . $value['sign'] => $value['min_val']);
                break;
            case 'between':
                $set['sql'] .= ' AND ' . '`'.$tableName .'`.' . $field . ' >= ' . $value['min_val'];
                $set['sql'] .= ' AND ' . '`'.$tableName .'`.' . $field . ' <= ' . $value['max_val'];
                $set['filter'] = array($field .'|' . $value['sign'] => array($value['min_val'], $value['max_val']));
                break;
            case 'than':
                $set['sql'].= ' AND ' . '`'.$tableName . '`.' . $field . ' > ' . $value['min_val'];
                $set['filter'] = array($field .'|' . $value['sign'] => $value['min_val']);
                break;
            case 'lthan':
                $set['sql'].= ' AND ' . '`'.$tableName . '`.' . $field . ' < ' . $value['min_val'];
                $set['filter'] = array($field .'|' . $value['sign'] => $value['min_val']);
                break;
        }
        return $set;
    }

    // 将过滤条件转换为标准filter
//    public function buildFilter($filter,$shop_id){
//
//        //$this->buildFilterSQL($filter,$shop_id);
//
//        $final_filter = array();
//        $final_filter['shop_id'] = $shop_id;
//
//        foreach($filter as $k=>$v) {
//            switch($k){
//                case 'birthday'://出生年月
//                    if($v['sign']=='between') {
//                        if(!$v['min_val'] || !$v['max_val']) break;
//                        //$v['min_val'] = strtotime($v['min_val']);
//                        //$v['max_val'] = strtotime($v['max_val']);
//                        $user_filter['birthday|between'] = array($v['min_val'],$v['max_val']);
//                    }elseif($v['sign']){
//                        if(!$v['min_val']) break;
//                        //$v['min_val'] = strtotime($v['min_val']);
//                        $user_filter['birthday|'.$v['sign']] = $v['min_val'];
//                    }else{
//                        break;
//                    }
//
//                    $oMember = $this->app->model('members');
//                    $rs = $oMember->getList('member_id',$user_filter);
//                    if($rs) {
//                        foreach($rs as $v){
//                            $birthday['member_id'][] = $v['member_id'];
//                        }
//                    }else{
//                        $final_filter['member_id'] = "0";
//                    }
//                    break;
//
//                case 'evaluation'://客户评价
//                    if($v) $final_filter[$k] = $v;
//                    break;
//
//                case 'lv_id'://客户等级
//                    if($v) $final_filter[$k] = $v;
//                    break;
//
//                case 'goods_id'://购买过商品的客户
//                    /*
//                    if($v){
//                        $where = '';
//                        $good_buy_date = intval($filter['good_buy_date']);
//                        $min_good_num = intval($filter['min_good_num']);
//                        if($good_buy_date>0) {
//                            $min_createtime = strtotime("-$good_buy_date days");
//                            $where = " and a.createtime>=$min_createtime ";
//                        }
//                        $sql = "select a.member_id,b.goods_id,sum(nums) as nums
//                        from sdb_ecorder_orders as a left join 
//                        sdb_ecorder_order_items as b on a.order_id=b.order_id
//                        where 
//                        a.shop_id = '$shop_id' AND b.shop_id = '$shop_id' AND
//                        b.goods_id in (".implode(',',$v).") $where
//                        group by a.member_id,b.goods_id
//                        ";
//                        $rs = kernel::database()->select($sql);
//                        if($rs) {
//                            foreach($rs as $v){
//                                if($v['nums']>$min_good_num){
//                                    $goods['member_id'][] = $v['member_id'];
//                                }
//                            }
//                            if(!$goods['member_id'])
//                            $final_filter['member_id'] = "0";
//                        }else{
//                            $final_filter['member_id'] = "0";
//                        }
//                    }*/
//                    if($v){
//                        $where = '';
//                        $good_buy_date = intval($filter['good_buy_date']);
//                        $min_good_num = intval($filter['min_good_num']);
//                        if($good_buy_date>0) {
//                            $min_createtime = strtotime("-$good_buy_date days");
//                            $where = " and last_time>=$min_createtime ";
//                        }
//                        $sql = "select member_id from sdb_ecorder_member_products
//                        where goods_id in (".implode(',',$v).") $where
//                        ";
//                        $rs = kernel::database()->select($sql);
//                        if($rs) {
//                            foreach($rs as $v){
//                                $goods['member_id'][] = $v['member_id'];
//                            }
//                            if(!$goods['member_id'])
//                            $final_filter['member_id'] = "0";
//                        }else{
//                            $final_filter['member_id'] = "0";
//                        }
//                    }
//                    break;
//
//                case 'last_buy_time'://最后购买时间
//                    if ($v){
//                        if($v['sign']=='between') {
//                            if(!$v['min_val'] || !$v['max_val']) break;
//                            //$v['min_val'] = strtotime($v['min_val']);
//                            //$v['max_val'] = strtotime($v['max_val']);
//                            $final_filter['last_buy_time|between'] = array($v['min_val'],$v['max_val']);
//                        }elseif($v['sign']){
//                            if(!$v['min_val']) break;
//                            //$v['min_val'] = is_string($v['min_val']) ? strtotime($v['min_val']) : $v['min_val'];
//                            $final_filter['last_buy_time|'.$v['sign']] = $v['min_val'];
//                        }
//                    }
//                    break;
//                    	
//                case 'regions_id'://所属地区
//                    if(!$v) break;
//                    $sql = "select member_id from sdb_taocrm_members
//                        where state in (".implode(',',$v).")
//                        ";
//                    $rs = kernel::database()->select($sql);
//                    //echo('<pre>');var_dump($sql);
//                    if($rs) {
//                        foreach($rs as $v){
//                            $state['member_id'][] = $v['member_id'];
//                        }
//                    }else{
//                        $final_filter['member_id'] = "0";
//                    }
//
//                    break;
//
//                case 'shop_id':
//                case 'good_buy_date':
//                case 'min_good_num':
//                case 'in_blacklist':
//                case 'is_vip':
//                    break;
//
//                default://购买行为
//                    if(!$v['min_val']) break;
//                    if($v['sign']=='between') {
//                        $final_filter[$k.'|between'] =
//                        array($v['min_val'],$v['max_val']);
//                    }elseif($v['sign']){
//                        $final_filter[$k.'|'.$v['sign']] = $v['min_val'];
//                    }
//                    break;
//            }
//        }
//
//        if($final_filter['member_id'] != '0') {
//            $valid_arr = array();
//            if($birthday['member_id']) $valid_arr = $birthday['member_id'];
//            if($goods['member_id']) $valid_arr = $goods['member_id'];
//            if($state['member_id']) $valid_arr = $state['member_id'];
//
//            if($valid_arr){
//                if(!$birthday['member_id']) $birthday['member_id'] = $valid_arr;
//                if(!$goods['member_id']) $goods['member_id'] = $valid_arr;
//                if(!$state['member_id']) $state['member_id'] = $valid_arr;
//
//                $final_filter['member_id'] = array_intersect($birthday['member_id'],$goods['member_id'],$state['member_id']);
//            }
//        }

//        //echo('<pre>');var_dump($state);
//        return $final_filter;
//    }

    // 根据分组id获取客户列表id
    public function getMemberList_m($params,$type='list'){
        if(is_numeric($params)) {
            $group_id = $params;
        }else{
            $group_id = $params['group_id'];
        }
        
        $data = $this->dump($group_id);
        $shop_id = $data['shop_id'];
        $filter = unserialize($data['filter']);
        $final_filter = $this->buildFilter($filter,$shop_id);

        if($type=='filter') return $final_filter;

        $oMemberAnalysis = $this->app->model('member_analysis');
        $page = $params['page'];
        $page_size = $params['plimit'];
        
        if (is_array($final_filter)) {
            $rs = $oMemberAnalysis->getList('member_id',$final_filter,$page*$page_size,$page_size);//var_dump($final_filter);
            if($rs){
                foreach($rs as $v){
                    $member_ids[] = $v['member_id'];
                }
            }
            $total = $oMemberAnalysis->count($final_filter);
        }
        else {
            $count = $this->db->select($final_filter, $shop_id);
            $total = $count[0]['_count'];
            if ($total > 0) {
                $dbParamsSql = $this->buildFilter($filter,$shop_id, '`'.$this->primaryTable .'`.'.$this->onFiled);
                $offset = $page * $page_size;
                $dbParamsSql .= " limit {$offset} , {$page_size}";
                $rs = $this->db->select($dbParamsSql);
                $member_ids = array();
                foreach ($rs as $v) {
                    $member_ids[] = $v['member_id'];
                }
            }
        }
        return array('member_id'=>$member_ids,'total'=>$total);
    }
    
    //获取客户列表
    public function getMemberList($params, $type='list')
    {
        if (is_numeric($params)) {
            $group_id = $params;
        }
        else {
            $group_id = $params['group_id'];
        }
        //获取客户自定义组数据
        $data = $this->dump($group_id);
        $shop_id = $data['shop_id'];
        $filter = unserialize($data['filter']);
        if ($type == 'filter') return $filter;
        $filter['shop_id'] = $shop_id;
        $page = $params['page'];
        $page_size = $params['plimit'];
        $connect = new taocrm_middleware_connect;
        $memberInfo = $connect->middleware_member_analysis_data($filter, $page, $page_size, false, true);
        $count = $memberInfo['count'];
        $memberList = array();
        foreach ($memberInfo['data'] as $v) {
            $memberList[] = $v['member_id'];
        }
        return array('member_id' => $memberList, 'total' => $count);
    }
    
    /**
     * 更新组客户数据
     *
     * @param array $condition
     */
    public function sync($condition){
        $where = ' WHERE shop_id = "' .$condition['shop_id'].'" ';

        if($condition['area']){
            $pos = strrpos($condition['area'], ':');
            $area = substr($condition['area'], 0, $pos);
            $where .= " AND area LIKE '{$area}%' ";
        }

        if($condition['member_lv']){
            $where .= " AND shop_lv_id = {$condition['member_lv']} ";
        }

        //订单总数
        if($condition['order_count']){

            if($condition['order_count_than'] == 'than'){
                $where .= " AND order_total_num >  {$condition['order_count']} ";
            } elseif ($condition['order_count_than'] == 'lthan'){
                $where .= " AND order_total_num <  {$condition['order_count']} ";
            } elseif ($condition['order_count_than'] == 'equal'){
                $where .= " AND order_total_num = {$condition['order_count']} ";
            } elseif ($condition['order_count_than'] == 'sthan'){
                $where .= " AND order_total_num <=  {$condition['order_count']} ";
            } elseif ($condition['order_count_than'] == 'bthan'){
                $where .= " AND order_total_num >=  {$condition['order_count']} ";
            }

        }

        //成功订单数
        if($condition['pay_count']){

            if($condition['pay_count_than'] == 'than'){
                $where .= " AND order_succ_num > {$condition['pay_count']} ";
            } elseif ($condition['pay_count_than'] == 'lthan'){
                $where .= " AND order_succ_num < {$condition['pay_count']} ";
            } elseif ($condition['pay_count_than'] == 'equal'){
                $where .= " AND order_succ_num = {$condition['pay_count']} ";
            } elseif ($condition['pay_count_than'] == 'sthan'){
                $where .= " AND order_succ_num <= {$condition['pay_count']} ";
            } elseif ($condition['pay_count_than'] == 'bthan'){
                $where .= " AND order_succ_num >= {$condition['pay_count']} ";
            }

        }

        //成功订单额
        if($condition['pay_total']){

            if($condition['pay_total_than'] == 'than'){
                $where .= " AND order_succ_amount > {$condition['pay_total']} ";
            } elseif ($condition['pay_total_than'] == 'lthan'){
                $where .= " AND order_succ_amount < {$condition['pay_total']} ";
            } elseif ($condition['pay_total_than'] == 'equal'){
                $where .= " AND order_succ_amount = {$condition['pay_total']} ";
            } elseif ($condition['pay_total_than'] == 'sthan'){
                $where .= " AND order_succ_amount <= {$condition['pay_total']} ";
            } elseif ($condition['pay_total_than'] == 'bthan'){
                $where .= " AND order_succ_amount >= {$condition['pay_total']} ";
            }

        }

        //下单时间
        switch ($condition['order_create_time_than']) {
            case 'than':
                $where .= $condition['createtime'] ? " AND order_first_time > {$condition['createtime']} " : '';
                break;

            case 'lthan':
                $where .= $condition['createtime'] ? " AND order_first_time < {$condition['createtime']} " : '';
                break;

            case 'equal':
                $where .= $condition['createtime'] ? " AND order_first_time = {$condition['createtime']} " : '';
                break;

            case 'between':
                if($condition['createtime_from'] && $condition['createtime_to']){
                    $where .= " AND order_first_time < {$condition['createtime_to']} ";
                    $where .= " AND order_first_time > {$condition['createtime_from']} ";
                }
                break;
        }

        //下单时间
        if($condition['order_last_time']){
            $now = time();
            if($condition['order_last_time'] == 1){
                //一个月内
                $where .= " AND order_last_time >= ".($now - 2592000). " AND order_last_time <= $now";
            } elseif ($condition['order_last_time'] == 7){
                //一个月前
                $where .= " AND order_last_time < ".($now - 5184000);
            } elseif ($condition['order_last_time'] == 2){
                //两个月内
                $where .= " AND order_last_time >= ".($now - 5184000). " AND order_last_time <= $now";
            } elseif ($condition['order_last_time'] == 8){
                //两个月前
                $where .= " AND order_last_time < ".($now - 5184000);
            } elseif ($condition['order_last_time'] == 3){
                //三个月内
                $where .= " AND order_last_time >= ".($now - 7776000). " AND order_last_time <= $now";
            } elseif ($condition['order_last_time'] == 9){
                //三个月前
                $where .= " AND order_last_time < ".($now - 7776000);
            } elseif ($condition['order_last_time'] == 6){
                //四个月内
                $where .= " AND order_last_time >= ".($now - 15552000). " AND order_last_time <= $now";
            } elseif ($condition['order_last_time'] == 10){
                //四个月前
                $where .= " AND order_last_time < ".($now - 15552000);
            }
        }

        //注册时间
        if($condition['reg_date']){
            $now = time();
            if($condition['reg_date'] == 1){
                //一个月内
                $where .= " AND regtime >= ".($now - 2592000). " AND regtime <= $now";
            } elseif ($condition['reg_date'] == 7){
                //一个月前
                $where .= " AND regtime < ".($now - 5184000);
            } elseif ($condition['reg_date'] == 2){
                //两个月内
                $where .= " AND regtime >= ".($now - 5184000). " AND regtime <= $now";
            } elseif ($condition['reg_date'] == 8){
                //两个月前
                $where .= " AND regtime < ".($now - 5184000);
            } elseif ($condition['reg_date'] == 3){
                //三个月内
                $where .= " AND regtime >= ".($now - 7776000). " AND regtime <= $now";
            } elseif ($condition['reg_date'] == 9){
                //三个月前
                $where .= " AND regtime < ".($now - 7776000);
            } elseif ($condition['reg_date'] == 6){
                //四个月内
                $where .= " AND regtime >= ".($now - 15552000). " AND regtime <= $now";
            } elseif ($condition['reg_date'] == 10){
                //四个月前
                $where .= " AND regtime < ".($now - 15552000);
            }
        }

        $sql = 'SELECT distinct member_id FROM sdb_taocrm_members '.$where;
        $data = kernel::database()->select($sql);

        if($data) {
            foreach($data as $v){
                $tmp[] = $v['member_id'];
            }
            $data = $tmp;
        }

        //标签
        if($data && $condition['tag']){
            $tagObj = &app::get('desktop')->model('tag_rel');
            $member_tags = $tagObj->getList('rel_id',array(
                'app_id' => 'taocrm',
                'tag_id' => $condition['tag']
            ));
            if($member_tags){
                foreach ($member_tags as $v){
                    $member_tag[] = $v['rel_id'];
                }
            }
            $data = array_intersect($member_tag, $data);
        }

        //保存相关联数据
        $member_group_data = $this->app->model('member_group_data');
        if($data){
            foreach($data as $v){
                $insert_data = array(
                    'group_id' => $condition['group_id'],
                    'member_id' => $v
                );
                $member_group_data->save($insert_data);
            }
        }
    }

    function pre_recycle($data){
        $groupDataObj = $this->app->model('member_group_data');
        $groupCount = $this->count(array('shop_id'=>$data[0]['shop_id']));
        if(count($data)>=$groupCount){
            $this->recycle_msg = '不能删除该店铺下所有分组！';
            return false;
        }
        foreach($data as $val){
            if(!$groupDataObj->delete(array('group_id'=>$val['group_id']))){
                $this->recycle_msg = '该分组下客户删除失败,此分组不能删除！';
                return false;
            }
        }
        return true;
    }

    //获取摸个分组中，客户的数量。
    function member_group($_POST){
        if (!empty($_POST['group_id'])) {
            $group_data = app::get('taocrm')->model('member_group')->getMemberList($group_id);
            return $group_data;
        }else {
            $memberanaly_obj = &app::get('taocrm')->model('member_analysis');
            $shopid_filter['shop_id']=$_POST['shop_id'];
            $group=$memberanaly_obj->getList('member_id',$shopid_filter);
            $group_data=array();
            foreach ($group as $k=>$v){
                $group_data[]=$v['member_id'];
            }
            return $group_data;
        }

    }

    public function gmBuildFilterSQL(&$filter,&$shop_id,&$active_id){
        if (is_array($filter)) {
            $this->dateCovertTime($filter);
        }
        if(!is_array($filter))$filter = array();
        $oderTable = false;
        $sign = array('than' => '>','lthan' => '<','nequal' => '=','sthan' => '<=','bthan' => '>=');
        $sql = "SELECT DISTINCT(a.member_id), $active_id as active_id,b.uname,b.name as truename,b.mobile FROM sdb_taocrm_member_analysis AS a";

        $sql .= ' LEFT JOIN sdb_taocrm_members AS b on a.member_id=b.member_id';

//        if(isset($filter['goods_id']))
//        $sql .= ' LEFT JOIN sdb_ecorder_member_products AS c on a.member_id=c.member_id';
        
        if (isset($filter['goods_id'])) {
            $oderTable = true;
            $sql .= ' LEFT JOIN sdb_ecorder_orders AS d on a.member_id = d.member_id';
            $sql .= ' LEFT JOIN sdb_ecorder_order_items AS c on d.order_id = c.order_id';
        }

        $sql .= " WHERE a.shop_id='$shop_id' ";

        foreach($filter as $k=>$v) {
            if (empty($v)) {
                continue;
            }
            switch($k){
                case 'birthday'://出生年月
                    if($v['sign']=='between') {
                        if(!$v['min_val'] || !$v['max_val']) break;
                        $sql .= ' AND (b.'.$k.' BETWEEN '.$v['min_val'].' AND '.$v['max_val'].' )';
                    }elseif($v['sign']){
                        if(!$v['min_val']) break;
                        $sql .= ' AND (b.'.$k.' '.$sign[$v['sign']].' '.$v['min_val'].' )';
                    }
                    break;

                case 'lv_id'://客户等级
                    $sql .= " AND (a.lv_id = {$v})";
                    break;
                case 'evaluation'://客户评价
                case 'f_level':
                    if($k=='evaluation') $k='shop_evaluation';
                    if($v != '') $sql .= " AND a.$k = '$v' ";
                    break;

                case 'goods_id'://购买过商品的客户
                    if($v){
//                        $where = '';
//                        $good_buy_date = intval($filter['good_buy_date']);
//                        $min_good_num = intval($filter['min_good_num']);
//                        if($good_buy_date>0) {
//                            $min_createtime = strtotime("-$good_buy_date days");
//                            $sql .= " AND (c.last_time>=$min_createtime) ";
//                        }
//                        if($min_good_num>0) {
//                            $sql .= " AND (c.buy_num>$min_good_num) ";
//                        }

                        $sql .= " AND (c.goods_id in (".implode(',',$v).")) ";
                    }
                    break;
                     
                case 'regions_id'://所属地区
                    if(!$v) break;
                    $sql .= " AND (b.state in (".implode(',',$v).")) ";
                    break;
                case 'good_buy_date':
                    $good_buy_date = intval($filter['good_buy_date']);
                    if (0 < $good_buy_date) {
                        $dateTime = 86400;
                        $todayTime= strtotime(date('Y-m-d'));
                        $finalTime = $todayTime - $good_buy_date * $dateTime;
                        $sql .= " AND (a.last_buy_time >= $finalTime) ";
                    }
                    break;
                case 'shop_evaluation':
                    $sql .= " AND (a.shop_evaluation = '{$v}')";
                    break;
                case 'shop_id':
                case 'min_good_num':
                case 'in_blacklist':
                case 'is_vip':
                    break;

                default://购买行为
                    if(isset($v['sign'])){
                        if($v['sign']=='between') {
                            if(!$v['min_val'] || !$v['max_val']) break;
                            $sql .= ' AND (a.'.$k.' BETWEEN '.$v['min_val'].' AND '.$v['max_val'].' )';
                        }elseif($v['sign']){
                            if(!$v['min_val']) break;
                            $sql .= ' AND (a.'.$k.' '.$sign[$v['sign']].' '.$v['min_val'].' )';
                        }else{
                            break;
                        }
                    }
                    break;
            }
        }
        //$sql .= ' GROUP BY a.member_id';
        //echo('<pre>');var_dump($sql);die();
        if ($sql) {
            $sql = "SELECT 
                       m.active_id,
                       m.member_id,
                       m.uname,
                       m.truename,
                       m.mobile
                     FROM (" . $sql . ") AS m";
        }
        return $sql;
    }

    public function gmEdmBuildFilterSQL(&$filter,&$shop_id,&$active_id){
        if (is_array($filter)) {
            $this->dateCovertTime($filter);
        }
        if(!is_array($filter))$filter = array();

        $sign = array('than' => '>','lthan' => '<','nequal' => '=','sthan' => '<=','bthan' => '>=');
        $sql = "SELECT DISTINCT(a.member_id), $active_id as active_id,b.uname,b.name as truename,b.email FROM sdb_taocrm_member_analysis AS a";

        //if($filter['birthday'] || $filter['regions_id'])
        $sql .= ' INNER JOIN sdb_taocrm_members AS b on a.member_id=b.member_id';

//        if(isset($filter['goods_id']))
//        $sql .= ' LEFT JOIN sdb_ecorder_member_products AS c on a.member_id=c.member_id';
        if (isset($filter['goods_id'])) {
            $sql .= ' LEFT JOIN sdb_ecorder_orders AS d on a.member_id = d.member_id';
            $sql .= ' LEFT JOIN sdb_ecorder_order_items AS c on d.order_id = c.order_id';
        }

        $sql .= " WHERE a.shop_id='$shop_id' ";

        foreach($filter as $k=>$v) {
            if (empty($v)) {
                continue;
            }
            switch($k){
                case 'birthday'://出生年月
                    if($v['sign']=='between') {
                        if(!$v['min_val'] || !$v['max_val']) break;
                        $sql .= ' AND (b.'.$k.' BETWEEN '.$v['min_val'].' AND '.$v['max_val'].' )';
                    }elseif($v['sign']){
                        if(!$v['min_val']) break;
                        $sql .= ' AND (b.'.$k.' '.$sign[$v['sign']].' '.$v['min_val'].' )';
                    }
                    break;
                case 'lv_id'://客户等级
                    $sql .= " AND (a.lv_id = {$v})";
                    break;
                case 'evaluation'://客户评价
                case 'f_level':
                    if($k=='evaluation') $k='shop_evaluation';
                    if($v != '') $sql .= " AND a.$k = '$v' ";
                    break;

                case 'goods_id'://购买过商品的客户
                    if($v){
//                        $where = '';
//                        $good_buy_date = intval($filter['good_buy_date']);
//                        $min_good_num = intval($filter['min_good_num']);
//                        if($good_buy_date>0) {
//                            $min_createtime = strtotime("-$good_buy_date days");
//                            $sql .= " AND (c.last_time>=$min_createtime) ";
//                        }
//                        if($min_good_num>0) {
//                            $sql .= " AND (c.buy_num>$min_good_num) ";
//                        }

                        $sql .= " AND (c.goods_id in (".implode(',',$v).")) ";
                    }
                    break;
                     
                case 'regions_id'://所属地区
                    if(!$v) break;
                    $sql .= " AND (b.state in (".implode(',',$v).")) ";
                    break;
                case 'good_buy_date':
                    $good_buy_date = intval($filter['good_buy_date']);
                    if (0 < $good_buy_date) {
                        $dateTime = 86400;
                        $todayTime= strtotime(date('Y-m-d'));
                        $finalTime = $todayTime - $good_buy_date * $dateTime;
                        $sql .= " AND (a.last_buy_time >= $finalTime) ";
                    }
                    break;
                case 'shop_evaluation':
                    $sql .= " AND (a.shop_evaluation = '{$v}')";
                    break;
                case 'shop_id':
                case 'min_good_num':
                case 'in_blacklist':
                case 'is_vip':
                    break;

                default://购买行为
                    if(isset($v['sign'])){
                        if($v['sign']=='between') {
                            if(!$v['min_val'] || !$v['max_val']) break;
                            $sql .= ' AND (a.'.$k.' BETWEEN '.$v['min_val'].' AND '.$v['max_val'].' )';
                        }elseif($v['sign']){
                            if(!$v['min_val']) break;
                            $sql .= ' AND (a.'.$k.' '.$sign[$v['sign']].' '.$v['min_val'].' )';
                        }else{
                            break;
                        }
                    }
                    break;
            }
        }
        //$sql .= ' GROUP BY a.member_id';
        //echo('<pre>');var_dump($sql);die();
        if ($sql) {
            $sql = "SELECT 
                       m.active_id,
                       m.member_id,
                       m.uname,
                       m.truename,
                       m.email
                     FROM (" . $sql . ") AS m";
        }
        return $sql;
    }
}