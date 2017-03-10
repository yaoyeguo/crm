<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class ecorder_mdl_gift_logs extends dbeav_model{
    
    public function modifier_gift_rule_id($gift_rule_id)
    {
        $mdl = app::get('ecorder')->model('gift_rule');
        $rs = $mdl->dump(array('id'=>$gift_rule_id), 'title');
        return '<a target="dialog::{title:\''.app::get('ecorder')->_('查看促销规则').'\', width:800, height:400}" href="index.php?app=ecorder&ctl=admin_gift_rule&act=view_rule&p[0]='.$gift_rule_id.'">'.$rs['title'].'</a>';
    }

}
