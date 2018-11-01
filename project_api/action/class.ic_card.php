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
| IC卡接口api，仅供网关调用 内部调用，请勿泄漏
+----------------------------------------------------------------------
*/
class Ic_cardAction extends Action{
	
	private $use_api = false; //是否调用其他平台的接口通知
	private $vmid = '';
	private $data;
	private $machine;
	private $api;

	protected function before(){
		$this->vmid = $_REQUEST['vmid'];
		$this->data = explode('*',$_REQUEST['data']);
		if(!is_numeric($this->vmid) || strlen($this->vmid) != 10){
			echo json_encode(['ok' => 0]);
			exit; //机器编号错误
		}
		$this->machine = M('machine')->where(['vmid',$this->vmid])->find();
		if(!is_array($this->machine)){
			echo json_encode(['ok' => 0]);
			exit; //机器不存在
		}
		//其他平台的接口配置
		$configs = array(
			'5861' => [
				'sale'		=> 'http://development.dtech-school.com/SchoolBank/SetIDConsume.ashx',
				'balance'	=> 'http://development.dtech-school.com/SchoolBank/GetIDMoney.ashx', 
			]
		);
		if(array_key_exists($this->machine['user_id'],$configs)){
			$this->use_api = true;
			$this->api = $configs[$this->machine['user_id']];
		}
	}

	//校园卡出货上报
	public function sale(){
		//校园卡出货，支付处理  *时间*交易号*货道编号*商品价格*校园卡卡号
		$this->data[1] = $this->format_time($this->data[1]);
		$saleid = $this->data[2];
		$pacode = $this->data[3];
		$price = abs(intval($this->data[4]));
		$cardid = $this->data[5];
		//创建收支记录
		$ar = array(
			'vmid'			=> $this->vmid,
			'currency'		=> 3,//0：硬币 1：纸币 2: 普通卡 3：学生卡 4 支付宝 5 微信
			'amount'		=> $price,//面值
			'time'			=> $this->data[1], //销售时间
			'payments'		=> 0,//0：收币1：找零2：吞币
			'num'			=> 1,//数量
			'saleid'		=> $saleid,//交易号
			'card'			=> $cardid, //卡号
			'coinchannel'	=> '',
			'createtime'	=> time(),//传输时间
			'saledate'		=> date('Ymd',$this->data[1]),//销售日期
			'createdate' 	=> date('Ymd') //传输日期
		);
		M('pay')->add($ar);
		//创建出货记录
		$road = M('road')->where(array(['vmid',$this->vmid],['pacode',$pacode]))->find();
		$ar = array(
			'saleid'		=> $saleid, // 交易号
			'saletype'		=> 5, //销售类型 一卡通
			'salecard'		=> $cardid,
			'salemoney'		=> $price,
			'saletime'		=> $this->data[1],
			'salenum'		=> 1,
			'pacode'		=> $pacode, //货道
			'vmid'			=> $this->vmid,
			'createtime'	=> time(),
			'saledate'		=> date('Ymd',$this->data[1]),
			'createdate' 	=> date('Ymd'), //传输日期
			'goods_id'		=> intval($road['goods_id']),
			'goods_name'	=> $road['goods_name'],
			'price_cb'		=> $price
		);
		if($ar['goods_id'] > 0){
			$goods = M('goods')->where(array('id',$ar['goods_id']))->find();
			if($goods){
				$ar['goods_name'] = $goods['goods_name']; 
				$ar['price_cb'] = $goods['goods_price']; 
			}
		}
		$ok = M('saledetail')->add($ar);
		//货道商品数减一
		if($ok && $road && $road['num'] > 0){
			M('road')->where(array('id',$road['id']))->save(array('num' => $road['num'] - 1));
		}
		
		if($this->use_api){
			//调用api通知扣款
			$post_data = [
				'cardid'	=> $cardid,
				'vmid'		=> $ar['vmid'],
				'pacode'	=> $ar['pacode'],
				'saleid'	=> $ar['saleid'],
				'goods_id'	=> $ar['goods_id'],
				'goods_name'=> $ar['goods_name'],
				'salemoney'	=> $ar['salemoney'],
				'saletime'	=> $ar['saletime']
				];
			$res = $this->http_post($this->api['sale'],$post_data);
			$res = json_decode($res);
			if(is_object($res) && $res->code == '1'){
				$str = '*'.intval($res->account);
				echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '30']);
			}else{
				echo json_encode(['ok' => 0]);
			}
			return;
		}
		//查询卡的余额......
		$item = M('school_card')->where(['cardid',$cardid])->find();
		if($item){
			//扣款
			$ar = array(
				'cardid'	=> $cardid,
				'before'	=> $item['account'],
				'change'	=> -$price,
				'after'		=> $item['account'] - $price,
				'note'		=> '售货机['.$this->vmid.']消费',
				'createtime'=> time(),
			);
			$res = M('school_card')->where(['cardid',$cardid])->save(['account' => $ar['after']]);
			if($res){
				M('school_card_log')->add($ar);
			}
		}
		echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '30']);
	}

	//现金充值 卡片在线充值处理 *校园卡卡号*充值金额
	public function recharge(){
		$cardid = $this->data[1];
		$money = abs(intval($this->data[2]));
		if($this->use_api){
			//api 模式不允许现金充值
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '34','str' => '*0*0']);
			return;
		}
		//查询卡信息
		$card_info = M('school_card')->where(array('cardid',$cardid))->find();
		if(!$card_info){
			//卡片不存在
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '34','str' => '*0*0']);
			return;
		}
		//查询机器信息，如果不是同一管理员  则不允许充值
		$where = array(
			array('vmid',$this->machine['vmid']),
			array('user_id',$card_info['user_id']),
		);
		$ma = M('machine')->where($where)->count();
		if($ma == 0){
			$str = '*0*'.$card_info['account'];
			$serv->send($fd,pack_data($result,'1d','34',$package[1]));
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '34','str' => $str]);
			return;
		}
		$this->data = array(
			'cardid'	=> $cardid,
			'before'	=> $card_info['account'],
			'change'	=> $money,
			'after'		=> $card_info['account'] + $money,
			'note'		=> '售货机充值',
			'createtime'=> time(),
		);
		$res = M('school_card')->where(array('cardid',$cardid))->save(array('account' => $this->data['after']));
		if($res){
			M('school_card_log')->add($this->data);
			//创建订单
			$ar = array(
				'trade_no'		=> '',
				'trade_type'	=> 0, //现金充值
				'income_userid'	=> $card_info['user_id'],
				'user_id'		=> $card_info['user_id'],
				'cardid'		=> $cardid,
				'amount'		=> $money,
				'vmid'			=> $this->machine['vmid'],
				'pay_status'	=> 1,
				'createtime'	=> time(),
				'paytime'		=> time(),
				'note'			=> '机器现金充值'
			);
			M('school_card_pay')->add($ar);
			$str = '*1*'.$this->data['after'];
		}else{
			$str = '*0*'.$card_info['account'];
		}
		echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '34','str' => $str]);
	}

	//余额查询
	public function balance(){
		$cardid = $this->data[1];
		if($this->use_api){
			$res = $this->http_post($this->api['balance'],['cardid' => $cardid]);
			$res = json_decode($res);
			if(is_object($res)){
				$str = '*'.intval($res->account);
				echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '35','str' => $str]);
			}else{
				echo json_encode(['ok' => 0]);
			}
			return;
		}
		$card_info = M('school_card')->where(array('cardid',$cardid))->find();
		$str = '*'.intval($card_info['account']);
		echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '35','str' => $str]);
	}

	//购买限制检查
	public function buycheck(){
		//提交校园卡卡号和选择的货道编号，后台根据校园卡设置，返回是否允许购买商品
		$cardid = $this->data[1];
		$pacode = intval($this->data[2]);
		$price = intval($this->data[3]);
		if($this->use_api){
			//api 模式,查询余额
			$res = $this->http_post($this->api['balance'],['cardid' => $cardid]);
			$res = json_decode($res);
			if(is_object($res)){
				$account = intval($res->account);
				if($account < $price){
					$str = '*1*'.$account;
				}else{
					$str = '*0*'.$account;
				} 
			}else{
				$str = '*1*0';
			}
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
			return;
		}
		$card_info = M('school_card')->where(array('cardid',$cardid))->find();
		$str = '';
		if(!$card_info){
			$str = '*1*0'; //卡片不存在
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
			return;
		}
		if($card_info['status'] != 1){
			$str = '*2*'.$card_info['account']; //已挂失
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
			return;
		}
		if($card_info['account'] < $price){
			$str = '*1*'.$card_info['account']; //余额不足
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
			return;
		}
		if($card_info['day_limit'] && $card_info['day_limit'] < $price){
			$str = '*3*'.$card_info['account']; //超过了日限额
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
			return;
		}
		//查询是否允许在这台机器上购买
		$where = array(
			array('vmid',$this->machine['vmid']),
			array('user_id',$card_info['user_id']),
		);
		$ma = M('machine')->where($where)->count();
		if($ma == 0){
			$str = '*2*'.$card_info['account']; //禁止购买
			echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
			return;
		}
		//查询货到商品是否允许购买
		if(!empty($card_info['goods_allow']) || (!empty($card_info['goods_fobid']))){
			$road = M('road')->where(array(array('vmid',$this->machine['vmid']),array('pacode',$pacode)))->find();
			if(!empty($card_info['goods_allow'])){
				$ar = explode(',',$card_info['goods_allow']);
				if(!in_array($road['goods_id'],$ar)){
					$str = '*2*'.$card_info['account']; //禁止购买
					echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
					return;
				}
			}
			if(!empty($card_info['goods_fobid'])){
				$ar = explode(',',$card_info['goods_fobid']);
				if(in_array($road['goods_id'],$ar)){
					$str = '*2*'.$card_info['account']; //禁止购买
					echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
					return;
				}
			}
		}
		//查询是否限额
		if($card_info['day_limit']){
			//查询当日消费金额
			$where = array(
				array('salecard',$cardid),
				array('createdate',date('Ymd')),
			);
			$res = M('saledetail')->fields('SUM(salemoney) as amount')->where($where)->find();
			if($res['amount'] + $price > $card_info['day_limit']){
				$str = '*3*'.$card_info['account']; //禁止购买
				echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
				return;
			}
		}
		$str = '*0*'.$card_info['account'];
		if(strlen($card_info['password']) > 1){
			$str .= '*1*'.$card_info['password'];
		}else{
			$str .= '*0';
		}
		echo json_encode(['ok' => 1,'big_sort' => '1d','small_sort' => '40','str' => $str]);
		return;
	}

	//校园卡微信二维码充值
	public function recharge_wx(){
		$this->recharge_alipay(true);
	}

	//校园卡支付宝二维码充值
	public function recharge_alipay($is_wx = false){
		$cardid = $this->data[1];
		$money = intval($this->data[2]);
		if($this->use_api){
			//api 模式
			echo json_encode(['ok' => 1,'big_sort' => '14','small_sort' => $is_wx ? '83':'84','str' => '']);
			return;
		}
		//查询卡信息
		$card_info = M('school_card')->where(['cardid',$cardid])->find();
		if(!$card_info){
			//卡片不存在
			$serv->send($fd,pack_data('','14',$package[2],$package[1]));
			echo json_encode(['ok' => 1,'big_sort' => '14','small_sort' => $is_wx ? '83':'84','str' => '']);
			return;
		}
		//查询机器信息，如果不是同一管理员  则不允许充值
		$where = array(
			array('vmid',$this->machine['vmid']),
			array('user_id',$card_info['user_id']),
		);
		$ma = M('machine')->where($where)->count();
		if($ma == 0){
			echo json_encode(['ok' => 1,'big_sort' => '14','small_sort' => $is_wx ? '83':'84','str' => '']);
			return;
		}
		$pay_type = $is_wx ? 'weixin' : 'alipay';
		$url = $this->get('cfg.web_url').'online_pay.php?a='.$pay_type.'_card&m=qrcode&vmid='.$this->machine['vmid'].'&cardid='.$cardid.'&user_id='.$card_info['user_id'].'&money='.$money;
		$url_ch = curl_init();
		curl_setopt($url_ch,CURLOPT_URL,$url);
		curl_setopt($url_ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($url_ch,CURLOPT_TIMEOUT,10);
		$qr_url = curl_exec($url_ch);
		curl_close($url_ch);
		echo json_encode(['ok' => 1,'big_sort' => '14','small_sort' => $is_wx ? '83':'84','str' => $qr_url]);
	}

	//yymmddhhiiss 的时间格式转化成秒数
	private function format_time($str){
		return strtotime('20'.substr($str,0,2).'-'.substr($str,2,2).'-'.substr($str,4,2).' '.substr($str,6,2).':'.substr($str,8,2).':'.substr($str,10,2));
	}

	private function http_post($url,$post_fields){
		$url_ch = curl_init();
		curl_setopt($url_ch,CURLOPT_URL,$url);
		curl_setopt($url_ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($url_ch,CURLOPT_TIMEOUT,10);
		curl_setopt($url_ch,CURLOPT_POST,1);
		curl_setopt($url_ch,CURLOPT_POSTFIELDS,$post_fields);
		$res = curl_exec($url_ch);
		curl_close($url_ch);
		return $res;
	}

}