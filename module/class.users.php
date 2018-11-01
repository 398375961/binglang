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
| Note: class.base_action 所有Action类的基类
+----------------------------------------------------------------------
*/
class UsersModule extends Module{
	public function __construct($table = ''){
		parent::__construct('users');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE `'.$this->table."` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`username` varchar(255) NOT NULL,
				`notename` varchar(255) DEFAULT '' COMMENT '备注名称',
				`group_id` int(11) NOT NULL COMMENT '分组id',
				`parent_id` int(11) unsigned NOT NULL DEFAULT '0',
				`password` varchar(32) NOT NULL,
				`pay_self` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否自己申请第三方支付接口',
				`money` int(11) NOT NULL DEFAULT '0' COMMENT '网上订单收入金额,单位为分',
				`money_2` int(11) NOT NULL DEFAULT '0' COMMENT '账户2资金，运营商代收的资金',
				`freeze_money` int(11) NOT NULL DEFAULT '0' COMMENT '冻结金额,单位为分',
				`freeze_money_2` int(11) NOT NULL DEFAULT '0' COMMENT '账户2冻结资金,运营商代收资金',
				`bank_name` varchar(255) NOT NULL DEFAULT '' COMMENT '银行名称',
				`card_id` varchar(40) NOT NULL DEFAULT '' COMMENT '银行卡卡号',
				`truename` varchar(100) NOT NULL DEFAULT '' COMMENT '持卡人姓名',
				`bank_area` varchar(255) NOT NULL DEFAULT '' COMMENT '开户行，支行',
				`mobile` varchar(40) NOT NULL DEFAULT '',
				`alipay` varchar(40) NOT NULL DEFAULT '',
				`sms_open` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否开启短信功能',
				`sms_num` int(12) NOT NULL DEFAULT '0' COMMENT '可用短信数目',
			PRIMARY KEY (`id`),
			UNIQUE KEY `username` (`username`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
		$this->add(array('username' => 'admin','group_id' => 0,'password' => md5('admin')));
	}
}