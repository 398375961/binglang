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
| Note: class.sms.php 短信发送记录表
+----------------------------------------------------------------------
*/
class SmsModule extends Module{
	public function __construct($table = ''){
		parent::__construct('sms');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`uid` INT(12) NOT NULL,
				`content` VARCHAR(255) NOT NULL COMMENT '短信内容',
				`mobile` VARCHAR(12) NOT NULL COMMENT '短信接收号码',
				`status` tinyint(2) DEFAULT 0 COMMENT '0失败，1成功',
				`sendtime` INT(12) DEFAULT 0 COMMENT '发送时间',
				`note` VARCHAR(40) DEFAULT '' COMMENT '备注',
				PRIMARY KEY (`id`),
				KEY (`uid`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}