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
| Note: class.Task 所有MyTask类的基类
+----------------------------------------------------------------------
*/
abstract class Task{
	protected $task = null;
	protected $task_start_time = 0; //任务开始时间
	protected $task_check_time = 0; //上次检查时间
	protected $_SC = array(); //静态数据
	protected $_PARMS = array(); //任务参数
		
	//构造函数
	abstract public function __construct($task_id);

	//析构函数
	abstract public function __destruct();

	//控制流程
	abstract public function run();

	//执行一次采集流程，这个流程执行时间不要太长了，30秒以内
	abstract public function process();
	
	protected function _run(){
		while(true){
			$this->check_task();
			$this->process();
			if(!IS_CLI) break; //非命令行模式只执行一次
		}
	}

	//任务计划
	protected function set_task($task_id){
		$this->task = M('task_plan')->where(array('id',$task_id))->find();
		$this->_SC = unserialize($this->task['static_vars']);
		$this->_PARMS = array();
		if(!empty($this->task['task_parms'])){
			$p = explode(';',$this->task['task_parms']);
			foreach($p as $s){
				list($k,$v) = explode(':',$s);
				$this->_PARMS[$k] = $v;
			}
		}
		$this->task_start_time = NOW_TIME;
		$this->task_check_time = NOW_TIME;
	}

	//检查任务是否关闭等
	protected function check_task($new = false){
		if(!$this->task) $this->_exit();
		if($new){
			$this->task = M('task_plan')->where(array('id',$this->task['id']))->find();
		}
		if($this->task['is_open'] == 0){
			$this->set_task_status('任务已经关闭！');
			$this->_echo('任务已经关闭！');
			$this->_exit();
		}
		if($this->task_start_time + $this->task['task_time_out']*60 < time()){
			$this->set_task_status('任务执行完毕！');
			$this->_echo('任务执行完毕！');
			$this->_exit();
		}
		if($this->task['task_check_status_time'] > 0 && $this->task_check_time + $this->task['task_check_status_time'] < time()){
			$this->set_task_status('任务执行中...');
			$this->task_check_time = time();
			$this->check_task(true);
		}
	}

	//设置任务状态
	protected function set_task_status($s){
		M('task_plan')->where(array('id',$this->task['id']))->save(array('task_status' => date('Y-m-d H:i:s ').$s));
	}

	//退出任务计划
	protected function _exit(){
		//保存任务参数
		if($this->task){
			M('task_plan')->where(array('id',$this->task['id']))->save(array('static_vars' => serialize($this->_SC)));
		}
		exit(0);
	}

	//属性字符串过滤
	public function attr_filter($s){
		$s = str_replace('&nbsp;','',$s);
		return ltrim(trim(strip_tags($s)));
	}

	//内容匹配
	protected function match_fields($html,$regs){
		$ret = array();
		foreach($regs as $s => $reg){
			$fields = explode(',',$s);
			preg_match_all($reg,$html,$m,PREG_SET_ORDER);
		//	var_dump($reg);
			if(sizeof($m) > 1){
				foreach($m as $m2){
					foreach($m2 as $k => $v){
						if($k == 0) continue;
						$ret[$fields[$k - 1]][] = $v;
					}
				}
				continue;
			}
			foreach((array)$m[0] as $k => $v){
				if($k == 0) continue;
				$ret[$fields[$k - 1]] = $v; 

			}
		}
		return $ret;
	}

	//输出
	public function _echo($s){
		//写日志
		$s = date('Y-m-d H:i:s ').$s;
		if(DEBUG) f_write(PATH_LOG.'task_'.$this->task['id'].'_'.date('Ymd').'.log',$s,'a');
		if(IS_CLI == 1){
			echo IS_WIN ? iconv('utf-8','gbk',$s) : $s;
			echo "\n";
		}else{
			echo $s.'<br/>';
		}
	}
}