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
| Note: 会员系统，积分日志
+----------------------------------------------------------------------
*/
class Hy_scorelogModule extends Module{
	public function __construct($table = ''){
		parent::__construct('hy_scorelog');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`cardid` varchar(40) DEFAULT '' COMMENT '会员卡卡号',
				`before_score` int(11) DEFAULT '0',
				`score` int(11) DEFAULT '0' COMMENT '记分变动记录',
				`note` varchar(255) DEFAULT '' COMMENT '记分变动原因',
				`create_time` int(11) DEFAULT '0',
				PRIMARY KEY (`id`),
				KEY `cardid` (`cardid`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}