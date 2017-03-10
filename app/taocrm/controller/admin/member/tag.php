<?php
class taocrm_ctl_admin_member_tag extends desktop_controller {

    var $_extra_view = array('taocrm'=>'admin/member/tag/cloud.html');

    public function index()
    {
        //top20 客户数最多的标签
        $rs = $this->app->model('member_tag')->getList('tag_id,tag_name,mobile_valid_nums', array(), 0, 20, 'mobile_valid_nums desc');
        $this->pagedata['tags'] = $rs;

        $this->base_filter=array();
        $this->finder('taocrm_mdl_member_tag',array(
            'title'=>'标签客户列表',
            'actions'=>array(
                array(
                    'label'=>'创建标签',
                    'href'=>'index.php?app=taocrm&ctl=admin_member_tag&act=create',
                    'target'=>'dialog::{onClose:function(){window.location.reload();},width:650,height:355,title:\'创建标签\'}'
                ),
                array(
                    'label'=>'删除',
                    'submit'=>'index.php?app=taocrm&ctl=admin_member_tag&act=delete',
                ),
                array(
                    'label'=>'快捷营销',
                    'submit'=>'index.php?app=market&ctl=admin_active_sms&act=create_active&create_source=tags',
                    'target'=>'dialog::{width:800,height:400,title:\'快捷营销\'}'
                ),
            ),
            'use_buildin_export'=>false,//导出
            'use_buildin_import'=>false,//导入
            'use_buildin_recycle'=>false,
            'orderBy'=> 'tag_id DESC',
			'base_filter' =>$this->base_filter,
        ));
    }

    function ajaxCreateTag()
    {
        $this->display('admin/member/tag/ajax_create_tag.html');
    }

    function create()
    {
        $this->display('admin/member/tag/create.html');
    }

    public function edit($tag_id)
    {
        $tag_id = isset ($tag_id) && intval($tag_id) > 0 ? intval($tag_id) : 0;

        $oTag = $this->app->model('member_tag');
        $data = $oTag->dump($tag_id);
        $this->pagedata['info'] = $data;

        $this->display('admin/member/tag/create.html');
    }

    function save(){
        $oTag= $this->app->model('member_tag');
        $data = $_POST['info'];
        $time = time();
        $tag_id = !empty($_POST['tag_id'])   ? intval($_POST['tag_id']) : 0;
        $data = array(
                'tag_name'      => !empty($data['tag_name'])    ? trim($data['tag_name'])   : false,
                'get_tag'       => isset($data['get_tag'])     ? 'true'                     : 'false',
                'tag_select'    => !empty($data['tag_select'])  ? intval($data['tag_select']) : 0,
                'activity_num'  => !empty($data['activity_num'])? abs(intval($data['activity_num'])) : 0,
                'one_min'       => !empty($data['one_min'])     ? abs(intval($data['one_min']))    : 0,
                'one_max'       => !empty($data['one_max'])     ? abs(intval($data['one_max']))    : 0,
                'all_min'       => !empty($data['all_min'])     ? abs(intval($data['all_min']))    : 0,
                'all_max'       => !empty($data['all_max'])     ? abs(intval($data['all_max']))    : 0,
                'tag_type'      => isset($data['get_tag'])     ? 'store'    : 'hand',
            );

        $this->begin();

        if($data['get_tag'] == 'false')
        {
            $data['tag_select'] = $data['activity_num'] = $data['one_min'] = $data['one_max'] = $data['all_min'] = $data['all_max'] = 0;
        }else{
            switch($data['tag_select'])
            {
                case 1:
                    $data['one_min'] = $data['one_max'] = $data['all_min'] = $data['all_max'] = 0;
                break;
                case 2:
                    $data['activity_num'] = $data['all_min'] = $data['all_max'] = 0;
                    if($data['one_min'] >= $data['one_max'])
                        $this->end(false,app::get('taocrm')->_('单笔最小金额不能大于等于最大金额'));
                break;
                case 3:
                    $data['activity_num'] = $data['one_min'] = $data['one_max'] = 0;
                    if($data['all_min'] >= $data['all_max'])
                        $this->end(false,app::get('taocrm')->_('累计最小金额不能大于等于最大金额'));
                break;
                default:
                    $this->end(false,app::get('taocrm')->_('数据有误'));
                break;
            }
        }

        if($tag_id){
            $filter = array(
                'tag_id' => $tag_id,
            );
            $res = $oTag->dump(array('tag_name'=>$data['tag_name']),'tag_id');
            if($res && $res['tag_id'] != $tag_id){
                $this->end(false,app::get('taocrm')->_('标签名称重复'));
            }

            $data['update_time'] = $time;
            $ret = $oTag->update($data,$filter);
        } else {
            $res = $oTag->dump(array('tag_name'=>$data['tag_name']),'tag_id');
            if($res){
                $this->end(false,app::get('taocrm')->_('标签名称已存在'));
            }
            $data['update_time'] = $time;
            $data['create_time'] = $time;
            $ret = $oTag->insert($data);
        }

        if($ret){
            $this->end(true,app::get('taocrm')->_('操作成功'));
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }

    function ajaxToCreateTag(){
        $oTag= $this->app->model('member_tag');
        $data = $_POST;

        $time = time();
        $response = array('status'=>'succ');
        if($data['tag_name']){
            $res = $oTag->dump(array('tag_name'=>$data['tag_name']),'tag_id');
            if($res){
                $response = array('status'=>'fail','info'=>'标签名称已存在');
            }else{
                $arr_data = array(
                'tag_name' => $data['tag_name'],
                'create_time' => $time,
                'update_time' => $time,
                );
                $ret = $oTag->insert($arr_data);
                if(!$ret){
                    $response = array('status'=>'fail','info'=>'操作失败');
                }else{
                     $response = array('status'=>'succ','tag_id'=>$ret);
                }
            }
        }else{
            $response = array('status'=>'fail','info'=>'请填写标签名称');
        }


        echo json_encode($response);
        exit;
    }

    function delete(){
        $oTag= $this->app->model('member_tag');
        $data = $_POST;
        $this->begin();
        if(!$data['tag_id']){
            $this->end(false,app::get('taocrm')->_('无数据提交'));
        }
        if($oTag->delete($data['tag_id'])){
            $this->end(true,app::get('taocrm')->_('操作成功'),'index.php?app=taocrm&ctl=admin_member_tag&act=index');
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }

    public function sendSms($tag_id)
    {
        if(!$tag_id){
            echo '没有标签ID';
            exit;
        }
        $oTag= $this->app->model('member_tag');
        $this->pagedata['tagSendInfo'] = $oTag->getTagSendInfo($tag_id);
        $this->pagedata['tag_id'] = $tag_id;
        
        //短信可选签名
        $oShop = app::get('ecorder')->model("shop");
        $sign_list = $oShop->get_sms_sign_list();

        $this->pagedata['sign_list'] = $sign_list;
        $this->display('admin/member/tag/sms/send.html');
    }

    public function checkSend(){
        $response = array('res'=>'succ');
        if(!$_POST['tag_id']){
            $response = array('res'=>'fail','msg'=>'标签ID丢失');
        }

        $tag_id = $_POST['tag_id'];

        $oTag= $this->app->model('member_tag');
        $tagSendInfo = $oTag->getTagSendInfo($tag_id);
        if($tagSendInfo['mobile_valid_nums'] <=0){
            $response = array('res'=>'fail','msg'=>'发送人数不能为0');
        }

        if($response['res'] == 'succ'){
            $response = $this->checkSms($tagSendInfo['mobile_valid_nums']);
        }

        echo json_encode($response);
        exit;
    }

    public function toSend()
    {
        $unsubscribe = '退订回N';
        $response = array('res'=>'succ');
        if(!$_POST['tag_id']){
            $response = array('res'=>'fail','msg'=>'标签ID丢失');
        }

        $tag_id = $_POST['tag_id'];

        if(!$_POST['sms_content']){
            $response = array('res'=>'fail','msg'=>'缺少短信内容');
        }
        $sms_content = trim($_POST['sms_content']);
        $sms_content = str_replace($unsubscribe, '', $sms_content);
        $sms_sign = trim($_POST['sms_sign']);

        if(!$_POST['page']){
            $response = array('res'=>'fail','msg'=>'缺少分页参数');
        }
        $page = $_POST['page'];
        $page = $page - 1;
        $page = ($page >= 0) ? $page : 0;

        $oTag= $this->app->model('member_tag');
        $tagSendInfo = $oTag->getTagSendInfo($tag_id);
        /*if($response['res'] == 'succ'){
         $response = $this->checkSms($tagSendInfo['mobile_valid_nums']);
         }*/

        if($response['res'] == 'succ'){
            $smssendobj= kernel::single('market_service_smsinterface');
            $type='fan-out';
            $sms_list = $oTag->getSmsList($tag_id,$page);
            if($sms_list){
                foreach($sms_list as $k=>$sms){
                    $sms['content'] = $sms_content." {$unsubscribe}【{$sms_sign}】";
                    $sms_list[$k] = $sms;
                }
                
                $content=json_encode($sms_list);
                $result=$smssendobj->send($content,$type);
                $oTag->updateSendTime($tag_id);
                if ($result['res']=='succ'){
                    $response = array('res'=>'succ','info'=>array('page'=>$page+2,'count'=>count($sms_list),'send_status'=>'succ','total'=>$tagSendInfo['mobile_valid_nums']));
                }else{
                    $response = array('res'=>'succ','info'=>array('page'=>$page+2,'count'=>count($sms_list),'send_status'=>'fail'));
                }
                
                //保存全局日志
                $this->save_global_sms_log($content, $result);
            }else{
                $response = array('res'=>'succ');
            }

        }

        echo json_encode($response);
        exit;
    }
    
    function save_global_sms_log($content, $res)
    {
        $sms_list = json_decode($content, true);
        if(!$this->oLog)
            $this->oLog = app::get('taocrm')->model('sms_log');
    
        if($res['res'] != 'succ'){
            $status = 'fail';
            $remark = json_encode($res);
        }else{
            $status = 'succ';
            $remark = '';
        }
    
        $batch_no = date('YmdHi').'tag';
        $op_user = kernel::single('desktop_user')->get_name();
        $ip = $_SERVER['REMOTE_ADDR'];
        
        foreach($sms_list as $v){
            $log = array(
                'source'=>'other',
                'source_id'=>0,
                'batch_no'=>$batch_no,
                'mobile'=>$v['phones'],
                'content'=>$v['content'],
                'status'=>$status,
                'send_time'=>time(),
                'create_time'=>time(),
                'sms_size'=>ceil(mb_strlen($v['content'],'utf-8')/67),
                'cyear'=>date('Y'),
                'cmonth'=>date('m'),
                'cday'=>date('d'),
                'op_user'=>$op_user,
                'ip'=>$ip,
                'remark'=>$remark,
            );
            $this->oLog->insert($log);
        }
    }

    function checkSms($memberNums){
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

    function getSmsCount(){

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
        );
        return $infoarray;
    }



}