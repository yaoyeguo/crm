<?php
class market_mdl_wx_sign_in_log extends dbeav_model {

    function saveSignInLog($data)
    {
        //$cur = date('Y-m-d',strtotime('-1 day'));
        $cur = date('Y-m-d',$data['create_time']-86400);
        $cur_begin = strtotime($cur." 00:00:00");
        $cur_end = strtotime($cur." 23:59:59");
        $wxSD = $this->dump(array('FromUserName'=>$data['fromusername'],'create_time|bthan'=>$cur_begin,'create_time|sthan'=>$cur_end));
        $insert_data = array('FromUserName'=>$data['fromusername'],'member_id'=>$data['member_id'],'create_time'=>$data['create_time']);
        if(empty($wxSD)){
            $insert_data['sign_in_times'] = 1;
        }else{
            $insert_data['sign_in_times'] = $wxSD['sign_in_times'] + 1;
        }

        return $this->insert($insert_data);
    }

}
