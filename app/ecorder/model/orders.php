<?php

class ecorder_mdl_orders extends dbeav_model {

    var $has_many = array(
    //'delivery' => 'delivery', TODO:非标准写法，去掉后有报错需要修改代码
        'order_objects' => 'order_objects',
    );

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        $data = parent::getList($cols, $filter, $offset, $limit, $orderby);
        
        $mdl_member_analysis = app::get('taocrm')->model('member_analysis');
        $mdl_shop_lv = app::get('ecorder')->model('shop_lv');
        
        $levels = $mdl_shop_lv->get_lv_name();
               
        foreach($data as $k=>$v){
            if($v['member_id']) $mnember_ids[] = $v['member_id'];
        }
        
        if($mnember_ids){
            //获取会员等级
            $rs_member = $mdl_member_analysis->getList('lv_id,member_id,shop_id', array('member_id'=>$mnember_ids));
            if($rs_member){
                foreach($rs_member as $v){
                    $level_name[$v['shop_id'].'_'.$v['member_id']] = $levels[$v['lv_id']];
                }
            }
            
            //获取会员标签
            $oTag = app::get('taocrm')->model('member_tag');
            $tagInfo = $oTag->getMemberTagInfo($mnember_ids);

            foreach($data as $k=>$v){
                $data[$k]['level_name'] = $level_name[$v['shop_id'].'_'.$v['member_id']];
                if(isset($tagInfo[$v['member_id']]))
                    $data[$k]['tagInfo'] = implode('；', $tagInfo[$v['member_id']]);
            }
        }
        return $data;
    }
    
    function _filter($filter,$tableAlias=null,$baseWhere=null)
    {
        if(isset($filter['member_id']) && $filter['member_id']){
            if(is_array($filter['member_id'])){
                $member_ids = $filter['member_id'];
            }elseif(is_numeric($filter['member_id'])){
                $member_ids[] = $filter['member_id'];
            }else{
                $filter_uname = trim($filter['member_id']);
                $sql = "select member_id from sdb_taocrm_members where uname like '{$filter_uname}%' ";
                $rs = $this->app->model('orders')->db->select($sql);
                if($rs){
                    foreach($rs as $v){
                        $member_ids[] = $v['member_id'];
                    }
                }
            }
            
            if($member_ids){
                $baseWhere[] = ' member_id IN  ('.implode(',', $member_ids).') ';
            }else{
                $baseWhere[] = ' order_id=0 ';
            }
            
            unset($filter['member_id']);
            $base_filter = array('op_name'=>'matrix');
        }
        return parent::_filter($filter,$tableAlias,$baseWhere);
    }

    /* create_order 订单创建
     * @param sdf $sdf
     * @return sdf
     */

    function create_order(&$sdf) {

        //本地化地区转换
        $area = $sdf['consignee']['area'];
        kernel::single("ecorder_func")->region_validate($area);
        $sdf['consignee']['area'] = $area;

        //$oProducts = &$this->app->model('products');

        //如果有OME捆绑插件设定的捆绑商品，则自动拆分
        /*if ($oPkg = kernel::service('omepkg_order_split')) {
         if (method_exists($oPkg, order_split)) {
         $sdf = $oPkg->order_split($sdf);
         }
         }*/

        $this->save($sdf);
        /*
         if($orderAds = kernel::service('ecorder_service_incremental')){
         $orderAds->getOrderIncremental($sdf);
         }
         */

        //如果有OME自动确认订单插件的话，会按照自动确认规则来自动确认订单
        /* ($oAuto = kernel::service('do_autodispatch')) {
         if ($sdf['shipping']['is_cod'] == 'true' or $sdf['pay_status'] == 1) {
         if (method_exists($oAuto, autodispatch)) {
         $oAuto->autodispatch($sdf);
         }
         }
         }*/

        //如果有KPI考核插件，会增加客服的考核
        /*if ($oKpi = kernel::service('omekpi_servicer_incremental')) {
         if (method_exists($oKpi, getOrderIncremental)) {
         $oKpi->getOrderIncremental($sdf);
         }
         }*/
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

    public function getMembersPaidAmount()
    {
        $sql = "SELECT SUM(payed) AS paid_amount, COUNT(payed) AS order_succ_num, member_id, shop_id FROM sdb_ecorder_orders WHERE pay_status = '1' AND status = 'finish' GROUP BY member_id";
        return $this->db->select($sql);
    }

}


