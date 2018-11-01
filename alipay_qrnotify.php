<?PHP
//支付宝回调处理入口 二维码扫码支付的回调
define('ROOT_PWC',dirname(__FILE__).'/');  //根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('PATH_ROOT',ROOT_PWC.'online_pay/'); //项目根目录
define('SOURCE_ROOT','public/'); //资源目录（也可以是url）
define('AUTO_SESSION',true);
$action = 'alipay';
$method = 'qr_notify';
include(PATH_LIB.'init.php');