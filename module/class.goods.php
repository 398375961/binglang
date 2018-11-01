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
class GoodsModule extends Module{
	public function __construct($table = ''){
		parent::__construct('goods');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`goods_name` varchar(20) COMMENT '商品名称', 
				`goods_note` varchar(40) COMMENT '备注', 
				`goods_type` int(5) COMMENT '商品类别',
				`goods_guige` varchar(10) COMMENT '商品规格，件，袋，包',
				`unit_case` INT(8) COMMENT '每大件商品里面有多少小件',
				`goods_price` int(5),
				`goods_num` int(10) COMMENT '库存',
				`user_id` INT(8) DEFAULT 0,
				`goods_pic` varchar(255) DEFAULT '' COMMENT '商品图片',
				`goods_desc` text COMMENT '商品描述',
				PRIMARY KEY (`id`),
				KEY (`user_id`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}