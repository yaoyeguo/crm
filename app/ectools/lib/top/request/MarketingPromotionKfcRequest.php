<?php
/**
 * TOP API: taobao.marketing.promotion.kfc request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:48.0
 */
class MarketingPromotionKfcRequest
{
	/** 
	 * 活动描述
	 **/
	private $promotionDesc;
	
	/** 
	 * 活动名称
	 **/
	private $promotionTitle;
	
	private $apiParas = array();
	
	public function setPromotionDesc($promotionDesc)
	{
		$this->promotionDesc = $promotionDesc;
		$this->apiParas["promotion_desc"] = $promotionDesc;
	}

	public function getPromotionDesc()
	{
		return $this->promotionDesc;
	}

	public function setPromotionTitle($promotionTitle)
	{
		$this->promotionTitle = $promotionTitle;
		$this->apiParas["promotion_title"] = $promotionTitle;
	}

	public function getPromotionTitle()
	{
		return $this->promotionTitle;
	}

	public function getApiMethodName()
	{
		return "taobao.marketing.promotion.kfc";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
