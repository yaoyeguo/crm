<?php

class market_ctl_admin_callcenter_callin extends desktop_controller{

    var $support_cti = array('bird','ronghe');

    public function index()
    {
        //保存caselog
        if($_POST['act']=='save_caselog'){
            $caselog_id = $this->save_caselog($_POST);
        }    
    
        $detail = trim($_GET['detail']);
        $mobile = trim($_GET['mobile']);
        $select_index = intval($_GET['select_index']);
        $db = kernel::database();
        
        //根据软电话类型获取呼入号码
        $call_script = '';//呼出脚本代码
        base_kvstore::instance('market')->fetch('cti_type', $cti_type);
        if(in_array($cti_type, $this->support_cti)){
            if( ! $mobile) kernel::single('market_cti_'.$cti_type)->inbound($mobile);
            $call_script = kernel::single('market_cti_'.$cti_type)->outbound();
        }
        
        //if(!$mobile) die('<h3>没有接入电话</h3>');

        //查询客户信息
        $sql = "select * from sdb_taocrm_members where (mobile like '{$mobile}%' or tel like '{$mobile}%') and parent_member_id=0 limit 6 ";
        $rs_members = $db->select($sql);
        if(!$rs_members[0]){
            $this->pagedata['mobile'] = $mobile;
            $this->redirect('index.php?app=taocrm&ctl=admin_all_member&act=add_member&source=callcenter&mobile='.$mobile);
            exit;
        }

        $customer = $rs_members[$select_index]['uname'];
        $this->member_id = $rs_members[$select_index]['member_id'];

        if(!$detail) $detail = 'caselog';
        if(method_exists($this, 'detail_'.$detail)){
            $method = 'detail_'.$detail;
            $this->$method();
        }
        
        base_kvstore::instance('market')->fetch('last_caselog',$last_caselog);
        if($last_caselog){
            $last_caselog = json_decode($last_caselog, true);
            $this->pagedata['rs'] = array(
                'source' => $last_caselog['source'],
                'media' => $last_caselog['media'],
                'category' => $last_caselog['category'],
            );
        }

        $rs_shop = $this->get_shops();
        $category = $this->get_category();
        $this->detail_caselog();//当前客户的联系记录列表

        $this->pagedata['customer'] = $customer;
        $this->pagedata['member_id'] || $this->pagedata['member_id'] = $this->member_id;
        $this->pagedata['detail'] = $detail;
        $this->pagedata['page_url'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&mobile='.$mobile;

        $this->pagedata['select_index'] = $select_index;
        $this->pagedata['rs_members'] = $rs_members;
        $this->pagedata['mobile'] = $mobile;
        $this->pagedata['rs_shop'] = $rs_shop;
        $this->pagedata['category'] = $category;
        $this->pagedata['page_type'] = 'ib';
        $this->pagedata['call_script'] = $call_script;
        //$this->page('admin/callcenter/callin.html');
        $this->page('admin/callcenter/ob.html');
    }

    public function save_caselog($data)
    {
        $app = app::get('taocrm');
        $data['modified_time'] = time();
        if($data['alarm_time']){
            $data['alarm_time'] = strtotime($data['alarm_time'].' '.$data['_DTIME_']['H']['alarm_time'].':'.$data['_DTIME_']['M']['alarm_time']);
        }
        $data['agent'] = kernel::single('desktop_user')->get_name();
        $id = intval($data['id']);
        $member_id = intval($data['member_id']);
        if($id==0){
            $data['create_time'] = time();
            $app->model('member_caselog')->insert($data);
        }else{
            $app->model('member_caselog')->update($data,array('id'=>$id));
        }

        $last_caselog = json_encode(array(
            'customer'=>$data['customer'],
            'source'=>$data['source'],
            'category'=>$data['category'],
            'media'=>$data['media'],
            'status'=>$data['status'],
        ));
        $last_contact_time = time();
        $sql = "update sdb_taocrm_members set last_caselog='%s',last_contact_time='%s' ";
        if($id==0){
            $sql .= ',contact_times=contact_times+1 ';
        }
        $sql .= " where member_id={$member_id} ";
        $sql = sprintf($sql, $last_caselog, $last_contact_time);
        $app->model('member_caselog')->db->exec($sql);

        //将最后一次的选择保存到kv
        base_kvstore::instance('market')->store('last_caselog',$last_caselog);

        return $data['id'];
    }

    public function callplan()
    {
        $desktop_user = kernel::single('desktop_user');
        $db = kernel::database();
        $user_id = $desktop_user->get_id();
        $assign_user = $desktop_user->get_name();

        $from = trim($_GET['from']);
        $callplan_id = intval($_GET['callplan_id']);
        $member_id = intval($_GET['member_id']);

        //保存caselog
        if($_POST['act']=='save_caselog'){
            $callplan_id = intval($_POST['callplan_id']);
            $member_id = intval($_POST['member_id']);
            $caselog_id = $this->save_caselog($_POST);

            if($callplan_id>0){
            //更新联系过的人数
            $call_result = $_POST['status'];
            $update_time = time();

                if($_POST['alarm_time']){
                    $alarm_time = strtotime($_POST['alarm_time'].' '.$_POST['_DTIME_']['H']['alarm_time'].':'.$_POST['_DTIME_']['M']['alarm_time']);
                    $alarm_time = ",alarm_time=$alarm_time ";
                }else{
                    $alarm_time = ",alarm_time=NULL ";
                }

            //更新已经完成的人数
            if($_POST['status']==13){//这里存在隐患，可能13不是完成状态
                $is_finish = 1;
            }else{
                $is_finish = 0;
            }
                $sql = "update sdb_market_callplan_members set is_finish={$is_finish},call_result={$call_result},call_times=call_times+1,caselog_id={$caselog_id},update_time={$update_time}{$alarm_time} where member_id={$member_id} and callplan_id={$callplan_id} ";
            $db->exec($sql);

            $sql = "update sdb_market_callplan as a,(select count(*) as total from sdb_market_callplan_members where callplan_id={$callplan_id} and call_times>0) as b set a.called_num=b.total where a.callplan_id={$callplan_id} ";
            $db->exec($sql);

            $sql = "update sdb_market_callplan as a,(select count(*) as total from sdb_market_callplan_members where callplan_id={$callplan_id} and call_times>0 and is_finish=1) as b set a.finish_num=b.total where a.callplan_id={$callplan_id} ";
            $db->exec($sql);
        }
        }

        if($member_id==0 or intval($_POST['goto_next']) == 1){
            $sql = "select a.id,a.member_id,b.mobile from sdb_market_callplan_members as a left join sdb_taocrm_members as b on a.member_id=b.member_id where a.callplan_id={$callplan_id} and a.assign_user_id=0 ";
            $rs = $db->selectRow($sql);
            if(!$rs){
                die('呼叫计划已经全部完成。');
            }else{
                $id = $rs['id'];
                $member_id = $rs['member_id'];
                $mobile = trim($rs['mobile']);

                $sql = "update sdb_market_callplan_members set assign_user_id={$user_id},assign_user='{$assign_user}' where id={$id} and assign_user_id=0 ";
                $db->exec($sql);

                $sql = "update sdb_market_callplan set assign_num=assign_num+1 where callplan_id={$callplan_id} ";
                $db->exec($sql);
            }
        }

        if($member_id > 0){
            $sql = "select a.id,a.member_id,b.mobile from sdb_market_callplan_members as a left join sdb_taocrm_members as b on a.member_id=b.member_id where a.member_id={$member_id} ";
            $rs = $db->selectRow($sql);

            $id = $rs['id'];
            $member_id = $rs['member_id'];
            $mobile = trim($rs['mobile']);
        }

        $detail = trim($_GET['detail']);
        $select_index = intval($_GET['select_index']);

        //查询客户信息
        $sql = "select * from sdb_taocrm_members where member_id='{$member_id}' ";
        $rs_members = $db->select($sql);

        //处理扩展属性的生日
        $ext_info = kernel::single('taocrm_service_member')->get_member_ext($member_id);
        if($ext_info){
            if($ext_info['b_year']){
                $rs_members[$select_index]['birthday'] = 
                    $ext_info['b_year'].'-'.$ext_info['b_month'].'-'.$ext_info['b_day'];
            }
        }

        $customer = $rs_members[$select_index]['uname'];
        $this->member_id = $rs_members[$select_index]['member_id'];

        if(!$detail) $detail = 'caselog';
        if(method_exists($this, 'detail_'.$detail)){
            $method = 'detail_'.$detail;
            $this->$method();
        }

        $rs_shop = $this->get_shops();
        $category = $this->get_category();
        $this->detail_caselog();//当前客户的联系记录列表
        
        base_kvstore::instance('market')->fetch('last_caselog',$last_caselog);
        if($last_caselog){
            $last_caselog = json_decode($last_caselog, true);
            $this->pagedata['rs'] = array(
                'source' => $last_caselog['source'],
                'media' => $last_caselog['media'],
                'category' => $last_caselog['category'],
            );
        }

        //根据软电话类型
        $call_script = '';//呼出脚本代码
        base_kvstore::instance('market')->fetch('cti_type', $cti_type);
        if(in_array($cti_type, $this->support_cti)){
            $call_script = kernel::single('market_cti_'.$cti_type)->outbound();
        }

        $this->pagedata['customer'] = $customer;
        $this->pagedata['member_id'] = $this->member_id;
        $this->pagedata['detail'] = $detail;
        $this->pagedata['page_url'] = 'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&callplan_id='.$callplan_id.'&member_id='.$member_id;

        $this->pagedata['callplan_id'] = $callplan_id;
        $this->pagedata['from'] = $from;
        $this->pagedata['select_index'] = $select_index;
        $this->pagedata['rs_members'] = $rs_members;
        $this->pagedata['mobile'] = $mobile;
        $this->pagedata['rs_shop'] = $rs_shop;
        $this->pagedata['category'] = $category;
        $this->pagedata['page_type'] = 'ob';
        $this->pagedata['call_script'] = $call_script;
        //$this->display('admin/callcenter/callin.html');
        $this->display('admin/callcenter/ob.html');
    }

    public function detail_orders()
    {
        $orderObj = app::get(ORDER_APP)->model('orders');
        $order_cols = 'order_id,order_bn,status,pay_status,ship_status,
        total_amount,createtime,shop_id,payed';
        $filter['member_id'] = $this->member_id;
        $orders = $orderObj->getList($order_cols, $filter);
        foreach($orders as $key=>$order){
            $orders[$key]['shop_name'] = $shops[$orders[$key]['shop_id']]['name'];
            $orders[$key]['status'] = $orderObj->trasform_status('status',$orders[$key]['status']);
            $orders[$key]['pay_status'] = $orderObj->trasform_status('pay_status',$orders[$key]['pay_status'] );
            $orders[$key]['ship_status'] = $orderObj->trasform_status('ship_status', $orders[$key]['ship_status']);
        }
        $this->pagedata['orders'] = $orders;
    }

    public function detail_analysis()
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
            'is_vip' => '否',
        );
        
        $db = kernel::database();
        $order_ids = array();
        $buy_month = array();
        $sql = "select order_id,total_amount,pay_status,status,createtime,payed,item_num,skus from sdb_ecorder_orders where member_id=".$this->member_id." ";
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
            //echo($sql);
            
            $analysis['buy_freq'] = round(($analysis['last_buy_time'] - $analysis['first_buy_time'])/($analysis['total_orders']*86400), 2);
            $analysis['total_per_amount'] = round($analysis['total_amount']/$analysis['total_orders'], 2);
            $analysis['finish_per_amount'] = round($analysis['finish_total_amount']/$analysis['finish_orders'], 2);
            $analysis['unpay_per_amount'] = round($analysis['unpay_amount']/$analysis['unpay_orders'], 2);
            $analysis['avg_buy_skus'] = round($analysis['buy_skus']/$analysis['total_orders'], 2);
            $analysis['avg_buy_products'] = round($analysis['buy_products']/$analysis['total_orders'], 2);
            $analysis['buy_month'] = count($buy_month);
        $analysis['first_buy_time'] = date('Y-m-d H:i:s',$analysis['first_buy_time']);
        $analysis['last_buy_time'] = date('Y-m-d H:i:s',$analysis['last_buy_time']);
        }

        if($analysis['lv_id']){
            $sql = 'select name from sdb_ecorder_shop_lv where lv_id='.$analysis['lv_id'].' ';
            $rs = $db->selectRow($sql);
        $analysis['lv_id'] = $rs['name'];
        }
        
        if(!$analysis['first_buy_time']) $analysis['first_buy_time'] = '-';
        if(!$analysis['last_buy_time']) $analysis['last_buy_time'] = '-';
        
        $this->pagedata['analysis'] = $analysis;
    }

    public function detail_active()
    {
        $app = app::get('taocrm');
        $marketApp = app::get('market');
        $orderObj = app::get('ecorder')->model('orders');

        //分页
        $activeMemberModel = $marketApp->model('active_member');
        $shopId = $this->shop_id;
        $filter = array('issend' => 1, 'member_id' => $this->member_id, 'shop_id' => $shopId);
        $memberList = $activeMemberModel->getList('*', $filter);
        $count = $activeMemberModel->count($filter);
        if ($memberList) {
            $activeModel = $marketApp->model('active');
            $ecorderApp = app::get('ecorder');
            $shopModel = $ecorderApp->model('shop');
            $shopInfo = $shopModel->getList('shop_id,name');
            $shopList = array();
            foreach ($shopInfo as $v) {
                $shopList[$v['shop_id']] = $v['name'];
            }
            $activenamelist = array();
            $i = 0;
            foreach ($memberList as $v) {
                $activenamelist[$i]['shop_name'] = isset($shopList[$v['shop_id']]) ? $shopList[$v['shop_id']] : '未知店铺';
                $activeInfo = $activeModel->dump(array('active_id' => $v['active_id']));
                if ($activeInfo) {
                	$activenamelist[$i]['shop_id'] = $v['shop_id'];
                    $activenamelist[$i]['active_name'] = $activeInfo['active_name'];
                    $activenamelist[$i]['total_num'] = $activeInfo['total_num'];
                    $activenamelist[$i]['create_time'] = date("Y-m-d H:i:s", $activeInfo['create_time']);
                    $activenamelist[$i]['end_time'] = date("Y-m-d H:i:s", $activeInfo['end_time']);

                    //获取营销活动期内的订单号
		    		$filter_order = array('shop_id'=>$v['shop_id'],'member_id'=>$this->member_id,'createtime|between'=>array($activeInfo['create_time'],$activeInfo['end_time']));
		    		$orders = $orderObj->getList("order_bn,order_id",$filter_order);
		    		$activenamelist[$i]['orders'] = $orders;
                }
                else {
                    $activenamelist[$i]['active_name'] = '活动已经删除';
                    $activenamelist[$i]['total_num'] = '未知';
                    $activenamelist[$i]['create_time'] = '未知';
                    $activenamelist[$i]['end_time'] = '未知';
                }
                $i++;
            }
            $this->pagedata['activenamelist'] = $activenamelist;
        }
    }

    public function detail_merge()
    {
        $app = app::get('taocrm');
        $objMembers = $app->model('members');
        $member = $objMembers->getList('*',array('member_id'=>$this->member_id));
        $member = $member[0];
        $subMembers = $objMembers->getList('*',array('parent_member_id'=>$this->member_id));
        $merge_members = array();
        $merge_members[] = $member;
        if($subMembers){
            foreach($subMembers as $row){
                $merge_members[] = $row;
            }
        }
        $this->pagedata['merge_members'] = $merge_members;
    }

    public function detail_caselog()
    {
        //呼叫中心字典
        $rs_category = app::get('taocrm')->model('member_caselog_category')->getList('category_id,category_name');
        foreach($rs_category as $v){
            $categorys[$v['category_id']] = $v['category_name'];
        }

        //呼叫记录
        $member_caselog = app::get('taocrm')->model('member_caselog');
        $rs_caselog = $member_caselog->getList('*',array('member_id'=>$this->member_id),0,-1,'id desc');
        foreach($rs_caselog as &$v){
            $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            if($v['alarm_time']>0)
                $v['alarm_time'] = date('Y-m-d H:i', $v['alarm_time']);
            $v['source'] = $categorys[$v['source']];
            $v['media'] = $categorys[$v['media']];
            $v['category'] = $categorys[$v['category']];
            $v['status'] = $categorys[$v['status']];
            $v['content'] = mb_substr($v['content'],0,12,'utf-8');
        }
        $this->pagedata['rs_caselog'] = $rs_caselog;
    }

    public function ajax_save_member()
    {
        $members = app::get('taocrm')->model('members');

        $member_id = intval($_POST['member_id']);
        $mobile = trim($_POST['mobile']);
        $tel = trim($_POST['tel']);
        $email = trim($_POST['email']);
        $birthday = strtotime($_POST['birthday']);

        $weixin = trim($_POST['weixin']);
        $weibo = trim($_POST['weibo']);
        $wangwang = trim($_POST['wangwang']);
        $qq = trim($_POST['qq']);

        $save_arr = array(
            'member_id' => $member_id,
            'mobile' => $mobile,
            'tel' => $tel,
            'email' => $email,
            'birthday' => $birthday,

            'qq' => $qq,
            'weibo' => $weibo,
            'weixin' => $weixin,
            'wangwang' => $wangwang,
        );
        $res = $members->update($save_arr, array('member_id' => $member_id));
        
        //保存ext扩展属性
        if($_POST['birthday']){
            $ext_info = array();
            $ext_info['member_id'] = $member_id;
            list($ext_info['b_year'],$ext_info['b_month'],$ext_info['b_day']) = 
                explode('-', $_POST['birthday']);
            kernel::single('taocrm_service_member')->save_member_ext($ext_info);
        }
        die();
    }

    public function send_sms()
    {
        if($_POST){
            $sms = $_POST['sms'];
            $sms['content'] = $sms['content'].'【'.$sms['sign'].'】';

            //写入接待记录
            $caselog = array(
                'member_id' => intval($sms['member_id']),
                'title' => date('Y-m-d').'发送短信',
                'category' => $sms['category'],
                'content' => $sms['content'],
                'status' => 13,
                'source' => 11,
                'customer' => $sms['name'],
                'mobile' => $sms['mobile'],
                'media' => 5,
                'is_finish' => 1,
                'create_time' => time(),
                'modified_time' => time(),
                'agent' => kernel::single('desktop_user')->get_name(),
            );
            app::get('taocrm')->model('member_caselog')->insert($caselog);

            //检测是否绑定短信帐号
            base_kvstore::instance('market')->fetch('account', $account);
            $account = unserialize($account);
            if(!isset($account['entid'])) return false;

            //实时发送接口
            $content = array(
                array(
                    'phones'=>$sms['mobile'],
                    'content'=>$sms['content'],
                    'extend_no'=>$sms['extend_no']
                )
            );
            $content = json_encode($content);
            $smsAPI = kernel::single('market_service_smsinterface');
            $res = $smsAPI->send($content, 'notice');
            
            //全局短信日志
            $logs = array(
                'mobile' => $sms['mobile'],
                'content' => $sms['content'],
                'source_id' => $caselog['id'],
            );
            if($res['res'] != 'succ'){
                $logs['status'] = 'fail';
                $logs['remark'] = json_encode($res);
            }else{
                $logs['status'] = 'succ';
                $logs['remark'] = '';
            }
            $this->save_sms_log($logs);
            
            exit;
        }

        $mobile = $_GET['mobile'];
        $name = $_GET['name'];
        $member_id = intval($_GET['member_id']);

        //短信可选签名
        $oShop = app::get('ecorder')->model("shop");
        $sign_list = $oShop->get_sms_sign_list();

        $this->pagedata['sign_list'] = $sign_list;
        $this->pagedata['category'] = $this->get_category();
        $this->pagedata['member_id'] = $member_id;
        $this->pagedata['name'] = $name;
        $this->pagedata['mobile'] = $mobile;
        $this->display('admin/callcenter/send_sms.html');
    }

    public function get_category()
    {
        $rs = app::get('taocrm')->model('member_caselog_category')->getlist('*');
        foreach((array)$rs as $v){
            $res[$v['type']][$v['category_id']] = $v['category_name'];
        }
        return $res;
    }

    public function get_shops()
    {
        $rs = app::get('ecorder')->model('shop')->getlist('*');
        foreach((array)$rs as $v){
            if($v['name'] == '') continue;
            $res[$v['shop_id']] = $v['name'];
        }
        return $res;
    }
    
    var $detail_edit = '客户信息';
    public function detail_client_infor(){
        $app = app::get('taocrm');
        $render = $app->render();
        $memberObj = $app->model('members');
        $taocrm_service_member = kernel::single('taocrm_service_member');
        $channelType = $memberObj->getChannelTypeList();

        if($_POST){
            $data=array();
            $data['channel_type']=array_search($_POST['channel_type'], $channelType);
            $data['name']=$_POST['name'];
            $data['member_card']=$_POST['member_card'];
            $data['sex']=$_POST['gender'];
            $data['birthday']=strtotime($_POST['birthday']);
            $data['mobile']=$_POST['mobile'];
            $data['email']=$_POST['email'];
            $data['tel']=$_POST['tel'];
            $data['qq']=$_POST['qq'];
            $data['wangwang']=$_POST['wangwang'];
            $data['weibo']=$_POST['weibo'];
            $data['weixin']=$_POST['weixin'];

            $data['alipay_no']=$_POST['alipay_no'];
            $data['area']=$_POST['area'];
            $data['addr']=$_POST['addr'];
            $data['zip']=$_POST['zipcode'];

            $member = $memberObj->dump($this->member_id);
            
            $data['uname'] = $member['account']['uname'];
            $data['remark']=$_POST['remark'];
            $data['member_id'] = $_POST['member_id'];
            $taocrm_service_member->saveOverallMember($data,$_POST['prop_name']);
            
            //保存ext扩展属性
            if($_POST['birthday']){
                $ext_info = array();
                $ext_info['member_id'] = $this->member_id;
                list($ext_info['b_year'],$ext_info['b_month'],$ext_info['b_day']) = 
                    explode('-', $_POST['birthday']);
                $taocrm_service_member->save_member_ext($ext_info);
            }
        }
        
        $mem = $memberObj->dump(array('member_id'=>$this->member_id),'*');
        $mem['other_contact'] = json_decode($mem['other_contact'],true);
        $mem['channel_type'] = $channelType[$mem['channel_type']];
        
        //处理扩展属性的生日
        $ext_info = $taocrm_service_member->get_member_ext($this->member_id);
        if($ext_info){
            if($ext_info['b_year']){
                $mem['profile']['birthday'] = 
                    $ext_info['b_year'].'-'.$ext_info['b_month'].'-'.$ext_info['b_day'];
            }
        }
        if(!$mem['profile']['birthday']) $mem['profile']['birthday'] = '';
        $render->pagedata['mem'] = $mem;

        $overall_member_props = $memberObj->get_member_prop();
        if($overall_member_props){
            $prop_val = $memberObj->get_member_prop_val($this->member_id);
        }
        $overall_member_props = array_unique($overall_member_props);
        $render->pagedata['prop_val'] = $prop_val;
        $render->pagedata['prop_name'] = $overall_member_props['prop_name'];

        $redirect_uri = 'index.php?act=index&app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&id='.$id.'&view='.$_GET['view'];
        $render->pagedata['redirect_uri'] = base64_encode($redirect_uri);
        return $render->fetch('admin/member/all/edit.html');
    }

    var $detail_goods = '买过的商品';
    function detail_goods(){
        $data = $this->_getMemberOrders($this->member_id);
        $goods = $data['item'];
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['goods'] = $goods;
        return $render->fetch('admin/member/goods.html');
    }

    /**
     * 获得接口客户数据
     */
    protected function _getMemberOrders($memberId)
    {
        $member_ids = $this->_get_members($memberId);
        $filter = array('member_id' => $member_ids);
        $oAnalysis = app::get('ecorder')->model('orders');
        $result = $oAnalysis->getList('*',$filter);
        foreach($result as $order)
        {
            $order_ids[] = $order['order_id'];  
        }
        $goodsArr = $this->mergeGoods($order_ids);
        return $goodsArr;
    }
    /**
     * 合并商品
     */
    protected function mergeGoods($orderIdList)
    {
        $orders = $orderIdList;
        $goods = array();
        $goodsArr = array();
        if($orders) {
            $sql = "SELECT order_id, bn, `name`, nums, amount FROM `sdb_ecorder_order_items` WHERE order_id in (".implode(',',$orders).")";
            $goods = kernel::database()->select($sql);
        }
        $num = 0;
        if ($goods) {
            $formatGoods = array();
            foreach ($goods as $k => $v) {
                if (isset($formatGoods[$v['name']])) {
                    if ($formatGoods[$v['name']]['bn'] == '' && $v['bn'] != '') {
                        $formatGoods[$v['name']]['bn'] = $v['bn'];
                    }
                    $formatGoods[$v['name']]['nums'] += $v['nums'];
                    $formatGoods[$v['name']]['amount'] += $v['amount'];
                }
                else {
                    $formatGoods[$v['name']] = $v;
                }
            }
            $goodsArr['count'] = count($formatGoods);
            foreach ($formatGoods as $v) {
                $goodsArr['item'][] = $v;
            }
        }
        return $goodsArr;
    }

    protected function _get_members($member_id)
    {
        if(!$member_id) return null;
        $objMembers = app::get('taocrm')->model('members');
        $subMembers = $objMembers->getList('member_id',array('parent_member_id'=>$member_id));
        $memberIds = array();
        $memberIds[] = $member_id;
        if($subMembers){
            foreach($subMembers as $row){
                $memberIds[] = $row['member_id'];
            }   
        } 
        return $memberIds;
    }

    var $detail_contact = '联 系 人';
    function detail_contact(){
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_contacts');
        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));
        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;
    }

    var $detail_addr = '收货地址';
    function detail_addr(){
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_receivers');
        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));

        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;

        return $render->fetch('admin/member/addr.html');
    }
   

    var $detail_points = '积分日志';
    function detail_points(){
        $id = $this->member_id;
        $this->member_id = $this->_get_members($this->member_id);
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }
        
        $mdl_points_log = app::get('taocrm')->model('all_points_log');
        $schema = $mdl_points_log->get_schema();
        //$points_type_conf = $schema['columns']['points_type']['type'];
        
        $mdl_member_points = app::get('taocrm')->model('member_points');
        $points = $mdl_member_points->get_points(array('member_id'=>$this->member_id));
        foreach($points as $k=>$v){
            $points[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            //$points[$k]['points_type'] = $points_type_conf[$v['points_type']];
        }
        
        $logs = $mdl_points_log->db->select('select * from sdb_taocrm_all_points_log where member_id in ('.implode(',',$this->member_id).') order by id desc limit 10');
        foreach($logs as $k=>$v){
            //$logs[$k]['points_type'] = $points_type_conf[$v['points_type']];
            $logs[$k]['user_name'] = app::get('taocrm')->model('members')->dump(array('member_id'=>$v['member_id']),'uname');
            $logs[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            $logs[$k]['order_bn'] = app::get('ecorder')->model('orders')->dump($v['order_id'],'order_bn');
            $logs[$k]['refund_bn'] = app::get('ecorder')->model('refunds')->dump($v['refund_id'],'refund_bn');
            $logs[$k]['refund_bn'] = $logs[$k]['refund_bn']['refund_bn'];
        }
        $app = app::get('taocrm');
        $render = $app->render();
        $render->pagedata['points'] = $points;
        $render->pagedata['logs'] = $logs;
        $render->pagedata['member_id'] = $id;
    }


//	var $detail_service = '接待日志';
//    function detail_service(){
//        $id = $this->_get_members($this->member_id);
//        $app = app::get('taocrm');
//        $memObj = $app->model('members');
//        $memInfo = $memObj->dump(array('member_id'=>$id),'uname');
//        $uname = trim($memInfo['account']['uname']);
//        $chatObj = $app->model('wangwang_shop_chat_log');
//        //分页
//        $pagelimit = 3;
//        $page = max(1, intval($_GET['page']));
//        $offset = ($page - 1) * $pagelimit;
//        $shopId = $this->getShopId();
//
//        $filter = array('uname' => $uname, 'shop_id' => $shopId);
//        $memberList = $chatObj->getList('*', $filter, $offset, $pagelimit,'chat_date desc');
//        $count = $chatObj->count($filter);
//        $render = $app->render();
//
//        if ($memberList) {
//            $i = 0;
//            $servicenamelist = array();
//            foreach ($memberList as $v) {
//				$servicenamelist[$i]['mark'] = '';
//                $servicenamelist[$i]['nick'] = $v['seller_nick'];
//                $servicenamelist[$i]['date'] = date('Y-m-d',$v['chat_date']);
//               	$servicenamelist[$i]['type'] = '旺旺接待';
//                $i++;
//            }
//
//            $view = $_GET['view'];
//            $total_page = ceil($count / $pagelimit);
//            $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id='.$uid.'&finderview=detail_service&page=%d&view='.$view.'&shop_id='.$shopId ));
//            $render->pagedata['pager'] = $pager;
//            $render->pagedata['servicenamelist'] = $servicenamelist;
//        }
//    }

    /**
     *
     * 获得所有店铺ID
     */
    protected function getAllShopId()
    {
        $shopObj = $this->getShopObj();
        $shopList = $shopObj->getList('shop_id,name');
        return $shopList;
    }

    /**
     * 获得店铺ID
     */
    protected function getShopId()
    {
        if ($this->shop_id == '') {
            $shopList = $this->getAllShopId();
            $currentShopInfo = $shopList[intval($_GET['view'])];
            if ($currentShopInfo) {
                $this->shop_id = $currentShopInfo['shop_id'];
            }
            else {
                if (isset($_GET['shop_id'])) {
                    $this->shop_id = $_GET['shop_id'];
                }
                else {
                    $this->shop_id = $shopList[0]['shop_id'];
                }

            }

        }
        return $this->shop_id;
    }
    protected static $shopObj = '';
    
    /**
     * 获得店铺对象
     * Enter description here ...
     */
    protected function getShopObj()
    {
        if (self::$shopObj == '') {
            self::$shopObj = app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
    }
    
    //保存全局短信日志
    function save_sms_log($logs)
    {
        if(!$this->oLog)
            $this->oLog = app::get('taocrm')->model('sms_log');

        $log = array(
            'source'=>'taocrm_member_caselog',
            'source_id'=>$logs['source_id'],
            'batch_no'=>date('YmdHis'),
            'mobile'=>$logs['mobile'],
            'content'=>$logs['content'],
            'status'=>$logs['status'],
            'send_time'=>time(),
            'create_time'=>time(),
            'sms_size'=>ceil(mb_strlen($logs['content'],'utf-8')/67),
            'cyear'=>date('Y'),
            'cmonth'=>date('m'),
            'cday'=>date('d'),
            'op_user'=>kernel::single('desktop_user')->get_name(),
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'remark'=>$logs['remark'],
        );
        $this->oLog->insert($log);
    }
}
