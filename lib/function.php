<?PHP
/*
+----------------------------------------------------------------------
| SPF-简单的PHP框架 1.0 测试版
+----------------------------------------------------------------------
| Copyright (c) 2012-2016 http:528918.com All rights reserved.
+----------------------------------------------------------------------
| Licensed ( http:www.apache.org/licenses/LICENSE-2.0 )
+----------------------------------------------------------------------
| Author: lufeng <lufengreat@163.com>
+----------------------------------------------------------------------
| Note: function.php 公用函数
+----------------------------------------------------------------------
*/

/*
* 统一的结果输出
* @parm msg 要输出的消息
* @parm url 下一步要跳转的url
* @parm seconds 延迟N秒后自动跳转，0则不自动跳转
*/
function output($msg,$url = null,$seconds = 2){
	global $CFG;
	$title = '温馨提示';
	empty($url) && $url = $_SERVER['HTTP_REFERER'];
	ob_end_clean();
	if($seconds == 404){
		$tpl = PATH_TPL.'404.php';
		if(file_exists($tpl)){
			include($tpl); //调用404模板
			exit(0);
		}
		$seconds = 3;
	}
	$tpl = PATH_TPL.'output.php';
	if(file_exists($tpl)){ include($tpl); exit(0); }
	$tpl = PATH_LIB.'tpl_output.php';
	if(file_exists($tpl)){ include($tpl); exit(0); }
	exit($msg);
}

/*
	递归创建目录
*/
function mk_dir($dir){
	if(!file_exists($dir))
	{
		if(!file_exists(dirname($dir)))
		{
			mk_dir(dirname($dir),0777);
		}
		return mkdir($dir);
	}
	return true;
}

/*
	批量删除文件夹及里面的内容
*/
function remove_dir_file($dir){
	if(is_file($dir)){
		@unlink($dir);
	}else if(is_dir($dir)){
		if ($handle = opendir($dir))
		{
			while (false !== ($file = readdir($handle))) 
			{
				if ($file != "." && $file != "..")
				{
					remove_dir_file($dir.'/'.$file);
				}
			}
			closedir($handle);
		}
		@rmdir($dir);
	}
}

//文件读取
function f_read($name){
	if(!file_exists($name)){
		return ''; //文件不存在
	}
	$handle = fopen($name, "r");
	$contents = fread($handle, filesize($name));
	fclose($handle);
	return $contents;
}

//文件写入
function f_write($filename,$value,$type = 'w'){
	mk_dir(dirname($filename));
	if (!$handle = fopen($filename, $type)) 
	{
		 return "can'not open file {$filename}";
	}
	if(flock($handle, LOCK_EX))
	{
		if(fwrite($handle, $value) === FALSE)
		{
			return "write file {$filename} failed!";
		}
		flock($handle, LOCK_UN);
	}else{
		return "Couldn't lock the file !".$filename;
	}
	fclose($handle);
	return true;
}

//数据缓存，读取缓存数据
function cache_data($key,$data = null,$expire = 86400){
	$file = PATH_CACHE.md5($key).'.php';
	if(is_null($data)){
		//GET 读取缓存
		if(!file_exists($file)) return false;
		$cache = include($file);
		if($cache['t'] < NOW_TIME) return false; //缓存文件过期
		return $cache['data'];
	}
	//缓存数据
	$cache = array(
		't'		=> NOW_TIME + $expire + rand(1,200),
		'data'	=> $data
	);
	f_write($file,'<?php return '.var_export($cache,true).';');
}

// 获取绝对地址
function getbaseurl($baseurl,$url){
    if("#" == $url){
        return "";
    }
    if(FALSE !== stristr($url,"http://")){
        return $url;
    }
    if( "/" == substr($url,0,1) ){
        $tmp = parse_url($baseurl);
        return $tmp["scheme"]."://".$tmp["host"].(empty($tmp['port'])||$tmp['port'] == '80' ? '' : ':'.$tmp['port']).$url;
    }
    if("/" != substr($baseurl,-1,1)){
        return $baseurl.'/'.$url;
    }
    return $baseurl.$url;
}

//取得文件后缀 返回eg： gif txt不带.
function get_file_sufix($file_name){
	$i = strrpos($file_name,'.');
	return $i > 0 ? strtolower(substr($file_name,$i + 1)) : '';
}

//写日志函数
function write_log($file,$str){
	$path = PATH_LOG.date('Ymd_').$file;
	f_write($path,date('Y-m-d H:i:s ').$str."\r\n",'a');
}

//utf8截取字符串
function substr_utf8($string,$length,$etc='...',$keep_first_style = true){  
	$result = '';    
	$string = html_entity_decode(trim(strip_tags($string)),ENT_QUOTES,'UTF-8');    
	$strlen = strlen($string);  
	for($i = 0; $i < $strlen && $length > 0; $i++)  
	{  
		if($number = strpos(str_pad(decbin(ord(substr($string,$i,1))),8,'0',STR_PAD_LEFT),'0'))  
		{  
			if($length < 1.0) break;  
			$result .= substr($string,$i,$number);  
			$length -= 1.0;  
			$i += $number - 1;  
		}else{  
			$result .= substr($string,$i,1);  
			$length -= 0.5;  
		}  
	}  
	$result = htmlspecialchars($result,ENT_QUOTES,'UTF-8');  
	if($i < $strlen)  
	{  
		$result .=  $etc;  
	}
	return $result;  
}

//格式化文件大小
function format_filesize($n){
	$n = round($n/1024,2);
	if($n < 1024) return $n.'K';
	$n = round($n/1024,2);
	if($n < 1024) return $n.'M';
	$n = round($n/1024,2);
	if($n < 1024) return $n.'G';
}

//输出验证码
function show_randcode(){
	$str = '2,3,4,5,6,7,8,9,a,b,c,d,e,f,g,h,j,k,m,n,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,J,K,L,M,N,P,Q,R,S,T,V,W,X,Y,Z';
	$a = explode(',',$str);
	$a2 = array_rand($a,4);
	$randCode = array();
	foreach($a2 as $k) $randCode[] = $a[$k];
	set_session('randcode',strtolower(implode('',$randCode)));
	$img = imagecreate(50,22);
	$bgColor = isset($_GET['mode']) && $_GET['mode'] == 't' ? imagecolorallocate($img,245,245,245) : imagecolorallocate($img,255,255,255);
	$pixColor = imagecolorallocate($img,rand(30, 180), rand(10, 100), rand(40, 250));
	for($i = 0; $i < 4 ; $i++){
		$x = $i * 11 + rand(0, 4) - 2;
		$y = rand(0, 3);
		$text_color = imagecolorallocate($img, rand(30, 180), rand(10, 100), rand(40, 250));
		imagechar($img, 5, $x + 5, $y + 3, $randCode[$i], $text_color);
	}
	for($j = 0; $j < 50; $j++){
		$x = rand(0,50);
		$y = rand(0,22);
		imagesetpixel($img,$x,$y,$pixColor);
	}
	header('Content-Type: image/png');
	imagepng($img);
	imagedestroy($img);
	exit();
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0){
	$type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

//设置cookie
function set_cookie($n,$v,$t = 0){
	$n = md5($n);
	setcookie($n,$v,$t > 0 ? NOW_TIME + $t : 0,'/');
	$_COOKIE[$n] = $v;
}

//获取cookie
function get_cookie($n){
	return $_COOKIE[md5($n)];
}

function get_session($n){
	$n = md5($n.'autosale');
	return isset($_SESSION[$n]) ? $_SESSION[$n] : false;
}

function set_session($n,$v){
	$n = md5($n.'autosale');
	$_SESSION[$n] = $v;
}

//获取数据库操作模块
function M($name = ''){
	static $modules = array();
	$class = ucfirst($name).'Module';
	if($modules[$class]) return $modules[$class];
	if(!file_exists(PATH_MODULE.'class.'.$name.'.php')){
		$modules[$class] = new Module($name);
	}else{
		include_once(PATH_MODULE.'class.'.$name.'.php');
		$modules[$class] = new $class();
	}
	return $modules[$class];
}

//获取数据库操作模块,针对swoole多进程
function M_SWOOLE($workid = 0,$name = ''){
	static $modules = array();
	static $dbs = array();
	$class = ucfirst($name).'Module';
	if($modules[$workid][$class]) return $modules[$workid][$class];
	if(!file_exists(PATH_MODULE.'class.'.$name.'.php')){
		$modules[$workid][$class] = new Module($name);
	}else{
		include_once(PATH_MODULE.'class.'.$name.'.php');
		$modules[$workid][$class] = new $class();
	}
	if(!is_object($dbs[$workid])) $dbs[$workid] = new DB();
	$modules[$workid][$class]->set_db($dbs[$workid]); 
	return $modules[$workid][$class];
}

//获取模型,模型必须存在
function Model($name){
	static $models = array();
	$class = ucfirst($name).'Model';
	if($models[$class]) return $models[$class];
	if(!file_exists(PATH_MODEL.'class.'.$name.'.php')){
		output('model '.$class.'不存在！');
	}
	include_once(PATH_MODEL.'class.'.$name.'.php');
	$models[$class] = new $class();
	return $models[$class];
}


//通知机器发货  后台 -> 网关 -> 机器
function notify_machine($order,$ip,$port){
	if(!$order || count($order) < 1) return '订单有误！';
	$data = pack_data('*'.$order['pacode'].'*'.$order['id'],'80','20'); 
	$res = my_socket_send($ip,$port,$data);
	if($res == 'vm_offline'){
		$str = '通知机器出货失败：机器不在线!';
		M('online_order')->where(array('id',$order['id']))->save(array('note' => $str));
	}
	if($res == 'ok'){
		//查询机器是否已经上报出货成功
		$order_n = M('online_order')->where(array('id',$order['id']))->find();
		if($order_n['trade_status'] > 0 || $order_n['pay_status'] > 1) return true;
	}
	return '机器出货失败！'; //通知3次还没有成功，视为失败（很有可能是网关通知机器出了问题）
}

//socket 发送数据
function my_socket_send($ip,$port,$data = ''){
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	$str = '';
	if ($socket < 0) {
		$str .= "socket_create() failed: reason: " . socket_strerror($socket);
	}
	$result = socket_connect($socket, $ip, $port);
	if ($result < 0) {
		$str .= "socket_connect() failed.\nReason: ($result) " . socket_strerror($result);
	}
	if(!socket_write($socket,$data,strlen($data))) {
		$str .= "socket_write() failed: reason: " . socket_strerror($socket);
	}
	if($out = socket_read($socket, 8192)) $str = $out;
	socket_close($socket);
	return $str;
}

//数据扎包 big_sort 通道编号 small_sort 业务编号 $flow_id 流水号
function pack_data($str,$big_sort,$small_sort,$flow_id = 0){
	static $_id = 1;
	if($flow_id == 0){
		$_id++;
		if($_id > 255) $_id = 1;
		$flow_id = $_id;
	}
	$send_data = chr(hexdec($big_sort)).chr($flow_id).chr(strlen($str) + 7).chr(hexdec($small_sort)).$str.chr(hexdec('0a'));
	$len = strlen($send_data);
	$num = 0;
	for($i = 0; $i < $len; $i++){
		$str = bin2hex(substr($send_data,$i,1));
		$num += hexdec($str);
	}
	$ver = verify($num);
	return $send_data.chr(hexdec($ver[0])).chr(hexdec($ver[1]));
}

//num为10进制数字 计算校验码 返回4位16进制字符串
function verify($num){
	$num = $num & hexdec('ff');//保留低八位 与ff
	$first = $num & hexdec('f0');
	$first += hexdec('0f');
	$first = $first & hexdec('ff');
	$first = dechex($first);
	strlen($first) == 1 && $first = '0'.$first;
	//取后八位
	$second = $num & hexdec('0f');
	$second = $second << 4; //左移4位
	$second += hexdec('0f');
	$second = $second & hexdec('ff');
	$second = dechex($second);
	strlen($second) == 1 && $second = '0'.$second;
	return array($first,$second);
}