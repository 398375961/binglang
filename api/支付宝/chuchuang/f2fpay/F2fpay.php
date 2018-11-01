<?php
require_once ROOT_PWC.'api/alipay/chuchuang/config.php';
require_once ROOT_PWC.'api/alipay/chuchuang/AopSdk.php';
require_once ROOT_PWC.'api/alipay/chuchuang/function.inc.php';
class F2fpay {
	public function barpay($out_trade_no, $auth_code, $total_amount, $subject,$royalty_info = ''){
		date_default_timezone_set('Asia/Shanghai');
		$time_expire = date('Y-m-d H:i:s', time()+3600);
		$parms = array(
			'out_trade_no'	=> $out_trade_no,
			'scene'			=> 'bar_code',
			'auth_code'		=> $auth_code,
			'total_amount'	=> (string)$total_amount,
			'subject'		=> $subject,
			'time_expire'	=> $time_expire
		);
		if($royalty_info) $parms['seller_id'] = $royalty_info; //实际收款账号？
		//https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7386797.0.0.ZDx4v5&treeId=26&articleId=103258&docType=1
		$biz_content = json_encode($parms);
//echo $biz_content;
//echo '<br/>';
		$request = new AlipayTradePayRequest();
		$request->setBizContent ($biz_content);
		$response = aopclient_request_execute($request);
		return $response;
	}
	
	
	public function qrpay($out_trade_no,$total_amount,$subject,$royalty_info = '',$notify_url = '') {
		date_default_timezone_set('Asia/Shanghai');
		$time_expire = date('Y-m-d H:i:s', time()+60*60);
		$parms = array(
			'out_trade_no'	=> $out_trade_no,
			'total_amount'	=> (string)$total_amount,
			'subject'		=> $subject,
			'time_expire'	=> $time_expire
		);
		//https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7386797.0.0.ZDx4v5&treeId=26&articleId=103258&docType=1
		if($royalty_info) $parms['seller_id'] = $royalty_info; //实际收款账号？
		$biz_content = json_encode($parms);
		$request = new AlipayTradePrecreateRequest();
		$request->setBizContent ( $biz_content );
		$response = aopclient_request_execute($request,null,$notify_url);
		return $response;
	}
	
	
	public function query($out_trade_no) {	
		$biz_content="{\"out_trade_no\":\"" . $out_trade_no . "\"}";
		$request = new AlipayTradeQueryRequest();
		$request->setBizContent ( $biz_content );
		$response = aopclient_request_execute ( $request );
		return $response;
	}
	
	
	public function cancel($out_trade_no) {
		$biz_content="{\"out_trade_no\":\"" . $out_trade_no . "\"}";
		$request = new AlipayTradeCancelRequest();
		$request->setBizContent ( $biz_content );
		$response = aopclient_request_execute ( $request );
		return $response;
	}
	
	public function refund($trade_no,$refund_amount, $out_request_no) {
		$biz_content = "{\"trade_no\":\"". $trade_no . "\",\"refund_amount\":\"". $refund_amount
		. "\",\"out_request_no\":\"". $out_request_no
		."\",\"refund_reason\":\"reason\",\"store_id\":\"store001\",\"terminal_id\":\"terminal001\"}";
		$request = new AlipayTradeRefundRequest();
		$request->setBizContent ( $biz_content );
		$response = aopclient_request_execute ( $request );
		return $response;
	}
}