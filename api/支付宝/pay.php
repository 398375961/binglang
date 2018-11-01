<?php
/* *
 * 功能：统一下单并支付接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */
header('content-type:text/html;charset=UTF-8');
require_once("alipay/alipay.config.php");
require_once("alipay/lib/alipay_submit.class.php");

/**************************请求参数**************************/

//卖家支付宝帐户
//$seller_email = $_POST['WIDseller_email'];
$seller_email = 'jinjizhineng1@163.com';
//必填
//商户订单号
$out_trade_no = $_POST['WIDout_trade_no'];
//商户网站订单系统中唯一订单号，必填
//订单名称
$subject = $_POST['WIDsubject'];
//必填
//付款金额
$total_fee = $_POST['WIDtotal_fee'];
//必填
//订单业务类型
//$product_code = $_POST['WIDproduct_code'];
$product_code = 'BARCODE_PAY_OFFLINE';
//SOUNDWAVE_PAY_OFFLINE：声波支付，FINGERPRINT_FAST_PAY：指纹支付，BARCODE_PAY_OFFLINE：条码支付；商户代扣：GENERAL_WITHHOLDING
//动态ID类型
//$dynamic_id_type = $_POST['WIDdynamic_id_type'];
$dynamic_id_type = 'qr_code';
//wave_code：声波，qr_code：二维码，bar_code：条码
//动态ID
$dynamic_id = $_POST['WIDdynamic_id'];
//例如3856957008a73b7d

//协议支付信息
//$agreement_info = $_POST['WIDagreement_info'];
$agreement_info = '';
//商户代扣不可空，json格式

/************************************************************/

//构造要请求的参数数组，无需改动
$parameter = array(
		"service"			=> "alipay.acquire.createandpay",
		"partner"			=> trim($alipay_config['partner']),
		"seller_email"		=> $seller_email,
		"out_trade_no"		=> $out_trade_no,
		"subject"			=> $subject,
		"total_fee"			=> $total_fee,
		"product_code"		=> $product_code,
		"dynamic_id_type"	=> $dynamic_id_type,
		"dynamic_id"		=> $dynamic_id,
		"agreement_info"	=> $agreement_info,
		"notify_url"		=> NOTIFY_URL,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestHttp($parameter);
//解析XML
//注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
//请在这里加上商户的业务逻辑程序代码

//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

//解析XML
$result = parse_xml($html_text);
if($result['ok']){
	if($result['result_code'] == 'ORDER_SUCCESS_PAY_SUCCESS'){
		//下单成功且，支付成功，通知售货机发货
		echo $result['trade_no']; //支付宝交易号
		echo $result['out_trade_no']; //商户网站唯一订单号
		echo $result['buyer_user_id']; //买家支付宝账号对应的支付宝唯一用户号。以2088开头的纯16位数字。
		echo $result['buyer_logon_id']; //买家支付宝账号，可以为email或者手机号。对部分信息进行了隐藏。
	}else{
		//ORDER_FAIL  ORDER_SUCCESS_PAY_FAIL ORDER_SUCCESS_PAY_INPROCESS 下单成功支付处理中 UNKNOWN 处理结果未知
		echo $result['detail_error_code']; //错误提示代码
		echo $result['detail_error_des']; //错误提示
	}
}
//——请根据您的业务逻辑来编写程序（以上代码仅作参考）—
?>