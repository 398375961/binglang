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
| Note: class.school_card_log.php 校园卡充值资金变动记录
+----------------------------------------------------------------------
*/
class School_card_logModule extends Module{
	public function __construct($table = ''){
		parent::__construct('school_card_log');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`cardid` VARCHAR(20) NOT NULL COMMENT '校园卡卡号',
				`before` INT(12) DEFAULT 0 COMMENT '变化前金额',
				`change` INT(12) DEFAULT 0 COMMENT '变化金额',
				`after` INT(12) DEFAULT 0 COMMENT '变化后余额',
				`note` VARCHAR(255) DEFAULT '' COMMENT '备注',
				`createtime` INT(12) DEFAULT 0 COMMENT '时间',
				PRIMARY KEY (`id`),
				KEY (`cardid`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}