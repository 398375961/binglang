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
| Note: class.ads_material.php 广告里面的素材
+----------------------------------------------------------------------
*/
class Ads_materialModule extends Module{
	public function __construct($table = ''){
		parent::__construct('ads_material');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`ads_id` INT(12) NOT NULL DEFAULT 0 COMMENT '广告id',
				`ma_id` INT(12) NOT NULL DEFAULT 0 COMMENT '素材id',
				`width` SMALLINT DEFAULT 0 COMMENT '显示宽度',
				`height` SMALLINT DEFAULT 0 COMMENT '显示高度',
				`_top` SMALLINT DEFAULT 0 COMMENT 'x',
				`_left` SMALLINT DEFAULT 0 COMMENT 'y',
				`link` VARCHAR(255) DEFAULT '' COMMENT '点击链接',
				`order_no` TINYINT DEFAULT 0 COMMENT '排序',
				PRIMARY KEY (`id`),
				KEY (`ads_id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}