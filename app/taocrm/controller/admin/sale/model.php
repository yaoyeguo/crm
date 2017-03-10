<?php
class taocrm_ctl_admin_sale_model extends desktop_controller {

    var $workground = 'taocrm.member';
    
    public function get_model()
    {
        $model = array(
            0=>array(
                0=>array(
                    'label'=>'全部客户',
                    'desc'=>'店铺内所有客户',
                    'filter_mem'=>array(
                        'last_buy_time'=>array('sign'=>'than','min_val'=>0),
                    ),
                ),
                1=>array(
                    'label'=>'全部成功交易客户',
                    'desc'=>'店铺内有成功订单的全部客户',
                    'filter_mem'=>array(
                        'finish_orders'=>array('sign'=>'bthan','min_val'=>1),
                    ),
                ),
            ),
            1=>array(
                0=>array(
                    'label'=>'近一个月新客户',
                    'desc'=>'第一次下单时间在一个月内，<br/>成功订单数大于等于1',
                    'filter_mem'=>array(
                        'finish_orders'=>array('sign'=>'bthan','min_val'=>1),
                        'first_buy_time'=>array('sign'=>'than','min_val'=>strtotime('-1 months')),
                    ),
                ),
                1=>array(
                    'label'=>'近三个月高价值客户',
                    'desc'=>'最后一次下单时间在三个月内，<br/>成功单数大于等于3，平均订单金额大于100',
                    'filter_mem'=>array(
                        'finish_orders'=>array('sign'=>'bthan','min_val'=>3),
                        'finish_per_amount'=>array('sign'=>'bthan','min_val'=>100),
                        'last_buy_time'=>array('sign'=>'than','min_val'=>strtotime('-3 months')),
                    ),
                ),
                2=>array(
                    'label'=>'铁杆粉丝',
                    'desc'=>'最后一次下单时间在三个月内，<br/>平均购买间隔小于30天，成功单数大于等于3',
                    'filter_mem'=>array(
                        'finish_orders'=>array('sign'=>'bthan','min_val'=>3),
                        'avg_buy_interval'=>array('sign'=>'sthan','min_val'=>30),
                        'last_buy_time'=>array('sign'=>'than','min_val'=>strtotime('-3 months')),
                    ),
                ),
                3=>array(
                    'label'=>'高质量沉睡老客户',
                    'desc'=>'最后一次下单时间在三个月前，<br/>成功单数大于等于3，平均订单金额大于100',
                    'filter_mem'=>array(
                        'finish_orders'=>array('sign'=>'bthan','min_val'=>3),
                        'finish_per_amount'=>array('sign'=>'bthan','min_val'=>100),
                        'last_buy_time'=>array('sign'=>'lthan','min_val'=>strtotime('-3 months')),
                    ),
                ),
            ),
            2=>array(
                0=>array(
                    'label'=>'近一个月未付款客户',
                    'desc'=>'最后一次下单时间在一个月内，<br/>最近一个月的订单均为未付款订单。',
                    'filter_mem'=>array(
                        'filter_func'=>'month_unpaid_members',
                    ),
                ),
            ),
        );
        return $model;
    }  
    
    public function index()
    {
        
    }
    
    public function model1()
    {
        $this->pagedata['model'] = $this->get_model();
        $this->page('admin/sale_model/model1.html');
    }
    
    public function model2()
    {
        
    }
    
    public function select_model()
    {
        $id = $_GET['id'];
        $id_arr = explode('_',$id);
        $all_model = &$this->get_model();
        $model = $all_model[$id_arr[0]][$id_arr[1]];
        $shopObj = app::get('ecorder')->model('shop');
        $shoplist=$shopObj->get_shops('no_fx');
        
        $this->pagedata['shoplist'] = $shoplist;
        $this->pagedata['id'] = $id;
        $this->pagedata['model'] = $model;
        $this->display('admin/sale_model/select_model.html');
    }
    
    function get_members()
    {
        $shop_id = $_GET['shop_id'];
        $id = $_GET['id'];
        $id_arr = explode('_',$id);
        $all_model = &$this->get_model();
        $model = $all_model[$id_arr[0]][$id_arr[1]];
        
        if($model) {
            //$oGroup = &$this->app->model('member_group');
            $data['shop_id'] = $shop_id;
            $data['filter'] = $model['filter_mem'];
            //自定义客户ID
            if(isset($data['filter']['filter_func'])){
                $filter_func = $data['filter']['filter_func'];
                $countMembers = $this->$filter_func('count', $shop_id);
            }else{
                //echo($oGroup->countMembers($data));
                //$data['filter'] = unserialize($data['filter']);
                //获得客户数量
                $this->interfacePacketName = 'ShopMemberAnalysis';
                $this->interfaceMethodName = 'SearchMemberAnalysisList';
                $this->interfaceTableName = 'taocrm_mdl_middleware_member_analysis';
                
                $middlewareContent = kernel::single('taocrm_middleware_connect');
                $tableName = $this->interfaceTableName;
                $data['packetName'] = $this->interfacePacketName;
                $data['methodName'] = $this->interfaceMethodName;
                $countMembers = $middlewareContent->count($tableName, $data);
            }
            echo($countMembers);
        }
    }
    
    function month_unpaid_members($type='count', $shop_id){
        $db = kernel::database();
        $unpaid_members = array();
        
        $createtime = strtotime('-1 month');
        $sql = "select member_id,count(order_id) as orders from sdb_ecorder_orders where createtime>=$createtime and pay_status='0' and shop_id='$shop_id' group by member_id ";
        $rs = $db->select($sql);
        if(!$rs) $rs=array();
        foreach($rs as $v){
            $unpaid_members[$v['member_id']] = $v['orders'];
        }
        
        if($unpaid_members){
            $sql = "select member_id,count(order_id) as orders from sdb_ecorder_orders where member_id in (".implode(',', array_keys($unpaid_members)).") and createtime>=$createtime and shop_id='$shop_id' group by member_id ";
            $rs = $db->select($sql);
            if(!$rs) $rs=array();
            foreach($rs as $v){
                if($unpaid_members[$v['member_id']] != $v['orders']){
                    unset($unpaid_members[$v['member_id']]);
                }
            }
        }
        
        if($type=='count'){
            return count($unpaid_members);
        }else{
            return array_keys($unpaid_members);
        }
    }
}