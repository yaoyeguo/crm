<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class ecorder_mdl_gift_rule extends dbeav_model{
    
    public function modifier_gift_bn($row)
    {
        $shop_gift = app::get(ORDER_APP)->model('shop_gift');
        $aGift = $shop_gift->dump(array('gift_bn'=>$row),'gift_name');
        return $aGift['gift_name'];
    }
    
}
