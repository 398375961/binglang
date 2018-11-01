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
| Note: 微信公众号配置
+----------------------------------------------------------------------
*/
class Weixin_mpModule extends Module{
	public function __construct($table = ''){
		parent::__construct('weixin_mp');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`weichat_name` varchar(50) NOT NULL,
			`weichat` varchar(50) NOT NULL DEFAULT '' COMMENT '微信公众号',
			`appid` varchar(50) NOT NULL DEFAULT '',
			`appsecret` varchar(50) NOT NULL DEFAULT '',
			`token` varchar(50) NOT NULL DEFAULT '',
			`uid` int(11) NOT NULL DEFAULT '0',
			`default_msg` varchar(255) DEFAULT '' COMMENT '默认回复信息',
			PRIMARY KEY (`id`),
			UNIQUE KEY `weichat` (`weichat`),
			UNIQUE KEY `token` (`token`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}