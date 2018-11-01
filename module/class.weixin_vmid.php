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
| Note: 微信关注 礼品派送配置
+----------------------------------------------------------------------
*/
class Weixin_vmidModule extends Module{
	public function __construct($table = ''){
		parent::__construct('weixin_vmid');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`weichatid` int(11) NOT NULL DEFAULT '0',
			`vmid` char(10) NOT NULL DEFAULT '' COMMENT '机器编号',
			`pacodes` text COMMENT '货道编号',
			`goods_rand` tinyint(4) DEFAULT '0' COMMENT '0 顺序 1 随机',
			`status` tinyint(4) DEFAULT '1' COMMENT '0 锁定  1 正常 2测试',
			PRIMARY KEY (`id`),
			UNIQUE KEY `weichatid` (`weichatid`,`vmid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}