<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_ctl_matrix extends desktop_controller{

    function index(){
        if(!$_POST)
        {
            base_kvstore::instance('desktop')->fetch('matrix_switch', $rs);
            $rs = json_decode($rs,true);
            $this->pagedata['matrix_switch'] = $rs;
            $this->page('matrix/edit.html');
        }else
        {
            $this->begin();
            base_kvstore::instance('desktop')->store('matrix_switch', json_encode($_POST));
            $certificate = kernel::single('base_certificate');
            if($certificate->register())
            {
                $this->end(true,'操作成功');
            }else
            {
                $this->end(false,'操作失败');
            }
        }
    }
}
