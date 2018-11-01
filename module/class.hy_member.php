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
| Note: 会员系统，会员等级
+----------------------------------------------------------------------
*/
class Hy_memberModule extends Module{
	public function __construct($table = ''){
		parent::__construct('hy_member');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`card_id` varchar(40) DEFAULT '' COMMENT '虚拟卡卡号',
			`user_id` int(11) DEFAULT '0' COMMENT '所属管理员',
			`username` varchar(20) DEFAULT '' COMMENT '持卡人姓名',
			`mobile` varchar(12) DEFAULT '' COMMENT '手机号码',
			`create_time` int(11) DEFAULT '0' COMMENT '创建时间',
			`agent_id` int(11) DEFAULT '0' COMMENT '所属代理商',
			`score` int(11) DEFAULT '0' COMMENT '会员积分',
			`degree` int(11) DEFAULT '0' COMMENT '会员等级',
			PRIMARY KEY (`id`),
			UNIQUE KEY `card_id` (`card_id`),
			KEY `user_id` (`user_id`),
			KEY `degree` (`degree`),
			KEY `agent_id` (`agent_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员表'";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}