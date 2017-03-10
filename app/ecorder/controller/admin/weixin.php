<?php
class ecorder_ctl_admin_weixin extends desktop_controller{

    function bind_event()
    {
        $this->begin();
        $node_id = $_GET['node_id'];
        if($node_id){
            kernel::single('ecorder_rpc_response_shop')->reg_weixin_service($node_id);
        }
        $this->end(true, '绑定成功');
    }

}
