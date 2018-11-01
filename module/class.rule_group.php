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
| 自定义权限组是同级管理权限
+----------------------------------------------------------------------
*/
class Rule_groupModule extends Module{

	public function __construct($table = ''){
		parent::__construct('rule_group');
		$this->auto_increment = true;
	}

	public function create_table(){
		$sql = 'CREATE TABLE `'.$this->table."` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `group_name` varchar(255) NOT NULL,
			  `group_type` tinyint(2) DEFAULT 0 COMMENT '0系统权限组1自定义权限组',
			  `admin_rules` text,
			  `group_rules` text COMMENT '允许创建的权限组下线',
			  `user_id` INT(10) DEFAULT 1 COMMENT '权限组创建者',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}