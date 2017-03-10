<?php

class taocrm_rpc_response_member_benefits extends taocrm_rpc_response
{

    public function additem($sdf, &$responseObj){
        $apiParams = array(
             'benefits_code'=>array('label'=>'权益项代码','required'=>true),
             'benefits_name'=>array('label'=>'权益项名称','required'=>true),
             'source'=>array('label'=>'来源业务','required'=>true),
         	 'is_enable'=>array('label'=>'是否启用','required'=>true),
             'op_name'=>array('label'=>'创建人','required'=>true),
             'op_time'=>array('label'=>'创建时间','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $objBenefitsItem = app::get('taocrm')->model('member_benefits_item');
        if( $objBenefitsItem->checkBenefitsCode($sdf['benefits_code'])){
            $responseObj->send_user_error('权益项代码已存在');
        }

        $data = array(
            'benefits_code'=>$sdf['benefits_code'],
            'benefits_name'=>$sdf['benefits_name'],
            'source'=>$sdf['source'],
            'op_name'=>$sdf['op_name'],
            'op_time'=>$sdf['op_time'],
            'is_enable'=>$sdf['is_enable'],
            'create_op_name'=>$sdf['op_name'],
            'create_op_time'=>strtotime($sdf['op_time']),
            'update_time'=>time(),
            'create_time'=>time()
        );
        $objBenefitsItem->save($data);

        return array('id'=>$data['id']);
    }
     

    public function getlogs($sdf, &$responseObj){
        $apiParams = array(
            'member_id'=>array('label'=>'客户ID','required'=>true),
            'start_date'=>array('label'=>'开始时间','required'=>true),
            'end_date'=>array('label'=>'结束时间','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $objBenefitsLog = app::get('taocrm')->model('member_benefits_log');
        $logs = $objBenefitsLog->getList('*',array('member_id'=>$sdf['member_id']));
        $list = array();
        foreach($logs as $k=>$log){
            $list[] = array(
                'id'=>$log['id'],
                'benefits_type'=>$log['benefits_type'],
                'get_benefits_mode'=>$log['get_benefits_mode'],
                'op_mode'=>$log['op_mode'],
                'get_benefits_desc'=>$log['get_benefits_desc'],
                'benefits_code'=>$log['benefits_code'],
                'benefits_name'=>$log['benefits_name'],
                'nums'=>$log['nums'],
                'effectie_time'=>date('Y-m-d H:i:s',$log['effectie_time']),
                'failure_time'=>date('Y-m-d H:i:s',$log['failure_time']),
                'is_enable'=>$log['is_enable'],
                'source_order_bn'=>$log['source_order_bn'],
                'source_business_code'=>$log['source_business_code'],
                'source_business_name'=>$log['source_business_name'],
                'source_store_name'=>$log['source_store_name'],
                'source_terminal_code'=>$log['source_terminal_code'],
                'memo'=>$log['memo'],
                'create_op_time'=>!empty($log['create_op_time']) ? date('Y-m-d H:i:s',$log['create_op_time']) : '',
                'create_op_name'=>$log['create_op_name'],
            );
        }

        return $list;
    }

    public function add($sdf, &$responseObj){
        $apiParams = array(
        	 'member_id'=>array('label'=>'会员ID','required'=>true),
             'benefits_type'=>array('label'=>'权益类型','required'=>true),
             'get_benefits_mode'=>array('label'=>'获取权益方式','required'=>true),
             'op_mode'=>array('label'=>'新增或扣减权益','required'=>true),
             'get_benefits_desc'=>array('label'=>'获取权益说明','required'=>false),
             'benefits_code'=>array('label'=>'权益项代码','required'=>true),
             'benefits_name'=>array('label'=>'权益项名称','required'=>true),
             'nums'=>array('label'=>'值(金额或者次数或者折扣)','required'=>true),
             'effectie_time'=>array('label'=>'生效时间','required'=>true),
             'failure_time'=>array('label'=>'失效时间','required'=>false),
             'is_enable'=>array('label'=>'是否可用','required'=>true),
             'source_order_bn'=>array('label'=>'来源关联单号','required'=>false),
             'source_business_code'=>array('label'=>'来源业务Code','required'=>false),
             'source_business_name'=>array('label'=>'来源业务名称','required'=>false),
             'source_store_name'=>array('label'=>'来源门店代码','required'=>false),
             'source_terminal_code'=>array('label'=>'来源终端代码','required'=>false),
             'memo'=>array('label'=>'说明备注','required'=>false),
             'create_op_time'=>array('label'=>'创建人','required'=>true),
             'create_op_name'=>array('label'=>'创建时间','required'=>true),
        );
        $this->checkApiParams($apiParams,$sdf, $responseObj);

        $objBenefitsItem = app::get('taocrm')->model('member_benefits_item');
        if( !$objBenefitsItem->checkBenefitsCode($sdf['benefits_code'])){
            $responseObj->send_user_error('权益项代码不存在');
        }

        $objBenefits = app::get('taocrm')->model('member_benefits');
        $op_before_nums = $objBenefits->getNumsByCode($sdf['member_id'],$sdf['benefits_code']);
        $data = array(
                'member_id'=>$sdf['member_id'],
                'benefits_type'=>$sdf['benefits_type'],
                'benefits_code'=>$sdf['benefits_code'],
                'benefits_name'=>$sdf['benefits_name'],
                'nums'=>$sdf['nums'],
                'effectie_time'=>$sdf['effectie_time'] ? strtotime($sdf['effectie_time']) : 0,
                'failure_time' => $sdf['failure_time'] ? strtotime($sdf['failure_time']) : 0,
                'is_enable'=>$sdf['is_enable'],
                'update_time'=>time(),
        		'create_time'=>time()
        );
        $objBenefits->save($data);
        $op_after_nums = $objBenefits->getNumsByCode($sdf['member_id'],$sdf['benefits_code']);

        $objBenefitsLog = app::get('taocrm')->model('member_benefits_log');
        $log = array(
                'member_benefits_id'=>$data['id'],
         		'member_id'=>$sdf['member_id'],
                'op_before_nums'=>$op_before_nums,
                'op_after_nums'=>$op_after_nums,
                'benefits_type'=>$sdf['benefits_type'],
                'get_benefits_mode'=>$sdf['get_benefits_mode'],
                'op_mode'=>$sdf['op_mode'],
                'get_benefits_desc'=>$sdf['get_benefits_desc'],
                'benefits_code'=>$sdf['benefits_code'],
                'benefits_name'=>$sdf['benefits_name'],
                'nums'=>$sdf['nums'],
                'effectie_time'=>$sdf['effectie_time'] ? strtotime($sdf['effectie_time']) : 0,
                'failure_time' => $sdf['failure_time'] ? strtotime($sdf['failure_time']) : 0,
                'is_enable'=>$sdf['is_enable'],
                'source_order_bn'=>$sdf['source_order_bn'],
                'source_business_code'=>$sdf['source_business_code'],
                'source_business_name'=>$sdf['source_business_name'],
                'source_store_name'=>$sdf['source_store_name'],
                'source_terminal_code'=>$sdf['source_terminal_code'],
                'memo'=>$sdf['memo'],
                'create_op_time'=>strtotime($sdf['create_op_time']),
                'create_op_name'=>$sdf['create_op_name'],
                'create_time'=>time()
        );
        $objBenefitsLog->save($log);


        return array('benefits_id'=>$data['id']);
    }

}