<?PHP
/*
+----------------------------------------------------------------------
| SPF-简单的PHP框架 1.0 测试版
+----------------------------------------------------------------------
| Copyright (c) 2012-2016 All rights reserved.
+----------------------------------------------------------------------
| Licensed ( http:www.apache.org/licenses/LICENSE-2.0 )
+----------------------------------------------------------------------
| Author: lufeng <lufengreat@163.com>
+----------------------------------------------------------------------
| 微信在线支付处理
+----------------------------------------------------------------------
*/

$vmid || $vmid = $_REQUEST['vmid'];
$user_id = intval($_REQUEST['user_id']);
if(METHOD === 'notify'){
	$values = json_decode(json_encode(simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'],'SimpleXMLElement',LIBXML_NOCDATA)),true);
	$out_trade_no = $values['out_trade_no'];
	$order = M('school_card_pay')->where(array('out_trade_no',$out_trade_no))->find();
	$user_id = $order['user_id'];
}
$pay_cfg = get_pay_cfg($vmid,'weixin',$user_id);
foreach($pay_cfg as $cfg_name => $cfg_value) define($cfg_name,$cfg_value);
define('PATH_SSLCERT_PATH',ROOT_PWC.'api/weixin/cert/apiclient_cert.pem');
define('PATH_SSLKEY_PATH',ROOT_PWC.'api/weixin/cert/apiclient_key.pem');

class Weixin_cardAction extends Action{

	/**
	 * 流程：
	 * 1、调用统一下单，取得code_url，生成二维码
	 * 2、用户扫描二维码，进行支付
	 * 3、支付完成之后，微信服务器会通知支付成功
	 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
	 * 输入参数： vmid pacode 
	 * 返回参数： 二维码链接，由机器屏幕显示二维码
	*/
	public function qrcode(){
		global $user_id,$vmid;
		$cardid = $_REQUEST['cardid'];
		$price = intval($_REQUEST['money']);
		if($price < 1) exit('error');
		//创建本地订单
		$ar = array(
			'trade_no'		=> '',
			'trade_type'	=> 2, //微信
			'income_userid'	=> INCOME_USERID,
			'user_id'		=> $user_id,
			'cardid'		=> $cardid,
			'amount'		=> $price,
			'vmid'			=> $vmid,
			'pay_status'	=> 0,
			'createtime'	=> NOW_TIME,
			'paytime'		=> 0,
			'note'			=> '预下单'
		);
		$order_id = M('school_card_pay')->add($ar); //创建订单
		if(!is_numeric($order_id) || $order_id < 1){
			$this->output(array('ok' => 0,'msg' => '订单创建失败'));
		}
		$out_trade_no = M('school_card_pay')->get_out_trade_no($order_id); //必填 商户订单号
		$subject = '一卡通充值'; //订单名称
		$notify_url = $this->get('cfg.web_url').'weixin_notify_card.php';
		require_once(ROOT_PWC.'api/weixin/lib/WxPay.Api.php');
		require_once(ROOT_PWC.'api/weixin/example/WxPay.NativePay.php');
		require_once(ROOT_PWC.'api/weixin/example/log.php');
		$logHandler= new CLogFileHandler(ROOT_PWC.'api/weixin/logs/'.date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 8);
		Log::DEBUG("begin native_2");
		$notify = new NativePay();
		$input = new WxPayUnifiedOrder();
		$input->SetBody($subject);
		$input->SetAttach("test");
		$input->SetOut_trade_no($out_trade_no);
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 7200));
		$input->SetGoods_tag("test");
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id(10000);
		$result = $notify->GetPayUrl($input);
		if(is_array($result) && $result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS') echo $result['code_url'];
	}

	//二维码支付成功的回调页面
	public function notify(){
		include(ROOT_PWC.'api/weixin/example/notify.php');
		//初始化日志
		$logHandler= new CLogFileHandler(ROOT_PWC.'api/weixin/logs/'.date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 8);
		Log::DEBUG("begin notify");
		$notify = new PayNotifyCallBack();
		$notify->Handle(false);
		$result = $notify->get_data(); //回调结果，数组形式
		if(array_key_exists("return_code",$result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS"){
			$online_order = M('school_card_pay')->where(array('out_trade_no',$result['out_trade_no']))->find();
			if(is_array($online_order) && $online_order['pay_status'] != 1){
				$data = array(
					'pay_status'	=> 1,
					'trade_no'		=> $result['transaction_id'],
					'buyer_name'	=> $result['openid'],
					'buyer_id'		=> $result['openid'],
					'paytime'		=> NOW_TIME,
					'note'			=> '支付成功',
				);
				//修改订单状态
				$res = M('school_card_pay')->where(array('id',$online_order['id']))->save($data);
				if($res){
					//修改校园卡资金
					$where = array('cardid',$online_order['cardid']);
					$card_info = M('school_card')->where($where)->find();
					$data = array(
						'cardid'	=> $card_info['cardid'],
						'before'	=> $card_info['account'],
						'change'	=> $online_order['amount'],
						'after'		=> $card_info['account'] + $online_order['amount'],
						'note'		=> '微信充值',
						'createtime'=> NOW_TIME,
					);
					M('school_card')->where(array('cardid',$card_info['cardid']))->save(array('account' => $data['after']));
					M('school_card_log')->add($data);
					if(PAY_SELF === 0 && INCOME_USERID == 1){
						//修改用户金额
						update_user_money(USER_ID,$online_order['amount'],'校园卡微信充值,订单编号：'.$result['out_trade_no']);
					}
				}
			}
		}
	}

	//订单查询
	public function query($ret = false,$out_trade_no = ''){
		$out_trade_no = $ret ? $out_trade_no : $_REQUEST['out_trade_no'];
		require_once ROOT_PWC.'api/weixin/lib/WxPay.Api.php';
		require_once ROOT_PWC.'api/weixin/example/log.php';
		$logHandler= new CLogFileHandler(ROOT_PWC.'api/weixin/logs/'.date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 8);
		$input = new WxPayOrderQuery();
		$input->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::orderQuery($input);
		if($ret) return $result;
		echo '<table width="100%">';
		foreach($result as $k => $v){
			echo '<tr><td width="40%" align="right">'.$k.'：</td><td>'.$v.'</td></tr>';
		}
		echo '</table>';
	}

	private function output($ar){
		echo $ar['msg'];
		//echo json_encode($ar);
		exit(0);
	}
}