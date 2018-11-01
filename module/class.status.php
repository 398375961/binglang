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
class StatusModule extends Module{
	public function __construct($table = ''){
		parent::__construct('status');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(12) NOT NULL AUTO_INCREMENT,
			`vmid` CHAR(10) NOT NULL,
			`netstatus` varchar(1) DEFAULT '2' COMMENT '通讯状态(1：正常 2：离线)',
			`ipaddress` varchar(16) DEFAULT NULL COMMENT 'IP地址',
			`doorkind` tinyint(2) DEFAULT '1' COMMENT '门控状态(1：关 2：开)',
			`tmpvalue` varchar(4) DEFAULT '0' COMMENT '储藏温度',
			`lastclienttime` int(12) DEFAULT '0' COMMENT '最后通信时间',
			`version` varchar(20) DEFAULT NULL COMMENT '终端程序版本',
			`bill_status` tinyint(2) DEFAULT '1' COMMENT '纸币器状态',
			`coin_status` tinyint(2) DEFAULT '1' COMMENT '硬币器状态',
			`device_status` tinyint(2) DEFAULT '1' COMMENT '驱动板(电机板)状态',
			`cash_device_status` tinyint(2) DEFAULT '1' COMMENT '收银板状态 1正常 非1异常',
			`dhjc` tinyint(2) DEFAULT '1' COMMENT '掉物检查',
			`vmstatus` tinyint(4) NOT NULL DEFAULT '1' COMMENT '机器运行状态正常,设置态,重启',
			`cash_amount` int(10) NOT NULL DEFAULT '0' COMMENT '纸币零钱',
			`coin_amount` int(10) NOT NULL DEFAULT '0' COMMENT '硬币零钱',
			PRIMARY KEY (`id`),
			UNIQUE KEY (`vmid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE  IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}