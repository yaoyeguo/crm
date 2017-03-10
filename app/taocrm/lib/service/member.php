<?php
class taocrm_service_member {

    static $error;

	/**
	 * 对应店铺信息
	 * @var Array
	 */
	protected $_shopInfo = array ();

	function __construct() {
		$this->app = app::get ( 'taocrm' );
	}

	/**
	 * 获取联系人key
	 *
	 * @param void
	 * @return void
	 */
	protected function getContactKey($data) {
		// $bindkey = md5($data['member_id'].$data['addr'].$data['zip'].$data['mobile'].$data['tel'].$data['area_city'].$data['area_state'].$data['area_district'].$data['email'].$data['name']);
		$bindkey = md5 ( $data ['member_id'] . $data ['mobile'] . $data ['name'] );
		return $bindkey;
	}

	/**
	 * 获取收货人key
	 *
	 * @param void
	 * @return void
	 */
	protected function getReceiverKey($data) {
		// $bindkey = md5($data['member_id'].$data['mobile'].$data['telephone'].$data['area_city'].$data['area_state'].$data['area_district'].$data['email'].$data['name']);
		$bindkey = md5 ( $data ['member_id'] . $data ['mobile'] . $data ['name'] );
		return $bindkey;
	}

	/**
	 * 客户添加与更新
	 *
	 * @access private
	 * @param array $memberInfo 客户信息
	 * @param string $shopId 店铺ID
	 * @return int 客户ID
	 */
	public function saveMember($shopId, $memberInfo, $consignee=array(), $node_type='taobao', $order_createtime=0)
    {
		$memberobj = app::get('taocrm')->model('members');

		if(empty($memberInfo)){
			return null;
		}

		//获取店铺信息并做检查
		$this->_shopInfo = $this->fetchShopInfo ( $shopId );

		if (empty ( $memberInfo ['name'] )) {
			if (! empty ( $consignee ['name'] )) { //拿收货人姓名当作客户姓名
				$memberInfo ['name'] = $consignee ['name'];
			} else {
				$memberInfo ['name'] = $memberInfo ['uname'];
			}
		}

			if (empty ( $memberInfo ['uname'] )) {
				$memberInfo['uname'] = $memberInfo['name'];
			}

		$memberDetail = array ();
		$memberId = null;
        $structs = $memberobj->get_structs();
		if ($memberInfo ['uname']) {
			$memberInfo['birthday'] = strtotime($membersData['profile']['birthday']);
			$memberInfo['zip'] = $memberInfo['zipcode'];
			$memberInfo['tel'] = $memberInfo['telephone'];

			//当手机号为空，固话又是手机号码，自动填充到手机字段上
			if(empty($memberInfo['mobile']) && strlen($memberInfo['tel']) == 11){
				$memberInfo['mobile'] = $memberInfo['tel'];
			}

			$memberInfo['ext_uid'] = $memberInfo ['member_id'];
			$memberInfo['stand_node_id'] = $this->_shopInfo['node_id'];

            unset($memberInfo['member_id']); //删除ecstore的会员ID

            //转换参数
            //$membersData = utils::structToArray($structs, $memberInfo);
            $membersData = $memberInfo;
			$membersData ['shop_id'] = $shopId;
            /*
			if (! empty ( $consignee )) {
				$membersData ['order_last_time'] = time ();
            }*/

			//判断是否存在该客户
            if(!$order_sdf['createtime']){
                $order_sdf['createtime'] = time();
            }
            $rs_member = $this->acceptCreateMember($memberInfo, $node_type);
            if($rs_member){
                if($order_createtime>0){
                    $rs_member['order_first_time'] = min($order_createtime, $rs_member['order_first_time']);
                    $rs_member['order_last_time'] = max($order_createtime, $rs_member['order_last_time']);
                }
                $rs_member = kernel::single('ecorder_func')->clear_value($rs_member);
                $membersData = array_merge($membersData, $rs_member);
                $membersData['member_id'] = $rs_member['member_id'];
                $act = 'update';
            }else{
				$membersData ['create_time'] = time ();
                $membersData['order_first_time'] = $order_createtime;
                $membersData['order_last_time'] = $order_createtime;
				$membersData['channel_type'] = $this->_shopInfo['node_type'];
                $act = 'insert';
			}

            $membersData['update_time'] = time();
            $membersData['order_first_time'] = floatval($membersData['order_first_time']);
            $membersData['order_last_time'] = floatval($membersData['order_last_time']);

            //防止空数据覆盖用户的输入数据
            $membersData = kernel::single('ecorder_func')->trim_array($membersData);
            $membersData = kernel::single('ecorder_func')->clear_value($membersData);

            if($act == 'insert'){
                unset($membersData['member_id']);
                $membersData['member_id'] = $memberobj->insert($membersData);
				}else{
                $memberobj->update($membersData, array('member_id'=>$membersData['member_id']));
            }

            if($membersData['member_id']){
					$memberId = $membersData ['member_id'];

				//保存客户联系方式
				$memberInfo ['shop_id'] = $shopId;
				$memberInfo['member_id'] = $memberId;
				$this->saveMemberContact ( $memberInfo );


				//保存客户收货人方式
				if (! empty ( $consignee )) {
					$consignee ['shop_id'] = $shopId;
					$consignee ['member_id'] = $memberId;
					$this->saveMemberReceiver ( $consignee );
				}
            }else{
                $log = app::get('ecorder')->model('api_log');
                $logTitle = '会员创建失败['.$membersData['uname'].']';
                $logInfo = '会员接口：<BR>';
                $logInfo .= '参数：'.var_export($membersData, true).'<BR>';
                $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$membersData['uname']));
			}
		}

		//保存客户自定义属性
		if(isset($memberInfo['prop_name']) && $memberInfo['prop_name']){
			$oMemberProp = app::get('taocrm')->model('member_property');
			$oMemberProp->delete(array('shop_id'=>$shopId,'uname'=>$memberInfo['uname']));
			$prop_name = $memberInfo['prop_name'];
			foreach($prop_name as $k=>$v){
				if($k && $v){
					$save = array(
                        'shop_id'=>$shopId,
                        'uname'=>$memberInfo['uname'],
                        'property'=>$k,
                        'value'=>$v,
					);
					$oMemberProp->insert($save);
				}
			}

            if($memberId){
                $memberobj->save_member_prop_val(array_values($prop_name), $memberId, $shopId);
            }
		}
		return $memberId;
	}

	/**
	 * 客户添加与更新
	 *
	 * @access private
	 * @param array $memberInfo 客户信息
	 * @param string $shopId 店铺ID
	 * @return int 客户ID
	 */
    public function saveOverallMember($memberInfo, $props=null)
    {
		$memberobj = app::get ('taocrm')->model('members');

		$memberId = null;

        $member_rec = array();
		$memberInfo['update_time'] = time();
		if(!isset($memberInfo['member_id'])){
			if (!$this->acceptCreateMember ( $memberInfo,$memberInfo['channel_type'] )) {
				$memberInfo['create_time'] = time();
			}
		}

		$structs = app::get ( 'taocrm' )->model ( 'members' )->get_structs ();
        //$membersData = utils::structToArray($structs, $memberInfo);
        //$res = $memberobj->save($memberInfo);
        if($memberInfo['member_id']){
            $memberId = $memberInfo['member_id'];
            $res = $memberobj->update($memberInfo,array('member_id'=>$memberId));
        }else{
            $res = $memberobj->insert($memberInfo);
            $memberId = $res;
        }
        if( ! $res){
            $this->error = array(
                'status'=>false,
                'msg'=>'客户添加失败！',
            );
            return false;
        }

        //保存店铺会员
        if($memberInfo['shop_id']){
            $save_arr = array(
                'member_id' => $memberId,
                'shop_id' => $memberInfo['shop_id'],
                'update_time' => time(),
            );
            app::get('taocrm')->model('member_analysis')->save($save_arr);
        }

        /*
         * 推荐开始
         * */
        //保存用户基本信息及自己的推荐码
        $rec_mod = app::get('taocrm')->model('members_recommend');
        $member_rec = array(
            'member_id' => $memberId,
            'uname' => $memberInfo['uname'],
            'name' => $memberInfo['name'],
            'mobile' => $memberInfo['mobile'],
            'update_time' => time(),
        );
        $has_code = $rec_mod->dump(array('member_id'=>$memberId));
        if( ! $has_code){
            $recno_mod = app::get('taocrm')->model('members_recommend_no');
            $code = array('id'=>'');
            $recno_mod->insert($code);
            $member_rec['self_code'] = $code['id'] + 1000000000;
            $member_rec['create_time'] = time();
        }
        //$memberInfo['parent_code'] && $member_rec['parent_code'] = $memberInfo['parent_code'];
        if($memberInfo['parent_code']){
            $member_rec['parent_code'] = $memberInfo['parent_code'];
        }
        $rec_mod->save($member_rec);

        //更新推荐关系
       // $memberInfo['parent_code'] && $rec_parent_info = $rec_mod->dump(array('self_code'=>$memberInfo['parent_code']));
        if($memberInfo['parent_code']){
            $rec_parent_info = $rec_mod->dump(array('self_code'=>$memberInfo['parent_code']));
        }
        if($rec_parent_info)
        {
            //“告诉”推荐人，被推荐人了
            $member_rec_d['update_time'] = time();
            $member_rec_d['is_parent'] = 'true';
            $rec_mod->update($member_rec_d,array('self_code'=>$member_rec['parent_code']));

            //把没有子推荐的状态改掉
            $is_p = $rec_mod->count(array('parent_code'=>$has_code['parent_code']));
            $member_rec_d2['update_time'] = time();
            $member_rec_d2['is_parent'] = 'false';
            if(!$is_p)
                $rec_mod->update($member_rec_d2,array('self_code'=>$has_code['parent_code']));
        }
        /*
         * 推荐结束
         */

		//保存客户自定义属性
		if(!empty($props)){
            $memberobj->save_member_prop_val($props, $memberId, 'all');
		}

		return $memberId;
	}

    //判断会员是否存在
    function check_repeat_members($filter, $fields='*')
    {
        $mdl_members = app::get('taocrm')->model('members');
        foreach($filter as $v){
            $v['parent_member_id'] = 0;
            //$v['channel_type'] = $node_type;
            $rs_member = $mdl_members->getList($fields, $v, 0, 1);
            if($rs_member[0]){
                return $rs_member[0];
            }
        }
        return false;
    }

    /**
     * 检查当前客户能否被创建
     *
     * 注意：如果tel和mobile全部为空，会导致判断出错
     * @param void
     * @return Boolean
     */
    public function acceptCreateMember(&$memberInfo, $node_type)
    {
        $mobile_key = 'mobile';
        $mobile = trim($memberInfo['mobile']);
        if( ! $mobile){
            $mobile_key = 'tel';
            $mobile = trim($memberInfo['tel']);
        }

        //判断条件用户名和手机号码
        $filter = array();
        if($node_type == 'taobao'){//淘宝订单数据
            $filter[] = array(
                'uname' => $memberInfo['uname'],
                'channel_type' => $node_type
            );
        }elseif($node_type == 'manual_entry'){//手动订单
            $filter[] = array(
                $mobile_key => $mobile
            );
        }elseif($node_type == 'ecos.b2c'){//b2c订单数据
            if($memberInfo['uname'] && $mobile){
                $filter[] = array(
                    'uname' => $memberInfo['uname'],
                    $mobile_key => $mobile
                );
            }
            if($this->_shopInfo['shop_id'] && $memberInfo['ext_uid']){
                $filter[] = array(
                    'ext_uid' => $memberInfo['ext_uid'],
                    'shop_id'=> $this->_shopInfo['shop_id']
                );
            }
            if($this->_shopInfo['shop_id'] && $memberInfo['uname']){
                $filter[] = array(
                'uname' => $memberInfo ['uname'],
                    'shop_id' => $this->_shopInfo['shop_id']
                );
            }
        }else{
            $filter[] = array(
                'uname' => $memberInfo['uname'],
                $mobile_key => $mobile
            );
        }

        $filter = kernel::single('ecorder_func')->clear_value($filter);
        $rs_member = $this->check_repeat_members($filter);
        if($rs_member){
            $memberInfo['member_id'] = $rs_member['member_id'];
			return $rs_member;
        } else {
            unset($memberInfo['member_id']);
			return false;
        }
    }

	/**
	 * 保存客户联系方式
	 *
	 * @param void
	 * @return Boolean
	 */
    public function saveMemberContact($memberInfo)
    {
		//获取店铺信息并做检查
		if (empty ( $this->_shopInfo ['channel_id'] )) {
			if(empty($memberInfo['shop_id']))return false;
			$this->_shopInfo = $this->fetchShopInfo ( $memberInfo ['shop_id'] );
		}

		$md5 = $this->getContactKey ( $memberInfo );
		$member_contact = app::get ( 'taocrm' )->model ( 'member_contacts' )->dump ( array ('member_id' => $memberInfo ['member_id'], 'md5' => $md5 ), 'contact_id' );

		/*$structs = array(
		 'account/uname'=>'member_id',
		 'contact/state'=>'area_state',
		 'contact/city'=>'area_city',
		 'contact/district'=>'area_district',
		 'contact/name'=>'name',
		 'contact/area'=>'area',
		 'contact/addr'=>'addr',
		 'contact/phone/mobile'=>'mobile',
		 'contact/phone/telephone'=>'tel',
		 'contact/email'=>'email',
		 'contact/zip'=>'zip',
		 'shop_id'=>'shop_id',
		 );*/
		$structs = app::get ( 'taocrm' )->model ( 'member_contacts' )->get_structs ();
		$memberContactData = utils::structToArray ( $structs, $memberInfo );
		$memberContactData ['channel_id'] = $this->_shopInfo ['channel_id'];
		/*$memberContactData = array(
		 'account' => array(
		 'uname' => $memberInfo['member_id'],
		 ),
		 'contact' => array(
		 'state'=> $memberInfo['area_state'],
		 'city'=> $memberInfo['area_city'],
		 'district'=> $memberInfo['area_district'],
		 'name' => $memberInfo['name'],
		 'area' => $memberInfo['area'],
		 'addr' => $memberInfo['addr'],
		 'phone' => array(
		 'mobile' => $memberInfo['mobile'],
		 'telephone' => $memberInfo['tel']
		 ),
		 'email' => $memberInfo['email'],
		 'zip' => $memberInfo['zip'],
		 ),
		 'shop_id'=>$memberInfo['shop_id'],
		 'channel_id'=>$this->_shopInfo['channel_id'],
		 'create_time'=>time(),
		 );*/
		if ($member_contact) {
			$memberContactData ['contact_id'] = $member_contact ['contact_id'];
		} else {
			//首次创建产生唯一值
			$memberContactData ['md5'] = $md5;

			$memberContactData ['create_time'] = time ();
		}
		app::get ( 'taocrm' )->model ( 'member_contacts' )->save ( $memberContactData );

		return $memberContactData ['contact_id'];
	}

	/**
	 * 保存客户收货人联系方式
	 *
	 * @param void
	 * @return Boolean
	 */
	public function saveMemberReceiver($consignee) {
		//获取店铺信息并做检查
		/*if (empty ( $this->_shopInfo ['channel_id'] )) {
			if(empty($consignee['shop_id']))return false;
			$this->_shopInfo = $this->fetchShopInfo ( $consignee ['shop_id'] );
		}*/

		$md5 = $this->getReceiverKey ( $consignee );
		$member_receiver = app::get ( 'taocrm' )->model ( 'member_receivers' )->dump ( array ('member_id' => $consignee ['member_id'], 'md5' => $md5 ), 'receiver_id' );

		/*$structs = array(
		 'account/uname'=>'member_id',
		 'contact/state'=>'area_state',
		 'contact/city'=>'area_city',
		 'contact/district'=>'area_district',
		 'contact/name'=>'name',
		 'contact/area'=>'area',
		 'contact/addr'=>'addr',
		 'contact/phone/mobile'=>'mobile',
		 'contact/phone/telephone'=>'tel',
		 'contact/email'=>'email',
		 'contact/zip'=>'zip',
		 'shop_id'=>'shop_id',
		 );*/
		$structs = app::get ( 'taocrm' )->model ( 'member_receivers' )->get_structs ();
		$memberReceiverData = utils::structToArray ( $structs, $consignee );
		//$memberReceiverData ['channel_id'] = $this->_shopInfo ['channel_id'];

		/*$memberReceiverData = array(
		 'account' => array(
		 'uname' => $consignee['member_id'],
		 ),
		 'contact' => array(
		 'state'=> $consignee['area_state'],
		 'city'=> $consignee['area_city'],
		 'district'=> $consignee['area_district'],
		 'name' => $consignee['name'],
		 'area' => $consignee['area'],
		 'addr' => $consignee['addr'],
		 'phone' => array(
		 'mobile' => $consignee['mobile'],
		 'telephone' => $consignee['telephone']
		 ),
		 'email' => $consignee['email'],
		 'zip' => $consignee['zip'],
		 ),
		 'shop_id'=>$consignee['shop_id'],
		 'channel_id'=>$this->_shopInfo['channel_id'],
		 'create_time'=>time(),
		 );*/

		if ($member_receiver) {
			$memberReceiverData ['receiver_id'] = $member_receiver ['receiver_id'];
		} else {
			//首次创建产生唯一值
			$memberReceiverData ['md5'] = $md5;

			$memberReceiverData ['create_time'] = time ();
		}
		app::get ( 'taocrm' )->model ( 'member_receivers' )->save ( $memberReceiverData );

		return $memberReceiverData ['receiver_id'];
	}

	/**
	 * 客户统计
	 *
	 * @param $memberId
	 * @return bool
	 */
	public function countMemberBuys($memberId, $shopId)
	{
        $order_total_num = 0;
        $order_total_amount = 0;
        $order_succ_num = 0;
        $order_succ_amount = 0;

        //todo:这里和定时任务的统计重复了,需要合并,yw,2015-05-16
        $m_orders = app::get('ecorder')->model('orders');
        $rs = $m_orders->getList('total_amount,payed,pay_status,status', array('member_id'=>$memberId));
        if($rs){
        foreach($rs as $v){
                if(intval($v['pay_status']) == 1){}

                $order_total_num += 1;
                $order_total_amount += $v['total_amount'];
                if($v['status'] == 'finish'){
                    $order_succ_num += 1;
                    $order_succ_amount += $v['total_amount'];
                }
            }

		$countData = array(
    		'order_total_num' => $order_total_num,
	    	'order_total_amount' => $order_total_amount,
	    	'order_succ_num' => $order_succ_num,
	    	'order_succ_amount' => $order_succ_amount,
                'update_time' => time(),
		);
            app::get('taocrm')->model('members')->update($countData, array('member_id' => $memberId));
        }

		//计算客户等级和积分
		//$this->updateMemberPoints($id,$type);//创建订单时直接调用
		//$this->saveMemberAnalysis ( $memberId, $shopId );
		$this->updateMemberLv($memberId, $shopId);
        return true;
	}

	// 初始化客户分析数据
	public function createMemberAnalysis($member_id, $shop_id, $order_info)
    {
		$db = kernel::database ();
        $sql = 'select id from sdb_taocrm_member_analysis where member_id=' . $member_id . ' and shop_id="' . $shop_id . '"';
		$memberInfo = $db->selectrow($sql);
		if (! $memberInfo) {
			$analysisData ['member_id'] = $member_id;
			$analysisData ['shop_id'] = $shop_id;
			$analysisData ['buyTimeIntval'] = 1;
			$analysisData ['buy_freq'] = 1;
			$analysisData ['avg_buy_interval'] = 0;
			$analysisData ['buy_month'] = 1;
			$analysisData ['total_orders'] = 1;
			$analysisData ['update_time'] = time ();

			//初始化订单信息
			if($order_info){
				$analysisData ['first_buy_time'] = $order_info['createtime'];
				$analysisData ['last_buy_time'] = $order_info['createtime'];
				$analysisData ['total_amount'] = $order_info['total_amount'];
				$analysisData ['total_per_amount'] = $order_info['total_amount'];
			}

			$q = app::get ( 'taocrm' )->model ( 'member_analysis' )->insert ( $analysisData );
			return $q;
		}
		return true;
	}

	/**客户数据分析
	 *
	 *
	 * @param void
	 * @return array
	 *  用于营销超市，需要保留的字段：
	 first_buy_time
	 total_orders
	 last_buy_time
	 finish_total_amount
	 finish_orders
	 total_per_amount
	 */
	protected function saveMemberAnalysis($memberId, $shopId)
	{
		$base_filter = array ('member_id' => $memberId, 'shop_id' => $shopId );
		$total_orders = app::get ( 'ecorder' )->model ( 'orders' )->count ( $base_filter );

		$db = kernel::database ();
		$memberInfo = $db->selectrow ( 'select * from sdb_taocrm_member_analysis where member_id=' . $memberId . ' and shop_id="' . $shopId . '"' );

		$sql = 'select min(createtime) as createtime from sdb_ecorder_orders where member_id='.$memberId .' and shop_id="'.$shopId.'"';
		$rs = $db->selectrow($sql);
		$first_buy_time = $rs['createtime'];
		if(!$first_buy_time) $first_buy_time = 1;

		if ($memberInfo) {
			//购买月数
			$buy_month = app::get ( 'ecorder' )->model ( 'orders' )->db->selectRow ( "select count(distinct FROM_UNIXTIME(createtime,'%Y-%m')) as num from sdb_ecorder_orders where member_id=$memberId and shop_id='$shopId' and pay_status='1' " );
			$buy_month = $buy_month ['num'];

			//最后下单时间
			$rs = $db->selectrow('select max(createtime) as createtime from sdb_ecorder_orders where member_id='.$memberId .' and shop_id="'.$shopId.'"');
			$last_buy_time = $rs['createtime'];

			//平均购买间隔(天)
			$buyTimeIntval = strtotime(date('Y-m-d',$last_buy_time)) - strtotime(date('Y-m-d',$first_buy_time));
			$buyTimeIntval = ($buyTimeIntval / (24 * 60 * 60)) + 1;
			$rs = app::get ( 'ecorder' )->model ( 'orders' )->db->selectRow ( "select count(distinct FROM_UNIXTIME(createtime,'%Y-%m-%d')) as num from sdb_ecorder_orders where member_id=$memberId and shop_id='$shopId' and pay_status='1' " );
			$buy_freq = $rs ['num'];
			$avg_buy_interval = ($buy_freq > 0) ? ($buyTimeIntval / $buy_freq) : 0;

		} else { //初次购买
			$buyTimeIntval = 1;
			$avg_buy_interval = 1;
			$buy_month = 1;
			$last_buy_time = $first_buy_time;
			$buy_freq = 1;
		}

		//最近三个月数据
		$sql = "select sum(total_amount) as month3_finish_amount,count(*) as month3_finish_orders from sdb_ecorder_orders where member_id={$memberId} and shop_id='{$shopId}' AND status='finish' AND createtime>".strtotime('-90 days')." ";
		$rs = $db->selectrow ($sql);
		$month3_finish_amount = floatval($rs['month3_finish_amount']);
		$month3_finish_orders = floatval($rs['month3_finish_orders']);
		//var_dump($sql);die();

		$nums = 0;
		$finish_total_amount = 0;
		$total_amount = 0;
		$finish_orders = 0;
		$base_filter ['pay_status'] = 1;
		$order_ids = array();
		$rs = app::get ( 'ecorder' )->model ( 'orders' )->getList ( 'order_id,status,total_amount', $base_filter );
		if ($rs) {
			foreach ( $rs as $v ) {
				$order_ids [] = $v ['order_id'];
				$total_amount += $v ['total_amount'];
				if ($v ['status'] == 'finish') {
					$finish_orders ++;
					$finish_total_amount += $v ['total_amount'];
				}
			}
		}
		unset ( $base_filter ['pay_status'] );

		$rs = app::get ( 'ecorder' )->model ( 'order_items' )->getList ( 'name,nums', array ('order_id' => $order_ids, 'delete' => 'false' ) );
		$bns = array ();
		if ($rs) {
			foreach ( $rs as $v ) {
				$bns [$v ['name']] = 1;
				$nums += $v ['nums'];
			}
		}
		$skus = count ( $bns );
		$pay_orders = count ( $order_ids ); //支付订单数
		$buy_skus = count (  ( $bns ) );
		$buy_products = $nums;
		$avg_buy_skus = ($pay_orders != 0) ? ($skus / $total_orders) : 0;
		$avg_buy_products = ($pay_orders != 0) ? ($buy_products / $total_orders) : 0;
		$finish_orders = $finish_orders;
		//$finish_total_amount = $finish_total_amount;
		$finish_per_amount = ($finish_orders != 0) ? ($finish_total_amount / $finish_orders) : 0;

		$base_filter['pay_status'] = 0;//未付款订单
		$unpay_orders = app::get ( 'ecorder' )->model ( 'orders' )->count ( $base_filter );
		$unpay_amount = $this->countMemberUnPayTotalAmount ( $memberId, $shopId );

		$base_filter['pay_status'] = 5;//退款订单
		$refund_orders = app::get ( 'ecorder' )->model ( 'orders' )->count ( $base_filter );
		$refund_amount = $this->countMemberRefundTotalAmount ( $memberId, $shopId );

		$total_per_amount = ($total_orders != 0) ? ($total_amount / $total_orders) : 0;
		$unpay_per_amount = ($unpay_orders != 0) ? ($unpay_amount / $unpay_orders) : 0;

		$analysisData = array(
            'member_id' => $memberId,
            'shop_id' => $shopId,
			'buy_freq' => $buy_freq,//购买频次(天)
            'avg_buy_interval' => $avg_buy_interval, //平均购买间隔(天)
            'buy_month' => $buy_month, //购买月数
            'buy_skus' => $buy_skus, //购买商品种数
            'buy_products' => $buy_products, //购买商品总数
            'first_buy_time' => $first_buy_time, //第一次购买时间
			'last_buy_time' => $last_buy_time,
			'channel_id' => $this->_shopInfo['channel_id'],
			'shop_id' =>  $shopId,
			'avg_buy_skus' => $avg_buy_skus,//平均购买商品种数
            'avg_buy_products' => $avg_buy_products, //平均购买商品件数
            'finish_orders' => $finish_orders, //成功的单数
            'total_orders' => $total_orders, //总订单数
            'total_amount' => $total_amount, //订单总金额
            'total_per_amount' => $total_per_amount, //订单客单价
            'unpay_per_amount' => $unpay_per_amount, //未支付客单价
            'finish_total_amount' => $finish_total_amount, //成功的金额
            'finish_per_amount' => $finish_per_amount, //成功的客单价
            'unpay_orders' => $unpay_orders, //未付款单数
			'unpay_amount' => $unpay_amount?$unpay_amount:0,//未付款金额
            'refund_orders' => $refund_orders, //退款订单数
			'refund_amount' => $refund_amount?$refund_amount:0,
            'month3_finish_amount' => $month3_finish_amount, //近3个月成功金额
            'month3_finish_orders' => $month3_finish_orders, //近3个月成功订单数
		//'points' => $memberInfo['points'],
		//'level' => $memberInfo['taobao_level'],
			'update_time' => time(),
		//'is_vip' => $memberInfo['is_vip'],
		//'shop_evaluation' => 'goods',//通过TOP获取
		);

		if ($memberInfo) {
			$filter = array ('id' => $memberInfo ['id'] );
			unset ( $analysisData ['shop_id'], $analysisData ['member_id'] );
			$q = app::get ( 'taocrm' )->model ( 'member_analysis' )->update ( $analysisData, $filter );
		} else {
			$q = app::get ( 'taocrm' )->model ( 'member_analysis' )->insert ( $analysisData );
		}

		return $q;
	}

	/**
	 * 统计订单总金额
	 *
	 * @param void
	 * @return array
	 */
	protected function countMemberTotalAmount($memberId, $shopId='')
	{
		$sql = "select sum(total_amount) as count from sdb_ecorder_orders where member_id={$memberId} AND pay_status='1' ";
		if($shopId){
			$sql .= " AND shop_id='{$shopId}' ";
		}
		$row = app::get('ecorder')->model('orders')->db->selectRow($sql);
		return $row['count'];
	}

	/**
	 * 统计成功订单总金额
	 *
	 * @param void
	 * @return array
	 */
	protected function countMemberSuccTotalAmount($memberId, $shopId='')
	{
		$sql = "select sum(total_amount) as count from sdb_ecorder_orders where member_id={$memberId} AND pay_status='1' AND status='finish' ";
		if($shopId){
			$sql .= " AND shop_id='{$shopId}' ";
		}
		$row = app::get('ecorder')->model('orders')->db->selectRow($sql);
		return $row['count'];
	}

	/**
	 * 统计未付款订单总金额
	 *
	 * @param void
	 * @return array
	 */
	protected function countMemberUnPayTotalAmount($memberId, $shopId) {
		$sql = 'select sum(total_amount) as count from sdb_ecorder_orders where pay_status = "0" and  member_id=' . $memberId . ' and shop_id="' . $shopId . '" ';
		$row = app::get ( 'ecorder' )->model ( 'orders' )->db->selectRow ( $sql );
		return $row ['count'];
	}

	/**
	 * 统计退款订单总金额 (只统计全额退)
	 *
	 * @param void
	 * @return int
	 */
	protected function countMemberRefundTotalAmount($memberId, $shopId) {
		$sql = 'select sum(total_amount) as count
                from sdb_ecorder_orders
                where pay_status = "5" and  member_id=' . $memberId . ' and shop_id="' . $shopId . '" ';
		$row = app::get ( 'ecorder' )->model ( 'orders' )->db->selectRow ( $sql );
		return $row ['count'];
	}

	/**
	 * 获取店铺信息
	 *
	 * @param void
	 * @return array
	 */
    protected function fetchShopInfo($shopId)
    {
		return app::get ( 'ecorder' )->model ( 'shop' )->dump ( $shopId, '*' );
	}

	function compara_val($sign, $val, $vall) {
		switch ($sign) {
			case 'gthan' :
				return $val > $vall;
				break;
			case 'sthan' :
				return $val < $vall;
				break;
			case 'equal' :
				return $val == $vall;
				break;
			case 'gethan' :
				return $val >= $vall;
				break;
			case 'sethan' :
				return $val <= $vall;
				break;
		}
	}

	/**
	 * 更新客户等级
	 *
	 * @param void
	 * @return bool
	 */
	public function updateMemberLv($member_id, $shop_id)
	{
		//获取等级规则
		$rs = app::get('ecorder')->model('shop_lv')->getList('*', array('shop_id' => $shop_id));
		if(!$rs) return false;

		$rs_memeber = array();
		$sql = 'select sum(total_amount) as finish_total_amount,count(*) as finish_orders from sdb_ecorder_orders where member_id='.$member_id.' AND shop_id="'.$shop_id.'" AND pay_status="1" AND `status`="finish" ';
		$row = kernel::database()->selectrow($sql);
		$rs_memeber['finish_total_amount'] = $row['finish_total_amount'];
		$rs_memeber['finish_orders'] = $row['finish_orders'];

        $amount = floatval($rs_memeber['finish_total_amount']);
        $buy_times = floatval($rs_memeber['finish_orders']);
		foreach ( $rs as $v ) {
			//购买金额
			if ($v ['is_default'] == '1') {
				$default_lv_id = $v ['lv_id'];
				continue;
			}
			if ($v ['amount_symbol'] == 'between') {
				if ($amount >= $v ['min_amount'] && $amount < $v ['max_amount']) {
					$tag = true;
				} else {
					$tag = false;
				}
			} elseif ($v ['amount_symbol'] == 'unlimited') {
				$tag = true;
			} else {
				$tag = $this->compara_val ( $v ['amount_symbol'], $amount, $v ['min_amount'] );
				$tag = $tag;
			}

			//购买次数
			if ($v ['buy_times_symbol'] == 'between') {
				if ($buy_times >= $v ['min_buy_times'] && $buy_times < $v ['max_buy_times']) {
					$tag_times = true;
				} else {
					$tag_times = false;
				}
			} elseif ($v ['buy_times_symbol'] == 'unlimited') {
				$tag_times = true;

			} else {
				$tag_times = $this->compara_val ( $v ['buy_times_symbol'], $buy_times, $v ['min_buy_times'] );

				$tag_times = $tag_times;
			}

			if ($tag == true && $tag_times == true) {
				$lv_id = $v ['lv_id'];
				break;
			}
		}

		//默认客户等级
		if ($default_lv_id && ! $lv_id) {
			$lv_id = $default_lv_id;
        }elseif( ! $lv_id){
            $lv_id = 0;
		}

			$filter = array ('shop_id'=>$shop_id,'member_id' => $member_id );
        $arr = array(
            'lv_id' => floatval($lv_id)
        );
			app::get ( 'taocrm' )->model ( 'member_analysis' )->update ( $arr, $filter );
		return true;
	}

	/**
	 * 更新客户积分
	 *
	 * @param int $id 订单或退款单ID
	 * @param string $type 单据类型：orders/refunds
	 * @return bool
	 */
    public function updateMemberPoints($id, $type, $_orderSdf)
    {
        //积分计算规则中选中的店铺，计算积分；不被选中店铺不计算。
        base_kvstore::instance('ecorder')->fetch('point_computation_rule', $point_computation_rule);
        if(!empty($point_computation_rule)){
            if(!in_array($_orderSdf['shop_id'],$point_computation_rule)){
                return false;
            }
        }

        $has_special_rules = false;

        //获取积分规则
        $rs_credit_rules = app::get('ecorder')->model('shop_credit')->getList('*', array ('shop_id' => $_orderSdf['shop_id']));
        if(!$rs_credit_rules){
            return false;
        }

		$oPointsLog = $this->app->model ( 'all_points_log' );
        if ($type == 'orders') { //已付款订单加分
            $sql = "select order_id,shop_id from sdb_taocrm_all_points_log where order_id=$id";
            $order_bn = $_orderSdf['order_bn'];
            if($order_bn != ''){
                $sql = "select order_id,shop_id from sdb_taocrm_all_points_log where order_bn='$order_bn' ";
            }
            $rs_logs = $oPointsLog->db->selectRow($sql);
            if($rs_logs){
                if(!$rs_logs['shop_id'] or $rs_logs['shop_id']==$_orderSdf['shop_id']){
				return false;
			}
            }

            $rs_amount = $_orderSdf;
			if(!$rs_amount) return false;
			$single_amount = $rs_amount['total_amount'];
			$single_payed = $rs_amount['payed'];
            $points_mark = '+';
			$arr_log ['order_id'] = $id;
			//$arr_log ['point_type'] = 'order';
        } else { //退款成功减分
            $rs_logs = $oPointsLog->db->selectRow("select * from sdb_taocrm_all_points_log where refund_id=$id");
            if($rs_logs) return false;

            $rs_amount = $_orderSdf;
            $order_bn = $rs_amount['tid'];
			$single_amount = $rs_amount['refund_fee'];
			$single_payed = $rs_amount['refund_fee'];
            $points_mark = '-';
			$arr_log ['refund_id'] = $id;
			//$arr_log ['point_type'] = 'refund';

            if( !$rs_amount ['shop_id'] or !$rs_amount ['member_id']) return false;
		}

		//获取该用户累计付款金额
		$sql = "select sum(total_amount) as all_amount,sum(payed) as all_payed from sdb_ecorder_orders where member_id=".$rs_amount['member_id']." and pay_status='1' and status in ('active','finish') ";
		$rs = $oPointsLog->db->selectRow($sql);
		if($rs['all_amount']){
			$all_amount = $rs['all_amount'];
			$all_payed = $rs['all_payed'];
		}else{
			$all_amount = $single_amount;
			$all_payed = $single_payed;
		}

        //处理单个商品的积分规则
        if($type == 'orders'){
            $good_sql = "select a.nums,a.amount,b.point_rule,b.fixed_point_num from sdb_ecorder_order_items as a
                left join sdb_ecgoods_shop_goods as b on a.goods_id=b.goods_id where a.order_id=".$arr_log['order_id'];
            $good_point_rules = $oPointsLog->db->select($good_sql);
            $good_points = 0;
            if($good_point_rules){
                foreach($good_point_rules as $goods){
                    if($goods['point_rule'] == '2'){//特价商品送固定积分
                        $good_points = intval($goods['nums'] * $goods['fixed_point_num']);
                    }
                    if($goods['point_rule'] == '2' or $goods['point_rule'] == '3'){//特价商品不送积分
                        $single_payed -= $goods['amount'];
                        $single_amount -= $goods['amount'];
                    }
                }
            }
        }

		//写入积分日志
		$shopId = $rs_amount ['shop_id'];
		$memberId = $rs_amount ['member_id'];
		$arr_log ['order_bn'] = $order_bn;
		$arr_log ['shop_id'] = $shopId;
		$arr_log ['member_id'] = $memberId;
		$arr_log ['op_time'] = time ();
		$arr_log ['op_user'] = 'system';

        //获取客户的生日信息
        $sql = 'select b_year,b_month,b_day from sdb_taocrm_member_ext where member_id='.$memberId;
        $member = $oPointsLog->db->selectRow($sql);

        //积分设置（叠加 还是 排他）
        $point_log = $oPointsLog->db->select('select set_type from sdb_ecorder_point_set_logs order by create_time desc limit 1');

        //通用积分规则计算
        foreach($rs_credit_rules as $v) {
            if( ! $v['cost_amount']){
                //'每个积分消费'为零时此积分规则为特殊积分规则（因为通用规则这个值大于等于1）
                $has_special_rules = true;
                continue;
            }
			if($v['order_type'] == 'all'){
				if($v['count_type'] == 'total_amount'){
					$amount = $all_amount;
				}else{
					$amount = $all_payed;
				}
			}else{
				if($v['count_type'] == 'total_amount'){
					$amount = $single_amount;
				}else{
					$amount = $single_payed;
				}
			}

			if ($v ['amount_symbol'] == 'between') {
				if ($amount >= $v ['min_amount'] && $amount <= $v ['max_amount']) {
					$tag = true;
				} else {
					$tag = false;
				}
			} elseif ($v ['amount_symbol'] == 'unlimited') {
				$tag = true;
			} else {
				$tag = $this->compara_val ( $v ['amount_symbol'], $amount, $v ['min_amount'] );
				$tag = $tag;
			}
			if ($tag == true) {
                $points = intval ( $amount / $v ['cost_amount'] );
				break;
			}
		}

        if($type == 'orders'){
            //若有特价商品送固定积分
            $points = intval($points) + intval($good_points);

            //特殊积分倍数
            if($has_special_rules == true){
                $points = $points * $this->get_points_ratio($rs_credit_rules,$point_log,$member,$id);
            }
        }

		if ($points) {
            //更新店铺分析表
            $sql = 'update sdb_taocrm_member_analysis set points=points'.$points_mark.$points.' WHERE member_id='.$memberId.' AND shop_id="'.$shopId.'" ';
            $oPointsLog->db->exec($sql);
            //若有负积分，直接清零
            $sql = 'UPDATE sdb_taocrm_member_analysis SET points=0 where points < 0 ';
            $oPointsLog->db->exec($sql);

            //变更积分符号
            if($points_mark == '-') {
                $points *= -1;
                $remark = '退款扣除:'.$_orderSdf['refund_id'];
            }else{
                $remark = '消费积分:'.$_orderSdf['order_bn'];
            }

            $arr_log['point_desc'] = $remark;
			$arr_log ['points'] = $points;
           // $arr_log['points_type'] = 'trade';
			$oPointsLog->save($arr_log);

            //全渠道积分
            if($points > 0){
                $logs = array(
                    'member_id' => $memberId,
                    'points_type' => 'trade',
                    'points' => $points,
                    'shop_id' => $shopId,
                );
            }else{
                $sql = 'select points,id from sdb_taocrm_member_points where  shop_id="'.$shopId.'"  member_id='.$memberId;
                $sql .= ' and (invalid_time >= '.time().' or ISNULL(invalid_time))';
                $sql .= ' order by invalid_time';
                $points_data = $oPointsLog->db->select($sql);
                $logs = array(
                    'member_id' => $memberId,
                    'points' => $points,
                    'point_data' => $points_data,
                );
            }
            $mdl_member_points = $this->app->model('member_points');
            $mdl_member_points->save_points($logs);
		}
		return true;
	}

	/**
	 * 获取最近一次的客户评价
	 */
	public function setMemberRate($shopId, $memberId, $result) {
		$row = kernel::database ()->selectrow ( 'select shop_evaluation from sdb_taocrm_member_analysis where member_id=' . $memberId . ' AND shop_id="' . $shopId . '" ' );
		$shop_evaluation = $row ['shop_evaluation'];
		$is_ok = false;
		if ($shop_evaluation == 'unkown') {
			$is_ok = true;
		} else {
			if ($result != $shop_evaluation) {

				if ($result == 'good') {
					if ($row ['shop_evaluation'] != 'bad' || $row ['shop_evaluation'] != 'neutral') {
						$is_ok = true;
					}
				} else if ($result == 'neutral') {
					if ($row ['shop_evaluation'] != 'bad') {
						$is_ok = true;
					}
				} else if ($result == 'bad') {
					$is_ok = true;
				}
			}
		}

		if ($is_ok) {
			$this->app->model ( 'member_analysis' )->db->exec ( 'update sdb_taocrm_member_analysis set shop_evaluation="' . $result . '" WHERE member_id=' . $memberId . ' AND shop_id="' . $shopId . '" ' );
		}
	}

	/**
	 * 按天统计数据
	 */
	public function runAnalysisDay($date,$type='modified')
	{
		if(is_int($date))$date = date('Y-m-d',$date);

		//店铺列表
		$oShop = &app::get ( 'ecorder' )->model ( 'shop' );
		$rs = $oShop->getList ( 'shop_id' );
		if(!$rs) return false;
		$execTime = time ();
		foreach ( $rs as $v ) {
			$curTime = time ();
			if ($curTime >= $execTime + 30) {
				kernel::database ()->dbclose ();
				$execTime = $curTime;
			}

			if($type == 'modified'){
				//获取发生变化订单创建日期
				$analysisDays = $this->getMemberAnalysisDay ( $v ['shop_id'], $date );
				foreach ( $analysisDays as $analysisDay ) {
					$this->saveMemberAnalysisDay ( $v ['shop_id'], $analysisDay );
				}
			}else{
				$this->saveMemberAnalysisDay ( $v ['shop_id'], $date );
			}

		}
		return true;
	}


	function getMemberAnalysisDay($shop_id, $date)
	{
		$start_time = strtotime ( $date );
		$end_time = strtotime ( $date ) + 86399;

		//$sql = "SELECT distinct FROM_UNIXTIME(createtime,'%Y-%m-%d') as analysis_day FROM sdb_ecorder_orders WHERE FROM_UNIXTIME(f_modified,'%Y-%m-%d')='$date' AND shop_id='$shop_id' ";

		$sql = "SELECT distinct FROM_UNIXTIME(createtime,'%Y-%m-%d') as analysis_day FROM sdb_ecorder_orders WHERE ( f_modified BETWEEN $start_time AND $end_time ) AND shop_id='$shop_id' ";
		$rows = kernel::database ()->select ( $sql );
		$days = array ();
		foreach ( $rows as $row ) {
			$days [] = $row ['analysis_day'];
		}

		return $days;
	}

	// 按订单更新时间更新数据,参数为时间戳
	public function updateAnalysisDay($shop_id, $date) {
		$db = kernel::database ();
		$where = '';
		if(!$date) $date = time();
		if($shop_id) $where = " and shop_id='$shop_id' ";

		$rs = $db->select ( 'SELECT shop_id,createtime  FROM `sdb_ecorder_orders`
                    WHERE f_modified >=' . strtotime ( '-1 days', $date ) . '
                    and f_modified < ' . $date . ' ' . $where );
		while ( $v = array_shift ( $rs ) ) {
			$dates [date ( 'Y-m-d', $v ['createtime'] )] [$v ['shop_id']] = $v;
		}

		if ($dates) {
			foreach ( $dates as $k => $v ) {
				foreach ( $v as $kk => $vv ) {
					$this->saveMemberAnalysisDay ( $kk, $k );
				}
			}
		}
	}

	/**
	 * 客户数据分析，以订单创建时间为准
	 * @param string $shop_id 店铺ID
	 * @param date   $date 年月日
	 * @return array
	 */
	public function saveMemberAnalysisDay($shop_id, $date)
	{
		if(strlen($date) != 10) return 'error date';
		$this->_shopInfo = $this->fetchShopInfo ( $shop_id );
		$oAnalysisDay = &app::get ( 'taocrm' )->model ( 'member_analysis_day' );
		$filter = array ('shop_id' => $shop_id, 'c_date' => $date );
		$rs = $oAnalysisDay->dump ( $filter, 'id' );
		if ($rs) {
			$data ['id'] = $rs ['id'];
		} else {
			$data = $filter;
			$data ['c_time'] = strtotime ( $date );
			$data ['c_date'] = $date;
			$data ['c_week'] = date ( 'W', strtotime ( $date ) );
			$data ['c_month'] = substr ( $date, 0, 7 );
			$data ['c_year'] = substr ( $date, 0, 4 );
		}

		$start_time = strtotime ( $date );
		$end_time = strtotime ( $date ) + 86399;

		//完成的订单数据
		$sql = "SELECT
                    count(distinct a.order_id) as finish_orders,
                    sum(a.total_amount) as finish_total_amount,
                    count(distinct a.member_id) as finish_members
                FROM sdb_ecorder_orders as a
                WHERE
                    (a.createtime BETWEEN $start_time AND $end_time)
                    AND a.shop_id='$shop_id'
                    AND a.status='finish'
                    AND a.pay_status='1'
                ";
		$rs = $oAnalysisDay->db->selectRow ( $sql );

		if($rs) $data = array_merge($data,$rs);
		if ($rs ['finish_orders'] > 0) {
			//$data['avg_buy_skus'] = $rs['buy_skus']/$rs['finish_orders'];
			//$data['avg_buy_products'] = $rs['buy_products']/$rs['finish_orders'];
		}
		if ($rs ['finish_members'] > 0) {
			$data ['finish_per_amount'] = $rs ['finish_total_amount'] / $rs ['finish_members'];
		}

		//总数
		$sql = "SELECT
                    count(*) as total_orders,
                    sum(total_amount) as total_amount,
                    count(distinct member_id) as total_members
                FROM sdb_ecorder_orders
                WHERE
                    (createtime BETWEEN $start_time AND $end_time)
                    AND shop_id='$shop_id'
                ";
		$rs = $oAnalysisDay->db->selectRow ( $sql );
		if($rs) $data = array_merge($data,$rs);
		if ($rs ['total_members'] > 0) {
			$data ['total_per_amount'] = $rs ['total_amount'] / $rs ['total_members'];
		}

		//付款数
		$sql = "SELECT
                    count(*) as paid_orders,
                    sum(total_amount) as paid_amount,
                    count(distinct member_id) as paid_members
                FROM sdb_ecorder_orders
                WHERE
                    (createtime BETWEEN $start_time AND $end_time)
                    AND shop_id='$shop_id'
                    AND pay_status='1'
                ";
		$rs = $oAnalysisDay->db->selectRow ( $sql );
		if($rs) $data = array_merge($data,$rs);

		//退款数(仅限全额退款)
		$sql = "SELECT
                    count(distinct order_id) as refund_orders,
                    sum(total_amount) as refund_amount
                FROM sdb_ecorder_orders
                WHERE
                    (createtime BETWEEN $start_time AND $end_time)
                    AND shop_id='$shop_id'
                    AND pay_status='5'
                ";
		$rs = $oAnalysisDay->db->selectRow ( $sql );
		if($rs) $data = array_merge($data,$rs);

		//失败的订单
		$sql = "SELECT
                    count(*) as failed_orders,
                    sum(total_amount) as failed_amount,
                    count(distinct member_id) as failed_members
                FROM sdb_ecorder_orders
                WHERE
                    (createtime BETWEEN $start_time AND $end_time)
                    AND shop_id='$shop_id'
                    AND status='dead'
                ";
		$rs = $oAnalysisDay->db->selectRow ( $sql );
		if($rs) $data = array_merge($data,$rs);

		$data ['channel_id'] = $this->_shopInfo ['channel_id'];
		$data ['update_time'] = time ();

		$q = $oAnalysisDay->save ( $data );
		return $q;
	}

	public function addMemberAnalysis($shopId, $memberId, $analysisData) {
		$db = kernel::database ();
		$memberInfo = $db->selectrow ( 'select id from sdb_taocrm_member_analysis where member_id=' . $memberId . ' and shop_id="' . $shopId . '"' );
		if ($memberInfo) {
			$filter = array ('id' => $memberInfo ['id'] );
			app::get ( 'taocrm' )->model ( 'member_analysis' )->update ( $analysisData, $filter );
			return $memberInfo ['id'];
		} else {
			return false;
		}
	}

    //保存客户扩展属性，暂时只有生日
    public function save_member_ext($ext_info)
    {
        if(!$ext_info['member_id']) return false;

        $save_arr = array();
        $save_arr['member_id'] = $ext_info['member_id'];
        $save_arr['update_time'] = time();
        if($ext_info['b_year'])
            $save_arr['b_year'] = intval($ext_info['b_year']);
        if($ext_info['b_month'])
            $save_arr['b_month'] = intval($ext_info['b_month']);
        if($ext_info['b_day'])
            $save_arr['b_day'] = intval($ext_info['b_day']);

        $model = app::get('taocrm')->model('member_ext');
        $rs = $model->dump(array('member_id'=>$ext_info['member_id']));
        if($rs){
            $model->update($save_arr, array('ext_id'=>$rs['ext_id']));
            $save_arr['ext_id'] = $rs['ext_id'];
        }else{
            $save_arr['create_time'] = time();
            $model->insert($save_arr);
        }
        return $save_arr['ext_id'];
    }

    public function get_member_recommend($member_id)
    {
        if(!$member_id) return false;

        $model = app::get('taocrm')->model('members_recommend');
        $rs = $model->dump(array('member_id'=>$member_id));
        if($rs){
            if($rs['parent_code']){
                $rs_parent = $model->dump(array('self_code'=>$rs['parent_code']));
                $rs['parent_mobile'] = $rs_parent['mobile'];
            }
            return $rs;
        }else{
            return false;
        }
    }

    public function get_member_ext($member_id)
    {
        if(!$member_id) return false;

        $model = app::get('taocrm')->model('member_ext');
        $rs = $model->dump(array('member_id'=>$member_id));
        if($rs){
            return $rs;
        }else{
            return false;
        }
    }
    //计算积分倍数
    public function get_points_ratio($rs_point_rules,$point_log,$member,$id)
    {
        $times_arr = array(1);
        //$current_time = date('Y-m-d',time());
        $sql = "select pay_time from sdb_ecorder_orders where order_id=".$id;
        $orders = app::get('ecorder')->model('orders');
        $order_data = $orders->db->selectRow($sql);
        $current_time = $order_data['pay_time'];
        foreach ($rs_point_rules as $v ) {
            if($v ['cost_amount']){//'每个积分消费'值大于等于1时，为通用积分规则
                continue;
            }
            $point_times=array(
                '1' => '1.5',
                '2' => '2',
                '3' => '3',
                '4' => '5'
            );
            if($v['special_point_rule'] == '1'){
                if(strtotime($current_time) >= strtotime($v ['time_from']) && strtotime($current_time) <= strtotime($v ['time_to'])){
                    $times_arr[] = $point_times[$v['activity_point_times']];
                }
            }elseif($v['special_point_rule'] == '2'){
                if($v['birthday_type'] == '1' && $member['b_month'] && $member['b_day']){//当天
                    $current_day = date('m-d',time());
                    $b_month = strlen($member['b_month']) == 2 ? $member['b_month'] : '0'.$member['b_month'];
                    $b_day = strlen($member['b_day']) == 2 ? $member['b_day'] : '0'.$member['b_day'];
                    $birthday_day = $b_month.'-'.$b_day;
                    if($current_day == $birthday_day){
                        $times_arr[] = $point_times[$v['birthday_point_times']];
                    }
                }elseif($v['birthday_type'] == '2' && $member['b_month']){//当月
                    $current_month = date('m',time());
                    $birthday_month = strlen($member['b_month']) == 2 ? $member['b_month'] : '0'.$member['b_month'];
                    if($current_month == $birthday_month){
                        $times_arr[] = $point_times[$v['birthday_point_times']];
                    }
                }
            }
        }

        $re_times = 1;
        if($point_log[0]['set_type'] == 'exclude'){
            $re_times = max($times_arr);
        }elseif($point_log[0]['set_type'] == 'include'){
            foreach($times_arr as $n=>$m){
                if($m){
                    $re_times = $re_times * $m;
                }
            }
        }else{
            $re_times = max($times_arr);
        }
        return $re_times;
    }
}
