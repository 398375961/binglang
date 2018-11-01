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
| Note: class.weichat.php 礼品里微信公众号绑定
+----------------------------------------------------------------------
*/
class Cash_logsModule extends Module{
	public function __construct($table = ''){
		parent::__construct('cash_logs');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`uid` INT(12) DEFAULT 0 COMMENT '客户uid',
				`status` SMALLINT(6) DEFAULT 0 COMMENT '提现状态',
				`money` INT(12) DEFAULT 0 COMMENT '提现金额，单位为分',
				`add_time` INT(12) DEFAULT 0 COMMENT '申请时间',
				`deal_time` INT(12) DEFAULT 0 COMMENT '处理时间',
				`deal_uid` INT(12) DEFAULT 0 COMMENT '处理人',
				`deal_username` VARCHAR(40) DEFAULT '' COMMENT '处理人',
				`note` VARCHAR(255) DEFAULT '' COMMENT '备注',
				PRIMARY KEY (`id`),
				KEY (`uid`),
				KEY (`status`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}