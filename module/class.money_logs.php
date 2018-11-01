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
| 资金明细，资金变动明细
+----------------------------------------------------------------------
*/
class Money_logsModule extends Module{
	public function __construct($table = ''){
		parent::__construct('money_logs');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE `'.$this->table."` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`uid` int(11) DEFAULT '0',
				`money_before` int(11) DEFAULT '0' COMMENT '变动前金额',
				`money` int(11) DEFAULT '0' COMMENT '变动金额',
				`note` varchar(255) DEFAULT NULL,
				`dt_time` int(11) NOT NULL,
				`account` tinyint(4) DEFAULT '1' COMMENT '变动账户',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}