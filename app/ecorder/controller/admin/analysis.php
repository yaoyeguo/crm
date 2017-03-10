<?php
class ecorder_ctl_admin_analysis extends desktop_controller{

    function member(){
         
        $oShop = &app::get('ecorder')->model("shop");
        $shop_data=$oShop->getList("shop_id,name");
        $this->pagedata['shoplist']=$shop_data;
        $this->pagedata['date_from'] = date('Y-m-d',strtotime('-1 days'));
        $this->pagedata['date_to'] = date('Y-m-d');
        $this->page("admin/download/member.html");
    }


    function index(){
         
        $oShop = &app::get('ecorder')->model("shop");
        $shop_data=$oShop->getList("shop_id,name");
        $this->pagedata['shoplist']=$shop_data;
        $this->pagedata['date_from'] = date('Y-m-d',strtotime('-1 days'));
        $this->pagedata['date_to'] = date('Y-m-d');
        $this->page("admin/download/analysis.html");
    }

    /**
     * 手动执行运算统计数据
     */
    public function run_analysis(){

        set_time_limit(360);

        $curr_days = intval($_POST['days']);
        $shop_id = $_POST['shop_id'];
        $date_from = strtotime($_POST['date_from']);
        $date_to = strtotime($_POST['date_to']);
        //$date_to = strtotime('+1 days',$date_from);
        $days = ($date_to - $date_from)/(24*60*60);

        if($curr_days >= $days) {

            //检查客户的地区信息数据
            $oMembers = &app::get('taocrm')->model('members');
            $oMembers->chkMemberArea(false,$shop_id);
            $oMembers->runAnalysisById(false,1000,$shop_id);

            echo('finish');
            die();
        }
        $date = date('Y-m-d',$date_from + $curr_days*24*60*60);
        if($shop_id && $date){
            kernel::single('ecorder_service_orders')->countBuys($shop_id,$date_from + $curr_days*24*60*60);
            kernel::single('taocrm_service_member')->saveMemberAnalysisDay($shop_id,$date);
        }
        echo($date);
    }

    /**
     * 手动执行客户统计数据
     */
    public function run_member(){

        set_time_limit(360);
        $days = intval($_POST['days']);
        $shop_id = $_POST['shop_id'];
        $filters = $_POST['filters'];//统计范围：all or uncount
        //error_log(var_export($_POST,1),3,'00000000.php');

        $oMembers = &app::get('taocrm')->model('members');

        $sql = "update sdb_taocrm_member_analysis
        set first_buy_time=NULL where shop_id='$shop_id' ";
        if($days==0 && $filters[0]=='all') $oMembers->db->exec($sql);

        if($filters[0]=='allmember'){
            $limit = ( $days * 50 ) .',50';
            $res = $oMembers->runAnalysisByAll(false,$limit,$shop_id);
        }else{
            $res = $oMembers->runAnalysisById(false,50,$shop_id);
        }
         
        echo($res);
    }
    
    /**
     * 内存管理
     */
    public function memory()
    {
        $this->page("admin/memory/index.html");
    }
    
    /**
     * 重新加载内存
     */
    public function repeat_load_memory()
    {
        $data = array('res' => 'fail', 'msg' => 'error vists', 'info' => array());
        if ($_POST['repeat'] == 1) {
            $data = array('res' => 'success', 'msg' => '', 'info' => array());
            $res = $this->checkLoadMemory('data');
            if ($res['status'] == 2) {
                $connect = kernel::single('taocrm_middleware_connect');
                $connect->removeDBIndex();
            }
            $data['info']['url'] = app::get('desktop')->base_url(1);
            $data['info']['status'] = $res['status'];
        }
        echo json_encode($data);
    }
    
    /**
     * 检查内存是否加载
     * Enter description here ...
     * @param unknown_type $return
     */
    public function checkLoadMemory($return = 'json')
    {
        $connect = new taocrm_middleware_connect;
        //加载方法
        $result = $connect->DbIndexState();
        $data = array();
        switch ($result) {
            case 'NULL':
                $data = array('status' => 1);
                break;
            case 'READY':
                $data = array('status' => 2);
                break;
            case 'LOADING':
                $data = array('status' => 3);
                break;
            default:
                $data = array('status' => 1);
                break;
        }
        
        if ($return != 'data') {
            echo json_encode($data);
            exit;
        }
        else {
            return $data;
        }
    }

}

