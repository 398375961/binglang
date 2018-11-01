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
| Note: class.weixin_transfer.php 微信企业付款转账记录表
+----------------------------------------------------------------------
*/
class Weixin_transferModule extends Module{
	public function __construct($table = ''){
		parent::__construct('weixin_transfer');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`user_id` INT(12) DEFAULT 0 COMMENT '收款用户uid',
				`username` VARCHAR(40) DEFAULT '' COMMENT '收款人真实姓名',
				`openid` VARCHAR(40) DEFAULT '' COMMENT '收款人openid',
				`trade_no` VARCHAR(30) DEFAULT '' COMMENT '唯一订单编号',
				`amount` INT(12) DEFAULT 0 COMMENT '转账金额',
				`tax` INT(12) DEFAULT 0 COMMENT '转账手续费',
				`status` TINYINT(4) DEFAULT 0 COMMENT '是否转账成功',
				`trade_no_weixin` VARCHAR(32) DEFAULT '' COMMENT '微信付款单号',
				`note` VARCHAR(255) DEFAULT '' COMMENT '付款描述',
				`transfer_time` INT(12) DEFAULT 0 COMMENT '转账时间',
				`deal_uid` INT(12) DEFAULT 0 COMMENT '转账操作人',
				`weixin_msg` VARCHAR(255) DEFAULT '' COMMENT '微信返回的文字描述',
				PRIMARY KEY (`id`),
				KEY(`user_id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}