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
| 校园卡支付宝 二维码充值
+----------------------------------------------------------------------
*/
$vmid || $vmid = $_REQUEST['vmid'];
$user_id = intval($_REQUEST['user_id']);
if(METHOD === 'qr_notify'){
	$out_trade_no = $_POST['out_trade_no'];
	$order = M('school_card_pay')->where(array('out_trade_no',$out_trade_no))->find();
	$user_id = $order['user_id'];
}
$pay_cfg = get_pay_cfg($vmid,'alipay',$user_id);
foreach($pay_cfg as $cfg_name => $cfg_value) define($cfg_name,$cfg_value);

class Alipay_cardAction extends Action{
	
	/**
	 * 流程：
	 * 1、调用统一下单，取得code_url，生成二维码
	 * 2、用户扫描二维码，进行支付
	 * 3、支付完成之后，支付宝服务器会通知支付成功
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
			'trade_type'	=> 1, //alipay
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
		require_once ROOT_PWC.'api/alipay/chuchuang/f2fpay/F2fpay.php';
		$f2fpay = new F2fpay();
		$total_fee = $price/100;
		$royalty_info = '';
		if(PAY_SELF == 2) $royalty_info = ALIPAY_ROAYLTY; //分账
		$notify_url = $this->get('cfg.web_url').'alipay_qrnotify_card.php';
		$response = $f2fpay->qrpay($out_trade_no,$total_fee,$subject,$royalty_info,$notify_url);
		$response = json_decode(json_encode($response),TRUE);
//var_dump($response);
		if(empty($response)){
			$ar['note'] = '支付宝下单失败';
		}elseif($response['alipay_trade_precreate_response']['code'] == 10000){
			$ar['note'] = '支付宝下单成功';
			//输出二维码链接
			echo $response['alipay_trade_precreate_response']['qr_code']; 
		}else{
			$ar['note'] = $response['alipay_trade_precreate_response']['sub_msg'];
		}
		M('school_card_pay')->where(array('id',$order_id))->save($ar);
	}

	//二维码支付回调
	public function qr_notify(){
		//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
		$out_trade_no = $_POST['out_trade_no']; //商户订单号
		$trade_no = $_POST['trade_no']; //支付宝交易号
		$trade_status = $_POST['trade_status']; //交易状态
		include(ROOT_PWC.'api/alipay/alipay.config.php');
		require_once(ROOT_PWC.'api/alipay/lib/alipay_notify.class.php');
			//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if(!$verify_result) echo "fail";
		if($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED'){
			//查询订单
			$online_order = M('school_card_pay')->where(array('out_trade_no',$out_trade_no))->find();
			if(is_array($online_order) && $online_order['pay_status'] != 1){
				$data = array(
					'pay_status'	=> 1,
					'trade_no'		=> $trade_no,
					'buyer_name'	=> $_POST['buyer_logon_id'],
					'buyer_id'		=> $_POST['open_id'],
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
						'note'		=> '支付宝充值',
						'createtime'=> NOW_TIME,
					);
					M('school_card')->where(array('cardid',$card_info['cardid']))->save(array('account' => $data['after']));
					M('school_card_log')->add($data);
					if(PAY_SELF === 0 && INCOME_USERID == 1){
						//修改用户金额
						update_user_money(USER_ID,$online_order['amount'],'校园卡支付宝充值,订单编号：'.$out_trade_no);
					}
				}
			}
			echo "success";		//请不要修改或删除
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		} else{
			//验证失败
			echo "fail";
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}

	//订单查询
	public function query($ret = false,$out_trade_no = ''){
		$out_trade_no = $ret ? $out_trade_no : $_REQUEST['out_trade_no']; //商户网站订单系统中唯一订单号，必填
		require_once ROOT_PWC.'api/alipay/chuchuang/f2fpay/F2fpay.php';
		$f2fpay = new F2fpay();
		$response = $f2fpay->query($out_trade_no);
		$result = json_decode(json_encode($response),true);
		if($ret) return $result;
		echo '<table width="100%">';
		foreach($result['alipay_trade_query_response'] as $k => $v){
			echo '<tr><td width="40%" align="right">'.$k.'：</td><td>'.(is_array($v) ? var_export($v,true): $v).'</td></tr>';
		}
		echo '</table>';
	}

	private function output($ar){
		echo $ar['msg'];
		//echo json_encode($ar);
		exit(0);
	}
}