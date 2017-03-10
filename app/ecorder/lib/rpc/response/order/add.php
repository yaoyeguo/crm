<?php
/**
 * 订单接收模块
 *
 * @author hzjsq@msn.com
 * @version 0.1b
 */

class ecorder_rpc_response_order_add {

    /**
     * 插件组
     */
    private $_plugins = array(
        'taobao', 'paipai', 'youa','360buy',
        'shopex_b2c','ecos.b2c','shopex_b2b','ecos.dzg',
        'yihaodian','fenxiao','amazon','dangdang','alibaba',
        'ecos.ome','manual_entry','offlinepos','ecshop',
        'meilishuo','mogujie','gome','suning',
        'youzan'
    );

    /**
     * 插件列表
     * @var Array
     */
    static $_plugObjects = array();

    public function __construct() {


    }

    /**
     * 接收并更新订单
     *
     * @param mixed $sdf 标准订单结构
     * @param object $response RPC对像
     * @return mixed
     */
    public function add($sdf , &$response)
    {
        $shop_info = $this->fetchShopInfo($sdf);
        if(empty($shop_info['shop_type']) && empty($sdf['shop_type'])){
            $log = app::get('ecorder')->model('api_log');
            $logTitle = '订单接口['. $sdf['order_bn'] .']';
            $logInfo = '订单接口：<BR>';
            $logInfo .= '接收参数 $sdf 信息：' . var_export($sdf, true) . '<BR>';
            $err_msg = 'shop_type is empty!';
            $logInfo .= '返回值为：' . $err_msg . '<BR>';
            $log->write_log($log->gen_id(), $logTitle, __CLASS__, __METHOD__, '', '', 'response', 'fail', $logInfo, array('task_id'=>$sdf['order_bn']));
            
            echo $err_msg;
            exit;
        }

        //记录下单时间的更新时间
        //$order_lastmodify = kernel::single('ecorder_func')->date2time($sdf['modified']);
        //kernel::single('taocrm_monitor_count')->setOrderTime($order_lastmodify);

        //ecstore类型店铺只要是已支付已发货就被认为是已完成
        if($shop_info['shop_type'] == 'ecos.b2c'){
            if($sdf['pay_status'] == 1 && $sdf['ship_status'] == 1 ){
                $sdf['status'] = 'finish';
            }
        }
        
        //转化为淘宝分销类型
        if($shop_info['shop_type'] == 'taobao' && $shop_info['subbiztype'] == 'fx'){
            $shop_info['shop_type'] = 'fenxiao';
        }
        
        //分销王订单
        if($shop_info['shop_type'] == 'shopex_b2b'){
            $shop_info['shop_type'] = 'fenxiao';
        }
        
        if($sdf['shop_type'] == 'drp'){
            $shop_info['shop_type'] = 'drp';
        }
        
        if($sdf['shop_type'] == 'offlinepos'){
            $shop_info['shop_type'] = 'offlinepos';
        }

        $objPlugin = $this->_instancePlugin($shop_info['shop_type']);
        $objPlugin->initSupportShopType($this->_plugins);

        return $objPlugin->add($sdf , $response);
    }

    /**
     * 通过插件名获取插件类并返回
     *
     * @param String $plugName 插件名
     * @return Object
     */
    private function & _instancePlugin($plugName)
    {
        $special_type = 'taobao,fenxiao,drp,offlinepos';
        if(!strstr($special_type, $plugName)){
            $plugName = 'default';
        }
        $fullPluginName = sprintf('ecorder_rpc_response_order_add_%s', $plugName);

        if(!class_exists($fullPluginName)){
            echo 'no '.$plugName.' plugin';
            exit;
            //$fullPluginName = 'ecorder_rpc_response_order_add_default';
        }
         
        $fix = md5(strtolower($fullPluginName));

        if (!isset(self::$_plugObjects[$fix])) {

            $obj = new $fullPluginName();
            if ($obj instanceof ecorder_rpc_response_order_add_interface) {

                self::$_plugObjects[$fix] = $obj;
            }
        }
        return self::$_plugObjects[$fix];
    }

    /**
     * 获取店铺信息
     *
     * @param void
     * @return array
     */
    protected function fetchShopInfo(&$sdf)
    {
        $node_id = base_rpc_service::$node_id;
        $oShop = app::get('ecorder')->model('shop');
        $shop_info = $oShop->dump(array('node_id' => $node_id), 'shop_type,subbiztype');
        return $shop_info;
    } 
}
