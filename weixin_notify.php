<?PHP
//微信扫码支付成功 回调页面
define('ROOT_PWC',dirname(__FILE__).'/');  //根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('PATH_ROOT',ROOT_PWC.'online_pay/'); //项目根目录
define('SOURCE_ROOT','public/'); //资源目录（也可以是url）
define('DEBUG',false); //是否为调试模式
define('AUTO_SESSION',true);
$action = 'weixin';
$method = 'notify';
include(PATH_LIB.'init.php');