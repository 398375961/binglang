<?php
/**
*	微信公众平台入口
*/
define('PATH_ROOT',dirname(__FILE__).'/'); //项目根目录
define('ROOT_PWC',PATH_ROOT.'../');  //根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('PATH_CACHE',ROOT_PWC.'cache/mp_weixin/');
define('SOURCE_ROOT','/public/'); //资源目录（也可以是url）
define('DEBUG',false); //是否为调试模式
include(PATH_LIB.'init.php');