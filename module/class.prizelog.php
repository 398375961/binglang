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
| Note: class.prizelog.php 礼品发放记录
+----------------------------------------------------------------------
*/
class PrizelogModule extends Module{
	public function __construct($table = ''){
		parent::__construct('prizelog');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`vmid` VARCHAR(20) NOT NULL,
				`weichat` VARCHAR(20) NOT NULL COMMENT '微信公众号',
				`clientname` VARCHAR(32) NOT NULL COMMENT '客户标志微信号或浏览器标示',
				`prizetime` INT(12) DEFAULT 0 COMMENT '礼品发放时间',
				`pacode` char(4) COMMENT '货道编号',
				`goodsname` VARCHAR(20) COMMENT '奖品名称',
				`pr_status` TINYINT(4) DEFAULT 1 COMMENT '1正常出货2测试出货',
				PRIMARY KEY (`id`),
				KEY (`pr_status`),
				KEY (`clientname`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}