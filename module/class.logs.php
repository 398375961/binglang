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
| Note: class.base_action 所有Action类的基类
+----------------------------------------------------------------------
*/
class LogsModule extends Module{
	public function __construct($table = ''){
		parent::__construct('logs');
	}
	
	//初始化表，请慎用
	public function create_table(){
		/*
		* em_id 管理员id
		* dt_time 操作时间
		*/
		$sql = 'CREATE TABLE `'.$this->table.'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `action` varchar(40) DEFAULT NULL,
			  `method` varchar(40) DEFAULT NULL,
			  `em_id` int(11) DEFAULT NULL,
			  `note` varchar(255) DEFAULT NULL,
			  `script_name` varchar(255) DEFAULT NULL,
			  `dt_time` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY (`em_id`),
			  KEY `action` (`action`,`method`) USING BTREE
			) ENGINE=MyISAM DEFAULT CHARSET=utf8';
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}