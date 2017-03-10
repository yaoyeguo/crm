<?php
/**
 *  db.class.php 数据库操作类
 *
 * @copyright			(C) 2005-2010 PHPCMS
 * @license				http://www.phpcms.cn/license/
 * @lastmodify			2010-6-1
 */

class taoapi_dbact extends dbeav_model{
	
	/**
	 * 数据库配置信息
	 */
	private $db = null;
	
	/**
	 * 数据库连接资源句柄
	 */
	public $db_prefix = 'sdb_taoapi_';
	
	public function __construct() {
        global $db,$db_prefix;
        $this -> db = $db;
        $this -> db_prefix = $db_prefix;
	}
	
    /**
	 * 保存商品
	 */
    public function saveItem($item){
        if(is_object($item)) $item = get_object_vars($item);
        $arr = $item;
        $arr['local_modified'] = date('Y-m-d H:i:s');
        $arr['active'] = 1;
        $this -> db -> update($arr,$this -> db_prefix.'item','num_iid='.$arr['num_iid']);
        if (0 == $this -> db -> affected_rows()) {
            $this -> db -> insert($arr,$this -> db_prefix.'item');
        }
    }

    /**
	 * 保存商品SKU
	 */
    public function saveItemSku($item_sku){
        if(is_object($item_sku)) $item_sku = get_object_vars($item_sku);
        $arr = $item_sku;
        $arr['local_modified'] = date('Y-m-d H:i:s');
        $arr['active'] = 1;
        $this -> db -> update($arr,$this -> db_prefix.'item_sku','sku_id='.$arr['sku_id']);
        if (0 == $this -> db -> affected_rows()) {
            $this -> db -> insert($arr,$this -> db_prefix.'item_sku');
        }
    }

    /**
	 * 保存店铺自定义分类
	 */
    public function saveSellerCat($seller_cat,$nick){
        if(is_object($seller_cat)) $seller_cat = get_object_vars($seller_cat);
        $arr = $seller_cat;
        $arr['local_modified'] = date('Y-m-d H:i:s');
        $arr['active'] = 1;
        $arr['seller_nick'] = $nick;
        $this -> db -> update($arr,$this -> db_prefix.'seller_cat','cid='.$arr['cid']);
        if (0 == $this -> db -> affected_rows()) {
            $this -> db -> insert($arr,$this -> db_prefix.'seller_cat');
        }
    }
    
    /**
	 * 保存淘宝商品分类
	 */
    public function saveItemCat($seller_cat,$nick){
        if(is_object($seller_cat)) $seller_cat = get_object_vars($seller_cat);
        $arr = $seller_cat;
        $arr['local_modified'] = date('Y-m-d H:i:s');
        $arr['active'] = 1;
        $arr['seller_nick'] = $nick;
        $this -> db -> update($arr,$this -> db_prefix.'seller_cat','cid='.$arr['cid']);
        if (0 == $this -> db -> affected_rows()) {
            $this -> db -> insert($arr,$this -> db_prefix.'seller_cat');
        }
    }

    /**
	 * 保存商品属性
	 */
    public function saveItemProp($seller_cat,$nick){
        if(is_object($seller_cat)) $seller_cat = get_object_vars($seller_cat);
        $arr = $seller_cat;
        $arr['local_modified'] = date('Y-m-d H:i:s');
        $arr['active'] = 1;
        $arr['seller_nick'] = $nick;
        $this -> db -> update($arr,$this -> db_prefix.'seller_cat','cid='.$arr['cid']);
        if (0 == $this -> db -> affected_rows()) {
            $this -> db -> insert($arr,$this -> db_prefix.'seller_cat');
        }
    }
    
    /**
	 * 保存商品属性值
	 */
    public function savePropValue($seller_cat,$nick){
        if(is_object($seller_cat)) $seller_cat = get_object_vars($seller_cat);
        $arr = $seller_cat;
        $arr['local_modified'] = date('Y-m-d H:i:s');
        $arr['active'] = 1;
        $arr['seller_nick'] = $nick;
        $this -> db -> update($arr,$this -> db_prefix.'seller_cat','cid='.$arr['cid']);
        if (0 == $this -> db -> affected_rows()) {
            $this -> db -> insert($arr,$this -> db_prefix.'seller_cat');
        }
    }
    
    /**
	 * 显示错误
	 * @param $message
	 */
    public function halt($message = '', $sql = '') {
		$this->errormsg = "<b style='color:red;'>error message</b><br/> $message <br /><a href='http://open.taobao.com/dev/index.php/错误码一览表' target='_blank' style='color:red'>Need Help?</a>";
		$msg = $this->errormsg;
			echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
			exit;
	}
}