<?php
class taocrm_mdl_members extends dbeav_model{

    var $has_tag = false;
    var $defaultOrder = array('update_time','DESC');

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null, $forceIndex='')
    {
        $rs = parent::getList($cols, $filter, $offset, $limit, $orderType, $forceIndex);
        if($rs){
            foreach($rs as $v){
                $member_id[] = $v['member_id'];
            }
            
            //获取每个会员的标签
            if($member_id){
                $oTag = app::get('taocrm')->model('member_tag');
                $tagInfo = $oTag->getMemberTagInfo($member_id);
                if($tagInfo){
                    foreach($rs as $k=>$v){
                        if(isset($tagInfo[$v['member_id']]))
                            $rs[$k]['tagInfo'] = implode('；', $tagInfo[$v['member_id']]);
                    }
                }
            }
        }
        return $rs;
    }

    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        $where = array(1);
        if(isset($filter['ext_order_amount']) && $filter['ext_order_amount']){
            $k = 'ext_order_amount';
            if($filter['_'.$k.'_search']=='between'){
                $amount[] = $filter[$k.'_from'];
                $amount[] = $filter[$k.'_to'];
                $type = 'between';
                $havingSql = str_replace('{field}','amount',dbeav_filter::_inner_getFilterType($type,$amount));
            }else{
                $amount = $filter[$k];
                $type = $filter['_'.$k.'_search'];
                $havingSql = 'amount'.dbeav_filter::_inner_getFilterType($type,$amount,false);
            }

            if(isset($filter['ext_order_createtime']) && $filter['ext_order_createtime']){
                $k = 'ext_order_createtime';
                if($filter['_'.$k.'_search']=='between'){
                    $createtime[] = strtotime($filter[$k.'_from'].' '.$filter['_DTIME_']['H'][$k.'_from'].':'.$filter['_DTIME_']['M'][$k.'_from'].':00');
                    $createtime[] = strtotime($filter[$k.'_to'].' '.$filter['_DTIME_']['H'][$k.'_to'].':'.$filter['_DTIME_']['M'][$k.'_to'].':00');
                    $type = 'between';
                    $whereSql = str_replace('{field}','createtime',dbeav_filter::_inner_getFilterType($type,$createtime));
                }else{
                    $createtime = strtotime($filter[$k].' '.$filter['_DTIME_']['H'][$k].':'.$filter['_DTIME_']['M'][$k].':00');
                    $type = $filter['_'.$k.'_search'];
                    $whereSql = 'createtime'.dbeav_filter::_inner_getFilterType($type,$createtime,false);
                }
            }

            $whereSql = $whereSql ? ' and '.$whereSql : '';

            $sql = 'SELECT `member_id`, sum(`total_amount`) as amount FROM `sdb_ecorder_orders` WHERE 1 '.$whereSql.' group by member_id HAVING '.$havingSql;
            $orders = $this->db->select($sql);

            $member_id[] = 0;
            foreach($orders as $row){
                $member_id[] = $row['member_id'];
            }

            $where[] = ' member_id IN ('.implode(',', $member_id).')';
            unset($filter['ext_order_amount']);
        }
        if(isset($filter['ext_order_createtime']) && $filter['ext_order_createtime']){
            $k = 'ext_order_createtime';
            if($filter['_'.$k.'_search']=='between'){
                $createtime[] = strtotime($filter[$k.'_from'].' '.$filter['_DTIME_']['H'][$k.'_from'].':'.$filter['_DTIME_']['M'][$k.'_from'].':00');
                $createtime[] = strtotime($filter[$k.'_to'].' '.$filter['_DTIME_']['H'][$k.'_to'].':'.$filter['_DTIME_']['M'][$k.'_to'].':00');
                $type = 'between';
            }else{
                $createtime = strtotime($filter[$k].' '.$filter['_DTIME_']['H'][$k].':'.$filter['_DTIME_']['M'][$k].':00');
                $type = $filter['_'.$k.'_search'];
            }

            $orderObj = &app::get(ORDER_APP)->model("orders");
            $rows = $orderObj->getList('order_id,member_id',array('createtime|'.$type=>$createtime));
            $member_id[] = 0;
            foreach($rows as $row){
                $member_id[] = $row['member_id'];
            }

            $where[] = ' member_id IN ('.implode(',', $member_id).')';
            unset($filter['ext_order_createtime']);
        }
        return parent::_filter($filter,$tableAlias,$baseWhere)." AND ".implode($where,' AND ');
    }

    public function change_exp($member_id,$experience,&$msg=''){
        $aMem = $this->dump($member_id,'*',array('contact'=>array('*')));
        if(!$aMem) return null;
        if(!is_numeric($experience)||strpos($experience,".")!==false){
            $msg = app::get('taocrm')->_("请输入整数值");
            return false;
        }
        if($experience<0){
            if($aMem['experience']<-$experience){
                $msg = app::get('taocrm')->_('经验值不足!');
                return false;
            }
        }
        $experience += $aMem['experience'];
        $aMem['experience'] = $experience;
        $aMem['member_lv']['member_group_id'] = $this->member_lv_chk($aMem['member_lv']['member_group_id'],$experience);
        $aMem['member_id'] = $member_id;
        if($aMem['member_id'] && $this->save($aMem)){
            return true;
        }else{
            $msg = app::get('taocrm')->_('保存失败!');
            return false;
        }
    }

    public function member_lv_chk($member_lv_id,$experience){
        $current_member_lv_id = $member_lv_id;
        $memberLvObj = $this->app->model('member_lv');
        $memberLvObj->defaultOrder = array('experience', ' ASC');
        $sdf_lv = $memberLvObj->getList('*');
        foreach($sdf_lv as $sdf){
            if($experience>=$sdf['experience']) $member_lv_id = $sdf['member_lv_id'];
        }
        $current_row = $memberLvObj->getList('experience',array('member_lv_id' => $current_member_lv_id));
        $after_row = $memberLvObj->getList('experience',array('member_lv_id' => $member_lv_id));
        if($current_row[0]['experience']>=$after_row[0]['experience']){
            return $current_member_lv_id;
        }
        return $member_lv_id;
    }

    public function member_lv_change_next($current_lv){
        $memLvObj = $this->app->model('member_lv');
        $next_lv = $memLvObj->get_member_lv_switch($current_lv['member_lv_id']);

        if($current_lv['member_lv_id'] && $current_lv['experience']>=0){
            $where = '`experience`>='.$current_lv['experience'];

            if($next_lv['show']=='YES' && $next_lv['experience']){
                $where .= ' and `experience`<'.$next_lv['experience'];
                $sql = 'UPDATE sdb_taocrm_members SET `member_lv_id`='.$current_lv['member_lv_id'].' WHERE '.$where;
                if($this->db->exec($sql)){
                    $this->member_lv_change_next($next_lv);
                    return true;
                }else{
                    return false;
                }
            }else{
                $sql = 'UPDATE sdb_taocrm_members SET `member_lv_id`='.$current_lv['member_lv_id'].' WHERE '.$where;
                if($this->db->exec($sql)){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    public function member_lv_change_pre($current_lv){
        $memLvObj = $this->app->model('member_lv');
        $pre_lv = $memLvObj->get_member_lv_switch($current_lv['member_lv_id'],$type='pre');

        if($current_lv['member_lv_id'] && $current_lv['experience']>=0){
            if($pre_lv['show']=='YES' && $pre_lv['experience']){
                $where = '`experience`>='.$pre_lv['experience'];
                $where .= ' and `experience`<'.$current_lv['experience'];
                $sql = 'UPDATE sdb_taocrm_members SET `member_lv_id`='.$pre_lv['member_lv_id'].' WHERE '.$where;

                if($this->db->exec($sql)){
                    $this->member_lv_change_pre($pre_lv);
                    return true;
                }else{
                    return false;
                }
            }else{
                $where = '`experience`<'.$current_lv['experience'];
                $default_lv = $memLvObj->get_default_lv();
                $default_lv = $default_lv ? $default_lv : 0;
                $sql = 'UPDATE sdb_taocrm_members SET `member_lv_id`='.$default_lv.' WHERE '.$where;
                if($this->db->exec($sql)){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    // 根据新等级规则，更新客户等级信息
    public function shop_lv_change($current_lv){

        $lv_id = $current_lv['lv_id'];
        $shop_id = $current_lv['shop_id'];
        $oMembers = $this->app->model('member_analysis');
        $sql = "select member_id from sdb_taocrm_member_analysis where lv_id=$lv_id and shop_id='$shop_id' ";
        $arr_member = $oMembers->db->select($sql);

        if($arr_member){
            foreach($arr_member as $v) {
                kernel::single('taocrm_service_member')->updateMemberLv($v['member_id'],$shop_id);
            }
            return true;
        }else{
            return true;
        }
    }

    // 更新客户等级信息
    /*
     public function shop_lv_change_pre($current_lv){
     return true;

     $shopLvObj = $this->app->model('shop_lv');
     $pre_lv = $shopLvObj->get_member_lv_switch($current_lv,$type='pre');

     if($current_lv['shop_lv_id'] && $current_lv['experience']>=0){
     if($pre_lv['show']=='YES' && $pre_lv['experience']){
     $where = '`shop_id`=\''.$current_lv['shop_id'].'\' and `experience`>='.$pre_lv['experience'];
     $where .= ' and `experience`<'.$current_lv['experience'];
     $sql = 'UPDATE sdb_taocrm_members SET `shop_lv_id`='.$pre_lv['shop_lv_id'].' WHERE '.$where;

     if($this->db->exec($sql)){
     $this->shop_lv_change_pre($pre_lv);
     return true;
     }else{
     return false;
     }
     }else{
     $where = '`shop_id`=\''.$current_lv['shop_id'].'\' and `experience`<'.$current_lv['experience'];
     $default_lv = $shopLvObj->get_default_lv($current_lv['shop_id']);
     $default_lv = $default_lv ? $default_lv : 0;
     $sql = 'UPDATE sdb_taocrm_members SET `shop_lv_id`='.$default_lv.' WHERE '.$where;
     if($this->db->exec($sql)){
     return true;
     }else{
     return false;
     }
     }
     }else{
     return false;
     }
     }
     */

    public function getRemarkByMemId($nMemberId){
        $row = $this->getList('remark,remark_type',array('member_id'=>$nMemberId ));
        return $row[0];
    }

    public function is_exists_email($email=null,$member_id=null){
        if(!$email) return true;
        $aEmail = $this->getList('member_id',array('email' => $email));
        if($aEmail && !$member_id) return true;
        if($aEmail && ($member_id != $aEmail[0]['member_id'])) return true;
        return false;
    }

    //按商品信息搜索客户 表members  //存在      生日范围   地区   客户信息  保留合集数组 array_intersect()
    public function mem_regb($_POST){
        $mem_obj = app::get('taocrm')->model('members');
        $member_array=array();
        //客户的来源店铺
        $member_array['shop_id']=$_POST['shop_id'];
        //客户的生日
        switch ($_POST['birthday_symbol']) {
            case 1:
                $member_array['birthday|than']=strtotime($_POST['birthday_id']);
                break;
            case 2:
                $member_array['birthday|lthan']=strtotime($_POST['birthday_big']);
                break;
            case 3:
                $member_array['birthday|nequal']=strtotime($_POST['birthday_big']);
                break;
            case 4:
                $member_array['birthday|bthan']=strtotime($_POST['birthday_big']);
                break;
            case 5:
                $member_array['birthday|sthan']=strtotime($_POST['birthday_big']);
                break;
            case 6:
                $member_array['birthday|between']=array(strtotime($_POST['birthday_big']),strtotime($_POST['birthday_small']));
                break;
        }
        //客户所在的地区
        if (!empty($_POST['area'])){
            $member_array['state|in']=$_POST['area'];
        }
        if (!empty($member_array)){
            $member_data=$mem_obj->getList('member_id',$member_array,0,-1);
            $member_array1=array();
            foreach ($member_data as $k=>$v){
                $member_array1[]=$v[member_id];
            }
            return $member_array1;
        }
    }

    /**
     * @desc 补全计划：更新客户的地区信息
     * @params mainland:辽宁/沈阳市/沈河区:1877
     */
    public function chkMemberArea($member_id,$shop_id){

        $oRegions = &app::get('ectools')->model('regions');
        $sql = "SELECT member_id,area FROM sdb_taocrm_members WHERE ISNULL(state) ";
        if(is_numeric($member_id)) $sql .= " AND member_id=$member_id ";
        if($shop_id) $sql .= " AND shop_id='$shop_id' ";
        $sql .= " limit 1000 ";
        $rs = $this->db->select($sql);
        if(!$rs) return true;

        foreach($rs as $v){
            unset($member_area);
            $area = explode('/',str_replace('mainland:','',$v['area']));
            if(sizeof($area)<2) continue;
            if(!isset($area[2])){
                $district = explode(':',$area[1]);
                $member_area['city'] = $district[1];
                $member_area['state'] = $oRegions->checkDlArea($area[0],false);
            }else{
                $district = explode(':',$area[2]);
                $member_area['district'] = $district[1];
                $member_area['state'] = $oRegions->checkDlArea($area[0],false);
                $member_area['city'] = $oRegions->checkDlArea($area[1],false);
            }

            $this->update($member_area,array('member_id'=>$v['member_id']));
        }

        return true;
    }

    /**
     * @desc 补全计划：更新客户的统计信息
     * @params int $member_id
     */
    public function runAnalysisById($member_id,$limit,$shop_id){

        $oMembers = kernel::single('taocrm_service_member');

        if(is_numeric($member_id)){
            $sql = "SELECT member_id,shop_id FROM sdb_taocrm_member_analysis
                    WHERE member_id=$member_id ";
        }else{
            $sql = "SELECT member_id,shop_id FROM sdb_taocrm_member_analysis
                    WHERE ISNULL(first_buy_time) ";
        }
        if($shop_id) $sql .= " and shop_id='$shop_id' ";
        if($limit) $sql .= " limit $limit ";
        $rs = $this->db->select($sql);
        if(!$rs) return 'finish';

        foreach($rs as $v){
            $oMembers->countMemberBuys($v['member_id'],$v['shop_id']);
        }
        return $v['member_id'];
    }

    /**
     * @desc 补全计划：更新客户的统计信息全部
     * @params int $member_id
     */
    public function runAnalysisByAll($member_id,$limit,$shop_id)
    {
        $oMembers = kernel::single('taocrm_service_member');

        if(is_numeric($member_id)){
            $sql = "SELECT member_id,shop_id FROM sdb_taocrm_member_analysis
                    WHERE member_id=$member_id ";
            if($shop_id) $sql .= " and shop_id='$shop_id' order by member_id";
        }else{
            $sql = "SELECT member_id,shop_id FROM sdb_taocrm_member_analysis   ";
            if($shop_id) $sql .= " where shop_id='$shop_id' order by member_id";
        }

        if($limit) $sql .= " limit $limit ";
        $rs = $this->db->select($sql);
        if(!$rs) return 'finish';

        foreach($rs as $v){
            $oMembers->countMemberBuys($v['member_id'],$v['shop_id']);
        }
        return $v['member_id'];
    }

    public function getAllAreasInfo($member_ids)
    {
        $rs = $this->getList('member_id,area,name', array('member_id'=>$member_ids));
        if($rs){
            foreach($rs as $v){
                $areas[$v['member_id']] = $v;
                $areas[$v['member_id']]['area'] = $this->clear_area($v['area']);
            }
        }
        return $areas;
    }

    public function getAreasInfo($member_id)
    {
        $area = $this->dump(array('member_id' => $member_id), 'area');
        if ($area) {
            $area = $this->clear_area($area['contact']['area']);
        }
        return $area;
    }
    
    //格式化地区字符串
    public function clear_area($area)
    {
            //mainland:湖北/孝感市/应城市:1419
            $pattern = "/^mainland:(.*):[0-9]+$/";
        preg_match($pattern, $area, $result);
            if (isset($result[1]) && $result[1]) {
                $area = $result[1];
        }else{
                $area = '';
        }
        return $area;
    }

    public function getAddrInfo($member_id) {
        $area = $this->dump(array('member_id' => $member_id), 'addr');
        return $area['contact']['addr'];
    }
    
    public function get_member_info($filter, $fields='*'){
        $rs = $this->dump($filter, $fields);
        return $rs;
    }

    function modifier_channel_type($row){
        if ($row){
            $channelTypeList = $this->getChannelTypeList();
            return $channelTypeList[$row];
        }else{
            return '-';
        }
    }

    function modifier_state($row){
        if ($row){
            $objRegions = &app::get('ectools')->model('regions');
            $row = $objRegions->getById($row);
            return $row['local_name'];
        }else{
            return '-';
        }
    }

    function modifier_city($row){
        if ($row){
            $objRegions = &app::get('ectools')->model('regions');
            $row = $objRegions->getById($row);
            return $row['local_name'];
        }else{
            return '-';
        }
    }

    function modifier_district($row){
        if ($row){
            $objRegions = &app::get('ectools')->model('regions');
            $row = $objRegions->getById($row);
            return $row['local_name'];
        }else{
            return '-';
        }
    }



    /**
     *
     * 获取渠道类型列表
     */
    function getChannelTypeList(){

        return array('unknow'=>'未知','manual_entry'=>'手动录入','taobao'=>'淘宝', 'paipai'=>'拍拍', '360buy'=>'京东商城','shopex_b2c'=>'48体系网店','ecos.b2c'=>'ec-store','shopex_b2b'=>'shopex分销王','ecos.dzg'=>'shopex店掌柜','yihaodian'=>'一号店','fenxiao'=>'淘宝分销','amazon'=>'亚马逊','dangdang'=>'当当','alibaba'=>'阿里巴巴','ecos.ome'=>'后端业务处理系统','ecshop_b2c' => 'ecshop','other'=>'其它');
    }

    /**
     *
     * 通过合并维度展示可以合并的会员数据
     * @param string $dimensions
     */
    function showBindMembers($dimensions='mobile'){
        $sql = ' SELECT count( * ) AS total, GROUP_CONCAT( member_id ) AS member_id_key
FROM sdb_taocrm_members
WHERE '.$dimensions.' != "" and parent_member_id = 0 
GROUP BY '.$dimensions.'
HAVING total >1 ';
        $row = $this->db->selectRow('select count(*) as total from ('.$sql.') as a');
        $total = intval($row['total']);
        $sql .=' LIMIT 0 , 5';
        $rows = $this->db->select($sql);
        $memberIds = array();
        foreach($rows as $row){
            $ids = explode(',', $row['member_id_key']);
            if($ids){
                foreach($ids as $id){
                    $memberIds[] = $id;
                }
            }
        }

        $memberIds = array_slice($memberIds,0,10);
        $rows = $this->db->select('select uname,name,mobile,addr,update_time,email,qq,weibo,weixin,wangwang,alipay_no from sdb_taocrm_members where member_id in('.implode(',', $memberIds).') order by '.$dimensions);

        return array('total'=>$total,'data'=>$rows);
    }

    /**
     *
     * 合并会员，向后端发起合并请求
     * @param String $bindDimensions
     */
    function doRequestBind($bindDimensions, &$msg)
    {
        $memberBindQueueObj = app::get('taocrm')->model('member_bind_queue');
        if(!$memberBindQueueObj->check($bindDimensions)){
            $msg = '相同合并任务正在处理中,请稍后再试!';
            return false;
        }
        
        $data = array(
            'title'=>$title,
            'bind_dimensions'=>$bindDimensions,
            'create_time'=>time(),
            'is_send'=>'unsend',
        );
        $memberBindQueueObj->save($data);
        $queue_id = $data['queue_id'];
        
        //主帐号店铺
        base_kvstore::instance('ecorder')->fetch('main_account_shop', $shop_id);

        $connect = new taocrm_middleware_connect;
        $result = $connect->requestBindMember($queue_id, $bindDimensions, $shop_id);
        if(isset($result['status'])){
            if($result['status'] == true){
                return true;
            }else{
                $data = array(
                    'finish_time'=>time(),
                    'is_send'=>'fail',
                );
                $memberBindQueueObj->update($data, array('queue_id'=>$queue_id));
        
                $msg = '请求全局客户合并服务失败,稍后请重试!';
                return false;
            }
        }else{
            $data = array(
                'finish_time'=>time(),
                'is_send'=>'fail',
            );
            $memberBindQueueObj->update($data, array('queue_id'=>$queue_id));
            
            $msg = '请求接口错误：'.$result;
            return false;
        }
    }

    /**
     *
     * 根据用户名关键字搜索会员
     * @param String $keywords
     */
    function searchMemberByKeywords($keywords){
        $rows = $this->db->select('select member_id,uname,name,mobile,addr,update_time,email,qq,weibo,weixin,wangwang,alipay_no from sdb_taocrm_members where uname="'.$keywords.'"  and parent_member_id = 0 ');

        foreach($rows as $k=>$row){
            $rows[$k]['qq'] = !is_null( $rows[$k]['qq']) ?  $rows[$k]['qq'] : '';
            $rows[$k]['weixin'] = !is_null( $rows[$k]['weixin']) ?  $rows[$k]['weixin'] : '';
        }

        return $rows;
    }

    /**
     *
     * 指定合并
     * @param Array $fromMemberIds
     * @param Int $toMemberId
     */
    function doAssignBind($fromMemberIds,$toMemberId,& $msg)
    {
        if(empty($fromMemberIds)){
            $msg = '被合并的会员为空';
            return false;
        }

        if(count($fromMemberIds) == 1 && $fromMemberIds[0] == $toMemberId){
            return false;
        }

        $index = array_search($toMemberId,$fromMemberIds);
        if($index){
            unset($fromMemberIds[$index]);
        }

        $fromMembers = $this->db->select('select member_id,uname,name,mobile,addr,email,qq,weibo,weixin,wangwang,alipay_no from sdb_taocrm_members where member_id in('.implode(',', $fromMemberIds).')');
        $toMember = $this->db->selectRow('select * from sdb_taocrm_members where member_id='.$toMemberId);
        $updateCols = array();
        foreach($fromMembers as $member){
            $this->db->exec('update sdb_taocrm_members set parent_member_id = '.$toMemberId.',is_merger=1 where member_id='.$member['member_id']);
            $this->db->exec('update sdb_taocrm_members set parent_member_id = '.$toMemberId.',is_merger=1 where parent_member_id='.$member['member_id']);
            foreach($member as $col=>$val){
                if($col != 'member_id' && !empty($val) && empty($toMember[$col])){
                    $updateCols[] = $col.'="'.$val.'"';
                }
            }
        }

        if( ! empty($updateCols)){
            $sql = 'update sdb_taocrm_members set '.implode(',', $updateCols).' where member_id='.$toMemberId;
            $this->db->exec($sql);
        }
        return true;
    }
    
    /**
     * 获取会员自定义属性设置
     * @shop_id 店铺ID,店铺ID为空时,返回全局属性
     */
    public function get_member_prop($shop_id='all')
    {
        $member_prop = array(
            'prop_name'=>array('','','','','','','','','',''),
            'prop_type'=>array('','','','','','','','','',''),
        );
        if( $shop_id == 'all' ){
            base_kvstore::instance('ecorder')->fetch('overall_member_props', $overall_member_props);
            if($overall_member_props){
                $member_prop = json_decode($overall_member_props, true);
                if( ! isset($member_prop['prop_name'])){
                    //兼容老的配置方式
                    $member_prop = array(
                        'prop_name' => $member_prop,
                        'prop_type' => array('','','','','','','','','',''),
                    );
                }

                if(count($member_prop['prop_name']) < 10){
                    for($i=count($member_prop['prop_name']);$i<=10;$i++){
                        $member_prop['prop_name'][] = '';
                        $member_prop['prop_type'][] = '';
                    }
                }else{
                    $member_prop1 = array();
                    for($j=0;$j< 10;$j++){
                        $member_prop1['prop_name'][] = $member_prop['prop_name'][$j];
                        $member_prop1['prop_type'][] = $member_prop['prop_type'][$j];
                    }
                    $member_prop = $member_prop1;
                }
            }
        }else{
            $shop = app::get('ecorder')->model('shop')->dump($shop_id);
            $shop_config = unserialize($shop['config']);
            if($shop_config['prop_name']) $member_prop['prop_name'] = $shop_config['prop_name'];
            if($shop_config['prop_type']) $member_prop['prop_type'] = $shop_config['prop_type'];
        }
        
        return $member_prop;
    }
    
    //获取单个会员的自定义属性的值
    public function get_member_prop_val($member_id='', $shop_id='all')
    {
        if( ! $member_id) return false;
        
        $prop_val = array('','','','','','','','','','');
        $oMemberProp = $this->app->model('member_attr');
        $filter = array(
            'member_id'=>$member_id,
            'shop_id' => $shop_id,
        );
        $rs_prop = $oMemberProp->dump($filter);
        if($rs_prop){
            $prop_val = array(
                $rs_prop['attr1'],
                $rs_prop['attr2'],
                $rs_prop['attr3'],
                $rs_prop['attr4'],
                $rs_prop['attr5'],
                $rs_prop['attr6'],
                $rs_prop['attr7'],
                $rs_prop['attr8'],
                $rs_prop['attr9'],
                $rs_prop['attr10'],
            );
            
            //处理自动补全的数据
            $member_prop = $this->get_member_prop($shop_id);
            foreach($member_prop['prop_type'] as $k=>$v){
                if($v=='num' && $prop_val[$k]){
                    $prop_val[$k] = floatval($prop_val[$k]);
                }elseif($v=='date' && $prop_val[$k] && !strstr($prop_val[$k],'-')){
                    $prop_val[$k] = substr($prop_val[$k],0,4).'-'.substr($prop_val[$k],4,2).'-'.substr($prop_val[$k],6,2);
                }else{
                    $prop_val[$k] = trim($prop_val[$k]);
                }
            }
        }
        
        return $prop_val;
    }
    
    //保存单个会员的自定义属性的值，并同步到java
    public function save_member_prop_val($props=array(), $member_id='', $shop_id='all')
    {
        if( ! $member_id) return false;
        
        $oMemberProp = $this->app->model('member_attr');
        $save_arr = array(
            'update_time' => time(),
            'member_id' => $member_id,
            'shop_id' => $shop_id,
            'attr1' => (string)$props[0],
            'attr2' => (string)$props[1],
            'attr3' => (string)$props[2],
            'attr4' => (string)$props[3],
            'attr5' => (string)$props[4],
            'attr6' => (string)$props[5],
            'attr7' => (string)$props[6],
            'attr8' => (string)$props[7],
            'attr9' => (string)$props[8],
            'attr10' => (string)$props[9],
        );
        
        //对特殊数据做补全处理
        $member_prop = $this->get_member_prop($shop_id);
        foreach($member_prop['prop_type'] as $k=>$v){
            if($v=='num' && $save_arr['attr'.($k+1)]){
                $save_arr['attr'.($k+1)] = substr('000000000'.$save_arr['attr'.($k+1)], -10);
            }elseif($v=='date' && $save_arr['attr'.($k+1)]){
                $arr = explode('-', $save_arr['attr'.($k+1)]);
                $save_arr['attr'.($k+1)] = $arr[0] . substr('0'.$arr[1], -2) . substr('0'.$arr[2], -2);
            }else{
                $save_arr['attr'.($k+1)] = trim($save_arr['attr'.($k+1)]);
            }
        }
        
        $filter = array(
            'member_id'=>$member_id,
            'shop_id' => $shop_id,
        );
        $rs = $oMemberProp->dump($filter);
        if($rs){
            $oMemberProp->update($save_arr, array('attr_id'=>$rs['attr_id']));
        }else{
            $attr_id = $oMemberProp->insert($save_arr);
        }
        
        $save_arr = array_merge($filter,$save_arr);
        $save_arr['attrId'] = $rs ? $rs['attr_id'] : $attr_id;
        $all_res = kernel::single('taocrm_middleware_connect')->SetMemberAttrInfo($save_arr);
    }
    
    //获取指定会员的统计数据
    public function get_analysis($member_id=0, $shop_id='')
    {
        $analysis = array(
            'total_orders' => 0,
            'total_amount' => 0,
            'total_per_amount' => 0,
            'points' => 0,
            'lv_id' => '',//普通会员
            'refund_orders' => 0,
            'refund_amount' => 0,
            'finish_orders' => 0,
            'finish_total_amount' => 0,
            'finish_per_amount' => 0,
            'unpay_orders' => 0,
            'unpay_amount' => 0,
            'unpay_per_amount' => 0,
            'buy_freq' => 0,
            'buy_month' => 0,
            'buy_skus' => 0,
            'buy_products' => 0,
            'avg_buy_skus' => 0,
            'avg_buy_products' => 0,
            'first_buy_time' => 0,
            'last_buy_time' => 0,
            'shop_evaluation' => '',
        );
        
        $db = $this->db;
        $order_ids = array();
        $buy_month = array();
        $sql = "select order_id,total_amount,pay_status,status,createtime,payed,item_num,skus from sdb_ecorder_orders where member_id=".$member_id." ";
        if($shop_id){
            $sql .= " AND shop_id='$shop_id' ";
        }
        $rs_orders = $db->select($sql);
        if($rs_orders){
            foreach($rs_orders as $v){
                if($analysis['first_buy_time']==0) $analysis['first_buy_time']=$v['createtime'];
                if($analysis['last_buy_time']==0) $analysis['last_buy_time']=$v['createtime'];
            
                $order_ids[] = $v['order_id'];
                $buy_month[date('Ym', $v['createtime'])] = 1;
                $analysis['total_orders'] ++;
                $analysis['buy_products'] += $v['item_num'];
                $analysis['buy_skus'] += $v['skus'];
                $analysis['total_amount'] += $v['total_amount'];
                $analysis['first_buy_time'] = min($analysis['first_buy_time'],$v['createtime']);
                $analysis['last_buy_time'] = max($analysis['last_buy_time'],$v['createtime']);
                
                if($v['status']=='finish'){
                    $analysis['finish_orders'] ++;
                    $analysis['finish_total_amount'] += $v['total_amount'];
                }
                
                if($v['pay_status']==5){
                    $analysis['refund_orders'] ++;
                    $analysis['refund_amount'] += $v['total_amount'];
                }elseif($v['pay_status']==0){
                    $analysis['unpay_orders'] ++;
                    $analysis['unpay_amount'] += $v['total_amount'];
                }
            }
            
            $sql = "select count(*) as buy_skus from sdb_ecorder_order_items where order_id in (".implode(',', $order_ids).") group by goods_id ";
            $rs_order_items = $db->selectRow($sql);
            $analysis['buy_skus'] = $rs_order_items['buy_skus'];
            
            $analysis['buy_freq'] = round(($analysis['last_buy_time'] - $analysis['first_buy_time'])/($analysis['total_orders']*86400), 2);
            $analysis['total_per_amount'] = round($analysis['total_amount']/$analysis['total_orders'], 2);
            $analysis['finish_per_amount'] = round($analysis['finish_total_amount']/$analysis['finish_orders'], 2);
            $analysis['unpay_per_amount'] = round($analysis['unpay_amount']/$analysis['unpay_orders'], 2);
            $analysis['avg_buy_skus'] = round($analysis['buy_skus']/$analysis['total_orders'], 2);
            $analysis['avg_buy_products'] = round($analysis['buy_products']/$analysis['total_orders'], 2);
            $analysis['buy_month'] = count($buy_month);
            $analysis['first_buy_time'] = date('Y-m-d H:i:s', $analysis['first_buy_time']);
            $analysis['last_buy_time'] = date('Y-m-d H:i:s', $analysis['last_buy_time']);
        }
        
        if(!$analysis['first_buy_time']) $analysis['first_buy_time'] = '-';
        if(!$analysis['last_buy_time']) $analysis['last_buy_time'] = '-';

        if($shop_id){
            //kernel::single('taocrm_service_member')->updateMemberLv($member_id, $shop_id);
        
            $sql = 'select a.id,b.name,a.points,a.is_vip from sdb_taocrm_member_analysis as a 
                    left join sdb_ecorder_shop_lv as b on a.lv_id=b.lv_id
                    where a.member_id='.$member_id.' and a.shop_id="'.$shop_id.'" ';
            $rs = $db->selectRow($sql);
            if($rs){
                $analysis_id = $rs['id'];
                $analysis['lv_id'] = $rs['name'];
                $analysis['points'] = $rs['points'];
                $analysis['is_vip'] = ($rs['is_vip']=='true' ? '是' : '否');
                
                $this->update_member_analysis($analysis_id, $analysis);
            }
        }
        
        return $analysis;
    }
    
    //更新店铺会员分析表的数据
    function update_member_analysis($analysis_id, $analysis)
    {
        //vdump($analysis);
        $save = array(
            'total_orders' => $analysis['total_orders'],
            'total_amount' => $analysis['total_amount'],
            'total_per_amount' => $analysis['total_per_amount'],
            'buy_freq' => $analysis['buy_freq'],
            //'avg_buy_interval' => $analysis['avg_buy_interval'],
            'buy_month' => $analysis['buy_month'],
            'buy_skus' => $analysis['buy_skus'],
            'buy_products' => $analysis['buy_products'],
            'avg_buy_skus' => $analysis['avg_buy_skus'],
            'avg_buy_products' => $analysis['avg_buy_products'],
            'finish_orders' => $analysis['finish_orders'],
            'finish_total_amount' => $analysis['finish_total_amount'],
            'finish_per_amount' => $analysis['finish_per_amount'],
            'unpay_orders' => $analysis['unpay_orders'],
            'unpay_amount' => $analysis['unpay_amount'],
            'unpay_per_amount' => $analysis['unpay_per_amount'],
            'refund_orders' => $analysis['refund_orders'],
            'refund_amount' => $analysis['refund_amount'],
            'first_buy_time' => strtotime($analysis['first_buy_time']),
            'last_buy_time' => strtotime($analysis['last_buy_time']),
            'update_time' => time(),
        );
        $this->app->model('member_analysis')->update($save, array('id'=>$analysis_id));
    }
    
    function get_member($data,$col='uname')
    {
        if ($col == 'mobile'){
            $sql = "SELECT member_id,uname,name,area,mobile,email,sex,ext_uid FROM `sdb_taocrm_members` WHERE mobile LIKE '".$data."%' LIMIT 0 , 11";
        }elseif($col == 'name') {
            $sql = "SELECT member_id,uname,name,area,mobile,email,sex,ext_uid FROM `sdb_taocrm_members` WHERE name LIKE '".$data."%' LIMIT 0 , 11";
        }else {
            $sql = "SELECT member_id,uname,name,area,mobile,email,sex,ext_uid FROM `sdb_taocrm_members` WHERE uname LIKE '".$data."%' LIMIT 0 , 11";
        }
        $rows = $this->db->select($sql);
        return $rows;
    }

    function modifier_level_id($row){
        if ($row != '0'){
            $data = $this->app->model('member_level')->dump(array('level_id'=>$row));
            return $data['level_name'];
        }else{
            return '-';
        }
    }
    
}
