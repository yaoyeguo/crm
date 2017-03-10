<?php
class taocrm_ctl_admin_member_import extends desktop_controller{
    var $workground = 'taocrm.member';

    var $is_debug = false;


    //onClose:function(){window.location.reload();},
    public function index()
    {
        $this->tempProcess();

        $title = '外部客户列表';
        $baseFilter = array();
        $this->finder('taocrm_mdl_member_import_group',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        	'actions'=>array(
        array(
                'label'=>'创建分组',
                'href'=>'index.php?app=taocrm&ctl=admin_member_import&act=createGroup',
                'target'=>'dialog::{width:650,height:355,title:\'创建外部客户分组\'}'
                ),
                ),
                //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
            'orderBy'=> 'group_id DESC',
                ));
    }

    /**
     *
     * 因为导入小工具问题，暂时临时处理数据问题
     */
    function tempProcess(){
        $db = kernel::database();
        $rows = $db->select('select batch_id from sdb_taocrm_member_import_batch where group_id=0');
        if($rows){
            foreach($rows as $row){
                $member_import = $db->selectRow('select group_id from sdb_taocrm_member_import where batch_id='.$row['batch_id']);
                $group_id = $member_import['group_id'];
                $db->exec('update sdb_taocrm_member_import_batch set group_id='.$group_id .' where batch_id='.$row['batch_id']);
                $this->app->model('member_import_batch')->countNums($group_id,$row['batch_id']);
            }
        }

    }

    function createGroup()
    {
        $this->display('admin/member/import/create_group.html');
    }

    public function editGroup($group_id){
        $group_id = isset ($group_id) && intval($group_id) > 0 ? intval($group_id) : 0;

        $oGroup = &$this->app->model('member_import_group');
        $data = $oGroup->dump($group_id);
        $this->pagedata['data'] = $data;

        $this->display('admin/member/import/create_group.html');
    }

    function saveGroup(){
        $oGroup= &$this->app->model('member_import_group');
        $data = $_POST;

        $group_id = isset ($data['group_id']) && intval($data['group_id']) > 0 ? intval($data['group_id']) : 0;
        $this->begin();
        if($group_id){
            $filter = array(
                'group_id' => $group_id,
            );
            $res = $oGroup->dump(array('group_name'=>$data['group_name']),'group_id');
            if($res && $res['group_id'] != $group_id){
                $this->end(false,app::get('taocrm')->_('分组名称重复'));
            }
            $ret = $oGroup->update(array(
                'group_name' => $data['group_name'],
            ),$filter);
        } else {
            $res = $oGroup->dump(array('group_name'=>$data['group_name']),'group_id');
            if($res){
                $this->end(false,app::get('taocrm')->_('分组名称已存在'));
            }
            $arr_data = array(
                'group_name' => $data['group_name'],
                'create_time' => time(),
            );
            $ret = $oGroup->insert($arr_data);
        }

        if($ret){
            $this->end(true,app::get('taocrm')->_('操作成功'));
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }

    /*public function index()
     {
     $title = '其它导入列表';
     $baseFilter = array();
     $this->finder('taocrm_mdl_member_import',array(
     'title'=> $title,
     'base_filter'=>$baseFilter,
     //'use_buildin_set_tag'=>true,
     'use_buildin_import'=>false,
     'use_buildin_export'=>false,
     'use_buildin_recycle'=>false,
     'use_buildin_filter'=>false,
     'use_buildin_tagedit'=>false,
     ));
     }

     function _views(){
     $sub_menu = array();
     $sub_menu[] = array(
     'label'=> '全部',
     //            'filter'=> '',
     'filter'=>array(),
     'optional'=>false,
     );
     $sub_menu[] = array(
     'label'=> '未发送',
     //            'filter'=> '',
     'filter'=>array('send_count'=>0),
     'optional'=>false,
     );

     $sub_menu[] = array(
     'label'=> '已发送',
     //            'filter'=> '',
     'filter'=>array('send_count|than'=>0),
     'optional'=>false,
     );

     $i=0;
     $memberObj = &app::get('taocrm')->model('member_import');
     $base_filter = array();
     foreach($sub_menu as $k=>$v){
     if (!IS_NULL($v['filter'])){
     $v['filter'] = array_merge($v['filter'],$base_filter);
     }
     $count =$memberObj->count($v['filter']);
     $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
     $sub_menu[$k]['addon'] = $count;
     $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
     }

     return $sub_menu;
     }*/

    function sms()
    {
        $title = '外部客户营销记录';
        $baseFilter = array();
        $this->finder('taocrm_mdl_member_import_sms',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
            'orderBy'=> 'last_send_time DESC',
        ));
    }

    function assess()
    {
        $title = '已清洗外部客户';
        $baseFilter = array('succ_member_id|noequal'=>0);
        $this->finder('taocrm_mdl_member_import',array(
            'title'=> $title,
            'base_filter'=>$baseFilter,
        //'use_buildin_set_tag'=>true,
            'use_buildin_import'=>false,
            'use_buildin_export'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_buildin_tagedit'=>false,
        ));
    }

    function send()
    {
        if(!$_GET['batch_id']){
            echo '没有批次ID';
            exit;
        }

        $message_text = ''; //短信内容初始化
        $unsubscribe_str = '退订回N'; //退订标志
        $batch_id = $_GET['batch_id'];
        $batchObj = app::get('taocrm')->model('member_import_batch');
        $rs_batch = $batchObj->getBatch($batch_id);

        $sql = "select count(*) as total from sdb_taocrm_member_import where batch_id=$batch_id";
        $rs = $batchObj->db->selectrow($sql);
        $rs_batch['total_nums'] = $rs['total'];

        $sql = "select count(*) as total from sdb_taocrm_member_import where batch_id=$batch_id and is_mobile_valid=1";
        $rs = $batchObj->db->selectrow($sql);

        $rs_batch['mobile_valid_nums'] = $rs['total'];
        $this->pagedata['batch'] = $rs_batch;

        $save = array(
            'batch_id'=>$batch_id,
            'total_nums'=>$rs_batch['total_nums'],
            'mobile_valid_nums'=>$rs_batch['mobile_valid_nums']
        );
        $batchObj->save($save);

        //店铺短信签名
        $sms_sign = app::get('ecorder')->model('shop')->get_sms_sign();
        $sms_content = app::get('taocrm')->getConf('import.sms_content');
        if($sms_content){
            $arr = explode('【', $sms_content);
            $sms_sign = str_replace('】','',$arr[1]);
            $message_text = str_replace($unsubscribe_str,'',$arr[0]);
            $message_text = trim($message_text);
        }

        //可用的短信签名
        $oShop = app::get('ecorder')->model("shop");
        $sign_list = $oShop->get_sms_sign_list();

        $this->pagedata['sign_list'] = $sign_list;
        $this->pagedata['unsubscribe_str'] = $unsubscribe_str;
        $this->pagedata['sms_sign'] = $sms_sign;
        $this->pagedata['message_text'] = $message_text;
        $this->display('admin/member/import/sms/send.html');
    }

    //过滤短信内的特殊字符
    private function clear_sms_content($str)
    {
        $str = trim($str);
        $str = str_replace('%', '％', $str);
        $str = str_replace('"', '“', $str);
        $str = str_replace(array("\r", "\n"), '', $str);
        return $str;
    }

    //调用java发送短信
    function toSend()
    {
        if($_SERVER['HTTP_HOST'] == 'xiaolu.crm.taoex.com'){
            $check_sms_account = 0;
        }else{
            $check_sms_account = 1;
        }
        
        $response = array('res'=>'succ');
        if(!$_POST['batch_id']){
            $response = array('res'=>'fail','msg'=>'批次号丢失');
        }

        $sms_content = $this->clear_sms_content($_POST['sms_content']);
        if(!$sms_content){
            $response = array('res'=>'fail','msg'=>'缺少短信内容');
        }

        //将短信内容保存到kv
        app::get('taocrm')->setConf('import.sms_content', $sms_content);

        $batchObj = app::get('taocrm')->model('member_import_batch');
        if($response['res'] == 'succ'){
            $batch = $batchObj->getBatch($_POST['batch_id']);
            if($check_sms_account == 1){
            $response = $this->checkSms($batch['mobile_valid_nums']);
        }
        }
        
        if($check_sms_account == 0) {
            $this->sms_account = kernel::single('market_service_smsinterface')->get_sms_account();
        }

        if($response['res'] == 'succ'){
            $err_msg = '';
            $op_user = kernel::single('desktop_user')->get_name();
            $smsObj = app::get('taocrm')->model('member_import_sms');
            $sms = array(
                'group_id' => $batch['group_id'],
                'batch_id' => $batch['batch_id'],
                'total_num' => $batch['total_nums'],
                'template' => $sms_content,
                'send_status' => 'unsend',
            );
            $sms_id = $smsObj->addSms($sms);
            
            //将发送任务提交到java中间件
            $jobarray = array(
                'batch_id'=>$_POST['batch_id'],
                'sms_id'=>$sms_id,
                'sms_content'=>$sms_content,
                'ip'=>$_SERVER['REMOTE_ADDR'],
                'op_user' => $op_user,
                'extend_no'=>$_POST['extend_no'],
                'entId' => $this->sms_account['entId'],
                'entPwd' => $this->sms_account['entPwd'],
                'license' => $this->sms_account['license'],
            );
            $taocrm_middleware_sms_import = kernel::single('taocrm_middleware_sms_import');
            $res = $taocrm_middleware_sms_import->send($jobarray, $err_msg);
            if(!$res){
                $response = array('res'=>'fail','msg'=>'发送失败:'.$err_msg);
            }
            
            /*
            if(kernel::single('taocrm_service_queue')->addJob('market_backstage_import@fetch',$jobarray)){
                $batchObj->sendRunning($_POST['batch_id']);
                $smsObj->sendRunning($sms_id);
            }else{
                $response = array('res'=>'fail','msg'=>'发送失败');
            }
            */
        }

        echo json_encode($response);
        exit;

    }

    function checkSms($memberNums)
    {
        $result = array('res'=>'succ');

        $smsInfo=$this->getSmsCount();
        if($smsInfo['smscount'] == -1){
            return array('res'=>'fail','msg'=>'您的短信账号出现异常，请检查配置信息');
        }


        //var_dump($smsInfo['overcount']);exit;
        if($smsInfo['overcount'] >= $memberNums){

        }else{
            $result = array('res'=>'balance_less','msg'=>'您的账号可用余额不足请充值');
        }


        return $result;

    }

    function getSmsCount()
    {
        $send=kernel::single('market_service_smsinterface');
        $send_info=$send->get_usersms_info();//get_usersms_info

        if ($send_info['res']=='succ'){
            $month_residual=$send_info['info']['month_residual']; //短信总条数 all_residual
            $blocknums=intval($send_info['info']['block_num']);//冻结短信条数
        }else{
            error_log(var_export($send_info,1), 3, DATA_DIR.'/log.sms_error.php');
            $month_residual=-1; //当前可用的短信数
            $blocknums=-1; //冻结短信条数
        }

        //测试信息
        if($this->is_debug == true) {
            $month_residual = 10000*100;
            $blocknums = 100;
        }

        $infoarray=array(
            'smscount'=>$month_residual,
            'blocknum'=>$blocknums,
            'overcount'=>$month_residual- $blocknums,
            'entId'=> isset($send_info['info']['account_info']['entid']) ? $send_info['info']['account_info']['entid'] : '2bcefef',
            'entPwd'=>$send_info['entPwd'],
            'license'=>$send_info['license'],
        );
        $this->sms_account = $infoarray;
        return $infoarray;
    }

    public function del_member()
    {
        $member_id = intval($_POST['member_id']);
        $this->app->model('member_import')->delete(array('member_id'=>$member_id));
        echo('删除成功！');
        die();
    }

    //将某个批次拆分成多个批次
    public function split()
    {
        if($_POST){
            $this->begin($_POST['refresh_url']);

            $old_batch_id = intval($_POST['batch_id']);
            $group_id = intval($_POST['group_id']);
            $split_num = intval($_POST['split_num']);

            //1.创建新批次
            $save_batch = array(
                'group_id'=>$group_id,
                'send_nums'=>0,
                'last_send_status'=>'unsend',
                'total_nums'=>$split_num,
                'mobile_valid_nums'=>0,
                'email_valid_nums'=>0,
                'create_time'=>time(),
            );
            $model = $this->app->model('member_import_batch');
            $model->insert($save_batch);
            $new_batch_id = $save_batch['batch_id'];

            //2.转移客户数据
            $sql = "update sdb_taocrm_member_import set batch_id=$new_batch_id where batch_id=$old_batch_id and group_id=$group_id limit $split_num";
            //echo($sql);
            $model->db->exec($sql);

            //3.更新新批次的统计信息
            $sql = "select count(*) as total from sdb_taocrm_member_import where batch_id=$new_batch_id and group_id=$group_id and is_mobile_valid=1";
            $rs = $model->db->selectrow($sql);
            //$mobile_valid_nums = $rs['total'];

            $sql = "select count(*) as total from sdb_taocrm_member_import where batch_id=$new_batch_id and group_id=$group_id and is_email_valid=1";
            $rs = $model->db->selectrow($sql);
            //$email_valid_nums = $rs['total'];

            $sql = "update sdb_taocrm_member_import_batch set mobile_valid_nums=$mobile_valid_nums,email_valid_nums=$email_valid_nums where batch_id=$new_batch_id";
            //$model->db->exec($sql);

            $sql = "update sdb_taocrm_member_import_batch set total_nums=total_nums-$split_num where batch_id=$old_batch_id";
            //echo($sql);
            $model->db->exec($sql);

            $this->end(true,'拆分成功');
            //die();
        }
        $batch_id = intval($_GET['batch_id']);
        $batch_count = $this->app->model('member_import')->count(array('batch_id'=>$batch_id));
        $rs_batch = $this->app->model('member_import_batch')->dump(array('batch_id'=>$batch_id));
        $rs_group = $this->app->model('member_import_group')->dump(array('group_id'=>$rs_batch['group_id']));

        $this->pagedata['split_num'] = ceil($batch_count/2);
        $this->pagedata['rs_batch'] = $rs_batch;
        $this->pagedata['batch_count'] = $batch_count;
        $this->pagedata['rs_group'] = $rs_group;
        $this->display('admin/member/import/split.html');
    }
    
    //批次删除
    public function del_batch()
    {
        $mdl_orders = app::get('ecorder')->model('orders');
        $batch_id = intval($_POST['batch_id']);
        $re_bool = true;
        
        //下单客户
        $sql = "select a.succ_member_id,b.order_id from sdb_taocrm_member_import as a,
            sdb_ecorder_orders as b 
            where a.batch_id=$batch_id and b.member_id=a.succ_member_id ";
        $rs = $mdl_orders->db->select($sql);
        if($rs){
            foreach($rs as $v){
                $order_members[$v['succ_member_id']] = $v['order_id'];
            }
        }
           
        $model = $this->app->model('member_import');
        $rs = $model->getlist('succ_member_id,group_id',array('batch_id'=>$batch_id));
        foreach($rs as $v){
            if( ! $group_id) $group_id = $v['group_id'];
            if( ! isset($order_members[$v['succ_member_id']])){
                $del_members[] =  $v['succ_member_id'];
            }
        }
        
        //删除会员表、会员扩展表、店铺会员表
        if($del_members){
            $filter = array('member_id'=>$del_members);
        
            $re_member_ext = $this->app->model('member_ext')->delete($filter);
            $re_members = $this->app->model('member_attr')->delete($filter);
            $re_analysis = $this->app->model('member_analysis')->delete($filter);
            
            if(!$re_members or !$re_analysis){
                $re_bool = false;
            }else{
                //删除会员表
                $re_members = $this->app->model('members')->delete($filter);
            }
        }

        //删除批次表
        $re_batch = $this->app->model('member_import_batch')->delete(array('batch_id'=>$batch_id));
        if(!$re_batch){
            $re_bool = false;
        }
        
        //删除批次会员表
        $re_import = $this->app->model('member_import')->delete(array('batch_id'=>$batch_id));
        if(!$re_import){
            $re_bool = false;
        }
        
        //更新批次统计数组
        $this->app->model('member_import_batch')->countNums($group_id,$batch_id);
        
        $desc = '删除外部客户批次：'.$batch_id;
        if($re_bool){
            $this->insert_log('succ', $desc);//写入系统日志
            echo('删除成功！');
        }else{
            $this->insert_log('fail', $desc);//写入系统日志
            echo('删除失败！');
        }
        die();
    }
    
    //删除日志
    public function insert_log($status='succ', $desc='')
    {
        $loginObj = app::get('ecorder')->model("login_log");
        $http = array(
            'DESC' => $desc,
            'HTTP_HOST' => $_SERVER['HTTP_HOST'],
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
        );
        $user_name = kernel::single('desktop_user')->get_name();
        $data = array(
            'login_time' => date('Y-m-d H:i:s'),
            'operate_type' => 'delete',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_name' => $user_name,
            'addon' => json_encode($http),
        );
        $data['status'] = $status;
        $loginObj->save($data);
    }
    
}