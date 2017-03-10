<?php
class ecorder_shop_type{

    /**
     * 店铺类型
     * @access public
     * @return Array
     */
    static function get_shop_type(){
        $shop_type = array (
            'shopex_b2c' => '48体系网店',
            'shopex_b2b' => '分销王',
            'ecos.ome' => '后端业务处理系统',
            'ecos.b2c' => 'ec-store',
            'taobao' => '淘宝',
            'paipai' => '拍拍',
            'ecshop_b2c' => 'ecshop',
            'yihaodian' => '一号店',
            '360buy' => '京东',
            'amazon' => '亚马逊',
            'dangdang' => '当当网',
            'meilishuo' => '美丽说',
            'mogujie' => '蘑菇街',
            'suning' => '苏宁',
            'gome' => '国美',
            'youzan' => '有赞',
            'wechat' => '微信',
          );
        return $shop_type;
    }

}