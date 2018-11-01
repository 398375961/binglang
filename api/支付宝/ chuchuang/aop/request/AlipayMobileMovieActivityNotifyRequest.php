<?php
/**
 * ALIPAY API: alipay.mobile.movie.activity.notify request
 *
 * @author auto create
 * @since 1.0, 2014-09-04 14:18:47
 */
class AlipayMobileMovieActivityNotifyRequest
{
	/** 
	 * 活动详情
	 **/
	private $activityDetail;
	
	/** 
	 * 淘宝活动id
	 **/
	private $activityId;
	
	/** 
	 * 活动状态：1(正常)；2(暂停);3(预发)
	 **/
	private $status;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	
	public function setActivityDetail($activityDetail)
	{
		$this->activityDetail = $activityDetail;
		$this->apiParas["activity_detail"] = $activityDetail;
	}

	public function getActivityDetail()
	{
		return $this->activityDetail;
	}

	public function setActivityId($activityId)
	{
		$this->activityId = $activityId;
		$this->apiParas["activity_id"] = $activityId;
	}

	public function getActivityId()
	{
		return $this->activityId;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getApiMethodName()
	{
		return "alipay.mobile.movie.activity.notify";
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
