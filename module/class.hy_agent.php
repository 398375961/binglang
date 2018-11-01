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
| Note: 会员系统，代理商信息表
+----------------------------------------------------------------------
*/
class Hy_agentModule extends Module{
	public function __construct($table = ''){
		parent::__construct('hy_agent');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(11) NOT NULL AUTO_INCREMENT COMMENT '代理商名字',
			`user_id` int(11) DEFAULT '0',
			`agent_name` varchar(20) DEFAULT NULL,
			`createtime` int(11) DEFAULT '0',
			`note` varchar(255) DEFAULT '',
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}