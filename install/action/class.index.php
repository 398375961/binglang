<?PHP
/*
+----------------------------------------------------------------------
| SPF-简单的PHP框架 1.0 测试版
+----------------------------------------------------------------------
| Copyright (c) 2012-2016 All rights reserved.
+----------------------------------------------------------------------
| Licensed ( http:www.apache.org/licenses/LICENSE-2.0 )
+----------------------------------------------------------------------
| Author: lufeng <lufengreat@163.com>
+----------------------------------------------------------------------
| 系统安装处理类
+----------------------------------------------------------------------
*/
class IndexAction extends Action{

	protected function before(){
		$install = $this->check_install(true);
		if($install) output('检查到系统已经安装，如需重新安装请手动删除文件:'.$file,'../index.php');
	}

	public function index(){
		$this->check_writeable();
		//删除配置文件
		remove_dir_file(CONFIG_FILE);
		$this->tpl();
	}

	public function create_config(){
		if(IS_POST){
			global $CFG;
			$CFG = [];
			$CFG["site_name"] = "金吉自动售货机管理系统";
			$CFG["version"] = "1.0";
			$CFG["soft_name"] = "SPF-简洁的PHP框架";
			$CFG["db_host"] = $_POST['db_host']; //数据库主机地址
			$CFG["db_user"] = $_POST['db_user']; //数据库默认用户名
			$CFG["db_pwd"] = $_POST['db_pwd']; //数据库默认密码
			$CFG["db_pre"] = $_POST['db_pre']; //数据表前缀
			$CFG["db_charset"] = "UTF8"; //数据库字符集
			$CFG["default_password"] = "888888"; //默认密码，用于重置用户密码
			$db = new DB();
			if($error = $db->get_error()){
				output($error,'?m=create_config',4);
			}
			$CFG["database"] = $_POST['database']; //数据库
			$str = var_export($CFG,true);
			$str = '<?PHP $VAL = []; $CFG = '.$str.';';
			f_write(CONFIG_FILE,$str);
			$this->create_table('create_table');
			return;
		}
		$this->tpl();
	}

	//创建表结构和测试数据等
	public function create_table(){
		if($_REQUEST['ajax'] == 1){
			$table = $_REQUEST['table'];
			DB::get_instance()->set_db($this->get('cfg.database')); //要重新设置一下连接的数据库
			M($table)->create_table();
			echo '创建表：'.DB::get_instance()->get_pre().$table.'<br/>';
			return;
		}
		//读取module目录，创建初始数据
		$tables = array();
		if(is_dir(PATH_MODULE)){
			$dr = opendir(PATH_MODULE);
			while (($file = readdir($dr)) !== false) {
				if($file == '.' || $file == '..') continue;
				list($class,$table,$php) = explode('.',$file);
				$tables[] = $table;
				
			}
			closedir($dr);
		}
		$this->set('tables',$tables);
		$this->tpl('create_table');
	}
	
	//检查是否安装成功
	public function check_install($ret = false){
		$file = ROOT_PWC.'install/install.lock';
		$ok = file_exists($file);
		if($ret) return $ok;
		echo $ok ? '1' :'0';
	}

	public function over(){
		f_write(ROOT_PWC.'install/install.lock','');
		output('系统安装完成！','../',5);
	}

	public function create_db(){
		M()->excute('drop database '.$this->get('cfg.database'));
		M()->excute('create database '.$this->get('cfg.database'));
		echo '创建数据库：'.$this->get('cfg.database').'<br/>';
	}

	private function check_writeable(){
		$files = array(PATH_LIB,PATH_CACHE,PATH_ROOT);
		$fw = array();
		foreach($files as $f){
			$fw[] = array($f,is_writable($f));
		}
		$this->set('filewrite',$fw);
	}
}