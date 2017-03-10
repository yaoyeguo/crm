<?php
/**
 * taocrm_finder_recommend_members 
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author liuqi 
 * @license PHP Version 3.0 liuqi1@shopex.cn
 */
class taocrm_finder_recommend_members {


	var $column_mypay = "本人消费金额";
    var $column_mypay_width = 100;
    var $column_mypay_order = 100;
    function column_mypay($row)
    {
        $search = $this->get_search_params();
        $conf = $this->get_config();
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

	var $column_recmoney = "推荐消费金额";
    var $column_recmoney_width = 100;
    var $column_recmoney_order = 110;
    function column_recmoney($row)
    {
        $search = $this->get_search_params();
        $conf = $this->get_config();

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
        return intval($pay[0]['sum']);
    }

	var $column_countman = "推荐人数";
    var $column_countman_width = 100;
    var $column_countman_order = 120;
    function column_countman($row)
    {
        $search = $this->get_search_params();
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
    
	var $column_return = "返点值";
    var $column_return_width = 100;
    var $column_return_order = 130;
    function column_return($row)
    {
        $search = $this->get_search_params();
        $app = app::get('taocrm');
        $sql = 'select sum(rebate_amount) as sum from sdb_taocrm_rebate where member_id = '.$row['member_id'];
        if($search)
        {
            $sql .= ' and create_time >= \''. $search['create_time|bthan'] .'\' and create_time <= \''. $search['create_time|lthan'].'\'';
        }
        $pay_return = $app->model('rebate')->db->select($sql);
        return  $pay_return[0]['sum']?$pay_return[0]['sum']:0;
//        if(($conf['rebate_type'] == 'paid_amount' || $conf['rebate_type'] == 'both') && $conf['is_join']['paid'][0])
//        {
//            //一级
//            if($conf['order_status'] == 'paid')
//                $f = 'pay_amount';
//            else
//                $f = 'finish_amount';
//            $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id = '.$row['member_id'];
//            if($search)
//            {
//                $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
//            }
//            $pay = $app->model('member_orders_day')->db->select($sql);
//            $pay_1_return = (intval($pay[0]['sum']) * intval($conf['ratio']['paid'][0])) / 100;
//        }
//        //二级
//        $params = array(
//            'parent_code' => $row['self_code'],
//        );
//        if($search)
//            $params = array_merge($search,$params);
//        $rec_list = $app->model('members_recommend')->getlist('*',$params);
//        foreach($rec_list as $rec)
//        {
//            $childs_ids[] = $rec['member_id'];
//            $childs_codes[] = $rec['self_code'];
//        }
//        if(($conf['rebate_type'] == 'paid_amount' || $conf['rebate_type'] == 'both') && $conf['is_join']['paid'][2])
//        {
//            if($conf['order_status'] == 'paid')
//                $f = 'pay_amount';
//            else
//                $f = 'finish_amount';
//            $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id in ('.implode(',',$childs_ids).')';
//            if($search)
//            {
//                $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
//            }
//            $pay = $app->model('member_orders_day')->db->select($sql);
//            $pay_2_return = (intval($pay[0]['sum']) * intval($conf['ratio']['paid'][2])) / 100;
//        }
//        if(($conf['rebate_type'] == 'join_num' || $conf['rebate_type'] == 'both') && $conf['is_join']['join'][2])
//            $count_2_return = floor(count($childs_codes)/intval($conf['join_num'][2])) * intval($conf['ratio']['join'][2]);
//
//        //三级
//        if($childs_codes)
//        {
//            $params = array(
//                'parent_code' => $childs_codes,
//            );
//            if($search)
//                $params = array_merge($search,$params);
//            $rec_list = $app->model('members_recommend')->getlist('*',$params);
//
//            foreach($rec_list as $rec)
//            {
//                $childs_ids_3[] = $rec['member_id'];
//                $childs_codes_3[] = $rec['self_code'];
//            }
//            if(($conf['rebate_type'] == 'paid_amount' || $conf['rebate_type'] == 'both') && $conf['is_join']['paid'][3])
//            {
//                //按人查询消费总计
//                if($conf['order_status'] == 'paid')
//                    $f = 'pay_amount';
//                else
//                    $f = 'finish_amount';
//                $sql = 'select sum('.$f.') as sum from sdb_taocrm_member_orders_day where member_id in ('.implode(',',$childs_ids_3).')';
//                if($search)
//                {
//                    $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
//                }
//                $pay_3 = $app->model('member_orders_day')->db->select($sql);
//                $pay_3_return = (intval($pay_3[0]['sum']) * intval($conf['ratio']['paid'][3])) / 100;
//            }
//            if(($conf['rebate_type'] == 'join_num' || $conf['rebate_type'] == 'both') && $conf['is_join']['join'][3])
//                $count_3_return = floor(count($childs_codes_3)/$conf['join_num'][3]) * $conf['ratio']['join'][3];
//        }
//        return $pay_1_return + $count_2_return + $count_3_return + $pay_2_return + $pay_3_return;
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

    var $detail_recommend_list_2 = '二级推荐';
    public function detail_recommend_list_2($id)
    {
        isset($_COOKIE['search_params']) && $search = json_decode($_COOKIE['search_params'],true);
        $conf = $this->get_config();
        $app = app::get('taocrm');
        $render = $app->render();
        
        $info = $app->model('members_recommend')->dump($id);

        $pagelimit = 10;
        $page = max(1, intval($_GET['page']));
        $offset = ($page - 1) * $pagelimit;
        //获取二级
        $params = array(
            'parent_code' => $info['self_code'],
        );

        if($search)
            $params = array_merge($search,$params);

        $rec_list = $app->model('members_recommend')->getList('*',$params,$offset,$pagelimit,'create_time desc');
        foreach($rec_list as $rec)
        {
            $childs_ids[] = $rec['member_id'];
            $childs_codes[] = $rec['self_code'];
        }

        $count = $app->model('members_recommend')->count($params);
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_recommend_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_basic&page=%d'));

        $member_list = array();
        if($childs_ids)
        {
            //按人查询消费总计
            $sql = 'select member_id,sum(pay_amount) as ps ,sum(finish_amount) as fs from sdb_taocrm_member_orders_day where member_id in ('.implode(',',$childs_ids).') ';
            if($search)
            {
                $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
            }
            $sql .= ' group by member_id';
            $pay_arr = $app->model('member_orders_day')->db->select($sql);
            $pay_list = array();
            if($pay_arr)
            {
                foreach($pay_arr as $pay)
                {
                    if($conf['order_status'] == 'paid')
                        $pay['money'] = $pay['ps'];
                    else
                        $pay['money'] = $pay['fs'];
                    $pay_list[$pay['member_id']] = $pay;
                }
            }

            $member_mod = $app->model('members');
            $shop_mod = app::get('ecorder')->model('shop');
            foreach($rec_list as $rec)
            {
                $rec['create_time'] = date('Y-m-d H:i:s', $rec['create_time']);
                $member = $member_mod->dump($rec['member_id'],'shop_id,source_terminal,channel_type');
                $rec = array_merge($member,$rec);
                //if($member['shop_id'])
                //{
                //    $shop = $shop_mod->dump($member['shop_id'],'shop_type,name');
                //    $rec['shop_name'] = $shop['name'];
                //    $rec['shop_type'] = $shop['shop_type'];
                //}

                $member_list[] = $pay_list[$rec['member_id']] ? array_merge($rec,$pay_list[$rec['member_id']]) : $rec;
            }
        }
        $render->pagedata['pager'] = $pager;
        $render->pagedata['member_list'] = $member_list;
		return $render->fetch('admin/recommend/childs_list.html');
    }

    
    var $detail_recommend_list_3 = '三级推荐';
    public function detail_recommend_list_3($id)
    {
        isset($_COOKIE['search_params']) && $search = json_decode($_COOKIE['search_params'],true);
        $conf = $this->get_config();
        $app = app::get('taocrm');
        $render = $app->render();
        
        $info = $app->model('members_recommend')->dump($id);

        //获取二级
        $params = array(
            'parent_code' => $info['self_code'],
        );
        if($search)
            $params = array_merge($search,$params);

        $rec_list = $app->model('members_recommend')->getList('*',$params);
        foreach($rec_list as $rec)
        {
            $childs_ids[] = $rec['member_id'];
            $childs_codes[] = $rec['self_code'];
        }


        if($childs_ids)
        {
            $pagelimit = 10;
            $page = max(1, intval($_GET['page']));
            $offset = ($page - 1) * $pagelimit;
            //获取三级
            $params = array(
                'parent_code' => $childs_codes,
            );
            if($search)
                $params = array_merge($search,$params);
            $rec_list = $app->model('members_recommend')->getList('*',$params,$offset,$pagelimit,'create_time desc');
            foreach($rec_list as $rec)
            {
                $childs_ids_3[] = $rec['member_id'];
                $childs_codes_3[] = $rec['self_code'];
            }

            $count = $app->model('members_recommend')->count($params);
            $total_page = ceil($count / $pagelimit);
            $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_recommend_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_basic&page=%d'));

            if($childs_ids_3)
            {
                //按人查询消费总计
                $sql = 'select member_id,sum(pay_amount) as ps ,sum(finish_amount) as fs from sdb_taocrm_member_orders_day where member_id in ('.implode(',',$childs_ids_3).') ';
                if($search)
                {
                    $sql .= ' and day >= \''.date('Y-m-d', $search['create_time|bthan']) .'\' and day <= \''.date('Y-m-d', $search['create_time|lthan']).'\'';
                }
                $sql .= ' group by member_id';
                $pay_arr = $app->model('member_orders_day')->db->select($sql);
                $pay_list = array();
                if($pay_arr)
                {
                    foreach($pay_arr as $pay)
                    {
                        if($conf['order_status'] == 'paid')
                            $pay['money'] = $pay['ps'];
                        else
                            $pay['money'] = $pay['fs'];
                        $pay_list[$pay['member_id']] = $pay;
                    }
                }

                $member_mod = $app->model('members');
                $shop_mod = app::get('ecorder')->model('shop');
                foreach($rec_list as $rec)
                {
                    $rec['create_time'] = date('Y-m-d H:i:s', $rec['create_time']);
                    $member = $member_mod->dump($rec['member_id'],'shop_id,source_terminal,channel_type');
                    $rec = array_merge($member,$rec);
                    //if($member['shop_id'])
                    //{
                    //    $shop = $shop_mod->dump($member['shop_id'],'shop_type,name');
                    //    $rec['shop_name'] = $shop['name'];
                    //    $rec['shop_type'] = $shop['shop_type'];
                    //}

                    $member_list[] = $pay_list[$rec['member_id']] ? array_merge($rec,$pay_list[$rec['member_id']]) : $rec;
                }
            }
        }
        $render->pagedata['pager'] = $pager;
        $render->pagedata['member_list'] = $member_list;
		return $render->fetch('admin/recommend/childs_list.html');
    }

    function get_search_params()
    {
        $baseFilter = array();
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
        }
        return $baseFilter;
    }
} 
