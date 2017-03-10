<?php
/**
 * 外部客户导入接口，CS客户端工具专用
 */
class taocrm_rpc_response_member extends taocrm_rpc_response
{

    public function add_new($sdf, &$responseObj)
    {
        if(!$sdf['group_id']){ 
            $responseObj->send_user_error(app::get('base')->_('分组ID为空!'),0);
        }

        if(!$sdf['batch_id']){
            $responseObj->send_user_error(app::get('base')->_('分组批次ID为空!'),0);
        }

        //写入API日志
        $log = app::get('ecorder')->model('api_log');
        $logTitle = '客户导入接口[uname：'. $sdf['uname'] .']';
        $logInfo = '客户导入接口：<br/>';
        $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<br/>';
        $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'success', $logInfo.$msg, array('task_id'=>$sdf['uname']));

        $insert_num = 0;
        $members[] = $sdf;
        if($members){
            $db = kernel::database();
            $db->exec('START TRANSACTION;');
            $curtime = time();
            foreach($members as $member){
            
                $member = kernel::single('ecorder_func')->trim_array($member);
                $member = kernel::single('ecorder_func')->clear_value($member);
                $this->formart_data($member);
            
                //忽略无效数据
                if(!$member['uname'] && !$member['real_name'] && !$member['mobile'] 
                    && !$member['wangwang'] && !$member['qq'] && !$member['email']){
                    continue;
                }
            
                //创建会员
                $this->process_member($member);               
            
                if(!isset($member['mobile']) || empty($member['mobile'])){
                    $is_mobile_valid = 0;
                }else{
                    if(strlen($member['mobile']) == 11){
                        $is_mobile_valid = 1;
                    }else{
                        $is_mobile_valid = 0;
                    }
                }

                if(!isset($member['email']) || empty($member['email'])){
                    $is_email_valid = 0;
                }else{
                    $is_email_valid = 1;
                }

                if(!$is_mobile_valid && !$is_email_valid && !$member['member_id']){
                    //continue;
                }

                $md5 = md5($sdf['group_id'].$member['mobile'].$member['email']);
                $row = $db->selectRow('select member_id from sdb_taocrm_member_import where unique_md5="'.$md5.'"');
                $data = array(
                     'group_id'=>$sdf['group_id'],
                     'batch_id'=>$sdf['batch_id'],
                     'uname'=>$member['real_name'] ? $member['real_name'] : $member['uname'],
                     'mobile'=>$member['mobile'],
                     'email'=>$member['email'],
                     'update_time'=>$curtime,
                     'succ_member_id'=>floatval($member['member_id']),
                     'is_mobile_valid'=>$is_mobile_valid,
                     'is_email_valid'=>$is_email_valid,
                );
                if(!$row){
                    $data['create_time'] = $curtime;
                    $data['unique_md5'] = $md5;
                    $db->insert("sdb_taocrm_member_import",$data);
                    $insert_num++;
                }
            }
            $db->exec('COMMIT;');

            if($insert_num > 0){
                app::get('taocrm')->model('member_import_batch')->countNums($sdf['group_id'],$sdf['batch_id']);
            }

            return $insert_num;
        }else{
            $responseObj->send_user_error(app::get('base')->_('上传客户为空!'),0);
        }
    }
    
    public function formart_data(&$member_info)
    {
        $member_info['sex'] == '女' ? $member_info['sex']='female' : $member_info['sex']='male';
        if($member_info['birthday']){
        
            $member_info['birthday'] = substr($member_info['birthday'],0,10);
            $member_info['birthday'] = 
                str_replace(array('.','/'),'-',$member_info['birthday']);
            list($member_info['b_year'], $member_info['b_month'], $member_info['b_day']) = 
                explode('-', $member_info['birthday']);
        
            $member_info['birthday']=strtotime($member_info['birthday']);
        }else{
            unset($member_info['birthday']);
        }
        
        $member_info['uname'] = $this->clear_chars($member_info['uname']);
        $member_info['real_name'] = $this->clear_chars($member_info['real_name']);
        $member_info['mobile'] = $this->clear_chars($member_info['mobile']);
    }
    
    /**
     * 处理会员信息
     *
     * @param array $sdf
     * @return null
     */
    public function process_member(&$member_info)
    {
        //优先级：旺旺 qq 手机 邮箱
        $member_filter = array();
        if($member_info['email'])    $member_filter = array('email'=>$member_info['email']);
        if($member_info['mobile'])   $member_filter = array('mobile'=>$member_info['mobile']);
        if($member_info['qq'])       $member_filter = array('qq'=>$member_info['qq']);
        if($member_info['wangwang']) $member_filter = array('wangwang'=>$member_info['wangwang']);
            
        $area = $member_info['state'] . '/' . $member_info['city'] . '/' . $member_info['district'];
        kernel::single("ecorder_func")->region_validate($area);
        $area = str_replace('::', '', $area);
        $member_info['area'] = $area;

        if(!empty($member_info['state'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$member_info['state'].'"');
            $member_info['state'] = $row['region_id'];
        }

        if(!empty($member_info['city'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$member_info['city'].'"');
            $member_info['city'] = $row['region_id'];
        }

        if(!empty($member_info['district'])){
            $row = kernel::database()->selectrow('select region_id from sdb_ectools_regions where local_name="'.$member_info['district'].'"');
            $member_info['district'] = $row['region_id'];
        }
            
        //保存会员信息
        $model_members = app::get('taocrm')->model('members'); 
        $rs_member = $model_members->dump($member_filter, 'member_id');
        $member_info['name'] = $member_info['real_name'];
        if(!$member_info['uname']){
            $member_info['uname'] = $member_info['real_name'];
        }
        
        if(!$rs_member){
            $member_info['create_time'] = time();
            $model_members->insert($member_info);
        }elseif($member_info['only_custom_attr']=='no'){
            $member_info['update_time'] = time();
            $model_members->update($member_info, array('member_id'=>$rs_member['member_id']));
            $member_info['member_id'] = $rs_member['member_id'];
        }
        
        //会员分析表
        if($member_info['shop_id']){
            $m_member_analysis = app::get('taocrm')->model('member_analysis');
            $member_analysis = array(
                'member_id' => $member_info['member_id'],
                'shop_id' => $member_info['shop_id'],
            );
            $m_member_analysis->insert($member_analysis);
        }
        
        $taocrm_service_member = kernel::single('taocrm_service_member');
        $taocrm_service_member->saveMemberContact($member_info);
        $taocrm_service_member->saveMemberReceiver($member_info);
        
        //会员自定义属性 
        $this->process_member_attr($member_info);
        
        //会员扩展属性 
        $this->process_member_ext($member_info);
    }
    
    //会员扩展属性
    function process_member_ext($member_info)
    {
        if($member_info['b_year']){
            $ext_info = array();
            $ext_info['member_id'] = $member_info['member_id'];
            $ext_info['b_year'] = $member_info['b_year'];
            $ext_info['b_month'] = $member_info['b_month'];
            $ext_info['b_day'] = $member_info['b_day'];
            kernel::single('taocrm_service_member')->save_member_ext($ext_info);
        }
    }
    
    //写入会员自定义属性
    public function process_member_attr($member_info)
    {  
        if( ! $member_info['shop_id']) $member_info['shop_id'] = 'all';
        
        $model_member_attr = app::get('taocrm')->model('member_attr');
        if($member_info['custom_attr1'] or $member_info['custom_attr2'] or $member_info['custom_attr3'] 
            or $member_info['custom_attr4'] or $member_info['custom_attr5'] or $member_info['custom_attr6'] 
            or $member_info['custom_attr7'] or $member_info['custom_attr8'] or $member_info['custom_attr9'] 
            or $member_info['custom_attr10']
        ){
            $save_attr = array(
                'update_time' => time(),
                'member_id' => $member_info['member_id'],
                'shop_id' => $member_info['shop_id'],
            );
            for($i=1;$i<=10;$i++){
                if($member_info['custom_attr'.$i])
                    $save_attr['attr'.$i] = $member_info['custom_attr'.$i];
            }
            $rs_member_attr = $model_member_attr->dump(array('member_id'=>$member_info['member_id'], 'shop_id'=>$member_info['shop_id']), 'attr_id');
            if($rs_member_attr){
                $save_attr['attr_id'] = $rs_member_attr['attr_id'];
            }else{
                $save_attr['create_time'] = time();
            }
            $model_member_attr->save($save_attr);
        }
    }

    public function getBatch($sdf, &$responseObj)
    {
        $db = kernel::database();
        $data = array(
                     'group_id'=>$sdf['group_id'],
                     'create_time'=>time(),
        );
        $db->insert("sdb_taocrm_member_import_batch",$data);

        return $db->lastinsertid();
    }

    public function grouplist($sdf, &$responseObj)
    {
        $db = kernel::database();
        $rows = $db->select('select group_id,group_name,total_nums from sdb_taocrm_member_import_group order by group_id');

        return $rows;
    }

    /**
     *  过滤非法字符
     */
    public function clear_chars($str)
    {
        if($str){
            $str = str_replace("'", '', $str);
            $str = str_replace('"', '', $str);
            $str = str_replace(array('“', '”'), '', $str);
            $str = str_replace('=', '', $str);
            $str = str_replace(' ', '', $str);
            $str = trim($str);
        }
        return $str;
    }
    
}
