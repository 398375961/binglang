<?PHP
//微信扫码支付回调页面
define('ROOT_PWC',dirname(__FILE__).'/');  //根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('PATH_ROOT',ROOT_PWC.'online_pay/'); //项目根目录
define('SOURCE_ROOT','public/'); //资源目录（也可以是url）
define('DEBUG',false); //是否为调试模式
define('AUTO_SESSION',true);
$action = 'weixin';
$method = 'notify_native';
//必须先在这里解析微信上报的商品id，根据商品id确定机器id
//将XML转为array 
$values = json_decode(json_encode(simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'],'SimpleXMLElement',LIBXML_NOCDATA)),true);
$product_id = $values['product_id'];
if(strlen($product_id) < 13) exit(0);
$vmid = substr($product_id,3,10);
$pacode = substr($product_id,0,3);
if(strlen($product_id) > 13){
	$price = intval(substr($product_id,13));
}else{
	$price = 0;
}
//file_put_contents('temp.txt',$product_id);
include(PATH_LIB.'init.php');