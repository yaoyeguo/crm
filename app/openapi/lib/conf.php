<?php

class openapi_conf {

    static public function getMethods(){
        return array(
            'point' => array(
                'label' => '积分接口',
                'methods' => array(
                    'update' => '更新积分',
                    'get' => '积分查询',
                    'update_by_parent_code' => '根据推荐码更新积分接口',
                )
            ),
            'pointlog' => array(
                'label' => '积分日志接口',
                'methods' => array(
                    'getlist' => '积分日志查询',
                )
            ),
            'posorder' => array(
                'label' => 'POS订单接口',
                'methods' => array(
                    'add' => 'POS订单',
                )
            ),
            'members' => array(
                'label' => '客户接口',
                'methods' => array(
                    'add' => '客户信息新增',
                    'get' => '客户信息查询',
                    'getlist' => '客户列表查询',
                    'update' => '客户信息更新',
                    'update_recommend' => '更新推荐关系接口',
                )
            ),
            'orders' => array(
                'label' => '客户历史订单接口',
                'methods' => array(
                    'search' => '客户历史订单查询',
                )
            ),
//            'member_benefits' => array(
//                'label' => '客户储值接口',
//                'methods' => array(
//                    'add' => '新增客户储值权益',
//                    'additem' => '客户新增权益项',
//                    'getlogs' => '查询客户权益账户变更明细',
//                )
//            ),
            'order' => array(
                'label' => '订单接口',
                'methods' => array(
                    'add' => '订单增加',
                )
            ),
            'refund' => array(
                'label' => '退款单接口',
                'methods' => array(
                    'add' => '退款单增加',
                )
            ),
        );
    }
}