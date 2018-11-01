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
| Note: 命令队列，后台发送给机器的命令队列
+----------------------------------------------------------------------
*/
class Set_queueModule extends Module{
	public function __construct($table = ''){
		parent::__construct('set_queue');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`vmid` char(10) NOT NULL DEFAULT '' COMMENT '机器编号',
			`order_type` varchar(20) COMMENT '命令类型',
			`order_info` varchar(255) COMMENT '命令参数',
			`status` tinyint(4) DEFAULT '0' COMMENT '是否收到机器反馈',
			PRIMARY KEY (`id`),
			KEY `vmid` (`vmid`),
			KEY `order_type` (`order_type`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}