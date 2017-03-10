<?php
class taocrm_ctl_admin_member_level extends desktop_controller {

    public function index()
    {
        $title = '全局客户等级规则';
       $this->finder('taocrm_mdl_member_level',array(
            'title'=> $title,
            'actions'=>array(
            	array(
                	'label'=>'添加客户等级名称/规则',
                    'href'=>'index.php?app=taocrm&ctl=admin_member_level&act=level_add',
                    'target'=>'dialog::{width:650,height:320,title:\'添加客户等级名称/规则\'}'
             	),
             ),
            'orderBy' => '',//去掉默认排序
            'use_buildin_recycle'=>true,
            'use_view_tab'=>true,
        ));
    }

    public function level_edit() {
        $log_mod = $this->app->model('members_rule_log');
        $sql = "select * from sdb_taocrm_members_rule_log order by create_time desc limit 1;";
        $rule = $log_mod->db->select($sql);
        $rule = current($rule);
        if($_POST)
        {
            $url = "index.php?app=taocrm&ctl=admin_member_level&act=index";
            $this->begin($url);
            if($rule['type'] == 'point')
            {
                $not_empty = array(
                    "level_name"           => '等级名称',
                    "rule_point_month"     => '成交积分时间',
                    "rule_point_condition" => '成交积分条件',
                    "rule_point_min"       => '成交积分最小值',
                    "rule_point_max"       => '成交积分最大值',
                );
                $data_check_rule = array(
                    "level_name"           => 'string',
                    "rule_point_month"     => 'int0',
                    "rule_point_condition" => array('between','nolimit'),
                    "rule_point_min"       => 'int0',
                    "rule_point_max"       => 'int',
                );

                if($_POST['info']['rule_point_condition'] == 'nolimit')
                {
                    unset($not_empty['rule_point_min']);
                    unset($not_empty['rule_point_max']);
                }

                $info = $this->_check_params($_POST['info'],$not_empty,$data_check_rule);
                $info['level_id'] || $this->end(false,'参数异常');
                $info['create_time'] = time();
                $info['update_time'] = time();

                if($info['rule_point_min'] >= $info['rule_point_max'] && $info['rule_point_condition'] != 'nolimit')
                {
                    $this->end(false,'最小积分不能大于最大积分');
                }
                //验证数据后保存到本地数据库
                $mod_obj = app::get('taocrm')->model("member_level");

                $sql = "select count(*) as c from ".app::get('taocrm')->model("member_level")->table_name(1);
                if($info['rule_point_condition'] == 'between')
                {
                    $sql.= " where 
                        (
                            (
                                (rule_point_min <= {$info['rule_point_min']} and rule_point_max > {$info['rule_point_min']}) 
                                or 
                                (rule_point_min < {$info['rule_point_max']} and rule_point_max > {$info['rule_point_max']})
                                or 
                                (rule_point_min >= {$info['rule_point_min']} and rule_point_max <= {$info['rule_point_max']})
                            ) 
                            and 
                            rule_point_condition = 'between'
                        )";
                }
                else
                {
                    $sql .= " where rule_point_condition = 'nolimit' ";
                }
                $sql.=" and level_id <> {$info[level_id]}";

                $find_data = app::get('taocrm')->model("member_level")->db->select($sql);
                if($find_data[0]['c'] > 0)
                {
                    $this->end(false,'积分条件已存在或有数据交叉');
                }
                $info['rule_type'] = 'point';
            }
            else
            {
            $not_empty = array(
                        "level_name"        => '等级名称',
                        "rule_amount_month" =>'成交金额时间',
                        "rule_amount_condition"=>'成交金额条件',
                        "rule_amount_min"   =>'成交金额最小值',
                        "rule_amount_max"   =>'成交金额最大值',
                        "rule_count_month"  =>'成交次数时间',
                        "rule_count_condition"=>'成交次数条件',
                        "rule_count_min"    =>'成交最少小次数',
                        "rule_count_max"    =>'成交最大次数',
                        "rule_select"       =>'规则选择方式',
                    );
            $data_check_rule = array(
                        "level_name"        => 'string',
                        "rule_amount_month" => 'int0',
                        "rule_amount_condition"=>array('between','nolimit'),
                        "rule_amount_min"   => 'int0',
                        "rule_amount_max"   => 'int',
                        "rule_count_month"  => 'int0',
                        "rule_count_condition"=>array('between','nolimit'),
                        "rule_count_min"    => 'int0',
                        "rule_count_max"    => 'int',
                        "rule_select"       => array('and','or'),
                        "rule_msg"          => 'string',
                        "level_id"          => 'key',
                    );


            if($_POST['info']['rule_amount_condition'] == 'nolimit')
            {
                unset($not_empty['rule_amount_min']);
                unset($not_empty['rule_amount_max']);
            }
            if($_POST['info']['rule_count_condition'] == 'nolimit')
            {
                unset($not_empty['rule_count_min']);
                unset($not_empty['rule_count_max']);
            }

            $info = $this->_check_params($_POST['info'],$not_empty,$data_check_rule);
            $info['update_time'] = time();

            $info['level_id'] || $this->end(false,'参数异常');

            if($info['rule_amount_min'] >= $info['rule_amount_max'] && $info['rule_amount_condition'] != 'nolimit')
            {
                $this->end(false,'最小金额不能大于最大金额');
            }

            if($info['rule_count_min'] >= $info['rule_count_max'] && $info['rule_count_condition'] != 'nolimit')
            {
                $this->end(false,'最小次数不能大于最大次数');
            }

            //验证数据后保存到本地数据库
            $mod_obj = app::get('taocrm')->model("member_level");

            $sql = "select count(*) as c from ".app::get('taocrm')->model("member_level")->table_name(1);
            $sql.= " where ";
                $sql_amount_sql = " 
                    (
                        (
                            (rule_amount_min <= {$info['rule_amount_min']} and rule_amount_max > {$info['rule_amount_min']}) 
                            or 
                            (rule_amount_min < {$info['rule_amount_max']} and rule_amount_max > {$info['rule_amount_max']})
                            or 
                            (rule_amount_min >= {$info['rule_amount_min']} and rule_amount_max <= {$info['rule_amount_max']})
                        ) 
                        and 
                        rule_amount_condition = 'between'
                    )";
                $sql_count_sql = " 
                    (
                        (
                            (rule_count_min <= {$info['rule_count_min']} and rule_count_max > {$info['rule_count_min']}) 
                            or 
                            (rule_count_min < {$info['rule_count_max']} and rule_count_max > {$info['rule_count_max']})
                            or 
                            (rule_count_min >= {$info['rule_count_min']} and rule_count_max <= {$info['rule_count_max']})
                        )  
                        and 
                        rule_count_condition = 'between'
                    )";
            $outid_sql = " and level_id <> {$info[level_id]}";

            if($info['rule_amount_condition'] == 'nolimit' && $info['rule_count_condition'] == 'nolimit')
            {
                $this->end(false,'成交金额条件和成交次数条件不能都选择“不限制”');
            }elseif($info['rule_amount_condition'] == 'between' && $info['rule_count_condition'] == 'between')
            {
                $all_sql = $sql . '(' . $sql_amount_sql . ' or ' . $sql_count_sql . ')' . $outid_sql;
            }elseif($info['rule_amount_condition'] != 'between' && $info['rule_count_condition'] == 'between')
            {
                $all_sql = $sql . $sql_count_sql . $outid_sql;
            }elseif($info['rule_amount_condition'] == 'between' && $info['rule_count_condition'] != 'between')
            {
                $all_sql = $sql . $sql_amount_sql . $outid_sql;
            }

            $find_data = app::get('taocrm')->model("member_level")->db->select($all_sql);
            if(intval($find_data[0]['c']) > 0)
            {
                $this->end(false,'成交金额条件或成交次数条件已存在其他条件中或有数据交叉');
            }
                $info['rule_type'] = 'pay';
            }
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));

        }else
        {
            $id = !empty($_GET['item_id']) ? intval($_GET['item_id']) : false;
            $render = app::get('taocrm')->render();

            if(!$id)
            {
                $render->pagedata['info'] = false;
            }else
            {
                $mod_obj = &app::get('taocrm')->model('member_level');
                $info = $mod_obj->dump($id);
                $render->pagedata['info'] = $info;
            }
            $render->pagedata['rule'] = $rule;
            $render->display('admin/member/level/edit.html');
        }
    }

      public function level_add() {
        $log_mod = $this->app->model('members_rule_log');
        $sql = "select * from sdb_taocrm_members_rule_log order by create_time desc limit 1;";
        $rule = $log_mod->db->select($sql);
        $rule = current($rule);
        if($_POST)
        {
            $url = "index.php?app=taocrm&ctl=admin_member_level&act=index";
            $this->begin($url);
            if($rule['type'] == 'point')
            {
                $not_empty = array(
                    "level_name"            => '等级名称',
                    "rule_point_month"     => '成交积分时间',
                    "rule_point_condition" => '成交积分条件',
                    "rule_point_min"       => '成交积分最小值',
                    "rule_point_max"       => '成交积分最大值',
                );
                $data_check_rule = array(
                    "level_name"            => 'string',
                    "rule_point_month"     => 'int0',
                    "rule_point_condition" => array('between','nolimit'),
                    "rule_point_min"       => 'int0',
                    "rule_point_max"       => 'int',
                );

                if($_POST['info']['rule_point_condition'] == 'nolimit')
                {
                    unset($not_empty['rule_point_min']);
                    unset($not_empty['rule_point_max']);
                }

                $info = $this->_check_params($_POST['info'],$not_empty,$data_check_rule);
                $info['create_time'] = time();
                $info['update_time'] = time();

                if($info['rule_point_min'] >= $info['rule_point_max'] && $info['rule_point_condition'] != 'nolimit')
                {
                    $this->end(false,'最小积分不能大于最大积分');
                }

                //验证数据后保存到本地数据库
                $mod_obj = app::get('taocrm')->model("member_level");

                $sql = "select count(*) as c 
                    from ".app::get('taocrm')->model("member_level")->table_name(1) ." 
                    where 
                    (
                        (rule_point_min <= {$info['rule_point_min']} and rule_point_max > {$info['rule_point_min']}) 
                        or 
                        (rule_point_min < {$info['rule_point_max']} and rule_point_max > {$info['rule_point_max']}) 
                        or 
                        (rule_point_min >= {$info['rule_point_min']} and rule_point_max <= {$info['rule_point_max']}) 
                    ) 
                    and rule_point_condition = '".$info['rule_point_condition']."'
                    and rule_type = 'point'";
                $find_data = app::get('taocrm')->model("member_level")->db->select($sql);
                if($find_data[0]['c'] > 0)
                {
                    $this->end(false,'积分条件已存在或有数据交叉');
                }
                $info['rule_type'] = 'point';
            }
            else
            {
            $not_empty = array(
                        "level_name"        => '等级名称',
                        "rule_amount_month" =>'成交金额时间',
                        "rule_amount_condition"=>'成交金额条件',
                        "rule_amount_min"   =>'成交金额最小值',
                        "rule_amount_max"   =>'成交金额最大值',
                        "rule_count_month"  =>'成交次数时间',
                        "rule_count_condition"=>'成交次数条件',
                        "rule_count_min"    =>'成交最少小次数',
                        "rule_count_max"    =>'成交最大次数',
                        "rule_select"       =>'规则选择方式',
                    );
            $data_check_rule = array(
                        "level_name"        => 'string',
                        "rule_amount_month" => 'int0',
                        "rule_amount_condition"=>array('between','nolimit'),
                        "rule_amount_min"   => 'int0',
                        "rule_amount_max"   => 'int',
                        "rule_count_month"  => 'int0',
                        "rule_count_condition"=>array('between','nolimit'),
                        "rule_count_min"    => 'int0',
                        "rule_count_max"    => 'int',
                        "rule_select"       => array('and','or'),
                    );

            if($_POST['info']['rule_amount_condition'] == 'nolimit')
            {
                unset($not_empty['rule_amount_min']);
                unset($not_empty['rule_amount_max']);
            }
            if($_POST['info']['rule_count_condition'] == 'nolimit')
            {
                unset($not_empty['rule_count_min']);
                unset($not_empty['rule_count_max']);
            }

            $info = $this->_check_params($_POST['info'],$not_empty,$data_check_rule);
            $info['create_time'] = time();
                $info['update_time'] = time();

            if($info['rule_amount_condition'] == 'nolimit' && $info['rule_count_condition'] == 'nolimit')
            {
                $this->end(false,'成交金额条件和成交次数条件不能都选择“不限制”');
            }

            if($info['rule_amount_min'] >= $info['rule_amount_max'] && $info['rule_amount_condition'] != 'nolimit')
            {
                $this->end(false,'最小金额不能大于最大金额');
            }

            if($info['rule_count_min'] >= $info['rule_count_max'] && $info['rule_count_condition'] != 'nolimit')
            {
                $this->end(false,'最小次数不能大于最大次数');
            }

            //验证数据后保存到本地数据库
            $mod_obj = app::get('taocrm')->model("member_level");

            $sql = "select count(*) as c 
                from ".app::get('taocrm')->model("member_level")->table_name(1) ." 
                where 
                (
                        (
                    (rule_amount_min <= {$info['rule_amount_min']} and rule_amount_max > {$info['rule_amount_min']}) 
                    or 
                    (rule_amount_min < {$info['rule_amount_max']} and rule_amount_max > {$info['rule_amount_max']}) 
                            or 
                            (rule_amount_min >= {$info['rule_amount_min']} and rule_amount_max <= {$info['rule_amount_max']}) 
                        )
                    and 
                    rule_amount_condition = '".$info['rule_amount_condition']."'
                ) 
                and
                (
                        (
                    (rule_count_min <= {$info['rule_count_min']} and rule_count_max > {$info['rule_count_min']}) 
                    or 
                    (rule_count_min < {$info['rule_count_max']} and rule_count_max > {$info['rule_count_max']})  
                            or 
                            (rule_count_min >= {$info['rule_count_min']} and rule_count_max <= {$info['rule_count_max']})  
                        )
                    and 
                    rule_count_condition = '".$info['rule_count_condition']."'
                    )
                    and rule_type = 'pay'";
            $find_data = app::get('taocrm')->model("member_level")->db->select($sql);
            if($find_data[0]['c'] > 0)
            {
                $this->end(false,'成交金额条件或成交次数条件已存在其他条件中或有数据交叉');
            }
                $info['rule_type'] = 'pay';
            }
            $rt = $mod_obj->save($info);
            $rt = $rt ? true : false;

            $this->end($rt,app::get('base')->_($rt?'保存成功':'保存失败'));

        }else
        {
            $render = app::get('taocrm')->render();
            $render->pagedata['rule'] = $rule;
            $render->display('admin/member/level/edit.html');
        }
    }

    private function _check_params($params,$not_empty,$data_check_rule)
    {
        if(!is_array($params))
        {
            return array();
        }
        foreach($params as $pk => $pd)
        {
            $pd = htmlspecialchars($pd);
            if($data_check_rule[$pk] == 'string')
            {
                $pd = !empty($pd) ? trim($pd) : false;
            }elseif($data_check_rule[$pk] == 'int' || $data_check_rule[$pk] == 'key')
            {
                $pd = intval($pd) > 0 ? intval($pd) : false;
            }elseif($data_check_rule[$pk] == 'int0')
            {
                $pd = intval($pd) >= 0 ? intval($pd) : false;
            }elseif(is_array($data_check_rule[$pk]))
            {
                if(!in_array($pd,$data_check_rule[$pk]))
                {
                    $pd = false;
                }
            }
            $info[$pk] = $pd;
        }
        foreach($not_empty as $k => $v)
        {
            if($info[$k] === false || !isset($info[$k]))
            {
                $this->end(false,$not_empty[$k].'不能为空');
            }
        }
        return $info;
    }

}
