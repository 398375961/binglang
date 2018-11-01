<?php
/***************************************************************
 *                           免责申明
 * 此DEMO仅供参考，支付宝不对次Demo可能隐含的bug负责任，请商户开发人员谨慎使用。
 * 
 * 
 **************************************************************/
require_once 'function.inc.php';
require_once 'HttpRequst.php';
require_once 'config.php';
require_once 'AlipaySign.php';
header ( "Content-type: text/html; charset=gbk" );
/**
 * 此文件未对接支付宝服务器的网关文件，将此文件的访问路径填入支付宝服务窗的开发中验证网关的页面中。
 * 次文件接收支付宝服务器发送的请求
*/

if (get_magic_quotes_gpc ()) {
	foreach ( $_POST as $key => $value ) {
		$_POST [$key] = stripslashes ( $value );
	}
	foreach ( $_GET as $key => $value ) {
		$_GET [$key] = stripslashes ( $value );
	}
	foreach ( $_REQUEST as $key => $value ) {
		$_REQUEST [$key] = stripslashes ( $value );
	}
}

// 日志记录下受到的请求
writeLog ( "POST: " . var_export ( $_POST, true ) );
writeLog ( "GET: " . var_export ( $_GET, true ) );

$sign = HttpRequest::getRequest ( "sign" );
$sign_type = HttpRequest::getRequest ( "sign_type" );
$biz_content = HttpRequest::getRequest ( "biz_content" );
$service = HttpRequest::getRequest ( "service" );
$charset = HttpRequest::getRequest ( "charset" );

if (empty ( $sign ) || empty ( $sign_type ) || empty ( $biz_content ) || empty ( $service ) || empty ( $charset )) {
	echo "some parameter is empty.";
	writeLog ( "some parameter is empty.");
	exit ();
}

// 收到请求，先验证签名

$as = new AlipaySign ();
$sign_verify = $as->rsaCheckV2 ( $_REQUEST, $config ['alipay_public_key_file'] );
if (! $sign_verify) {
	// 如果验证网关时，请求参数签名失败，则按照标准格式返回，方便在服务窗后台查看。
	if (HttpRequest::getRequest ( "service" ) == "alipay.service.check") {
		$gw = new Gateway ();
		$gw->verifygw ( false );
	} else {
		echo "sign verfiy fail.";
		writeLog ( "sign verfiy fail.");
	}
	exit ();
}

// 验证网关请求
if (HttpRequest::getRequest ( "service" ) == "alipay.service.check") {
	// Gateway::verifygw();
	$gw = new Gateway ();
	$gw->verifygw ( true );
} else if (HttpRequest::getRequest ( "service" ) == "alipay.mobile.public.message.notify") {
	// 处理收到的消息
	require_once 'Message.php';
	$msg = new Message ( $biz_content );
}




class Gateway {
	public function verifygw($is_sign_success) {
		$biz_content = HttpRequest::getRequest ( "biz_content" );
		$as = new AlipaySign ();
		$xml = simplexml_load_string ( $biz_content );
		// print_r($xml);
		$EventType = ( string ) $xml->EventType;
		// echo $EventType;
		if ($EventType == "verifygw") {
			require 'config.php';
			// global $config;
			// print_r ( $config );
			if ($is_sign_success) {
				$response_xml = "<success>true</success><biz_content>" . $as->getPublicKeyStr ( $config ['merchant_public_key_file'] ) . "</biz_content>";
			} else { // echo $response_xml;
				$response_xml = "<success>false</success><error_code>VERIFY_FAILED</error_code><biz_content>" . $as->getPublicKeyStr ( $config ['merchant_public_key_file'] ) . "</biz_content>";
			}
			$return_xml = $as->sign_response ( $response_xml, $config ['charset'], $config ['merchant_private_key_file'] );
			writeLog ( "response_xml: " . $return_xml );
			echo $return_xml;
			exit ();
		}
	}
}
?>