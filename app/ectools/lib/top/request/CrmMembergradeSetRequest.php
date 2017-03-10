<?php
/**
 * TOP API: taobao.crm.membergrade.set request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:26:48.0
 */
class CrmMembergradeSetRequest
{
	/** 
	 * 买家昵称
	 **/
	private $buyerNick;
	
	/** 
	 * 买家客户级别有四种1：普通客户。2：高级客户。 3VIP客户。 4：至尊VIP
	 **/
	private $grade;
	
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

	public function setGrade($grade)
	{
		$this->grade = $grade;
		$this->apiParas["grade"] = $grade;
	}

	public function getGrade()
	{
		return $this->grade;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.membergrade.set";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
