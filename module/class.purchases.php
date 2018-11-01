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
| Note:  采购记录
+----------------------------------------------------------------------
*/
class PurchasesModule extends Module{

	public function __construct($table = ''){
		parent::__construct('purchases');
	}

	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE `'.$this->table."`(
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`supplier` INT(10) COMMENT '供应商ID',
				`supplier_name` VARCHAR(20),
				`goods_nums` smallint(4) COMMENT '商品种类',
				`total_cost` INT(8) COMMENT '总金额',
				`create_time` INT(12),
				`create_date` INT(8),
				`user_id` INT(8) DEFAULT 0,
				PRIMARY KEY (`id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}