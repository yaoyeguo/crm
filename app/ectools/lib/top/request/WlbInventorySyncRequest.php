<?php
/**
 * TOP API: taobao.wlb.inventory.sync request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:42.0
 */
class WlbInventorySyncRequest
{
	/** 
	 * 商品ID
	 **/
	private $itemId;
	
	/** 
	 * 外部实体类型.存如下值 
IC_ITEM --表示IC商品; 
IC_SKU --表示IC最小单位商品;
WLB_ITEM  --表示WLB商品.
若值不在范围内，则按WLB_ITEM处理
	 **/
	private $itemType;
	
	/** 
	 * 库存数量
	 **/
	private $quantity;
	
	private $apiParas = array();
	
	public function setItemId($itemId)
	{
		$this->itemId = $itemId;
		$this->apiParas["item_id"] = $itemId;
	}

	public function getItemId()
	{
		return $this->itemId;
	}

	public function setItemType($itemType)
	{
		$this->itemType = $itemType;
		$this->apiParas["item_type"] = $itemType;
	}

	public function getItemType()
	{
		return $this->itemType;
	}

	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		$this->apiParas["quantity"] = $quantity;
	}

	public function getQuantity()
	{
		return $this->quantity;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.inventory.sync";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
