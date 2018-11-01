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
| Note: 礼品机验证码存储表
+----------------------------------------------------------------------
*/
class Rand_codeModule extends Module{
	public function __construct($table = ''){
		parent::__construct('rand_code');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT COMMENT '自增订单号',
				`rand_code` INT(12) DEFAULT 0 COMMENT '验证码',
				`createtime` INT(12) DEFAULT 0 COMMENT '生成时间',
				`weichat` VARCHAR(50) DEFAULT '' COMMENT '微信公众号',
				`has_prize` TINYINT(4) DEFAULT 0 COMMENT '是否送出了礼品',
				`client` varchar(50) DEFAULT '',
				`scene_id` int(11) DEFAULT '0' COMMENT '关注场景id',
				PRIMARY KEY (`id`),
				KEY (`rand_code`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}