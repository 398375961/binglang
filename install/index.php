<?PHP
//安装
define('ROOT_PWC',str_replace('install','',dirname(__FILE__)));  //根目录
define('PATH_LIB',ROOT_PWC.'lib'.DIRECTORY_SEPARATOR ); //公用函数，类库
define('PATH_ROOT',ROOT_PWC.'install'.DIRECTORY_SEPARATOR ); //项目根目录
define('SOURCE_ROOT','files/'); //资源目录（也可以是url）
define('DEBUG',false); //是否为调试模式
include(PATH_LIB.'init.php');