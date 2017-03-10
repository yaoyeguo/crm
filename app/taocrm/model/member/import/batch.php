<?php
class taocrm_mdl_member_import_batch extends dbeav_model {

    function getBatchList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $sql = 'select '.$cols.' from sdb_taocrm_member_import_batch where group_id='.$filter['group_id'];

        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);
        $rows = $this->db->selectLimit($sql,$limit,$offset);

        return $rows;
    }

    function getValidMemberCount($batch_id){

        $row = $this->db->selectRow('select count(*) as total from sdb_taocrm_member_import where  batch_id='.$batch_id .' and is_mobile_valid = 1');

        return intval($row['total']);
    }

    function getBatch($batch_id){
        $row = $this->db->selectRow('select * from sdb_taocrm_member_import_batch where  batch_id='.$batch_id);

        return $row;
    }
    
    function sendRunning($batch_id){
        $this->db->exec('update sdb_taocrm_member_import_batch set last_send_status="sending",send_nums=send_nums+1,last_send_time='.time().' where batch_id='.$batch_id);
    }
    
    public function countNums($group_id,$batch_id)
    {
        $db = kernel::database();
        //更新批次表
        $row = $db->selectRow('select count(*) as total from sdb_taocrm_member_import where  batch_id='.$batch_id);
        $total_nums_batch = intval($row['total']);
        $row = $db->selectRow('select count(*) as total from sdb_taocrm_member_import where  batch_id='.$batch_id .' and is_mobile_valid = 1');
        $mobile_valid_nums = intval($row['total']);
        $row = $db->selectRow('select count(*) as total from sdb_taocrm_member_import where  batch_id='.$batch_id .' and is_email_valid = 1');
        $email_valid_nums = intval($row['total']);
        $db->exec('update sdb_taocrm_member_import_batch set total_nums='.$total_nums_batch.',mobile_valid_nums='.$mobile_valid_nums.',email_valid_nums='.$email_valid_nums .' where batch_id='.$batch_id);

        //更新分组表
        $row = $db->selectRow('select sum(total_nums) as sum_total_nums,sum(mobile_valid_nums) as sum_mobile_valid_nums,sum(email_valid_nums) as sum_email_valid_nums from sdb_taocrm_member_import_batch where  group_id='.$group_id);
        $total_nums = intval($row['sum_total_nums']);
        $mobile_valid_nums = intval($row['sum_mobile_valid_nums']);
        $email_valid_nums = intval($row['sum_email_valid_nums']);
        $db->exec('update sdb_taocrm_member_import_group set total_nums='.$total_nums.',mobile_valid_nums='.$mobile_valid_nums.',email_valid_nums='.$email_valid_nums .',last_import_time='.time().' where group_id='.$group_id);
    }
    
    public function get_analysis($batch_id, $start_time, $end_time)
    {
        $member_num = 0;
        $order_num = 0;
        $payed = 0;
        
        $app = app::get('taocrm');
        $batch = $app->model('member_import');
        $orders = app::get('ecorder')->model('orders');
            
        $i = 0;
        $page_size = 1999;
        while(1){
            $offset = $i*$page_size;
            $memberList = $batch->getlist('mobile', array('batch_id'=>$batch_id), $offset, $page_size,'member_id');
            if( ! $memberList) break;
            $ship_mobiles = array();
            foreach($memberList as $n=>$m){
                $ship_mobiles[] = $m['mobile'];
            }
            $filter = array(
                'ship_mobile'=>$ship_mobiles,
                'pay_status'=>'1',
                'createtime|bthan'=>$start_time,
                'createtime|sthan'=>$end_time
            );
            $fields = 'count(distinct member_id) as member_num,
                    count(order_id) as order_num,
                    sum(payed) as payed
                    ';
            $order_num_arr = $orders->getlist($fields, $filter);
            $member_num += $order_num_arr[0]['member_num'];
            $order_num += $order_num_arr[0]['order_num'];
            $payed += $order_num_arr[0]['payed'];
            $i++;
        }
        
        $analysis_data = array(
            'member_num' => $member_num,
            'order_num' => $order_num,
            'payed' => '￥' . $payed,
        );
            
        return $analysis_data;
    }
}