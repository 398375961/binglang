<?php
/**
 * ALIPAY API: alipay.ebpp.config.product.search request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:53
 */
class AlipayEbppConfigProductSearchRequest
{
	/** 
	 * 出账机构例如杭州电力HZELECTRIC
	 **/
	private $chargeInst;
	
	/** 
	 * 获取场景，如query或者是confirm
	 **/
	private $fieldScene;
	
	/** 
	 * 产品业务类型如缴费：JF
	 **/
	private $orderType;
	
	/** 
	 * 产品子业务类型如水费WATER
	 **/
	private $subOrderType;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	
	public function setChargeInst($chargeInst)
	{
		$this->chargeInst = $chargeInst;
		$this->apiParas["charge_inst"] = $chargeInst;
	}

	public function getChargeInst()
	{
		return $this->chargeInst;
	}

	public function setFieldScene($fieldScene)
	{
		$this->fieldScene = $fieldScene;
		$this->apiParas["field_scene"] = $fieldScene;
	}

	public function getFieldScene()
	{
		return $this->fieldScene;
	}

	public function setOrderType($orderType)
	{
		$this->orderType = $orderType;
		$this->apiParas["order_type"] = $orderType;
	}

	public function getOrderType()
	{
		return $this->orderType;
	}

	public function setSubOrderType($subOrderType)
	{
		$this->subOrderType = $subOrderType;
		$this->apiParas["sub_order_type"] = $subOrderType;
	}

	public function getSubOrderType()
	{
		return $this->subOrderType;
	}

	public function getApiMethodName()
	{
		return "alipay.ebpp.config.product.search";
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
