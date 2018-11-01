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
| Note: 会员系统，会员等级
+----------------------------------------------------------------------
*/
class Hy_degreeModule extends Module{
	public function __construct($table = ''){
		parent::__construct('hy_degree');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`degree_name` varchar(20) DEFAULT NULL,
			`user_id` int(11) DEFAULT '0' COMMENT '所属管理员',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员等级表'";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}