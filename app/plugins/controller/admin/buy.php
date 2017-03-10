<?php
class plugins_ctl_admin_buy extends desktop_controller{
    var $workground = 'plugins.manage';

    public function index(){
        $workers = $plug_list = array();
        $oPlugins = $this->app->model('plugins');
        $rs = $oPlugins->getList('worker,end_time');
        if($rs){
            foreach($rs as $v){
                $workers[$v['worker']] = $v['end_time'];
            }
        }
        
        $plugins = kernel::single('plugins_service_api')->plugins;
        foreach($plugins as $v){
            $rs = kernel::single('plugins_service_'.$v)->get_desc();
            
            if(isset($rs['dead_line']) && time() > strtotime($rs['dead_line'].' 00:00:00')){
                continue;
            }
            
            if($rs['status'] != 'active') continue;
            
            if(isset($workers['plugins_service_'.$v])){
                if(time() > $workers['plugins_service_'.$v]){
                    $rs['buy_status'] = 'renew_red';
                }else{
                    $rs['buy_status'] = 'renew';
                }
            }else{
                $rs['buy_status'] = 'buy';
            }
            
            $plug_list[] = $rs;
        }

        $this->array_sort($plug_list,'sort','asc');
        $this->pagedata['plug_list'] = $plug_list;
        $this->page('admin/free/plugins.html');
    }
    
    public function buy(){
        //获取余额：usersms_info.month_residual
        $smsAPI = kernel::single('market_service_smsinterface');
        $res = $smsAPI->get_usersms_info();
        if($res['res'] == 'fail') die('<p align=center><br/><br/><br/><br/><a href="index.php?app=market&ctl=admin_sms_account&act=index"  target="_top">您可能没有激活短信帐号：'.$res['info'].'！</a></p>');   

        if($_POST){
            $worker = $_POST['worker'];
            kernel::single($worker)->plugin_buy();
            return false;
        }
        
        $worker = $_GET['worker'];
        $plugins = kernel::single($worker)->get_desc();
        
        //检查剩余的服务时限
        $db = kernel::database();
        $sql = "select end_time from sdb_plugins_plugins where worker='".$worker."' ";
        $rs = $db->selectRow($sql);
        if($rs){
            $remain_time = $rs['end_time'] - time();
            $remain_time = ceil($remain_time/(30*86400));//转换成月份
            $this->pagedata['remain_time'] = $remain_time;
        }
        
        $this->pagedata['price_list'] = implode(',',$plugins['price']);
        $this->pagedata['month_list'] = implode(',',$plugins['month']);
        $this->pagedata['plugins'] = $plugins;
        $this->pagedata['month_residual'] = $res['info']['all_residual'] - $res['info']['block_num'];
        $this->display('admin/free/buy.html');
    }
    
    //禁用插件
    public function close_plugin()
    {
        $this->begin('index.php?app=plugins&ctl=admin_manage&act=index');
        $plugin_id = $_GET['plugin_id'];
        $plugin = kernel::single($plugin_id)->get_desc();
        
        $db = kernel::database();
        $oPluginsOrders = app::get('plugins')->model('orders');
        $oPlugins = app::get('plugins')->model('plugins');
        
        $arr['worker'] = $plugin['worker'];
        $arr['plugin_name'] = $plugin['title'];
        $arr['amount'] = $plugin['price'][intval($_POST['month'])];
        $arr['price'] = 0;
        $arr['buy_time'] = time();
        $arr['month'] = $plugin['month'][intval($_POST['month'])];
        
        
        $sql = 'select * from sdb_plugins_plugins where worker="'.$arr['worker'].'" ';
        $rs = $db->selectRow($sql);
        if($rs){
            // 续费
            $arr['plugin_id'] = $rs['plugin_id'];
            $arr['end_time'] = strtotime('-1 months');
            $oPlugins->save($arr);
        }
        
        // 保存购买记录
        $arr['op_user'] = kernel::single('desktop_user')->get_name();
        $oPluginsOrders->insert($arr);
        $this->end(true,'保存成功');
    }
    
    public function array_sort(&$arr,$keys,$type='desc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        $i = 1;
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
            $new_array[$k]['order'] = $i++;
        }
        $arr = $new_array;
    }

}
