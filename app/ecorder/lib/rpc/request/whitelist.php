<?php
/**
 * RPC同步白名单
 * 所有发起请求的最后都需要经过白名单，拒绝或许可
 * @author ome
 * @access public
 * @copyright www.shopex.cn 2010
 *
 */
class ecorder_rpc_request_whitelist {
    
    /**
     * EC-STORE RPC服务接口名列表
     * @access private
     */
    private $ecosb2c = array(
        'store.trade.status.update',
        'store.trade.ship_status.update',
        'store.trade.pay_status.update',
        'store.trade.memo.add',
        'store.trade.memo.update',
        'store.trade.shippingaddress.update',
        'store.trade.payment.add',
        'store.trade.payment.status.update',
        'store.trade.refund.add',
        'store.trade.refund.status.update',
        'store.trade.reship.add',
        'store.trade.reship.status.update',
        'store.trade.shipping.add',
           'store.trade.shipping.update',
        'store.trade.shipping.status.update',
        'store.items.quantity.list.update',
        'store.trade.item.freezstore.update',
        'store.trade.aftersale.status.update',
        'store.trade.aftersale.add',
        'store.trade.buyer_message.add',
    );
    
    /**
     * SHOPEX485 RPC服务接口名列表
     * @access private
     */
    private $shopexb2c = array(
        'store.trade.update',
        'store.trade.shippingaddress.update',
        'store.trade.memo.add',
        'store.trade.memo.update',
        'store.trade.buyer_message.add',
        'store.trade.status.update',
        'store.trade.ship_status.update',
        'store.trade.shipping.add',
        'store.trade.shipping.update',
        'store.trade.shipping.status.update',
        'store.trade.reship.add',
        'store.trade.refund.add',
        'store.trade.payment.add',
        'store.items.quantity.list.update',
    );
    
    /**
     * 淘宝 RPC服务接口名列表
     * @access private
     */
    private $taobao = array(
        'store.items.quantity.list.update',
        'store.trade.delivery.send',
    );
    
    /**
     * 拍拍 RPC服务接口名列表
     * @access private
     */
    private $paipai = array(
        'store.items.quantity.list.update',
        'store.trade.delivery.send',
    );
    
    /**
     * SHOPEX B2B RPC服务接口名列表
     * @access private
     */
    private $shopexb2b = array();
    
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