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
class Purchases_detailModule extends Module{

	public function __construct($table = ''){
		parent::__construct('purchases_detail');
	}

	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`purchases` int(10) COMMENT '采购号',
				`goods_id` int(10),
				`goods_name` varchar(20),
				`cases` int(5) COMMENT '采购了多少大件',
				`cost_case` int(6) COMMENT '每大件多少钱',
				`units` int(5) COMMENT '一共采购多少商品',
				`unit_case` int(6) COMMENT '每大件有多少商品',
				`unit_cost` int(6) COMMENT '每个商品多少钱',
				`total_cost` int(8) COMMENT '总金额',
				`create_time` int(12),
				`create_date` int(8),
				PRIMARY KEY (`id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}