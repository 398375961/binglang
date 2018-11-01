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
| Note: 商品分类表
+----------------------------------------------------------------------
*/
class Goods_typeModule extends Module{
	public function __construct($table = ''){
		parent::__construct('goods_type');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`type_name` varchar(20) DEFAULT NULL COMMENT '分类名称',
			`user_id` int(11) NOT NULL DEFAULT '1',
			`parent_id` int(11) DEFAULT '0',
			`note` varchar(255) DEFAULT '' COMMENT '说明',
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`),
			KEY `parent_id` (`parent_id`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}