<?php
/**
 * TOP API: taobao.taohua.audioreader.myalbums.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:36.0
 */
class TaohuaAudioreaderMyalbumsGetRequest
{
	/** 
	 * 当前页码
	 **/
	private $pageNo;
	
	/** 
	 * 每页个数
	 **/
	private $pageSize;
	
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

	public function getApiMethodName()
	{
		return "taobao.taohua.audioreader.myalbums.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
