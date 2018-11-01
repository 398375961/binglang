<?php
/**
 * ALIPAY API: alipay.ebpp.recharge.item.update request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:48
 */
class AlipayEbppRechargeItemUpdateRequest
{
	/** 
	 * 测试
	 **/
	private $cardNo;
	
	/** 
	 * 是否销售
	 **/
	private $isForSale;
	
	/** 
	 * 货架id
	 **/
	private $itemCode;
	
	/** 
	 * 商品类型
	 **/
	private $itemCodeType;
	
	/** 
	 * 业务类型
	 **/
	private $orderType;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	
	public function setCardNo($cardNo)
	{
		$this->cardNo = $cardNo;
		$this->apiParas["card_no"] = $cardNo;
	}

	public function getCardNo()
	{
		return $this->cardNo;
	}

	public function setIsForSale($isForSale)
	{
		$this->isForSale = $isForSale;
		$this->apiParas["is_for_sale"] = $isForSale;
	}

	public function getIsForSale()
	{
		return $this->isForSale;
	}

	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		$this->apiParas["item_code"] = $itemCode;
	}

	public function getItemCode()
	{
		return $this->itemCode;
	}

	public function setItemCodeType($itemCodeType)
	{
		$this->itemCodeType = $itemCodeType;
		$this->apiParas["item_code_type"] = $itemCodeType;
	}

	public function getItemCodeType()
	{
		return $this->itemCodeType;
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

	public function getApiMethodName()
	{
		return "alipay.ebpp.recharge.item.update";
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
