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
| Note: 微信自动回复
+----------------------------------------------------------------------
*/
class Weixin_autoModule extends Module{
	public function __construct($table = ''){
		parent::__construct('weixin_auto');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`weichatid` int(11) NOT NULL DEFAULT '0',
				`auto_key` varchar(50) NOT NULL DEFAULT '' COMMENT '自动回复key',
				`auto_content` text COMMENT '自动回复内容',
				`prizetype` enum('randcode','share','prize','no_prize') DEFAULT 'randcode' COMMENT '验证码出货，分享出货，不出货',
				`articleid` int(11) DEFAULT '0' COMMENT '如果是分享出货，则这个文章就是要分享的文章',
				PRIMARY KEY (`id`),
				UNIQUE KEY `weichatid` (`weichatid`,`auto_key`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}