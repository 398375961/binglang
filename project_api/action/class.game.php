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
| 游戏api
+----------------------------------------------------------------------
*/
class GameAction extends Action{
	
	const C_OK = '1';
	const C_ERROR_1 = '1001'; //参数错误
	const C_ERROR_2 = '1002'; //缺少参数
	const C_ERROR_3 = '1003'; //签名错误
	const C_ERROR_4 = '1004'; //未找到机器
	const C_ERROR_5 = '1005'; //系统错误，请重试
	const C_ERROR_6 = '1006'; //机器不在线
    const C_ERROR_106 = '1106'; //抽奖错误
    const C_ERROR_107 = '1107'; //抽奖错误
    const C_ERROR_108 = '1108'; //抽奖错误

    const XYZP_GAME_TYPE = 1;
    const XYZP_RATE_KEY1 = 'xyzp_rate1'; // 再来一包
    const XYZP_RATE_KEY2 = 'xyzp_rate2'; // 再来一包
    const XYZP_RATE_KEY3 = 'xyzp_rate3'; // 再来一包
    const XYZP_ENABLE_KEY = 'xyzp_enable';

    const ZPCJ_RATE_KEY = 'zpcj_rate'; // 转盘抽奖
    const ZPCJ_ENABLE_KEY = 'zpcj_enable';
    const ZPCJ_PRICE_KEY = 'zpcj_price';

	private $vmid;
	private $machine;
	
	protected function before(){
//		$this->vmid = $_REQUEST['vmid'];
//		if(empty($this->vmid)){
//			$this->result(self::C_ERROR_2);
//		}
//		$this->machine = M('machine')->where(array('vmid',$this->vmid))->find();
//		if(!is_array($this->machine)){
//			$this->result(self::C_ERROR_4);
//		}
//		if(empty($this->machine['private_key'])) $this->result(self::C_ERROR_5);
//		if(METHOD != 'callback') $this->verify();
//		$this->_log();
	}

	private function get_request_machine(){
        $this->vmid = $_REQUEST['vmid'];
        if(empty($this->vmid)){
            return null;
        }
        return M('machine')->where(array('vmid',$this->vmid))->find();
    }


    public function game_config() {
        $configList = M('game_config')->select();
        $configArr = array_column($configList, 'val', 'config_key');
        $data = array(
            'xyzp' => array(
                'enable' => $configArr[self::XYZP_ENABLE_KEY],
                'items' => array(
                    array(
//                        'rate' => $configArr[self::XYZP_RATE_KEY1],
                        'name' => '再来一包',
                        'win_code' => 1
                    ),
                    array(
//                        'rate' => $configArr[self::XYZP_RATE_KEY1],
                        'name' => '再来两包',
                        'win_code' => 2
                    ),
                    array(
//                        'rate' => $configArr[self::XYZP_RATE_KEY1],
                        'name' => '再来三包',
                        'win_code' => 3
                    ),
                    array(
                        'name' => '谢谢参与',
                        'win_code' => 0
                    )
                )
            ),
            'zpcj' => array(
                'enable' => $configArr[self::ZPCJ_ENABLE_KEY],
//                'rate' => $configArr[self::ZPCJ_RATE_KEY],
                'price' => $configArr[self::ZPCJ_PRICE_KEY],
            )
        );
        $this->result(self::C_OK, $data);
    }

//	public function xyzp_config() {
//	    $configList = M('game_config')->where(array('game_type', self::XYZP_GAME_TYPE))->select();
//	    $data = array(
//	        'enable' => $configList[self::XYZP_ENABLE_KEY],
//            'rate' => floatval($configList[self::XYZP_RATE_KEY])
//        );
//	    $this->result(self::C_OK, $data);
//    }

    /**
     * 幸运转盘游戏开始
     */
    public function xyzp_play() {

        $order_id = $_REQUEST['order_id'];
        $win_code = $_REQUEST['win_code'];

        if(empty($order_id)) {
            $this->result(self::C_ERROR_108, array('message' => '错误数据'));
        }
        $order = M('online_order')->where(array('id', $order_id))->find();
        if(empty($order)) {
            $this->result(self::C_ERROR_108, array('message' => '订单不存在'));
        }
        $play_info = M('game_play')->where(array('related_id', $order['id']))->find();
        if(!empty($play_info)) {
            // 游戏已经玩过了
            // $this->result(self::C_ERROR_108, array('message' => '不能重复玩游戏'));
        }
        $vimid = $order['vmid'];
        $pacode = $order['pacode'];

        $data = array(
            'vmid' => $vimid,
            'pacode' => $pacode,
            'game_type' => self::XYZP_GAME_TYPE,
            'related_id' => $order['id'],
            'prize_status' => 0,
            'createtime' => time(),
            'win_code' => $win_code
        );
	    $id = M('game_play')->add($data);
	    if($id) {
            $this->result(self::C_OK, array('win_code' => $win_code, 'game_play_id' => $id, 'message' => ''));
        }
        else {
            $this->result(self::C_ERROR_108, array('message' => '抽奖失败'));
        }
    }

    public function ch_test() {
        for($i =0; $i++; $i< 3) {
            $this->game_win_ch('0000005242', '100');
        }

        $this->result(self::C_OK, array('message' => '')); //通知机器失败
    }


    /**
     * 抽奖
     * @param $rate
     * @return bool
     */
    private function check_win($rate) {
        if($rate == 0) {
            return false;
        }
        $rate = floatval($rate);
        $rate_int = intval(1/$rate);

        return rand(1, $rate_int) == 1;
    }

    /**
     * 转盘游戏出货
     */
    public function xyzp_ch() {
        $id = $_REQUEST['play_id'];
        $where = array(
            'id', $id
        );
        $game_play = M('game_play')->where($where)->find();
        if(empty($game_play)) {
            $this->result(self::C_ERROR_108, array('message' => '找不到游戏记录'));
        }
//        else if($game_play['prize_status'] == $game_play['win_code']) {
//            $this->result(self::C_ERROR_108, array('message' => '奖品已领取'));
//        }
        M('game_play')->where($where)->save(array('prize_status' => $game_play['prize_status'] + 1));
        if(!empty($game_play['win_code'])) {
            // 中奖处理
            $this->game_win_ch($game_play['vmid'], $game_play['pacode']);
        }
        $this->result(self::C_OK, array('message' => ''));
    }

    /**
     * 游戏出货
     */
    private function game_win_ch($vmid, $pacode) {
        global $CFG;
        $port = $CFG['server_port'];
        $ip =	$CFG['server_ip'];

        $machine = M('machine')->where(array('vmid', $vmid))->find();
		if(!is_array($machine)){
			$this->result(self::C_ERROR_4, array('message' => '找不到机器'));
		}
        if(!empty($machine['gate'])){
            list($ip,$port) = explode(':', $machine['gate']);
        }

        $data = pack_data('*'.$pacode.'*'.$vmid,'80','21');
        $res = my_socket_send($ip,$port,$data);

        if($res != 'ok'){
            $this->result(self::C_ERROR_6, array('message' => '通知机器失败')); //通知机器失败
        }
        else {
            $sql = 'UPDATE ##road SET num=num-1 WHERE vmid="'.$vmid.'" AND pacode="'.$pacode.'" AND num>0';
            M()->excute($sql);
        }

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