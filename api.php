<?php
define('ROOT_PWC',dirname(__FILE__).'/');  //根目录
define('PATH_LIB',ROOT_PWC.'lib/'); //公用函数，类库
define('PATH_ROOT',ROOT_PWC.'project_api/'); //项目根目录
define('SOURCE_ROOT','public/'); //资源目录（也可以是url）
define('CHECK_RULE',false); //是否登录验证
define('DEBUG',false); //是否为调试模式
include(PATH_LIB.'init.php');