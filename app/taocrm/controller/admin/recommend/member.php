<?php

class taocrm_ctl_admin_recommend_member extends desktop_controller{

    var $workground = 'plugins.manage';

    public function __construct($app)
    {
        parent::__construct($app);        
        $last_month_from   = date('Y-m-01',strtotime("-1 month"));
        $last_month_to     = date('Y-m-d',strtotime("$last_month_from +1 month -1 day"));

        $last_3_month_from = date('Y-m-01', strtotime("-3 month")) ;
        $last_3_month_to   = date('Y-m-d',strtotime("$last_3_month_from +3 month -1 day"));

        $timeBtn = array(
            'this_month_from'   => date("Y-m-" . 01),
            'this_month_to'     => date("Y-m-d"),

            'last_month_from'   => $last_month_from,
            'last_month_to'     => $last_month_to,

            'last_3_month_from' => $last_3_month_from,
            'last_3_month_to'   => $last_3_month_to,
        );
        $this->pagedata['timeBtn'] = $timeBtn;
        $types = array(
            'uname'     => '客户名称',
            'name'      => '真实姓名',
            'mobile'     => '手机号',
            'self_code' => '推荐人唯一码',
            'rec_code'  => '被推荐人唯一码',
            'rec_name'  => '被推荐人名称',
        );
        $this->pagedata['types'] = $types;
    }

    public function index()
    {
        $title = '推荐人列表';

        $baseFilter = array('is_parent'=>'true');
        //搜索参数
        if(isset($_POST['s'])){
            $s = $_POST['s'];
            foreach($s as $k=>$v){
                if(!$v) continue;
                if($k=='type' || $k == 'value')
                {
                    $params[$k] = trim($v);
                }elseif($k=='time_from')
                {
                    $baseFilter["create_time|bthan"] = strtotime($v);
                }elseif($k=='time_to')
                {
                    $baseFilter["create_time|lthan"] = strtotime($v.' 23:59:59');
                }
            }
            $this->pagedata['s'] = $_POST['s'];
        }
        
        $cookie = $baseFilter;
        unset($cookie['is_parent']);
        !empty($cookie) && setcookie('search_params', json_encode($cookie), 0, '/');

        $export_url = 'index.php?app=taocrm&ctl=admin_recommend_member&act=export';
        $baseFilter['create_time|bthan'] && $export_url .= '&from_time='.$baseFilter["create_time|bthan"].'&to_time='.$baseFilter["create_time|lthan"];
        ($params['type'] && $params['value']) && $export_url .= '&'.$params['type'].'='.$params['value'];
        $actions[] = array(
            'label'  => '导出推荐客户',
            'href' => $export_url,
            'target' => 'new'
        );
        if($params['type'] == 'rec_code')
        {
            $app = app::get('taocrm');
            $rec_info = $app->model('members_recommend')->dump(array('self_code' => $params['value']),'parent_code');
            $baseFilter['self_code'] = $rec_info['parent_code'];
        }elseif($params['type'] == 'rec_name')
        {
            $app = app::get('taocrm');
            $rec_info = $app->model('members_recommend')->dump(array('uname' => $params['value']),'parent_code');
            $baseFilter['self_code'] = $rec_info['parent_code'];
        }else
        {
            $baseFilter[$params['type']] = $params['value'];
        }


        $extra_view = array('taocrm'=>'admin/recommend/members_seach.html');
        $this->finder('taocrm_mdl_members_recommend',array(
            'title'               => $title,
            'actions'             => $actions,
            'base_filter'         => $baseFilter,
            'top_extra_view'      => $extra_view,
            'orderBy'             => 'create_time DESC',//默认排序
            'use_buildin_set_tag' => false,
            'use_buildin_import'  => false,
            'use_buildin_export'  => false,
            'use_buildin_recycle' => false,
            'use_buildin_filter'  => false,//暂时去掉高级筛选功能
            'use_buildin_tagedit' => false,
            'use_buildin_setcol'  => false,//列配置
            'use_buildin_refresh' => false,//刷新
        ));
    }
    function export()
    {
//        $ids = $_POST['member_id'];
        $params = array(
//            'member_id' => $ids,
            'is_parent' => 'true',
        );
        $_GET['from_time'] && $params['create_time|bthan'] = $_GET['from_time'];
        $_GET['to_time'] && $params['create_time|lthan'] = $_GET['to_time'];
        isset($_GET['uname']) && $params['uname'] = $_GET['uname'];
        isset($_GET['name']) && $params['name'] = $_GET['name'];
        isset($_GET['mobile']) && $params['mobile'] = $_GET['mobile'];
        isset($_GET['self_code']) && $params['self_code'] = $_GET['self_code'];
        if(isset($_GET['rec_code']))
        {
            $app = app::get('taocrm');
            $rec_info = $app->model('members_recommend')->dump(array('self_code' => $_GET['rec_code']),'parent_code');
            $params['self_code'] = $rec_info['parent_code'];
        }elseif(isset($_GET['rec_uname']))
        {
            $app = app::get('taocrm');
            $rec_info = $app->model('members_recommend')->dump(array('uname' => $_GET['rec_uname']),'parent_code');
            $params['self_code'] = $rec_info['parent_code'];
        }
        
        $app = app::get('taocrm');
        $rec_list = $app->model('members_recommend')->getList('uname,name,mobile,self_code',$params);
        foreach($rec_list as $k => $rec)
        {
            $rec_list[$k]['my_pay']    = $this->column_mypay($rec);
            $rec_list[$k]['rec_money'] = $this->column_recmoney($rec);
            $rec_list[$k]['count_man'] = $this->column_countman($rec);
            $rec_list[$k]['return']    = $this->column_return($rec);
        }
        $title = array(
            '推荐人名称（客户名）','真实姓名','手机号','推荐唯一码','本人消费金额','推荐消费金额','推荐人数','返点值'
        );
        
        $this->export_to_csv($title ,$rec_list);
    }

    /**
     * 导出csv文件
     *
     * @param array $data 数据（如果需要，列标题也包含在这里）
     * @param string $filename 文件名（不含扩展名）
     * @param string $to_charset 目标编码
     */
    function export_to_csv($title,$data, $filename, $to_charset = '')
    {
        $filename || $filename = date('Y-m-d-H:i:s');
        header("Content-type: application/unknown");
        header("Content-Disposition: attachment; filename={$filename}.csv");
        foreach ($title as $row)
        {
           echo $row = iconv('utf-8','gb2312',$row);
           echo ',';
        }
        echo "\r\n";
        foreach ($data as $row)
        {
            foreach ($row as $key => $col)
            {
                $col = iconv('utf-8','gb2312',$col);
                $row[$key] = $this->_replace_special_char($col);
            }
            echo join(',', $row) . "\r\n";
        }
    }

    /**
     * 替换影响csv文件的字符
     *
     * @param $str string 处理字符串
     */
    function _replace_special_char($str, $replace = true)
    {
        $str = str_replace("\r\n", "", $str);
        $str = str_replace("\t", "    ", $str);
        $str = str_replace("\n", "", $str);
        if ($replace == true)
        {
            $str = '"' . str_replace('"', '""', $str) . '"';
        }
        return $str;
    }

    function column_mypay($row)
    {
        isset($_COOKIE['search_params']) && $search = json_decode($_COOKIE['search_params'],true);
        $app = app::get('taocrm');
        //按人查询消费总计
        if($conf['order_status'] == 'paid')
            $f = 'pay_amount';
        else
            $f = 'finish_amount';
        $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id = '.$row['member_id'];
        if($search)
        {
            $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
        }
        $pay = $app->model('member_orders_day')->db->select($sql);
        return intval($pay[0]['sum']);
    }

    function column_recmoney($row)
    {
        isset($_COOKIE['search_params']) && $search = json_decode($_COOKIE['search_params'],true);
        $app = app::get('taocrm');
        //按人查询消费总计
        $params = array(
            'parent_code' => $row['self_code'],
        );
        if($search)
            $params = array_merge($search,$params);
        
        $rec_list = $app->model('members_recommend')->getList('*',$params);
        foreach($rec_list as $rec)
        {
            $childs_ids[] = $rec['member_id'];
            $childs_codes[] = $rec['self_code'];
        }
        if($childs_codes)
        {
            $params = array(
                'parent_code' => $childs_codes,
            );
            if($search)
                $params = array_merge($search,$params);
            $rec_list = $app->model('members_recommend')->getList('*',$params);
            foreach($rec_list as $rec)
            {
                $childs_ids[] = $rec['member_id'];
                $childs_codes[] = $rec['self_code'];
            }
        }
        //按人查询消费总计
        if($conf['order_status'] == 'paid')
            $f = 'pay_amount';
        else
            $f = 'finish_amount';
        $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id in ('.implode(',',$childs_ids).')';
        if($search)
        {
            $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
        }
        $pay = $app->model('member_orders_day')->db->select($sql);
        $conf = $this->get_config();
        return intval($pay[0]['sum']);
    }

    function column_countman($row)
    {
        isset($_COOKIE['search_params']) && $search = json_decode($_COOKIE['search_params'],true);
        $app = app::get('taocrm');
        //按人查询消费总计
        $params = array(
            'parent_code' => $row['self_code'],
        );
        if($search)
            $params = array_merge($search,$params);
        $rec_list = $app->model('members_recommend')->getlist('*',$params);
        foreach($rec_list as $rec)
        {
            $childs_codes[] = $rec['self_code'];
        }
        if($childs_codes)
        {
            $params = array(
                'parent_code' => $childs_codes,
            );
            if($search)
                $params = array_merge($search,$params);
            $rec_list = $app->model('members_recommend')->getlist('*',$params);
            foreach($rec_list as $rec)
            {
                $childs_codes[] = $rec['self_code'];
            }
        }
        return count($childs_codes);
    }
    
    function column_return($row)
    {
        isset($_COOKIE['search_params']) && $search = json_decode($_COOKIE['search_params'],true);

        $pay_1_return = $count_2_return = $count_3_return = $pay_2_return = $pay_3_return = 0;
        $app = app::get('taocrm');
        $conf = $this->get_config();
        if(($conf['rebate_type'] == 'paid_amount' || $conf['rebate_type'] == 'both') && $conf['is_join']['paid'][0])
        {
            //一级
            if($conf['order_status'] == 'paid')
                $f = 'pay_amount';
            else
                $f = 'finish_amount';
            $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id = '.$row['member_id'];
            if($search)
            {
                $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
            }
            $pay = $app->model('member_orders_day')->db->select($sql);
            $pay_1_return = (intval($pay[0]['sum']) * intval($conf['ratio']['paid'][0])) / 100;
        }
        //二级
        $params = array(
            'parent_code' => $row['self_code'],
        );
        if($search)
            $params = array_merge($search,$params);
        $rec_list = $app->model('members_recommend')->getlist('*',$params);
        foreach($rec_list as $rec)
        {
            $childs_ids[] = $rec['member_id'];
            $childs_codes[] = $rec['self_code'];
        }
        if(($conf['rebate_type'] == 'paid_amount' || $conf['rebate_type'] == 'both') && $conf['is_join']['paid'][2])
        {
            if($conf['order_status'] == 'paid')
                $f = 'pay_amount';
            else
                $f = 'finish_amount';
            $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id in ('.implode(',',$childs_ids).')';
            if($search)
            {
                $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
            }
            $pay = $app->model('member_orders_day')->db->select($sql);
            $pay_2_return = (intval($pay[0]['sum']) * intval($conf['ratio']['paid'][2])) / 100;
        }
        if(($conf['rebate_type'] == 'join_num' || $conf['rebate_type'] == 'both') && $conf['is_join']['join'][2])
            $count_2_return = floor(count($childs_codes)/intval($conf['join_num'][2])) * intval($conf['ratio']['join'][2]);
        
        //三级
        if($childs_codes)
        {
            $params = array(
                'parent_code' => $childs_codes,
            );
            if($search)
                $params = array_merge($search,$params);
            $rec_list = $app->model('members_recommend')->getlist('*',$params);

            foreach($rec_list as $rec)
            {
                $childs_ids_3[] = $rec['member_id'];
                $childs_codes_3[] = $rec['self_code'];
            }
            if(($conf['rebate_type'] == 'paid_amount' || $conf['rebate_type'] == 'both') && $conf['is_join']['paid'][3])
            {
                //按人查询消费总计
                if($conf['order_status'] == 'paid')
                    $f = 'pay_amount';
                else
                    $f = 'finish_amount';
                $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id in ('.implode(',',$childs_ids_3).')';
                if($search)
                {
                    $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
                }
                $pay_3 = $app->model('member_orders_day')->db->select($sql);
                $pay_3_return = (intval($pay_3[0]['sum']) * intval($conf['ratio']['paid'][3])) / 100;
            }
            if(($conf['rebate_type'] == 'join_num' || $conf['rebate_type'] == 'both') && $conf['is_join']['join'][3])
                $count_3_return = floor(count($childs_codes_3)/$conf['join_num'][3]) * $conf['ratio']['join'][3];
        }
        return $pay_1_return + $count_2_return + $count_3_return + $pay_2_return + $pay_3_return;
    }

    function get_config()
    {
        $app = app::get('taocrm');
        $filter = array('is_del'=>0, 1=>1);
        $rs = $app->model('rebate_rule')->dump($filter);
        //默认设置
        $conf = array(
            'rebate_type' => 'paid_amount',
            'order_status' => 'paid',
            'set_period' => 'month',
            'is_join' => array(
                'paid' => array(
                    0 => '0',
                    2 => '0',
                    3 => '0',
                )
            ),
            'ratio' => array(
                'paid' => array(
                    0 => '0',
                    2 => '0',
                    3 => '0',
                )
            ),
        );
        if($rs && $rs['conf']){
            $conf = json_decode($rs['conf'], true);
        }
        return $conf;
    }
}

