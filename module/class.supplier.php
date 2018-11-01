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
| Note: 供应商表
+----------------------------------------------------------------------
*/
class SupplierModule extends Module{

	public function __construct($table = ''){
		parent::__construct('supplier');
	}


	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`supplier_name` varchar(20) DEFAULT '',
				`supplier_address` varchar(100) DEFAULT '',
				`link_man` varchar(20) DEFAULT '',
				`supplier_phone` varchar(20) DEFAULT '',
				`supplier_email` varchar(50) DEFAULT '',
				`user_id` INT(8) DEFAULT 0,
				PRIMARY KEY(`id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}