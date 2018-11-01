<?php
/*
* 2015-09-10 修改bug，每个worker独立使用mysql数据库对象，避免同时操作数据库
* 2016-07-07 增加 自动配置货道的时候，设定好货道容量
*		     增加 对交易数据flowid判断，防止重复提交数据
			 $sw_tab_fd->column('flowid',swoole_table::TYPE_INT,2);
* 2016-07-07 修改 日志按日期分目录存储
* 2016-07-23 增加 单片机程序自动升级功能
* 2016-08-15 增加 校园卡购物充值查询的协议 0x14 35,0x14 33
* 2016-09-24 增加 校园卡是否允许购物查询协议 0x14 40
* 2016-09-29 修改 校园卡是否允许购物查询协议 0x14 40 增加密码功能
* 2016-09-30 增加 校园卡在线充值（二维码） 0x14 83 84
* 2016-10-20 增加 重置机器管理密码 0x80 11
* 2016-12-03 增加 一键上货 0x11 34
* 2017-03-04 增加 取货命令 0x80 22，0x14 22
*			 修改 定位(增加基站定位) 
* 2017-04-09 增加 收银板状态上报命令0x12 3a，修改 0x10 34整机状态上报
* 2017-05-09 增加 线上退款功能（修改0x14 80命令）
			 修改 故障上报warn数据处理
* 2017-09-20 增加会员系统积分功能
*/
define('PATH_ROOT',dirname(__FILE__).'/');  //根目录
define('ROOT_PWC',PATH_ROOT.'../'); //项目根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('PATH_MODULE',ROOT_PWC.'module/');
define('DEBUG',true); //是否为调试模式，设置为true把sql都保存起来 false 只保存出错的sql
define('LOG_WRITE',true); //是否写数据日志
if(!DEBUG) error_reporting(0);
include(PATH_LIB.'config.php'); //配置文件
include(PATH_LIB.'sys_config.php'); //配置文件
define('SERV_IP','127.0.0.1');
// define('SERV_PORT',8090);
//define('SERV_IP','0.0.0.0');
define('SERV_PORT',10000);
define('WEB_URL',$CFG['web_url']);
define('PAY_URL',$CFG['web_url'].'online_pay.php'); //支付宝扫码支付 微信扫码支付 调用网址
include(PATH_LIB.'function.php'); //函数库
include(PATH_LIB.'class.mysql.php'); //加载数据库处理类
include(PATH_LIB.'class.module.php'); //加载module类的基类
/* 
* 内存数据表，最大N行 必须是2的指数
* key : fd
* value : array(vmid,update_time,flowid)
*/
$sw_tab_fd = new swoole_table(1024*8);
$sw_tab_fd->column('vmid',swoole_table::TYPE_STRING,10); //机器编号
$sw_tab_fd->column('update_time',swoole_table::TYPE_INT,4); //最后保存到数据库的时间
$sw_tab_fd->column('flowid',swoole_table::TYPE_INT,2);
$sw_tab_fd->create();
/* 
* 内存数据表，最大N行 必须是2的指数
* key : vmid
* value : array(fd)
*/
$sw_tab_vm = new swoole_table(1024*8); 
$sw_tab_vm->column('fd',swoole_table::TYPE_INT,4);
$sw_tab_vm->create();

$serv = new swoole_server('0.0.0.0',SERV_PORT,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);
$serv->set(array(
	'heartbeat_check_interval'	=> 60, //每N秒侦测一次心跳
    'heartbeat_idle_time'		=> 120, //一个TCP连接如果在N秒内未向服务器端发送数据，将会被切断
	'worker_num'				=> 8, //启动的worker进程数 ,设置为CPU的1-4倍最合理
	'task_worker_num'			=> 4, //启动的task进程数
	'max_request'				=> 0, //worker进程的最大任务数 每个worker进程在处理完超过此数值的任务后将自动退出 ,当worker进程内发生致命错误或者人工执行exit时，进程会自动退出。主进程会重新启动一个新的worker进程来处理任务
	'log_file'					=> PATH_ROOT.'log/swoole.log', //swoole日志，swoole错误日志文件
	'daemonize'					=> true //是否做为守护进程，后台运行
));
$serv->on('Connect','my_onConnect');
$serv->on('Receive','my_onReceive');
$serv->on('Task','my_task');
$serv->on('Finish','my_taskfinish');
$serv->on('Close','my_onClose');
$serv->on('WorkerStart',function($serv,$worker_id){
	if($worker_id == 0){
		$serv->tick(3000,function($id){
			my_timer();
		});
	}
});
M_SWOOLE(0,'status')->where(array('netstatus',1))->save(array('netstatus' => 0));
$serv->start();


function my_onConnect($serv, $fd){
	swoole_log("建立新的连接: Client[{$fd}]",$fd);
}

//检查连接，删除掉掉线的tcp连接
function check_links($serv,$fd){
	global $sw_tab_fd,$sw_tab_vm;
	static $_CHECK_TIME = 0;
	if($machine = $sw_tab_fd->get($fd)){
		if($machine['update_time'] < time() - 60){
			$machine['update_time'] = time();
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(array('netstatus' => 1,'lastclienttime' => $machine['update_time']));
			$sw_tab_fd->set($fd,$machine);
			$sw_tab_vm->set($machine['vmid'],array('fd' => $fd));
		}
	}
	if(time() < $_CHECK_TIME + 300) return;
	$_CHECK_TIME = time();
	M_SWOOLE($serv->worker_id,'status')->where(array(array('netstatus',1),array('lastclienttime',$_CHECK_TIME - 300,'<')))->save(array('netstatus' => 0));
}

//异步任务
function my_task($serv,$task_id,$from_id,$data){
	$do_post = true;
	$send_res = false;
	$post_fields = [];
	switch($data['key']){
		case 'location':
			//地理位置上报
			$url = WEB_URL.'index.php?a=map&m=location&parms='.$data['val'];
			$do_post = false; //不处理
		break;
		case 'wx_transfer':
			//微信企业付款触发
			$url = WEB_URL.'index.php';
			$post_fields = [
				'a'		=> 'weixin',
				'm'		=> 'api_transfer',
				'vmid'	=> $data['val']
			];
		break;
		case 'refund':
			//退款处理
			if($data['val']['dynamic_id'] === 'H5'){
				$url = WEB_URL.'s/index.php?m=refund';
				$post_fields = [
					'a'				=> 'index',
					'm'				=> 'refund',
					'trade_type'	=> $data['val']['trade_type'],
					'out_trade_no'	=> $data['val']['out_trade_no']
				];
			}else{
				$url = PAY_URL;
				$post_fields = [
					'a'				=> $data['val']['trade_type'],
					'm'				=> 'refund',
					'vmid'			=> $data['val']['vmid'],
					'out_trade_no'	=> $data['val']['out_trade_no']
				];
			}
		break;
		case 'shop_sale':
			//商城取货，出货成功回调
			$url = WEB_URL.'api.php';
			$post_fields = [
				'a'		=> 'shop',
				'm'		=> 'callback',
				'vmid'	=> $data['vmid'],
				'id'	=> $data['order_id']
			];
		break;
		case 'ic_sale': //ic卡出货
		case 'ic_recharge': //IC卡现金充值
		case 'ic_balance': //IC卡余额查询
		case 'ic_buycheck': //IC卡购买限制检查
		case 'ic_recharge_wx': //IC卡微信二维码充值
		case 'ic_recharge_alipay': //IC卡支付宝二维码充值
			$send_res = true;
			$url = WEB_URL.'api.php';
			$post_fields = [
				'a'		=> 'ic_card',
				'm'		=> substr($data['key'],3),
				'vmid'	=> $data['vmid'],
				'data'	=> $data['data']
			];
		break;
		case 'hy_sale': //会员卡消费积分
			$send_res = false;
			$url = WEB_URL.'api.php';
			$post_fields = [
				'a'		=> 'huiyuan',
				'm'		=> 'sale',
				'vmid'	=> $data['vmid'],
				'saleid'=> $data['saleid'],
				'money' => $data['money'],
				'cardid'=> $data['cardid'],
			];
		break;
	}
	if($do_post){
		$url_ch = curl_init();
		curl_setopt($url_ch,CURLOPT_URL,$url);
		curl_setopt($url_ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($url_ch,CURLOPT_TIMEOUT,10);
		curl_setopt($url_ch,CURLOPT_POST,1);
		curl_setopt($url_ch,CURLOPT_POSTFIELDS,$post_fields);
		$res = curl_exec($url_ch);
		curl_close($url_ch);
		if($send_res){
			$json = json_decode($res);
			if($json->ok){
				if($json->big_sort == '1d' && ($json->small_sort == '30' or $json->small_sort == '31')){
					send_result($serv,$data['fd'],$from_id,$data['flowid'],$json->small_sort);
				}else{
					$serv->send($data['fd'],pack_data($json->str,$json->big_sort,$json->small_sort,$data['flowid']));
				}
			}else{
				send_result($serv,$data['fd'],$from_id,$data['flowid'],'31');
			}
		}
	}
}

function my_taskfinish($serv,$task_id,$data){}

function my_timer(){
	global $serv;
	//查询订单
	$where = array(
		['pay_status',1],
		['trade_status',0],
	);
	$orders = M('online_order')->where($where)->order('id','DESC')->limit(20)->select();
	foreach($orders as $order){
		if($order['paytime'] < time() - 60) break;
		$fd_machine = get_vm_fd($order['vmid']);
		if($fd_machine < 1){
			swoole_log('通知机器发货失败,机器'.$order['vmid'].'不在线',$fd);
			continue;
		}
		$str = '*'.$order['pacode'].'*'.$order['id'];
	    if($order['trade_type'] == 'weixin') $str .= '*0';
	    if($order['trade_type'] == 'alipay') $str .= '*1';
		$ok = $serv->send($fd_machine,pack_data($str,'80','20'));
		swoole_log('通知机器发货:'.$str.($ok ? 'ok' : 'failed'),$fd_machine);
	}
}

function my_onReceive($serv, $fd, $from_id, $data){
	global $sw_tab_fd;
	check_links($serv,$fd);
	log_hex($data,$fd); //保存16进制日志
	$package = un_pack($data);
	if($package[0] == '00'){
		swoole_log('命令['.$package[0].$package[1].'],DTU',$fd);
		return;
	}
	if($package[0] != 'ff'){
		swoole_log('命令['.$package[0].$package[2].'],数据:'.$package[3],$fd);
		if(!$package[4]){
			//数据验证失败
			send_result($serv,$fd,$from_id,$package[1],'ff','校验计算值：'.$package[5].'；接收值'.$package[6]);
			return;
		}
	}
	if($sw_tab_fd->exist($fd)){
		$machine = $sw_tab_fd->get($fd);
		if($machine['flowid'] == $package[1]*100 + $package[2]){
			swoole_log('重复数据上报',$fd);
			send_result($serv,$fd,$from_id,$package[1],30); //返回接收成功
			return;
		}
		$machine['flowid'] = $package[1]*100 + $package[2]; 
		$sw_tab_fd->set($fd,$machine);
	}
	switch($package[0]){
		case '10':
			//连接
			do_connect($serv, $fd, $from_id,$package);
		break;
		case '11':
			//设置上报
			do_report($serv, $fd, $from_id,$package);
		break;
		case '12':
			//机器状态汇报类
			do_machine_status($serv, $fd, $from_id,$package);
		break;
		case '14':
			//机器交易汇报类
			do_sale($serv, $fd, $from_id,$package);
		break;
		case '80':
			//网关主动上报
			do_up_report($serv, $fd, $from_id,$package);
		break;
		case '90':
			//单片机自动升级程序
			do_auto_update($serv, $fd, $from_id,$package);
		break;
		case 'ff':
			//心跳包
			if(!$sw_tab_fd->exist($fd)){
				//没有登录
				send_result($serv,$fd,$from_id,$package[1],'fe','心跳包');
				break;
			}
			send_result($serv,$fd,$from_id,$package[1],'30','心跳包');
		break;
		default:
			$serv->send($fd,$package[0].'unknow order!');
		break;
	}
	
}

//连接被关闭
function my_onClose($serv,$fd){
	global $sw_tab_fd;
	$machine = $sw_tab_fd->get($fd);
	if($machine['vmid']){
		//设置离线
		M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(array('netstatus' => 0));
	}
	$sw_tab_fd->del($fd);
	swoole_log("连接断开：Client[{$fd}]",$fd);
}


//处理登录大类
function do_connect($serv, $fd, $from_id,$package){
	global $sw_tab_fd,$sw_tab_vm;
	$data = explode('*',$package[3]);
	switch($package[2]){
		case '30':
			//机器登录 *1000000012*000000*123456* 机器通讯识别号（机器出厂编号）*客户机器自编号*机器连接密码
			swoole_log('机器登录',$fd);
			$machine = M_SWOOLE($serv->worker_id,'machine')->where(array('vmid',$data[1]))->find();
			if(!is_array($machine) || sizeof($machine) == 0){
				return send_result($serv, $fd, $from_id,$package[1],'fe');
			}
			if($machine['pwd'] != $data[3]){
				//密码错误
				return send_result($serv, $fd, $from_id,$package[1],'34','登录失败，密码错误,'.$data[1]);
			}
			$sw_tab_fd->set($fd,array('vmid' => $machine['vmid'],'update_time' => time(),'flowid' => 1000));
			$sw_tab_vm->set($machine['vmid'],['fd' => $fd]);
			//保存机器状态
			$status = M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->find();
			if($status){
				M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(array('netstatus' => 1,'lastclienttime' => time()));
			}else{
				$ar = array(
					'netstatus'		=> 1,//在线
					'vmid'			=> $machine['vmid'],
					'doorkind'		=> 1,
					'coin_status'	=> 1,
					'bill_status'	=> 1,
					'device_status'	=> 1,
					'dhjc'			=> 1,
					'lastclienttime'=> time()
				);
				M_SWOOLE($serv->worker_id,'status')->add($ar);
			}
			M_SWOOLE($serv->worker_id,'machine')->where(array('id',$machine['id']))->save(['gate' => SERV_IP.':'.SERV_PORT]);
			send_result($serv,$fd,$from_id,$package[1],'30','login');
			return;
		break;
		case '34':
			//整机基本状态 *时间*门状态*硬币状态*纸币状态*驱动板状态*掉物检测 *150413124006*1*1*1*1*1
			swoole_log('整机基本状态',$fd);
			if(!$sw_tab_fd->exist($fd)){
				return send_result($serv, $fd, $from_id,$package[1],'fe');
			}
			$machine = $sw_tab_fd->get($fd);
			$data[1] = format_time($data[1]); //时间
			$ar = array(
				'netstatus'		=> 1,//在线
				'doorkind'		=> intval($data[2]),
				'coin_status'	=> intval($data[3]),
				'bill_status'	=> intval($data[4]),
				'device_status'	=> intval($data[5]),
				'lastclienttime'=> time()
			);
			if(array_key_exists(6,$data)){
				$ar['dhjc'] = intval($data[6]);
			}
			if(array_key_exists(7,$data)){
				$ar['cash_device_status'] = intval($data[7]);
			}
			$status = M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->find();
			if($status){
				M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save($ar);
			}else{
				$ar['vmid'] = $machine['vmid'];
				M_SWOOLE($serv->worker_id,'status')->add($ar);
			}
			return send_result($serv, $fd, $from_id,$package[1],'30','整机状态：'.$package[3]);
		break;
		case '35':
			//机器程序版本 *时间*版本号 *150413124006*vend01.1210
			swoole_log('机器程序版本',$fd);
			if(!$sw_tab_fd->exist($fd)){
				return send_result($serv, $fd, $from_id,$package[1],'fe');
			}
			$machine = $sw_tab_fd->get($fd);
			//$data[1] = format_time($data[1]); //时间
			$ar = array(
				'netstatus'		=> 1,//在线
				'version'		=> $data[2],
				'lastclienttime'=> time()
			);
			$status = M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->find();
			if($status){
				M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save($ar);
			}else{
				$ar['vmid'] = $machine['vmid'];
				M_SWOOLE($serv->worker_id,'status')->add($ar);
			}
			return send_result($serv, $fd, $from_id,$package[1],'30','机器版本：'.$package[3]);
		break;
	}
	send_result($serv,$fd,$from_id,$package[1],'31','未知数据业务类型');
}


//机器状态汇报类
function do_machine_status($serv, $fd, $from_id,$package){
	global $sw_tab_fd;
	$data = explode('*',$package[3]);
	if(!$sw_tab_fd->exist($fd)){
		return send_result($serv, $fd, $from_id,$package[1],'fe');
	}
	$machine = $sw_tab_fd->get($fd);
	$cvalue = false;
	switch($package[2]){
		case '30': 
			//门控状态上报 *时间*柜门编码*门控状态 *150413124006*1*2
			swoole_log('门控状态上报',$fd);
			$close = intval($data[3]);
			$cvalue = '柜门'.$data[2].':'.($close == 1 ? '关闭':'打开');
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(['doorkind' => $close]);
		break;
		case '31':
			//温度状态上报 *时间*箱柜编码*状态*温度值
			swoole_log('温度状态上报',$fd);
			$driver = $data[2];
			$tmpvalue = $data[4];
			$cvalue = '机柜'.$data[2].'温度:'.$tmpvalue;
			if($data[3] == 1) $cvalue .= ';温度超出范围！';
			if($data[3] == 2) $cvalue .= ';正常';
			if($data[3] == 3) $cvalue .= ';找不到温控探头！';
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(['tmpvalue' => $tmpvalue]);
		break;
		case '32':
			//货道状态上报 *时间*货道编号*状态
			swoole_log('货道状态上报',$fd);
			$cvalue = '货道['.$data[2].']';
			if($data[3] == 1) $cvalue .= '正常！';
			if($data[3] == 2) $cvalue .= '电机故障！';
			if($data[3] == 3) $cvalue .= '电磁锁故障！';
		break;
		case '33':
			//硬币器状态上报 *时间*状态
			swoole_log('硬币器状态上报',$fd);
			$cstatus = $data[2];
			$cvalue = '硬币器:'.($data[2] ? '正常！':'异常！');
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(['coin_status' => $cstatus]);
		break;
		case '34':
			//纸币器状态上报 *时间*状态
			swoole_log('纸币器状态上报',$fd);
			$cstatus = $data[2];
			$cvalue = '纸币器:'.($data[2] ? '正常！':'异常！');
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(['bill_status' => $cstatus]);
		break;
		case '35':
			//驱动板状态上报 *时间*驱动编号*状态[*所有状态]
			swoole_log('驱动板状态上报',$fd);
			$cstatus = intval($data[3]);
			$cvalue = '驱动板['.$data[2].']:'.($data[3] ? '正常！':'异常！');
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save([
				'device_status' => isset($data[4]) ? intval($data[4]) : $cstatus
			]);
		break;
		case '36':
			//掉货检测状态上报 *时间*掉货编号*状态[*所有状态]
			swoole_log('掉货检测状态上报',$fd);
			$driver = $data[2];
			$cstatus = intval($data[3]);
			$cvalue = '掉货检测板['.$data[2].']:'.($data[3] ? '正常！':'异常！');
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save([
				'dhjc' => isset($data[4]) ? intval($data[4]) : $cstatus
			]);
		break;
		case '37':
			//机器运行状态上报 *时间*状态
			swoole_log('机器运行状态上报',$fd);
			$cvalue = '机器运行状态:';
			if($data[2] == 1) $cvalue .= '正常！';
			if($data[2] == 2) $cvalue .= '设置状态！';
			if($data[2] == 3) $cvalue .= '重启中！';
			if($data[2] == 4) $cvalue .= '故障！';
			if($data[2] == 5) $cvalue .= '振动报警！';
		break;
		case '38':
			//零钱数量上报 *时间*纸币金额*硬币金额
			swoole_log('零钱数量上报',$fd);
			$cash_amount = intval($data[2]);
			$coin_amount = intval($data[3]);
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(['coin_amount' => $coin_amount,'cash_amount' => $cash_amount]);
		break;
		case '39':
			//上报机器机器位置 *类型[*参数1*参数2] 类型为1表示基站定位 $package[3]
			swoole_log('地理位置上报',$fd);
			$serv->task(array('key' => 'location','vmid' => $machine['vmid'],'val' => $package[3]));
		break;
		case '3a':
			//收银板状态上报 *状态
			swoole_log('收银板状态上报',$fd);
			$cash_device_status = intval($data[1]);
			M_SWOOLE($serv->worker_id,'status')->where(array('vmid',$machine['vmid']))->save(['cash_device_status' => $cash_device_status]);
			$cvalue = '收银板状态:'.($cash_device_status ? '正常':'异常');
		break;
	}
	if($cvalue !== false){
		$ar = array(
			'vmid'			=> $machine['vmid'],
			'cstatus'		=> $package[3],
			'cvalue'		=> $cvalue,
			'createtime'	=> time()
		);
		M_SWOOLE($serv->worker_id,'warn')->add($ar);
	}
	send_result($serv,$fd, $from_id,$package[1],'30');
}

//交易类处理
function do_sale($serv, $fd, $from_id,$package){
	global $sw_tab_fd;
	$data = explode('*',$package[3]);
	if(!$sw_tab_fd->exist($fd)){
		return send_result($serv, $fd, $from_id,$package[1],'fe');
	}
	$machine = $sw_tab_fd->get($fd);
	$return_code = '30'; //返回码
	switch($package[2]){
		case '22':
			//商城取货，出货上报 *时间*订单编号
			$data[1] = format_time($data[1]);
			$id = intval($data[2]);
			$order = M_SWOOLE($serv->worker_id,'quhuo')->where(array('id',$id))->find();
			if($order){
				$ar = array(
					'saletime'		=> $data[1],
					'sale_status'	=> 1,
				);
				M_SWOOLE($serv->worker_id,'quhuo')->where(array('id',$id))->save($ar);
				//货道商品数减一
				$sql = 'UPDATE ##road SET num=num-1 WHERE vmid="'.$order['vmid'].'" AND pacode="'.$order['pacode'].'" AND num>0';
				M_SWOOLE($serv->worker_id,'road')->excute($sql);
				//投递商城出货回调任务
				$serv->task(array('key' => 'shop_sale','vmid' => $order['vmid'],'order_id' => $id));
			}
		break;
		case '31': //纸币收支 *时间*交易号*收支*币值*数量
		case '32': //硬币收支 *时间*交易号*收支*币值*数量
			swoole_log($package[2] == '31' ? '纸币收支' : '硬币收支',$fd);
			$currency = $package[2] == '31' ? 1 : 0;
			$data[1] = format_time($data[1]);
			$ar = array(
				'vmid'			=> $machine['vmid'],
				'currency'		=> $currency,//0：硬币 1：纸币 2: 普通卡 3：学生卡
				'amount'		=> $data[4],//面值
				'time'			=> $data[1], //销售时间
				'payments'		=> $data[3],//0：收币1：找零2：吞币
				'num'			=> $data[5],//数量
				'saleid'		=> $data[2],//交易号
				'card'			=> '', //卡号
				'coinchannel'	=> '',
				'createtime'	=> time(),//传输时间
				'saledate'		=> date('Ymd',$data[1]),//销售日期
				'createdate' 	=> date('Ymd') //传输日期
			);
			M_SWOOLE($serv->worker_id,'pay')->add($ar);
		break;
		case '33':
			swoole_log('校园卡出货上报',$fd);
			//校园卡出货，支付处理
			$serv->task(array('key' => 'ic_sale','vmid' => $machine['vmid'],'fd' => $fd,'flowid' => $package[1],'data' => $package[3]));
			return;
		break;

		case '34':
			//校园卡充值处理 *校园卡卡号*充值金额
			swoole_log('校园卡充值',$fd);
			$serv->task(array('key' => 'ic_recharge','vmid' => $machine['vmid'],'fd' => $fd,'flowid' => $package[1],'data' => $package[3]));
			return;
		break;

		case '35':
			//校园卡余额查询 *校园卡卡号
			swoole_log('校园卡余额查询',$fd);
			$serv->task(array('key' => 'ic_balance','vmid' => $machine['vmid'],'fd' => $fd,'flowid' => $package[1],'data' => $package[3]));
			return;
		case '36':
			//出货 *时间*交易流水号*货道编码*销售价格*商品编码*数量*类型
			swoole_log('出货上报',$fd);
			$data[1] = format_time($data[1]);
			//查询商品信息
			$road = M_SWOOLE($serv->worker_id,'road')->where(array(array('vmid',$machine['vmid']),array('pacode',$data[3])))->find();
			$ar = array(
				'saleid'		=> $data[2], // 交易号
				'saletype'		=> $data[7], //销售类型
				'salecard'		=> '',
				'salemoney'		=> $data[4],
				'saletime'		=> $data[1],
				'salenum'		=> $data[6],
				'pacode'		=> $data[3], //货道
				'vmid'			=> $machine['vmid'],
				'createtime'	=> time(),
				'saledate'		=> date('Ymd',$data[1]),
				'createdate' 	=> date('Ymd'), //传输日期
				'goods_id'		=> intval($road['goods_id']),
				'goods_name'	=> $road['goods_name'],
				'price_cb'		=> $data[4],
			);
			if($ar['goods_id'] > 0){
				$goods = M_SWOOLE($serv->worker_id,'goods')->where(array('id',$ar['goods_id']))->find();
				if($goods){
					$ar['goods_name'] = $goods['goods_name']; 
					$ar['price_cb'] = $goods['goods_price'];
					if($goods['goods_num'] > 0){
						M_SWOOLE($serv->worker_id,'goods')->where(['id',$goods['id']])->save(['goods_num' => $goods['goods_num'] -1]);
					}
				}
			}
			$ok = M_SWOOLE($serv->worker_id,'saledetail')->add($ar);
			//货道商品数减一
			if($ok && $road && $road['num'] > 0 && $ar['salenum'] > 0){
				M_SWOOLE($serv->worker_id,'road')->where(array('id',$road['id']))->save(array('num' => $road['num'] - 1));
			}
			if($ar['saletype'] == 4){
				//银联卡补充数量
				$ar = array(
					'vmid'			=> $machine['vmid'],
					'currency'		=> 2,//0：硬币 1：纸币 2: 银联卡 3：学生卡
					'amount'		=> $data[4],//面值
					'time'			=> $data[1], //销售时间
					'payments'		=> 0,//0：收币1：找零2：吞币
					'num'			=> 1,//数量
					'saleid'		=> $data[2],//交易号
					'card'			=> '', //卡号
					'coinchannel'	=> '',
					'createtime'	=> time(),//传输时间
					'saledate'		=> date('Ymd',$data[1]),//销售日期
					'createdate' 	=> date('Ymd') //传输日期
				);
				M_SWOOLE($serv->worker_id,'pay')->add($ar);
			}
		break;
		case '37':
			//在线付款，在下面做处理，先返回
			swoole_log('条码支付',$fd);
		break;
		case '38':
			//条码支付查询 *时间*交易号*付款码(条码)
			$dynamic_id = $data[3];
			$saleid = $data[2];
			$vmid = $machine['vmid'];
			$where = array(array('vmid',$vmid),array('dynamic_id',$dynamic_id));
			$order = M_SWOOLE($serv->worker_id,'online_order')->where($where)->find();
			if(!is_array($order) || sizeof($order) < 1){
				$return_code = '32';
				break;
			}
			$return_code = '33'; //支付成功返回33
			if($order['trade_status'] == 1){
				//已经发货了
			}else{
				if($order['pay_status'] == 1){
					//支付成功了，修改状态，设置为已经发货
					$ar = array(
						'trade_status'		=> 1,
						'finishtime'		=> time(), //发货时间，成交时间
					);
					M_SWOOLE($serv->worker_id,'online_order')->where(array('id',$order['id']))->save($ar);
				}else{
					$return_code = '32';
				}
			}
		break;
		case '39':
			//获取最大的交易号，也可用于校正交易号，返回机器当前交易号和后台交易号之间的最大值 *机器当前交易号
			$saleid = $data[1];
			if(!is_numeric($saleid)) $saleid = '00000001';
			$vmid = $machine['vmid'];
			$where = array('vmid',$vmid);
			$saledetail = M_SWOOLE($serv->worker_id,'saledetail')->fields('MAX(saleid) AS saleid')->where($where)->select_one();
			if(is_array($saledetail) && count($saledetail) > 0 && $saledetail['saleid'] > $saleid) $saleid = $saledetail['saleid'];
			$pay = M_SWOOLE($serv->worker_id,'pay')->fields('MAX(saleid) AS saleid')->where($where)->select_one();
			if(is_array($pay) && count($pay) > 0 && $pay['saleid'] > $saleid) $saleid = $pay['saleid'];
			$serv->send($fd,pack_data($saleid,'1d','39',$package[1]));
			return;
		break;
		case '40':
			//ic卡购买限制查询
			swoole_log('ic卡购买限制查询',$fd);
			$serv->task(array('key' => 'ic_buycheck','vmid' => $machine['vmid'],'fd' => $fd,'flowid' => $package[1],'data' => $package[3]));
			return;
		break;
		case '80':
			//微信二维码/支付宝 支付出货 *时间*交易流水号*货道编码*订单编号[*是否出货失败]
			$data[1] = format_time($data[1]);
			$pacode = intval($data[3]);
			$order_id = intval($data[4]);
			$failed = intval($data[5]);
			$order = M_SWOOLE($serv->worker_id,'online_order')->where(array('id',$order_id))->find();
			if(!is_array($order) || sizeof($order) < 1){
				$return_code = '31'; //保存错误
				break;
			}
			if($order['trade_status'] == 1) break; //已经出货，重复提交
			if($failed == 1){
				//出货失败，已返现到机器界面
				$ar = array(
					'vmid'			=> $machine['vmid'],
					'currency'		=> $order['trade_type'] == 'alipay' ? 4 : 5,
					'amount'		=> $order['amount'],//面值
					'time'			=> $data[1], //销售时间
					'payments'		=> 0,//0：收币1：找零2：吞币
					'num'			=> 1,//数量
					'saleid'		=> $data[2],//交易号
					'card'			=> '', //卡号
					'coinchannel'	=> '',
					'createtime'	=> time(),//传输时间
					'saledate'		=> date('Ymd',$data[1]),//销售日期
					'createdate' 	=> date('Ymd') //传输日期
				);
				M_SWOOLE($serv->worker_id,'pay')->add($ar);
				$ar = array(
					'trade_status'		=> 2, //
					'saleid'			=> $data[2],//交易号
					'note'				=> '出货失败，已返现',
					'finishtime'		=> time()
				);
				M_SWOOLE($serv->worker_id,'online_order')->where(array('id',$order_id))->save($ar);
				break;
			}
			if($failed == 2){
				//出货失败，要求退还金额到客户支付账户;
				M_SWOOLE($serv->worker_id,'online_order')->where(array('id',$order_id))->save(['saleid' => $data[2]]);
				//投递退款任务
				$serv->task(['key' => 'refund','val' => $order]);
				break;
			}
			$road = M_SWOOLE($serv->worker_id,'road')->where(array(array('vmid',$machine['vmid']),array('pacode',$order['pacode'])))->find();
			//创建出货记录
			$ar = array(
				'saleid'		=> $data[2], // 交易号
				'saletype'		=> $order['trade_type'] == 'alipay' ? 2 : 3, //销售类型 微信
				'salecard'		=> '',
				'salemoney'		=> $order['amount'],
				'saletime'		=> $data[1],
				'salenum'		=> 1,
				'pacode'		=> $pacode, //货道
				'vmid'			=> $machine['vmid'],
				'createtime'	=> time(),
				'saledate'		=> date('Ymd',$data[1]),
				'createdate' 	=> date('Ymd'), //传输日期
				'goods_id'		=> intval($road['goods_id']),
				'goods_name'	=> $road['goods_name'],
				'price_cb'		=> $order['amount'],
			);
			if($ar['goods_id'] > 0){
				$goods = M_SWOOLE($serv->worker_id,'goods')->where(array('id',$ar['goods_id']))->find();
				if($goods){
					$ar['goods_name'] = $goods['goods_name']; 
					$ar['price_cb'] = $goods['goods_price'];
					if($goods['goods_num'] > 0){
						M_SWOOLE($serv->worker_id,'goods')->where(['id',$goods['id']])->save(['goods_num' => $goods['goods_num'] -1]);
					}
				}
			}
			$ok = M_SWOOLE($serv->worker_id,'saledetail')->add($ar);
			//货道商品数减一
			if($ok && $road && $road['num'] > 0){
				M_SWOOLE($serv->worker_id,'road')->where(array('id',$road['id']))->save(array('num' => $road['num'] - 1));
			}
			//创建收支记录
			$ar = array(
				'vmid'			=> $machine['vmid'],
				'currency'		=> $order['trade_type'] == 'alipay' ? 4 : 5,//0：硬币 1：纸币 2: 普通卡 3：学生卡 4 支付宝 5 微信
				'amount'		=> $order['amount'],//面值
				'time'			=> $data[1], //销售时间
				'payments'		=> 0,//0：收币1：找零2：吞币
				'num'			=> 1,//数量
				'saleid'		=> $data[2],//交易号
				'card'			=> '', //卡号
				'coinchannel'	=> '',
				'createtime'	=> time(),//传输时间
				'saledate'		=> date('Ymd',$data[1]),//销售日期
				'createdate' 	=> date('Ymd') //传输日期
			);
			M_SWOOLE($serv->worker_id,'pay')->add($ar);
			//修改订单信息
			$ar = array(
				'trade_status'		=> 1, //已发货	
				'saleid'			=> $data[2],//交易号
				'note'				=> '交易完成',
				'finishtime'		=> time()
			);
			M_SWOOLE($serv->worker_id,'online_order')->where(array('id',$order_id))->save($ar);
			//投递微信企业付款任务
			$serv->task(array('key' => 'wx_transfer','val' => $order['vmid']));
		break;
		case '81':
			//获取微信预付二维码链接 *货道id*
			$pacode = intval($data[1]);
			$price = intval($data[2]);
			$url = PAY_URL.'?a=weixin&m=qrcode&vmid='.$machine['vmid'].'&pacode='.$pacode.'&price='.$price;
			swoole_log('获取微信预付二维码链接url：'.$url,$fd);
			$url_ch = curl_init();
			curl_setopt($url_ch,CURLOPT_URL,$url);
			curl_setopt($url_ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($url_ch,CURLOPT_TIMEOUT,10);
			$qr_url = curl_exec($url_ch);
			curl_close($url_ch);
			$serv->send($fd,pack_data($qr_url,'1d','30',$package[1]));
			swoole_log('获取微信预付二维码链接：'.$qr_url,$fd);
			return;
		break;
		case '82':
			//获取支付宝预付二维码链接 *货道id*
			$pacode = intval($data[1]);
			$price = intval($data[2]);
			$url = PAY_URL.'?a=alipay&m=qrcode&vmid='.$machine['vmid'].'&pacode='.$pacode.'&price='.$price;
			$url_ch = curl_init();
			curl_setopt($url_ch,CURLOPT_URL,$url);
			curl_setopt($url_ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($url_ch,CURLOPT_TIMEOUT,10);
			$qr_url = curl_exec($url_ch);
			curl_close($url_ch);
			$serv->send($fd,pack_data($qr_url,'1d','30',$package[1]));
			swoole_log('获取支付宝预付二维码链接：'.$qr_url,$fd);
			return;
		break;
		case '83':
			swoole_log('获取ic卡微信充值二维码',$fd);
			$serv->task(array('key' => 'ic_recharge_wx','vmid' => $machine['vmid'],'fd' => $fd,'flowid' => $package[1],'data' => $package[3]));
			return;
		break;
		case '84':
			swoole_log('获取ic卡支付宝充值二维码',$fd);
			$serv->task(array('key' => 'ic_recharge_alipay','vmid' => $machine['vmid'],'fd' => $fd,'flowid' => $package[1],'data' => $package[3]));
			return;
		break;
		case '90':
			//投递积分任务 *会员卡卡号*消费金额*交易号
			$cardid = $data[1];
			$money = intval($data[2]);
			$saleid = $data[3];
			$ar = [
				'key'		=> 'hy_sale',
				'vmid'		=> $machine['vmid'],
				'saleid'	=> $saleid,
				'cardid'	=> $cardid,
				'money'		=> $money
			];
			$serv->task($ar);
		break;
	}
	send_result($serv,$fd,$from_id,$package[1],$return_code); //返回接收成功
	if($package[2] == '37'){
		// *时间*交易号*货道编号*商品价格*付款码  根据dynamic_id区分是支付宝还是微信
		$saletime = format_time($data[1]);
		$saleid = $data[2];
		$pacode = $data[3];
		$amount = $data[4]; //单位为分
		$dynamic_id = $data[5];
		$vmid = $machine['vmid'];
		$where = array(array('vmid',$vmid),array('dynamic_id',$dynamic_id));
		$count = M_SWOOLE($serv->worker_id,'online_order')->where($where)->count();
		if($count > 0) return;
		$url = PAY_URL.'?vmid='.$vmid.'&pacode='.$pacode.'&dynamic_id='.$dynamic_id.'&amount='.$amount.'&saleid='.$saleid.'&saletime='.$saletime;
		$url_ch = curl_init();
		curl_setopt($url_ch,CURLOPT_URL,$url);
		curl_setopt($url_ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($url_ch,CURLOPT_TIMEOUT,5);
		$json = curl_exec($url_ch);
		curl_close($url_ch);
	}
}

//设置上报
function do_report($serv, $fd, $from_id,$package){
	global $sw_tab_fd;
	if(!$sw_tab_fd->exist($fd)){
		return send_result($serv, $fd, $from_id,$package[1],'fe');
	}
	$machine = $sw_tab_fd->get($fd);
	$data = explode('*',$package[3]);
	switch($package[2]){
		case '31':
			//货道销售价格设置上报 *时间*类型*货道*价格[*按键编号] 
			// 类型 1：单个设置 2：某托盘设置 3：整机 4：货道范围设置 *150413124006*4*110|115*300*1
			$type = intval($data['2']);
			if($type > 4 || $type < 1 || $type == 2) break;
			if(array_key_exists(5,$data)) $ar['key_no'] = intval($data['5']);
			$where = array(array('vmid',$machine['vmid']));
			if($type == '1'){
				$pacode = intval($data['3']);
				$where[] = array('pacode',$pacode);
			}
			if($type == '4'){
				list($min,$max) = explode('|',$data['3']);
				$where[] = array('pacode',intval($min),'>=');
				$where[] = array('pacode',intval($max),'<=');
			}
			$ar['price_machine'] = intval($data['4']);
			M_SWOOLE($serv->worker_id,'road')->where($where)->save($ar);
		break;
		case '32':
			/* 
			* 数据：*211111111111 货道自检结果上报
			* 如果机器上有此货道而后台没有则增加
			*/
			$str = $data[1];
			$head = substr($str,0,2);
			if(!is_numeric($head) || $head < 10){
				send_result($serv,$fd,$from_id,$package[1],'31'); //保存错误
				return;
			} 
			$where = array(
				array('vmid',$machine['vmid']),
				array('pacode',$head*10,'>='),  //注意这里是字符串 不能用 99 去比较
				array('pacode',$head*10 + 10,'<'),
			);
			$roads = M_SWOOLE($serv->worker_id,'road')->fields('pacode')->where($where)->select();
			$pacodes = array();
			foreach($roads as $temp) $pacodes[] = $temp['pacode'];
			for($i = 2; $i < strlen($str);$i++){
				$pacode = $head.($i - 2);
				if(substr($str,$i,1) == '0'){
					//货道不可用
					if(in_array($pacode,$pacodes)){
						//删除货道
						M_SWOOLE($serv->worker_id,'road')->where(array(
							array('vmid',$machine['vmid']),
							array('pacode',$pacode),
						))->delete();
					}
					continue;
				}
				if(in_array($pacode,$pacodes)) continue;
				$ar = array(
					'vmid'			=> $machine['vmid'],
					'pacode'		=> $pacode,
					'goods_id'		=> 0,
					'goods_name'	=> '',
					'price'			=> 0,
					'price_machine'	=> 0,
					'num'			=> 0,
					'max_num'		=> intval($pacode) > 199 ? 1 : 10
				);
				M_SWOOLE($serv->worker_id,'road')->add($ar);
			}
		break;
		case '33':
			//货道容量库存上报 *起始货道编号*终止货道编号*货道容量[*货道库存]
			$min = intval($data[1]);
			$max = intval($data[2]);
			$max_num = intval($data[3]);
			$num = isset($data[4]) ? intval($data[4]) : -1;
			if($max_num < 1 && $num < 0) break;
			$vmid = $machine['vmid'];
			$where = array(
				array('vmid',$vmid),
				array('pacode',$min,'>='),
				array('pacode',$max,'<=')
			);
			$ar = array();
			if($max_num > 0) $ar['max_num'] = $max_num;
			if($num >= 0) $ar['num'] = $num;
			if(count($ar) > 0){
				M_SWOOLE($serv->worker_id,'road')->where($where)->save($ar);
			}
		break;
		case '34':
			//机器一键上货上报 *起始货道编号*终止货道编号
			$vmid = $machine['vmid'];
			$min = intval($data[1]);
			$max = intval($data[2]);
			$table = M_SWOOLE($serv->worker_id,'road')->table();
			$sql = 'UPDATE '.$table." SET num=max_num WHERE vmid='{$vmid}' AND pacode BETWEEN '{$min}' AND '{$max}'";
			M_SWOOLE($serv->worker_id,'road')->excute($sql);
		break;
		case '45':
			//机器设置结果反馈
			$id = intval($data['1']);
			M_SWOOLE($serv->worker_id,'set_queue')->where(['id',$id])->save(['status' => 1]);
		break;
	}
	send_result($serv,$fd,$from_id,$package[1],'30');
}

//网关主动上报机器
function do_up_report($serv, $fd, $from_id,$package){
	$data = explode('*',$package[3]);
	switch($package[2]){
		case '01':
			//检查机器是否在线
			$vmid = $data[1];
			$fd_machine = get_vm_fd($vmid);
			$code = $fd_machine < 1 ? 'offline' : 'online';
			swoole_log('机器'.$vmid.':'.$code,$fd);
			return $serv->send($fd,$code);
		break;
		case '10':
			//上传货道价格配置到机器
			$road_id = intval($data[1]);
			//查询货道信息
			$road = M_SWOOLE($serv->worker_id,'road')->where(array('id',$road_id))->find();
			$fd_machine = get_vm_fd($road['vmid']);
			if($fd_machine < 1){
				swoole_log('设置货道：'.$road_id.'，机器'.$road['vmid'].'不在线',$fd);
				return $serv->send($fd,'vm_offline'); //机器未登陆 不在线
			}
			//发送货道设置
			$parms = [
				'first'		=> '',
				'pacode'	=> $road['pacode'],
				'price'		=> $road['price'],
				'num'		=> $road['num'],
				'max_num'	=> $road['max_num'],
				'sort_name'	=> '',
				'goods_name'=> '',
				'goods_pic' => '',
				'key_no'	=> $road['key_no'],
				'goods_id'	=> $road['goods_id'],
				'price_yj'	=> $road['price_yj'],
				'price_cb'	=> 0,
			];
			//查询商品信息
			if($road['goods_id']){
				$goods_info = M_SWOOLE($serv->worker_id,'goods')->where(array('id',$road['goods_id']))->find();
				$goods_type = M_SWOOLE($serv->worker_id,'goods_type')->where(array('id',intval($goods_info['goods_type'])))->find();
				$parms['sort_name'] = $goods_type['type_name'];
				$parms['goods_name'] = $goods_info['goods_name'];
				if(!empty($goods_info['goods_pic'])){
					$parms['goods_pic'] = WEB_URL.'public/'.$goods_info['goods_pic'];
				}
			}
			$str = implode('*',$parms);
			$ok = $serv->send($fd_machine,pack_data($str,'80','10'));
			swoole_log('设置货道：'.$road_id.($ok ? 'ok' : 'failed').':'.$str,$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '11':
			//重置机器管理密码
			$vmid = $data[1];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('重置机器密码失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'vm_offline'); //机器未登陆 不在线
			}
			$str = ''; //包体内容为空
			$ok = $serv->send($fd_machine,pack_data($str,'80','11'));
			swoole_log('重置机器密码：'.$vmid.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '20':
			//微信二维码支付 通知机器发货 *货道id*（后台）订单编号
			$pacode = $data[1];
			$order_id = $data[2];
			$order = M_SWOOLE($serv->worker_id,'online_order')->where(array('id',$order_id))->find();
			$fd_machine = get_vm_fd($order['vmid']);
			if($fd_machine < 1){
				swoole_log('通知机器发货失败，机器'.$order['vmid'].'不在线',$fd);
				return $serv->send($fd,'vm_offline'); //机器未登陆 不在线
			}
			$str = $package[3]; //原封不动转发
			if($order['trade_type'] == 'weixin') $str += '*0';
			if($order['trade_type'] == 'alipay') $str += '*1';
			$ok = $serv->send($fd_machine,pack_data($str,'80','20'));
			swoole_log('通知机器发货：'.$pacode.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '21':
			//礼品机 通知机器发货 *货道编号*机器id
			$pacode = $data[1];
			$vmid = $data[2];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('通知机器发货失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'vm_offline'); //机器未登陆 不在线
			}
			$str = $package[3]; //原封不动转发
			$ok = $serv->send($fd_machine,pack_data($str,'80','21'));
			swoole_log('通知机器发货：'.$pacode.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '22':
			//商城取货api接口实现 *流水号（订单号）*货道编号*机器编号
			$orderid = $data[1];
			$pacode = $data[2];
			$vmid = $data[3];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('商城取货失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'vm_offline'); //机器未登陆 不在线
			}
			$str = '*'.$orderid.'*'.$pacode;
			$ok = $serv->send($fd_machine,pack_data($str,'80','22'));
			swoole_log('商城取货：'.$pacode.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '30':
			//后台通过网关设置机器状态 *机器id*状态
			$vmid = $data[1];
			$vmstatus = $data[2];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('后台通过网关设置机器状态失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$ok = $serv->send($fd_machine,pack_data('*'.$vmstatus,'80','30'));
			swoole_log('设置机器状态：'.$vmid.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? '设置成功' : '设置失败'); //回报给网站后台
		break;
		case '40':
			//打开所有商品柜
			$vmid = $data[1];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('后台通过网关打开所有商品柜失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$ok = $serv->send($fd_machine,pack_data($package[3],'80','40'));
			swoole_log('打开所有商品柜：'.$vmid.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '41':
			//打开某个商品柜
			$vmid = $data[1];
			$pacode = $data[2];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('后台通过网关打开商品柜'.$pacode.'失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$ok = $serv->send($fd_machine,pack_data($package[3],'80','41'));
			swoole_log('打开商品柜：'.$vmid.'['.$pacode.']'.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '42':
			//通知机器进行商品柜自检
			$vmid = $data[1];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('商品柜自检失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$ok = $serv->send($fd_machine,pack_data($package[3],'80','42'));
			swoole_log('商品柜自检：'.$vmid.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '43':
			//后台通过网关通知单片机升级程序 *机器编号*硬件版本
			//*总长度*校验和*硬件版本
			$vmid = $data[1];
			$hardware = $data[2];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('后台通过网关通知单片机升级程序失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$file = PATH_ROOT.'software/'.$hardware.'.php';
			if(!file_exists($file)){
				return $serv->send($fd,'程序配置文件不存在！');
			}
			$machine = 
			$cfg = include($file);
			$ok = $serv->send($fd_machine,pack_data('*'.$cfg['size'].'*'.$cfg['sum'].'*'.$hardware,'90','01'));
			swoole_log('通知单片机升级：'.$vmid.($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '44':
			//通知机器 一键上货
			$vmid = $data[1];
			$min = $data[2];
			$max = $data[3];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('一键上货失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$ok = $serv->send($fd_machine,pack_data('*'.$min.'*'.$max,'80','44'));
			swoole_log('一键上货：'.$package[3].($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '45':
			//后台通过网关设置机器状态
			$id = intval($data[1]);
			$order = M_SWOOLE($serv->worker_id,'set_queue')->where(['id',$id])->find();
			if(!$order){
				swoole_log('设置机器状态失败，命令'.$id.'未找到',$fd);
				return $serv->send($fd,'命令'.$id.'未找到');
			}
			$vmid = $order['vmid'];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('设置机器状态失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$str = '*'.$order['id'].'*'.$order['order_type'].'*'.$order['order_info'];
			$ok = $serv->send($fd_machine,pack_data($str,'80','45'));
			swoole_log('设置机器状态：'.$package[3].($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
		case '50':
			//设置机器运行状态 *机器编号*是否运行(1或者0)
			$vmid = $data[1];
			$status = $data[2];
			$fd_machine = get_vm_fd($vmid);
			if($fd_machine < 1){
				swoole_log('设置机器运行状态失败，机器'.$vmid.'不在线',$fd);
				return $serv->send($fd,'机器'.$vmid.'不在线');
			}
			$str = '*'.$status;
			$ok = $serv->send($fd_machine,pack_data($str,'80','50'));
			swoole_log('设置机器运行状态：'.$package[3].($ok ? 'ok' : 'failed'),$fd_machine);
			$serv->send($fd,$ok ? 'ok' : 'failed'); //回报给网站后台
		break;
	}
}

function do_auto_update($serv, $fd, $from_id,$package){
	$data = explode('*',$package[3]);
	switch($package[2]){
		case '02':
			//*起始地址*数据长度*硬件版本
			$start = intval($data[1]);
			$length = intval($data[2]); 
			$hardware = $data[3];
			$cfg_file = PATH_ROOT.'software/'.$hardware.'.php';
			if(!file_exists($cfg_file)){
				//配置不存在！
				break;
			}
			$machine = 
			$cfg = include($cfg_file);
			$filename = PATH_ROOT.'software/'.$cfg['file'];
			if(!$cfg || !file_exists($filename)){
				//升级程序不存在
				break;
			}
			if($start > $cfg['size']){
				$serv->send($fd,pack_data('','90','03',$package[1]));
			}
			$handle = fopen($filename,"r");
			if($start > 0) fseek($handle,$start);
			$contents = fread($handle,$length);
			fclose($handle);
			$send_data = chr(0x90).chr($package[1]).chr(0xff).chr(0x02).$contents;
			$serv->send($fd,$send_data);
			return;
		break;
	}
	send_result($serv,$fd,$from_id,$package[1],'31','未知数据业务类型');
}

//根据机器id获取机器的 tcp
function get_vm_fd($vmid = 0){
	global $sw_tab_vm,$sw_tab_fd;
	if($sw_tab_vm->exist($vmid)){
		$ar = $sw_tab_vm->get($vmid);
		$machine = $sw_tab_fd->get($ar['fd']);
		if($machine && $machine['update_time'] > time() - 120){
			return $ar['fd'];
		}
	}
	return 0;
}

//发送处理结果
function send_result($serv,$fd,$from_id,$flow_id,$res,$note = ''){
	if($res == '30' && $note == 'login'){
		$serv->send($fd,pack_data(substr(date('YmdHis'),2),'1d',$res,$flow_id));
		$fdinfo = $serv->connection_info($fd);
		$note .= $fdinfo['remote_ip'];
	}else{
		if($res != 'ff'){
			$serv->send($fd,chr(hexdec('1d')).chr($flow_id).chr(hexdec($res)).chr(hexdec('00')));
		}
	}
	switch($res){
		case '30': return swoole_log('保存成功'.$note,$fd);
		case '31': return swoole_log('保存错误'.$note,$fd);
		case '32': return swoole_log('条码支付不成功'.$note,$fd);
		case '33': return swoole_log('条码支付成功'.$note,$fd);
		case '34': return swoole_log('密码错误'.$note,$fd);
		case 'ff': return swoole_log('校验错误'.$note,$fd);
		case 'fe': return swoole_log('没有登录'.$note,$fd);
	}
}

//数据解析
function un_pack($data){
	$big_sort = bin2hex(substr($data,0,1)); //第一个字节
	$flow_id = hexdec(bin2hex(substr($data,1,1))); //流水号
	if($big_sort == 'ff'){
		return array($big_sort,$flow_id); //心跳包
	}
	$third = bin2hex(substr($data,2,1)); //第三个字节
	$fourth = bin2hex(substr($data,3,1)); //第四个字节
	if($third == '00'){
		if($fourth == '01' || $fourth == '03'){
			//dtu的登录包 或者是 心跳包
			return array($third,$fourth);
		}
	}
	$len = hexdec($third); //长度 0 ~ 255
	$small_sort = $fourth; //16进制小分类
	$body = substr($data,4,$len - 7); //数据主体内容
	//数据校验 以上所有字节的累加和
	$num = 0;
	for($i = 0; $i < $len - 2; $i++){
		$str = bin2hex(substr($data,$i,1));
		$num += hexdec($str);
	}
	$verify = implode('',verify($num)); //16进制验证码
	$v2 = bin2hex(substr($data,$len - 2,1)); //过滤掉了尾部第一个字节
	$v2 .= bin2hex(substr($data,$len - 1,1));
	return array($big_sort,$flow_id,$small_sort,$body,$verify == $v2,$verify,$v2);
}

function log_hex($data,$fd){
	if(!LOG_WRITE) return;
	$len = strlen($data);
	$str = ' hex-data:';
	for($i = 0; $i < $len ; $i++){
		$str .= bin2hex(substr($data,$i,1));
	}
	swoole_log($str,$fd);
}

//yymmddhhiiss 的时间格式转化成秒数
function format_time($str){
	if($str == 0) return time();
	return strtotime('20'.substr($str,0,2).'-'.substr($str,2,2).'-'.substr($str,4,2).' '.substr($str,6,2).':'.substr($str,8,2).':'.substr($str,10,2));
}

//调试日志
function swoole_log($str,$fd){
	global $sw_tab_fd;
	if(!LOG_WRITE) return;
	$machine = $sw_tab_fd->get($fd);
	$vmid = $machine ? $machine['vmid'] : '';
	$str = date('H:i:s').iconv('utf-8','gbk',$str)."\r\n";
	f_write(PATH_ROOT.'log/'.date('Ymd').'/'.$vmid.'_log.txt',$str,'a');
}
