<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class taocrm_mdl_member_group_data extends dbeav_model{
    
   /**
    * 删除分组与客户关联数据
    * 
    * @return bool
    */
   public function delete_data($group_id){
       $sql = ' DELETE FROM sdb_taocrm_member_group_data WHERE group_id = '.$group_id;
       return kernel::database()->query($sql);
   }
    
    
}