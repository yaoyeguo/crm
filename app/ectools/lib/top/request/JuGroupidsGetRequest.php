<?php
/**
 * TOP API: taobao.ju.groupids.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:50.0
 */
class JuGroupidsGetRequest
{
	/** 
	 * 分页获取团信息页序号，代表第几页
	 **/
	private $pageNo;
	
	/** 
	 * 每次获取团id列表的数量
	 **/
	private $pageSize;
	
	/** 
	 * IPHONE,WAP,ANDROID,SINA,163 各种终端类型
	 **/
	private $terminalType;
	
	private $apiParas = array();
	
	public function setPageNo($pageNo)
	{
		$this->pageNo = $pageNo;
		$this->apiParas["page_no"] = $pageNo;
	}

	public function getPageNo()
	{
		return $this->pageNo;
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

	public function setTerminalType($terminalType)
	{
		$this->terminalType = $terminalType;
		$this->apiParas["terminal_type"] = $terminalType;
	}

	public function getTerminalType()
	{
		return $this->terminalType;
	}

	public function getApiMethodName()
	{
		return "taobao.ju.groupids.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
