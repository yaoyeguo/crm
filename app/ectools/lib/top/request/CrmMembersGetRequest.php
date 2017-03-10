<?php
/**
 * TOP API: taobao.crm.members.get request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:33:46.0
 */
class CrmMembersGetRequest
{
	/** 
	 * 买家的昵称
	 **/
	private $buyerNick;
	
	/** 
	 * 显示第几页的客户，如果输入的页码大于总共的页码数，例如总共10页，但是current_page的值为11，则返回空白页，最小页数为1
	 **/
	private $currentPage;
	
	/** 
	 * 客户等级，1：普通客户，2：高级客户，3：高级客户， 4：至尊vip
	 **/
	private $grade;
	
	/** 
	 * 最迟上次交易时间
	 **/
	private $maxLastTradeTime;
	
	/** 
	 * 最大交易额，单位为元
	 **/
	private $maxTradeAmount;
	
	/** 
	 * 最大交易量
	 **/
	private $maxTradeCount;
	
	/** 
	 * 最早上次交易时间
	 **/
	private $minLastTradeTime;
	
	/** 
	 * 最小交易额,单位为元
	 **/
	private $minTradeAmount;
	
	/** 
	 * 最小交易量
	 **/
	private $minTradeCount;
	
	/** 
	 * 表示每页显示的客户数量,page_size的最大值不能超过100条,最小值不能低于1，
	 **/
	private $pageSize;
	
	private $apiParas = array();
	
	public function setBuyerNick($buyerNick)
	{
		$this->buyerNick = $buyerNick;
		$this->apiParas["buyer_nick"] = $buyerNick;
	}

	public function getBuyerNick()
	{
		return $this->buyerNick;
	}

	public function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
		$this->apiParas["current_page"] = $currentPage;
	}

	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	public function setGrade($grade)
	{
		$this->grade = $grade;
		$this->apiParas["grade"] = $grade;
	}

	public function getGrade()
	{
		return $this->grade;
	}

	public function setMaxLastTradeTime($maxLastTradeTime)
	{
		$this->maxLastTradeTime = $maxLastTradeTime;
		$this->apiParas["max_last_trade_time"] = $maxLastTradeTime;
	}

	public function getMaxLastTradeTime()
	{
		return $this->maxLastTradeTime;
	}

	public function setMaxTradeAmount($maxTradeAmount)
	{
		$this->maxTradeAmount = $maxTradeAmount;
		$this->apiParas["max_trade_amount"] = $maxTradeAmount;
	}

	public function getMaxTradeAmount()
	{
		return $this->maxTradeAmount;
	}

	public function setMaxTradeCount($maxTradeCount)
	{
		$this->maxTradeCount = $maxTradeCount;
		$this->apiParas["max_trade_count"] = $maxTradeCount;
	}

	public function getMaxTradeCount()
	{
		return $this->maxTradeCount;
	}

	public function setMinLastTradeTime($minLastTradeTime)
	{
		$this->minLastTradeTime = $minLastTradeTime;
		$this->apiParas["min_last_trade_time"] = $minLastTradeTime;
	}

	public function getMinLastTradeTime()
	{
		return $this->minLastTradeTime;
	}

	public function setMinTradeAmount($minTradeAmount)
	{
		$this->minTradeAmount = $minTradeAmount;
		$this->apiParas["min_trade_amount"] = $minTradeAmount;
	}

	public function getMinTradeAmount()
	{
		return $this->minTradeAmount;
	}

	public function setMinTradeCount($minTradeCount)
	{
		$this->minTradeCount = $minTradeCount;
		$this->apiParas["min_trade_count"] = $minTradeCount;
	}

	public function getMinTradeCount()
	{
		return $this->minTradeCount;
	}

	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		$this->apiParas["page_size"] = $pageSize;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.members.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
