<?php
class taocrm_mdl_member_tag extends dbeav_model {

    var $tmpTags = array();

    function delete($tag_id){
        $ids = array();
        if(!is_array($tag_id)){
            $ids[] = $tag_id;
        }else{
            $ids = $tag_id;
        }

        kernel::database()->exec('delete from sdb_taocrm_member_tag where tag_id in ('.implode(',', $ids).') and tag_type = "hand"');
        kernel::database()->exec('delete from sdb_taocrm_member_to_tag where tag_id in ('.implode(',', $ids).')');

        return true;
    }

    /*function add($tag_id,$member_ids){
     foreach($member_ids as $id){
     kernel::database()->exec('replace into sdb_taocrm_member_to_tag(tag_id, member_id) values('.$tag_id.', '.$id.');');
     }
     }*/

    function getTagList(){
        return kernel::database()->select('select * from sdb_taocrm_member_tag');
    }

    function getTagsByMember($member_ids){
        $tag_ids = array();
        $sql = "select tag_id from sdb_taocrm_member_to_tag where member_id in (".implode(',', $member_ids).") and tag_type in ('hand_on','system_on') ";
        $rs = kernel::database()->select($sql);
        foreach($rs as $v){
            $tag_ids[] = $v['tag_id'];
        }
        return $tag_ids;
    }

    //获取客户列表
    public function getMemberList($params, $offset=0, $limit=-1)
    {
        if(is_numeric($params)){
            $tag_id = $params;
        }else {
            $tag_id = $params['tag_id'];
        }
        
        /*
        $sql = "select member_id,mobile from sdb_taocrm_member_to_tag where tag_id={$tag_id} ";
        $rs = $this->db->select($sql);
        $memberList = array();
        $count = 0;
        foreach($rs as $v){
            if(strlen($v['mobile']) == 11){
                $memberList[] = $v['member_id'];
                $count ++;
            }
        }
        */
        
        $sql = "select count(DISTINCT member_id) as total from sdb_taocrm_member_to_tag where tag_id={$tag_id} and length(mobile)=11 and tag_type in ('hand_on','system_on') ";
        $rs = $this->db->selectRow($sql);
        $count = $rs['total'];
        
        //$count = 0;
        $sql = "select a.member_id,b.order_last_time from sdb_taocrm_member_to_tag as a
            left join sdb_taocrm_members as b on a.member_id=b.member_id
            where a.tag_id={$tag_id} and length(a.mobile)=11 and tag_type in ('hand_on','system_on')
            order by b.order_last_time DESC";
        if($limit>0){
            $sql .= " limit $offset,$limit ";
        }
        //echo($sql);
        $rs = $this->db->select($sql);
        if($rs){
            $memberList = array();
            foreach($rs as $v){
                $memberList[] = $v['member_id'];
                //$count ++;
            }
        }else{
            $memberList = array(-1);
        }
        
        //$memberList = "(select member_id from sdb_taocrm_member_to_tag where tag_id={$tag_id} and length(mobile)=11 ) ";
        //echo('<pre>');var_dump($memberList);
        return array('member_id'=>$memberList, 'total'=>$count);
    }

    function saveMemberTag($member_ids, $tag_ids, $old_tag_ids)
    {
        //$sql = "delete from sdb_taocrm_member_to_tag where member_id in (".implode(',', $member_ids).") ";
        $sql = "update sdb_taocrm_member_to_tag set tag_type = 'hand_off' where member_id in (".implode(',', $member_ids).") ";
        kernel::database()->exec($sql);

        if($tag_ids){
            foreach($member_ids as $member_id){
                //kernel::database()->exec('delete from sdb_taocrm_member_to_tag where member_id='.$member_id);
                foreach($tag_ids as $tag_id){
                    if(!$tag_id) continue;
                    //error_log('replace into sdb_taocrm_member_to_tag(tag_id, member_id,mobile) values (select '.$tag_id.','.$member_id.',mobile from sdb_taocrm_members where member_id='.$member_id.')'."\n",3,DATA_DIR.'/sy.txt');
                    kernel::database()->exec('replace into sdb_taocrm_member_to_tag (tag_id, member_id,mobile,tag_type)  (select '.$tag_id.','.$member_id.',mobile,\'hand_on\' from sdb_taocrm_members where member_id='.$member_id.')');
                }
            }
        }

        if($tag_ids){
            if($old_tag_ids){
                $tag_ids = array_merge($tag_ids, $old_tag_ids);
            }
        }else{
            $tag_ids = $old_tag_ids;
        }
        foreach($tag_ids as $tag_id){
            if(!$tag_id) continue;
            //error_log('update sdb_taocrm_member_tag as a,(select count(*) as total from sdb_taocrm_member_to_tag where tag_id='.$tag_id.') as b,as (select count(*) as total from sdb_taocrm_member_to_tag where tag_id='.$tag_id.' and mobile!=0) as c set members=b.total,mobile_valid_nums=c.total where a.tag_id='.$tag_id."\n",3,DATA_DIR.'/sy.txt');
            kernel::database()->exec('update sdb_taocrm_member_tag as a,(select count(*) as total from sdb_taocrm_member_to_tag where tag_id='.$tag_id.' and tag_type in ("hand_on","system_on") ) as b,(select count(*) as total from sdb_taocrm_member_to_tag where tag_id='.$tag_id.' and tag_type in ("hand_on","system_on") and mobile!=0) as c set members=b.total,mobile_valid_nums=c.total where a.tag_id='.$tag_id);
        }
    }

    public function getMemberTagInfo($member_id)
    {
        if($member_id && is_array($member_id)){
            $member_id = implode(',', $member_id);
        }
        
        if(!$member_id) return false;
        
        $tags = array();
        $rows = kernel::database()->select("select tag_id,member_id from sdb_taocrm_member_to_tag where member_id in ({$member_id}) and tag_type in ('hand_on','system_on') ");
        if($rows){
            if(empty($this->tmpTags)){
                $tagList = kernel::database()->select('select tag_id,tag_name from sdb_taocrm_member_tag');
                foreach($tagList as $row){
                    $this->tmpTags[$row['tag_id']] = $row['tag_name'];
                }
            }
            foreach($rows as $row){
                if(isset($this->tmpTags[$row['tag_id']])){
                    $tags[$row['member_id']][] = $this->tmpTags[$row['tag_id']];
                }
            }
        }

        return $tags;
    }

    function getTagSendInfo($tag_id){
        $info = array('total_nums'=>0,'mobile_valid_nums'=>0);
        $row = kernel::database()->selectRow('select count(*) as total from sdb_taocrm_member_to_tag where tag_id='.$tag_id.' and tag_type in ("hand_on","system_on") ');
        $info['total_nums'] = $row['total'];
        $row = kernel::database()->selectRow('select count(*) as total from sdb_taocrm_member_to_tag where tag_id='.$tag_id .' and tag_type in ("hand_on","system_on") and mobile !=0');
        $info['mobile_valid_nums'] = $row['total'];

        return $info;
    }

    function getSmsList($tag_id,$page,$page_size = 200){
        return kernel::database()->select('select mobile as phones from sdb_taocrm_member_to_tag where tag_id='.$tag_id.' and mobile !=0 and tag_type in ("hand_on","system_on") order by member_id limit '.($page*$page_size) .','.$page_size);
    }

    function updateSendTime($tag_id){
        kernel::database()->exec('update sdb_taocrm_member_tag set last_send_time='.time().' where tag_id='.$tag_id);
    }

    function getTagTop($num){

        $rows = kernel::database()->select('select tag_id,tag_name from sdb_taocrm_member_tag limit 0,'.$num);
        return $rows;
    }

    function getTagByKeyWord($keyword){

        $rows = kernel::database()->select('select tag_id,tag_name from sdb_taocrm_member_tag where tag_name like "%'.$keyword.'%"');

        return $rows;
    }
}