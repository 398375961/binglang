<?php
/**
 * ALIPAY API: alipay.evercall.recharge.result.update request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:38
 */
class AlipayEvercallRechargeResultUpdateRequest
{
	/** 
	 * 充值明细
	 **/
	private $rechargeItems;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	
	public function setRechargeItems($rechargeItems)
	{
		$this->rechargeItems = $rechargeItems;
		$this->apiParas["recharge_items"] = $rechargeItems;
	}

	public function getRechargeItems()
	{
		return $this->rechargeItems;
	}

	public function getApiMethodName()
	{
		return "alipay.evercall.recharge.result.update";
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
