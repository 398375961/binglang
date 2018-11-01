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
if(METHOD === 'notify'){
	$values = json_decode(json_encode(simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'],'SimpleXMLElement',LIBXML_NOCDATA)),true);
	$out_trade_no = $values['out_trade_no'];
	$order = M('online_order')->where(array('out_trade_no',$out_trade_no))->find();
	$vmid = $order['vmid'];
}
$pay_cfg = get_pay_cfg($vmid,'weixin');
foreach($pay_cfg as $cfg_name => $cfg_value) define($cfg_name,$cfg_value);
if($pay_cfg['PAY_SELF'] == 1 && $pay_cfg['INCOME_USERID'] > 1){
	define('PATH_SSLCERT_PATH',ROOT_PWC.'public/upload/wx_cert/'.$pay_cfg['INCOME_USERID'].'_apiclient_cert.pem');
	define('PATH_SSLKEY_PATH',ROOT_PWC.'public/upload/wx_cert/'.$pay_cfg['INCOME_USERID'].'_apiclient_key.pem');
}else{
	define('PATH_SSLCERT_PATH',ROOT_PWC.'api/weixin/cert/apiclient_cert.pem');
	define('PATH_SSLKEY_PATH',ROOT_PWC.'api/weixin/cert/apiclient_key.pem');
}
class WeixinAction extends Action{

	//老版单片机系统需升级才能使用
	protected function before(){
		if($_REQUEST['vmid']){
			$item = M('status')->fields('version')->where(['vmid',$_REQUEST['vmid']])->find();
			if(preg_match('/^v1\.[\d]{4}$/i',$item['version'])) exit(0);
		}
	}

	/*
	* 网关数据处理 条码支付，由机器请求网关，网关再请求此页面
	* 请求参数  vmid机器id amount金额 saleid交易号 dynamic_id 条码号 time时间（机器发生交易的时间）
	*/
	public function index(){
		set_time_limit(60);
		require_once(ROOT_PWC.'api/weixin/lib/WxPay.Api.php');
		require_once(ROOT_PWC.'api/weixin/example/WxPay.MicroPay.php');
		require_once(ROOT_PWC.'api/weixin/example/log.php');
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
			'trade_no'		=> '', //微信交易号
			'trade_type'	=> 'weixin', //交易方式
			'amount'		=> $amount, //总价
			'goods_id'		=> $road['goods_id'],
			'goods_name'	=> $road['goods_name'],
			'saleid'		=> $saleid, //交易编号
			'pay_status'	=> 0, //支付状态
			'trade_status'	=> 0, //交易状态
			'buyer_id'		=> '', //购买者id，如微信id
			'buyer_name'	=> '', //购买者姓名，如微信账号
			'saletime'		=> $saletime, //机器交易时间
			'createtime'	=> NOW_TIME,
			'user_id'		=> USER_ID,
			'income_userid'	=> INCOME_USERID
		);
		$order_id = M('online_order')->add($ar); //创建订单
		if(!is_numeric($order_id) || $order_id < 1){
			$this->output(array('ok' => 0,'msg' => '订单创建失败'));
		}
		$out_trade_no = M('online_order')->get_out_trade_no($order_id); //必填 商户订单号
		$subject = $road['goods_name'].'自动售货机交易'; //订单名称
		$total_fee = $amount; //付款金额 单位为分

		//初始化日志
		$logHandler= new CLogFileHandler(ROOT_PWC.'api/weixin/logs/'.date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 8);
		$auth_code = $dynamic_id;
		$input = new WxPayMicroPay();
		$input->SetAuth_code($auth_code);
		$input->SetBody($subject);
		$input->SetDevice_info($vmid);
		$input->SetTotal_fee($total_fee); //订单金额，单位为分
		$input->SetOut_trade_no($out_trade_no);
		$microPay = new MicroPay();
		$res = $microPay->pay($input);
		/*
		* 正常返回结果 array(19) { ["appid"]=> string(18) "wxb72cc48d72ad08f7" ["attach"]=> array(0) { } ["bank_type"]=> string(9) "CMB_DEBIT" ["cash_fee"]=> string(1) "1" ["fee_type"]=> string(3) "CNY" ["is_subscribe"]=> string(1) "N" ["mch_id"]=> string(10) "1249665701" ["nonce_str"]=> string(16) "TjrsSEuL7zK5nFez" ["openid"]=> string(28) "oAUppuKUgSSJOXSQNr89ExI4k_HA" ["out_trade_no"]=> string(2) "66" ["result_code"]=> string(7) "SUCCESS" ["return_code"]=> string(7) "SUCCESS" ["return_msg"]=> string(2) "OK" ["sign"]=> string(32) "54F0A92825B4153C41B0FB9B492A0E25" ["time_end"]=> string(14) "20150626172404" ["total_fee"]=> string(1) "1" ["trade_state"]=> string(7) "SUCCESS" ["trade_type"]=> string(8) "MICROPAY" ["transaction_id"]=> string(28) "1007820549201506260306970151" }
		*/
		if($res === false){
			$data = array(
				'note'	=> '请求失败，支付失败'	
			);
		}else if($res['result_code'] == 'SUCCESS' && $res['trade_state'] == 'SUCCESS'){
			//支付成功
			$data = array(
					'pay_status'	=> 1,
					'paytime'		=> time(),
					'finishtime'	=> time(),
					'trade_no'		=> $res['transaction_id'], //微信交易号
					'buyer_id'		=> $res['openid'], //买家微信账号对应的微信唯一用户号
					'buyer_name'	=> $res['openid'], //买家微信账号，可
					'note'			=> '下单成功，支付成功'
			);
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
		}else{
			/*
			* array(9) { ["appid"]=> string(18) "wxb72cc48d72ad08f7" ["err_code"]=> string(14) "AUTHCODEEXPIRE" ["err_code_des"]=> string(51) "每个二维码仅限使用一次，请刷新再试" ["mch_id"]=> string(10) "1249665701" ["nonce_str"]=> string(16) "h4sOrxmIOK319p6h" ["result_code"]=> string(4) "FAIL" ["return_code"]=> string(7) "SUCCESS" ["return_msg"]=> string(2) "OK" ["sign"]=> string(32) "0F23EA35D7112AEE1F4C0A61ECC45F3A" }
			*/
			$data = array(
				'buyer_id'		=> $res['openid'],
				'buyer_name'	=> $res['openid'],
				'note'			=> $res['err_code_des']
			);
		}
		M('online_order')->where(array('id',$order_id))->save($data);
	}

	/*
	* 微信扫码支付回调 手机微信扫描二维码->微信后台处理->回调本页面生成订单->调用统一下单接口 。。。
	* 详见 http://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=6_4
	*/
	public function notify_native(){
		$vmid = $GLOBALS['vmid'];
		$pacode = $GLOBALS['pacode'];
		$price = $GLOBALS['price'];
		//查询商品信息
		$where = array(
			array('vmid',$vmid),
			array('pacode',$pacode)
		);
		$road = M('road')->where($where)->find();
		if($price == 0){
			if(!is_array($road) || count($road) < 1) return;
			$price = $road['price'];
		}else{
			if(!is_array($road)) $road = array();
		}
		$price = $price*PAY_ZHEKOU;
		//检查机器是否在线，如果不在线则不能完成支付
		$online = $this->check_machine($vmid);
		if($online === false) return;
		//创建本地订单
		$ar = array(
			'vmid'			=> $vmid, //机器id
			'pacode'		=> $pacode, //货道编号
			'dynamic_id'	=> '', //动态验证码，条码
			'trade_no'		=> '', //微信交易号
			'trade_type'	=> 'weixin', //交易方式
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
		$notify_url = $this->get('cfg.web_url').'weixin_notify.php';
		$order_info = array(
			'body'			=> $subject,
			'out_trade_no'	=> $out_trade_no,
			'notify_url'	=> $notify_url,
			'total_fee'		=> $price
		);
		include(ROOT_PWC.'api/weixin/example/native_notify.php');
		//初始化日志
		$logHandler= new CLogFileHandler(ROOT_PWC.'api/weixin/logs/'.date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 8);
		Log::DEBUG("begin notify!");
		$notify = new NativeNotifyCallBack();
		$notify->set_order($order_info);
		$notify->Handle(true);
	}

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
		$vmid = $_REQUEST['vmid'];
/*
if($vmid !== '0000010003' && $vmid !== '0000088888'){
	return '';
}*/
		$pacode = $_REQUEST['pacode'];
		$price = intval($_REQUEST['price']);
		$goodsid = $pacode.$vmid;
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
			'trade_type'	=> 'weixin', //交易方式
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
		$notify_url = $this->get('cfg.web_url').'weixin_notify.php';
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
		$input->SetDevice_info($vmid);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 7200));
		$input->SetGoods_tag("test");
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($goodsid);
		$result = $notify->GetPayUrl($input);
		// var_dump($result);
		if(is_array($result) && $result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS') echo $result['code_url'];
	}

	//二维码支付成功的回调页面
	public function notify(){
		set_time_limit(60);
		include(ROOT_PWC.'api/weixin/example/notify.php');
		//初始化日志
		$logHandler= new CLogFileHandler(ROOT_PWC.'api/weixin/logs/'.date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 8);
		Log::DEBUG("begin notify");
		$notify = new PayNotifyCallBack();
		$notify->Handle(false);
		$result = $notify->get_data(); //回调结果，数组形式
		if(array_key_exists("return_code",$result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS"){
			$order = M('online_order')->where(array('out_trade_no',$result['out_trade_no']))->find();
			if($order['pay_status'] != 0) return;
			//更新订单
			$data = array(
				'pay_status'	=> 1,
				'paytime'		=> time(),
				'finishtime'	=> time(),
				'trade_no'		=> $result['transaction_id'], //微信交易号
				'buyer_id'		=> $result['openid'], //买家微信账号对应的微信唯一用户号。以2088开头的纯16位数字。
				'buyer_name'	=> $result['openid'], //买家微信账号，可以为email或者手机号。对部分信息进行了隐藏。
				'note'			=> '下单成功，支付成功'
			);
			M('online_order')->where(array('id',$order['id']))->save($data);
			if(PAY_SELF === 0 && INCOME_USERID == 1){
				//修改用户金额
				update_user_money(USER_ID,$order['amount'],'销售收入，订单编号：'.$order['out_trade_no']);
			}
			if(USER_ID != MACHINE_UID){
				//修改用户金额
				update_user_money_2(MACHINE_UID,$order['amount'],'销售收入，订单编号：'.$order['out_trade_no']);
			}
			//通知机器发货
			$this->notify_machine($order);
		}
	}

	//根据商品ID生成二维码内容 商品ID 由 机器id + 货道id 拼装起来
	public function show_prepayurl($ret = false){
		$vmid = $_REQUEST['vmid'];
		$pacode = $_REQUEST['pacode'];
		$price = intval($_REQUEST['price']);
		if(empty($vmid)){
			echo '请传入机器编号';
			return;
		}
		if($_REQUEST['api'] == 'pacodes'){
			$pacodes = M('road')->fields('pacode')->where(array('vmid',$vmid))->order('pacode','ASC')->select();
			foreach($pacodes as $item) echo $item['pacode'].'|';
			return;
		}
		if(!empty($pacode)){
			require_once(ROOT_PWC.'api/weixin/lib/WxPay.Api.php');
			require_once(ROOT_PWC.'api/weixin/example/WxPay.NativePay.php');
			require_once(ROOT_PWC.'api/weixin/example/log.php');
			$goodsid = $pacode.$vmid;
		  //if($price > 0) $goodsid .= $price; //不要拼装价格，有安全漏洞
			$notify = new NativePay();
			$url = $notify->GetPrePayUrl($goodsid);
	//短链接生成,短链接会过期失效
		//	$input = new WxPayShortUrl();
		//	$input->SetLong_url($url);
		//	$result = WxPayApi::shorturl($input);
	//end
			if($_REQUEST['api'] || $ret){
				echo $url;
				return;
			}
			$this->set('url',$url);
		}
		$this->tpl();
	}

	//退款
	public function refund(){
		set_time_limit(60);
		$out_trade_no = $_REQUEST['out_trade_no'];
		$order = M('online_order')->where(['out_trade_no',$out_trade_no])->find();
		if($order['trade_type'] != ACTION or $order['pay_status'] != 1 or $order['paytime'] < NOW_TIME - 300) return;
		$refound_amount = $order['amount'];
		require_once(ROOT_PWC.'api/weixin/lib/WxPay.Api.php');
		$transaction_id = $order['trade_no']; //微信订单号
		$total_fee = $order["amount"]; //订单总金额
		$refund_fee = $refound_amount; //退款金额
		$input = new WxPayRefund();
		$input->SetTransaction_id($transaction_id);
		$input->SetTotal_fee($total_fee);
		$input->SetRefund_fee($refund_fee);
		$input->SetOut_refund_no('RE'.$out_trade_no);
		$input->SetOp_user_id(WxPayConfig::MCHID);
		for($i = 0; $i < 2;$i++){
			$response = WxPayApi::refund($input);
			//f_write(PATH_CACHE.'wx.log',"\n".date('Y-m-d H:i:s')."\n",'a');
			//f_write(PATH_CACHE.'wx.log',var_export($response,TRUE),'a');
			if($response['return_code'] == 'SUCCESS' && $response['result_code'] == 'SUCCESS'){
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
		}
	}

	//订单查询
	public function query($ret = false,$out_trade_no = ''){
		$out_trade_no = $ret ? $out_trade_no : $_REQUEST['out_trade_no'];
		require_once ROOT_PWC.'api/weixin/lib/WxPay.Api.php';
		require_once ROOT_PWC.'api/weixin/example/log.php';
		$logHandler= new CLogFileHandler(ROOT_PWC.'api/weixin/logs/'.date('Y-m-d').'.log');
		$log = Log::Init($logHandler,8);
		$input = new WxPayOrderQuery();
		$input->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::orderQuery($input);
		if($ret) return $result;
		if($result['trade_state'] == 'SUCCESS'){
			$where = array('out_trade_no',$out_trade_no);
			M('online_order')->where($where)->save(array(
				'pay_status'	=> 1,
				'trade_no'		=> $result['transaction_id'], //微信交易号
				'buyer_id'		=> $result['openid'], //买家微信账号对应的微信唯一用户号
				'buyer_name'	=> $result['openid'],
			));
		}
		echo '<table width="100%">';
		foreach($result as $k => $v){
			echo '<tr><td width="40%" align="right">'.$k.'：</td><td>'.$v.'</td></tr>';
		}
		echo '</table>';
	}

	//检查机器是否在线
	private function check_machine($vmid){
		$port = $this->get('cfg.server_port');
		$ip =	$this->get('cfg.server_ip');
		$machine = M('machine')->fields('gate')->where(array('vmid',$vmid))->find();
		if(!empty($machine['gate'])){
			list($ip,$port) = explode(':',$machine['gate']);
		}
		$data = pack_data('*'.$vmid,'80','01'); 
		$res = my_socket_send($ip,$port,$data);
		return $res == 'online' ? true : false;
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