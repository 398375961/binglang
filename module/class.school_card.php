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
| Note: class.school_card.php 校园卡信息表
+----------------------------------------------------------------------
*/
class School_cardModule extends Module{
	public function __construct($table = ''){
		parent::__construct('school_card');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`cardid` VARCHAR(20) NOT NULL COMMENT '校园卡卡号',
				`studentid` VARCHAR(20) NOT NULL COMMENT '学生证编号',
				`account` INT(12) DEFAULT 0 COMMENT '账户余额，单位为分',
				`username` VARCHAR(20) DEFAULT '' COMMENT '持卡人姓名',
				`mobile` VARCHAR(11) DEFAULT '' COMMENT '持卡人联系方式',
				  `day_limit` int(10) DEFAULT '0' COMMENT '日限额',
				`status` TINYINT(2) DEFAULT 1 COMMENT '状态0锁定,1正常',
				`goods_allow` TEXT  COMMENT '允许购买的商品',
				`goods_fobid` TEXT COMMENT '禁止购买的商品',
				`note` VARCHAR(255) DEFAULT '' COMMENT '备注，填写班级等',
				`createtime` INT(12) DEFAULT 0 COMMENT '发卡时间',
				`user_id` INT(12) DEFAULT 0 COMMENT '所属管理员',
				`client_id` VARCHAR(18) DEFAULT '' COMMENT '监护人身份证，家长身份证',
				`password` varchar(10) DEFAULT '' COMMENT '支付密码',
				PRIMARY KEY (`cardid`),
				KEY (`studentid`),
				KEY (`user_id`),
				KEY (`client_id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}