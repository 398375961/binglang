<?PHP
namespace Lib;
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
| memcache 缓存实现
+----------------------------------------------------------------------
*/
class MemcachedModel{

	private $obj = null;

	//初始化
	public function __construct(){
		if(!extension_loaded("Memcached")){
			exit("Memcached did not installed!");
		}
		$obj = new Memcached();
		$obj->addServer('127.0.0.1',11211,1);
	}

	//缓存数据
	protected function setValue($k,$v,$expire){
		return $this->obj->set($k,$v,$expire);
	}

	//获取数据
	protected function getValue($k){
		return $this->obj->get($k);
	}

	public function __destruct(){
		$this->obj->quit();
	}
}