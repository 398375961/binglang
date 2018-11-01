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
| Note: class.ads_plan.php 广告计划
+----------------------------------------------------------------------
*/
class Ads_planModule extends Module{
	public function __construct($table = ''){
		parent::__construct('ads_plan');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`vmids` TEXT COMMENT '所属机器',
				`plan_name` VARCHAR(40) COMMENT '计划名称',
				`user_id` INT(12) DEFAULT 0,
				`ads_id` INT(12) DEFAULT 0 COMMENT '广告id',
				`date_start` CHAR(10) DEFAULT '' COMMENT 'Y-m-d',
				`date_end` CHAR(10) DEFAULT '' COMMENT 'Y-m-d',
				`weeks` VARCHAR(15) DEFAULT '' COMMENT '0,1,2,3...',
				`time_start` CHAR(5) DEFAULT '00:00' COMMENT '开始时间',
				`time_end` CHAR(5) DEFAULT '23:59' COMMENT '结束时间',
				PRIMARY KEY (`id`),
				FULLTEXT KEY `vmids` (`vmids`),
				KEY (`user_id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}