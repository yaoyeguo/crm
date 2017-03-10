<?php

class ecorder_mdl_fx_orders extends dbeav_model {

    var $has_many = array(
        //'delivery' => 'delivery', TODO:非标准写法，去掉后有报错需要修改代码
        'order_objects' => 'fx_order_objects',
    );

    /* create_order 订单创建
     * @param sdf $sdf
     * @return sdf
     */

    function create_order(&$sdf) {

        //本地化地区转换
        $area = $sdf['consignee']['area'];
        kernel::single("ecorder_func")->region_validate($area);
        $sdf['consignee']['area'] = $area;

        $this->save($sdf);
    }

    function save(&$data, $mustUpdate = null)
    {
        //外键 先执行save
        $this->_save_parent($data, $mustUpdate);
        $plainData = $this->sdf_to_plain($data);
        if (!$this->db_save($plainData, $mustUpdate))
            return false;

        $order_id = $plainData['order_id'];
        $shop_id = $plainData['shop_id'];
        if (isset($data['order_objects'])) {
            foreach ($data['order_objects'] as $k => $v) {
                if (isset($v['order_items'])) {
                    foreach ($v['order_items'] as $k2 => $item) {
                        if($data['member_id'])
                            $data['order_objects'][$k]['order_items'][$k2]['member_id'] = $data['member_id'];
                        $data['order_objects'][$k]['order_items'][$k2]['order_id'] = $order_id;
                        $data['order_objects'][$k]['order_items'][$k2]['shop_id'] = $shop_id;
                        $data['order_objects'][$k]['order_items'][$k2]['create_time'] = $data['createtime'];
                    }
                } else {
                    break;
                }
            }
        }

        if (!is_array($this->idColumn)) {
            $data[$this->idColumn] = $plainData[$this->idColumn];
            $this->_save_depends($data, $mustUpdate);
        }
        $plainData = null; //内存用完就放
        return true;
    }
    
    /**
     * 返回订单字段的对照表
     * @params string 状态
     * @params string key value
     */
    public function trasform_status($type='status', $val)
    {
        switch($type){
            case 'status':
                $tmpArr = array(
                            'active' => app::get('b2c')->_('活动'),
                            'finish' => app::get('b2c')->_('完成'),
                            'dead' => app::get('b2c')->_('死单'),
                );
                return $tmpArr[$val];
            break;
            case 'pay_status':
                $tmpArr = array(
                            0 => app::get('b2c')->_('未付款'),
                            1 => app::get('b2c')->_('已付款'),
                            2 => app::get('b2c')->_('付款至担保方'),
                            3 => app::get('b2c')->_('部分付款'),
                            4 => app::get('b2c')->_('部分退款'),
                            5 => app::get('b2c')->_('已退款'),
                );
                return $tmpArr[$val];
            break;
            case 'ship_status':
                $tmpArr = array(
                            0 => app::get('b2c')->_('未发货'),
                            1 => app::get('b2c')->_('已发货'),
                            2 => app::get('b2c')->_('部分发货'),
                            3 => app::get('b2c')->_('部分退货'),
                            4 => app::get('b2c')->_('已退货'),
                );
                return $tmpArr[$val];
            break;
        }
    }
	
    /*public function getMembersPaidAmount()
    {
    	$sql = "SELECT SUM(payed) AS paid_amount, COUNT(payed) AS order_succ_num, member_id, shop_id FROM sdb_ecorder_orders WHERE pay_status = '1' AND status = 'finish' GROUP BY member_id";
    	return $this->db->select($sql);
    }*/
}


