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
| Note: class.weichat.php 礼品里微信公众号绑定
+----------------------------------------------------------------------
*/
class WeichatModule extends Module{
	public function __construct($table = ''){
		parent::__construct('weichat');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`vmid` VARCHAR(20) NOT NULL,
				`weichat` VARCHAR(20) NOT NULL COMMENT '微信公众号',
				`weichat_name` VARCHAR(20) NOT NULL COMMENT '公众号名称',
				`status` tinyint(2) DEFAULT 1 COMMENT '状态 1 正常 ，0 未激活',
				`goods_rand` tinyint(2) DEFAULT 0 COMMENT '是否随机出货',
				`p_type` tinyint(2) DEFAULT 0 COMMENT '发奖方式，默认为验证码输入接口，1表示由公众平台直接调用',
				`pacodes` text COMMENT '货道编号逗号分隔',
				`code` char(8) COMMENT '验证码',
				PRIMARY KEY (`id`),
				UNIQUE KEY (`vmid`,`weichat`,`code`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}