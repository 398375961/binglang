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
| Note: 微信图文信息
+----------------------------------------------------------------------
*/
class Weixin_articleModule extends Module{
	public function __construct($table = ''){
		parent::__construct('weixin_article');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`pic` varchar(255) DEFAULT '' COMMENT '封面图片',
				`title` varchar(255) DEFAULT '' COMMENT '文章标题',
				`weichatid` int(10) DEFAULT '0' COMMENT '公众号id',
				`content` mediumtext COMMENT '文章内容',
				PRIMARY KEY (`id`),
				KEY `weichatid` (`weichatid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}