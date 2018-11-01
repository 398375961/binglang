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
| Note: 所有Module类的基类，数据库相关操作
+----------------------------------------------------------------------
*/
class Module
{
	protected $db = null;
	protected $table = ''; //实际查询的表，可以更改为别名
	private $_table = ''; //数据库中的表 不可更改
	protected $where = '';
	protected $fields = '*';
	protected $order = '';
	protected $group = '';
	protected $group_count = '';
	protected $limit = '';
	protected $db_pre = '';

	public function __construct($table = ''){
		$this->db = DB::get_instance();
		$this->db_pre = $this->db->get_pre();
		$this->table = $this->db_pre.$table;
		$this->_table = $this->db_pre.$table;
	}

	public function set_db($db){
		$this->db = $db;
	}
	
	public function get_pre(){
		return $this->db_pre;
	}

	private function init(){
		$this->order = '';
		$this->fields = '*';
		$this->group = '';
		$this->group_count = '';
		$this->limit = '';
		$this->where = '';
		$this->table = $this->_table;
	}

	public function table($table = ''){
		if(empty($table)) return $this->_table;
		$this->table = str_replace('##',$this->db_pre,$table);
		return $this;
	}

	public function where($a){
		if(empty($a)) return $this;
		if(!is_array($a[0])) $a = array($a);
		$w = array();
		foreach($a  as $a2){
			list($k,$v,$op) = $a2;
			$op || $op = '=';
			$op = strtoupper($op);
			if($v === 'sql') $w[] = ' '.$k.' ';
			elseif(in_array($op,array('IN','NOT IN'))) $w[] = " {$k} {$op} {$v} ";
			else{
				$v = addslashes($v);
				$w[] = " {$k} {$op} '{$v}' ";
			}
		}
		if(sizeof($w) > 0)	$this->where = ' WHERE '.implode('AND',$w);
		return $this;
	}

	public function fields($s = '*'){
		$this->fields = $s;
		return $this;
	}

	public function group($s = ''){
		if(!empty($s)){
			$this->group = ' GROUP BY '.$s;
			$this->group_count = $s;
		}
		return $this;
	}

	public function limit($s){
		$this->limit = ' LIMIT '.$s;
		return $this;
	}

	public function order($f,$t = 'DESC'){
		$this->order .= empty($this->order) ? ' ORDER BY ' : ',';
		$this->order .= $f.' '.$t;
		return $this;
	}

	public function query($sql){
		$sql = str_replace('##',$this->db_pre,$sql);
		return $this->db->get_data($sql);
	}

	public function excute($sql){
		$sql = str_replace('##',$this->db_pre,$sql);
		return $this->db->excute($sql);
	}

	public function select_one(){
		$sql = 'SELECT '.$this->fields.' FROM '.$this->table.$this->where.$this->group.$this->order.' LIMIT 1';
		$this->init();
		return $this->db->query_first($sql);
	}

	public function select(){
		$sql = 'SELECT '.$this->fields.' FROM '.$this->table.$this->where.$this->group.$this->order.$this->limit;
		$this->init();
		return $this->db->get_data($sql);
	}

	public function count(){
		$key = empty($this->group_count) ? 1 : 'distinct '.$this->group_count;
		$sql = 'SELECT COUNT('.$key.') AS con FROM '.$this->table.$this->where;
		$res = $this->db->query_first($sql);
		return $res['con'];
	}

	public function find(){
		$sql = 'SELECT '.$this->fields.' FROM '.$this->table.$this->where;
		$this->init();
		return $this->db->query_first($sql);
	}
	
	public function add($data = array()){
		$sql = 'INSERT INTO '.$this->table.$this->db->build_sql_val($data);
		return $this->db->insert($sql);
	}

	public function save($data = array()){
		$sql = 'UPDATE '.$this->table.' SET '.$this->db->build_sql_val($data,true).$this->where;
		$this->init();
		return $this->db->excute($sql);
	}
	
	public function delete(){
		if(empty($this->where)) return 0; //禁止清空表
		$sql = 'DELETE FROM '.$this->table.$this->where;
		$this->init();
		return $this->db->excute($sql);
	}

	//分页 与 列表
	public function page_and_list($ar = array()){
		$con = get_cookie($this->table.$this->where.$this->group);
		if(!is_numeric($con)){
			$con = intval($this->count());
			set_cookie($this->table.$this->where.$this->group,$con,30);
		}
		$pagesize = max(5,intval($ar['pagesize']));
		$pages = ceil($con/$pagesize);
		$page = max(1,intval($_REQUEST['page']));
		$pages < $page && $page = $pages;
		if(empty($ar['base_url'])) $ar['base_url'] = array('a' => ACTION,'m' => METHOD);
		if(is_array($ar['base_url'])){
			$ar_url = array();
			$ar['base_url']['pagesize'] = $pagesize;
			foreach($ar['base_url'] as $k => $v){
				$ar_url[] = $k.'='.urlencode($v);
			}
			$ar['base_url'] = '?'.implode('&',$ar_url);
		}
		$str_page = page($con,$pagesize,$page,$ar['base_url']);
		$page < 1 && $page = 1;
		$start = $page * $pagesize - $pagesize;
		$list = $this->limit($start.','.$pagesize)->select();
		return array('list' => $list ,'str_page' => $str_page);
	}
}