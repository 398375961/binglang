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
| 礼品派送
+----------------------------------------------------------------------
*/
class PrizeModel{ 

	/* 
	* 通知机器发货
	$item = array(
		'vmid'
		'pacodes'	 货道编号 110,111...
		'goods_rand' 是否随机出货
		'status'	 0 锁定 1 正常 2 测试
		'weichat'	 微信公众号
	);
	$client 微信用户唯一标示，或者是浏览器唯一标识
	*/
	public function push_prize($item,$client){
		//查询货道信息
		if(empty($item['pacodes'])) return '礼品已发完，请等待补货';
		$where = array(
			array('vmid',$item['vmid']),
			array('num',0,'>'),
			array('pacode','('.$item['pacodes'].')','IN')
		);
		$roads = M('road')->where($where)->order('pacode','ASC')->select();
		if(count($roads) < 1) return '礼品已发完，请等待补货'; //没有货了
		if($item['goods_rand'] == 1){
			//随机出货
			$rand = array_rand($roads);
			$road = $roads[$rand];
		}else{
			//顺序出货
			$road = $roads[0];
		}
		if($this->notify_machine($road)){
			$ar = array(
				'vmid'				=> $item['vmid'],
				'weichat'			=> $item['weichat'],
				'clientname'		=> $client,
				'prizetime'			=> NOW_TIME,
				'pacode'			=> $road['pacode'],
				'goodsname'			=> $road['goods_name'],
				'pr_status'			=> $item['status'],
			);
			M('prizelog')->add($ar);
			//商品数目自减
			M('road')->where(array('id',$road['id']))->save(array('num' => max(0,$road['num'] - 1)));
			return true;
		}
		return '通知机器出货失败，请稍后重试';
	}

	//检查机器是否在线
	public function check_machine($vmid){
		global $CFG;
		$port = $CFG['server_port'];
		$ip =	$CFG['server_ip'];
		$machine = M('machine')->fields('gate')->where(array('vmid',$vmid))->find();
		if(!empty($machine['gate'])){
			list($ip,$port) = explode(':',$machine['gate']);
		}
		$data = pack_data('*'.$vmid,'80','01'); 
		$res = my_socket_send($ip,$port,$data);
		return $res == 'online' ? true : false;
	}

	//通知机器发货
	private function notify_machine($road){
		if(!$road || sizeof($road) < 1) return false;
		global $CFG;
		$port = $CFG['server_port'];
		$ip =	$CFG['server_ip'];
		$machine = M('machine')->fields('gate')->where(array('vmid',$road['vmid']))->find();
		if(!empty($machine['gate'])){
			list($ip,$port) = explode(':',$machine['gate']);
		}
		$data = pack_data('*'.$road['pacode'].'*'.$road['vmid'],'80','21'); 
		$res = my_socket_send($ip,$port,$data);;
		if($res == 'ok') return true;
		return false;
	}
}