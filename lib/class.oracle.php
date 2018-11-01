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
| Note: class.mysql.php mysql数据库操作类
+----------------------------------------------------------------------
*/
class DB{
	private $conn = null;
	private $msg = '';  //记录错误消息
	private $result = null;
	private $charset = ''; //数据库字符集
	private $db_host = ''; //数据库主机地址 127.0.0.1:3306
	private $db_user = ''; //数据库默认用户名
	private $db_pwd = '';  //数据库默认密码
	private $database = ''; //数据库

	private $stid = null; //sql 句柄
	private $query_times = 0; //查询次数
	public  $debug = false;  //是否为调试模式

	private static $instance = null;

	public function __construct(){
		global $CFG;
		if(defined('DEBUG')) $this->debug = DEBUG;
		$this->set_config($CFG['db_host'],$CFG['db_user'],$CFG['db_pwd'],$CFG['database'],$CFG['db_charset'],$CFG['db_timeout'])->connect();
	}

	public static function get_instance(){
		if(is_null(self::$instance)) self::$instance = new self();
		return self::$instance;
	}
	
	/*
	* 数据库连接配置
	*/
	public function set_config($host,$user,$pwd,$database = '',$charset = 'UTF8')
	{
		$this->db_host = $host;
		$this->db_user = $user;
		$this->db_pwd = $pwd;
		$this->database = $database;
		$this->charset = $charset;
		return $this;
	}

	/*
	* 数据库连接
	*/
	public function connect()
	{
		$this->conn = oci_connect($this->db_user,$this->db_pwd,$this->database,$this->charset);
	    if (!$this->conn){
			$e = oci_error();
			$this->output("连接数据库失败。".htmlentities($e['message'], ENT_QUOTES));
			return $this;
	    }
	    //if(!empty($this->database)) $this->set_db($this->database);
	    //if(!empty($this->charset)) $this->set_charset($this->charset);
		return $this;
	}

	//设置数据库
	public function set_db($db=''){
		if (empty($db)){
			$this->output("切换数据库，但是没有传递数据库参数！ ");
			return $this;
		}
		if (!mysql_select_db($db,$this->conn))
		{
			 $this->output("无法使用数据库 '".$this->database."'。");
			 return $this;
		}
		$this->database = $db;
		return $this;
	}
	
	//取得当前字符集
	public function get_charset()
	{
		return $this->charset;
	}
	
	//设置查询字符集
	public function set_charset($charset)
	{
		$this->charset = $charset;
		$this->query("SET NAMES '{$charset}'");
	}

	//检查 重建链接
	private function check(){
		if(!$this->conn) $this->connect();
	}
	
	//数据查询
	public function query($sql)
	{
		$this->check();
		$this->query_times++;
		$this->debug && $this->output($sql);
		@oci_free_statement($this->stid);
		$this->stid = oci_parse($this->conn,$sql);
		$suc = @oci_execute($this->stid,OCI_COMMIT_ON_SUCCESS);
		$success = $this->success();
		if ($suc === false || true !== $success){
			$this->output("执行以下SQL语句时失败：'".$sql."' 错误信息：".$success);
		}
		return $this;
	}

	//只查询一条数据
	public function query_first($sql)
	{
		return $this->query($sql)->fetch_array();
	}

	//返回查询是否有错误
	public function success()
	{
		$er = oci_error($this->stid);
		return $er === false ? true : var_export($er,true);
	}

	//返回update delete 语句影响的数据行数
	public function excute($sql)
	{
		$this->query($sql);
		return oci_num_rows($this->stid);
	}

	//返回查询数据的数组表现
	public function get_data($sql)
	{
		$this->query($sql);
		//oci_fetch_all($this->stid,$ret);
		//return $ret;
		$ret = array();	
		while($row = $this->fetch_array()){
			$ret[] = array_change_key_case($row,CASE_LOWER); 
		}
		return $ret;
	 }

	//取得上一次查询操作的id
	public function insert($sql)
	{
		$this->query($sql);
		return 0;
	}

	//一条一条的读取结果集
	public function fetch_array()
	{
		 return array_change_key_case(oci_fetch_array($this->stid,OCI_ASSOC),CASE_LOWER);
	}

	//获取错误信息
	public function get_error()
	{
		return $this->msg;
	}
	
	//获取错误信息
	public function get_msg()
	{
		return $this->get_error();
	}

	//获取查询次数
	public function query_times()
	{
		return $this->query_times;
	}
 
	//创建 insert update 串
	public function build_sql_val($a,$update = false)
	{
		$up = array();
		$in = array();
		foreach($a as $k => $v)
		{
			$v = "'".addslashes($v)."'";
			$in[] = $v;
			$up[] = $k.'='.$v;
		}
		if($update){
			return ' '.implode(',',$up).' ';
		}
		return ' ('.implode(',',array_keys($a)).') VALUES ('.implode(',',$in).') ';
	}

	//析构函数，自动释放数据库连接
	public function __destruct()
	{
		@oci_close($this->conn);
	}

	//结果输出
	private function output($msg)
	{
		 $this->msg= $msg;
		 $msg = date('Y-m-d H:i:s ').$msg."\r\n";
		 f_write(PATH_ROOT.'log/db_error_'.date('Ymd').'.txt',$msg,'a');
	}
}