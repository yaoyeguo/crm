<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class taocrm_mdl_member_bind_queue extends dbeav_model{

    var $defaultOrder = array('create_time', ' DESC');

    function modifier_bind_dimensions($row){
        $dimensionsList = array('mobile'=>'手机','email'=>'邮箱','qq'=>'QQ号','weixin'=>'微信号','weibo'=>'新浪微博','addr'=>'收货地址','alipay_no'=>'支付宝账号');
        $str = '';
        foreach(explode(',', $row) as $key){
            $str .= $dimensionsList[$key].'&nbsp&nbsp';
        }

        return $str;
    }
    
    function modifier_finish_time($row)
    {
        if( ! $row){
            $str = '-';
        }else{
            $str = date('Y-m-d H:i:s', $row);
        }
        return $str;
    }

    function check($bindDimensions)
    {
        //$sql = 'select * from sdb_taocrm_member_bind_queue where bind_dimensions="'.$bindDimensions.'" and is_send in("unsend","sending")';
        $sql = 'select * from sdb_taocrm_member_bind_queue where bind_dimensions="'.$bindDimensions.'" and is_send in("sending")';
        $row = $this->db->selectRow($sql);
        if(!$row){
            return true;
        }else{
            return false;
        }
    }

}

