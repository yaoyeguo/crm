<?php

class openapi_api_params_v1_taocrm_members extends openapi_api_params_abstract implements openapi_api_params_interface{

    public function checkParams($method,$params,&$sub_msg,$defined_params=array(),$dataType='kv'){
        if(parent::checkParams($method,$params,$sub_msg,$defined_params,$dataType)){
            return true;
        }else{
            return false;
        }
    }

    public function getAppParams($method){
        $params = array(
            'add'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.members.add','type'=>'json',
                    'cols'=>array(
                        'uname'=>array('name'=>'客户用户名','required'=>false,'type'=>'string'),
                        'uid'=>array('name'=>'外部会员ID','required'=>true,'type'=>'int'),
                        'real_name'=>array('name'=>'客户真实姓名','required'=>false,'type'=>'string'),
                        'source_terminal'=>array('name'=>'来源终端','required'=>false,'type'=>'string'),
                        'zip'=>array('name'=>'邮编','required'=>false,'type'=>'string'),
                        'state'=>array('name'=>'省份','required'=>false,'type'=>'string'),
                        'city'=>array('name'=>'城市','required'=>false,'type'=>'string'),
                        'district'=>array('name'=>'地区','required'=>false,'type'=>'string'),
                        'address'=>array('name'=>'详细地址','required'=>false,'type'=>'string'),
                        'mobile'=>array('name'=>'手机','required'=>false,'type'=>'string'),
                        'email'=>array('name'=>'email','required'=>false,'type'=>'string'),
                        'birthday'=>array('name'=>'生日','required'=>false,'type'=>'string'),
                        'sex'=>array('name'=>'性别 0未知1男2女','required'=>false,'type'=>'number'),
                        'is_vip'=>array('name'=>'是否贵宾0不是1是','required'=>false,'type'=>'number'),
                        'is_sms_black'=>array('name'=>'短信黑名单0不是1是','required'=>false,'type'=>'number'),
                        'is_email_black'=>array('name'=>'邮件黑名单0不是1是','required'=>false,'type'=>'number'),
                        'alipay'=>array('name'=>'支付宝账号','required'=>false,'type'=>'string'),
                        'remark'=>array('name'=>'客户备注','required'=>false,'type'=>'string'),
                        // 'props'=>array('name'=>'自定义属性','required'=>false,'type'=>'string'),
                        //'other_contact'=>array('name'=>'其他联系账号','required'=>false,'type'=>'string'),
                        // 'qq'=>array('name'=>'qq','required'=>false,'type'=>'string'),
                        //'weibo'=>array('name'=>'微博','required'=>false,'type'=>'string'),
                        //'weixin'=>array('name'=>'微信','required'=>false,'type'=>'string'),
                        //'wangwang'=>array('name'=>'旺旺','required'=>false,'type'=>'string'),
                        'parent_code'=>array('name'=>'推荐人推荐码','required'=>false,'type'=>'int'),
                    ),
                ),
            ),
            'get'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.members.get','type'=>'json',
                    'cols'=>array(
                        'member_id'=>array('name'=>'客户ID ','required'=>true,'type'=>'int'),
                    ),
                ),
            ),
            'getlist'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.members.getlist','type'=>'json',
                    'cols'=>array(
                        'start_update_date'=>array('name'=>'客户更新时间(开始时间)  ','required'=>true,'type'=>'string'),
                        'end_update_date'=>array('name'=>'客户更新时间(结束时间) ','required'=>false,'type'=>'string'),
                        'start_created_date'=>array('name'=>'客户创建时间(开始时间)  ','required'=>false,'type'=>'string'),
                        'end_created_date'=>array('name'=>'客户创建时间(结束时间) ','required'=>false,'type'=>'string'),
                        'page_size'=>array('name'=>'每页显示  ','required'=>true,'type'=>'int'),
                        'page'=>array('name'=>'页码 ','required'=>true,'type'=>'int'),
                    ),
                ),
            ),
            'update'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.members.update','type'=>'json',
                    'cols'=>array(
                        'member_id'=>array('name'=>'客户ID ','required'=>true,'type'=>'int'),
                        //'node_id'=>array('name'=>'店铺节点ID ','required'=>true,'type'=>'int'),
                        'real_name'=>array('name'=>'客户真实姓名','required'=>false,'type'=>'string'),
                        'zip'=>array('name'=>'邮编','required'=>false,'type'=>'string'),
                        'state'=>array('name'=>'省份','required'=>false,'type'=>'string'),
                        'city'=>array('name'=>'城市','required'=>false,'type'=>'string'),
                        'district'=>array('name'=>'地区','required'=>false,'type'=>'string'),
                        'address'=>array('name'=>'详细地址','required'=>false,'type'=>'string'),
                        'mobile'=>array('name'=>'手机','required'=>false,'type'=>'string'),
                        'email'=>array('name'=>'email','required'=>false,'type'=>'string'),
                        'birthday'=>array('name'=>'生日','required'=>false,'type'=>'string'),
                        'sex'=>array('name'=>'性别 0未知1男2女','required'=>false,'type'=>'number'),
                        //'LevelCode'=>array('name'=>'等级代码 ','required'=>false,'type'=>'string'),
                        //'LevelName'=>array('name'=>'等级名称 ','required'=>false,'type'=>'string'),
                        'is_vip'=>array('name'=>'是否贵宾0不是1是','required'=>false,'type'=>'number'),
                        'is_sms_black'=>array('name'=>'短信黑名单0不是1是','required'=>false,'type'=>'number'),
                        'is_email_black'=>array('name'=>'邮件黑名单0不是1是','required'=>false,'type'=>'number'),
                        'alipay'=>array('name'=>'支付宝账号','required'=>false,'type'=>'string'),
                        'remark'=>array('name'=>'客户备注','required'=>false,'type'=>'string'),
                        //'props'=>array('name'=>'自定义属性','required'=>false,'type'=>'string'),
                        //'other_contact'=>array('name'=>'其他联系账号','required'=>false,'type'=>'string'),
                        //'qq'=>array('name'=>'qq','required'=>false,'type'=>'string'),
                        //'weibo'=>array('name'=>'微博','required'=>false,'type'=>'string'),
                        //'weixin'=>array('name'=>'微信','required'=>false,'type'=>'string'),
                        //'wangwang'=>array('name'=>'旺旺','required'=>false,'type'=>'string'),
                    ),
                ),
            ),
            'update_recommend'=>array(
                'content'=>array('required'=>'true','name'=>'taocrm.members.update_recommend','type'=>'json',
                    'cols'=>array(
                        'referee_member_id'=>array('name'=>'推荐人会员ID ','required'=>true,'type'=>'int'),
                        'recommended_member_ids'=>array('name'=>'被推荐会员ID数组  ','required'=>true,'type'=>'string'),
                    ),
                ),
            ),
        );
        return $params[$method];
    }

    public function description($method){
        $desccription = array(
            'add'=>array('name'=>'客户信息新增','description'=>'客户信息新增'),
            'get'=>array('name'=>'客户信息查询','description'=>'客户信息查询'),
            'getlist'=>array('name'=>'客户列表查询','description'=>'客户列表查询'),
            'update'=>array('name'=>'客户信息更新','description'=>'客户信息更新'),
            'update_recommend'=>array('name'=>'更新推荐关系接口','description'=>'更新推荐关系接口'),
        );
        return $desccription[$method];
    }
}