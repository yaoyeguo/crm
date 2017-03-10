<?php
class taocrm_service_group {

    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array ();
    protected $init_arr = array ();
    protected $form_temp = array ();
    protected $interfacePacketName = '';
    protected $interfaceMethodName = '';
    protected $interfaceTableName = '';

    function __construct() {
        $this->app = app::get ( 'taocrm' );
        $this->init_arr = $this->get_init_arr();
        $this->form_temp = $this->get_form_arr();
        $this->interfacePacketName = 'ShopMemberAnalysis';
        $this->interfaceMethodName = 'SearchMemberAnalysisList';
        $this->interfaceTableName = 'taocrm_mdl_middleware_member_analysis';
    }
    
    public function init_group($params = array())
    {
        if(isset($params['shop_id']) && !empty($params['shop_id']))
        {
            $this->shop_id = $params['shop_id'];
            $this->init_group_insert($this->init_arr);
        }elseif(empty($params))
        {
            $this->_shopInfo = $this->fetchShopInfo();
            foreach($this->_shopInfo as $shop)
            {
                $this->shop_id = $shop['shop_id'];
                $this->init_group_insert($this->init_arr);
            }
        }
    }

    public function get_full_data($init_data)
    {
        switch($this->_type)
        {
            case 'regions':
                $this->regions_list = $this->regions_list ? $this->regions_list : app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('region_grade'=>1,'region_id|sthan'=>3266));
                if($this->regions_list){
                    foreach($this->regions_list as $v){
                        if(!$v['group_name']) $v['group_name'] = '其它';
                        //$regions[$v['group_name']][$v['region_id']] = $v['local_name'];
                        if($v['group_name'] == $init_data['group_name'] || $v['local_name'] == $init_data['group_name'])
                        {
                            $regions_ids[] = $v['region_id'];
                        }
                    }
                    if($init_data['chk_all'] == 2)  $init_data['filter']['regions_id'] = $regions_ids;
                }
                break;
        }
        return $init_data;
    }

    public function merge_data($temp,$arr)
    {
        foreach($arr as $k => $d)
        {
            if(is_array($d))
                $d = $this->merge_data($temp[$k],$d);
            $temp[$k] = $d;
        }
        return $temp;
    }

    public function init_group_insert($init_arr,$pid=0)
    {
        foreach($init_arr as $d)
        {
            $filter['parent_id'] = 0;
            $filter['shop_id'] = $this->shop_id;
            $filter['group_name'] = $d['group_name'];
            $filter['create_type'] = 'system';
            $has_groups = $this->app->model('member_group')->dump($filter,'group_id', 0, -1, 'group_id ASC');
            if($has_groups)
            {
                continue;
            }

            $d_c = isset($d['childs']) ? $d['childs'] : false;
            $this->_type = $d['type'] ? $d['type'] : $this->_type;
            if(isset($d['childs']))unset($d['childs']);
            if(isset($d['type']))unset($d['type']);

            $form_data = $this->merge_data($this->form_temp,$d);
            $pid && $form_data = array_merge($form_data,array('parent_id'=>$pid));
            $form_data = $this->get_full_data($form_data);
            $p_id = $this->save_group_for_init($form_data);
            if($d_c && $p_id)
            {
                $d_c = array_reverse($d_c);
                $this->init_group_insert($d_c,$p_id);
            }
        }
    }

    private function save_group_for_init($data){
        $oGroup= $this->app->model('member_group');
        $group_id = isset ($data['group_id']) && intval($data['group_id']) > 0 ? intval($data['group_id']) : 0;
        if(!$data['group_name'] || $data['group_name']==''){
            return false;
        }
        $op_user = kernel::single('desktop_user')->get_name();

        //验证条件是否冲突
        if($oGroup->validateFilter($data,$err_msg)==false){
            return false;
        }

        if(isset($data['filter']['goods_id']) && $data['filter']['goods_id'])
            $data['filter']['goods_id'] = array_unique($data['filter']['goods_id']);

        if(isset($data['filter']['chk_goods_id']) && $data['filter']['chk_goods_id']==2){
            if(isset($data['filter']['good_name']) && $data['filter']['good_name']){

                $good_name_sign = $data['filter']['good_name_sign'];
                $good_name = $data['filter']['good_name'];
                $good_name2 = $data['filter']['good_name2'];

                if($good_name_sign != 'or') $good_name_sign='and';

                $data['filter']['goods_id'] = array();

                $sql = "select goods_id from sdb_ecgoods_shop_goods where shop_id='".$data['shop_id']."' and (name like '%$good_name%' ";
                if($good_name2)
                    $sql .= " $good_name_sign name like '%$good_name2%' ";
                $sql .= ')';
                $goods_id_list = kernel::database()->select($sql);
                foreach($goods_id_list as $v){
                    $data['filter']['goods_id'][] = $v['goods_id'];
                }
                $data['filter']['goods_id'] = array_unique($data['filter']['goods_id']);
            }
        }
        #  获得客户数量
        $middlewareContent = kernel::single('taocrm_middleware_connect');
        $tableName = $this->interfaceTableName;
        $data['packetName'] = $this->interfacePacketName;
        $data['methodName'] = $this->interfaceMethodName;

        $countMembers = $middlewareContent->count($tableName, $data);
        if($group_id){
            $filter = array(
                'group_id' => $group_id,
            );
            $ret = $oGroup->update(array(
                'members' => $countMembers,
                'op_user' => $op_user,
                'group_name' => $data['group_name'],
                'group_content' => $data['group_content'],
                'shop_id' => $data['shop_id'],
                'parent_id' => intval($data['parent_id']),
                'update_time' => time(),
                'filter' => serialize($this->serialize_condition($data))
            ),$filter);
        } else {
            $time = time();
            $arr_data = array(
                'members' => $countMembers,
                'op_user' => $op_user,
                'group_name' => $data['group_name'],
                'group_content' => $data['group_content'],
                'shop_id' => $this->shop_id,
                'parent_id' => intval($data['parent_id']),
                'filter' => serialize($this->serialize_condition($data)),
                'create_time' => $time,
                'update_time' => $time,
                'create_type' => 'system',
            );
            $group_id = $oGroup->insert($arr_data);
        }

        if($data['parent_id']>0) {
            $this->countChilds($data['parent_id']);
        }

        if($group_id){
            return $group_id;
        }else{
            return false;
        }
    }
    
    private function get_form_arr()
    {
        return array(
            "shop_id"=> '',
            "group_id"=> '',
            "parent_id"=> '',
            "group_name"=> '',
            "group_content"=> '',
            "filter"=> array(
                "total_orders"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "finish_orders"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "total_amount"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "total_per_amount"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "buy_freq"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "avg_buy_interval"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "buy_month"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "buy_products"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "finish_total_amount"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "finish_per_amount"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "unpay_orders"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "refund_orders"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "refund_amount"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "create_time"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "sex"=> '',
                "points"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "birthday"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "lv_id"=> '',
                "f_level"=> '',
                "is_vip"=> "false",
                "in_blacklist"=> "false",
                "first_buy_time"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "last_buy_time"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "active_times"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "active_buy_times"=> array(
                    "sign"=> '',
                    "min_val"=> '',
                    "max_val"=> '',
                ),
                "good_name"=> '',
                "good_name_sign"=> '',
                "good_name2"=> '',
                "good_bn"=> '',
                "chk_goods_id"=> '',
            ),
            "_DTYPE_DATE"=> array(
                0=> "filter[create_time][min_val]",
                1=> "filter[create_time][max_val]",
                2=> "filter[birthday][min_val]",
                3=> "filter[birthday][max_val]",
                4=> "filter[first_buy_time][min_val]",
                5=> "filter[first_buy_time][max_val]",
                6=> "filter[last_buy_time][min_val]",
                7=> "filter[last_buy_time][max_val]",
            ),
            "_DTYPE_BOOL"=> array(
                0=> "filter[is_vip]",
                1=> "filter[in_blacklist]",
            ),
            "chk_all"=> "1",
        );
    }
    
    private function get_init_arr()
    {
        $init_data = array(
            array(
                'type' => 'ave_amount',
                'group_name' => '付款订单的平均金额',
                'childs' => array(
                    array(
                        'group_name' => '0-50',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'between',
                                'min_val'=>'0',
                                'max_val'=>'50',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '50-100',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'between',
                                'min_val'=>'50',
                                'max_val'=>'100',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '100-200',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'between',
                                'min_val'=>'100',
                                'max_val'=>'200',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '200-300',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'between',
                                'min_val'=>'200',
                                'max_val'=>'300',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '300-500',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'between',
                                'min_val'=>'300',
                                'max_val'=>'500',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '500-1000',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'between',
                                'min_val'=>'500',
                                'max_val'=>'1000',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '1000-2000',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'between',
                                'min_val'=>'1000',
                                'max_val'=>'2000',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '2000以上',
                        'filter' => array(
                            'finish_per_amount'=>array(
                                'sign'=>'bthan',
                                'min_val'=>'2000',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'count_buy_time',
                'group_name' => '客户累计购买频次',
                'childs' => array(
                    array(
                        'group_name' => '1',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'1',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '2',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'2',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '3',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'3',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '4',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'4',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '5',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'5',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '6',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'6',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '7',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'7',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '8',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'8',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '9',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'9',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '10',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'nequal',
                                'min_val'=>'10',
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '10次以上',
                        'filter' => array(
                            'buy_freq'=>array(
                                'sign'=>'bthan',
                                'min_val'=>'11',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'regions',
                'group_name' => '地区',
                'chk_all' => 1,
                'childs' => array(
                    array(
                        'group_name' => '华北',
                        'chk_all' => 2,
                        'childs' => array(
                            array(
                                'group_name' => '北京',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '天津',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '河北',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '内蒙古',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '山东',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '山西',
                                'chk_all' => 2,
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '华东',
                        'chk_all' => 2,
                        'childs' => array(
                            array(
                                'group_name' => '上海',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '安徽',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '江苏',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '浙江',
                                'chk_all' => 2,
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '西南',
                        'chk_all' => 2,
                        'childs' => array(
                            array(
                                'group_name' => '重庆',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '福建',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '广东',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '广西',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '贵州',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '海南',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '四川',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '西藏',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '云南',
                                'chk_all' => 2,
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '西北',
                        'chk_all' => 2,
                        'childs' => array(
                            array(
                                'group_name' => '甘肃',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '宁夏',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '青海',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '陕西',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '新疆',
                                'chk_all' => 2,
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '中南',
                        'chk_all' => 2,
                        'childs' => array(
                            array(
                                'group_name' => '河南',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '湖北',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '湖南',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '江西',
                                'chk_all' => 2,
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '东北',
                        'chk_all' => 2,
                        'childs' => array(
                            array(
                                'group_name' => '黑龙江',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '吉林',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '辽宁',
                                'chk_all' => 2,
                            ),
                        ),
                    ),
                    array(
                        'group_name' => '港澳台',
                        'chk_all' => 2,
                        'childs' => array(
                            array(
                                'group_name' => '香港',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '澳门',
                                'chk_all' => 2,
                            ),
                            array(
                                'group_name' => '台湾',
                                'chk_all' => 2,
                            ),
                        ),
                    ),
                ),
            ),
        );
        
        //日期数据处理
        $group_date['year'] = date('Y',time());
        $group_date['mon'] = date('m',time());
        $years = range(2013,$group_date['year']);
        $this_mon = range(1,$group_date['mon']);
        $date_arr = array('group_name'=>'客户下单时间','childs'=>'');
        foreach($years as $k => $year)
        {
            $date_arr['childs'][$k]['group_name'] = $year;
            $date_arr['childs'][$k]['filter']['create_time'] = array(
                'sign' => 'between',
                'min_val' => $year.'-1-1 00:00:00',
                'max_val' => ($year+1).'-1-1 00:00:00'
            );
            if($year == $group_date['year']){
                foreach($this_mon as $mk=>$mon)
                {
                    $date_arr['childs'][$k]['childs'][$mk]['group_name'] = $mon;
                    $date_arr['childs'][$k]['childs'][$mk]['filter']['create_time']['sign'] = 'between';
                    $date_arr['childs'][$k]['childs'][$mk]['filter']['create_time']['min_val'] = $year.'-'.$mon.'-1';
                    $date_arr['childs'][$k]['childs'][$mk]['filter']['create_time']['max_val'] = date('Y-m-t',strtotime($year.'-'.$mon.'-1'));
                }
            }else{
                for($i=1;$i<=12;$i++)
                {
                    $date_arr['childs'][$k]['childs'][$i]['group_name'] = $i;
                    $date_arr['childs'][$k]['childs'][$i]['filter']['create_time']['sign'] = 'between';
                    $date_arr['childs'][$k]['childs'][$i]['filter']['create_time']['min_val'] = $year.'-'.$i.'-1';
                    $date_arr['childs'][$k]['childs'][$i]['filter']['create_time']['max_val'] = date('Y-m-t',strtotime($year.'-'.$i.'-1'));
                }
            }
        }
        return array_merge($init_data,array($date_arr));
    }

    /**
     * 获取店铺信息
     *
     * @param void
     * @return array
     */
    protected function fetchShopInfo()
    {
        return app::get('ecorder')->model('shop')->getList('shop_id');
    }

    /**
     * 序列化查询条件
     *
     * $condition array
     */
    public function serialize_condition($data){
        $newFilter = array();
        foreach ($data['filter'] as $k => $v) {
            $exist = false;
            if (is_array($v)) {
                foreach ($v as $v1) {
                    if (!empty($v1)) {
                        $exist = true;
                    }
                }
            }
            elseif (is_string($v)) {
                if (!empty($v)) {
                    $exist = true;
                }
            }
            if ($exist == true) {
                $newFilter[$k] = $v;
            }
        }
        return $newFilter;
        //return $data['filter'];

        foreach($data['query_condition'] as $k=>$v){
            if($v){
                $tmp[$k] = $v;
            }
        }

        if($data['createtime_from'] && $data['createtime_to']){
            $createtime_from = $data["createtime_from"] . ' '.
            $data["_DTIME_"]["H"]["createtime_from"] .':'.
            $data["_DTIME_"]["M"]["createtime_from"] .':00';

            $createtime_to = $data["createtime_to"] . ' '.
            $data["_DTIME_"]["H"]["createtime_to"] .':'.
            $data["_DTIME_"]["M"]["createtime_to"] .':00';

            $tmp['createtime_from'] = strtotime($createtime_from);
            $tmp['createtime_to'] = strtotime($createtime_to);
        }

        if($data['createtime']){
            $date = $data["createtime"] . ' '.
            $data["_DTIME_"]["H"]["createtime"] .':'.
            $data["_DTIME_"]["M"]["createtime"] .':00';
            $tmp['createtime'] = strtotime($date);
        }

        $tmp['group_id'] = $data['group_id'];
        $tmp['shop_id'] = $data['shop_id'];
        return $tmp;
    }

    function countChilds($group_id){
        $group_id = intval($group_id);
        $oMemberGroup = $this->app->model('member_group');
        $rs = $oMemberGroup->count(array('parent_id'=>$group_id));
        $oMemberGroup->update(array('childs'=>$rs),array('group_id'=>$group_id));
    }
}
