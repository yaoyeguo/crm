<?php

/**
 * 旺旺精灵服务
 *
 */
class taocrm_wangwangjingling_service
{
    protected static $shopObj = '';
    protected static $plugBindObj = '';
    protected static $memberPropertyObj = '';
    protected static $db = '';
    const FIRST_FIELD = '客户名';
    
    //是否绑定旺旺精灵
    public function isBind($shop_id = '')
    {
        return true;
    
        if ($shop_id == '') {
            $shop_id = $this->getShopId();
        }
        $model = $this->getPlugBindObj();
        $filter = array('shop_id' => $shop_id, 'status' => 1);
        $result = $model->dump($filter);
        if ($result) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * 获得旺旺精灵信息
     */
    public function getTagInfo($nick)
    {
        //以下暂时是测试代码
        $field = array('标签1','标签2','标签3','标签4','标签5','标签6','标签7','标签8','标签9','标签a');
        $value = array('值1','值2','值3','值4','值5','值6','值7','值8','值9','值a');
        return array('field' => $field, 'value' => $value);
    }
    
    /**
     * 获得店铺下的所有标签 
     */
    public function getTagAllInfoByShopId($shop_id, $fieds = array(), $search = array())
    {
        $db = $this->getDb();
        $sql = "
            SELECT
               `shop_id`, `uname`, GROUP_CONCAT(property) AS card_key,GROUP_CONCAT(`value`) AS card_value
            FROM
              `sdb_taocrm_member_property`
            WHERE
              `shop_id` = '{$shop_id}'";
        $sql .= " GROUP BY `uname`";
        $result = $db->select($sql);
        $data = array();
        if ($result) {
            if (empty($fieds)) {
                $fieds = $this->getTagField($shop_id);
            }
            $i = 0;
            foreach ($result as $v) {
                $data[$i]['shop_id'] = $v['shop_id'];
                $card_key = explode(',', $v['card_key']);
                $card_value = explode(',', $v['card_value']);
                $cardKV = array();
                foreach ($card_key as $k1 => $v1) {
                    $cardKV[$v1] = $card_value[$k1];
                }
                //ksort($cardKV);
                $stack = array();
                foreach ($fieds as $v2) {
                    if ($v2 == self::FIRST_FIELD) {
                        $stack[$v2] = $v['uname'];
                    }
                    else {
                        $stack[$v2] = isset($cardKV[$v2]) ? $cardKV[$v2] : "";
                    }
                }
                $data[$i]['uname'] = $v['uname'];
                $data[$i]['data'] = $stack;
                $i++;
            }
        }
        if ($search) {
            $newSearch = array();
            foreach ($search as $k => $v) {
                if ($v) {
                    $newSearch[$k] = $v;
                }
            }
            if ($newSearch && $data) {
                $newData = array();
                foreach ($data as $k => $v) {
                    $result = array_diff_assoc($newSearch, $v['data']);
                    if (empty($result)) {
                        $newData[] = $v;
                    }
                }
                $data = $newData;
            }
        }
        return $data;
    }
    
    /**
     * 获取店铺下的客户数量
     */
    public function getTypeTagAllInfoByShopId($shop_id, $type, $page = 0, $pageSize = 0, $fieds = array(), $search = array())
    {
        $db = $this->getDb();
        if ($type == 1) {
            $typeSql = " AND `sdb_taocrm_app_members`.`member_id` > 0";
        }
        elseif ($type == 0) {
            $typeSql = " AND `sdb_taocrm_app_members`.`member_id` <= 0";
        }
        $sql = "SELECT b.`member_id`,a.`uname`, a.`shop_id`, GROUP_CONCAT(a.property) AS card_key,
                GROUP_CONCAT(a.`value`) AS card_value
                FROM `sdb_taocrm_member_property` as a LEFT JOIN `sdb_taocrm_members` as b
                   ON a.`uname` = b.`uname`
                WHERE a.`shop_id` = '{$shop_id}'";
        //$sql .=$typeSql;
        $sql .= " GROUP BY a.`uname`";
        $result = $db->select($sql);
        $data = array();
        $count = 0;
        if ($result) {
            if (empty($fieds)) {
                $fieds = $this->getTypeTagField($shop_id, $type);
            }
            $i = 0;
            foreach ($result as $v) {
                $data[$i]['member_id'] = $v['member_id'];
                $data[$i]['shop_id'] = $v['shop_id'];
                $card_key = explode(',', $v['card_key']);
                $card_value = explode(',', $v['card_value']);
                $cardKV = array();
                foreach ($card_key as $k1 => $v1) {
                    $cardKV[$v1] = $card_value[$k1];
                }
                //ksort($cardKV);
                $stack = array();
                foreach ($fieds as $v2) {
                    if ($v2 == self::FIRST_FIELD) {
                        $stack[$v2] = $v['uname'];
                    }
                    else {
                        $stack[$v2] = isset($cardKV[$v2]) ? $cardKV[$v2] : "";
                    }
                }
                $data[$i]['uname'] = $v['uname'];
                $data[$i]['data'] = $stack;
                $i++;
            }
            $count = count($data);
        }
        if ($search) {
            $newSearch = array();
            foreach ($search as $k => $v) {
                if ($v) {
                    $newSearch[$k] = $v;
                }
            }
            if ($newSearch && $data) {
                $newData = array();
                foreach ($data as $k => $v) {
                    $result = array_diff_assoc($newSearch, $v['data']);
                    if (empty($result)) {
                        $newData[] = $v;
                    }
                }
                $data = $newData;
            }
            $count = count($data);
        }
        
        if ($data && $page > 0) {
            $start = ($page - 1) * $pageSize;
            $end = $start + $pageSize;
            $pageData = array();
            for ($i = $start; $i < $end; $i++) {
                if (isset($data[$i])) {
                    $pageData[] = $data[$i];
                }
                else {
                    break;
                }
                
            }
            $data = $pageData;
        }
        return array('count' => $count, 'data' => $data);
    }
    
    /**
     * 获得自定义标签字段
     */
    public function getTagField($shop_id)
    {
        $db = $this->getDb();
        $sql = "
            SELECT DISTINCT `property`
            FROM  `sdb_taocrm_member_property`
            WHERE `shop_id` = '{$shop_id}'";
        $result = $db->select($sql);
        $fields = array();
        if ($result) {
            $stack = array();
            foreach ($result as $v) {
                $stack[] = $v['property'];
            }
            asort($stack);
            $fields[0] = self::FIRST_FIELD;
            foreach ($stack as $v) {
                $fields[] = $v;
            }
        }
        return $fields;
    }
    
    /**
     * 获得自定义类型标签字段
     */
    public function getTypeTagField($shop_id, $type)
    {
        $db = $this->getDb();
        $sql = "SELECT  DISTINCT a.`property`
                FROM  
                    `sdb_taocrm_member_property` as a
                    LEFT JOIN sdb_taocrm_members as b ON a.uname=b.uname
                WHERE
                  a.`shop_id` = '{$shop_id}'";
        if ($type == 1) {
            $sql .= " AND b.`member_id` > 0";
        }
        elseif ($type == 0) {
            $sql .= " AND b.`member_id` <= 0";
        }
        //echo($sql);
        $result = $db->select($sql);
        $fields = array();
        if ($result) {
            $stack = array();
            foreach ($result as $v) {
                $stack[] = $v['property'];
            }
            asort($stack);
            $fields[0] = self::FIRST_FIELD;
            foreach ($stack as $v) {
                $fields[] = $v;
            }
        }
        return $fields;
    }
    
    /**
     * 获得类型搜索键值对
     */
    public function getTypeSearch($shop_id, $type, $fields = array())
    {
        $db = $this->getDb();
        if (empty($fields)) {
            $fields = $this->getTypeTagField($shop_id, $type);
        }
        if (($key = array_search(self::FIRST_FIELD, $fields)) !== null) {
            unset($fields[$key]);
        }
        $data = array();
        if ($type == 1) {
            $typeSql = " AND b.`member_id` > 0";
        }
        elseif ($type == 0) {
            $typeSql .= " AND b.`member_id` <= 0";
        }
        foreach ($fields as $v) {
            $sql = "SELECT DISTINCT a.`value` 
                    FROM `sdb_taocrm_member_property` as a
                    LEFT JOIN `sdb_taocrm_members` as b
                         ON a.`uname` = b.`uname`
                    WHERE a.`shop_id` = '{$shop_id}' AND `property` = '{$v}'";
            $sql .= $typeSql;
            $result = $db->select($sql);
            if ($result) {
                foreach ($result as $v1) {
                    $data[$v][] = $v1['value'];
                }
            }
        }
        return $data;
    }
    
    /**
     * 获得搜索键值对
     */
    public function getSearch($shop_id, $fields = array())
    {
        $db = $this->getDb();
        if (empty($fields)) {
            $fields = $this->getTagField($shop_id);
        }
        if (($key = array_search(self::FIRST_FIELD, $fields)) !== null) {
            unset($fields[$key]);
        }
        
        $data = array();
        foreach ($fields as $v) {
            $sql = "SELECT DISTINCT `value` FROM `sdb_taocrm_member_property` WHERE `shop_id` = '{$shop_id}' AND `property` = '{$v}'";
            $result = $db->select($sql);
            if ($result) {
                foreach ($result as $v1) {
                    $data[$v][] = $v1['value'];
                }
            }
        }
        return $data;
    }
    
    /**
     * 获得客户信息
     */
    public function getMemberInfo($uname, $shop_id)
    {
        if ($shop_id == '') {
            $shop_id = $this->getShopId();
        }
        $model = $this->getMemberPropertyObj();
        $filter = array('shop_id' => $shop_id, 'uname' => $uname);
        $result = $model->getList('*', $filter);
        $data = array();
        if ($result) {
            
        }
        else {
            //通过接口获取旺旺属性值
        }
        //exit;
        return $data;
    }
    
    /**
     * 获得店铺对象
     */
    protected function getShopObj()
    {
        if (self::$shopObj == '') {
            self::$shopObj = &app::get(ORDER_APP)->model('shop');
        }
        return self::$shopObj;
    }
    
    /**
     * 获得绑定应用插件的对象
     */
    protected function getPlugBindObj()
    {
        if (self::$plugBindObj == '') {
            self::$plugBindObj = &app::get('taocrm')->model('app');
        }
        return self::$plugBindObj;
    }
    
    /**
     * 获得自定义属性
     */
    protected function getMemberPropertyObj()
    {
        if (self::$memberPropertyObj == '') {
            self::$memberPropertyObj = &app::get('taocrm')->model('member_property');
        }
        return self::$memberPropertyObj;
    }
    
    /**
     * 获得所有店铺ID
     */
    protected function getAllShopId()
    {
        $shopObj = $this->getShopObj();
        $shopList = $shopObj->getList('shop_id,name');
        return $shopList;
    }
    
    /**
     * 获得数据库连接句柄
     */
    protected function getDb()
    {
        if (self::$db == '') {
            self::$db = kernel::database();
        }
        return self::$db;
    }
    
    public function getTagFields($tags)
    {
        $finalTags = array();
        foreach ($tags as $tag) {
            if ($tag == self::FIRST_FIELD) {
                continue;
            }
            $finalTags[] = $tag;
        }
        return $finalTags;
    }
    
    /**
     * 获得店铺ID
     */
    protected function getShopId()
    {
        if ($this->shop_id == '') {
            $shopList = $this->getAllShopId();
            $currentShopInfo = $shopList[intval($_GET['view'])];
            if ($currentShopInfo) {
                $this->shop_id = $currentShopInfo['shop_id'];
            }
            else {
                if (isset($_GET['shop_id'])) {
                    $this->shop_id = $_GET['shop_id'];
                }
                else {
                    $this->shop_id = $shopList[0]['shop_id'];
                }
                
            }
            
        }
        return $this->shop_id;
    }
}
