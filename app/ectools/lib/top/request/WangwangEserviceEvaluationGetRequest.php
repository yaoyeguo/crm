<?php
/**
 * TOP API: taobao.wangwang.eservice.evaluation.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:40.0
 */
class WangwangEserviceEvaluationGetRequest
{
	/** 
	 * 查询结束日期
	 **/
	private $endDate;
	
	/** 
	 * 客服人员id：cntaobao+淘宝nick，例如cntaobaotest
	 **/
	private $serviceStaffId;
	
	/** 
	 * 查询开始日期
	 **/
	private $startDate;
	
	private $apiParas = array();
	
	public function setEndDate($endDate)
	{
		$this->endDate = $endDate;
		$this->apiParas["end_date"] = $endDate;
	}

	public function getEndDate()
	{
		return $this->endDate;
	}

	public function setServiceStaffId($serviceStaffId)
	{
		$this->serviceStaffId = $serviceStaffId;
		$this->apiParas["service_staff_id"] = $serviceStaffId;
	}

	public function getServiceStaffId()
	{
		return $this->serviceStaffId;
	}

	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
		$this->apiParas["start_date"] = $startDate;
	}

	public function getStartDate()
	{
		return $this->startDate;
	}

	public function getApiMethodName()
	{
		return "taobao.wangwang.eservice.evaluation.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
