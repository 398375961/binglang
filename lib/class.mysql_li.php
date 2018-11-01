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
| Note: class.mysql_li.php mysql数据库操作类
+----------------------------------------------------------------------
*/
define('CLIENT_MULTI_RESULTS', 131072);
class DB
{
	private $conn = null;
	private $msg = '';  //记录错误消息
	private $result = null;
	private $charset = ''; //数据库字符集
	private $db_host = ''; //数据库主机地址 127.0.0.1:3306
	private $db_user = ''; //数据库默认用户名
	private $db_pwd = '';  //数据库默认密码
	private $database = ''; //数据库
	private $query_times = 0; //查询次数
	private $db_pre = ''; //数据表前缀
	public  $debug = false;  //是否为调试模式

	private static $instance = null;

	public function __construct(){
		global $CFG;
		$this->db_pre = $CFG['db_pre'];
		if(defined('DEBUG')) $this->debug = DEBUG;
		$this->set_config($CFG['db_host'],$CFG['db_user'],$CFG['db_pwd'],$CFG['database'],$CFG['db_charset'])->connect();
	}

	public function get_pre(){
		return $this->db_pre;
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
	public function connect(){
		if(empty($this->db_host)){
			$this->output("数据库连接配置信息不完整，请检查配置！");
			return $this;
		}
		$this->conn = new mysqli($this->db_host,$this->db_user,$this->db_pwd);
		if ($this->conn->connect_errno){
			$this->output("Failed to connect to MySQL: " .$this->conn->connect_error);
			return $this;
		}
	    if(!empty($this->database)) $this->set_db($this->database);
	    if(!empty($this->charset)) $this->set_charset($this->charset);
		return $this;
	}

	//设置数据库
	public function set_db($db=''){
		if(empty($db)){
			$this->output("切换数据库，但是没有传递数据库参数！ ");
			return $this;
		}
		if (!$this->conn->select_db($db)){
			 $this->output("无法使用数据库 '".$this->database."'。");
			 return $this;
		}
		$this->database = $db;
		return $this;
	}
	
	//取得当前字符集
	public function get_charset(){
		return $this->charset;
	}
	
	//设置查询字符集
	public function set_charset($charset){
		$this->charset = $charset;
		$this->conn->set_charset($charset);
	}
	
	//数据查询
	public function query($sql){
		$this->query_times++;
		$this->debug && $this->output($sql);
		$this->free_result();
		for($i = 0 ; $i < 2; $i++){
			$this->result = $this->conn->query($sql);
			if (false === $this->success()){
				if($this->conn->errno == 2006 or $this->conn->errno == 2013){
					unset($this->conn);
					$this->connect(); //连接失败，检测重新连接
					continue;
				}
			}
			break;
		}
		if(false === $this->success()){
			$this->output("执行以下SQL语句时失败：'".$sql."' <br><br>".$this->conn->error);
		}
		return $this;
	}

	private function free_result(){
		if(!is_object($this->result)) return;
		@$this->result->free();
		@$this->result->close();
	}

	//只查询一条数据
	public function query_first($sql){
		$res = $this->query($sql)->fetch_array();
		$this->free_result();
		return $res;
	}

	//返回查询是否有错误
	public function success(){
		$er = $this->conn->error;
		return empty($er)?true:false;
	}

	//返回update delete 语句影响的数据行数
	public function excute($sql){
		$this->query($sql);
		$res = $this->conn->affected_rows;
		$this->free_result();
		return $res;
	}

	//返回查询数据的数组表现
	public function get_data($sql){
		$this->query($sql);
		$ret = array();
		if(!$this->result){
			$this->output("没有数据，请先执行SQL的'select'语句!");
			return $ret;
		}		
		while($row = $this->fetch_array()){
			$ret[] = $row; 
		}
		$this->free_result();
		return $ret;
	 }

	//取得上一次查询操作的id
	public function insert($sql){
		$this->query($sql);
		$res = $this->conn->insert_id;
		$this->free_result();
		return $res;
	}

	//一条一条的读取结果集
	private function fetch_array(){
		 if(!$this->result) return false;
		 return $this->result->fetch_assoc();
	}

	//取得数据表结构
	public function show_table_info($table){
		$this->query('SHOW COLUMNS FROM '.$table);
		return $this->get_data();
	}

	//取得数据库中的数据表名称
	public function show_tables($db){
		$db = empty($db) ? $this->database : $db;
		$this->query('SHOW TABLES FROM '.$db);
		return $this->get_data();
	}
	
	//取得所有数据库名称
	public function show_databases(){
		$this->query('SHOW DATABASES');
		return $this->get_data();
	}

	//获取错误信息
	public function get_error(){
		return $this->msg;
	}
	
	//获取错误信心
	public function get_msg(){
		return $this->get_error();
	}

	//获取查询次数
	public function query_times(){
		return $this->query_times;
	}
 
	//创建 insert update 串
	public function build_sql_val($a,$update = false){
		$up = array();
		$in = array();
		foreach($a as $k => $v)
		{
			$v = '"'.addslashes($v).'"';
			$in[] = $v;
			$up[] = '`'.$k.'`='.$v;
		}
		if($update){
			return ' '.implode(',',$up).' ';
		}
		return ' (`'.implode('`,`',array_keys($a)).'`) VALUES ('.implode(',',$in).') ';
	}

	//析构函数，自动释放数据库连接
	public function __destruct(){
		$this->conn->close();
	}

	//结果输出
	private function output($msg){
		 $this->msg= $msg;
		 $msg = date('Y-m-d H:i:s ').$msg."\r\n";
		 f_write(PATH_ROOT.'log/db_error.txt',$msg,'a');
	}
}