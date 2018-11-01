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
| Note: init.php 统一的入口文件，初始化
+----------------------------------------------------------------------
*/
//常量定义
date_default_timezone_set('PRC');
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('
IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1 : 0);
!defined('DEBUG') && define('DEBUG',FALSE); //是否为调试模式
if(DEBUG){
	error_reporting(E_ALL ^ E_NOTICE);
	define('START_TIME',microtime(true));
}else{
	error_reporting(0);
}
define('IS_POST',$_SERVER['REQUEST_METHOD'] == 'POST');
define('NOW_TIME',time());
!defined('ROOT_PWC') && define('ROOT_PWC',dirname(__FILE__).DIRECTORY_SEPARATOR);  //根目录
!defined('PATH_LIB') && define('PATH_LIB',ROOT_PWC.'lib'.DIRECTORY_SEPARATOR); //公用函数，类库
!defined('PATH_CACHE') && define('PATH_CACHE',ROOT_PWC.'cache'.DIRECTORY_SEPARATOR);  //根目录
!defined('PATH_ROOT') && define('PATH_ROOT',ROOT_PWC.'project'.DIRECTORY_SEPARATOR); //项目根目录
!defined('PATH_MODULE') && define('PATH_MODULE',ROOT_PWC.'module'.DIRECTORY_SEPARATOR); //module目录
!defined('PATH_MODEL') && define('PATH_MODEL',ROOT_PWC.'model'.DIRECTORY_SEPARATOR); //model目录
!defined('PATH_ACTION') && define('PATH_ACTION',PATH_ROOT.'action'.DIRECTORY_SEPARATOR); //action类目录
!defined('PATH_TPL') && define('PATH_TPL',PATH_ROOT.'tpl'.DIRECTORY_SEPARATOR); //模板目录
!defined('PATH_LOG') && define('PATH_LOG',PATH_ROOT.'log'.DIRECTORY_SEPARATOR); //日志目录
!defined('SOURCE_ROOT') && define('SOURCE_ROOT','project'.DIRECTORY_SEPARATOR); //资源目录（也可以是url）
!defined('TPL_SUFFIX') && define('TPL_SUFFIX','.php'); //模版后缀，建议用php
!defined('CONFIG_FILE') && define('CONFIG_FILE',PATH_LIB.DIRECTORY_SEPARATOR.'config.php'); //配置文件地址 
!defined('CHECK_RULE') && define('CHECK_RULE',FALSE); //是否要检查权限，后天管理时设置为true
!defined('AUTO_SESSION') && define('AUTO_SESSION',true); //是否开启session
define('PATH_ADMIN_RULE',PATH_CACHE.'menus.php'); //后台总菜单
if(!IS_CLI){
	ob_start();
	if(AUTO_SESSION) session_start();
	@header('Content-Type: text/html; charset=UTF-8');
}
include(PATH_LIB.'class.mysql_li.php'); //加载数据库处理类
include(PATH_LIB.'class.action.php'); //加载action类的基类
include(PATH_LIB.'class.module.php'); //加载module类的基类
if(file_exists(CONFIG_FILE)) include(CONFIG_FILE); //加载配置文件
if(file_exists(PATH_LIB.DIRECTORY_SEPARATOR.'sys_config.php')) include(PATH_LIB.DIRECTORY_SEPARATOR.'sys_config.php'); //加载配置文件
include(PATH_LIB.'function.php'); //加载公用函数库
file_exists(PATH_ROOT.'include/function.php') && include(PATH_ROOT.'include/function.php'); //加载工程类库
$action or $action = empty($_REQUEST['a']) ? 'index' : $_REQUEST['a']; //action请求
$method or $method = empty($_REQUEST['m']) ? 'index' : $_REQUEST['m']; //model请求
if(!file_exists(ROOT_PWC.'install/install.lock') && strpos($_SERVER['SCRIPT_FILENAME'],'install') === false){
	output('系统未安装!','install/'); //转到安装页面
}
define('ACTION',$action);
define('METHOD',$method);
$path = PATH_ACTION.'class.'.strtolower(ACTION).'.php';
if(!file_exists($path)){
	if(DEBUGE) echo $path;
	output('您访问的页面不存在！');
}
if(CHECK_RULE && function_exists('check_rule')){
	$ret = check_rule(ACTION,METHOD); //权限检查
	if($ret !== true) output($ret,'',3);
}
include($path);
$c = ucfirst(ACTION).'Action';
$obj = new $c();
$obj->run();