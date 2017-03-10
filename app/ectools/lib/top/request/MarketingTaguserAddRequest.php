<?php
/**
 * TOP API: taobao.marketing.taguser.add request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:52.0
 */
class MarketingTaguserAddRequest
{
	/** 
	 * 淘宝客户昵称
	 **/
	private $nick;
	
	/** 
	 * 标签ID
	 **/
	private $tagId;
	
	private $apiParas = array();
	
	public function setNick($nick)
	{
		$this->nick = $nick;
		$this->apiParas["nick"] = $nick;
	}

	public function getNick()
	{
		return $this->nick;
	}

	public function setTagId($tagId)
	{
		$this->tagId = $tagId;
		$this->apiParas["tag_id"] = $tagId;
	}

	public function getTagId()
	{
		return $this->tagId;
	}

	public function getApiMethodName()
	{
		return "taobao.marketing.taguser.add";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
