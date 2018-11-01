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
| Note: 校园卡，家长
+----------------------------------------------------------------------
*/
class ParentsModule extends Module{
	public function __construct($table = ''){
		parent::__construct('parents');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE `'.$this->table."` (
			`cardid` varchar(18) NOT NULL COMMENT '身份证',
			`password` varchar(32) NOT NULL,
			`mobile` varchar(20) DEFAULT '' COMMENT '手机号码',
			`truename` varchar(40) DEFAULT '' COMMENT '真实姓名',
			`user_id` int(11) DEFAULT '0',
			PRIMARY KEY (`cardid`),
			UNIQUE KEY `cardid` (`cardid`),
			KEY `user_id` (`user_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}