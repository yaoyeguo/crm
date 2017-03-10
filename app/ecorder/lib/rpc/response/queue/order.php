<?php

/**
 * 前端店铺订单数据业务处理
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */

class ecorder_rpc_response_queue_order extends ecorder_rpc_response {

    /**
     * 订单创建
     * @access public
     * @param Array $sdf 订单标准结构的数据
     * @param Object $responseObj 框架API接口实例化对象
     * @return array('order_id'=>'订单主键ID')
     */
    public function add($sdf, &$responseObj) {
		
		
        $order_sdf = $sdf;
        $orderObj = &app::get('ecorder')->model('orders');
        $membersObj = &app::get('ecorder')->model('members');
        $smemberObj = &app::get('ecorder')->model('shop_members');
        $oApi_log = &app::get('ecorder')->model('api_log');

        //前端店铺信息
        $shop_info = $this->get_shop("shop_id,shop_type,name", $responseObj);

        $shop_id = $shop_info['shop_id'];
        $shop_name = $shop_info['name'];
        $shop_type = $shop_info['shop_type'];
        //参数初始化
        $payment_detail = $order_sdf['payment_detail'];
        $pmt_detail = $order_sdf['pmt_detail'];
        unset($order_sdf['payment_detail']);
        unset($order_sdf['order_source']);
        unset($order_sdf['pmt_detail']);
        //日志标题
        $log_title = "接收店铺({$shop_name})的订单:" . $order_sdf['order_bn'];

        //返回值
        $return_value = array('tid' => $order_sdf['order_bn']);

        //判断order_bn是否为空
        if (empty($order_sdf['order_bn'])) {
            $msg = 'Order number can not be empty';
            $log_id = $oApi_log->gen_id();
            $oApi_log->write_log($log_id, $log_title, __CLASS__, __FUNCTION__, '', '', 'response', 'fail', $msg);
            $responseObj->send_user_error(app::get('base')->_($msg), $return_value);
        }

        //获取订单信息
        $order_detail = $orderObj->dump(array('order_bn' => $order_sdf['order_bn'], 'shop_id' => $shop_id), 'order_id,order_bn,mark_text');

        //买家留言
        $custom_memo = $order_sdf['custom_mark'];
        if ($custom_memo) {
            $custommemo[] = array('op_name' => $shop_type, 'op_time' => date("Y-m-d H:i:s", time()), 'op_content' => htmlspecialchars($custom_memo));
            $order_sdf['custom_mark'] = serialize($custommemo);
        }
        //判断该shop的同bn订单是否已经存在

		 //订单备注
		$mark_memo = $order_sdf['mark_text'];
		if ($mark_memo) {
			$markmemo[] = array('op_name' => $shop_type, 'op_time' => date("Y-m-d H:i:s", time()), 'op_content' => htmlspecialchars($mark_memo));
			$order_sdf['mark_text'] = serialize($markmemo);
		}


        $order_sdf['status'] = $order_sdf['status'];
        $order_sdf['pay_status'] = $order_sdf['pay_status'];
        $order_sdf['ship_status'] = $order_sdf['ship_status'];
        $order_sdf['payed'] = $order_sdf['payed'] ? $order_sdf['payed'] : 0.00;

        //订单状态业务逻辑过滤
        switch ($shop_type) {
            case 'paipai':
            case 'youa':
            case 'taobao':
                //拒绝未支付订单
                //if ($order_sdf['pay_status'] == '0') {
                //    return true;
                //}
                //淘宝订单优惠、折扣和商品优惠金额转为正数

                $order_sdf['pmt_goods'] = abs($order_sdf['pmt_goods']);
                $order_sdf['pmt_order'] = abs($order_sdf['pmt_order']);
                break;
            default:
            //
        }

        //参数转换
        $order_sdf['shipping'] = json_decode($order_sdf['shipping'], true);
        $order_sdf['shipping']['cost_shipping'] = $order_sdf['shipping']['cost_shipping'] ? $order_sdf['shipping']['cost_shipping'] : 0.00;
        $order_sdf['shipping']['is_protect'] = $order_sdf['shipping']['is_protect'] ? $order_sdf['shipping']['is_protect'] : 'false';
        $order_sdf['shipping']['cost_protect'] = $order_sdf['shipping']['cost_protect'] ? $order_sdf['shipping']['cost_protect'] : 0.00;
        $order_sdf['shipping']['is_cod'] = $order_sdf['shipping']['is_cod'] ? $order_sdf['shipping']['is_cod'] : 'false';

        $order_sdf['shop_id'] = $shop_id;
        $order_sdf['shop_type'] = $shop_type;
        $order_sdf['is_delivery'] = $order_sdf['is_delivery'] ? $order_sdf['is_delivery'] : 'Y';

        $order_sdf['cost_item'] = $order_sdf['cost_item'] ? $order_sdf['cost_item'] : 0.00;
        $order_sdf['is_tax'] = $order_sdf['is_tax'] ? $order_sdf['is_tax'] : 'false';
        $order_sdf['cost_tax'] = $order_sdf['cost_tax'] ? $order_sdf['cost_tax'] : 0.00;
        $order_sdf['discount'] = $order_sdf['discount'] ? $order_sdf['discount'] : 0.00;
        $order_sdf['total_amount'] = $order_sdf['total_amount'] ? $order_sdf['total_amount'] : 0.00;

        $order_sdf['source'] = 'matrix';
        $order_sdf['createtime'] = kernel::single('ecorder_func')->date2time($order_sdf['createtime']);
        $order_sdf['consignee'] = json_decode($order_sdf['consignee'], true);
        $order_sdf['consignee']['area'] = $order_sdf['consignee']['area_state'] . '/' . $order_sdf['consignee']['area_city'] . '/' . $order_sdf['consignee']['area_district'];
        $order_sdf['payinfo'] = json_decode($order_sdf['payinfo'], true);
        $objects = json_decode($order_sdf['order_objects'], true);
        $order_sdf['order_objects'] = $objects;

        //订单货品明细业务规则判断
		if ($order_detail['order_bn']) {
			$product_status = false;
		} else {
			$product_status = true;
		}

        if ($objects && $product_status)
            foreach ($objects as $key => $object) {

                $order_sdf['order_objects'][$key]['obj_type'] = $object['obj_type'] ? $object['obj_type'] : 'goods';
                $order_sdf['order_objects'][$key]['shop_goods_id'] = $object['shop_goods_id'] ? $object['shop_goods_id'] : 0;
                $order_sdf['order_objects'][$key]['price'] = $object['price'] ? $object['price'] : 0.00;
                $order_sdf['order_objects'][$key]['weight'] = $object['weight'] ? $object['weight'] : 0.00;
                $order_sdf['order_objects'][$key]['amount'] = $object['amount'] ? $object['amount'] : 0.00;
                $order_sdf['order_objects'][$key]['quantity'] = $object['amount'] ? $object['quantity'] : 0;

                $items = $object['order_items'];
                foreach ($items as $k => $item) {
                    //商品状态
                    $item_status = 'false';
                    if ($item['status'] == 'close') {
                        $item_status = 'true';
                    }
                    //货号规格属性
                    if ($item['product_attr']) {
                        $product_attr['product_attr'] = $item['product_attr'];
                        $order_sdf['order_objects'][$key]['order_items'][$k]['addon'] = serialize($product_attr);
                    }
                    $order_sdf['order_objects'][$key]['order_items'][$k]['delete'] = $item_status;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['product_id'] = $product_info['product_id'] ? $product_info['product_id'] : 0;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['shop_goods_id'] = $item['shop_goods_id'] ? $item['shop_goods_id'] : 0;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['shop_product_id'] = $item['shop_product_id'] ? $item['shop_product_id'] : 0;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['price'] = $item['price'] ? $item['price'] : 0.00;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['amount'] = $item['amount'] ? $item['amount'] : 0.00;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['quantity'] = $item['quantity'] ? $item['quantity'] : 1;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['sendnum'] = $item['sendnum'] ? $item['sendnum'] : 0;
                    $order_sdf['order_objects'][$key]['order_items'][$k]['item_type'] = trim($item['item_type']) ? $item['item_type'] : 'product';
                }
            }



        //设置订单失败时间
        $order_sdf['order_limit_time'] = time() + 60 * (app::get('ome')->getConf('ome.order.failtime'));

        //订单创建

        if ($product_status == true) {

			//登记前端店铺客户信息
			if ($order_sdf['member_info']) {

				$member_id = $this->_save_member($order_sdf['member_info'], $shop_id);

				if ($member_id) {
					$order_sdf['member_id'] = $member_id;
				}
			} else {
				unset($order_sdf['member_id']);
			}
            $orderObj->create_order($order_sdf);
        } else {
			unset($order_sdf['shipping']);
			unset($order_sdf['member_id']);
			unset($order_sdf['order_objects']);
			unset($order_sdf['payinfo']);
			unset($order_sdf['member_info']);
			unset($order_sdf['consignee']);
			unset($order_sdf['memeber_id']);
			$orderObj->update($order_sdf, array('order_id' => $order_detail['order_id']));
		}
       //获取订单信息
       $order_info = $orderObj->dump(array('order_bn' => $order_sdf['order_bn']), 'order_id,createtime');
       /*$queueObj = kernel::single('taobukpi_mdl_order_queue');//对列表
       $order_queue = $queueObj->dump(array('order_id'=>$order_info['order_id']));
       //数据存队列
       if(empty($order_queue)){
		    $data = array(
                           'order_id'=>$order_info['order_id'],
                           'create_time'=>$order_info['createtime'],
                          );
       	  $queueObj->insert($data);
       	}*/

        //更新店铺下载订单时间
        $shopObj = &app::get('ecorder')->model('shop');
        $shopdata = array('shop_id' => $shop_id, 'last_download_time' => time());
        $shopObj->save($shopdata);

        //生成支付单
        switch ($shop_type) {
            case 'youa'://有啊
            case 'taobao'://淘宝
            case 'paipai'://拍拍
                if ($tmp_pay_status == 1) {
                    $payment_detail = json_decode($payment_detail, true);
                    $payment_detail['pay_time'] = kernel::single('ecorder_func')->date2time($payment_detail['pay_time']);
                    $payment_sdf = array(
                        'status' => 'succ',
                        'money' => $tmp_payed,
                        'order_bn' => $order_sdf['order_bn'],
                        'pay_account' => $payment_detail['pay_account'],
                        'paymethod' => $payment_detail['paymethod'],
                        'trade_no' => $payment_detail['trade_no'],
                        't_begin' => $payment_detail['pay_time'],
                        'source_shop' => $shop_type,
                    );
                    if ($product_status == true) {
                        kernel::single("ecorder_rpc_response_payment")->add($payment_sdf, $responseObj);
                    } else {
                        $apilogData['taobao_payment_sdf'] = $payment_sdf;
                    }
                }
                break;
        }
        //优惠方案
        $pmt_detail = json_decode($pmt_detail, true);
        if ($pmt_detail && $product_status == true) {
            $pmtObj = &app::get('ecorder')->model('order_pmt');
            foreach ($pmt_detail as $k => $v) {
                if (!$v['pmt_amount'])
                    continue;
                $pmt_sdf = array(
                    'order_id' => $order_sdf['order_id'],
                    'pmt_amount' => $v['pmt_amount'],
                    'pmt_describe' => $v['pmt_describe'],
                );
                $pmtObj->save($pmt_sdf);
            }
        }else {
            $apilogData['pmt_detail'] = $pmt_detail;
        }

		//数据返回
        $result = array('tid' => $order_sdf['order_bn']);
        return $result;
    }

    /**
     * 客户添加与更新
     * @access private
     * @param array $member_info 客户信息
     * @param string $shop_id 店铺ID
     * @return int 客户ID
     */
    private function _save_member($member_info, $shop_id) {
        if (empty($member_info))
            return null;
        $membersObj = &app::get('ecorder')->model('members');
        $smemberObj = &app::get('ecorder')->model('shop_members');

        $member_info = json_decode($member_info, true);
        unset($order_sdf['member_info']);
        if (empty($member_info['name']))
            $member_info['name'] = $member_info['uname'];
        $order_sdf['member_id'] = '';
        $member_detail = array();
        $member_id = null;
        if ($member_info['uname']) {
            //判断是否存在该客户
            $member_detail = $smemberObj->dump(array('shop_member_id' => $member_info['uname'], 'shop_id' => $shop_id), 'member_id');
            $area = $member_info['area_state'] . '/' . $member_info['area_city'] . '/' . $member_info['area_district'];
            kernel::single("ecorder_func")->region_validate($area);
            $area = str_replace('::', '', $area);
            $members_data = array(
                //'account' => array(
                    'uname' => $member_info['uname'],
                //),
                //'contact' => array(
                    'name' => $member_info['name'],
                    'area' => $area,
                    'addr' => $member_info['addr'],
                  //  'phone' => array(
                        'mobile' => $member_info['mobile'],
                        'tel' => $member_info['tel'],
                   // ),
                    'email' => $member_info['email'],
                    'zip' => $member_info['zip']
                //),
            );
            if (empty($member_detail['member_id'])) {
                //增加客户

                $membersObj->save($members_data);
                $shop_members_data = array(
                    'shop_id' => $shop_id,
                    'shop_member_id' => $member_info['uname'],
                    'member_id' => $members_data['member_id'],
                );
                //--以下代码是解决并发量大的情况 - 开始
                if (!@$smemberObj->insert($shop_members_data)) {
                    //将插入关系表失败的客户信息删除
                    $membersObj->delete(array('member_id' => $members_data['member_id']));
                    //从关系表中查询客户ID
                    $member_detail = $smemberObj->dump(array('shop_member_id' => $member_info['uname'], 'shop_id' => $shop_id), 'member_id');
                    $members_data['member_id'] = $member_detail['member_id'];
                }
                //--并发解决 - 结束
                $member_detail['member_id'] = $members_data['member_id'];
            } else {
                //更新客户
                $membersObj->update($members_data, array('member_id' => $member_detail['member_id']));
            }
            $member_id = $member_detail['member_id'];
        }
        return $member_id;
    }

    /**
     * 更新订单状态
     * @access public
     * @param Array $order_sdf 待更新订单状态标准结构数据
     * @param Object $responseObj 框架API接口实例化对象
     */
    public function status_update($order_sdf, &$responseObj) {

        $shop_id = $this->get_shop_id($responseObj);
        $orderObj = &app::get('ecorder')->model('orders');

        $status = $order_sdf['status'];
        $order_bn = $order_sdf['order_bn'];

        //返回值
        $return_value = array('tid' => $order_bn);

        if ($status == '') {
            $responseObj->send_user_error(app::get('base')->_('Order status ' . $status . ' is not exists'), $return_value);
        }
        $order_info = kernel::database()->selectrow("SELECT order_id,op_id FROM sdb_ecorder_orders WHERE order_bn='" . $order_bn . "' AND shop_id='" . $shop_id . "'");

        if (!empty($order_info)) {

            $order_id = $order_info['order_id'];

            if (!$order_info['op_id']) {
                $user_info = kernel::database()->selectrow("SELECT user_id FROM sdb_desktop_users WHERE super='1' ORDER BY user_id asc ");
                $op_id = $user_info['user_id'];
                $op_idsql = ",op_id='" . $op_id . "'";
            }
            kernel::database()->exec("UPDATE sdb_ecorder_orders SET status='$status'$op_idsql WHERE order_id='$order_id'");
            if ($status == 'dead') {//订单取消
                $orderObj->cancel($order_id, "订单被取消");
            }
            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('Order Order_bn ' . $order_bn . ' is not exists'), $return_value);
        }
    }

    /**
     * 更新订单支付状态
     * @access public
     * @param Array $order_sdf  待更新订单支付状态标准结构数据
     * @param Object $responseObj  框架API接口实例化对象
     */
    public function pay_status_update($order_sdf, &$responseObj) {
        $shop_id = $this->get_shop_id($responseObj);

        $status = $order_sdf['pay_status'];
        $order_bn = $order_sdf['order_bn'];

        //返回值
        $return_value = array('tid' => $order_bn);

        if ($status == '') {
            $responseObj->send_user_error(app::get('base')->_('Order status ' . $status . ' is not exists'), $return_value);
        }
        $order_info = kernel::database()->selectrow("SELECT order_id FROM sdb_ecorder_orders WHERE order_bn='" . $order_bn . "' AND shop_id='" . $shop_id . "'");

        if (!empty($order_info)) {

            $order_id = $order_info['order_id'];
            kernel::database()->exec("UPDATE sdb_ecorder_orders SET pay_status='$status' WHERE order_id='$order_id'");

            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('Order_bn: ' . $order_bn . ' is not exists'), $return_value);
        }
    }

    /**
     * 更新订单发货状态
     * @access public
     * @param Array $order_sdf 待更新订单发货状态标准结构数据
     * @param Object $responseObj  框架API接口实例化对象
     */
    public function ship_status_update($order_sdf, &$responseObj) {
        $shop_id = $this->get_shop_id($responseObj);
        $status = $order_sdf['ship_status'];
        $order_bn = $order_sdf['order_bn'];

//返回值
        $return_value = array('tid' => $order_bn);

        if ($status == '') {
            $responseObj->send_user_error(app::get('base')->_('Order status ' . $status . ' is not exists'), $return_value);
        }
        $order_info = kernel::database()->selectrow("SELECT order_id FROM sdb_ecorder_orders WHERE order_bn='" . $order_bn . "' AND shop_id='" . $shop_id . "'");

        if (!empty($order_info)) {

            $order_id = $order_info['order_id'];
            kernel::database()->exec("UPDATE sdb_ecorder_orders SET ship_status='$status' WHERE order_id='$order_id'");

            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('Order_bn: ' . $order_bn . ' is not exists'), $return_value);
        }
    }

    /**
     * 添加买家留言
     * @access public
     * @param Array $order_sdf 买家留言标准结构数据
     * @param Object $responseObj 框架API接口实例化对象
     */
    public function custom_mark_add($order_sdf, &$responseObj) {

        $shop_id = $this->get_shop_id($responseObj);

        $order_bn = $order_sdf['tid'];
        $op_content = $order_sdf['message'];
        $op_name = $order_sdf['sender'];
        $op_time = kernel::single('ecorder_func')->date2time($order_sdf['add_time']);
        $order_info = kernel::database()->selectrow("SELECT order_id,custom_mark FROM sdb_ecorder_orders WHERE order_bn='" . $order_bn . "' AND shop_id='" . $shop_id . "'");

//返回值
        $return_value = array('tid' => $order_bn);

        if (!empty($order_info)) {
            $order_id = $order_info['order_id'];
            $orderObj = &app::get('ecorder')->model('orders');

            //取出买家留言信息
            $oldmemo = unserialize($order_info['custom_mark']);
            if ($oldmemo)
                foreach ($oldmemo as $k => $v) {
                    $custom_memo[] = $v;
                }
            $newmemo = htmlspecialchars($op_content);
            $newmemo = array('op_name' => $op_name, 'op_time' => $op_time, 'op_content' => $newmemo);
            $custom_memo[] = $newmemo;
            $order_memo['custom_mark'] = serialize($custom_memo);
            $filter = array('order_id' => $order_id);
            $orderObj->update($order_memo, $filter);

            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('Order: ' . $order_bn . ' is not exists'), $return_value);
        }
    }

    /**
     * 更新买家留言
     * @access public
     * @param Array $order_sdf 买家留言标准结构数据
     * @param Object $responseObj 框架API接口实例化对象
     */
    public function custom_mark_update($order_sdf, &$responseObj) {

        $shop_id = $this->get_shop_id($responseObj);

        $order_bn = $order_sdf['tid'];
        $op_content = $order_sdf['message'];
        $op_name = $order_sdf['sender'];
        $op_time = kernel::single('ecorder_func')->date2time($order_sdf['add_time']);
        $order_info = kernel::database()->selectrow("SELECT order_id,custom_mark FROM sdb_ecorder_orders WHERE order_bn='" . $order_bn . "' AND shop_id='" . $shop_id . "'");

//返回值
        $return_value = array('tid' => $order_bn);

        if (!empty($order_info)) {
            $order_id = $order_info['order_id'];
            $orderObj = &app::get('ecorder')->model('orders');

            //取出买家留言信息
            $oldmemo = unserialize($order_info['custom_mark']);
            if ($oldmemo)
                foreach ($oldmemo as $k => $v) {
                    $custom_memo[] = $v;
                }
            $newmemo = htmlspecialchars($op_content);
            $newmemo = array('op_name' => $op_name, 'op_time' => $op_time, 'op_content' => $newmemo);
            $custom_memo[] = $newmemo;
            $order_memo['custom_mark'] = serialize($custom_memo);
            $filter = array('order_id' => $order_id);
            $orderObj->update($order_memo, $filter);

            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('Order: ' . $order_bn . ' is not exists'), $return_value);
        }
    }

    /**
     * 添加订单备注
     * @access public
     * @param Array $order_sdf 订单备注标准结构数据
     * @param Object $responseObj 框架API接口实例化对象
     */
    public function memo_add($order_sdf, &$responseObj) {
        $shop_id = $this->get_shop_id($responseObj);

        $order_bn = $order_sdf['tid'];
        $op_content = $order_sdf['memo'];
        $op_name = $order_sdf['sender'];
        $op_time = kernel::single('ecorder_func')->date2time($order_sdf['add_time']);
        $order_info = kernel::database()->selectrow("SELECT order_id,mark_text FROM sdb_ecorder_orders WHERE order_bn='" . $order_bn . "' AND shop_id='" . $shop_id . "'");

//返回值
        $return_value = array('tid' => $order_bn);

        if (!empty($order_info)) {
            $order_id = $order_info['order_id'];
            $orderObj = &app::get('ecorder')->model('orders');

            //取出订单备注信息
            $oldmemo = unserialize($order_info['mark_text']);
            if ($oldmemo)
                foreach ($oldmemo as $k => $v) {
                    $custom_memo[] = $v;
                }
            $newmemo = htmlspecialchars($op_content);
            $newmemo = array('op_name' => $op_name, 'op_time' => $op_time, 'op_content' => $newmemo);
            $custom_memo[] = $newmemo;
            $order_memo['mark_text'] = serialize($custom_memo);
            $order_memo['mark_type'] = $order_sdf['flag'];
            $filter = array('order_id' => $order_id);
            $orderObj->update($order_memo, $filter);

            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('Order: ' . $order_bn . ' is not exists'), $return_value);
        }
    }

    /**
     * 更新订单备注
     * @access public
     * @param Array $order_sdf 订单备注注标准结构数据
     * @param Object $responseObj 框架API接口实例化对象
     */
    public function memo_update($order_sdf, &$responseObj) {

        $shop_id = $this->get_shop_id($responseObj);

        $order_bn = $order_sdf['tid'];
        $op_content = $order_sdf['memo'];
        $op_name = $order_sdf['sender'];
        $op_time = kernel::single('ecorder_func')->date2time($order_sdf['add_time']);
        $order_info = kernel::database()->selectrow("SELECT order_id,mark_text FROM sdb_ecorder_orders WHERE order_bn='" . $order_bn . "' AND shop_id='" . $shop_id . "'");

        //返回值
        $return_value = array('tid' => $order_bn);

        if (!empty($order_info)) {
            $order_id = $order_info['order_id'];
            $orderObj = &app::get('ecorder')->model('orders');

            //取出订单备注信息
            $oldmemo = unserialize($order_info['mark_text']);
            if ($oldmemo)
                foreach ($oldmemo as $k => $v) {
                    $custom_memo[] = $v;
                }
            $newmemo = htmlspecialchars($op_content);
            $newmemo = array('op_name' => $op_name, 'op_time' => $op_time, 'op_content' => $newmemo);
            $custom_memo[] = $newmemo;
            $order_memo['mark_text'] = serialize($custom_memo);
            $order_memo['mark_type'] = $order_sdf['flag'];
            $filter = array('order_id' => $order_id);
            $orderObj->update($order_memo, $filter);

            return $return_value;
        } else {
            $responseObj->send_user_error(app::get('base')->_('Order: ' . $order_bn . ' is not exists'), $return_value);
        }
    }

}

?>
