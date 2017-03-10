<?php

class market_ctl_admin_callcenter_callplan extends desktop_controller{

    var $pagelimit = 10;
    var $is_debug = false;

    public function __construct($app)
    {
        parent::__construct($app);
        $desktop_user = kernel::single('desktop_user');
        $this->user_id = $desktop_user->get_id();
        $this->user_name = $desktop_user->get_name();
    }

    public function index()
    {
        $actions = array(
            array(
                'label'=>'创建呼叫计划',
                'href'=>'index.php?app=market&ctl=admin_callcenter_callplan&act=create',
                'target'=>'dialog::{width:760,height:380,title:\'创建呼叫计划\'}'
            ),
        );

        $param=array(
            'title'=>'呼叫计划',
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
        	'use_buildin_selectrow' => true,
            'orderBy' => "callplan_id DESC",
            'base_filter'=>array(),
            'actions'=>$actions,
        );
        $this->finder('market_mdl_callplan',$param);
    }

    function _views()
    {
    }

    function edit($callplan_id=0)
    {
        $this->create($callplan_id);
    }

    //创建呼叫计划
    function create($callplan_id=0)
    {
        if($_POST){
            $this->save_callplan($_POST);
            exit;
        }

        //参数初始化
        $member_groups = array();
        $import_groups = array();
        $member_tags = array();
        $callplans = array();
        $assign_users = '请选择';

        //自定义分组
        $taocrm = app::get('taocrm');
        $rs = $taocrm->model('member_group')->getList('group_id,shop_id,group_name,members');
        //$rs = $taocrm->model('member_group')->getList('group_id,group_name,members', array(), 0, 50);
        foreach($rs as $v){
            $member_groups[$v['shop_id']][$v['group_id']] = $v['group_name'].' -- 约'.$v['members'].'人';
        }

        //店铺列表
        $shops = app::get('ecorder')->model('shop')->getList('shop_id,name,subbiztype',array('subbiztype|neqAndNotNull' => 'fx'));
        $curr_shop = current($shops);
        $this->pagedata['shops_list'] = $shops;
        $this->pagedata['shops_curr_id'] = $curr_shop['shop_id'];


        //导入分组
        //$rs = $taocrm->model('member_import_group')->getList('group_id,group_name,mobile_valid_nums', array(), 0, 50);
        $rs = $taocrm->model('member_import_group')->getList('group_id,group_name,mobile_valid_nums');
        foreach($rs as $v){
            $import_groups[$v['group_id']] = $v['group_name'].' -- 约'.$v['mobile_valid_nums'].'人';
        }

        //会员标签
        //$rs = $taocrm->model('member_tag')->getList('tag_id,tag_name,mobile_valid_nums', array(), 0, 50);
        $rs = $taocrm->model('member_tag')->getList('tag_id,tag_name,mobile_valid_nums');
        foreach($rs as $v){
            $member_tags[$v['tag_id']] = $v['tag_name'].' -- 约'.$v['mobile_valid_nums'].'人';
        }

        //老的呼叫计划
        $rs = $this->app->model('callplan')->getList('callplan_id,callplan_name,total_num', array(), 0, 50);
        foreach($rs as $v){
            if($v['callplan_id'] == $callplan_id)
                continue;
            $callplans[$v['callplan_id']] = $v['callplan_name'].' -- 约'.$v['total_num'].'人';
        }

        //用户列表
        $users = app::get('desktop')->model('users')->getList('user_id,name');

        $rs_callplan = array(
            'callplan_id' => 0,
            'status' => 1,
            'source' => 'member_group',
            'start_time' => date('Y-m-d'),
            'end_time' => date('Y-m-d', strtotime('+30 days')),
        );

        if($callplan_id>0){
            $rs_callplan = $this->app->model('callplan')->dump($callplan_id);
            $rs_callplan['assign_user_id'] = explode(';', $rs_callplan['assign_user_id']);
        }

        $this->pagedata['rs'] = $rs_callplan;
        $this->pagedata['callplan_id'] = $callplan_id;
        $this->pagedata['assign_users'] = $assign_users;
        $this->pagedata['users'] = $users;
        $this->pagedata['member_groups'] = $member_groups;
        $this->pagedata['import_groups'] = $import_groups;
        $this->pagedata['member_tags'] = $member_tags;
        $this->pagedata['callplans'] = $callplans;
        $this->display('admin/callcenter/callplan/create.html');
    }

    public function save_callplan($data)
    {
        $this->begin('index.php?app=market&ctl=admin_callcenter_callplan&act=index');
        $callplan_id = intval($data['callplan_id']);
        $data['callplan_id'] = $callplan_id;
        $data['create_user'] = $this->user_name;
        $data['update_time'] = time();
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['assign_user_id'] = @implode(';', $data['assign_user_id']);

        if($callplan_id == 0){
            $data['create_time'] = time();
        }
        $this->app->model('callplan')->save($data);

        //仅在新建计划时创建客户明细
        if($callplan_id == 0){
            $this->create_callplan_members($data);
        }

        $this->end(true,'保存成功');
    }

    public function create_callplan_members($data)
    {
        $source = $data['source'];
        $source_id = $data['source_id'];
        $callplan_id = $data['callplan_id'];
        $time = time();
        $db = kernel::database();

        $db->exec('begin');

        switch($source){

            //自定义客户分组
            case 'member_group':
                $rs_group = app::get('taocrm')->model('member_group')->dump($data['source_id']);
                $data['filter'] = unserialize($rs_group['filter']);
                $data['shop_id'] = $rs_group['shop_id'];
                $data['create_time'] = time();
                $data['assign_user_id'] = 0;
                $res = kernel::single('taocrm_middleware_connect')->createCallplanMembers($data);
                if($res['errmsg']){
                    $err_msg = addslashes($res['errmsg']);
                }else{
                    $err_msg = '正在创建';
                }
                $sql = "update sdb_market_callplan set err_msg='{$err_msg}' where callplan_id={$callplan_id} ";
                $this->app->model('callplan')->db->exec($sql);
            break;

            //导入分组
            case 'member_import':
                $sql = "INSERT INTO sdb_market_callplan_members
                (callplan_id,member_id,mobile,customer,truename,assign_user_id,create_time,update_time)
                (select {$callplan_id},a.member_id,a.mobile,b.uname,b.name,0,{$time},{$time} from sdb_taocrm_member_import as a left join sdb_taocrm_members as b on a.member_id=b.member_id where a.group_id={$source_id} and a.is_mobile_valid=1 and a.mobile<>'' group by a.mobile )";
                $db->exec($sql);
            break;

            //会员标签
            case 'member_tags':
                $sql = "INSERT INTO sdb_market_callplan_members
                (callplan_id,member_id,mobile,customer,truename,assign_user_id,create_time,update_time)
                (select {$callplan_id},a.member_id,a.mobile,b.uname,b.name,0,{$time},{$time} from sdb_taocrm_member_to_tag as a left join sdb_taocrm_members as b on a.member_id=b.member_id where a.tag_id={$source_id} and a.mobile<>'' group by a.mobile )";
                $db->exec($sql);
            break;

            //历史呼叫计划
            case 'old_callplan':
                $sql = "INSERT INTO sdb_market_callplan_members
                (callplan_id,member_id,mobile,customer,truename,assign_user_id,create_time,update_time)
                (select {$callplan_id},member_id,mobile,customer,truename,0,{$time},{$time} from sdb_market_callplan_members where callplan_id={$source_id} )";
                $db->exec($sql);
            break;
        }

        //更新客户总数
        if($data['source'] != 'member_group'){
            $sql = "update sdb_market_callplan as a,(select count(*) as total_num from sdb_market_callplan_members where callplan_id={$callplan_id} ) as b set a.total_num=b.total_num where a.callplan_id={$callplan_id} ";
            $db->exec($sql);
        }

        $db->commit();
        return true;
    }

}
