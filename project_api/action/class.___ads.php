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
| 广告类api
+----------------------------------------------------------------------
*/
class AdsAction extends Action{

	//获取广告xml
	public function index(){
		header("Content-type: text/xml");
		$id = intval($_REQUEST['id']);
		$item = M('ads_info')->where(array('id',$id))->find();
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo "<adinfo>\n";
		if(!$item){
			echo "<error>1</error>\n";
			echo "<msg>请求的数据不存在</msg>\n";
			echo "</adinfo>\n";
			return;
		}
		echo "<error>0</error>\n";
		echo "<adsid>".$item['id']."</adsid>\n";
		echo "<width>".$item['width']."</width>\n";
		echo "<height>".$item['height']."</height>\n";
		echo "<ads_name>".$item['ads_name']."</ads_name>\n";
		echo "<update_time>".$item['createtime']."</update_time>\n";
		echo "<materials>\n";
		$table = M('ads_material')->table().' am JOIN '.M('ads_material_info')->table().' ami ON ami.id=am.ma_id ';
		$fields = 'am.*,ami.url,ami.type';
		$where = ' WHERE am.ads_id='.$id;
		$order = ' ORDER BY am.order_no ASC ';
		$sql = 'SELECT '.$fields.' FROM '.$table.$where.$order;
		$materials = M('ads_material')->query($sql);
		$web_url = $this->get('cfg.web_url');
		foreach($materials as $m){
			echo "<material>\n";
			echo "<id>".$m['id']."</id>\n";
			echo "<url>".$web_url.'public/'.$m['url']."</url>\n";
			echo "<type>".$m['type']."</type>\n";
			echo "<width>".$m['width']."</width>\n";
			echo "<height>".$m['height']."</height>\n";
			echo "<top>".$m['_top']."</top>\n";
			echo "<left>".$m['_left']."</left>\n";
			echo "<link>".$m['link']."</link>\n";
			echo "<order_no>".$m['order_no']."</order_no>\n";
			echo "</material>\n";
		}
		echo "</materials>\n";
		echo "</adinfo>";
	}

	//获取广告计划
	public function plans(){
		header("Content-type: text/xml");
		$vmid = $_REQUEST['vmid'];
		$machine = M('machine')->where(array('vmid',$vmid))->find();
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo "<vmplan>\n";
		if(!$machine){
			echo "<error>1</error>\n";
			echo "<msg>未找到机器配置</msg>\n";
			echo "</vmplan>\n";
			return;
		}
		//查询适合机器的广告计划
		$sql = 'SELECT * FROM '.M('ads_plan')->table().' WHERE user_id='.$machine['user_id'].' AND (vmids="" OR vmids LIKE "'.$vmid.',%")';
		$data = M('ads_plan')->query($sql);
		if(count($data) < 1){
			echo "<error>1</error>\n";
			echo "<msg>暂无广告计划</msg>\n";
			echo "</vmplan>\n";
			return;
		}
		echo "<error>0</error>\n";
		echo "<plans>\n";
		foreach($data as $m){
			echo "<plan>\n";
			echo "<plan_name>".$m['plan_name']."</plan_name>\n";
			echo "<ads_id>".$m['ads_id']."</ads_id>\n";
			echo "<date_start>".$m['date_start']."</date_start>\n";
			echo "<date_end>".$m['date_end']."</date_end>\n";
			echo "<weeks>".$m['weeks']."</weeks>\n";
			echo "<time_start>".$m['time_start']."</time_start>\n";
			echo "<time_end>".$m['time_end']."</time_end>\n";
			echo "</plan>\n";
		}
		echo "</plans>\n";
		echo "</vmplan>\n";
	}

	public function ads_view(){
		$id = intval($_REQUEST['id']);
		$where = array(array('id',$id));
		$item = M('ads_info')->where($where)->find();
		if(!$item) output(L('权限不够或数据不存在'),'?a='.ACTION.'&m=ads');
		$table = M('ads_material')->table().' am JOIN '.M('ads_material_info')->table().' ami ON ami.id=am.ma_id ';
		$fields = 'am.*,ami.url,ami.type';
		$where = ' WHERE am.ads_id='.$id;
		$order = ' ORDER BY am.order_no ASC ';
		$sql = 'SELECT '.$fields.' FROM '.$table.$where.$order;
		$item['materials'] = M('ads_material')->query($sql);
		$this->set('item',$item);
		$this->tpl('ads_view');
	}

	/*
	* 礼品机商品展示，买
	* @id 商品id
	*/
	public function buy(){
		$gid = intval($_REQUEST['gid']);
		$vmid = $_REQUEST['vmid'];
		$this->set('vmid',$vmid);
		switch($_REQUEST['act']){
			case 'ajax_machine_goods':
				$where = array(
					array('vmid',$vmid),
					array('goods_id',$gid)
				);
				$res = M('road')->fields('SUM(num) AS num,price')->where($where)->find();
				if(intval($res['num']) < 1){
					echo json_encode(array('error' => 1,'msg' => '缺货！'));
					return;
				}
				echo json_encode($res);
			break;
			case 'ajax_qrcode':
				//检查机器是否在线
				$port = $this->get('cfg.server_port');
				$ip =	$this->get('cfg.server_ip');
				$machine = M('machine')->fields('gate')->where(array('vmid',$vmid))->find();
				if(!empty($machine['gate'])){
					list($ip,$port) = explode(':',$machine['gate']);
				}
				$data = pack_data('*'.$vmid,'80','01'); 
				$res = my_socket_send($ip,$port,$data);
				if($res != 'online'){
					//机器不在线
					echo json_encode(array('error' => 1,'msg' => '当前机器离线，不能购物！'));
					return;
				}
				//获取一个可以出货的货道
				$where = array(
					array('vmid',$vmid),
					array('num',0,'>'),
					array('goods_id',$gid)
				);
				$road = M('road')->where($where)->order('num','DESC')->select_one();
				if(!$road){
					echo json_encode(array('error' => 1,'msg' => '本商品已售罄,请选择其他商品！'));
					return;
				}
				//获取二维码
				$url = $this->get('cfg.web_url').'online_pay.php?a='.$_REQUEST['pay_type'].'&m=qrcode&vmid='.$vmid.'&pacode='.$road['pacode'];
				$qrcode = file_get_contents($url);
				echo json_encode(array('error' => 0,'qrcode' => urlencode($qrcode)));
			break;
			default:
				if($gid < 1) exit('No goods!');
				$goods = M('goods')->where(array('id',$gid))->find();
				$this->set('goods',$goods);
				//获取最大的网上订单号
				$res = M('online_order')->fields('id')->order('id','DESC')->select_one();
				$this->set('max_order_id',$res['id']);
				//$this->set('max_order_id',1);
				$style = intval($_REQUEST['style']);
				$this->tpl('buy_'.$style);
			break;
		}
	}

	//图片轮播
	public function carousel(){
		$PlayerName = $_REQUEST['PlayerName'];
		if(!empty($PlayerName)){
			$vmid = substr($PlayerName,3);
			while(strlen($vmid) < 10) $vmid = '0'.$vmid;
		}else{
			echo '<h2>请设置终端参数！</h2>';
			return;
		}
		//默认显示多少个商品
		$cols = $_REQUEST['cols'] ? intval($_REQUEST['cols']) : 4;
		$speed = $_REQUEST['speed'] ? intval($_REQUEST['speed']) : 2;
		$style = intval($_REQUEST['style']);
		$table = M('road')->table().' r JOIN '.M('goods')->table().' g ON g.id=r.goods_id';
		$where = array(
			array('r.vmid',$vmid),
			array('r.goods_id',0,'>'),
			array('g.goods_pic','','<>')
		);
		$fields = 'DISTINCT(g.id),r.price AS goods_price,g.goods_name,g.goods_pic';
		$goods = M()->table($table)->fields($fields)->where($where)->select();
		if(count($goods) < 1){
			echo '<h2>暂无商品可售！</h2>';
			return;
		} 
		$this->set('goods',$goods);
		$this->set('cols',$cols);
		$this->set('vmid',$vmid);
		$this->set('speed',$speed);
		$this->tpl('carousel_'.$style);
	}

	//检查是否已支付，已出货
	public function ajax_check_buy(){
		$vmid = $_REQUEST['vmid'];
		$max_id = $_REQUEST['max_id'];
		$where = array(
			array('vmid',$vmid),
			array('id',$max_id,'>'),
			array('pay_status',1)
		);
		$res = M('online_order')->fields('id,pay_status,trade_status')->where($where)->select_one();
		if(is_array($res) && count($res) > 0){
			$res['ok'] = 1;
			echo json_encode($res);
		}else{
			echo json_encode(array('ok' => 0));
		}
	}
}