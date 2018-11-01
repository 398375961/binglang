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
| 商城api
+----------------------------------------------------------------------
*/
class ShopAction extends Action{
	
	const C_OK = '1';
	const C_ERROR_1 = '1001'; //参数错误
	const C_ERROR_2 = '1002'; //缺少参数
	const C_ERROR_3 = '1003'; //签名错误
	const C_ERROR_4 = '1004'; //未找到机器
	const C_ERROR_5 = '1005'; //系统错误，请重试
	const C_ERROR_6 = '1006'; //机器不在线

	private $vmid;
	private $machine;
	
	protected function before(){
		$this->vmid = $_REQUEST['vmid'];
		if(empty($this->vmid)){
			$this->result(self::C_ERROR_2);
		}
		$this->machine = M('machine')->where(array('vmid',$this->vmid))->find();
		if(!is_array($this->machine)){
			$this->result(self::C_ERROR_4);
		}
		if(empty($this->machine['private_key'])) $this->result(self::C_ERROR_5);
		if(METHOD != 'callback') $this->verify();
		$this->_log();
	}
	
	//获取机器信息
	public function machineinfo(){
		$info = array(
			'vmid'		=> $this->machine['vmid'],
			'vmcode'	=> $this->machine['vmcode'],
			'vmname'	=> $this->machine['vmname'],
			'ip'		=> $this->machine['ip'],
			'lng'		=> $this->machine['lng'],
			'lat'		=> $this->machine['lat'],
		);
		$this->result(self::C_OK,$info);
	}

	//设置货道库存
	public function set_num(){
		$min_pacode = intval($_REQUEST['min_pacode']);
		$max_pacode = intval($_REQUEST['max_pacode']);
		$num = intval($_REQUEST['num']);
		$where = array(
			array('vmid',$this->vmid),
			array('pacode',$min_pacode,'>='),
			array('pacode',$max_pacode,'<='),
		);
		if($num >= 0){
			$res = M('road')->where($where)->save(array('num' => $num));
		}else{
			$sql = 'UPDATE ##road SET num=max_num WHERE vmid="'.$this->vmid.'" AND pacode>='.$min_pacode.' AND pacode <='.$max_pacode;
			$res = M()->excute($sql);
		}
		if($res){
			$this->result(self::C_OK,array('update_num' => $res));
		}else{
			$this->result(self::C_OK,array('update_num' => 0));
		}
	}

	//获取货道配置
	public function section(){
		$list = M('road')->where(array('vmid',$this->vmid))->order('pacode','ASC')->select();
		$data = array();
		foreach($list as $item){
			$data[] = array(
				'pacode'	=> $item['pacode'],
				'goods_id'	=> $item['goods_id'],
				'max_num'	=> $item['max_num'],
				'num'		=> $item['num']
			);
		}
		$this->result(self::C_OK,$data);
	}

	//获取机器当前是否在线
	public function status(){
		$item = M('status')->where(array('vmid',$this->vmid))->find();
		if($item['netstatus'] == 1) $data = array('online' => 1);
		else $data = array('online' => 0);
		$this->result(self::C_OK,$data);
	}

	//打开格子
	public function open_box(){
		global $CFG;
		$pacode = $_REQUEST['pacode'];
		//通知网关 机器出货
		$port = $CFG['server_port'];
		$ip =	$CFG['server_ip'];
		if(!empty($this->machine['gate'])){
			list($ip,$port) = explode(':',$this->machine['gate']);
		}
		$data = pack_data('*'.$this->vmid.'*'.$pacode,'80','41');
		$res = my_socket_send($ip,$port,$data);
		if($res == 'ok'){
			$this->result(self::C_OK);
		}
		$this->result(self::C_ERROR_6); //通知机器失败
	}

	//调用出货
	public function sale(){
		global $CFG;
		$pacode = $_REQUEST['pacode'];
		if(!is_numeric($pacode) || strlen($pacode) != 3) $this->result(self::C_ERROR_1);
		$salecode = $_REQUEST['salecode']; //取货码
		if(empty($salecode)) $this->result(self::C_ERROR_1);
		$goods_name = $_REQUEST['goods_name'];
		$callback = $_REQUEST['callback']; //回调地址
		$note = ltrim(trim($_REQUEST['note'])); //备注
		$where = array(
			array('vmid',$this->vmid),
			array('salecode',$salecode)
		);
		$item = M('quhuo')->where($where)->find();
		if(is_array($item) && $item['sale_status']){
//			$this->result(self::C_OK,array('status' => 0)); //测试回调的时候用到
			$this->result(self::C_OK,array('status' => 1,'saletime' => $item['saletime']));
		}
		if(!is_array($item)){
			$item = array(
				'vmid'			=> $this->vmid,
				'pacode'		=> $pacode,
				'salecode'		=> $salecode,
				'goods_name'	=> $goods_name,
				'createtime'	=> NOW_TIME,
				'saletime'		=> 0,
				'sale_status'	=> 0,
				'callbackurl'	=> $callback,
				'note'			=> $note,
			);
			$id = M('quhuo')->add($item);
			if(!$id) $this->result(self::C_ERROR_5);
			$item['id'] = $id;
		}
		//通知网关 机器出货
		$port = $CFG['server_port'];
		$ip =	$CFG['server_ip'];
		if(!empty($this->machine['gate'])){
			list($ip,$port) = explode(':',$this->machine['gate']);
		}
		$data = pack_data('*'.$item['id'].'*'.$pacode.'*'.$this->vmid,'80','22'); 
		$res = my_socket_send($ip,$port,$data);
		if($res == 'ok'){
			$this->result(self::C_OK,array('status' => 0));
		}
		$this->result(self::C_ERROR_6); //通知机器失败
	}
	
	//出货成功后回调，通知商城处理
	public function callback(){
		$id = intval($_REQUEST['id']);
		$where = array('id',$id);
		$item = M('quhuo')->where($where)->find();
		if(!$item) $this->result(self::C_ERROR_1);
		$url = $item['callbackurl'];
		if(empty($url)) return;
		$parms = array(
			'vmid'		=> $item['vmid'],
			'salecode'	=> $item['salecode'],
			'saletime'	=> $item['saletime'],
			'status'	=> $item['sale_status'],
		);
		$sign = $this->sign($parms);
		$url .= strpos('?',$url) === false ? '?' : '&';
		foreach($parms as $k => $v) $url .= $k.'='.$v.'&';
		$url .= 'sign='.$sign;
		file_get_contents($url);
		$this->_log($url);
	}

	//获取商品列表
	public function glist(){
		$user_id = $this->machine['user_id'];
		$list = M('goods')->fields('id,goods_name AS name,goods_note,goods_price/100 AS price')->where(array('user_id',$user_id))->select();
		$this->result(self::C_OK,$list);
	}

	//返回结果
	private function result($code,$data = array()){
		echo json_encode(array('code' => $code,'result' => $data));
		exit;
	}

	//签名验证
	private function verify(){
		$sign = $_REQUEST['sign'];
		if(empty($sign)) $this->result(self::C_ERROR_2);
		unset($_REQUEST['sign']);
		$sign_2 = $this->sign($_REQUEST);
		if($sign != $sign_2) $this->result(self::C_ERROR_3);
	}

	/*
	* $parms 除去sign以外的所有参数
	* return sign 签名
	*/
	private function sign($parms){
		ksort($parms);
		$raw_str = implode('',$parms);
		$sign = strtolower(md5($raw_str));
		return strtolower(md5($sign.$this->machine['private_key']));
	}
	
	//记录调用日志
	private function _log($msg = ''){
		$txt = sprintf("%s VMID:%s Method:%s %s\n",date('Y-m-d H:i:s'),$this->vmid,METHOD,$msg);
		$file = PATH_CACHE.'apilog/'.date('Ymd').'.txt';
		f_write($file,$txt,'a');
	}
}