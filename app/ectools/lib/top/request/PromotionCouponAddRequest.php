<?php
/**
 * TOP API: taobao.promotion.coupon.add request
 * 
 * @author auto create
 * @since 1.0, 2011-07-27 18:28:18.0
 */
class ectools_top_request_PromotionCouponAddRequest
{
	/** 
	 * 订单满多少元才能用这个优惠券，500就是满500元才能使用
	 **/
	private $condition;
	
	/** 
	 * 优惠券的面额，必须是3，5，10，20，50，100
	 **/
	private $denominations;
	
	/** 
	 * 优惠券的截止日期
	 **/
	private $endTime;
	
	private $startTime;
	
	private $apiParas = array();
	
	public function setCondition($condition)
	{
		$this->condition = $condition;
		$this->apiParas["condition"] = $condition;
	}

	public function getCondition()
	{
		return $this->condition;
	}

	public function setDenominations($denominations)
	{
		$this->denominations = $denominations;
		$this->apiParas["denominations"] = $denominations;
	}

	public function getDenominations()
	{
		return $this->denominations;
	}

	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		$this->apiParas["end_time"] = $endTime;
	}
    
    public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
		$this->apiParas["start_time"] = $startTime;
	}

	public function getEndTime()
	{
		return $this->endTime;
	}

	public function getApiMethodName()
	{
		return "taobao.promotion.coupon.add";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
