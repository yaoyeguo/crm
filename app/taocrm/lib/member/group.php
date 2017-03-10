<?php
class taocrm_member_group{
    public function run(){
        $groupObj = &app::get('taocrm')->model('member_group');
        $groupDataObj = &app::get('taocrm')->model('member_group_data');

        $groups = $groupObj->getList('*');
        foreach($groups as $group){
            $groupDataObj->delete_data($group['group_id']);
            $groupObj->sync(unserialize($group['query_condition']));
            unset($group);
        }
        unset($groups);
        return true;
    }
}