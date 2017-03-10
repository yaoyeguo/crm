<?php
class taocrm_ctl_admin_fx_member_group extends desktop_controller {

    public function __construct($app) {
        parent::__construct($app);
        $this->interfacePacketName = 'ShopMemberAnalysis';
        $this->interfaceMethodName = 'FxSearchMemberAnalysisList';
        $this->interfaceTableName = 'taocrm_mdl_middleware_member_analysis';
    }
    public function index() {
        $this->_showGroups();
    }

    protected function _showGroups(){
        //店铺列表
        $shop_id = $_GET['shop_id'];
        $shopObj = &app::get('ecorder')->model('shop');
        $sql = "select name,shop_id from sdb_ecorder_shop where (shop_type='taobao' and subbiztype='fx') or shop_type='shopex_b2b' ";
        $rs = $shopObj->db->select($sql);
        foreach($rs as $v){
            if(!$shop_id) $shop_id = $v['shop_id'];
        }
        $this->pagedata['shops'] = $rs;//var_dump($rs);
        $this->pagedata['shop_id'] = $shop_id;
        $filter['parent_id'] = 0;
        $filter['shop_id'] = $shop_id;
        $groups = $this->app->model('fx_member_group')->getList('*', $filter, 0, -1, 'group_id ASC');
        if($groups) {
            foreach($groups as $k=>$v) {
                $groups[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
        }
        $this->pagedata['groups'] = $groups;
        $this->page('admin/fx/member/group_list.html');
    }

    public function refresh_group_member($group_id){
        $oGroup = &$this->app->model('fx_member_group');
        $data = $oGroup->dump($group_id);
        $data['filter'] = unserialize($data['filter']);
        //获得客户数量
        $middlewareContent = kernel::single('taocrm_middleware_connect');
        $tableName = $this->interfaceTableName;
        $data['packetName'] = $this->interfacePacketName;
        $data['methodName'] = $this->interfaceMethodName;
        $countMembers = $middlewareContent->count($tableName, $data);
        $filter = array('group_id' => $group_id,);
        $ret = $oGroup->update(array(
            'members' => $countMembers,
            'update_time' => time()
        ),$filter);
    }

    public function refresh(){
        $group_id = intval($_GET['group_id']);
        $shop_id = trim($_GET['shop_id']);

        if($group_id>0) {
            $this->begin('index.php?app=taocrm&ctl=admin_fx_member_group&act=index&shop_id='.$shop_id);
            $this->refresh_group_member($group_id);
            $this->end(true,'更新成功');
        }else {
            $this->begin('index.php?app=taocrm&ctl=admin_fx_member_group&act=index&shop_id='.$shop_id);
            $oGroup = &$this->app->model('fx_member_group');
            $data = $oGroup->getList('group_id', array('shop_id'=>$shop_id));
            foreach($data as $v){
                $this->refresh_group_member($v['group_id']);
            }
            $this->end(true,'更新成功');
        }
    }

    function _views(){
        $memberObj = $this->app->model('fx_member_group');
        $base_filter = array();

        $shopObj = &app::get(ORDER_APP)->model('shop');
        
        $sql = "select name,shop_id from sdb_ecorder_shop where (shop_type='taobao' and subbiztype='fx') or shop_type='shopex_b2b' ";
        $shopList = $shopObj->db->select($sql);
         
        foreach($shopList as $shop){
            $sub_menu[] = array(
                'label'=>$shop['name'],
                'filter'=>array('shop_id'=>$shop['shop_id']),
                'optional'=>false
            );
        }
        $i=0;
        foreach($sub_menu as $k=>$v){
            if (!IS_NULL($v['filter'])){
                $v['filter'] = array_merge($v['filter'], $base_filter);
            }
            $sub_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
            $sub_menu[$k]['addon'] = $memberObj->count($v['filter']);
            $sub_menu[$k]['href'] = 'index.php?app=taocrm&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&view='.$i++;
        }

        return $sub_menu;
    }

    public function edit_group($group_id,$act){
        $shop_id = $_GET['shop_id'];
        $shopObj = &app::get(ORDER_APP)->model('shop');
        $brandObj = app::get('ecgoods')->model('brand');
        $group_id = isset ($group_id) && intval($group_id) > 0 ? intval($group_id) : 0;

        //店铺列表
        $sql = "select name,shop_id from sdb_ecorder_shop where (shop_type='taobao' and subbiztype='fx') or shop_type='shopex_b2b' ";
        $rs = $shopObj->db->select($sql);
        if(!$rs) return false;
        foreach($rs as $v){
            $shops[$v['shop_id']] = $v['name'];
        }
        $this->pagedata['shops'] = $shops;
		//商品品牌
        $sql = "select brand_id,brand_name from sdb_ecgoods_brand";
        $brands = $brandObj->db->select($sql);
        
		$this->pagedata['brands'] = $brands;
        //地区列表
        $rs = app::get('ectools')->model('regions')->getList('region_id,local_name,group_name',array('region_grade'=>1));
        if($rs){
            foreach($rs as $v){
                if(!$v['group_name']) $v['group_name'] = '其它';
                $regions[$v['group_name']][$v['region_id']] = $v['local_name'];
            }
        }
        $this->pagedata['regions'] = $regions;

        if($group_id){
            $Obj = &$this->app->model('fx_member_group');
            $data = $Obj->dump($group_id);
            $data['filter'] = unserialize($data['filter']);
            $data['filter']['goods_id'] = array_unique($data['filter']['goods_id']);
            $data['filter']['goods_id'] = implode(',',$data['filter']['goods_id']);
            $data['filter']['regions_id'] = implode(',',$data['filter']['regions_id']);
            $data['filter']['pay_status'] = $data['filter']['pay_status'];
            $data['parent_name'] = '无';
            if($data['parent_id']>0) {
                $rs = $Obj->dump($data['parent_id'],'group_name');
                $data['parent_name'] = $rs['group_name'];
            }
        } else {
            $shop_id = $_GET['shop_id'];
            $shop = $shopObj->count(array(
                'shop_id' => $shop_id
            ));
            if(!$shop){
                echo '传参错误, 商店不存在!';
                exit();
            }

            $data['shop_id'] = $shop_id;
            $data['parent_name'] = '无';
        }

        if(!isset($data['filter']['chk_goods_id']))
        $data['filter']['chk_goods_id'] = 1;

        //添加子分组
        if($act=='add_child') {
            //修正创建子分组时，代父组的描述信息$data['group_content']
            unset($data['group_id'],$data['group_name'],$data['group_content']);
            $data['parent_id'] = $group_id;
            $data['actmemeber'] = 'yes';
            $rs = &$this->app->model('fx_member_group')->dump($group_id,'group_name');
            $data['parent_name'] = $rs['group_name'];
        }

        $select_sign = array(
            'nequal' => '等于',
            'sthan' => '小于等于',
            'bthan' => '大于等于',
            'between' => '介于'
        );
        $select_sign_time = array(
            'than' => '晚于',
            'lthan' => '早于',
            //'nequal' => '等于',
            'between' => '介于'
        );
       $select_sign_pay = array(
            '0' => '未支付',
            '1' => '已支付',          
            '5' => '退款',          
        );

           
        $this->pagedata['select_sign'] = $select_sign;
        $this->pagedata['select_sign_time'] = $select_sign_time;
        $this->pagedata['select_sign_pay'] = $select_sign_pay;

        $this->pagedata['data'] = $data;
     
        $this->display('admin/fx/member/group_edit.html');
    }

    function del_group($group_id=0,$parent_id=0){
        $this->begin('index.php?app=taocrm&ctl=admin_fx_member_group&act=index');
        $oMemberGroup = &$this->app->model('fx_member_group');
        $filter = array('parent_id'=>$group_id);
        $rs = $oMemberGroup->dump($filter);
        if($rs) {
            $this->end(false, '请先删除子分组!');
        }else{
            $filter = array('group_id'=>$group_id);
            $oMemberGroup->delete($filter);
            $this->countChilds($parent_id);

            $this->end(true, '删除成功!');
        }
    }

    public function save_group(){

        $oGroup= &$this->app->model('fx_member_group');
        $data = $_POST;
        $group_id = isset ($data['group_id']) && intval($data['group_id']) > 0 ? intval($data['group_id']) : 0;
        $this->begin('index.php?app=taocrm&ctl=admin_fx_member_group&act=index&shop_id='.$data['shop_id']);
        if(!$data['group_name'] || $data['group_name']==''){
            $this->end(false,'分组名称不能为空！');
        }
        $op_user = kernel::single('desktop_user')->get_name();

        if(isset($data['filter']['goods_id']) && $data['filter']['goods_id'])
        $data['filter']['goods_id'] = array_unique($data['filter']['goods_id']);

        if(isset($data['filter']['chk_goods_id']) && $data['filter']['chk_goods_id']==2){        	
            if(isset($data['filter']['good_name']) && $data['filter']['good_name']){
                $good_name_sign = $data['filter']['good_name_sign'];
                $good_name = $data['filter']['good_name'];
                $good_name2 = $data['filter']['good_name2'];
                
                if($good_name_sign != 'or') $good_name_sign='and';
            
                $data['filter']['goods_id'] = array();
                
                $sql = "select goods_id from sdb_ecorder_fx_order_items where shop_id='".$data['shop_id']."' and (name like '%$good_name%' ";
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
		
        /**
         * 获得客户数量
         */
        $middlewareConnect = kernel::single('taocrm_middleware_connect');
        $tableName = $this->interfaceTableName;
        $data['packetName'] = $this->interfacePacketName;
        $data['methodName'] = $this->interfaceMethodName;
        $countMembers = $middlewareConnect->count($tableName, $data);
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
                'shop_id' => $data['shop_id'],
                'parent_id' => intval($data['parent_id']),
                'filter' => serialize($this->serialize_condition($data)),
                'create_time' => $time,
                'update_time' => $time,
            );
            $group_id = $oGroup->insert($arr_data);
        }

        if($data['parent_id']>0) {
            $this->countChilds($data['parent_id']);
        }

        if($group_id){
            //删除与此组相关联的group_id
            /*
            $data['group_id'] = $group_id;
            $memberDataObj = &$this->app->model('member_group_data');
            $memberDataObj->delete_data($group_id);
            $memberGroupObj = &$this->app->model('member_group');
            $memberGroupObj->sync($this->serialize_condition($data));
            */
            $this->end(true,app::get('taocrm')->_('操作成功'));
        }else{
            $this->end(false,app::get('taocrm')->_('操作失败'));
        }
    }

    public function getShopGroup($shop_id){
        $memGroupObj = &$this->app->model('member_group');
        $groups = $memGroupObj->getList('group_id,group_title',array('shop_id'=>$shop_id));
        if($groups){
            $this->pagedata['shop_id'] = $shop_id;
            $this->pagedata['groups'] = $groups;
            echo $this->fetch('admin/member/shop_group.html');
        }else{
            echo '<span class="red">此店铺没有有效的客户分组</span>';
        }
    }

    //查看相关客户
    public function view(){
        $group_id = $_GET['p'][0];
        if(!$group_id){
            exit();
        }

        $group = $this->app->model('fx_member_group')->dump($group_id);
        $shop_id = $group['shop_id'];

        //获取member_id
        $oGroupData= &$this->app->model('member_group_data');
        $data = $oGroupData->getList('member_id',array(
            'group_id' => $group_id,
        ));
        foreach($data as $v){
            $tmp[] = $v['member_id'];
        }

        if(!$tmp){
            $tmp[] = 0;
        }

        $base_filter = array(
            'member_id' => $tmp
        );

        $this->finder('taocrm_mdl_members',array(
            'actions' => array(
        array(
	            	'label'=>'创建活动',
	                'submit'=>'index.php?app=taocrm&ctl=admin_fx_member&act=createActive',
	                'target'=>'dialog::{width:500,height:270}'
	                ),
	                array(
	                'label' => '发送短信', 
	                'submit'=>'index.php?app=taocrm&ctl=admin_fx_member&act=buildCustomerSmsByMember&shop_id=' . $shop_id . '&group_id=' . $group_id,
	                'target'=>'dialog::{width:500,height:270}'                           	 
	                ),
	                ),
            'base_filter'=>$base_filter,
            'title'=>'客户列表',
            'use_buildin_import'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>false,
            'use_view_tab' => false
	                ));
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
            }else if($k == 'pay_status'){
            	$exist = true;
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

    public function createActive(){
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);
        if($newFilter['isSelectedAll'] || count($newFilter['group_id'])>=2){
            echo '<span class="red">只允许对单个分组创建活动！</span>';
            exit;
        }

        $memberObj = $this->app->model('members');
        $groupObj = $this->app->model('fx_member_group');
        $groupDataObj = &$this->app->model('member_group_data');
        $shopObj = &app::get(ORDER_APP)->model('shop');

        if($newFilter['group_id'][0] && $newFilter['group_id'][0]>0){
            $memCount = $groupDataObj->count(array('group_id'=>$newFilter['group_id'][0]));
            if(!$memCount || $memCount<=0){
                echo '<span class="red">此分组没有客户，请选择其它分组！</span>';
                exit;
            }

            $group = $groupObj->dump($newFilter['group_id'][0]);
            $this->pagedata['group'] = $group;
            $this->pagedata['shop'] = $shopObj->dump($group['shop_id']);
        }else{
            echo '<span class="red">请选择客户分组！</span>';
            exit;
        }

        $groupObj = &$this->app->model('message_themes_group');
        $this->pagedata['groupList'] = $groupObj->getList('group_id,group_title');

        $this->display('admin/member/group/create_active.html');
    }

    public function buildCustomerSmsByGroup() {
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        $memberGroupObj = $this->app->model('member_group');
        $shopObj = &app::get(ORDER_APP)->model('shop');

        $filter = array();
        $shop_id = trim($_GET['shop_id']);
        $filter['shop_id'] = $shop_id;
        if ($newFilter['group_id'][0] && $newFilter['group_id'][0] > 0) {
            $filter['group_id'] = $newFilter['group_id'];
        }
        elseif ($newFilter['isSelectedAll'] && $newFilter['isSelectedAll'] == '_ALL_') {
            $conditions = array('shop_id' => $filter['shop_id']);
            $groupList = $memberGroupObj->getList('group_id', $conditions);
            $groupIds = array();
            foreach ($groupList as $valule) {
                $groupIds[] = $valule['group_id'];
            }
            $filter['group_id'] = $groupIds;
        }

        $this->pagedata['shop'] = $shopObj->dump($shop_id);
        $this->pagedata['filter'] = htmlspecialchars(serialize($filter));

        $groupList = $this->app->model('message_themes_group')->getList('*');
        $this->pagedata['groupList'] = $groupList;
        $this->display('admin/member/group/buildCustomerSmsByGroup.html');
    }

    public function sendCustomerSms() {
        $data = $_POST;
        $shop = app::get('ecorder')->model('shop')->dump(array('shop_id' => $data['shop_id']));
        $memberObj = $this->app->model('members');
        $memberGroupDataObj = $this->app->model('member_group_data');
        $filter = unserialize($data['filter']);

        $groupIds = $filter['group_id'];

        if ($filter['group_id']) {
            $memberGroupData = $memberGroupDataObj->getList('member_id', array('group_id|in' => $filter['group_id']));
            $memberIds = array();
            foreach ($memberGroupData as $value) {
                $memberIds[] = $value['member_id'];
            }
            $members = $memberObj->getList('member_id, uname, shop_id, mobile', array('member_id|in' => $memberIds));
        }
        else {
            $members = array();
        }
        $members = $this->_array_unique_fb($members);

        $this->begin();
        $queue = app::get('base')->model('queue');
        $memberGroups = array_chunk($members, 30);
        foreach ($memberGroups as $memberGroup) {
            $msgs = array();
            foreach ($memberGroup as $member) {
                if (!$member['mobile']) {
                    continue;
                }
                $message = $this->_buildMessage($shop, $member, $data['smsContent']);
                $msgs[] = array(
					'phones' => array($member['mobile']),
					'content' => $message
                );
            }
            	
            $queueData = array (
				'queue_title' => '自定义短信',
	            'start_time' => time(),
	            'params' => array(
	            	'msgs' => $msgs
            ),
	            'worker'=>'taocrm_queue.sendSms',
            );

            if (!$queue->insert($queueData)) {
                $this->end(false, '操作失败!');
            }
        }
        $logData = array(
			'type' => '1',
			'shop_id' => $shop['shop_id'],
			'log' => '客户分组处手动发送短信' . serialize($filter['group_id']),
			'optime' => time(),
        );
        $this->app->model('sms_log')->insert($logData);
        $this->end(true, '操作成功!');
    }

    function countChilds($group_id){
        $group_id = intval($group_id);
        $oMemberGroup = &$this->app->model('fx_member_group');
        $rs = $oMemberGroup->count(array('parent_id'=>$group_id));
        $oMemberGroup->update(array('childs'=>$rs),array('group_id'=>$group_id));
    }

    function getChildGroup() {
        $parent_id = intval($_POST['parent_id']);
        $oMemberGroup = &$this->app->model('fx_member_group');
        $rs = $oMemberGroup->getList('*',array('parent_id'=>$parent_id));
        if($rs) {
            foreach($rs as $k=>$v) {
                $rs[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
        }
        echo(json_encode($rs));
    }

    /**
     *
     * 把短信模板转变为具体的短信内容
     * @order array 一条订单
     * @smsTemplate string 一个短信模板
     */
    private function _buildMessage($shop, $member, $smsTemplate) {
        $message = str_replace(array('<{$uname}>', '<{$shop}>'), array($member['uname'], $shop['name']), $smsTemplate);
        return $message;
    }

    protected function _array_unique_fb($array2D){
        foreach ($array2D as $v){
            $temp[] = serialize($v);
        }

        $temp = array_unique($temp);
        $uniqueArr = array();
        foreach ($temp as $k => $v) {
            $uniqueArr[$k] = unserialize($v);
        }
        return $uniqueArr;
    }

    public function managerTag(){
        $oTag= &$this->app->model('member_tag');
        $this->pagedata['taglist'] = $oTag->getTagList();
        $this->pagedata['group_id'] = $_GET['group_id'];
        $this->display('admin/member/group/tag.html');
    }
}