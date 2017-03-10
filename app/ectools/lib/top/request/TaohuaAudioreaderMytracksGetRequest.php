<?php
/**
 * TOP API: taobao.taohua.audioreader.mytracks.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:37.0
 */
class TaohuaAudioreaderMytracksGetRequest
{
	/** 
	 * 当前页码
	 **/
	private $pageNo;
	
	/** 
	 * 每页个数
	 **/
	private $pageSize;
	
	/** 
	 * 购买专辑的序列ID
	 **/
	private $serialId;
	
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

	public function setSerialId($serialId)
	{
		$this->serialId = $serialId;
		$this->apiParas["serial_id"] = $serialId;
	}

	public function getSerialId()
	{
		return $this->serialId;
	}

	public function getApiMethodName()
	{
		return "taobao.taohua.audioreader.mytracks.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
