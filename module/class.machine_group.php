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
| Note: 机器分组
+----------------------------------------------------------------------
*/
class Machine_groupModule extends Module{
	public function __construct($table = ''){
		parent::__construct('machine_group');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			 `id` int(11) NOT NULL AUTO_INCREMENT,
			 `group_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			 `user_id` int(10) NOT NULL,
			 PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='机器分组表'";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}