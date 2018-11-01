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
| Note: class.action 所有Action类的基类
+----------------------------------------------------------------------
*/
class Action
{
	//前期准备工作
	protected function before(){}

	// 运行函数
	public function run()
	{
		global $CFG;
		$forbid = array('before','run','get','set','append','tpl','after');
		if(in_array(METHOD,$forbid))
		{
			output('出现了一个错误，您访问的文件不存在！','?a=index');
		}
		//数据库配置 
		$this->set('cfg',$CFG);
		$this->before(); //执行前置方法
		call_user_func(array($this,METHOD)); //执行主业务逻辑
		$this->after(); //执行后置方法
	}

	//读取变量,支持层级读取,以[.]分割
	protected function get($name)
	{
		global $VAL;
		$ns = explode('.',$name);
		$value = $VAL;
		foreach($ns as $n)
		{
			if(isset($value[$n])){
				$value = $value[$n];
			}else{
				return null;
			}
		}
		return $value;
	}

	//设置模版变量
	protected function set($name,$value)
	{
		global $VAL;
		$ns = explode('.',$name);
		switch(sizeof($ns))
		{
			case 0:
				output('设置变量['.$value.'],却没有传递变量名称！');
			break;
			case 1:
				$VAL[$name] = $value;
			break;
			case 2:
				$VAL[$ns[0]][$ns[1]] = $value;
			break;
			case 3:
				$VAL[$ns[0]][$ns[1]][$ns[2]] = $value;
			break;
			case 4:
				$VAL[$ns[0]][$ns[1]][$ns[2]][$ns[3]] = $value;
			break;
			default:
				output('您要缓存的变量名称为['.$name.']，不支持4维以上的数组变量设置！');
			break;
		}
	}
	
	//设置模版变量，append 支持[.]操作
	protected function append($name,$value)
	{
		global $VAL;
		$ns = explode('.',$name);
		switch(sizeof($ns))
		{
			case 0:
				output('设置变量['.$value.'],却没有传递变量名称！');
			break;
			case 1:
				$VAL[$ns[0]][] = $value;
			break;
			case 2:
				$VAL[$ns[0]][$ns[1]][] = $value;
			break;
			case 3:
				$VAL[$ns[0]][$ns[1]][$ns[2]][] = $value;
			break;
			default:
				output('您要缓存的变量名称为['.$name.']，append方法不支持4维以上的数组变量设置！');
			break;
		}
	}

	// 魔术方法
	public function __call($name,$arguments)
	{
		output('出现了一个错误，类方法【'.$name.'】不存在！',null,404);
	}

	// 加载模版文件
	protected function tpl($method = '',$action = '')
	{
		global $VAL;
		$action || $action = ACTION;
		$method || $method = METHOD;
		$file = PATH_TPL.$action.'/'.$method.TPL_SUFFIX;
		if(file_exists($file)) include($file);
		else output('模版文件'.$file.'不存在！');
	}
	
	//后续工作
	protected function after(){}
}