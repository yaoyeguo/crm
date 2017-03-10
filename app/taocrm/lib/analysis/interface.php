<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

interface taocrm_analysis_interface{

    public function get_logs($time);

    public function detail();

    public function graph_data($params);
        
}//End Class