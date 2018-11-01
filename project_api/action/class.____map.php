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
| 地图
+----------------------------------------------------------------------
*/
class MapAction extends Action{
	
	protected function before(){
		$this->set('app_key','CAc217229d387f46f2e15dc7f3b7b983');
	}

	private function log_ip($vmid,$ip){
		return;
		$file = PATH_CACHE.'ip_log.txt';
		$str = sprintf("%s %s IP:%s\r\n",date('H:i:s'),$vmid,$ip);
		$res = f_write($file,$str,'a');
	}

	public function location(){
		$vmid = $_REQUEST['vmid'];
		$parms = explode('*',$_REQUEST['parms']);
		$type = $parms[1];
		if($type == 1) $this->jizhan($vmid,$parms[2],$parms[3]);
	}

	//基站定位 
	private function jizhan($vmid,$lac,$cid){
		//接口返回的是谷歌地图坐标系
		$url = 'http://api.cellid.cn/cellid.php?lac='.$lac.'&cell_id='.$cid.'&coord=wgs84&token=TOKEN';
	}

	//通过ip定位
	public function ip(){
return;
		$vmid = $_REQUEST['vmid'];
		$machine = M('machine')->where(array('vmid',$vmid))->find();
		$ip = $_REQUEST['ip'] ? $_REQUEST['ip'] : $machine['ip'];
		if(empty($ip)) output('未获取到机器的ip地址！');
		$this->log_ip($vmid,$ip);
		$url = 'http://api.map.baidu.com/location/ip?ak='.$this->get('app_key').'&ip='.$ip.'&coor=bd09ll';
		$res = file_get_contents($url);
		$result = json_decode($res,true);
		$province = $result['content']['address_detail']['province'];
		$city = $result['content']['address_detail']['city'];
		$district = $result['content']['address_detail']['district'];
		//查询省份代码
		$provinceid = $cityid = $districtid = 0;
		$res = M('province')->where(array('provincename',$province))->select_one();
		if($res){
			$provinceid = $res['provinceid'];
			$res = M('city')->where(array(array('provinceid',$provinceid),array('cityname',$city)))->select_one();
			if($res){
				$cityid = $res['cityid'];
				$res = M('district')->where(array(array('cityid',$cityid),array('districtname',$district)))->select_one();
				if($res){
					$districtid = $res['districtid'];
				}
			}
		}
		$ar = array(
			'ip'		=> $ip,
			'provinceid'=> $provinceid,
			'cityid'	=> $cityid,
			'districtid'=> $districtid,
			'lng'		=> $result['content']['point']['x'],
			'lat'		=> $result['content']['point']['y'],
		//	'address'	=> $result['content']['address_detail']['street']
		);
		M('machine')->where(array('vmid',$vmid))->save($ar);
		output('定位地址为：'.$result['content']['address']);
	}
}