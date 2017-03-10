<?php
class taocrm_finder_members {

    var $pagelimit = 20;


    protected static $shopObj = '';
    protected static $hardeWareConnect = null;

    var $column_edit = '操作';
   	var $column_edit_order = COLUMN_IN_HEAD;
   	var $column_edit_width = 90;
   	public function column_edit($row)
   	{
        $user = kernel::single('desktop_user');
        $user_id = $user->get_id();
        $is_super = $user->is_super();
        $users = app::get('desktop')->model('users');
        $sdf_users = $users->dump($user_id);

        $member_id = $row['member_id'];
        $act = '';
        $_GET['ctl']=='admin_member_caselog' &&  $act .= "<a target='dialog::{width:650,height:300,title:\"添加服务\"}' href='index.php?app=taocrm&ctl=admin_member_caselog&act=caselog_edit&member_id={$member_id}'>添加服务</a> | ";
        $act .= " <a target='dialog::{width:650,height:300,title:\"发短信\"}' href='index.php?app=market&ctl=admin_callcenter_callin&act=send_sms&member_id={$member_id}&name={$row['name']}&mobile={$row['mobile']}'>发短信</a>";
        if($is_super || $sdf_users['customer_delete']){
            $act .= ' | <a href="index.php?app=taocrm&ctl=admin_all_member&act=delete_member&member_id='.$member_id.'&tagInfo='.$row['tagInfo'].'"  target="dialog::{title:\''.app::get('taocrm')->_('是否删除？').'\', width:360, height:100}">'.app::get('taocrm')->_('删除').'</a>';
        }

        return $act;
   	}

    var $column_tag = '标签';
    var $column_tag_width = 50;
    var $column_tag_order = 2;
    function column_tag($row)
    {
        $tagInfo = $row['tagInfo'];
        if($tagInfo){
            $tagInfo = '<img border=0 title="'.$tagInfo.'" align="absmiddle" src="'.app::get('taocrm')->res_url.'/teg_ico.png" >';
        }
        return $tagInfo;
    }

    var $detail_basic = '统计信息';
    public function detail_basic($member_id)
    {
        $app = app::get('taocrm');
        $analysis = $app->model('members')->get_analysis($member_id);

        $render = $app->render();
        $render->pagedata['analysis'] = $analysis;
        return $render->fetch('admin/member/all/analysis.html');
    }

    var $detail_edit = '客户信息';
    public function detail_edit($member_id)
    {
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

            $member = $memberObj->dump($member_id);
            $data['uname'] = $member['account']['uname'];
            $data['remark']=$_POST['remark'];
            $data['member_id'] = $_POST['member_id'];
            $memberObj->chkMemberArea($member_id);

            //保存客户自定义属性
            $prop_name = $_POST['prop_name'];
            $memberObj->save_member_prop_val($prop_name, $member_id);

            //推荐验证开始
            $rec_mobile = !empty($_POST['commend_mobile']) ? trim($_POST['commend_mobile']) : false;
            $rec_code = !empty($_POST['commend_code']) ? trim($_POST['commend_code']) : false;

            /*$rec_mod = app::get('taocrm')->model('members_recommend');
            if($rec_mobile && $rec_code)
            {
                $comm_info = $rec_mod->dump(array('mobile'=>trim($rec_mobile)));
                if($comm_info && $comm_info['self_code'] == $rec_code)
                    $rec_code = $comm_info['self_code'];
            }elseif($rec_mobile && !$rec_code)
            {
                $comm_info = $rec_mod->dump(array('mobile'=>trim($rec_mobile)));
                if($comm_info)
                    $rec_code = $comm_info['self_code'];
            }
            $rec_code && $comm_info = $rec_mod->dump(array('self_code'=>trim($rec_code)));
            $self_info = $rec_mod->dump($member_id);
            if($self_info['self_code'] != $rec_code && $comm_info && $self_info)
            {
                $data['parent_code'] = $rec_code;
                $data['self_code'] = $self_info['self_code'];
            }*/
            $data['parent_code'] = $rec_code;
            $taocrm_service_member->saveOverallMember($data,$_POST['prop_name']);

            //保存ext扩展属性
            if($_POST['birthday']){
                $ext_info = array();
                $ext_info['member_id'] = $_POST['member_id'];
                list($ext_info['b_year'],$ext_info['b_month'],$ext_info['b_day']) =
                    explode('-', $_POST['birthday']);
                $taocrm_service_member->save_member_ext($ext_info);
            }
        }

        $mem = $memberObj->dump(array('member_id'=>$member_id),'*');
        $mem['other_contact'] = json_decode($mem['other_contact'],true);
        $mem['channel_type'] = $channelType[$mem['channel_type']];

        //处理扩展属性的生日
        $ext_info = $taocrm_service_member->get_member_ext($member_id);
        if($ext_info){
            if($ext_info['b_year']){
                $mem['profile']['birthday'] =
                    $ext_info['b_year'].'-'.$ext_info['b_month'].'-'.$ext_info['b_day'];
            }
        }
        if(!$mem['profile']['birthday']) $mem['profile']['birthday'] = '';

        //获取推荐code
        $rec_info = $taocrm_service_member->get_member_recommend($member_id);
        $render->pagedata['rec_info'] = $rec_info;
        $render->pagedata['mem'] = $mem;

        $overall_member_props = $memberObj->get_member_prop();
        if($overall_member_props){
            /*
            $oMemberProp = app::get('taocrm')->model('member_overall_property');
            $rs = $oMemberProp->getList('*',array('member_id'=>$member_id));
            if($rs){
                foreach($rs as $v){
                    $prop_val[$v['property']] = $v['value'];
                }
            }
            */
            $prop_val = $memberObj->get_member_prop_val($member_id);
        }

        //$overall_member_props = array_unique($overall_member_props);
        $render->pagedata['prop_val'] = $prop_val;
        $render->pagedata['prop_name'] = $overall_member_props['prop_name'];
        $render->pagedata['prop_type'] = $overall_member_props['prop_type'];

        $redirect_uri = 'index.php?act=index&app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&id='.$id.'&view='.$_GET['view'];
        $render->pagedata['redirect_uri'] = base64_encode($redirect_uri);
        return $render->fetch('admin/member/all/edit.html');
    }


    var $detail_goods = '买过的商品';
    function detail_goods($id)
    {
        if(!$id) return null;
        $app = app::get('taocrm');
        $render = $app->render();
        $goods = array();
        $orders = array();

        $this->member_id = $id;
        $sql = "select order_id from sdb_ecorder_orders where member_id=".$this->member_id." and pay_status='1' ";
        $rs = kernel::database()->select($sql);
        if($rs){
            foreach($rs as $v){
               $orders[] = $v['order_id'];
            }
        }

        if($orders){
            $sql = "select bn,name,nums,amount from sdb_ecorder_order_items where order_id in (".implode(',',$orders).") ";
            $rs = kernel::database()->select($sql);
            foreach($rs as $v){
                $k = $v['bn'] ? $v['bn'] : $v['name'];
                if(isset($goods[$k])){
                    $goods[$k]['nums'] += $v['nums'];
                    $goods[$k]['amount'] += $v['amount'];
                }else{
                    $goods[$k] = $v;
                }
            }
        }
        $render->pagedata['goods'] = $goods;
        return $render->fetch('admin/member/goods.html');
    }
    /**
     *
     * @desc 补全计划：更新客户的地区信息
     * @params mainland:辽宁/沈阳市/沈河区:1877
     */
    public function chkMemberArea($member_id,$shop_id){

        $oRegions = app::get('ectools')->model('regions');
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

    var $detail_order = '历史订单';
    function detail_order($member_id=null)
    {
        if(!$member_id) return null;

        //分页参数
        $page_order = intval($_GET['page_order']);
        if( ! $page_order) $page_order = 1;
        $page_size = 6;
        $offset = ($page_order - 1) * $page_size;

        $objMembers = app::get('taocrm')->model('members');
        $subMembers = $objMembers->getList('member_id',array('parent_member_id'=>$member_id));
        $memberIds = array();
        $memberIds[] = $member_id;
        if($subMembers){
            foreach($subMembers as $row){
                $memberIds[] = $row['member_id'];
            }
        }

        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orderObj = app::get(ORDER_APP)->model('orders');
        $shopObj = app::get(ORDER_APP)->model('shop');

        //分页总数
        $total = $orderObj->count(array('member_id'=>$memberIds));
        $total_page = ceil($total/$page_size);

        $order_cols = 'order_id,order_bn,status,pay_status,ship_status,total_amount,createtime,shop_id,payed';
        $orders = $orderObj->getList($order_cols,array('member_id' => $memberIds), 0, -1, 'createtime DESC');

        //订单报表,最多返回13个月数据
        $order_report = array();
        $order_max_amount = 0;

        $count = count($orders);
        foreach($orders as $key=>$order){
            $rk = date('Y-m', $order['createtime']);
            if($order['pay_status']=='1'){
                if(date('Y', $order['createtime']) != date('Y')){
                    $order_report[$rk]['bgcolor'] = '#F4F4F4';
                }
                $order_report[$rk]['amount'] = intval($order_report[$rk]['amount']) + $order['total_amount'];
                $order_report[$rk]['orders'] = intval($order_report[$rk]['orders']) + 1;
                $order_report[$rk]['avg_amount'] = round($order_report[$rk]['amount']/$order_report[$rk]['orders'], 2);
                $order_max_amount = max($order_max_amount, $order['total_amount']);
            }

            $shop_name= $shopObj->dump(array('shop_id'=>$order['shop_id']));
            $orders[$key]['shop_name']=$shop_name['name'];
            $orders[$key]['status'] = $orderObj->trasform_status('status',$orders[$key]['status']);
            $orders[$key]['pay_status'] = $orderObj->trasform_status('pay_status',$orders[$key]['pay_status'] );
            $orders[$key]['ship_status'] = $orderObj->trasform_status('ship_status', $orders[$key]['ship_status']);
        }
        if($order_report) $order_report = array_slice($order_report, 0, 13);

        //订单数据分页
        $orders = array_slice($orders, $offset, $page_size);

        $app = app::get('taocrm');
        $render = $app->render();

        //分页导航
        $pager = $render->ui()->pager ( array ('current' => $page_order, 'total' => $total_page, 'link' =>'index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act='.$_GET['act'].'&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview='.__FUNCTION__.'&page_order=%d&id='.$member_id));

        $render->pagedata['pager'] = $pager;
        $render->pagedata['orders'] = $orders;
        $render->pagedata['order_max_amount'] = $order_max_amount;
        $render->pagedata['order_report'] = ($order_report);
        return $render->fetch('admin/member/order.html');
    }


    var $detail_contact = '联 系 人';
    function detail_contact($id=null){
        $this->member_id = $id;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_contacts');
        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));
        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;
        return $render->fetch('admin/member/contact.html');
        //        if(!$id) return null;
        //        $this->getMemberInfo($id);
        //        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        //        $app = app::get('taocrm');
        //        $addrObj = $app->model('member_contacts');
        //        $addrs = $addrObj->getList('*',array('member_id' => $this->member_id));
        //        $render = $app->render();
        //        $render->pagedata['addrs'] = $addrs;
        //        return $render->fetch('admin/member/contact.html');
    }

    var $detail_addr = '收货地址';
    function detail_addr($id=null){
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('taocrm');
        $addrObj = $app->model('member_receivers');
        //$addrs = $addrObj->getList('*',array('member_id' => $id));
        $addrs = $addrObj->db->select('SELECT * FROM sdb_taocrm_member_receivers WHERE member_id='.$id);
        foreach($addrs as $key => $value){
            $area = explode(':',$value['area']);
            $addrs[$key]['area'] = str_replace('/','',$area[1]);
        }

        $render = $app->render();
        $render->pagedata['addrs'] = $addrs;

        return $render->fetch('admin/member/addr.html');
    }

    var $detail_active = '营销活动';
    function detail_active($id)
    {
        if(!$id) $id = $_get['id'];

        $id = $this->_get_members($id);
        $app = app::get('taocrm');
        $marketapp = app::get('market');
        $ordermodel = app::get('ecorder')->model('orders');

        $pagelimit = 3;//分页
        $page = max(1, intval($_get['page']));
        $offset = ($page - 1) * $pagelimit;
        $activemembermodel = $marketapp->model('active_member');
        $shopid = $this->getshopid();
        $filter = array(
            'issend' => 1,
            'member_id' => $id,
            'shop_id' => $shopid
        );
        $memberlist = $activemembermodel->getlist('*', $filter, $offset, $pagelimit, 'active_id desc');
        $count = $activemembermodel->count($filter);
        $render = $app->render();

        $filter = array(
            'member_id' => $id,
            'shop_id' => $shopid
        );
        $rs_orders = $ordermodel->getlist('order_id,order_bn,member_id,pay_time', $filter);
        if(!$rs_orders) $rs_orders=array();

        if($memberlist){
            $activemodel = $marketapp->model('active');
            $ecorderapp = app::get('ecorder');
            $shopmodel = $ecorderapp->model('shop');
            $shopinfo = $shopmodel->getlist('shop_id,name');
            $shoplist = array();
            foreach ($shopinfo as $v) {
                $shoplist[$v['shop_id']] = $v['name'];
            }
            $activenamelist = array();
            $i = 0;
            foreach($memberlist as $v){
                $activenamelist[$i]['shop_name'] = isset($shoplist[$v['shop_id']]) ? $shoplist[$v['shop_id']] : '未知店铺';
                $activeinfo = $activemodel->dump(array('active_id' => $v['active_id']));
                if($activeinfo){

                    foreach($rs_orders as $v_order){
                        if($v_order['pay_time']>=$activeinfo['create_time'] && $v_order['pay_time']<=$activeinfo['end_time']){
                            $activenamelist[$i]['orders'][] = $v_order;
                        }
                    }

                    $activenamelist[$i]['active_name'] = $activeinfo['active_name'];
                    $activenamelist[$i]['total_num'] = $activeinfo['total_num'];
                    $activenamelist[$i]['create_time'] = date("y-m-d h:i:s", $activeinfo['create_time']);
                    $activenamelist[$i]['end_time'] = date("y-m-d h:i:s", $activeinfo['end_time']);
                }else{
                    $activenamelist[$i]['active_name'] = '活动已经删除';
                    $activenamelist[$i]['total_num'] = '未知';
                    $activenamelist[$i]['create_time'] = '未知';
                    $activenamelist[$i]['end_time'] = '未知';
                }
                $i++;
            }

            $view = $_get['view'];
            $total_page = ceil($count / $pagelimit);
            $pager = $render->ui()->pager ( array ('current' => $page, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_member&act=index&action=detail&_finder[finder_id]=' . $_get['_finder']['finder_id'] . '&id='.$id.'&finderview=detail_active&page=%d&view='.$view.'&shop_id='.$shopid ));
            $render->pagedata['pager'] = $pager;
            $render->pagedata['activenamelist'] = $activenamelist;
        }
        return $render->fetch('admin/member/active.html');
    }

    var $detail_points = '积分日志';
    function detail_points($id)
    {
        if(!$id){
            $id = $_GET['id'];
        }
        $app = app::get('taocrm');
        $this->member_id = $this->_get_members($id);
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }

        $mdl_points_log = app::get('taocrm')->model('all_points_log');
        $schema = $mdl_points_log->get_schema();
        $points_type_conf = $schema['columns']['points_type']['type'];

        $mdl_member_points = app::get('taocrm')->model('member_points');
        $points = $mdl_member_points->get_points(array('member_id'=>$this->member_id));
        foreach($points as $k=>$v){
            $points[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            $points[$k]['points_type'] = $points_type_conf[$v['points_type']];
        }

        $pagelimit = 3;//分页
        $page_log = max(1, intval($_GET['page_log']));
        $offset = ($page_log - 1) * $pagelimit;
        $logs = $mdl_points_log->db->select('select * from sdb_taocrm_all_points_log where member_id in ('.implode(',',$this->member_id).') order by id desc limit '.$offset.','.$pagelimit);
        $count = $mdl_points_log->count(array('member_id'=>$this->member_id));
        foreach($logs as $k=>$v){
            $logs[$k]['user_name'] = app::get('taocrm')->model('members')->dump(array('member_id'=>$v['member_id']),'uname');
            $logs[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
            //$logs[$k]['point_type'] = $point_type[$v['point_type']];
           // $logs[$k]['points_type'] = $points_type_conf[$v['points_type']];
            $logs[$k]['order_bn'] = app::get('ecorder')->model('orders')->dump($v['order_id'],'order_bn');
            $logs[$k]['refund_bn'] = app::get('ecorder')->model('refunds')->dump($v['refund_id'],'refund_bn');
        }
        $render = $app->render();
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page_log, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_all_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_points&page_log=%d&id='.$id));
        $render->pagedata['pager'] = $pager;
        $render->pagedata['logs'] = $logs;
        $render->pagedata['points'] = $points;
        $render->pagedata['member_id'] = $id;
        $render->pagedata['finder_id'] = $_GET['finder_id'];
        return $render->fetch('admin/member/points_log.html');
    }


//	var $detail_service = '接待日志';
//    function detail_service($id){
//         if (!$id) $uid = $_GET['id'];
//        $id = $this->_get_members($uid);
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
//
//        return $render->fetch('admin/member/service.html');
//
//    }


    var $detail_caselog = '服务记录';
    public function detail_caselog($member_id)
    {
        if(!$member_id) $member_id = $_GET['member_id'];
        $member_id = $this->_get_members($member_id);
        $app = app::get('taocrm');
        $ecorder = app::get('ecorder');
        $member_caselog = $app->model('member_caselog');
        $shop = $ecorder->model('shop');

        $rs_shop = $shop->getList('shop_id,name');
        foreach($rs_shop as $v){
            $shops[$v['shop_id']] = $v['name'];
        }

        $rs_category = $app->model('member_caselog_category')->getList('category_id,category_name');
        foreach($rs_category as $v){
            $categorys[$v['category_id']] = $v['category_name'];
        }

        $rs_caselog = $member_caselog->getList('*',array('member_id'=>$member_id),0,-1,'id desc');
        foreach($rs_caselog as &$v){
            $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            if($v['alarm_time']>0)
            $v['alarm_time'] = date('Y-m-d H:i', $v['alarm_time']);
            $v['shop_name'] = $shops[$v['shop_id']];
            $v['media'] = $categorys[$v['media']];
            $v['category'] = $categorys[$v['category']];
            $v['content'] = mb_substr($v['content'],0,12,'utf-8');
        }

        $render = $app->render();
        $render->pagedata['caselog'] = $rs_caselog;
        return $render->fetch('admin/member/finder/caselog.html');
    }


    var $detail_merger = '客户合并历史';
    public function detail_merger($member_id){
        $app = app::get('taocrm');
        $objMembers = $app->model('members');
        $member = $objMembers->getList('*',array('member_id'=>$member_id));
        $member = $member[0];
        $subMembers = $objMembers->getList('*',array('parent_member_id'=>$member_id));
        $memberList = array();
        $memberList[] = $member;
        if($subMembers){
            foreach($subMembers as $row){
            	
                $memberList[] = $row;
            }
        }
        
        $channelTypeList = $objMembers->getChannelTypeList();
        foreach($memberList as $key=>$row){
        	$memberList[$key]['channel_type'] = !empty($channelTypeList[$row['channel_type']]) ? $channelTypeList[$row['channel_type']] : '-';
        }
        $render = $app->render();
        $render->pagedata['memberList'] = $memberList;
        $render->pagedata['member_id'] = $member_id;
        return $render->fetch('admin/member/all/merger.html');
    }
    
    var $detail_mobilemsg = '短信记录';
    public function detail_mobilemsg($member_id)
    {
        $rs = app::get('taocrm')->model('members')->dump($member_id, 'mobile');
        if($rs) {
        $sms_log_mod = app::get('taocrm')->model('sms_log');
        $params = array(
                'mobile' => $rs['contact']['phone']['mobile'],
                //'status' => 'succ',
        );
        $log_list = $sms_log_mod->getList('*',$params,0,10,'send_time desc');
        $source_type = array(
                'active_plan' => '营销计划',
                'active_cycle' => '周期营销',
                'market_active' => '营销活动',
                'plugins_plugins' => '自动插件',
                'taocrm_member_import_batch' => '导入客户',
                'taocrm_member_caselog' => '服务记录',
                'market_callplan' => '呼叫计划',
                'taocrm_member_group' => '自定义分组',
                'market_fx_activity' => '分销活动',
                'sale_model' => '营销模型',
                'weixin' => '微信服务',
                'report' => '运营报表',
                'other' => '其他',
            );
            foreach($log_list as $k => $log){
            $log_list[$k]['source'] = $source_type[$log['source']];
            }
        }
        $render = app::get('taocrm')->render();
        $render->pagedata['log_list'] = $log_list;
        return $render->fetch('admin/member/all/mobilemsg.html');
    }

    var $detail_stored_value = '储值记录';
    function detail_stored_value($id)
    {
        if(!$id){
            $id = $_GET['id'];
        }
        $app = app::get('taocrm');
        $this->member_id = $this->_get_members($id);
        $rs = app::get('ecorder')->model('shop')->getList('shop_id,shop_bn,name');
        if($rs) {
            foreach($rs as $v){
                $shops[$v['shop_id']] = $v;
            }
        }

        $mdl_stored_value_log = app::get('taocrm')->model('stored_value_log');
        $member_stored_value = app::get('taocrm')->model('stored_value');
        //$stored_values = $member_stored_value->getList(array('member_id'=>$this->member_id));
        $stored_values = $member_stored_value->getList('*',array('member_id'=>$id));
        foreach($stored_values as $k=>$v){
            empty($v['shop_id']) ?  $shop_name =  '-' :  $shop_name = $shops[$v['shop_id']]['name'];
            $stored_values[$k]['shop_name'] = $shop_name;
        }

        $pagelimit = 3;//分页
        $page_log = max(1, intval($_GET['page_log']));
        $offset = ($page_log - 1) * $pagelimit;


        $logs = $mdl_stored_value_log->db->select('select * from sdb_taocrm_stored_value_log where member_id in ('.implode(',',$this->member_id).') order by log_id desc limit '.$offset.','.$pagelimit);

        $count = $mdl_stored_value_log->count(array('member_id'=>$this->member_id));
        foreach($logs as $k=>$v){
            $logs[$k]['user_name'] = app::get('taocrm')->model('members')->dump(array('member_id'=>$v['member_id']),'uname');
            $logs[$k]['shop_name'] = $shops[$v['shop_id']]['name'];
        }
        $render = $app->render();
        $total_page = ceil($count / $pagelimit);
        $pager = $render->ui()->pager ( array ('current' => $page_log, 'total' => $total_page, 'link' =>'index.php?app=taocrm&ctl=admin_all_member&act=index&action=detail&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finderview=detail_stored_value&page_log=%d&id='.$id));

        $render->pagedata['stored_values'] = $stored_values;
        $render->pagedata['pager'] = $pager;
        $render->pagedata['logs'] = $logs;
        $render->pagedata['stored_values'] = $stored_values;
        $render->pagedata['member_id'] = $id;
        $render->pagedata['finder_id'] = $_GET['finder_id'];
        return $render->fetch('admin/member/stored_value_log.html');
    }

    protected function getConnect()
    {
        if (self::$hardeWareConnect == null) {
            self::$hardeWareConnect = new taocrm_middleware_connect;
        }
        return self::$hardeWareConnect;
    }

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

    /**
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
    /*
    var $detail_pointlog = '积分统计';
     public function detail_pointlog($member_id)
     {
     if(!$member_id) $member_id = $_GET['member_id'];
     $app = app::get('taocrm');
     $ecorder = app::get('ecorder');
     $member_caselog = $app->model('member_caselog');
     $shop = $ecorder->model('shop');

     $rs_shop = $shop->getList('shop_id,name');
     foreach($rs_shop as $v){
     $shops[$v['shop_id']] = $v['name'];
     }

     $rs_category = $app->model('member_caselog_category')->getList('category_id,category_name');
     foreach($rs_category as $v){
     $categorys[$v['category_id']] = $v['category_name'];
     }

     $rs_caselog = $member_caselog->getList('*',array('member_id'=>$member_id),0,-1,'id desc');
     foreach($rs_caselog as &$v){
     $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
     if($v['alarm_time']>0)
     $v['alarm_time'] = date('Y-m-d H:i', $v['alarm_time']);
     $v['shop_name'] = $shops[$v['shop_id']];
     $v['media'] = $categorys[$v['media']];
     $v['category'] = $categorys[$v['category']];
     $v['content'] = mb_substr($v['content'],0,12,'utf-8');
     }

     $render = $app->render();
     $render->pagedata['caselog'] = $rs_caselog;
     return $render->fetch('admin/member/finder/caselog.html');
     }

     var $detail_advancelog = '储值统计';
     public function detail_advancelog($member_id)
     {
     if(!$member_id) $member_id = $_GET['member_id'];
     $app = app::get('taocrm');
     $ecorder = app::get('ecorder');
     $member_caselog = $app->model('member_caselog');
     $shop = $ecorder->model('shop');

     $rs_shop = $shop->getList('shop_id,name');
     foreach($rs_shop as $v){
     $shops[$v['shop_id']] = $v['name'];
     }

     $rs_category = $app->model('member_caselog_category')->getList('category_id,category_name');
     foreach($rs_category as $v){
     $categorys[$v['category_id']] = $v['category_name'];
     }

     $rs_caselog = $member_caselog->getList('*',array('member_id'=>$member_id),0,-1,'id desc');
     foreach($rs_caselog as &$v){
     $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
     if($v['alarm_time']>0)
     $v['alarm_time'] = date('Y-m-d H:i', $v['alarm_time']);
     $v['shop_name'] = $shops[$v['shop_id']];
     $v['media'] = $categorys[$v['media']];
     $v['category'] = $categorys[$v['category']];
     $v['content'] = mb_substr($v['content'],0,12,'utf-8');
     }

     $render = $app->render();
     $render->pagedata['caselog'] = $rs_caselog;
     return $render->fetch('admin/member/finder/caselog.html');
     }*/
}
