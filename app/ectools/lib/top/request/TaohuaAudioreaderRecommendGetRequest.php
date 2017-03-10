<?php
/**
 * TOP API: taobao.taohua.audioreader.recommend.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:51.0
 */
class TaohuaAudioreaderRecommendGetRequest
{
	/** 
	 * 推荐专辑的类型，有两个可选项，recent:最近更新，hot:热门
	 **/
	private $itemType;
	
	/** 
	 * 当前页码
	 **/
	private $pageNo;
	
	/** 
	 * 每页个数
	 **/
	private $pageSize;
	
	private $apiParas = array();
	
	public function setItemType($itemType)
	{
		$this->itemType = $itemType;
		$this->apiParas["item_type"] = $itemType;
	}

	public function getItemType()
	{
		return $this->itemType;
	}

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
		return "taobao.taohua.audioreader.recommend.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
