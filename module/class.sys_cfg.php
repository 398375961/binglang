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
| Note: 系统参数设置表
+----------------------------------------------------------------------
*/
class Sys_cfgmodule extends Module{
	public function __construct($table = ''){
		parent::__construct('system_config');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				uid INT(10),
				cfg_type varchar(10) COMMENT '参数分组名称',
				cfg_name varchar(20) COMMENT '参数名称',
				cfg_value varchar(100) COMMENT '参数值',
				KEY (`uid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}