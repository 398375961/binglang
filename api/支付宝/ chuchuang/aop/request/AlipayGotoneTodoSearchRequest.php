<?php
/**
 * ALIPAY API: alipay.gotone.todo.search request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:35
 */
class AlipayGotoneTodoSearchRequest
{
	/** 
	 * 终端类型，如Android，iPhone，iPad.
	 **/
	private $terminal;
	
	/** 
	 * 要查询的支付宝账户id
	 **/
	private $userId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	
	public function setTerminal($terminal)
	{
		$this->terminal = $terminal;
		$this->apiParas["terminal"] = $terminal;
	}

	public function getTerminal()
	{
		return $this->terminal;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		$this->apiParas["user_id"] = $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getApiMethodName()
	{
		return "alipay.gotone.todo.search";
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
