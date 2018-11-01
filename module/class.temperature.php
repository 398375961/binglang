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
class TemperatureModule extends Module{
	public function __construct($table = ''){
		parent::__construct('temperature');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`vmid` VARCHAR(20),
				`tmpvalue` VARCHAR(4) DEFAULT 0 COMMENT '温度值',
				`equcode` VARCHAR(3) COMMENT '温度头状态',
				`createtime` INT(12) COMMENT '时间',
				`date_id` INT(8) COMMENT '日期',
				`code` VARCHAR(3) COMMENT '机箱号',
				PRIMARY KEY (`id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}