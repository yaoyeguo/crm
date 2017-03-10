<?php
class taocrm_ctl_admin_rebate_rule extends desktop_controller{

    public function index()
    {
        if($_POST){
            $this->save();
        }
        
        //默认设置
        $conf = array(
            'rebate_type' => 'paid_amount',
            'order_status' => 'finish',
            'is_join' => array(
                'paid' => array(
                    0 => '1',
                    2 => '1',
                    3 => '1',
                )
            ),
            'ratio' => array(
                'paid' => array(
                    0 => '3',
                    2 => '2',
                    3 => '1',
                )
            ),
        );
        
        $filter = array('is_del'=>0, 1=>1);
        $rs = $this->app->model('rebate_rule')->dump($filter);
        if($rs && $rs['conf']){
            $conf = json_decode($rs['conf'], true);
        }
        
        $this->pagedata['conf'] = $conf;
        $this->page('admin/rebate/rule.html');
    }
    
    function save()
    {
        $url = 'index.php?app=taocrm&ctl=admin_rebate_rule&act=index';
        $this->begin($url);
        $save['conf'] = json_encode($_POST);
        $save['op_user'] = kernel::single('desktop_user')->get_name();
        $save['create_time'] = date('Y-m-d H:i:s');
        
        $update = array('is_del'=>1);
        $filter = array();
        $this->app->model('rebate_rule')->update($update, $filter);
        $this->app->model('rebate_rule')->insert($save);
        $this->end(true,'保存成功');
    }

}

