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
| 支付宝在线支付处理
| 2015-12-30 条码支付改成统一
+----------------------------------------------------------------------
*/
$vmid || $vmid = $_REQUEST['vmid'];
if(METHOD === 'notify' || METHOD === 'qr_notify'){
	$out_trade_no = $_POST['out_trade_no'];
	$order = M('online_order')->where(array('out_trade_no',$out_trade_no))->find();
	$vmid = $order['vmid'];
}
$pay_cfg = get_pay_cfg($vmid,'alipay');
foreach($pay_cfg as $cfg_name => $cfg_value) define($cfg_name,$cfg_value);

class AlipayAction extends Action{

	//老版单片机系统需升级才能使用
	protected function before(){
		if($_REQUEST['vmid']){
			$item = M('status')->fields('version')->where(['vmid',$_REQUEST['vmid']])->find();
			if(preg_match('/^v1\.[\d]{4}$/i',$item['version'])) exit(0);
		}
	}

	/*
	* 网关数据处理
	* 请求参数  vmid机器id amount金额 saleid交易号 dynamic_id 条码号 time时间（机器发生交易的时间）
	*/
	public function index(){
		set_time_limit(60);
		$vmid = $_REQUEST['vmid'];
		$amount = $_REQUEST['amount'];
		if(substr($amount,0,1) == '0') $amount = substr($amount,1);
		$amount = $amount*PAY_ZHEKOU;
		$saleid = $_REQUEST['saleid'];
		$dynamic_id = $_REQUEST['dynamic_id']; //动态id,条码id
		$saletime = $_REQUEST['saletime']; //销售时间
		$pacode = $_REQUEST['pacode']; //货道编号
		//验证机器是否存在
		$machine = M('machine')->where(array('vmid',$vmid))->find();
		if(!is_array($machine) || sizeof($machine) == 0) $this->output(array('ok' => 0,'msg' => '机器['.$vmid.']不存在'));
		if(empty($dynamic_id)) $this->output(array('ok' => 0,'msg' => '条码不能为空！'));
		if(empty($saleid)) $this->output(array('ok' => 0,'msg' => '交易编号不能为空'));
		//查询商品信息
		$where = array(
			array('vmid',$vmid),
			array('pacode',$pacode)
		);
		$road = M('road')->where($where)->find();
		//创建本地订单
		$ar = array(
			'vmid'			=> $vmid, //机器id
			'pacode'		=> $pacode, //货道编号
			'dynamic_id'	=> $dynamic_id, //动态验证码，条码
			'trade_no'		=> '', //支付宝交易号
			'trade_type'	=> 'alipay', //交易方式
			'amount'		=> $amount, //总价
			'goods_id'		=> $road['goods_id'],
			'goods_name'	=> $road['goods_name'],
			'saleid'		=> $saleid, //交易编号
			'pay_status'	=> 0, //支付状态
			'trade_status'	=> 0, //交易状态
			'buyer_id'		=> '', //购买者id，如支付宝id
			'buyer_name'	=> '', //购买者姓名，如支付宝账号
			'saletime'		=> $saletime, //机器交易时间
			'createtime'	=> NOW_TIME,
			'user_id'		=> USER_ID,
			'income_userid'	=> INCOME_USERID,
		);
		$order_id = M('online_order')->add($ar); //创建订单
		if(!is_numeric($order_id) || $order_id < 1){
			$this->output(array('ok' => 0,'msg' => '订单创建失败'));
		}
		$out_trade_no = M('online_order')->get_out_trade_no($order_id); //必填 商户订单号
		$subject = $road['goods_name'].'-自动售货机交易'; //订单名称
		$total_fee = $amount/100; //必填 付款金额
		$royalty_info = '';
		if(PAY_SELF == 2) $royalty_info = ALIPAY_ROAYLTY; //分账
		require_once ROOT_PWC.'api/alipay/chuchuang/f2fpay/F2fpay.php';
		$f2fpay = new F2fpay();
		$response = $f2fpay->barpay($out_trade_no, $dynamic_id, $total_fee, $subject,$royalty_info);
		$result = json_decode(json_encode($response),TRUE);
		if($result['alipay_trade_pay_response']['code'] == 10000 || $result['alipay_trade_pay_response']['code'] == 10003){
			if($result['alipay_trade_pay_response']['msg'] == 'Success'){
				//下单成功且，支付成功，通知售货机发货
				$data = array(
					'pay_status'	=> 1,
					'paytime'		=> time(),
					'finishtime'	=> time(),
					'trade_no'		=> $result['alipay_trade_pay_response']['trade_no'], //支付宝交易号
					'buyer_id'		=> $result['alipay_trade_pay_response']['buyer_user_id'], //买家支付宝账号对应的支付宝唯一用户号。以2088开头的纯16位数字。
					'buyer_name'	=> $result['alipay_trade_pay_response']['buyer_logon_id'], //买家支付宝账号，可以为email或者手机号。对部分信息进行了隐藏。
					'note'			=> '下单成功，支付成功'
				);
				M('online_order')->where(array('id',$order_id))->save($data);
				if(PAY_SELF === 0 && INCOME_USERID == 1){
					//修改用户金额
					update_user_money(USER_ID,$amount,'销售收入，订单编号：'.$out_trade_no);
				}
				if(USER_ID != MACHINE_UID){
					//修改用户金额
					update_user_money_2(MACHINE_UID,$amount,'销售收入，订单编号：'.$out_trade_no);
				}
				$order = M('online_order')->where(array('id',$order_id))->find();
				$this->notify_machine($order);
				$this->output(array('ok' => 1,'msg' => '下单成功，支付成功','order_id' => $order_id));	
			}else{
				$data = array(
					'trade_no'		=> $result['alipay_trade_pay_response']['trade_no'], //支付宝交易号
					'buyer_id'		=> $result['alipay_trade_pay_response']['buyer_user_id'], //买家支付宝账号对应的支付宝唯一用户号。以2088开头的纯16位数字。
					'buyer_name'	=> $result['alipay_trade_pay_response']['buyer_logon_id'], //买家支付宝账号，可以为email或者手机号。对部分信息进行了隐藏。
					'note'			=> '下单成功，支付处理中'
				);
				M('online_order')->where(array('id',$order_id))->save($data);
				//下单成功支付处理中
				for($ii = 0; $ii < 9;$ii++){
					sleep(3);
					$result = $this->query(true,$out_trade_no);
					if($result['alipay_trade_query_response']['trade_status'] == 'TRADE_SUCCESS'){
						//支付成功
						$data = array(
							'pay_status'	=> 1,
							'paytime'		=> time(),
							'finishtime'	=> time(),
							'note'			=> '下单成功，支付成功'
						);
						M('online_order')->where(array('id',$order_id))->save($data);
						$ar = array(
							'vmid'			=> $vmid,
							'currency'		=> 4,//0：硬币 1：纸币 2: 普通卡 3：学生卡 4 支付宝
							'amount'		=> $amount,//面值
							'time'			=> time(), //销售时间
							'payments'		=> 0,//0：收币1：找零2：吞币
							'num'			=> 1,//数量
							'saleid'		=> $saleid,//交易号
							'card'			=> '', //卡号
							'coinchannel'	=> '',
							'createtime'	=> time(),//传输时间
							'saledate'		=> date('Ymd',$saletime),//销售日期
							'createdate' 	=> date('Ymd') //传输日期
						);
						M('pay')->add($ar);
						if(PAY_SELF === 0 && INCOME_USERID == 1){
							//修改用户金额
							update_user_money(USER_ID,$amount,'销售收入，订单编号：'.$out_trade_no);
						}
						if(USER_ID != MACHINE_UID){
							//修改用户金额
							update_user_money_2(MACHINE_UID,$amount,'销售收入，订单编号：'.$out_trade_no);
						}
						$order = M('online_order')->where(array('id',$order_id))->find();
						$this->notify_machine($order);
						return;
					}
				}
				//还没有支付成功，则撤销订单
				$f2fpay->cancel($out_trade_no);
			}
		}else{
			//请求失败
			M('online_order')->where(array('id',$order_id))->save(array('note' => '请求支付宝失败！'));
			var_dump($response);
			$this->output(array('ok' => 0,'msg' => '请求支付宝失败！','order_id' => $order_id));
		}
	}

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
		$vmid = $_REQUEST['vmid'];
/*
if($vmid !== '0000088888'){
	$status = M('status')->where(array('vmid',$vmid))->find();
	if(in_array($status['version'],array('V1.1003','V1.1004','V1.0004','V1.0005','V1.0006','V1.0007','V1.2005','V1.2006'))) return '';
}
if($vmid !== '0000001005'){
	return '';
}*/
		$pacode = $_REQUEST['pacode'];
		$price = intval($_REQUEST['price']);
		$road = M('road')->where(array(array('vmid',$vmid),array('pacode',$pacode)))->find();
		if(!is_array($road)) $road = array();
		$price = $price > 0 ? $price : $road['price'];
		if($price < 1) exit('error');
		$price = $price*PAY_ZHEKOU;
		//创建本地订单
		$ar = array(
			'vmid'			=> $vmid, //机器id
			'pacode'		=> $pacode, //货道编号
			'dynamic_id'	=> '', //动态验证码，条码
			'trade_no'		=> '', //微信交易号
			'trade_type'	=> 'alipay', //交易方式
			'amount'		=> $price, //总价
			'goods_id'		=> $road['goods_id'],
			'goods_name'	=> $road['goods_name'],
			'saleid'		=> 0, //交易编号
			'pay_status'	=> 0, //支付状态
			'trade_status'	=> 0, //交易状态
			'buyer_id'		=> '', //购买者id，如微信id
			'buyer_name'	=> '', //购买者姓名，如微信账号
			'saletime'		=> NOW_TIME, //机器交易时间
			'createtime'	=> NOW_TIME,
			'user_id'		=> USER_ID,
			'income_userid'	=> INCOME_USERID,
			'note'			=> '预下单'	
		);
		$order_id = M('online_order')->add($ar); //创建订单
		if(!is_numeric($order_id) || $order_id < 1){
			$this->output(array('ok' => 0,'msg' => '订单创建失败'));
		}
		$out_trade_no = M('online_order')->get_out_trade_no($order_id); //必填 商户订单号
		$subject = $road['goods_name'].'-自动售货机'; //订单名称
		require_once ROOT_PWC.'api/alipay/chuchuang/f2fpay/F2fpay.php';
		$f2fpay = new F2fpay();
		$total_fee = $price/100;
		$royalty_info = '';
		if(PAY_SELF == 2) $royalty_info = ALIPAY_ROAYLTY; //分账
//		if($vmid == '0000000206') $royalty_info = '2088002171059281';
		$notify_url = $this->get('cfg.web_url').'alipay_qrnotify.php';
		$response = $f2fpay->qrpay($out_trade_no,$total_fee,$subject,$royalty_info,$notify_url);
		$response = json_decode(json_encode($response),TRUE);
		if(empty($response)){
			$ar['note'] = '支付宝下单失败';
		}elseif($response['alipay_trade_precreate_response']['code'] == 10000){
			$ar['note'] = '支付宝下单成功';
			//输出二维码链接
			echo $response['alipay_trade_precreate_response']['qr_code']; 
		}else{
			$ar['note'] = $response['alipay_trade_precreate_response']['sub_msg'];
		}
		M('online_order')->where(array('id',$order_id))->save($ar);
	}


	//支付宝回调验证 用于条码支付的回调  不需要通知机器发货
	public function notify(){
		include(ROOT_PWC.'api/alipay/alipay.config.php');
		require_once(ROOT_PWC.'api/alipay/lib/alipay_notify.class.php');
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result && $_POST['trade_status'] == 'TRADE_SUCCESS'){
			//验证成功
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			$out_trade_no = $_POST['out_trade_no']; //商户订单号
			$trade_no = $_POST['trade_no']; //支付宝交易号
			$trade_status = $_POST['trade_status']; //交易状态
			//判断是否在商户网站中已经做过了这次通知返回的处理
			//如果没有做过处理，那么执行商户的业务程序
			//如果有做过处理，那么不执行商户的业务程序
			
			//查询订单
			$online_order = M('online_order')->where(array('out_trade_no',$out_trade_no))->find();
			if(is_array($online_order) && $online_order['trade_no'] == $trade_no && $online_order['pay_status'] == 0){
				$data = array(
					'pay_status'	=> 1,
					'paytime'		=> time(),
					'note'			=> '支付成功',
				);
				M('online_order')->where(array('id',$online_order['id']))->save($data);
				$ar = array(
					'vmid'			=> $online_order['vmid'],
					'currency'		=> 4,//0：硬币 1：纸币 2: 普通卡 3：学生卡 4 支付宝
					'amount'		=> $online_order['amount'],//面值
					'time'			=> time(), //销售时间
					'payments'		=> 0,//0：收币1：找零2：吞币
					'num'			=> 1,//数量
					'saleid'		=> $online_order['saleid'],//交易号
					'card'			=> '', //卡号
					'coinchannel'	=> '',
					'createtime'	=> time(),//传输时间
					'saledate'		=> date('Ymd',$online_order['saletime']),//销售日期
					'createdate' 	=> date('Ymd') //传输日期
				);
				M('pay')->add($ar);
				if(PAY_SELF === 0 && INCOME_USERID == 1){
					//修改用户金额
					update_user_money(USER_ID,$online_order['amount'],'销售收入，订单编号：'.$out_trade_no);
				}
				if(USER_ID != MACHINE_UID){
					//修改用户金额
					update_user_money_2(MACHINE_UID,$online_order['amount'],'销售收入，订单编号：'.$out_trade_no);
				}
				$this->notify_machine($online_order);
			}
			echo "success";//请不要修改或删除
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		}else{
			//验证失败
			echo "fail";
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}

	//二维码支付回调，支付成功后需要调用机器发货
	public function qr_notify(){
		set_time_limit(60);
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
			$online_order = M('online_order')->where(array('out_trade_no',$out_trade_no))->find();
			if(is_array($online_order) && $online_order['pay_status'] == 0){
				$data = array(
					'pay_status'	=> 1,
					'trade_no'		=> $trade_no,
					'buyer_name'	=> $_POST['buyer_logon_id'],
					'buyer_id'		=> $_POST['open_id'],
					'paytime'		=> time(),
					'note'			=> '支付成功',
				);
				M('online_order')->where(array('id',$online_order['id']))->save($data);
				if(PAY_SELF === 0 && INCOME_USERID == 1){
					//修改用户金额
					update_user_money(USER_ID,$online_order['amount'],'销售收入，订单编号：'.$out_trade_no);
				}
				if(USER_ID != MACHINE_UID){
					//修改用户金额
					update_user_money_2(MACHINE_UID,$online_order['amount'],'销售收入，订单编号：'.$out_trade_no);
				}
				//通知机器发货
				$this->notify_machine($online_order);
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

	//支付宝退款
	public function refund(){
		set_time_limit(60);
		$out_trade_no = $_REQUEST['out_trade_no'];
		$order = M('online_order')->where(['out_trade_no',$out_trade_no])->find();
		if($order['trade_type'] != ACTION or $order['pay_status'] != 1) return;
		if($_REQUEST['enforce'] != 1 && $order['paytime'] < NOW_TIME - 300) return;
		$refound_amount = $order['amount'];
		$req_index = 'RF001'; //退款批次
		require_once ROOT_PWC.'api/alipay/chuchuang/f2fpay/F2fpay.php';
		$trade_no = trim($order['trade_no']);
		$out_request_no = $req_index; //退款订单编号
		$f2fpay = new F2fpay();
		for($i = 0; $i < 2;$i++){
			$response = $f2fpay->refund($trade_no,$refound_amount/100,$out_request_no);
			$response = json_decode(json_encode($response),TRUE);
			//f_write(PATH_CACHE.'alipay.log',"\n".date('Y-m-d H:i:s')."\n",'a');
			//f_write(PATH_CACHE.'alipay.log',var_export($response,TRUE),'a');
			if($response['alipay_trade_refund_response']['fund_change'] == 'Y'){
				//修改订单
				$ar = ['pay_status' => 2];
				M('online_order')->where(['out_trade_no',$out_trade_no])->save($ar);
				//扣除用户资金
				if($order['income_userid'] == 1 && USER_ID > 1){
					update_user_money(USER_ID,-$order['amount'],'订单退款，订单编号：'.$out_trade_no);
				}
				if(USER_ID != MACHINE_UID){
					//修改用户金额
					update_user_money_2(MACHINE_UID,-$order['amount'],'订单退款，订单编号：'.$out_trade_no);
				}
				break;
			}
		}//end for
	}

	//订单查询
	public function query($ret = false,$out_trade_no = ''){
		$out_trade_no = $ret ? $out_trade_no : $_REQUEST['out_trade_no']; //商户网站订单系统中唯一订单号，必填
		require_once ROOT_PWC.'api/alipay/chuchuang/f2fpay/F2fpay.php';
		$f2fpay = new F2fpay();
		$response = $f2fpay->query($out_trade_no);
		$result = json_decode(json_encode($response),true);
		if($ret) return $result;
		if($result['alipay_trade_query_response']['trade_status'] == 'TRADE_SUCCESS'){
			$where = array('out_trade_no',$out_trade_no);
			M('online_order')->where($where)->save(array(
				'pay_status' => 1,
				'trade_no'	=> $result['alipay_trade_query_response']['trade_no'],
				'buyer_id'		=> $result['alipay_trade_query_response']['buyer_user_id'],
				'buyer_name'	=> $result['alipay_trade_query_response']['buyer_logon_id']
				));
		}
		echo '<table width="100%">';
		foreach($result['alipay_trade_query_response'] as $k => $v){
			echo '<tr><td width="40%" align="right">'.$k.'：</td><td>'.(is_array($v) ? var_export($v,true) : $v).'</td></tr>';
		}
		echo '</table>';
	}

	//通知机器发货
	private function notify_machine($order){
		$port = $this->get('cfg.server_port');
		$ip = $this->get('cfg.server_ip');
		$machine = M('machine')->fields('gate')->where(array('vmid',$order['vmid']))->find();
		if(!empty($machine['gate'])){
			list($ip,$port) = explode(':',$machine['gate']);
		}
		return notify_machine($order,$ip,$port);
	}

	private function output($ar){
		echo $ar['msg'];
		//echo json_encode($ar);
		exit(0);
	}
}