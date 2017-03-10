<?php

/**
 * 订单接收模块抽像类
 *
 * @author hzjsq@msn.com
 * @version 0.1b
 */
abstract class ecgoods_rpc_response_goods_add_abstract {

    /**
     * 订单数据模块所在APP名
     * @var boolean
     */
    static $_APP = 'ecgoods';
    
    /**
     * 数据结构
     * @var Array
     */
    protected $_goodsSdf = array();
    
    /**
     * RPC对像
     * @var object
     */
    protected $_response = null;
    
    /**
     * 对应店铺信息
     * @var Array
     */
    protected $_shopInfo = array();
    
    /**
     * 返回值
     * @var array
     */
    protected $_result = array();
    
}