<?PHP
//header('Content-type:text/html;charset=utf-8');
//exit('系统升级中，请稍后访问...');
define('PATH_ROOT',dirname(__FILE__).'/'); //项目根目录
define('ROOT_PWC',PATH_ROOT.'../');  //根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('SOURCE_ROOT','public/'); //资源目录（也可以是url）
define('CHECK_RULE',true); //是否登录验证
define('DEBUG',true); //是否为调试模式
include(PATH_LIB.'init.php');