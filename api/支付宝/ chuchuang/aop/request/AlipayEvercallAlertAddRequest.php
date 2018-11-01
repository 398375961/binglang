<?php
/**
 * ALIPAY API: alipay.evercall.alert.add request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:41
 */
class AlipayEvercallAlertAddRequest
{
	/** 
	 * 预警明细
	 **/
	private $alertItems;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	
	public function setAlertItems($alertItems)
	{
		$this->alertItems = $alertItems;
		$this->apiParas["alert_items"] = $alertItems;
	}

	public function getAlertItems()
	{
		return $this->alertItems;
	}

	public function getApiMethodName()
	{
		return "alipay.evercall.alert.add";
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getTerminalType()
	{
		return $this->terminalType;
	}

	public function setTerminalType($terminalType)
	{
		$this->terminalType = $terminalType;
	}

	public function getTerminalInfo()
	{
		return $this->terminalInfo;
	}

	public function setTerminalInfo($terminalInfo)
	{
		$this->terminalInfo = $terminalInfo;
	}

	public function getProdCode()
	{
		return $this->prodCode;
	}

	public function setProdCode($prodCode)
	{
		$this->prodCode = $prodCode;
	}

	public function setApiVersion($apiVersion)
	{
		$this->apiVersion=$apiVersion;
	}

	public function getApiVersion()
	{
		return $this->apiVersion;
	}

}
