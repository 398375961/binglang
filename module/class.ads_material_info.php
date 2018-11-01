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
| Note: class.ads_material_info.php 广告素材信息
+----------------------------------------------------------------------
*/
class Ads_material_infoModule extends Module{
	public function __construct($table = ''){
		parent::__construct('ads_material_info');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`user_id` INT(8) DEFAULT 0,
				`material_name` VARCHAR(40) DEFAULT '' COMMENT '素材名称',
				`width` SMALLINT DEFAULT 0 COMMENT '宽度',
				`height` SMALLINT DEFAULT 0 COMMENT '高度',
				`type` TINYINT DEFAULT 0 COMMENT '素材类型0图片，1视频，2音乐',
				`url` VARCHAR(255) DEFAULT '' COMMENT '资源存储路径',
				`createtime` INT DEFAULT 0 COMMENT '创建时间',
				PRIMARY KEY (`id`),
				KEY(`user_id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}