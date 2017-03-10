<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class taocrm_misc_task{

    function week(){

    }

    function minute(){

    }

    function hour(){
        kernel::single('taocrm_analysis_task')->analysis_hour();
        kernel::single('taocrm_analysis_task')->analysis_day();
    }

    function day(){
        kernel::single('taocrm_coupon_task')->updateSentStatus();
    }

    function month(){

    }
}
