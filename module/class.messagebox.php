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
| Note: 留言系统
+----------------------------------------------------------------------
*/
class MessageboxModule extends Module{
	public function __construct($table = ''){
		parent::__construct('messagebox');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`vmid` varchar(10) DEFAULT '',
			`username` varchar(40) DEFAULT '',
			`email` varchar(60) DEFAULT '',
			`mobile` varchar(11) DEFAULT '' COMMENT '手机号码',
			`qq` varchar(12) DEFAULT '',
			`message` varchar(255) DEFAULT '' COMMENT '留言内容',
			`createtime` int(11) DEFAULT '0' COMMENT '留言时间',
			PRIMARY KEY (`id`),
			KEY `vmid` (`vmid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='留言箱'";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}