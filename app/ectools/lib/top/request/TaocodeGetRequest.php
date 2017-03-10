<?php
/**
 * TOP API: taobao.taocode.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:31.0
 */
class TaocodeGetRequest
{
	/** 
	 * 淘代码详情
	 **/
	private $taoCode;
	
	private $apiParas = array();
	
	public function setTaoCode($taoCode)
	{
		$this->taoCode = $taoCode;
		$this->apiParas["tao_code"] = $taoCode;
	}

	public function getTaoCode()
	{
		return $this->taoCode;
	}

	public function getApiMethodName()
	{
		return "taobao.taocode.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
