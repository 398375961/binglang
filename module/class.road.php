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
| Note: 商品表，商品信息表
+----------------------------------------------------------------------
*/
class RoadModule extends Module{
	public function __construct($table = ''){
		parent::__construct('road');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`vmid` VARCHAR(20),
				`pacode` VARCHAR(4) COMMENT '货道编号',
				`goods_id` INT(10) DEFAULT 0 COMMENT '商品ID',
				`goods_name` VARCHAR(20) COMMENT '商品名称',
				`price` INT(5) DEFAULT 0 COMMENT '价格，以分为单位',
				`price_machine` INT(5) DEFAULT 0 COMMENT '价格，以分为单位，在机器上的价格',
				`price_yj` int(5) DEFAULT '0' COMMENT '原价',
				`num` INT(3) COMMENT '货道库存',
				`max_num` INT(3) COMMENT '最大容量',
				`key_no` INT(4) COMMENT '按键编号[组合]',
				PRIMARY KEY (`id`),
				UNIQUE KEY `vmid_pacode` (`vmid`,`pacode`),
				KEY (`vmid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}