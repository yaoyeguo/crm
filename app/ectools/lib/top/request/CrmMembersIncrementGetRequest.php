<?php
/**
 * TOP API: taobao.crm.members.increment.get request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:21:06.0
 */
class ectools_top_request_CrmMembersIncrementGetRequest
{
	/** 
	 * 显示第几页的客户，如果输入的页码大于总共的页码数，例如总共10页，但是current_page的值为11，则返回空白页，最小页数为1
	 **/
	private $currentPage;
	
	/** 
	 * 最迟修改日期，如果不填写此字段，默认为当前时间
	 **/
	private $endModify;
	
	/** 
	 * 客户等级，1：普通客户，2：高级客户，3：高级客户 ，4：至尊vip
	 **/
	private $grade;
	
	/** 
	 * 每页显示的客户数，page_size的值不能超过100，最小值要大于1
	 **/
	private $pageSize;
	
	/** 
	 * 最早修改日期
	 **/
	private $startModify;
	
	private $apiParas = array();
	
	public function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
		$this->apiParas["current_page"] = $currentPage;
	}

	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	public function setEndModify($endModify)
	{
		$this->endModify = $endModify;
		$this->apiParas["end_modify"] = $endModify;
	}

	public function getEndModify()
	{
		return $this->endModify;
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

	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		$this->apiParas["page_size"] = $pageSize;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function setStartModify($startModify)
	{
		$this->startModify = $startModify;
		$this->apiParas["start_modify"] = $startModify;
	}

	public function getStartModify()
	{
		return $this->startModify;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.members.increment.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
