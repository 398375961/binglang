<?php
/* *
 * 功能：收单撤销接口接入页
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

商户可撤销已经存在的交易。
 如果交易不存在，直接返回撤销成功；
 如果交易存在，且交易状态为待付款，则关闭交易；
 如果交易存在，切交易状态为已付款，则对交易进行全额退款；
 如果交易存在，且交易状态为成功结束，无法进行逆向资金操作，则返回撤
销失败。

 */

require_once("alipay/alipay.config.php");
require_once("alipay/lib/alipay_submit.class.php");

/**************************请求参数**************************/

//支付宝交易号
$trade_no = $_POST['WIDtrade_no'];
//支付宝交易号与商户网站订单号不能同时为空
//商户订单号
$out_trade_no = $_POST['WIDout_trade_no'];


/************************************************************/

//构造要请求的参数数组，无需改动
$parameter = array(
		"service" => "alipay.acquire.cancel",
		"partner" => trim($alipay_config['partner']),
		"trade_no"	=> $trade_no,
		"out_trade_no"	=> $out_trade_no,
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
//解析XML
if($result['ok']){
	//请求成功
	if($result['result_code'] == 'SUCCESS'){
		//撤单成功
	}else{
		//撤单失败
		echo $result['detail_error_code']; //错误提示
		echo $result['detail_error_des']; //错误提示
	}
}else{
	//请求失败
}
//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——