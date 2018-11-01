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
| Note: 设备状态明细 机器状态表
+----------------------------------------------------------------------
*/
class Warnmodule extends Module{
	public function __construct($table = ''){
		parent::__construct('warn');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(12) NOT NULL AUTO_INCREMENT,
			`vmid` varchar(10) DEFAULT NULL,
			`cstatus` varchar(255) DEFAULT NULL COMMENT '状态',
			`cvalue` varchar(255) DEFAULT '' COMMENT '具体值',
			`createtime` int(12) DEFAULT '0' COMMENT '传输时间',
			PRIMARY KEY (`id`),
			KEY `vmid` (`vmid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}