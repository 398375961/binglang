<?PHP
//在线支付处理入口
define('ROOT_PWC',dirname(__FILE__).'/');  //根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('PATH_ROOT',ROOT_PWC.'online_pay/'); //项目根目录
define('SOURCE_ROOT','public/'); //资源目录（也可以是url）
define('AUTO_SESSION',false);
define('DEBUG',false); //是否为调试模式
$dy_id = substr($_REQUEST['dynamic_id'],0,2);
if($dy_id === '28') $action = 'alipay';
if($dy_id === '13') $action = 'weixin';
/*
if(empty($dy_id)){
	if(!in_array($_REQUEST['vmid'],array('0000000005','0000000345','0000000346','0000000347'))) return; //先关闭二维码
}
*/
include(PATH_LIB.'init.php');