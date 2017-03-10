<?php
/**
 * RPC同步白名单
 * 所有发起请求的最后都需要经过白名单，拒绝或许可
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class market_rpc_request_whitelist {
    
    /**
     * EC-STORE RPC服务接口名列表
     * @access private
     */
    private $ecosb2c = array(
    );
    
    /**
     * SHOPEX485 RPC服务接口名列表
     * @access private
     */
    private $shopexb2c = array(
    );
    
    /**
     * 淘宝 RPC服务接口名列表
     * @access private
     */
    private $taobao = array(
        'store.promotion.coupon.add',
        'store.promotion.coupon.send',
        'store.promotion.coupondetail.get',
    );
    
    /**
     * 拍拍 RPC服务接口名列表
     * @access private
     */
    private $paipai = array(
    );
    
    /**
     * SHOPEX B2B RPC服务接口名列表
     * @access private
     */
    private $shopexb2b = array(
    );
    
    /**
     * ECSHOP RPC服务接口名列表
     * @access private
     */
    private $ecshop = array();
    
    /**
     * 有啊 RPC服务接口名列表
     * @access private
     */
    private $youa = array();
    
    function __construct(){
        $this->whitelist = array(
            'shopex_b2c' => $this->shopexb2c,
            'shopex_b2b' => $this->shopexb2b,
            'ecos.b2c' => $this->ecosb2c,
            'taobao' => $this->taobao,
            'ecshop_b2c' => $this->ecshop,
            'youa' => $this->youa,
            'paipai' => $this->paipai,
        );
    }
    
    /**
     * RPC白名单过滤
     * @access public
     * @param string $node_type 节点类型
     * @param 远程服务接口名称
     * @return boolean 允许或拒绝
     */
    public function check_node($node_type,$method){
        if(isset($this->whitelist[$node_type]) && in_array($method,$this->whitelist[$node_type])){
            return true;
        }else{
            return false;
        }
    }
}